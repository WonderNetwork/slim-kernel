<?php

declare(strict_types=1);

use WonderNetwork\SlimKernel\Apache\Vhost;

return [
    Vhost::secure('example.com')
        ->withServerAdmin('support@example.com')
        ->withServerAlias('www.example.com')
        ->withStandarLogs(),
    Vhost::upgradeToSecure('example.com', 'Redirect to HTTPS'),
];
