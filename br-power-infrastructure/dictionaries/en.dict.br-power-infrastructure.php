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
));
