<?php

declare(strict_types=1);

use WonderNetwork\SlimKernel\Apache\Vhost;

return [
    Vhost::secure('testlocal.ly')->withLetsEncrypt(),
    Vhost::secure('staging-secure.testlocal.ly')->withLetsEncrypt('staging.testlocal.ly'),
];
