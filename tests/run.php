<?php

declare(strict_types=1);

// Execute the local PDU batch creation method without installing iTop.
try {
    $model = simplexml_load_file(
        __DIR__ . '/../br-power-infrastructure/datamodel.br-power-infrastructure.xml',
        SimpleXMLElement::class,
        LIBXML_NONET
    );
    if ($model === false) {
        throw new RuntimeException('Cannot load the extension data model.');
    }
    $methods = $model->xpath('/itop_design/classes/class[@id="PDU"]/methods/method[@id="CreatePowerSockets"]/code');
    if ($methods === false || count($methods) !== 1) {
        throw new RuntimeException('Expected exactly one CreatePowerSockets method.');
    }
    eval('trait PowerSocketCreationUnderTest {' . (string) $methods[0] . '}');

    $tests = require __DIR__ . '/power-sockets.php';
    $tests = array_merge($tests, require __DIR__ . '/socket-assignment.php');
    $tests = array_merge($tests, require __DIR__ . '/schemas.php');
    $tests = array_merge($tests, require __DIR__ . '/prepare-release.php');
    if (!is_array($tests) || $tests === []) {
        throw new RuntimeException('No tests found.');
    }
    $failures = 0;
    foreach ($tests as $name => $test) {
        try {
            $test();
            echo "PASS: $name\n";
        } catch (Throwable $error) {
            $failures++;
            fwrite(STDERR, "FAIL: $name: {$error->getMessage()}\n");
        }
    }
    echo count($tests) . " tests, $failures failures\n";
    exit($failures === 0 ? 0 : 1);
} catch (Throwable $error) {
    fwrite(STDERR, "Test setup failed: {$error->getMessage()}\n");
    exit(1);
}
