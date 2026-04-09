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
