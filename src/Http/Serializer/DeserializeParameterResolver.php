<?php
declare(strict_types=1);
namespace WonderNetwork\SlimKernel\Http\Serializer;

use Invoker\ParameterResolver\ParameterResolver;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use Slim\Exception\HttpBadRequestException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final readonly class DeserializeParameterResolver implements ParameterResolver {
    public function __construct(private DenormalizerInterface $serializer) {
    }

    /**
     * @param array<mixed> $providedParameters
     * @param array<mixed> $resolvedParameters
     * @return array<mixed>
     */
    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters,
    ): array {
        $parameters = $reflection->getParameters();

        if (! empty($resolvedParameters)) {
            $parameters = array_diff_key($parameters, $resolvedParameters);
        }

        /** @var ServerRequestInterface $request */
        $request = $providedParameters['request'];

        foreach ($parameters as $index => $parameter) {
            $parameterType = $parameter->getType();
            if (false === $parameterType instanceof ReflectionNamedType || $parameterType->isBuiltin()) {
                continue;
            }

            $parameterClass = $parameterType->getName();
            foreach ($parameter->getAttributes(Payload::class) as $payload) {
                /** @var Payload $attribute */
                $attribute = $payload->newInstance();
                $data = match ($attribute->source) {
                    PayloadSource::Post => $request->getParsedBody(),
                    PayloadSource::Get => $request->getQueryParams(),
                };

                try {
                    $resolvedParameters[$index] = $this->serializer->denormalize(
                        $data,
                        $parameterClass,
                        context: $attribute->context,
                    );
                } catch (NotNormalizableValueException $e) {
                    throw new HttpBadRequestException(
                        $request,
                        sprintf(
                            "Failed to parse input at path %s. Expected %s, got %s",
                            $e->getPath(),
                            implode(', ', $e->getExpectedTypes()),
                            $e->getCurrentType(),
                        ),
                    );
                } catch (MissingConstructorArgumentsException $e) {
                    throw new HttpBadRequestException(
                        $request,
                        sprintf(
                            "Failed to parse input because of missing fields: %s",
                            implode(', ', $e->getMissingConstructorArguments()),
                        ),
                    );
                } catch (ExceptionInterface) {
                    throw new HttpBadRequestException($request);
                }
            }
        }

        return $resolvedParameters;
    }
}
