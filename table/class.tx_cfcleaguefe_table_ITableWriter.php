<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-13 Rene Nitzsche (rene@system25.de)
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


/**
 * Implementors generate an output string for table.
 */
interface tx_cfcleaguefe_table_ITableWriter {
	/**
	 * Set table data by round
	 * @param tx_cfcleaguefe_table_ITableType $table
	 * @param string $template
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 * @return string
	 */
	public function writeTable($table, $template, $configurations, $confId);
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cfc_league_fe/table/class.tx_cfcleaguefe_table_ITableWriter.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cfc_league_fe/table/class.tx_cfcleaguefe_table_ITableWriter.php']);
}

?>