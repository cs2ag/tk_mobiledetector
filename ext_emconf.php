<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "tk_mobiledetector".
 *
 * Auto generated 01-08-2013 08:28
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Mobile Detector',
	'description' => 'Mobile Detector tries to detect mobile devices. It uses UAProf headers to get device capabilities and stores a few of them into database for future use.',
	'category' => 'misc',
	'shy' => 0,
	'version' => '2.4.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Tomasz Krawczyk',
	'author_email' => 'tomasz@typo3.pl',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-6.1.99',
			'php' => '5.3.0-5.3.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:37:{s:9:"ChangeLog";s:4:"e9ef";s:42:"class.ux_t3lib_matchcondition_frontend.php";s:4:"a409";s:13:"constants.php";s:4:"9fb6";s:16:"ext_autoload.php";s:4:"0248";s:21:"ext_conf_template.txt";s:4:"6d2c";s:12:"ext_icon.gif";s:4:"9e8d";s:17:"ext_localconf.php";s:4:"e220";s:14:"ext_tables.php";s:4:"4b79";s:14:"ext_tables.sql";s:4:"dbce";s:25:"ext_tables_static+adt.sql";s:4:"bdbe";s:36:"icon_tx_tkmobiledetector_devices.gif";s:4:"9e8d";s:10:"README.txt";s:4:"9fa9";s:7:"tca.php";s:4:"e427";s:20:"Classes/ParseRdf.php";s:4:"e8e2";s:40:"Classes/Hook/detectmobilebrowser.php.txt";s:4:"68df";s:27:"Classes/Hook/DeviceInfo.php";s:4:"6237";s:35:"Classes/Xclass/ConditionMatcher.php";s:4:"3129";s:30:"Language/Backend/locallang.xml";s:4:"3e81";s:33:"Language/Backend/locallang_db.xml";s:4:"3f34";s:35:"Language/Backend/mod2/locallang.xml";s:4:"1705";s:39:"Language/Backend/mod2/locallang_mod.xml";s:4:"1ad3";s:14:"doc/manual.pdf";s:4:"c93f";s:14:"doc/manual.sxw";s:4:"77ad";s:29:"hook/class.tx_device_info.php";s:4:"f6b0";s:26:"lib/class.tx_parse_rdf.php";s:4:"fa29";s:13:"mod2/conf.php";s:4:"3272";s:14:"mod2/index.php";s:4:"31dc";s:22:"mod2/mod_template.html";s:4:"1b14";s:19:"mod2/moduleicon.gif";s:4:"9e8d";s:14:"pi1/ce_wiz.gif";s:4:"0f61";s:37:"pi1/class.tx_tkmobiledetector_pi1.php";s:4:"3ef1";s:45:"pi1/class.tx_tkmobiledetector_pi1_wizicon.php";s:4:"9440";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.xml";s:4:"1058";s:25:"static/main/constants.txt";s:4:"a48c";s:21:"static/main/setup.txt";s:4:"8fd7";s:24:"static/tt_news/setup.txt";s:4:"5291";}',
);

?>