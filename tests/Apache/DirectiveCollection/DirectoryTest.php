<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\DirectiveCollection;

use PHPUnit\Framework\TestCase;
use WonderNetwork\SlimKernel\Apache\Exception\ApacheConfigurationException;
use WonderNetwork\SlimKernel\Apache\Exception\SecureSiteException;
use WonderNetwork\SlimKernel\Apache\Filesystem\Path;
use WonderNetwork\SlimKernel\Apache\Vhost;

final class DirectoryTest extends TestCase {
    public function testClientCertificateRequiresSecureEndpoint(): void {
        $sut = Directory::from(
            directory: Path::of('/var/www/example.com/current/public/secure'),
            vhost: Vhost::http('example.com'),
        );

        try {
            $sut->withClientCertificateRequirement();
            $this->fail("Expected exception not thrown");
        } catch (ApacheConfigurationException $exception) {
            self::assertInstanceOf(SecureSiteException::class, $exception);
        }
    }

    public function testClientCertificateRequiresDowngradedTls(): void {
        $sut = Directory::from(
            directory: Path::of('/var/www/example.com/current/public/secure'),
            vhost: Vhost::secure('example.com'),
        );

        try {
            $sut->withClientCertificateRequirement();
            $this->fail("Expected exception not thrown");
        } catch (ApacheConfigurationException $exception) {
            self::assertInstanceOf(SecureSiteException::class, $exception);
        }
    }
}
