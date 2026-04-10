<?php

/**
 *
 * @copyright   Copyright (C) 2022-2026 Björn Rudner
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version     2026-04-10
 */

namespace BR\Extension\PowerInfrastructure\Controller;

use cmdbAbstractObject;
use DBObjectSet;
use Combodo\iTop\Application\TwigBase\Controller\Controller;
use DBObjectSearch;
use Dict;
use Exception;
use MetaModel;
use utils;
use WebPage;

class PowerSocketController extends Controller
{
    public const ROUTE_NAMESPACE = 'powersocket';

    public function __construct($sViewPath = '', $sModuleName = 'br-power-infrastructure', $aAdditionalPaths = [])
    {
        $sModuleName = 'br-power-infrastructure';
        $sViewPath = MODULESROOT . $sModuleName . '/templates';
        parent::__construct($sViewPath, $sModuleName, $aAdditionalPaths);

        $this->CheckAccess();
    }

    public function OperationCreatePowerSockets()
    {
        $iKey = utils::ReadParam('id');
        $oPDU = MetaModel::GetObject('PDU', $iKey);
        $sError = $oPDU->CreatePowerSockets();

        if ($sError != '') {
            cmdbAbstractObject::SetSessionMessage('PDU', $iKey, 'create_power_socket', Dict::S($sError), WebPage::ENUM_SESSION_MESSAGE_SEVERITY_ERROR, 0);
        }
        $aParams = [];
        $aParams['sURL'] = utils::GetAbsoluteUrlAppRoot() . 'pages/UI.php?operation=details&class=PDU&id=' . $iKey;
        $this->m_sOperation = 'CreatePowerSockets';
        $this->DisplayPage($aParams);
    }
}
