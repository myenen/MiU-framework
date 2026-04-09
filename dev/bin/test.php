<?php

declare(strict_types=1);

$tests = [
    dirname(__DIR__) . '/tests/ArrTest.php',
    dirname(__DIR__) . '/tests/AuthorizationServiceTest.php',
    dirname(__DIR__) . '/tests/FileUploadServiceTest.php',
    dirname(__DIR__) . '/tests/IdentityServiceTest.php',
    dirname(__DIR__) . '/tests/ModelsUpdateTest.php',
    dirname(__DIR__) . '/tests/MaintenanceModeTest.php',
    dirname(__DIR__) . '/tests/ModelCacheTest.php',
    dirname(__DIR__) . '/tests/RouterDynamicDispatchTest.php',
    dirname(__DIR__) . '/tests/StrTest.php',
    dirname(__DIR__) . '/tests/UserServiceTest.php',
];

$failures = 0;

foreach ($tests as $test) {
    echo 'Running ' . basename($test) . "...\n";

    try {
        require $test;
    } catch (Throwable $exception) {
        $failures++;
        fwrite(STDERR, 'FAILED: ' . $exception->getMessage() . "\n");
    }
}

exit($failures > 0 ? 1 : 0);
