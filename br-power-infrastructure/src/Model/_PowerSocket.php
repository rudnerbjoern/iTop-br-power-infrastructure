<?php

/**
 * PowerSocket extension: automatic synchronization between PowerSocket and DatacenterDevice power slots.
 *
 * This class implements iTop event listeners for the PowerSocket class. It ensures that when a PowerSocket is
 * linked to a DatacenterDevice (via datacenterdevice_id), the DatacenterDevice is updated accordingly by placing
 * the socket into the first free slot (powerAsocket_id / powerBsocket_id) and setting the matching power source
 * (powerA_id / powerB_id) to the PDU of this PowerSocket.
 *
 * It also keeps the relation consistent on updates and deletes:
 *  - If the PowerSocket is moved to another DatacenterDevice, the old DatacenterDevice is disconnected first.
 *  - If the DatacenterDevice has no free slots, the assignment is rolled back and the user gets a session message.
 *
 * Notes:
 *  - ExternalKey "empty" values are represented by 0 (not an empty string).
 *  - PHP 8.2+ dynamic properties are avoided by declaring $bDatacenterDeviceChanged explicitly.
 *
 * @copyright   Copyright (C) 2022-2026 Björn Rudner
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version     2026-04-09
 */

namespace BR\Extension\PowerInfrastructure\Model;

use Combodo\iTop\Service\Events\EventData;
use cmdbAbstractObject;
use MetaModel;
use Dict;

class _PowerSocket extends cmdbAbstractObject
{

    /**
     * iTop class name for the DatacenterDevice. Kept as a constant to avoid string duplication.
     */
    private const CLASS_DC_DEVICE = 'DatacenterDevice';

    /**
     * In-memory flag used across BEFORE_WRITE / AFTER_WRITE to detect whether datacenterdevice_id changed on update.
     * Required to keep the logic idempotent and to avoid unnecessary reconnects.
     */
    private bool $bDatacenterDeviceChanged = false;

    /**
     * Loads a DatacenterDevice by its id.
     *
     * @param int $datacenterdevice_id The DatacenterDevice id (0 means "no device").
     *
     * @return cmdbAbstractObject|null The DatacenterDevice object if it exists and is accessible, otherwise null.
     */
    private function LoadDatacenterDevice(int $datacenterdevice_id): ?cmdbAbstractObject
    {
        if ($datacenterdevice_id === 0) {
            return null;
        }

        // MetaModel::GetObject(..., false) returns either an object or false if not found / not accessible.
        $o = MetaModel::GetObject(self::CLASS_DC_DEVICE, $datacenterdevice_id, false);

        return ($o instanceof cmdbAbstractObject) ? $o : null;
    }

    /**
     * EVENT_DB_CHECK_TO_WRITE listener.
     *
     * Purpose:
     *  - Prevent linking a PowerSocket to a DatacenterDevice that already has both power slots occupied,
     *    unless this PowerSocket is already one of the connected sockets (idempotent update).
     *
     * Behavior:
     *  - Adds a check issue (blocking save) if no free slot is available on the target DatacenterDevice.
     */
    public function OnPowerSocketCheckToWrite(EventData $oEventData): void
    {
        // This method will block the PowerSocket modification by adding a Check Issue

        // Only validate when the datacenterdevice_id attribute is being modified.
        $aChanges = $this->ListChanges();
        if (!array_key_exists('datacenterdevice_id', $aChanges)) {
            return;
        }

        $datacenterdevice_id = (int) $this->Get('datacenterdevice_id');
        if ($datacenterdevice_id === 0) {
            return; // Not linking to a device -> nothing to validate.
        }

        $oDatacenterDeviceObject = $this->LoadDatacenterDevice($datacenterdevice_id);
        if ($oDatacenterDeviceObject === null) {
            return; // Target device not found / not accessible -> let iTop handle the rest.
        }

        // --- Socket type compatibility check (PowerSocket -> DatacenterDevice) ---
        $requiredSocketTypeId = (int) $oDatacenterDeviceObject->Get('required_socket_type_id');
        $thisSocketTypeId     = (int) $this->Get('socket_type_id');

        // If the DatacenterDevice enforces a socket type, the PowerSocket must match it
        if ($requiredSocketTypeId !== 0 && $thisSocketTypeId !== $requiredSocketTypeId) {
            $this->AddCheckIssue(Dict::S('Class:PowerSocket/Error:SocketTypeMismatch'));
            return;
        }

        $powerA = (int) $oDatacenterDeviceObject->Get('powerAsocket_id');
        $powerB = (int) $oDatacenterDeviceObject->Get('powerBsocket_id');
        $thisSocketId  = (int) $this->GetKey();

        // Both slots are already occupied and this PowerSocket is neither of them -> block.
        if (
            $powerA !== 0 && $powerB !== 0 &&
            $powerA !== $thisSocketId  &&
            $powerB !== $thisSocketId
        ) {
            $this->AddCheckIssue(Dict::S('Class:PowerSocket/Error:NoFreeSocketOnDatacenterDevice'));
        }
    }

