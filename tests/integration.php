<?php

declare(strict_types=1);

if (getenv('ITOP_TEST_MODE') !== '1') {
    throw new RuntimeException('Run in a disposable iTop test environment.');
}

require '/var/www/html/approot.inc.php';
require_once APPROOT . 'core/config.class.inc.php';
$config = new Config(APPROOT . 'conf/production/config-itop.php');
$database = [$config->Get('db_host'), $config->Get('db_name'), $config->Get('db_user')];
if (!in_array($database, [['db', 'itop_power_fixture', 'itop'], ['test-db', 'itop_test', 'itop_test']], true)) {
    throw new RuntimeException('Refusing to use a database outside the isolated test fixtures.');
}
require APPROOT . 'application/startup.inc.php';

function check(bool $expected, string $message): void
{
    if (!$expected) {
        throw new RuntimeException($message);
    }
}

function create(string $class, array $values): DBObject
{
    $object = MetaModel::NewObject($class, $values);
    $object->DBInsert();
    return $object;
}

function socketOwner(DBObject $socket): int
{
    return (int) MetaModel::GetObject('PowerSocket', $socket->GetKey())->Get('datacenterdevice_id');
}

function verifyInstallerMigration(Config $config): void
{
    require_once APPROOT . 'setup/moduleinstaller.class.inc.php';
    require_once APPROOT . 'setup/modulediscovery.class.inc.php';
    $reader = Combodo\iTop\Setup\ModuleDiscovery\ModuleFileReader::GetInstance();
    $definition = $reader->ReadModuleFileInformation(dirname(__DIR__)
        . '/br-power-infrastructure/module.br-power-infrastructure.php');
    $installer = $reader->GetAndCheckModuleInstallerClass($definition[$reader::MODULE_INFO_CONFIG]);
    check($installer === PowerInfrastructureInstaller::class, 'Native setup did not load the module installer.');

    $class = 'lnkPowerConnectionToPowerConnection';
    $table = MetaModel::DBGetTable($class);
    check(preg_match('/^[a-zA-Z0-9_]+$/D', $table) === 1, 'Unexpected fixture table name.');
    $schema = CMDBSource::QueryToArray("SHOW COLUMNS FROM " . chr(96) . $table . chr(96) . " WHERE Field = 'link_role'", MYSQLI_ASSOC)[0];
    $type = $schema['Type'];
    check(preg_match("/^enum\('[a-z_]+'(?:,'[a-z_]+')*\)$/iD", $type) === 1
        && $schema['Default'] === 'downstream' && !str_contains($type, "'output'"), 'Unexpected fixture role schema.');
    $column = "ALTER TABLE " . chr(96) . $table . chr(96) . " MODIFY link_role ";
    $spec = ($schema['Null'] === 'YES' ? ' NULL' : ' NOT NULL') . " DEFAULT 'downstream'";
    // Model the post-schema state of a legacy upgrade, which retains the old enum value.
    CMDBSource::Query($column . substr($type, 0, -1) . ",'output')" . $spec);
    CMDBObject::SetCurrentChange(null);
    CMDBSource::Query('START TRANSACTION');
    try {
        $prefix = 'Installer fixture ' . bin2hex(random_bytes(4));
        $organization = create('Organization', ['name' => $prefix, 'status' => 'active']);
        $common = ['org_id' => $organization->GetKey()];
        $location = create('Location', $common + ['name' => "$prefix location"]);
        $rack = create('Rack', $common + ['name' => "$prefix rack", 'location_id' => $location->GetKey()]);
        $source = create('PowerSource', $common + ['name' => "$prefix source"]);
        $pdu = create('PDU', $common + ['name' => "$prefix duplicate", 'rack_id' => $rack->GetKey()]);
        $pduToMigrate = create('PDU', $common + ['name' => "$prefix role", 'rack_id' => $rack->GetKey()]);
        $pduToImport = create('PDU', $common + [
            'name' => "$prefix import", 'rack_id' => $rack->GetKey(), 'powerstart_id' => $source->GetKey(),
        ]);
        $link = [
            'source_powerconnection_id' => $source->GetKey(),
            'target_powerconnection_id' => $pdu->GetKey(),
        ];
        $downstream = create($class, $link + ['link_role' => 'downstream']);
        $duplicate = create($class, $link + ['link_role' => 'other']);
        $link['target_powerconnection_id'] = $pduToMigrate->GetKey();
        $legacy = create($class, $link + ['link_role' => 'other']);
        $legacyIds = (int) $duplicate->GetKey() . ',' . (int) $legacy->GetKey();
        CMDBSource::Query("UPDATE " . chr(96) . $table . chr(96) . " SET link_role = 'output' WHERE id IN ($legacyIds)");

        $installer::AfterDatabaseCreation($config, '', '3.3.0');
        check(MetaModel::GetObject($class, $legacy->GetKey())->Get('link_role') === 'output', 'Fresh setup modified a legacy fixture.');

        $installer::AfterDatabaseCreation($config, '1.9.0', '3.3.0');
        $installer::AfterDatabaseCreation($config, '1.9.0', '3.3.0');
        check(MetaModel::GetObject($class, $downstream->GetKey())->Get('link_role') === 'downstream'
            && !MetaModel::GetObject($class, $duplicate->GetKey(), false), 'The installer did not remove the duplicate legacy path.');
        check(MetaModel::GetObject($class, $legacy->GetKey())->Get('link_role') === 'downstream', 'The installer did not rename the legacy role.');
        $imported = new DBObjectSet(DBObjectSearch::FromOQL(
            "SELECT $class WHERE source_powerconnection_id = :source AND target_powerconnection_id = :target AND link_role = 'downstream'",
            ['source' => $source->GetKey(), 'target' => $pduToImport->GetKey()]
        ));
        check($imported->Count() === 1, 'Legacy PDU link import was missing or not idempotent.');
    } finally {
        CMDBSource::Query('ROLLBACK');
        CMDBSource::Query($column . $type . $spec);
    }
    echo "Installer loaded through native setup metadata, skipped fresh migration, and migrated legacy links idempotently.\n";
}

