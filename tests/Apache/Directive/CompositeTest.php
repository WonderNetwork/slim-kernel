<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Directive;

use PHPUnit\Framework\TestCase;
use WonderNetwork\SlimKernel\Apache\Exception\ApacheConfigurationException;

final class CompositeTest extends TestCase {
    public function testComposite(): void {
        $sut = Composite::fromDirectives(
            Named::tryFrom('ServerName', 'example.com'),
            Named::tryFrom('ServerAdmin', null),
            Named::tryFrom('ServerAlias', "www.example.com"),
        );

        $actual = $sut->toString();

        self::assertSame(
            <<<EOF
            ServerName example.com
            ServerAlias www.example.com
            EOF,
            $actual,
        );
    }

    public function testGlueNext(): void {
        $sut = Composite::emptyGroup()
            ->with(Comment::from('Next line comment'))
            ->with(Named::from('ServerName', 'example.com'));

        $actual = $sut->toString();

        self::assertSame(
            <<<EOF
            # Next line comment
            ServerName example.com
            EOF,
            $actual,
        );
    }

    public function testGroup(): void {
        $sut = Composite::group(
            Composite::fromDirectives(
                Named::tryFrom('ServerName', 'example.com'),
            ),
            Composite::fromDirectives(
                Named::tryFrom('ServerAlias', "www.example.com"),
            ),
        );

        $actual = $sut->toString();

        self::assertSame(
            <<<EOF
            ServerName example.com

            ServerAlias www.example.com
            EOF,
            $actual,
        );
    }

    public function testThrowsWhenEndsInGlued(): void {
        $sut = Composite::fromDirectives(Comment::from('Next line comment'));

        try {
            $sut->toString();
            $this->fail("Expected to throw");
        } catch (ApacheConfigurationException $e) {
            self::assertSame("The last line refers to a line after it. It cannot be last", $e->getMessage());
        }
    }
}
