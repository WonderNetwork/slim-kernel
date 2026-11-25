<?php

declare(strict_types=1);

use WonderNetwork\SlimKernel\Apache\Vhost;

return Vhost::http('example.com')->withDeflateCompression();
