<?php

/**
 * @copyright   Copyright (C) 2026 Björn Rudner
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version     2026-04-08
 *
 * Localized data
 */

//
// Class: PowerConnection
//
/** @disregard P1009 Undefined type Dict */
Dict::Add('EN US', 'English', 'English', array(
    'Class:PowerConnection/Attribute:phase_type' => 'Phase type',
    'Class:PowerConnection/Attribute:phase_type+' => 'Electrical phase type of the power connection.',
    'Class:PowerConnection/Attribute:phase_type/Value:1ph' => 'Single-phase',
    'Class:PowerConnection/Attribute:phase_type/Value:3ph' => 'Three-phase',
    'Class:PowerConnection/Attribute:phase_type/Value:dc' => 'DC',
    'Class:PowerConnection/Attribute:phase_type/Value:other' => 'Other',

    'Class:PowerConnection/Attribute:nominal_voltage' => 'Nominal voltage (V)',
    'Class:PowerConnection/Attribute:nominal_voltage+' => 'Nominal input or output voltage of the power connection.',

    'Class:PowerConnection/Attribute:nominal_frequency' => 'Nominal frequency (Hz)',
    'Class:PowerConnection/Attribute:nominal_frequency+' => 'Nominal electrical frequency of the power connection.',
    'Class:PowerConnection/Attribute:nominal_frequency/Value:50' => '50',
    'Class:PowerConnection/Attribute:nominal_frequency/Value:60' => '60',
    'Class:PowerConnection/Attribute:nominal_frequency/Value:50_60' => '50/60',
    'Class:PowerConnection/Attribute:nominal_frequency/Value:other' => 'Other',

    'Class:PowerConnection/Attribute:max_current_ampere' => 'Maximum current (A)',
    'Class:PowerConnection/Attribute:max_current_ampere+' => 'Maximum supported or rated current of the power connection.',

    'Class:PowerConnection/Attribute:last_maintenance_date' => 'Last maintenance date',
    'Class:PowerConnection/Attribute:last_maintenance_date+' => 'Date of the last maintenance activity for this power connection.',
    'Class:PowerConnection/Attribute:next_maintenance_date' => 'Next maintenance date',
    'Class:PowerConnection/Attribute:next_maintenance_date+' => 'Planned date of the next maintenance activity for this power connection.',
));

//
// Class: UPS
//
/** @disregard P1009 Undefined type Dict */
Dict::Add('EN US', 'English', 'English', array(
    'Class:UPS' => 'UPS',
    'Class:UPS+' => 'Uninterruptible power supply',

    'Class:UPS/Attribute:ups_topology' => 'UPS topology',
    'Class:UPS/Attribute:ups_topology+' => 'Topology of the UPS system.',
    'Class:UPS/Attribute:ups_topology/Value:offline' => 'Offline',
    'Class:UPS/Attribute:ups_topology/Value:line_interactive' => 'Line-interactive',
    'Class:UPS/Attribute:ups_topology/Value:online' => 'Online',
    'Class:UPS/Attribute:ups_topology/Value:modular' => 'Modular',
    'Class:UPS/Attribute:ups_topology/Value:other' => 'Other',

    'Class:UPS/Attribute:rated_power_va' => 'Rated power (VA)',
    'Class:UPS/Attribute:rated_power_va+' => 'Rated apparent power of the UPS in volt-amperes.',

    'Class:UPS/Attribute:rated_power_watt' => 'Rated power (W)',
    'Class:UPS/Attribute:rated_power_watt+' => 'Rated active power of the UPS in watts.',

    'Class:UPS/Attribute:autonomy_time' => 'Autonomy time',
    'Class:UPS/Attribute:autonomy_time+' => 'Expected autonomy time of the UPS as a duration.',
));

//
// Class: UPSBattery
//
/** @disregard P1009 Undefined type Dict */
Dict::Add('EN US', 'English', 'English', array(
    'Class:UPSBattery' => 'UPS battery',
    'Class:UPSBattery+' => 'Battery unit assigned to an uninterruptible power supply',

    'Class:UPSBattery/Attribute:ups_id' => 'UPS',
    'Class:UPSBattery/Attribute:ups_id+' => 'UPS to which this battery unit belongs.',
    'Class:UPSBattery/Attribute:ups_name' => 'UPS name',

    'Class:UPS/Attribute:batteries_list' => 'Batteries',
    'Class:UPS/Attribute:batteries_list+' => 'Battery units assigned to this UPS.',

    'Class:UPSBattery/Attribute:battery_role' => 'Battery role',
    'Class:UPSBattery/Attribute:battery_role+' => 'Role of this battery unit within the UPS installation.',
    'Class:UPSBattery/Attribute:battery_role/Value:internal' => 'Internal',
    'Class:UPSBattery/Attribute:battery_role/Value:external' => 'External',
    'Class:UPSBattery/Attribute:battery_role/Value:expansion' => 'Expansion',
    'Class:UPSBattery/Attribute:battery_role/Value:replacement' => 'Replacement',
    'Class:UPSBattery/Attribute:battery_role/Value:other' => 'Other',

    'Class:UPSBattery/Attribute:battery_type' => 'Battery type',
    'Class:UPSBattery/Attribute:battery_type+' => 'Battery technology used by this battery unit.',
    'Class:UPSBattery/Attribute:battery_type/Value:vrla' => 'VRLA',
    'Class:UPSBattery/Attribute:battery_type/Value:agm' => 'AGM',
    'Class:UPSBattery/Attribute:battery_type/Value:gel' => 'Gel',
    'Class:UPSBattery/Attribute:battery_type/Value:li_ion' => 'Li-Ion',
    'Class:UPSBattery/Attribute:battery_type/Value:nicd' => 'NiCd',
    'Class:UPSBattery/Attribute:battery_type/Value:other' => 'Other',

    'Class:UPSBattery/Attribute:battery_status' => 'Battery status',
    'Class:UPSBattery/Attribute:battery_status+' => 'Current status of this battery unit.',
    'Class:UPSBattery/Attribute:battery_status/Value:ok' => 'OK',
    'Class:UPSBattery/Attribute:battery_status/Value:warning' => 'Warning',
    'Class:UPSBattery/Attribute:battery_status/Value:critical' => 'Critical',
    'Class:UPSBattery/Attribute:battery_status/Value:expired' => 'Expired',
    'Class:UPSBattery/Attribute:battery_status/Value:replaced' => 'Replaced',
    'Class:UPSBattery/Attribute:battery_status/Value:unknown' => 'Unknown',

    'Class:UPSBattery/Attribute:last_replacement_date' => 'Last replacement date',
    'Class:UPSBattery/Attribute:last_replacement_date+' => 'Date of the last replacement of this battery unit.',

    'Class:UPSBattery/Attribute:next_replacement_date' => 'Next replacement date',
    'Class:UPSBattery/Attribute:next_replacement_date+' => 'Planned date for the next replacement of this battery unit.',

    'Class:UPSBattery/Attribute:battery_voltage' => 'Battery voltage (V)',
    'Class:UPSBattery/Attribute:battery_voltage+' => 'Nominal battery voltage of this battery unit.',

    'Class:UPSBattery/Attribute:battery_capacity_ah' => 'Battery capacity (Ah)',
    'Class:UPSBattery/Attribute:battery_capacity_ah+' => 'Nominal battery capacity of this battery unit.',
));
