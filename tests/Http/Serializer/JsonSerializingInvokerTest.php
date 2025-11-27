<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\Serializer;

use Fig\Http\Message\StatusCodeInterface;
use JsonSerializable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use stdClass;

final class JsonSerializingInvokerTest extends TestCase {
    /**
     * @return iterable<array{0:mixed}>
     */
    public static function dataSimpleTypes(): iterable {
        yield [null];
        yield [1];
        yield [1.5];
        yield ["hello"];
        yield [['message' => 'Success']];
    }

    #[DataProvider('dataSimpleTypes')]
    public function testSerializesSimpleTypes(mixed $result): void {
        $sut = JsonSerializingInvokerMother::all();
        $response = $sut->call(fn () => $result);
        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    #[DataProvider('dataSimpleTypes')]
    public function testSkipsWhenSimpleTypesDisabled(mixed $result): void {
        $sut = JsonSerializingInvokerMother::onlyMarked();

        $response = $sut->call(fn () => $result);
        self::assertSame($result, $response);
    }

    public function testSerializesJsonSerializable(): void {
        $sut = JsonSerializingInvokerMother::all();

        $object = new class () implements JsonSerializable {
            public function jsonSerialize(): null {
                return null;
            }
        };

        $response = $sut->call(fn () => $object);

        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testSkipsWhenJsonSerializableDisabled(): void {
        $sut = JsonSerializingInvokerMother::onlyMarked();

        $object = new class () implements JsonSerializable {
            public function jsonSerialize(): null {
                return null;
            }
        };
        $response = $sut->call(fn () => $object);

        self::assertSame($object, $response);
    }

    public function testSerializesObjects(): void {
        $sut = JsonSerializingInvokerMother::all();

        $response = $sut->call(fn () => new stdClass());

        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testSkipsWhenObjectsDisabled(): void {
        $sut = JsonSerializingInvokerMother::onlyMarked();

        $object = new stdClass();
        $response = $sut->call(fn () => $object);

        self::assertSame($object, $response);
    }

    public function testSkipsOnResources(): void {
        $sut = JsonSerializingInvokerMother::all();

        $resource = fopen('php://temp', 'rb+');
        $response = $sut->call(fn () => $resource);

        self::assertSame($resource, $response);
    }

    public function testSerializesObjectsMarkedWithAttribute(): void {
        $sut = JsonSerializingInvokerMother::onlyMarked();

        $response = $sut->call(fn () => JsonResponse::of('whatever'));

        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testSetsCustomStatusCode(): void {
        $sut = JsonSerializingInvokerMother::all();

        $response = $sut->call(fn () => JsonResponse::of(null, StatusCodeInterface::STATUS_ACCEPTED));

        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertSame(202, $response->getStatusCode());
    }
}
