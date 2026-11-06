<?php

declare(strict_types=1);

namespace Combodo\iTop\Service\Events {
    class EventData {}
}

namespace {
    // Only the object operations used by the real PowerSocket methods.
    class cmdbAbstractObject
    {
        public array $issues = [];
        public int $writes = 0;
        public function __construct(public array $values, private int $id = 7) {}
        public function Get(string $key): mixed { return $this->values[$key] ?? 0; }
        public function Set(string $key, mixed $value): void { $this->values[$key] = $value; }
        public function GetKey(): int { return $this->id; }
        public function ListChanges(): array { return ['datacenterdevice_id' => true]; }
        public function AddCheckIssue(string $issue): void { $this->issues[] = $issue; }
        public function DBUpdate(): void { $this->writes++; }
        public function SetSessionMessageFromInstance(...$arguments): void {}
    }

    require_once __DIR__ . '/../br-power-infrastructure/src/Model/_PowerSocket.php';

    $tests = [];
    foreach (['A' => [0, 0], 'B' => [99, 0]] as $slot => [$a, $b]) {
        $tests["assign socket to first free slot $slot and disconnect it"] = static function () use ($slot, $a, $b): void {
            $device = new cmdbAbstractObject(['powerAsocket_id' => $a, 'powerBsocket_id' => $b]);
            MetaModel::$devices = [1 => $device];
            $socket = new BR\Extension\PowerInfrastructure\Model\_PowerSocket(['pdu_id' => 42]);
            $socket->ConnectPowerSocket(1);
            if ($device->Get("power{$slot}socket_id") !== 7 || $device->Get("power{$slot}_id") !== 42) {
                throw new RuntimeException('Socket or matching PDU was not assigned.');
            }
            $socket->ConnectPowerSocket(1);
            if ($device->writes !== 1) {
                throw new RuntimeException('Repeated connection wrote the device again.');
            }
            $socket->DisconnectPowerSocket(1);
            if ($device->Get("power{$slot}socket_id") !== 0 || $device->Get("power{$slot}_id") !== 0
                || ($slot === 'B' && $device->Get('powerAsocket_id') !== 99)) {
                throw new RuntimeException('Disconnect left references or changed the other slot.');
            }
        };
    }
    foreach (['NoFreeSocketOnDatacenterDevice', 'SocketTypeMismatch'] as $error) {
        $tests["block socket assignment: $error"] = static function () use ($error): void {
            MetaModel::$devices = [1 => new cmdbAbstractObject([
                'powerAsocket_id' => 98, 'powerBsocket_id' => 99,
                'required_socket_type_id' => $error === 'SocketTypeMismatch' ? 2 : 0,
            ])];
            $socket = new BR\Extension\PowerInfrastructure\Model\_PowerSocket([
                'datacenterdevice_id' => 1, 'socket_type_id' => 1,
            ]);
            $socket->OnPowerSocketCheckToWrite(new Combodo\iTop\Service\Events\EventData());
            if ($socket->issues !== ['Class:PowerSocket/Error:' . $error]) {
                throw new RuntimeException('Expected assignment rejection was not reported.');
            }
        };
    }
    $tests['roll back assignment when device has no free slot'] = static function (): void {
        $device = new cmdbAbstractObject(['powerAsocket_id' => 98, 'powerBsocket_id' => 99]);
        MetaModel::$devices = [1 => $device];
        $socket = new BR\Extension\PowerInfrastructure\Model\_PowerSocket(['datacenterdevice_id' => 1]);
        $socket->ConnectPowerSocket(1);
        if ($socket->Get('datacenterdevice_id') !== 0 || $socket->writes !== 1 || $device->writes !== 0) {
            throw new RuntimeException('Full device assignment was not rolled back correctly.');
        }
    };

    return $tests;
}
