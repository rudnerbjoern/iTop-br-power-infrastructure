<?php

/**
 * @copyright   Copyright (C) 2026 Björn Rudner
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version     2026-04-10
 * iTop module definition file
 */

/** @disregard P1009 Undefined type SetupWebPage */
SetupWebPage::AddModule(
    __FILE__, // Path to the current file, all other file names are relative to the directory containing this file
    'br-power-infrastructure/1.1.0',
    array(
        // Identification
        //
        'label' => 'Datamodel: Power Infrastructure',
        'category' => 'business',

        // Setup
        //
        'dependencies' => array(
            'itop-config-mgmt/3.2.0',
            'itop-datacenter-mgmt/3.2.0',
            'itop-virtualization-mgmt/3.2.0',
            'itop-storage-mgmt/3.2.0',
            'teemip-network-mgmt-extended/3.1.0',
        ),
        'mandatory' => false,
        'visible' => true,

        // Components
        //
        'datamodel' => array(
            'src/Controller/PowerSocketController.php',
            'src/Hook/PowerSocketOtherActions.php',
        ),
        'webservice' => array(),
        'data.struct' => array(
            // add your 'structure' definition XML files here,
        ),
        'data.sample' => array(
            // add your sample data XML files here,
        ),

        // Documentation
        //
        'doc.manual_setup' => '', // hyperlink to manual setup documentation, if any
        'doc.more_information' => 'https://github.com/rudnerbjoern/iTop-br-power-infrastructure/blob/main/README.md',

        // Default settings
        //
        'settings' => array(
            // Module specific settings go here, if any
        ),
    )
);
