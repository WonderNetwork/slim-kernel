<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\ContentSecurityPolicy;

use PHPUnit\Framework\TestCase;

final class ContentSecurityPolicyBuilderTest extends TestCase {
    public function test(): void {
        $actual = 'Header always set Content-Security-Policy '.ContentSecurityPolicyBuilder::start()
            ->rule(
                "defaults",
                fn ($rule) => $rule
                    ->defaultSrc(ContentSecurityPolicyBuilder::None)
                    ->connectSrc(ContentSecurityPolicyBuilder::Self)
                    ->fontSrc(ContentSecurityPolicyBuilder::Self, ContentSecurityPolicyBuilder::Data)
                    ->imgSrc(ContentSecurityPolicyBuilder::Self)
                    ->objectSrc(ContentSecurityPolicyBuilder::None)
                    ->scriptSrc(ContentSecurityPolicyBuilder::Self, ContentSecurityPolicyBuilder::WasmUnsafeEval)
                    ->styleSrc(ContentSecurityPolicyBuilder::Self, ContentSecurityPolicyBuilder::UnsafeInline)
                    ->frameAncestors(ContentSecurityPolicyBuilder::None)
                    ->formAction(ContentSecurityPolicyBuilder::Self)
                    ->baseUri(ContentSecurityPolicyBuilder::None)
                    ->manifestSrc(ContentSecurityPolicyBuilder::Self)
                    ->childSrc(ContentSecurityPolicyBuilder::Blob),
            )

            ->rule(
                "plausible",
                fn ($rule) => $rule
                    ->connectSrc("https://plausible.io"),
            )

            ->rule(
                "friendlycaptcha",
                fn ($rule) => $rule
                    ->connectSrc("https://api.friendlycaptcha.com")
                    ->frameSrc("https://*.frcapi.com"),
            )

            ->rule(
                "sentry",
                fn ($rule) => $rule
                    ->connectSrc("https://*.sentry.io"),
            )

            ->rule(
                "pdf rendering",
                fn ($rule) => $rule
                    ->connectSrc("https://assetsdev.testlocal.ly")
                    ->imgSrc(ContentSecurityPolicyBuilder::Data, 'https://assetsdev.testlocal.ly'),
            )

            ->rule(
                "admin area metric charts",
                fn ($rule) => $rule
                    ->scriptSrc("https://www.gstatic.com/")
                    ->styleSrc("https://www.gstatic.com/"),
            )

            ->toString();

        $expected = <<<EOF
        Header always set Content-Security-Policy \
            "default-src 'none'; \
            connect-src 'self' \
                https://plausible.io \
                https://api.friendlycaptcha.com \
                https://*.sentry.io \
                https://assetsdev.testlocal.ly; \
            font-src 'self' \
                data:; \
            img-src 'self' \
                data: \
                https://assetsdev.testlocal.ly; \
            object-src 'none'; \
            script-src 'self' \
                'wasm-unsafe-eval' \
                https://www.gstatic.com/; \
            style-src 'self' \
                'unsafe-inline' \
                https://www.gstatic.com/; \
            frame-ancestors 'none'; \
            form-action 'self'; \
            base-uri 'none'; \
            manifest-src 'self'; \
            child-src blob:; \
            frame-src https://*.frcapi.com;"
        EOF;

        self::assertSame($expected, (string) $actual);
    }
}
