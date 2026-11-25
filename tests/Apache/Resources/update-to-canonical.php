<?php

declare(strict_types=1);

use WonderNetwork\SlimKernel\Apache\Vhost;

return Vhost::upgradeToCanonical('example.com', ['www.example.com', 'www2.example.com']);
