<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2010 Rene Nitzsche (rene@system25.de)
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_cfcleaguefe_util_league_DefaultTableProvider');

/**
 * The a table provider used to compare two opponents of a single match
 */
class tx_cfcleaguefe_util_league_SingleMatchTableProvider extends tx_cfcleaguefe_util_league_DefaultTableProvider{

	private $match;
	
	function __construct($match, $parameters, $configurations, $league, $confId='') {
		parent::__construct($parameters, $configurations, $league, $confId);
		$this->match = $match;
	}

	function getChartClubs(){
		// Die Clubs aus dem Spiel ermitteln
		$clubs = array();
		$clubId = $this->match->getHome()->record['club'];
		if($clubId) $clubs[] = $clubId;
		$clubId = $this->match->getGuest()->record['club'];
		if($clubId) $clubs[] = $clubId;

		return $clubs;
	} 
	function getMarkClubs(){
		return array();
	}

	function getPenalties() {
		return $this->getLeague()->getPenalties();
	}
	function getMatches() {
    return $this->getLeague()->getMatches(2, $this->cfgTableScope);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cfc_league_fe/util/league/class.tx_cfcleaguefe_util_league_SingleMatchTableProvider.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cfc_league_fe/util/league/class.tx_cfcleaguefe_util_league_SingleMatchTableProvider.php']);
}

?>