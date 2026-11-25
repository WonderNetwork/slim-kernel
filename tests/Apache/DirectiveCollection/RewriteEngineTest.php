<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\DirectiveCollection;

use PHPUnit\Framework\TestCase;
use WonderNetwork\SlimKernel\Apache\Directive\Composite;
use WonderNetwork\SlimKernel\Apache\Exception\ApacheConfigurationException;

final class RewriteEngineTest extends TestCase {
    public function testCompact(): void {
        $sut = RewriteEngine::compact()
            ->withRules('Foo')
            ->withRules('Bar');

        $actual = Composite::fromCollection($sut)->toString();

        self::assertSame(
            <<<EXPECTED
            RewriteEngine on
            RewriteRule Foo
            RewriteRule Bar
            EXPECTED,
            $actual,
        );
    }

    public function testExplicitlyDisable(): void {
        $sut = RewriteEngine::auto()
            ->withRules('Foo', 'Bar')
            ->disable();

        $actual = Composite::fromCollection($sut)->toString();

        self::assertSame("RewriteEngine off", $actual);
    }

    public function testConditionsStack(): void {
        $sut = RewriteEngine::auto()
            ->withCondition('Condition alpha')
            ->withCondition('Condition bravo')
            ->withRules('Foo')
            ->withCondition('Condition charlie')
            ->withRules('Bar');

        $actual = Composite::fromCollection($sut)->toString();

        self::assertSame(
            <<<EXPECTED
            RewriteEngine on

            RewriteCond Condition alpha
            RewriteCond Condition bravo
            RewriteRule Foo

            RewriteCond Condition charlie
            RewriteRule Bar
            EXPECTED,
            $actual,
        );
    }

    public function testComment(): void {
        $sut = RewriteEngine::auto()
            ->withComment('Foo')
            ->withRules('Bar');

        $actual = Composite::fromCollection($sut)->toString();

        self::assertSame(
            <<<EXPECTED
            RewriteEngine on

            # Foo
            RewriteRule Bar
            EXPECTED,
            $actual,
        );
    }

    public function testConditionsThrowWhenUnused(): void {
        $sut = RewriteEngine::auto()
            ->withCondition('unused');

        try {
            Composite::fromCollection($sut)->toString();
            $this->fail('Expected to throw');
        } catch (ApacheConfigurationException $e) {
            self::assertSame(
                'RewriteCond was left dangling without a following RewriteRule',
                $e->getMessage(),
            );
        }
    }
}
