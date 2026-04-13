<?php

/**
 * @copyright   Copyright (C) 2022-2026 Björn Rudner
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version     2026-04-13
 *
 * iTop module definition file
 */

/** @disregard P1009 Undefined type SetupWebPage */
SetupWebPage::AddModule(
    __FILE__, // Path to the current file, all other file names are relative to the directory containing this file
    'br-power-infrastructure-bridge-for-teemip-ip-mgmt/3.2.3',
    array(
        // Identification
        'label' => 'Links between TeemIp IP Management and power socket',
        'category' => 'business',

        // Setup
        'dependencies' => array(
            'itop-datacenter-mgmt/3.1.0',
            'teemip-ip-mgmt/3.2.0',
            'teemip-config-mgmt-adaptor/3.2.0',
            'br-power-infrastructure/2.0.0',
            'teemip-datacenter-mgmt-adaptor/3.2.2||br-power-infrastructure/2.0.0',
        ),
        'mandatory' => false,
        'visible' => true,
        'auto_select' => 'SetupInfo::ModuleIsSelected("br-power-infrastructure") && SetupInfo::ModuleIsSelected("teemip-datacenter-mgmt-adaptor")',

        // Components
        'datamodel' => array(),
        'webservice' => array(),
        'dictionary' => array(),
        'data.struct' => array(),
        'data.sample' => array(),

        // Documentation
        'doc.manual_setup' => '',
        'doc.more_information' => '',

        // Default settings
        'settings' => array(),
    )
);
