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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

tx_rnbase::load('tx_cfcleaguefe_table_TableWriterBase');
tx_rnbase::load('tx_rnbase_util_BaseMarker');

/**
 * Computes league tables for football.
 */
class tx_cfcleaguefe_table_football_TableWriter extends tx_cfcleaguefe_table_TableWriterBase implements tx_cfcleaguefe_table_ITableWriter {
	protected static $token = '';

	/**
	 * Set table data by round
	 * @param tx_cfcleaguefe_table_ITableType $table
	 * @param string $template
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 * @return string
	 */
	public function renderTable($table, $template, $configurations, $confId) {
		$result = $table->getTableData();
		$formatter = $configurations->getFormatter();
		// Zuerst den Wettbewerb
		if(tx_rnbase_util_BaseMarker::containsMarker($template, 'LEAGUE_')) {
			$compMarker = tx_rnbase::makeInstance('tx_cfcleaguefe_util_CompetitionMarker');
			$template = $compMarker->parseTemplate($template, $result->getCompetition(), $configurations->getFormatter(), $confId.'league.', 'LEAGUE');
		}
//$start = microtime(true);
		$penalties = array(); // Strafen sammeln
		$subpartArray['###ROWS###'] = $this->createTable(tx_rnbase_util_Templates::getSubpart($template, '###ROWS###'), 
				$result, $penalties, $table->getMatchProvider(), $configurations, $confId);

		// Jetzt die Strafen auflisten
		$listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
		$template = $listBuilder->render($penalties,
						false, $template, 'tx_rnbase_util_SimpleMarker',
						$confId.'penalty.', 'PENALTY', $formatter, array('classname'=>'tx_cfcleague_models_CompetitionPenalty'));

		// Die Tabellensteuerung
		$subpartArray['###CONTROLS###'] = $this->createControls(tx_rnbase_util_Templates::getSubpart($template, '###CONTROLS###'),
				$result->getConfigurator(), $configurations, $confId.'controls.');
//t3lib_div::debug((microtime(true)-$start), 'class.tx_cfcleaguefe_table_football_TableWriter.php'); // TODO: remove me

		$out = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray);
		return $out;
	}

	/**
	 * Erstellt die Ligatabelle.
	 * @param string $templateList
	 * @param tx_cfcleaguefe_table_ITableResult $result
	 * @param array $penalties
	 * @param tx_cfcleaguefe_table_IMatchProvider $matchProvider
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 */
	protected function createTable($templateList, $result, &$penalties, $matchProvider, $configurations, $confId) {
		$marks = $result->getMarks();

		$round = 0;
		if(intval($configurations->get($confId.'useRoundFromScope'))) {
			$scope = $matchProvider->getScope();
			$round = intval($scope['ROUND_UIDS']);
		}
		$tableData = $result->getScores($round);

		// Sollen alle Teams gezeigt werden?
		$tableSize = intval($configurations->get($confId.'tablecfg.tableSize'));
		if($tableSize && $tableSize < count($tableData)) {
			// Es sollen weniger Teams gezeigt werden als vorhanden sind
			// Diesen Ausschnitt müssen wir jetzt ermitteln
			$tableData = $this->cropTable($tableData, $tableSize);
		}
		// Den TeamMarker erstellen
		$teamMarker = tx_rnbase::makeInstance('tx_cfcleaguefe_util_TeamMarker');
		$templateEntry = tx_rnbase_util_Templates::getSubpart($templateList,'###ROW###');
		
		$parts = array();
		// Die einzelnen Zeilen zusammenbauen
		$rowRoll = intval($configurations->get($confId.'table.roll.value'));
		$rowRollCnt = 0;

		foreach($tableData As $row){
			$row['roll'] = $rowRollCnt;
			// Die Marks für die Zeile setzen
			$this->setMark($row, $marks);
			// auf Strafen prüfen
			$this->preparePenalties($row, $penalties);

			$team = $row['team'];
			unset($row['team']); // Gibt sonst Probleme mit PHP5.2
			$team->record = t3lib_div::array_merge($row, $team->record);

			$parts[] = $teamMarker->parseTemplate($templateEntry, $team, $configurations->getFormatter(), $confId.'table.', 'ROW');
			$rowRollCnt = ($rowRollCnt >= $rowRoll) ? 0 : $rowRollCnt + 1;
		}

		// Jetzt die einzelnen Teile zusammenfügen
		$markerArray = array();
		$subpartArray['###ROW###'] = implode($parts, $configurations->get($confId.'table.implode'));
		return tx_rnbase_util_Templates::substituteMarkerArrayCached($templateList, $markerArray, $subpartArray);
	}

	/**
	 * Wenn nur ein Teil der Tabelle gezeigt werden soll, dann wird dieser Ausschnitt hier
	 * ermittelt und zurückgeliefert. 
	 * @param &$tableData Daten der Tabelle
	 * @param $tableSize Maximale Anzahl Teams, die gezeigt werden soll
	 */
	protected function cropTable(&$tableData, $tableSize) {
		// Es werden nur 5 Teams gezeigt, dabei wird das erste markierte Team in die Mitte gesetzt
		// Suche des Tabellenplatz des markierten Teams
		$cnt = 0;
		$mark = 0;
		foreach($tableData As $row){
			if($row['markClub']) {
				$markIdx = $cnt;
				$mark = 1;
				break;
			}
			$cnt++;
		}

		if($mark) {
			$offsetStart = intval($tableSize / 2);
			$idxStart = ($markIdx - $offsetStart) >= 0 ? $markIdx - $offsetStart : 0;
			$idxEnd = $idxStart + $tableSize;
			// Am Tabellenende nachregulieren
			if($idxEnd > count($tableData)) {
				$idxStart = count($tableData) - $tableSize;
			}
		}

		return array_slice($tableData, $idxStart, $tableSize);
	}

	/**
	 * Die Strafen müssen gesammelt und gezählt werden.
	 */
	protected function preparePenalties(&$row, &$penalties) {
		if(is_array($row['penalties'])) {
			$penalties = array_merge($penalties, $row['penalties']);
			$row['penalties'] = count($row['penalties']);
		}
		else
			$row['penalties'] = 0;
	}

	/**
	 * Setzt die Tabellenmarkierungen für eine Zeile
	 */
	protected function setMark(&$row, &$marks) {
		if(is_array($marks) && array_key_exists($row['position'],$marks)){
			// Markierung und Bezeichnung setzen
			$row['mark'] = $marks[$row['position']][0];
			$row['markLabel'] = $marks[$row['position']][1];
		}
		else {
			$row['mark'] = '';
			$row['markLabel'] = '';
		}
	}


	/**
	 * Erstellt das Steuerungspanel für die Tabelle.
	 * @param tx_rnbase_configurations $configurations
	 * @param tx_cfcleaguefe_table_football_Configurator $configurator
	 */
	protected function createControls($template, $configurator, $configurations, $confId) {
		// Der Link für die Controls
		$formatter = $configurations->getFormatter();
		$parameters = $configurations->getParameters();

		$subpartArray = array('###CONTROL_TABLETYPE###' => '', '###CONTROL_TABLESCOPE###' => '', '###CONTROL_POINTSYSTEM###' =>'',);

		// Tabletype => Home/Away
		if($configurations->get('tabletypeSelectionInput')) {
			$items = array(0,1,2);
			// Wir bereiten die Selectbox vor
			$arr = Array($items, $configurator->getTableType());
			//$arr = Array($items, ($parameters->offsetGet('tabletype') ? $parameters->offsetGet('tabletype') : 0));
			$subpartArray['###CONTROL_TABLETYPE###'] = 
				$this->fillControlTemplate(tx_rnbase_util_Templates::getSubpart($template, '###CONTROL_TABLETYPE###'), 
					$arr, $link, 'TABLETYPE', $configurations, $confId);
		}

		if($configurations->get('tablescopeSelectionInput') || $configurations->get($confId.'tablescopeSelectionInput')) {
			$items = array(0,1,2);
			// Wir bereiten die Selectbox vor
			$arr = Array($items, $configurator->getTableScope());
			$subpartArray['###CONTROL_TABLESCOPE###'] = $this->fillControlTemplate(tx_rnbase_util_Templates::getSubpart($template, '###CONTROL_TABLESCOPE###'), 
				$arr, $link, 'TABLESCOPE', $configurations, $confId);
		}

		if($configurations->get('pointSystemSelectionInput')) {
			// Die Daten für das Punktsystem kommen aus dem TCA der Tabelle tx_cfcleague_competition
			// Die TCA laden

			$sports = $configurator->getCompetition()->getSports();
			$srv = tx_cfcleague_util_ServiceRegistry::getCompetitionService();
			$systems = $srv->getPointSystems($sports);
			$items = array();
			foreach($systems As $system) {
				$items[] = $system[1];
			}

			// Wir bereiten die Selectbox vor
			// $items = array(0,1);
			//$items = array(1=>0,0=>1);

			$arr = array($items, $configurator->getPointSystem());
			$subpartArray['###CONTROL_POINTSYSTEM###'] = $this->fillControlTemplate(tx_rnbase_util_Templates::getSubpart($template, '###CONTROL_POINTSYSTEM###'), 
				$arr, $link, 'POINTSYSTEM', $configurations, $confId);
		}

		$out = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray);
		return $out;
	}

	/**
	 * Die Auswahl für Tabellentyp, Tabellenscope und Punktesystem.
	 * @param string $template HTML- Template
	 * @param array &$itemsArr Datensätze für die Auswahl
	 * @param tx_rnbase_util_Link &$link Linkobjekt
	 * @param string $markerName Name des Markers (TYPE, SCOPE oder SYSTEM)
	 * @param tx_rnbase_configurations &$configurations Konfig-Objekt
	 */
	protected function fillControlTemplate($template, &$itemsArr, $link, $markerName, $configurations, $confId) {
//		$link->initByTS($configurations, $confId.'link.', array());

		$items = $itemsArr[0];
		$currItem = $itemsArr[1];
		$confName = strtolower($markerName); // Konvention
		$noLink = array('','');
		$formatter = $configurations->getFormatter();

		// Aus den KeepVars den aktuellen Wert entfernen
//		$keepVars = $configurations->getKeepVars()->getArrayCopy();
//		unset($keepVars[$confName]);

//		if($link) {
//			$token = md5(microtime());
//			$link->label($token);
//		}
		
		$currentNoLink = intval($configurations->get($confId. $confName .'.current.noLink'));

		$token = self::getToken();
		$markerArray = array();
		// Jetzt über die vorhandenen Items iterieren
		while( list($key, $value) = each($itemsArr[0])) {
			$link = $configurations->createLink();
			$link->label($token);
			$link->initByTS($configurations, $confId. $confName .'.link.', array($confName=>$key));

			$isCurrent = ($key == $currItem);
			$markerLabel = $formatter->wrap($key, $confId. $confName .'.'.$key.'.');

			$data['iscurrent'] = $isCurrent ? 1 : 0;
			$data['value'] = $value;
	
			$tempArray = $formatter->getItemMarkerArrayWrapped($data, $confId. $confName.'.', 0, 'CONTROL_'.$markerName.'_'. $markerLabel.'_');
			$tempArray['###CONTROL_'. $markerName .'_'. $markerLabel .'###'] = $tempArray['###CONTROL_'. $markerName .'_'. $markerLabel .'_VALUE###'];
			$markerArray = array_merge($markerArray, $tempArray);
			$url = $formatter->wrap($link->makeUrl(false), $confId. $confName . ($isCurrent ? '.current.' : '.normal.'));
			$markerArray['###CONTROL_'. $markerName .'_'. $markerLabel .'_LINK_URL###'] = $url;
			$markerArray['###CONTROL_'. $markerName .'_'. $markerLabel .'_LINKURL###'] = $url;

			$linkStr = ($currentNoLink && $key == $currItem) ? $token : $link->makeTag();
			// Einen zusätzliche Wrap um das generierte Element inkl. Link
			$linkStr = $formatter->wrap($linkStr, $confId. $confName . ($isCurrent ? '.current.' : '.normal.') );
			$wrappedSubpartArray['###CONTROL_'.$markerName.'_'. $markerLabel .'_LINK###'] = explode($token, $linkStr);
		}
		
		$out = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);
		return $out;
	}
	/**
	 * Returns a token string.
	 * @return string
	 */
	protected static function getToken() {
		if(!self::$token)
			self::$token = md5(microtime());
		return self::$token;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cfc_league_fe/table/football/class.tx_cfcleaguefe_table_football_TableWriter.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cfc_league_fe/table/football/class.tx_cfcleaguefe_table_football_TableWriter.php']);
}

?>