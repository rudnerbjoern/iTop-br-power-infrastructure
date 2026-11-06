<?php

declare(strict_types=1);

/** All first-party modules shipped in the release package. */
function releaseModulePaths(): array
{
    $modules = ['br-power-infrastructure'];
    foreach (['datacenter-view', 'datacenter-view-extended', 'teemip-ip-mgmt'] as $bridge) {
        $modules[] = 'br-power-infrastructure-bridge-for-' . $bridge;
    }
    return array_combine($modules, array_map(
        static fn (string $module): string => "$module/module.$module.php",
        $modules
    ));
}

/** Validate every input before producing updated manifest and module contents. */
function prepareReleaseContents(string $manifest, array $modules, string $version, string $date): array
{
    if (!preg_match('/^(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)$/D', $version)) {
        throw new RuntimeException('Version must use X.Y.Z format without leading zeros.');
    }
    $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    if ($parsedDate === false || $parsedDate->format('Y-m-d') !== $date) {
        throw new RuntimeException('Date must be a valid YYYY-MM-DD date.');
    }
    $previous = libxml_use_internal_errors(true);
    try {
        $xml = simplexml_load_string($manifest, SimpleXMLElement::class, LIBXML_NONET);
        if ($xml === false || $xml->getName() !== 'extension'
            || count($xml->version) !== 1 || count($xml->release_date) !== 1
            || trim((string) $xml->extension_code) !== 'br-power-infrastructure') {
            throw new RuntimeException('Expected one version and release_date in the power infrastructure extension manifest.');
        }
        $currentVersion = trim((string) $xml->version);
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
    }
    if (array_keys($modules) !== array_keys(releaseModulePaths())) {
        throw new RuntimeException('Expected the main module and all three bridge modules.');
    }
    foreach ($modules as $name => $content) {
        $pattern = "~'" . preg_quote($name, '~') . "/([^']+)'~";
        if (preg_match_all($pattern, $content, $matches) !== 1) {
            throw new RuntimeException('Expected exactly one registration version for ' . $name);
        }
        $oldVersion = $matches[1][0];
        if ($name === 'br-power-infrastructure' && $oldVersion !== $currentVersion) {
            throw new RuntimeException('Extension and main module versions must match before preparing a release.');
        }
        if (!preg_match('/^(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)$/D', $oldVersion)
            || !version_compare($version, $oldVersion, '>')) {
            throw new RuntimeException('Release version must be newer than ' . $name . '/' . $oldVersion);
        }
        $content = preg_replace_callback($pattern, static fn (): string => "'$name/$version'", $content);
        // Update internal dependency alternatives, preserving third-party requirements.
        $modules[$name] = preg_replace_callback(
            '~(?<=[\'|])br-power-infrastructure/[^\'|]+~',
            static fn (): string => 'br-power-infrastructure/' . $version,
            $content
        );
    }
    foreach (['version' => $version, 'release_date' => $date] as $element => $value) {
        $manifest = preg_replace_callback(
            '~<' . $element . '>.*?</' . $element . '>~s',
            static fn (): string => "<$element><![CDATA[$value]]></$element>",
            $manifest,
            -1,
            $count
        );
        if ($manifest === null || $count !== 1) {
            throw new RuntimeException('Expected exactly one editable ' . $element . ' element.');
        }
    }
    return array_merge([$manifest], array_values($modules));
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    try {
        if ($argc < 2 || $argc > 3) {
            throw new RuntimeException('Usage: php scripts/prepare-release.php X.Y.Z [YYYY-MM-DD]');
        }
        $date = $argv[2] ?? (new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin')))->format('Y-m-d');
        $root = dirname(__DIR__);
        $paths = array_merge([$root . '/extension.xml'], array_map(
            static fn (string $path): string => $root . '/' . $path, array_values(releaseModulePaths())
        ));
        $originals = [];
        foreach ($paths as $path) {
            $content = file_get_contents($path);
            if ($content === false || !is_writable($path)) {
                throw new RuntimeException('Cannot read and write ' . $path);
            }
            $originals[] = $content;
        }
        $updated = prepareReleaseContents($originals[0], array_combine(array_keys(releaseModulePaths()), array_slice($originals, 1)), $argv[1], $date);
        try {
            foreach ($paths as $index => $path) {
                if (file_put_contents($path, $updated[$index], LOCK_EX) !== strlen($updated[$index])) {
                    throw new RuntimeException('Cannot write ' . $path);
                }
            }
        } catch (Throwable $error) {
            foreach ($paths as $index => $path) {
                if (file_put_contents($path, $originals[$index], LOCK_EX) !== strlen($originals[$index])) {
                    fwrite(STDERR, "Could not restore $path; inspect it manually.\n");
                }
            }
            throw $error;
        }
        echo "Prepared version {$argv[1]} with release date $date. Running tests...\n";
        passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . '/tests/run.php'), $status);
        if ($status !== 0) {
            fwrite(STDERR, "Tests failed. Version edits are retained for inspection; do not publish yet.\n");
        }
        exit($status);
    } catch (Throwable $error) {
        fwrite(STDERR, $error->getMessage() . "\n");
        exit(1);
    }
}
