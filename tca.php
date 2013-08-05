<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

require_once(t3lib_extMgm::extPath('tk_mobiledetector') . 'constants.php');

$TCA['tx_tkmobiledetector_devices'] = array(
	'ctrl' => $TCA['tx_tkmobiledetector_devices']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'hidden,vendor,model,screen_width,screen_height,browser_name,browser_version,frames,tables,java,javascript,html_version,xhtml_version,xhtml_modules'
	),
	'feInterface' => $TCA['tx_tkmobiledetector_devices']['feInterface'],
	'columns' => array(
		'hidden' => array(		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array(
				'type'    => 'check',
				'default' => '0'
			)
		),
		'uaprof' => array(
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.uaprof',		
			'config' => array(
				'type' => 'input',	
				'size' => '30',
				'max' => DB_UAPROF_LENGTH,
			)
		),
		'vendor' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.vendor',		
			'config' => array(
				'type' => 'input',	
				'size' => '30',	
				'max' => DB_VENDOR_LENGTH,
			)
		),
		'model' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.model',		
			'config' => array(
				'type' => 'input',	
				'size' => '30',	
				'max' => DB_MODEL_LENGTH,
			)
		),
		'screen_width' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.screen_width',		
			'config' => array(
				'type'     => 'input',
				'size'     => '4',
				'max'      => DB_SCREEN_WIDTH_LENGTH,
				'eval'     => 'int',
				'checkbox' => '0',
				'range'    => array(
					'upper' => '1000',
					'lower' => '10'
				),
				'default' => 0
			)
		),
		'screen_height' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.screen_height',		
			'config' => array(
				'type'     => 'input',
				'size'     => '4',
				'max'      => DB_SCREEN_HEIGHT_LENGTH,
				'eval'     => 'int',
				'checkbox' => '0',
				'range'    => array(
					'upper' => '1000',
					'lower' => '10'
				),
				'default' => 0
			)
		),
		'browser_name' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.browser_name',		
			'config' => array(
				'type' => 'input',	
				'size' => '30',	
				'max' => DB_BROWSER_NAME_LENGTH,
			)
		),
		'browser_version' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.browser_version',		
			'config' => array(
				'type' => 'input',	
				'size' => '30',
				'max' => DB_BROWSER_VERSION_LENGTH,
			)
		),
		'frames' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.frames',		
			'config' => array(
				'type' => 'check',
			)
		),
		'html_tables' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.html_tables',		
			'config' => array(
				'type' => 'check',
			)
		),
		'java' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.java',		
			'config' => array(
				'type' => 'check',
			)
		),
		'javascript' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.javascript',		
			'config' => array(
				'type' => 'check',
			)
		),
		'html_version' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.html_version',		
			'config' => array(
				'type' => 'input',	
				'size' => '30',
				'max' => DB_HTML_VERSION_LENGTH,
			)
		),
		'xhtml_version' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.xhtml_version',		
			'config' => array(
				'type' => 'input',	
				'size' => '30',
				'max' => DB_XHTML_VERSION_LENGTH,
			)
		),
		'os' => array(
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.os',		
			'config' => array(
				'type' => 'input',	
				'size' => '30',
				'max' => DB_OS_NAME_LENGTH,
			)
		),
		'os_version' => array(
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.os_version',		
			'config' => array(
				'type' => 'input',	
				'size' => '30',
				'max' => DB_OS_VERSION_LENGTH,
			)
		),		
		'os_vendor' => array(
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.os_vendor',		
			'config' => array(
				'type' => 'input',	
				'size' => '30',
				'max' => DB_OS_VENDOR_LENGTH,
			)
		),
		'gif' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.gif',		
			'config' => array(
				'type' => 'check',
			)
		),
		'jpg' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.jpg',		
			'config' => array(
				'type' => 'check',
			)
		),
		'png' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.png',		
			'config' => array(
				'type' => 'check',
			)
		),
		'call_str' => array(
			'exclude' => 0,		
			'label' => 'LLL:EXT:tk_mobiledetector/Language/Backend/locallang_db.xml:tx_tkmobiledetector_devices.call_str',
			'config' => array(
				'type' => 'input',	
				'size' => '20',
				'max' => DB_CALL_STR,
			)
		),
	),
	'types' => array(
		'0' => array('showitem' => 'hidden;;1;;1-1-1, vendor, model, screen_width, screen_height, browser_name, browser_version, frames, tables, java, javascript, html_version, xhtml_version, os, os_version, os_vendor, gif, jpg, png, call_str')
	),
	'palettes' => array(
		'1' => array('showitem' => '')
	)
);
?>