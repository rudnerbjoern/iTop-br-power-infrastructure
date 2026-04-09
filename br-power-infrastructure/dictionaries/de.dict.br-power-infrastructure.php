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
Dict::Add('DE DE', 'German', 'Deutsch', array(
    'Class:PowerConnection/Attribute:phase_type' => 'Phasentyp',
    'Class:PowerConnection/Attribute:phase_type+' => 'Elektrischer Phasentyp der Stromverbindung.',
    'Class:PowerConnection/Attribute:phase_type/Value:1ph' => 'Einphasig',
    'Class:PowerConnection/Attribute:phase_type/Value:3ph' => 'Dreiphasig',
    'Class:PowerConnection/Attribute:phase_type/Value:dc' => 'Gleichstrom',
    'Class:PowerConnection/Attribute:phase_type/Value:other' => 'Sonstige',

    'Class:PowerConnection/Attribute:nominal_voltage' => 'Nennspannung (V)',
    'Class:PowerConnection/Attribute:nominal_voltage+' => 'Nenneingangs- oder Nennausgangsspannung der Stromverbindung.',

    'Class:PowerConnection/Attribute:nominal_frequency' => 'Nennfrequenz (Hz)',
    'Class:PowerConnection/Attribute:nominal_frequency+' => 'Elektrische Nennfrequenz der Stromverbindung.',
    'Class:PowerConnection/Attribute:nominal_frequency/Value:50' => '50',
    'Class:PowerConnection/Attribute:nominal_frequency/Value:60' => '60',
    'Class:PowerConnection/Attribute:nominal_frequency/Value:50_60' => '50/60',
    'Class:PowerConnection/Attribute:nominal_frequency/Value:other' => 'Sonstige',

    'Class:PowerConnection/Attribute:max_current_ampere' => 'Maximalstrom (A)',
    'Class:PowerConnection/Attribute:max_current_ampere+' => 'Maximal unterstützter oder ausgelegter Strom der Stromverbindung.',

    'Class:PowerConnection/Attribute:last_maintenance_date' => 'Datum letzte Wartung',
    'Class:PowerConnection/Attribute:last_maintenance_date+' => 'Datum der letzten Wartung dieser Stromverbindung.',
    'Class:PowerConnection/Attribute:next_maintenance_date' => 'Datum nächste Wartung',
    'Class:PowerConnection/Attribute:next_maintenance_date+' => 'Geplantes Datum der nächsten Wartung dieser Stromverbindung.',
));

//
// Class: UPS
//
/** @disregard P1009 Undefined type Dict */
Dict::Add('DE DE', 'German', 'Deutsch', array(
    'Class:UPS' => 'USV',
    'Class:UPS+' => 'Unterbrechungsfreie Stromversorgung',

    'Class:UPS/Attribute:ups_topology' => 'USV-Topologie',
    'Class:UPS/Attribute:ups_topology+' => 'Topologie des USV-Systems.',
    'Class:UPS/Attribute:ups_topology/Value:offline' => 'Offline',
    'Class:UPS/Attribute:ups_topology/Value:line_interactive' => 'Line-Interactive',
    'Class:UPS/Attribute:ups_topology/Value:online' => 'Online',
    'Class:UPS/Attribute:ups_topology/Value:modular' => 'Modular',
    'Class:UPS/Attribute:ups_topology/Value:other' => 'Sonstige',

    'Class:UPS/Attribute:rated_power_va' => 'Nennleistung (VA)',
    'Class:UPS/Attribute:rated_power_va+' => 'Nennscheinleistung der USV in Voltampere.',

    'Class:UPS/Attribute:rated_power_watt' => 'Nennleistung (W)',
    'Class:UPS/Attribute:rated_power_watt+' => 'Nennwirkleistung der USV in Watt.',

    'Class:UPS/Attribute:autonomy_time' => 'Überbrückungszeit',
    'Class:UPS/Attribute:autonomy_time+' => 'Erwartete Überbrückungszeit der USV als Zeitdauer.',
));

