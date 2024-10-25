<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http;

use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use WonderNetwork\SlimKernel\Accessor\ArrayAccessor;

/**
 * @property ArrayAccessor $params
 * @property ArrayAccessor $post
 * @property ArrayAccessor $query
 * @property ArrayAccessor $server
 */
final class RequestParams {
    private ArrayAccessor $params;
    private ArrayAccessor $query;
    private ArrayAccessor $server;
    private ServerRequestInterface $request;

    public static function of(ServerRequestInterface $request): self {
        return new self($request);
    }

    public function __construct(ServerRequestInterface $request) {
        $this->params = ArrayAccessor::of($request->getParsedBody(), [$this, 'exceptionFactory']);
        $this->query = ArrayAccessor::of($request->getQueryParams(), [$this, 'exceptionFactory']);
        $this->server = ArrayAccessor::of($request->getServerParams(), [$this, 'exceptionFactory']);
        $this->request = $request;
    }

    public function exceptionFactory(string $message): HttpBadRequestException {
        return new HttpBadRequestException($this->request, $message);
    }

    public function __get(string $name): ArrayAccessor {
        switch ($name) {
            case 'params':
            case 'post':
                return $this->params;
            case 'query':
                return $this->query;
            case 'server':
                return $this->server;
        }

        throw new RuntimeException("Unknown collection: $name");
    }

    /** @return never */
    public function notFound(string $message = null): void {
        throw new HttpNotFoundException($this->request, $message);
    }

    /** @return never */
    public function badRequest(string $message = null): void {
        throw new HttpBadRequestException($this->request, $message);
    }

    public function request(): ServerRequestInterface {
        return $this->request;
    }
}