$adminPassword = getenv('ITOP_TEST_ADMIN_PASSWORD') ?: getenv('ITOP_ADMIN_PASSWORD');
check(is_string($adminPassword) && UserRights::CheckCredentials('admin', $adminPassword)
    && UserRights::Login('admin') && UserRights::IsAdministrator(), 'Could not authenticate the test administrator.');

$withTeemIp = in_array('--teemip', $argv, true);
$modules = GetModulesInfo();
check(isset($modules['br-power-infrastructure']), 'The power infrastructure module was not installed.');
check(isset($modules['br-power-infrastructure-bridge-for-teemip-ip-mgmt']) === $withTeemIp, 'Unexpected TeemIP bridge selection.');
$withDatacenterView = in_array('--datacenter-view', $argv, true);
check(isset($modules['br-power-infrastructure-bridge-for-datacenter-view']) === $withDatacenterView, 'Unexpected Datacenter View bridge selection.');
check(!isset($modules['br-power-infrastructure-bridge-for-datacenter-view-extended']), 'Unexpected Datacenter View Extended bridge.');

$layout = [
    'PowerSource' => ['name', 'model_end_of_support', 'groups_list', 'phase_type', 'last_maintenance_date', 'pdus_list', 'incoming_powerconnection_links', 'outgoing_powerconnection_links'],
    'PDU' => ['name', 'model_end_of_support', 'groups_list', 'rack_id', 'phase_type', 'capacity', 'powerstart_id', 'last_maintenance_date', 'powersocket_list', 'outgoing_powerconnection_links'],
    'Server' => ['powerA_id', 'powerB_id', 'powerAsocket_id', 'powerBsocket_id'],
    'NetworkDevice' => ['powerA_id', 'powerB_id', 'powerAsocket_id', 'powerBsocket_id'],
];
if ($withTeemIp) {
    $layout['PDU'][] = 'macaddress';
    $layout['PDU'][] = 'ipaddress_id';
    check(MetaModel::GetAttributeDef('PDU', 'macaddress') instanceof AttributeMacAddress
        && MetaModel::GetAttributeDef('PDU', 'ipaddress_id')->GetTargetClass() === 'IPAddress', 'Unexpected TeemIP PDU field definitions.');
}
if ($withDatacenterView) {
    $rackFields = ['rack_id', 'enclosure_id', 'position_v', 'nb_u'];
    $baseInfo = MetaModel::GetZListItems('PDU', 'details')['col:col1']['fieldset:ConfigMgmt:baseinfo'] ?? [];
    check(array_values(array_intersect($baseInfo, $rackFields)) === $rackFields, 'Unexpected PDU rack field placement or order.');
    array_push($layout['PDU'], 'enclosure_id', 'position_v', 'nb_u');
}
foreach ($layout as $class => $fields) {
    $details = MetaModel::FlattenZList(MetaModel::GetZListItems($class, 'details'));
    foreach ($fields as $field) {
        check(count(array_keys($details, $field, true)) === 1, "$class must display $field exactly once.");
    }
}

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (str_contains($message, 'bPowerSocketAChanged') || str_contains($message, 'bPowerSocketBChanged')) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
    return false;
}, E_ALL);

