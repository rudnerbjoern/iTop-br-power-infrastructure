<?php

declare(strict_types=1);

require_once __DIR__ . '/../scripts/prepare-release.php';

$manifest = file_get_contents(__DIR__ . '/../extension.xml');
$modules = [];
foreach (releaseModulePaths() as $name => $path) {
    $modules[$name] = file_get_contents(__DIR__ . '/../' . $path);
}
$xml = simplexml_load_string($manifest);
$current = trim((string) $xml->version);
$highest = $current;
foreach ($modules as $name => $content) {
    preg_match("~'" . preg_quote($name, '~') . "/([^']+)'~", $content, $match);
    if (version_compare($match[1], $highest, '>')) { $highest = $match[1]; }
}
$parts = explode('.', $highest);
$next = $parts[0] . '.' . $parts[1] . '.' . ((int) $parts[2] + 1);

$tests = [
    'prepare all module versions and internal dependencies without unrelated edits' => static function () use ($manifest, $modules, $next): void {
        $updated = prepareReleaseContents($manifest, $modules, $next, '2028-02-29');
        $xml = simplexml_load_string($updated[0]);
        if ((string) $xml->version !== $next || (string) $xml->release_date !== '2028-02-29'
            || preg_replace('~<(version|release_date)>.*?</\\1>~s', '', $updated[0])
                !== preg_replace('~<(version|release_date)>.*?</\\1>~s', '', $manifest)) {
            throw new RuntimeException('Unexpected manifest edits.');
        }
        foreach (array_keys($modules) as $index => $name) {
            $expected = preg_replace_callback(
                '~(?<=[\'|])br-power-infrastructure(?:-bridge-for-[a-z-]+)?/[^\'|]+~',
                static function (array $match) use ($next): string {
                    return explode('/', $match[0])[0] . '/' . $next;
                },
                $modules[$name]
            );
            if ($updated[$index + 1] !== $expected) {
                throw new RuntimeException('Unexpected registration, dependency or unrelated edit: ' . $name);
            }
        }
    },
];
$ambiguous = $modules;
$ambiguous['br-power-infrastructure'] .= $modules['br-power-infrastructure'];
$mismatched = $modules;
$mismatched['br-power-infrastructure'] = str_replace("'br-power-infrastructure/$current'", "'br-power-infrastructure/0.0.0'", $modules['br-power-infrastructure']);
$missing = $modules;
array_pop($missing);
$badBridge = $modules;
$bridge = 'br-power-infrastructure-bridge-for-datacenter-view';
$badBridge[$bridge] = preg_replace("~'" . $bridge . "/[^']+'~", "'$bridge/invalid'", $badBridge[$bridge]);

$cases = [
    'reject missing bridge' => [$manifest, $missing, $next, '2028-02-29'],
    'reject invalid bridge version' => [$manifest, $badBridge, $next, '2028-02-29'],
    'reject target older than a bridge' => [$manifest, $modules, $highest, '2028-02-29'],
    'reject malformed version' => [$manifest, $modules, 'v3.2.3', '2028-02-29'],
    'reject version with leading zeros' => [$manifest, $modules, '03.2.3', '2028-02-29'],
    'reject unchanged version' => [$manifest, $modules, $current, '2028-02-29'],
    'reject older version' => [$manifest, $modules, '0.0.0', '2028-02-29'],
    'reject invalid calendar date' => [$manifest, $modules, $next, '2027-02-29'],
    'reject ambiguous module version' => [$manifest, $ambiguous, $next, '2028-02-29'],
    'reject mismatched module version' => [$manifest, $mismatched, $next, '2028-02-29'],
    'reject missing date element' => [preg_replace('~<release_date>.*?</release_date>~s', '', $manifest), $modules, $next, '2028-02-29'],
];
foreach ($cases as $name => $arguments) {
    $tests[$name] = static function () use ($arguments): void {
        try {
            prepareReleaseContents(...$arguments);
        } catch (RuntimeException $error) {
            return;
        }
        throw new RuntimeException('Invalid release input was accepted.');
    };
}

return $tests;
