<?php
$arr = array();

// Check TYPO3 version
// Use PHP function because t3lib_utility_VersionNumber::convertVersionNumberToInteger is not loaded yet.
if (version_compare(TYPO3_version, '6.0', '>=')) { 

	$extPath = t3lib_extMgm::extPath('tk_mobiledetector');

	$arr = array(
		'ux_ConditionMatcher' => $extPath . 'Classes/Xclass/ConditionMatcher.php',
		'ParseRdf' => $extPath . 'Classes/ParseRdf.php'
	);
}

return $arr;
?>
