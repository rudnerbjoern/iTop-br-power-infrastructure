<?php

declare(strict_types=1);

// Minimal substitutes for the iTop APIs used by the extracted PDU method.
class Dict
{
    public static function S(string $key): string { return $key; }
}

class TestSocket
{
    public array $values = [];
    public function Set(string $key, mixed $value): void { $this->values[$key] = $value; }
    public function DBInsert(): void { MetaModel::$sockets[] = $this->values; }
}

class MetaModel
{
    public static array $sockets = [];
    public static array $devices = [];
    public static function GetObject(string $class, int $id, bool $mustExist = true): mixed
    {
        if ($class !== 'DatacenterDevice') {
            throw new RuntimeException('Unexpected class: ' . $class);
        }
        return self::$devices[$id] ?? false;
    }
    public static function NewObject(string $class): TestSocket
    {
        if ($class !== 'PowerSocket') {
            throw new RuntimeException('Unexpected class: ' . $class);
        }
        return new TestSocket();
    }
}

class TestSocketSet
{
    public function Count(): int { return count(MetaModel::$sockets); }
    public function GetColumnAsArray(string $key): array { return array_column(MetaModel::$sockets, $key); }
}

class TestPdu
{
    use PowerSocketCreationUnderTest;
    public function __construct(private ?int $capacity) {}
    public function GetKey(): int { return 42; }
    public function Get(string $key): mixed
    {
        return match ($key) {
            'capacity' => $this->capacity,
            'powersocket_list' => new TestSocketSet(),
            default => throw new RuntimeException('Unexpected attribute: ' . $key),
        };
    }
}

$tests = [];
foreach (['missing' => null, 'zero' => 0, 'negative' => -1] as $name => $capacity) {
    $tests["reject $name PDU capacity"] = static function () use ($capacity): void {
        MetaModel::$sockets = [];
        if ((new TestPdu($capacity))->CreatePowerSockets() === '' || MetaModel::$sockets !== []) {
            throw new RuntimeException('Invalid capacity created sockets or reported success.');
        }
    };
}
$tests['create sockets up to capacity and reject repeated creation'] = static function (): void {
    MetaModel::$sockets = [];
    $pdu = new TestPdu(10);
    if ($pdu->CreatePowerSockets() !== '' || count(MetaModel::$sockets) !== 10) {
        throw new RuntimeException('Expected ten created sockets.');
    }
    foreach (MetaModel::$sockets as $index => $socket) {
        if ($socket !== ['name' => sprintf('%02d/10', $index + 1), 'pdu_id' => 42, 'status' => 'active']) {
            throw new RuntimeException('Unexpected socket name, PDU or status.');
        }
    }
    if ($pdu->CreatePowerSockets() === '' || count(MetaModel::$sockets) !== 10) {
        throw new RuntimeException('Repeated creation exceeded capacity.');
    }
};
$tests['preserve existing sockets and skip colliding names'] = static function (): void {
    $existing = ['name' => '1/3', 'pdu_id' => 42, 'status' => 'inactive'];
    MetaModel::$sockets = [$existing];
    if ((new TestPdu(3))->CreatePowerSockets() !== ''
        || MetaModel::$sockets[0] !== $existing
        || array_column(MetaModel::$sockets, 'name') !== ['1/3', '2/3', '3/3']) {
        throw new RuntimeException('Existing socket changed or generated names collided.');
    }
};
$tests['reject capacity below the existing socket count'] = static function (): void {
    MetaModel::$sockets = [['name' => 'one'], ['name' => 'two']];
    $before = MetaModel::$sockets;
    if ((new TestPdu(1))->CreatePowerSockets() === '' || MetaModel::$sockets !== $before) {
        throw new RuntimeException('Exceeded capacity changed existing sockets.');
    }
};

return $tests;
