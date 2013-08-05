<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2013 Tomasz Krawczyk <tomasz@typo3.pl>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/ 

class ux_ConditionMatcher extends \TYPO3\CMS\Frontend\Configuration\TypoScript\ConditionMatching\ConditionMatcher {
	
	private $extKey = 'tk_mobiledetector';

	/**
	 * Evaluates a TypoScript condition given as input, eg. "[browser=net][...(other conditions)...]"
	 *
	 * @param	string		$string: The condition to match against its criterias.
	 * @return	boolean		Whether the condition matched
	 * @see t3lib_tsparser::parse()
	 */
	protected function evaluateCondition($string) {

		$result = parent::evaluateCondition($string);
		if (is_bool($result)) {
			if($result)
				return $result;
		}

		$browserInfo = array();
		$browserInfo = parent::getBrowserInfo(\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_USER_AGENT'));	
		
		list($key, $value) = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('=', $string, FALSE, 2);

		if (!array_key_exists('uaprof', $browserInfo)) {
			return FALSE;
		}

		switch ($key) {
			case 'vendor':
				$values = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $value, TRUE);
				foreach ($values as $test) {
					if ($this->searchStringWildcard($browserInfo['vendor'], $test))
						return TRUE;
				}
				break;

			case 'model':
				$values = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $value, TRUE);
				foreach ($values as $test) {
					if ($this->searchStringWildcard($browserInfo['model'], $test))
						return TRUE;
				}
				break;

			case 'screen_width':
				$values = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $value, TRUE);
				foreach ($values as $test) {
					if ($this->compareNumber($test, $browserInfo['screen_width'])) {
						return TRUE;
					}
				}
				break;

			case 'screen_height':
				$values = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $value, TRUE);
				foreach ($values as $test) {
					if ($this->compareNumber($test, $browserInfo['screen_height'])) {
						return TRUE;
					}
				}
				break;

			case 'browser_name':
				$values = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $value, TRUE);
				foreach ($values as $test) {
					$browsers = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('|', $browserInfo['browser_name'], TRUE);
					foreach ($browsers as $browser) {
						if ($this->searchStringWildcard($browser, $test))
							return TRUE;
					}
				}
				break;
				
			case 'browser_version':
				$values = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $value, TRUE);
				foreach ($values as $test) {
					$bVersions = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('|', $browserInfo['browser_version'], TRUE);
					foreach ($bVersions as $bVersion) {
						if (strcasecmp($test, $bVersion) == 0) {
							return TRUE;
						}
					}
				}
				break;

			case 'frames':
				if (intval($browserInfo['frames']) == intval($value)) {
					return TRUE;
				}
				break;
				
			case 'html_tables':
				if (intval($browserInfo['html_tables']) == intval($value)) {
					return TRUE;
				}
				break;

			case 'java':
				if (intval($browserInfo['java']) == intval($value)) {
					return TRUE;
				}
				break;

			case 'javascript':
				if (intval($browserInfo['javascript']) == intval($value)) {
					return TRUE;
				}
				break;

			case 'html_version':
				$values = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $value, TRUE);
				foreach ($values as $test) {
					if ($this->compareNumber($test, $browserInfo['html_version'])) {
						return TRUE;
					}
				}
				break;

			case 'xhtml_version':
				$values = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $value, TRUE);
				foreach ($values as $test) {
					if ($this->compareNumber($test, $browserInfo['xhtml_version'])) {
						return TRUE;
					}
				}
				break;

			case 'os':
				$values = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $value, TRUE);
				foreach ($values as $test) {
					if ($this->searchStringWildcard($browserInfo['os'], $test))
						return TRUE;
				}
				break;

			case 'os_version':
				$values = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $value, TRUE);
				foreach ($values as $test) {
					if ($this->compareNumber($test, $browserInfo['os_version'])) {
						return TRUE;
					}
				}
				break;

			case 'os_vendor':
				$values = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $value, TRUE);
				foreach ($values as $test) {
					if ($this->searchStringWildcard($browserInfo['os_vendor'], $test))
						return TRUE;
				}
				break;

			case 'gif':
				if (intval($browserInfo['gif']) == intval($value)) {
					return TRUE;
				}
				break;

			case 'jpg':
				if (intval($browserInfo['jpg']) == intval($value)) {
					return TRUE;
				}
				break;

			case 'png':
				if (intval($browserInfo['png']) == intval($value)) {
					return TRUE;
				}
				break;
				
			case 'call_str':
				$values = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $value, TRUE);
				foreach ($values as $test) {
					if (strcasecmp($test, $browserInfo['call_str']) == 0) {
						return TRUE;
					}
				}
				break;
		}
		return FALSE;
 	}
	
}
?>