    /**
     * EVENT_DB_BEFORE_WRITE listener.
     *
     * Purpose:
     *  - On update: if datacenterdevice_id changes, disconnect this PowerSocket from the previous DatacenterDevice
     *    before the new link is written.
     *
     * Why a flag:
     *  - We store $bDatacenterDeviceChanged so the AFTER_WRITE handler knows whether it needs to connect.
     */
    public function OnPowerSocketBeforeWrite(EventData $oEventData): void
    {
        if ($oEventData->Get('is_new') === true) {
            return; // Insert: no previous DatacenterDevice to disconnect from.
        }

        $aChanges = $this->ListChanges();
        if (!array_key_exists('datacenterdevice_id', $aChanges)) {
            return; // No change -> nothing to do.
        }

        // Record state for AFTER_WRITE.
        $this->bDatacenterDeviceChanged = true;

        // Disconnect the old DatacenterDevice (if any).
        $oldDatacenterdeviceId = (int) $this->GetOriginal('datacenterdevice_id');
        if ($oldDatacenterdeviceId != 0) {
            $this->DisconnectPowerSocket($oldDatacenterdeviceId);
        }
    }

    /**
     * EVENT_DB_AFTER_WRITE listener.
     *
     * Purpose:
     *  - After insert: connect the PowerSocket to the selected DatacenterDevice (if set).
     *  - After update: connect only if datacenterdevice_id was changed (flag from BEFORE_WRITE).
     *
     * Note:
     *  - The flag is reset at the end to keep object state clean for subsequent operations.
     */
    public function OnPowerSocketAfterWrite(EventData $oEventData): void
    {
        $datacenterdevice_id = (int) $this->Get('datacenterdevice_id');
        if ($datacenterdevice_id === 0) {
            return; // No target -> nothing to connect.
        }

        if ($oEventData->Get('is_new') === true) {
            // AfterInsert: always connect if a target exists.
            $this->ConnectPowerSocket($datacenterdevice_id);
            $this->bDatacenterDeviceChanged = false;
            return;
        }

        // AfterUpdate: connect only if the field changed.
        if ($this->bDatacenterDeviceChanged) {
            $this->ConnectPowerSocket($datacenterdevice_id);
        }

        // Reset in-memory flag.
        $this->bDatacenterDeviceChanged = false;
    }

    /**
     * EVENT_DB_ABOUT_TO_DELETE listener.
     *
     * Purpose:
     *  - Before deleting a PowerSocket, remove the reference from the connected DatacenterDevice slot (A or B).
     *  - This keeps DatacenterDevice consistent and avoids dangling references.
     */
    public function OnPowerSocketAboutToDelete(EventData $oEventData): void
    {
        $datacenterdevice_id = (int) $this->Get('datacenterdevice_id');
        if ($datacenterdevice_id !== 0) {
            $this->DisconnectPowerSocket($datacenterdevice_id);
        }
    }

