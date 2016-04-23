<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "cfc_league_fe".
 *
 * Auto generated 06-01-2016 17:12
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'T3sports FE',
	'description' => 'FE-Plugins von T3sports. Liefert u.a. die Views Spielplan, Spielbericht, Tabellen, Team- und Spieleransicht. FE plugins for T3sports. Contains views for matchtable, leaguetable, matchreport, team and player reports and many more. Requires PHP5!',
	'category' => 'plugin',
	'version' => '1.0.0',
	'state' => 'stable',
	'uploadfolder' => false,
	'createDirs' => '',
	'clearcacheonload' => true,
	'author' => 'Rene Nitzsche',
	'author_email' => 'rene@system25.de',
	'author_company' => 'System 25',
	'constraints' => 
	array (
		'depends' => 
		array (
			'typo3' => '4.3.0-6.2.99',
			'php' => '5.3.0-0.0.0',
			'rn_base' => '0.14.1-0.0.0',
			'pbimagegraph' => '2.0.0-0.0.0',
			'cfc_league' => '1.0.0-0.0.0',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
);

