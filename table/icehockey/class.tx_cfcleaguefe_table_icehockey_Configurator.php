<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2011 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('tx_cfcleaguefe_table_football_Configurator');

/**
 * Configurator for icehockey league tables. 
 */
class tx_cfcleaguefe_table_icehockey_Configurator extends tx_cfcleaguefe_table_football_Configurator {
	/**
	 * Whether or not loose points are count
	 * @return boolean
	 */
	public function isCountLoosePoints() {
		return $this->getPointSystem() == '1'; // Beim eishockey gibt es im 3-Punktsystem keine Minuspunkte.
	}

	public function getPointsWin($afterExtraTime, $afterPenalty) {
		$points = $this->getPointSystem() == '1' ? 2 : 3;
		if($this->getPointSystem() == 0) {
			// Drei Punkt
			$points = $afterExtraTime || $afterPenalty ? 2 : $points;
		}
		return $points;
	}
	public function getPointsDraw($afterExtraTime, $afterPenalty) {
		return 1; // Unentschieden gibt es eigentlich nicht...
	}
	public function getPointsLoose($afterExtraTime, $afterPenalty) {
		return $afterExtraTime || $afterPenalty ? 1 : 0;
	}
	/**
	 * Quelle: http://de.wikipedia.org/wiki/Punkteregel#Eishockey
	 * 0- 3-Punktsystem
	 * 1- 2-Punktsystem
	 */
	public function getPointSystem() {
		return $this->cfgPointSystem;
	}
	/**
	 * @return tx_cfcleaguefe_table_football_IComparator
	 */
	public function getComparator() {
		$compareClass = $this->cfgComparatorClass ? $this->cfgComparatorClass : 'tx_cfcleaguefe_table_football_Comparator';
		$comparator = tx_rnbase::makeInstance($compareClass);
		if(!is_object($comparator))
			throw new Exception('Could not instanciate comparator: '.$compareClass);

		tx_rnbase::load('tx_cfcleaguefe_table_football_IComparator');
		if(!($comparator instanceof tx_cfcleaguefe_table_football_IComparator))
			throw new Exception('Comparator is no instance of tx_cfcleaguefe_table_football_IComparator: '.get_class($comparator));
		return $comparator;
	}

	protected function init() {
		// Der TableScope wirkt sich auf die betrachteten Spiele (Hin-Rückrunde) aus
		$parameters = $this->configurations->getParameters();
		$this->cfgTableScope = $this->getConfValue('tablescope');
		// Wir bleiben mit den alten falschen TS-Einstellungen kompatibel und fragen
		// beide Einstellungen ab
		if($this->configurations->get('tabletypeSelectionInput') || $this->getConfValue('tablescopeSelectionInput')) {
			$this->cfgTableScope = $parameters->offsetGet('tablescope') ? $parameters->offsetGet('tablescope') : $this->cfgTableScope;
		}

		// tabletype means home or away matches only
		$this->cfgTableType = $this->getConfValue('tabletype');
		if($this->configurations->get('tabletypeSelectionInput') || $this->getConfValue('tabletypeSelectionInput')) {
			$this->cfgTableType = $parameters->offsetGet('tabletype') ? $parameters->offsetGet('tabletype') : $this->cfgTableType;
		}

		$this->cfgPointSystem = $this->getMatchProvider()->getBaseCompetition()->record['point_system'];
		if($this->configurations->get('pointSystemSelectionInput') || $this->getConfValue('pointSystemSelectionInput')) {
			$this->cfgPointSystem = is_string($parameters->offsetGet('pointsystem')) ? intval($parameters->offsetGet('pointsystem')) : $this->cfgPointSystem;
		}
		$this->cfgLiveTable = intval($this->getConfValue('showLiveTable'));
		$this->cfgComparatorClass = $this->getConfValue('comparatorClass');
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cfc_league_fe/table/icehockey/class.tx_cfcleaguefe_icehockey_football_Configurator.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cfc_league_fe/table/icehockey/class.tx_cfcleaguefe_table_icehockey_Configurator.php']);
}

?>