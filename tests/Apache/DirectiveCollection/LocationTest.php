<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\DirectiveCollection;

use PHPUnit\Framework\TestCase;
use WonderNetwork\SlimKernel\Apache\Exception\ApacheConfigurationException;
use WonderNetwork\SlimKernel\Apache\Exception\SecureSiteException;
use WonderNetwork\SlimKernel\Apache\Vhost;

final class LocationTest extends TestCase {
    public function testClientCertificateRequiresSecureEndpoint(): void {
        $sut = Location::from('/foo', vhost: Vhost::http('example.com'));

        try {
            $sut->withClientCertificateRequirement();
            $this->fail("Expected exception not thrown");
        } catch (ApacheConfigurationException $exception) {
            self::assertInstanceOf(SecureSiteException::class, $exception);
        }
    }

    public function testClientCertificateRequiresDowngradedTls(): void {
        $sut = Location::from('/foo', vhost: Vhost::secure('example.com'));

        try {
            $sut->withClientCertificateRequirement();
            $this->fail("Expected exception not thrown");
        } catch (ApacheConfigurationException $exception) {
            self::assertInstanceOf(SecureSiteException::class, $exception);
        }
    }
}
