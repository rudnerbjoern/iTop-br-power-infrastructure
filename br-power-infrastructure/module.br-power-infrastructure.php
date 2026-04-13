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
        ),
        'mandatory' => false,
        'visible' => true,
        'installer' => 'PowerInfrastructureInstaller',

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

if (!class_exists('PowerInfrastructureInstaller')) {
    /**
     * Class PowerInfrastructureInstaller
     */
    class PowerInfrastructureInstaller extends ModuleInstallerAPI
    {
        public static function BeforeWritingConfig(Config $oConfiguration)
        {
            return $oConfiguration;
        }

        public static function AfterDatabaseCreation(Config $oConfiguration, $sPreviousVersion, $sCurrentVersion)
        {
            if (version_compare($sPreviousVersion, '2.0.0', '<')) {
                SetupLog::Info("|- Upgrading br-power-infrastructure from '$sPreviousVersion' to '$sCurrentVersion'.");
                self::ImportLegacyPduPowerSourceLinks();
            }
        }

        protected static function ImportLegacyPduPowerSourceLinks(): void
        {
            $oSearch = DBSearch::FromOQL('SELECT PDU WHERE powerstart_id != 0');
            $oSet = new DBObjectSet($oSearch, array(), array());

            $iChecked = 0;
            $iCreated = 0;
            $iSkipped = 0;

            while ($oPDU = $oSet->Fetch()) {
                $iChecked++;

                $iSourceId = (int) $oPDU->Get('powerstart_id');
                $iTargetId = (int) $oPDU->GetKey();

                if (($iSourceId === 0) || ($iTargetId === 0)) {
                    $iSkipped++;
                    continue;
                }

                $oExistingSearch = DBSearch::FromOQL(
                    'SELECT lnkPowerConnectionToPowerConnection
                     WHERE source_powerconnection_id = :source
                     AND target_powerconnection_id = :target
                     AND link_role = :role'
                );
                $oExistingSet = new DBObjectSet($oExistingSearch, array(), array(
                    'source' => $iSourceId,
                    'target' => $iTargetId,
                    'role' => 'downstream',
                ));

                if ($oExistingSet->Count() > 0) {
                    $sSourceName = trim((string) $oPDU->Get('powerstart_name'));
                    $sTargetName = trim((string) $oPDU->GetName());

                    if ($sSourceName === '') {
                        $sSourceName = '#' . $iSourceId;
                    }
                    if ($sTargetName === '') {
                        $sTargetName = '#' . $iTargetId;
                    }

                    SetupLog::Info("|  |- Skipped existing legacy power path: '$sSourceName' -> '$sTargetName'.");
                    $iSkipped++;
                    continue;
                }

                $oLink = MetaModel::NewObject('lnkPowerConnectionToPowerConnection');
                $oLink->Set('source_powerconnection_id', $iSourceId);
                $oLink->Set('target_powerconnection_id', $iTargetId);
                $oLink->Set('link_role', 'downstream');
                $oLink->Set('comment', 'Imported from legacy PDU powerstart_id');
                $oLink->DBInsert();

                $sSourceName = trim((string) $oPDU->Get('powerstart_name'));
                $sTargetName = trim((string) $oPDU->GetName());

                if ($sSourceName === '') {
                    $sSourceName = '#' . $iSourceId;
                }
                if ($sTargetName === '') {
                    $sTargetName = '#' . $iTargetId;
                }

                $iCreated++;

                SetupLog::Info("|  |- Imported legacy power path: '$sSourceName' -> '$sTargetName'.");
            }

            SetupLog::Info("|- Legacy PDU power path import finished. Checked: $iChecked, created: $iCreated, skipped: $iSkipped.");
        }
    }
}
