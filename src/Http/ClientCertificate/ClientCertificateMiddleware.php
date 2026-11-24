<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\ClientCertificate;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpBadRequestException;
use WonderNetwork\SlimKernel\Accessor\ArrayAccessor;

final readonly class ClientCertificateMiddleware implements MiddlewareInterface {
    public function __construct(private ClientCertificateSource $source) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $pem = $this->source->findPem($request);

        if (null === $pem) {
            return $handler->handle($request);
        }

        $asn = openssl_x509_read($pem);

        if (false === $asn) {
            throw new HttpBadRequestException($request, 'Unable to parse client certificate');
        }

        $payload = openssl_x509_parse($asn);

        if (false === $payload) {
            throw new HttpBadRequestException($request, 'Unable to parse client certificate');
        }

        $subject = ArrayAccessor::of($payload)->at('subject');

        $certificate = new ClientCertificate(
            commonName: $subject->requireString('CN'),
            emailAddress: $subject->maybeString('emailAddress'),
        );

        return $handler->handle(
            $request->withAttribute(ClientCertificate::class, $certificate),
        );
    }
}
