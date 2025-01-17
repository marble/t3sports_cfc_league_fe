<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2010 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('tx_rnbase_view_Base');


/**
 * Viewklasse für die Anzeige der Ligatabelle mit Hilfe eines HTML-Templates.
 */
class tx_cfcleaguefe_views_MatchReport extends tx_rnbase_view_Base {
  function getMainSubpart() {return '###MATCHREPORT###';}
	
  /**
   * Erstellen des Frontend-Outputs
   */
	function createOutput($template, &$viewData, &$configurations, &$formatter){
    $cObj =& $this->formatter->cObj;
    $matchReport = $viewData->offsetGet('matchReport');

    $home = $matchReport->match->getHome();
    $guest = $matchReport->match->getGuest();

    // Marker:
    // HOME_, GUEST_
    // MATCH_

    $matchMarker = tx_rnbase::makeInstance('tx_cfcleaguefe_util_MatchMarker');
    $match = $matchReport->getMatch();
    $matchStr = $matchMarker->parseTemplate($template, $match, $formatter, 'matchreport.match.', 'MATCH');

    return $matchStr;
  }
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cfc_league_fe/views/class.tx_cfcleaguefe_views_MatchReport.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cfc_league_fe/views/class.tx_cfcleaguefe_views_MatchReport.php']);
}
?>