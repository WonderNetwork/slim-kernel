<?php

declare(strict_types=1);

use WonderNetwork\SlimKernel\Apache\Vhost;

return Vhost::secure('secure.example.com')->withClientCertificateRequirement(
    signerOrganization: "WonderProxy, Inc",
);