//
// Class: UPSBattery
//
/** @disregard P1009 Undefined type Dict */
Dict::Add('DE DE', 'German', 'Deutsch', array(
    'Class:UPSBattery' => 'USV-Batterie',
    'Class:UPSBattery+' => 'Einer unterbrechungsfreien Stromversorgung zugeordnete Batterieeinheit',

    'Class:UPSBattery/Attribute:ups_id' => 'USV',
    'Class:UPSBattery/Attribute:ups_id+' => 'USV, zu der diese Batterieeinheit gehört.',
    'Class:UPSBattery/Attribute:ups_name' => 'USV-Name',

    'Class:UPS/Attribute:batteries_list' => 'Batterien',
    'Class:UPS/Attribute:batteries_list+' => 'Dieser USV zugeordnete Batterieeinheiten.',

    'Class:UPSBattery/Attribute:battery_role' => 'Batterierolle',
    'Class:UPSBattery/Attribute:battery_role+' => 'Rolle dieser Batterieeinheit innerhalb der USV-Installation.',
    'Class:UPSBattery/Attribute:battery_role/Value:internal' => 'Intern',
    'Class:UPSBattery/Attribute:battery_role/Value:external' => 'Extern',
    'Class:UPSBattery/Attribute:battery_role/Value:expansion' => 'Erweiterung',
    'Class:UPSBattery/Attribute:battery_role/Value:replacement' => 'Ersatz',
    'Class:UPSBattery/Attribute:battery_role/Value:other' => 'Sonstige',

    'Class:UPSBattery/Attribute:battery_type' => 'Batterietyp',
    'Class:UPSBattery/Attribute:battery_type+' => 'In dieser Batterieeinheit verwendete Batterietechnologie.',
    'Class:UPSBattery/Attribute:battery_type/Value:vrla' => 'VRLA',
    'Class:UPSBattery/Attribute:battery_type/Value:agm' => 'AGM',
    'Class:UPSBattery/Attribute:battery_type/Value:gel' => 'Gel',
    'Class:UPSBattery/Attribute:battery_type/Value:li_ion' => 'Li-Ion',
    'Class:UPSBattery/Attribute:battery_type/Value:nicd' => 'NiCd',
    'Class:UPSBattery/Attribute:battery_type/Value:other' => 'Sonstige',

    'Class:UPSBattery/Attribute:battery_status' => 'Batteriestatus',
    'Class:UPSBattery/Attribute:battery_status+' => 'Aktueller Status dieser Batterieeinheit.',
    'Class:UPSBattery/Attribute:battery_status/Value:ok' => 'OK',
    'Class:UPSBattery/Attribute:battery_status/Value:warning' => 'Warnung',
    'Class:UPSBattery/Attribute:battery_status/Value:critical' => 'Kritisch',
    'Class:UPSBattery/Attribute:battery_status/Value:expired' => 'Abgelaufen',
    'Class:UPSBattery/Attribute:battery_status/Value:replaced' => 'Ersetzt',
    'Class:UPSBattery/Attribute:battery_status/Value:unknown' => 'Unbekannt',

    'Class:UPSBattery/Attribute:last_replacement_date' => 'Datum letzter Austausch',
    'Class:UPSBattery/Attribute:last_replacement_date+' => 'Datum des letzten Austauschs dieser Batterieeinheit.',

    'Class:UPSBattery/Attribute:next_replacement_date' => 'Datum nächster Austausch',
    'Class:UPSBattery/Attribute:next_replacement_date+' => 'Geplantes Datum des nächsten Austauschs dieser Batterieeinheit.',

    'Class:UPSBattery/Attribute:battery_voltage' => 'Batteriespannung (V)',
    'Class:UPSBattery/Attribute:battery_voltage+' => 'Nennspannung dieser Batterieeinheit.',

    'Class:UPSBattery/Attribute:battery_capacity_ah' => 'Batteriekapazität (Ah)',
    'Class:UPSBattery/Attribute:battery_capacity_ah+' => 'Nennkapazität dieser Batterieeinheit.',
));