    /**
     * Connects this PowerSocket to a DatacenterDevice.
     *
     * Why this exists:
     *  - A PowerSocket can be linked to a DatacenterDevice through PowerSocket::datacenterdevice_id.
     *  - The DatacenterDevice represents the actual occupied power slots via:
     *      - DatacenterDevice::powerAsocket_id / powerA_id (PDU of the A socket)
     *      - DatacenterDevice::powerBsocket_id / powerB_id (PDU of the B socket)
     *
     * This method performs the "reverse update" on the DatacenterDevice:
     *  - It places this PowerSocket into the first free slot (A preferred, then B),
     *    and sets the corresponding PDU reference (powerA_id or powerB_id) to this socket's PDU.
     *
     * Strategy:
     *  1) Guard clauses:
     *     - Ignore empty target id (0).
     *     - Ignore missing/unreadable DatacenterDevice.
     *
     *  2) Idempotency:
     *     - If this socket is already referenced in slot A or slot B, do nothing.
     *       (This prevents repeated DB updates on re-save or concurrent event calls.)
     *
     *  3) First-free-slot assignment:
     *     - If slot A is free -> assign A (powerAsocket_id + powerA_id).
     *     - Else if slot B is free -> assign B (powerBsocket_id + powerB_id).
     *
     *  4) Rollback when full:
     *     - If both slots are occupied (and neither is this socket), we cannot connect.
     *     - Because this method is typically called from EVENT_DB_AFTER_WRITE,
     *       we must actively persist the rollback by updating PowerSocket::datacenterdevice_id back to 0.
     *     - A user-facing session message is added to explain why the link was removed.
     *
     * Safety / idempotency:
     *  - Safe to call multiple times; once connected, subsequent calls become a no-op.
     *  - Writes to the DatacenterDevice occur only when a slot is actually assigned.
     *  - Rollback writes to the PowerSocket occur only when the device is full.
     *
     * Implementation notes:
     *  - ExternalKey “empty” is represented by integer 0.
     *  - This method assumes the DatacenterDevice power slots are the authoritative representation
     *    of which sockets are in use (A/B). The PowerSocket's datacenterdevice_id is treated as the user's intent.
     *
     * @param int $datacenterdevice_id The target DatacenterDevice id (0 means “no device”).
     */
    public function ConnectPowerSocket(int $datacenterdevice_id): void
    {
        if ($datacenterdevice_id === 0) {
            return;
        }

        $oDatacenterDeviceObject = $this->LoadDatacenterDevice($datacenterdevice_id);
        if ($oDatacenterDeviceObject === null) {
            return;
        }

        $thisSocketId = (int) $this->GetKey();

        // Read both slots once (avoid repeated Get() calls and keep logic readable).
        $powerA = (int) $oDatacenterDeviceObject->Get('powerAsocket_id');
        $powerB = (int) $oDatacenterDeviceObject->Get('powerBsocket_id');

        // Already connected -> nothing to do (idempotent).
        if ($powerA === $thisSocketId || $powerB === $thisSocketId) {
            return;
        }

        $pduId = (int) $this->Get('pdu_id');

        // Slot A is free -> connect to A.
        if ($powerA === 0) {
            $oDatacenterDeviceObject->Set('powerA_id', $pduId);
            $oDatacenterDeviceObject->Set('powerAsocket_id', $thisSocketId);
            $oDatacenterDeviceObject->DBUpdate();
            return;
        }

        // Slot B is free -> connect to B.
        if ($powerB === 0) {
            $oDatacenterDeviceObject->Set('powerB_id', $pduId);
            $oDatacenterDeviceObject->Set('powerBsocket_id', $thisSocketId);
            $oDatacenterDeviceObject->DBUpdate();
            return;
        }

        // No free slot -> rollback the PowerSocket's link and inform the user.
        // Note: This method is called from AFTER_WRITE, so we must persist the rollback with DBUpdate().
        $this->Set('datacenterdevice_id', 0);
        $this->DBUpdate();

        $this->SetSessionMessageFromInstance('powersocket-1', Dict::S('Class:PowerSocket/Message:NoFreeSocketOnDatacenterDevice'), 'error', 1);
    }

    /**
     * Disconnects this PowerSocket from a DatacenterDevice.
     *
     * Why this exists:
     *  - A PowerSocket keeps a link to a DatacenterDevice via PowerSocket::datacenterdevice_id.
     *  - The DatacenterDevice stores the actual “occupied slot” in either:
     *      - DatacenterDevice::powerAsocket_id (+ powerA_id = PDU), or
     *      - DatacenterDevice::powerBsocket_id (+ powerB_id = PDU).
     *
     * This method removes the back-reference on the DatacenterDevice side when this PowerSocket is no longer
     * associated with that device (e.g., PowerSocket moved to another device or deleted).
     *
     * Behavior:
     *  - If the DatacenterDevice references this PowerSocket in slot A, clear powerAsocket_id and powerA_id.
     *  - Else if it references this PowerSocket in slot B, clear powerBsocket_id and powerB_id.
     *  - If the DatacenterDevice does not reference this PowerSocket at all, do nothing.
     *
     * Safety / idempotency:
     *  - The method is safe to call multiple times; after the first successful disconnect it becomes a no-op.
     *  - No DB write occurs unless a slot actually needs to be cleared.
     *
     * Implementation notes:
     *  - ExternalKey “empty” is represented by integer 0.
     *  - We only clear the slot that matches this socket's id; we never modify other sockets.
     *
     * @param int $datacenterdevice_id The DatacenterDevice id (0 means “no device”).
     */
    public function DisconnectPowerSocket(int $datacenterdevice_id): void
    {
        if ($datacenterdevice_id === 0) {
            return;
        }

        $oDatacenterDeviceObject = $this->LoadDatacenterDevice($datacenterdevice_id);
        if ($oDatacenterDeviceObject === null) {
            return;
        }

        $thisSocketId = (int) $this->GetKey();

        // Read both slots once (avoid repeated Get() calls and keep logic readable).
        $powerA = (int) $oDatacenterDeviceObject->Get('powerAsocket_id');
        $powerB = (int) $oDatacenterDeviceObject->Get('powerBsocket_id');

        // Nothing to do if neither slot references this socket.
        if ($powerA !== $thisSocketId && $powerB !== $thisSocketId) {
            return;
        }

        // Clear slot A if it references this socket.
        if ($powerA === $thisSocketId) {
            $oDatacenterDeviceObject->Set('powerA_id', 0);
            $oDatacenterDeviceObject->Set('powerAsocket_id', 0);
            $oDatacenterDeviceObject->DBUpdate();
            return;
        }

        // Clear slot B if it references this socket.
        if ($powerB === $thisSocketId) {
            $oDatacenterDeviceObject->Set('powerB_id', 0);
            $oDatacenterDeviceObject->Set('powerBsocket_id', 0);
            $oDatacenterDeviceObject->DBUpdate();
            return;
        }

        // Not referenced -> nothing to do.
    }
}
