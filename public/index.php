<?php

declare(strict_types=1);

/**
 * Uygulamayi baslatan ve calistiran on controller.
 */
$basePath = dirname(__DIR__);

require $basePath . '/config/bootstrap.php';

$app = bootstrapApplication($basePath);
$app->run();
