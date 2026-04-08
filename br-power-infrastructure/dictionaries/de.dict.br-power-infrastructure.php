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
));