CMDBSource::Query('START TRANSACTION');
try {
    $prefix = 'Power fixture ' . bin2hex(random_bytes(4));
    $organization = create('Organization', ['name' => $prefix, 'status' => 'active']);
    $common = ['org_id' => $organization->GetKey()];
    $location = create('Location', $common + ['name' => "$prefix location"]);
    $rack = create('Rack', $common + ['name' => "$prefix rack", 'location_id' => $location->GetKey()]);
    $pduValues = $common + ['name' => "$prefix PDU", 'capacity' => 6, 'rack_id' => $rack->GetKey()];
    if ($withTeemIp) {
        $ip = create('IPv4Address', $common + ['ip' => '192.0.2.23', 'status' => 'unassigned']);
        $pduValues += ['macaddress' => '02-00-00-00-00-23', 'ipaddress_id' => $ip->GetKey()];
    }
    $pdu = create('PDU', $pduValues);
    if ($withTeemIp) {
        $saved = MetaModel::GetObject('PDU', $pdu->GetKey());
        check($saved->Get('macaddress') === '02:00:00:00:00:23'
            && (int) $saved->Get('ipaddress_id') === (int) $ip->GetKey(), 'PDU MAC or IP assignment did not persist.');
    }
    foreach (['Server', 'NetworkDevice'] as $class) {
        $sockets = [];
        foreach (['A', 'B', 'replacement'] as $label) {
            $sockets[$label] = create('PowerSocket', [
                'name' => "$prefix $class $label", 'pdu_id' => $pdu->GetKey(),
            ]);
        }
        $deviceValues = $common;
        if ($class === 'NetworkDevice') {
            $type = create('NetworkDeviceType', ['name' => "$prefix network type"]);
            $deviceValues['networkdevicetype_id'] = $type->GetKey();
        }
        $device = create($class, $deviceValues + [
            'name' => "$prefix $class", 'powerA_id' => $pdu->GetKey(), 'powerB_id' => $pdu->GetKey(),
            'powerAsocket_id' => $sockets['A']->GetKey(), 'powerBsocket_id' => $sockets['B']->GetKey(),
        ]);
        $id = (int) $device->GetKey();
        check(socketOwner($sockets['A']) === $id && socketOwner($sockets['B']) === $id, "$class failed to connect both sockets on insert.");

        $freshDevice = MetaModel::GetObject($class, $id);
        $freshDevice->Set('description', 'An unrelated update');
        $freshDevice->DBUpdate();
        check(socketOwner($sockets['A']) === $id && socketOwner($sockets['B']) === $id, "$class changed the sockets on an unrelated update.");

        $freshDevice->Set('powerAsocket_id', $sockets['replacement']->GetKey());
        $freshDevice->DBUpdate();
        check(socketOwner($sockets['A']) === 0 && socketOwner($sockets['replacement']) === $id
            && socketOwner($sockets['B']) === $id, "$class failed to replace only slot A.");

        $freshDevice->Set('powerBsocket_id', $sockets['A']->GetKey());
        $freshDevice->DBUpdate();
        check(socketOwner($sockets['B']) === 0 && socketOwner($sockets['A']) === $id
            && socketOwner($sockets['replacement']) === $id, "$class failed to replace only slot B.");

        $freshDevice->DBDelete();
        check(socketOwner($sockets['A']) === 0 && socketOwner($sockets['replacement']) === 0, "$class left sockets assigned after delete.");
    }
} finally {
    CMDBSource::Query('ROLLBACK');
    restore_error_handler();
}

echo "Compiled layouts, automatic module selection and A/B socket persistence passed.\n";

verifyInstallerMigration($config);
