<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\Serializer;

use Fig\Http\Message\StatusCodeInterface;
use Invoker\Exception\InvocationException;
use Invoker\Exception\NotCallableException;
use Invoker\Exception\NotEnoughParametersException;
use Invoker\InvokerInterface;
use JsonException;
use JsonSerializable;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use ReflectionClass;

final readonly class JsonSerializingInvoker implements InvokerInterface {
    public function __construct(
        private InvokerInterface $inner,
        private JsonSerializingInvokerOptions $options,
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * @param callable $callable
     * @param array<mixed> $parameters
     * @throws InvocationException
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     * @throws JsonException
     */
    public function call($callable, array $parameters = []): mixed {
        $result = $this->inner->call($callable, $parameters);

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        if (false === $this->shouldConvert($result)) {
            return $result;
        }

        $statusCode = StatusCodeInterface::STATUS_OK;

        if ($result instanceof HasStatusCode) {
            $statusCode = $result->getStatusCode();
        }

        return $this->responseFactory->createResponse($statusCode)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(
                $this->streamFactory->createStream(
                    json_encode($result, JSON_THROW_ON_ERROR),
                ),
            );
    }

    private function shouldConvert(mixed $result): bool {
        if (is_resource($result)) {
            return false;
        }

        if (false === is_object($result)) {
            return $this->options->serializeSimpleTypes;
        }

        if ($this->options->serializeObjects) {
            return true;
        }

        $reflection = new ReflectionClass($result);

        if (0 !== count($reflection->getAttributes(Json::class))) {
            return true;
        }

        if ($result instanceof JsonSerializable) {
            return $this->options->serializeJsonSerializable;
        }

        return false;
    }
}
