<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_tkmobiledetector_pi1.php', '_pi1', 'list_type', 0);

// Get TYPO3 version
if (class_exists('\TYPO3\CMS\Core\Utility\VersionNumberUtility')) {
	$t3version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
} else {
	if (class_exists('t3lib_utility_VersionNumber')) {
		$t3version = t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version);
	} else {
		$t3version = t3lib_div::int_from_ver(TYPO3_version);
	}
}
	
if ($t3version >= 6000000) {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/div/class.t3lib_utility_client.php']['getDeviceType'][] = 'EXT:' . $_EXTKEY . '/Classes/Hook/DeviceInfo.php:&DeviceInfo->getDeviceType';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/div/class.t3lib_utility_client.php']['getBrowserInfo'][] = 'EXT:' . $_EXTKEY . '/Classes/Hook/DeviceInfo.php:&DeviceInfo->getBrowserInfo';

	$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\Configuration\\TypoScript\\ConditionMatching\\ConditionMatcher'] = array('className' => 'ux_ConditionMatcher');
} else {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/div/class.t3lib_utility_client.php']['getDeviceType'][] = 'EXT:' . $_EXTKEY . '/hook/class.tx_device_info.php:&tx_device_info->getDeviceType';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/div/class.t3lib_utility_client.php']['getBrowserInfo'][] = 'EXT:' . $_EXTKEY . '/hook/class.tx_device_info.php:&tx_device_info->getBrowserInfo';

	$GLOBALS['TYPO3_CONF_VARS']['FE']['XCLASS']['t3lib/matchcondition/class.t3lib_matchcondition_frontend.php'] = t3lib_extMgm::extPath($_EXTKEY, 'class.ux_t3lib_matchcondition_frontend.php');
}
?>
