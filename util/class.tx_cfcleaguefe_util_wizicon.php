<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2013 Rene Nitzsche (rene@system25.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

tx_rnbase::load('tx_rnbase_util_Wizicon');

/**
 * Class that adds the wizard icon.
 *
 * @author	René Nitzsche <rene[at]system25.de>
 */
class tx_cfcleaguefe_util_wizicon extends tx_rnbase_util_Wizicon {
	/**
	 * 
	 */
	protected function getPluginData() {
		$plugins = array();
		$plugins['tx_cfcleaguefe_competition'] = array(
			'icon'=>t3lib_extMgm::extRelPath('cfc_league_fe').'/ext_icon.gif',
			'title'=>'plugin.competition.label',
			'description'=>'plugin.competition.description',
		);
		$plugins['tx_cfcleaguefe_report'] = array(
			'icon'=>t3lib_extMgm::extRelPath('cfc_league_fe').'/ext_icon.gif',
			'title'=>'plugin.report.label',
			'description'=>'plugin.report.description',
		);
		return $plugins;
	}
	protected function getLLFile() {
		return t3lib_extMgm::extPath('cfc_league_fe').'locallang_db.xml';
	}
	/**
	 * Adds the T3sports report plugin wizard icon
	 *
	 * @param array Input array with wizard items for plugins
	 * @return array Modified input array, having the items for T3sports plugins added.
	 */
	function proc____($wizardItems)	{
		global $LANG;

		$LL = $this->includeLocalLang();

		$wizardItems['plugins_tx_cfcleaguefe_competition'] = array(
			'icon'=>t3lib_extMgm::extRelPath('cfc_league_fe').'/ext_icon.gif',
			'title'=>$LANG->getLLL('plugin.competition.label',$LL),
			'description'=>$LANG->getLLL('plugin.competition.description',$LL),
			'params'=>'&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=tx_cfcleaguefe_competition'
		);

		$wizardItems['plugins_tx_cfcleaguefe_report'] = array(
			'icon'=>t3lib_extMgm::extRelPath('cfc_league_fe').'/ext_icon.gif',
			'title'=>$LANG->getLLL('plugin.report.label',$LL),
			'description'=>$LANG->getLLL('plugin.report.description',$LL),
			'params'=>'&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=tx_cfcleaguefe_report'
		);

		return $wizardItems;
	}
	function includeLocalLang_____()	{
		$llFile = t3lib_extMgm::extPath('cfc_league_fe').'locallang_db.xml';
		// FIXME: Ändern
		$LOCAL_LANG = t3lib_l10n_parser_Llxml::getParsedData($llFile, $GLOBALS['LANG']->lang);
		return $LOCAL_LANG;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cfc_league_fe/util/class.tx_cfcleaguefe_util_wizicon.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cfc_league_fe/util/class.tx_cfcleaguefe_util_wizicon.php']);
}
?>