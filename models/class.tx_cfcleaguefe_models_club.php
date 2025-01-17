<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('tx_rnbase_model_base');
tx_rnbase::load('tx_cfcleague_models_Club');


/**
 * Model für einen Verein.
 */
class tx_cfcleaguefe_models_club extends tx_cfcleague_models_Club {
  /** Array with loaded club instances */
  private static $instances;
	
  /**
   * Returns address dataset or null
   * @return tx_cfcleague_models_Address or null
   */
  function getAddress() {
  	if(!$this->record['address'])
  		return null;
    $address = tx_rnbase::makeInstance('tx_cfcleague_models_Address', $this->record['address']);
		return $address->isValid() ? $address : null;
  }
  /**
   * Whether or not this is a favorite club
   *
   * @return boolean
   */
  function isFavorite() {
  	return intval($this->record['favorite']) > 0;
  }
  /**
   * Liefert die Teams dieses Vereins
   * TODO: In Service auslagern
   * @param $saisonIds commaseperated saison-uids
   * @param $agegroups commaseperated agegroup-uids
   */
  function getTeams($saisonIds, $agegroups) {
    $what = 'distinct tx_cfcleague_teams.uid, tx_cfcleague_teams.comment, ' .
            'tx_cfcleague_teams.name, tx_cfcleague_teams.short_name, ' .
            'tx_cfcleague_teams.coaches, tx_cfcleague_teams.players, tx_cfcleague_teams.supporters, '. 
            'tx_cfcleague_teams.coaches_comment, tx_cfcleague_teams.players_comment, tx_cfcleague_teams.supporters_comment, '. 
            'tx_cfcleague_teams.dam_images';
    $from = array('tx_cfcleague_teams INNER JOIN tx_cfcleague_competition c ON FIND_IN_SET(tx_cfcleague_teams.uid, c.teams) AND c.hidden=0 AND c.deleted=0 ',
                  'tx_cfcleague_teams');
    $options = array();
    $options['where'] = 'tx_cfcleague_teams.club = ' . $this->uid .
                    ' AND c.saison IN (' . $saisonIds . ')' .
                    ' AND c.agegroup IN (' . $agegroups . ')';
    $options['wrapperclass'] = 'tx_cfcleaguefe_models_team';

    return tx_rnbase_util_DB::doSelect($what,$from,$options,0);


/*
SELECT distinct t.uid, t.name FROM tx_cfcleague_teams
INNER JOIN tx_cfcleague_competition c
ON FIND_IN_SET(t.uid, c.teams)
WHERE t.club = 1
AND c.saison = 1
AND c.agegroup = 1
*/
  }

  /**
   * statische Methode, die ein Array mit Instanzen dieser Klasse liefert. Ist der übergebene
   * Parameter leer, dann werden alle Vereins-Datensätze aus der Datenbank geliefert. Ansonsten 
   * wird ein String mit der uids der gesuchten Vereine erwartet ('2,4,10,...').
   * @param string $clubUids String mit UIDs von Clubs oder leer
   * @param string $saisonUids String with saison uids
   * @param string $groupUids string with age group uids
   * @param string $compUids string with competition uids
   * @return Array of tx_cfcleaguefe_models_club
   */
  function findAll($clubUids, $saisonUids = '', $groupUids = '', $compUids = '') {
    // FIXME: Die Felder des Clubs aus der TCA laden.
    $what = 'DISTINCT tx_cfcleague_club.uid, tx_cfcleague_club.name, tx_cfcleague_club.short_name, tx_cfcleague_club.dam_logo ';
    $from = array('
      tx_cfcleague_club 
      INNER JOIN tx_cfcleague_teams ON tx_cfcleague_club.uid = tx_cfcleague_teams.club
      INNER JOIN tx_cfcleague_competition ON FIND_IN_SET(tx_cfcleague_teams.uid, tx_cfcleague_competition.teams)', 'tx_cfcleague_club' );

    $options['wrapperclass'] = 'tx_cfcleaguefe_models_club';
    $options['orderby'] = 'name';

    $saison = (strlen($saisonUids)) ? implode(t3lib_div::intExplode(',', $saisonUids), ',') : '';
    if(strlen($saison) > 0)
      $where .= ' tx_cfcleague_competition.saison IN (' . $saison . ')';

    $groups = (strlen($groupUids)) ? implode(t3lib_div::intExplode(',', $groupUids), ',') : '';
    if(strlen($groups) > 0) {
      if(strlen($where) >0) $where .= ' AND ';
      $where .= ' tx_cfcleague_competition.agegroup IN (' . $groups . ')';
    }

    $comps = (strlen($compUids)) ? implode(t3lib_div::intExplode(',', $compUids), ',') : '';
    if(strlen($comps) > 0) {
      if(strlen($where) >0) $where .= ' AND ';
      $where .= ' tx_cfcleague_competition.uid IN (' . $comps . ')';
    }

    $clubs = (strlen($clubUids)) ? implode(t3lib_div::intExplode(',', $clubUids), ',') : '';
    if(strlen($clubs) > 0) {
      if(strlen($where) >0) $where .= ' AND ';
      $where .= ' tx_cfcleague_club.uid IN (' . $clubs . ')';
    }

    $options['where'] = (strlen($where) >0) ? $where : '1';

/*
select distinct tx_cfcleague_club.uid, tx_cfcleague_club.name 
from tx_cfcleague_club 
INNER JOIN tx_cfcleague_teams ON tx_cfcleague_club.uid = tx_cfcleague_teams.club
INNER JOIN tx_cfcleague_competition ON FIND_IN_SET(tx_cfcleague_teams.uid, tx_cfcleague_competition.teams)

WHERE tx_cfcleague_competition.saison = 1
AND tx_cfcleague_competition.agegroup = 1
*/    
    return tx_rnbase_util_DB::doSelect($what,$from,$options,0);

  }

  /**
   * Returns cached instances of clubs
   *
   * @param int $$clubUid
   * @return tx_cfcleaguefe_models_club
   */
  static function getInstance($clubUid) {
    $uid = intval($clubUid);
    if(!$uid) throw new Exception('Club uid expected. Was: >' . $clubUid . '<', -1);
    if(! self::$instances[$uid]) {
      self::$instances[$uid] = tx_rnbase::makeInstance('tx_cfcleaguefe_models_club', $clubUid);
    }
    return self::$instances[$uid];
  }
  
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cfc_league_fe/models/class.tx_cfcleaguefe_models_club.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cfc_league_fe/models/class.tx_cfcleaguefe_models_club.php']);
}

?>