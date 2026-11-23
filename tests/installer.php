<?php

declare(strict_types=1);

class Config {}

class SetupWebPage
{
    public static array $module = [];

    public static function AddModule(string $file, string $id, array $definition): void
    {
        self::$module = $definition;
    }
}

class ModuleInstallerAPI
{
    public static function BeforeDatabaseCreation(Config $config, $previous, $current): void {}

    public static function IsVersionCrossed(string $previous, string $current, string $threshold): bool
    {
        return $previous === '' || (version_compare($previous, $current, '<')
            && version_compare($previous, $threshold, '<') && version_compare($threshold, $current, '<='));
    }
}

class SetupLog
{
    public static array $messages = [];

    public static function Info(string $message): void
    {
        self::$messages[] = $message;
    }
}

require __DIR__ . '/../br-power-infrastructure/module.br-power-infrastructure.php';

$tests = [
    'setup registers the power installer and preserves its configuration' => static function (): void {
        $config = new Config();
        if (SetupWebPage::$module['installer'] !== PowerInfrastructureInstaller::class
            || PowerInfrastructureInstaller::BeforeWritingConfig($config) !== $config) {
            throw new RuntimeException('Incorrect power infrastructure installer registration.');
        }
    },
];

foreach ([
    'fresh install' => ['', '3.3.0', false],
    'upgrade below the migration threshold' => ['1.5.0', '1.9.0', false],
    'upgrade crossing the threshold' => ['1.9.0', '2.0.0', true],
    'direct legacy upgrade to iTop 3.3' => ['1.9.0', '3.3.0', true],
    'upgrade from 2.0' => ['2.0.0', '3.3.0', false],
    'upgrade from 3.2' => ['3.2.4', '3.3.0', false],
    'repeat setup' => ['3.3.0', '3.3.0', false],
] as $scenario => [$previous, $current, $expected]) {
    $tests["installer migration gates: $scenario"] = static function () use ($previous, $current, $expected): void {
        SetupLog::$messages = [];
        PowerInfrastructureInstaller::BeforeDatabaseCreation(new Config(), $previous, $current);
        if (SetupLog::$messages !== []) {
            throw new RuntimeException('Migration must wait until the schema supports the downstream role.');
        }
        PowerInfrastructureInstaller::AfterDatabaseCreation(new Config(), $previous, $current);
        if (count(SetupLog::$messages) !== ($expected ? 3 : 0)) {
            throw new RuntimeException('Legacy migrations must run only after crossing their version threshold.');
        }
    };
}

return $tests;
