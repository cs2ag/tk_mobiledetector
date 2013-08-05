<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_tkmobiledetector_devices'] = array(
	'ctrl' => array(
		'title'     => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices',		
		'label'     => 'model',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate DESC',	
		'delete' => 'deleted',	
		'enablecolumns' => array(		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY) . 'icon_tx_tkmobiledetector_devices.gif',
	),
);

// TS
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/main/', 'Mobile Main');
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/tt_news/', 'Mobile tt_news'); 

// BE
if (TYPO3_MODE === 'BE') {
	t3lib_extMgm::addModulePath('tools_txtkmobiledetectorM2', t3lib_extMgm::extPath($_EXTKEY) . 'mod2/');		
	t3lib_extMgm::addModule('tools', 'txtkmobiledetectorM2', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod2/');
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_tkmobiledetector_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_tkmobiledetector_pi1_wizicon.php'; 
}

// FE
t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key,pages'; 

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
), 'list_type');
?>
