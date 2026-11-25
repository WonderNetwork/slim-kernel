<?php

declare(strict_types=1);

use WonderNetwork\SlimKernel\Apache\ContentSecurityPolicy\ContentSecurityPolicyBuilder;
use WonderNetwork\SlimKernel\Apache\Vhost;

return Vhost::http('example.com')->withContentSecurityPolicy(
    fn ($builder) => $builder
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
                ->connectSrc("https://assets.example.com")
                ->imgSrc(ContentSecurityPolicyBuilder::Data, 'https://assets.example.com'),
        )

        ->rule(
            "admin area metric charts",
            fn ($rule) => $rule
                ->scriptSrc("https://www.gstatic.com/")
                ->styleSrc("https://www.gstatic.com/"),
        ),
);
