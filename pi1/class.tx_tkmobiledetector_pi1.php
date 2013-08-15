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

/**
 * Plugin 'Detected Mobile Capabilities' for the 'tk_mobiledetector' extension.
 *
 * @author	Tomasz Krawczyk <tomasz@typo3.pl>
 * @package	TYPO3
 * @subpackage	tk_mobiledetector
 */ 
class tx_tkmobiledetector_pi1 extends tslib_pibase {

	public $prefixId        = 'tx_tkmobiledetector_pi1';		// Same as class name
	public $scriptRelPath   = 'pi1/class.tx_tkmobiledetector_pi1.php';	// Path to this script relative to the extension dir.
	public $extKey          = 'tk_mobiledetector';	// The extension key.
	public $pi_checkCHash   = FALSE; // no caching
	public $pi_USER_INT_obj = 1;     // USER_INT - no caching
	protected $isVersion6   = TRUE;
	
	private function init($conf) {

		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		
		if (!function_exists('t3lib_utility_VersionNumber::convertVersionNumberToInteger'))
			$isVersion6 = FALSE;
	}

	
	private function buildDeviceCapsTable($arrData) {

		$content = '<div class="diTable">';
		
		if (!empty($arrData)) {

			$content .= '<h3>' . $this->pi_getLL('browser_info') . '</h3>';
			$content .= '<table width="100%" border="1" cellpadding="2" cellspacing="0">';
			
			foreach($arrData as $key => $value) {
				$content .= '<tr>';
				$content .= '<td><strong>' . $key . '</strong></td>';
				
				if (!is_array($value) ) {

					if (empty($value)) {

						$content .= '<td>-</td>';
					} else {				
						$content .= '<td>' . $value . '</td>';
					}
				} else {
				
					if (empty($value)) {

						$content .= '<td>-</td>';
					} else {				
							// Sub TABLE
						$content .= '<td>';
						$content .= '<table width="100%" border="1" cellpadding="2" cellspacing="0">';
						
						foreach($value as $subKey => $subValue) {
							$content .= '<tr>';
							$content .= '<td><strong>' . $subKey . '</strong></td>';
							$content .= '<td>' . $subValue . '</td>';
							$content .= '</tr>';
						}
						$content .= '</table>';
						$content .= '</td>';
					}
					
				}
								
				$content .= '</tr>';
			}
			$content .= '</table></div>';
		}
		
		return $content;
	}	


	private function buildUAProfHeadersTable() {

		$content = '<div class="diTable">';
	
		$content .= '<h3>' . $this->pi_getLL('uaprof_headers') . '</h3>';
		$content .= '<table width="100%" border="1" cellpadding="2" cellspacing="0">';
		
		$content .= '<tr>';
		$content .= '<td><strong>HTTP_PROFILE</strong></td>';		
		$content .= '<td>' . (isset($_SERVER['HTTP_PROFILE']) != '' ? $_SERVER['HTTP_PROFILE'] : '-') . '</td>';
		$content .= '</tr>';

		$content .= '<tr>';
		$content .= '<td><strong>HTTP_X_WAP_PROFILE</strong></td>';
		$content .= '<td>' . (isset($_SERVER['HTTP_X_WAP_PROFILE']) ? $_SERVER['HTTP_X_WAP_PROFILE'] : '-') . '</td>';
		$content .= '</tr>';

		$content .= '<tr>';
		$content .= '<td><strong>WAP-PROFILE</strong></td>';
		$content .= '<td>' . (isset($_SERVER['WAP-PROFILE']) ? $_SERVER['WAP-PROFILE'] : '-') . '</td>';
		$content .= '</tr>';

		$content .= '<tr>';
		$content .= '<td><strong>X-WAP-PROFILE</strong></td>';
		$content .= '<td>' . (isset($_SERVER['X-WAP-PROFILE']) ? $_SERVER['X-WAP-PROFILE'] : '-') . '</td>';
		$content .= '</tr>';

		foreach($_SERVER as $key => $val) {
			if (preg_match('/^\d\d\-PROFILE$/', $key, $matches)) {
				
				$content .= '<tr>';
				$content .= '<td><strong>' . $key . '</strong></td>';
				$content .= '<td>' . $val . '</td>';
				$content .= '</tr>';				
			}
		}
		$content .= '</table></div>';
	
		return $content;
	}

	
	private function buildAcceptHeadersTable() {

		$content = '<div class="diTable">';
	
		$content .= '<h3>' . $this->pi_getLL('accept_headers') . '</h3>';
		$content .= '<table width="100%" border="1" cellpadding="2" cellspacing="0">';

		$content .= '<tr>';
		$content .= '<td><strong>HTTP_ACCEPT</strong></td>';
		$content .= '<td>' . (isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '-') . '</td>';
		$content .= '</tr>';

		$content .= '<tr>';
		$content .= '<td><strong>HTTP_ACCEPT_ENCODING</strong></td>';
		$content .= '<td>' . (isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '-') . '</td>';
		$content .= '</tr>';
		
		$content .= '<tr>';
		$content .= '<td><strong>HTTP_ACCEPT_LANGUAGE</strong></td>';
		$content .= '<td>' . (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '-') . '</td>';
		$content .= '</tr>';

		$content .= '</table></div>';
	
		return $content;		
	}


		// Windows Mobile http://msdn.microsoft.com/en-us/library/bb159684.aspx
	private function buildWindowsMobileTable() {
		
		$content = '<div class="diTable">';
	
		$content .= '<h3>' . $this->pi_getLL('win_mobi_headers') . '</h3>';
		$content .= '<table width="100%" border="1" cellpadding="2" cellspacing="0">';
		
		$content .= '<tr>';
		$content .= '<td><strong>UA-pixels</strong></td>';
		$content .= '<td>' . (isset($_SERVER['UA-pixels']) ? $_SERVER['UA-pixels'] : '-') . '</td>';
		$content .= '</tr>';
		
		$content .= '<tr>';
		$content .= '<td><strong>UA-color</strong></td>';
		$content .= '<td>' . (isset($_SERVER['UA-color']) ? $_SERVER['UA-color'] : '-') . '</td>';
		$content .= '</tr>';

		$content .= '<tr>';
		$content .= '<td><strong>UA-OS</strong></td>';
		$content .= '<td>' . (isset($_SERVER['UA-OS']) ? $_SERVER['UA-OS'] : '-') . '</td>';
		$content .= '</tr>';

		$content .= '<tr>';
		$content .= '<td><strong>UA-CPU</strong></td>';
		$content .= '<td>' . (isset($_SERVER['UA-CPU']) ? $_SERVER['UA-CPU'] : '-') . '</td>';
		$content .= '</tr>';

		$content .= '<tr>';
		$content .= '<td><strong>UA-Voice</strong></td>';
		$content .= '<td>' . (isset($_SERVER['UA-Voice']) ? $_SERVER['UA-Voice'] : '-') . '</td>';
		$content .= '</tr>';
		
		$content .= '</table></div>';
	
		return $content;
	}
	
		// Opera mini http://dev.opera.com/articles/view/opera-mini-request-headers/
	private function buildOperaMiniTable() {
		
		$content = '<div class="diTable">';
	
		$content .= '<h3>' . $this->pi_getLL('opera_mini_headers') . '</h3>';
		$content .= '<table width="100%" border="1" cellpadding="2" cellspacing="0">';
		
		$content .= '<tr>';
		$content .= '<td><strong>X-OperaMini-Features</strong></td>';
		$content .= '<td>' . (isset($_SERVER['X-OperaMini-Features']) ? $_SERVER['X-OperaMini-Features'] : '-') . '</td>';
		$content .= '</tr>';
		
		$content .= '<tr>';
		$content .= '<td><strong>X-OperaMini-Phone-UA</strong></td>';
		$content .= '<td>' . (isset($_SERVER['X-OperaMini-Phone-UA']) ? $_SERVER['X-OperaMini-Phone-UA'] : '-') . '</td>';
		$content .= '</tr>';

		$content .= '<tr>';
		$content .= '<td><strong>X-OperaMini-Phone</strong></td>';
		$content .= '<td>' . (isset($_SERVER['X-OperaMini-Phone']) ? $_SERVER['X-OperaMini-Phone'] : '-') . '</td>';
		$content .= '</tr>';

		$content .= '<tr>';
		$content .= '<td><strong>X-Forwarded-For</strong></td>';
		$content .= '<td>' . (isset($_SERVER['X-Forwarded-For']) ? $_SERVER['X-Forwarded-For'] : '-') . '</td>';
		$content .= '</tr>';

		$content .= '</table></div>';
	
		return $content;
	}


	protected function printDetectedDeviceType($strType) {
		
		$content = '<div class="diTable">';	
		$content .= '<h3>' . $this->pi_getLL('device_type') . ': "' . ($strType == '' ? 'not a mobile devide' : $strType) . '"</h3>';
		$content .= '</div>';
		
		return $content;
	}
	
	/**
	 * Main method of your Plugin.
	 *
	 * @param string $content The content of the Plugin
	 * @param array $conf The Plugin Configuration
	 * @return string The content that should be displayed on the website
	 */
	public function main($content, array $conf) {

		$this->init($conf);
		if ($isVersion6) {
			$strUA = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_USER_AGENT');
			$biObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_utility_Client');
		} else {
			$strUA = t3lib_div::getIndpEnv('HTTP_USER_AGENT');
			$biObj = t3lib_div::makeInstance('t3lib_utility_Client');
		}
	
		$bi = $biObj->getBrowserInfo($strUA);
		
		$content = $this->printDetectedDeviceType($biObj->getDeviceType($strUA));
		$content .= $this->buildDeviceCapsTable($bi);
		$content .= $this->buildUAProfHeadersTable();
		$content .= $this->buildAcceptHeadersTable();
		$content .= $this->buildWindowsMobileTable();
		$content .= $this->buildOperaMiniTable();
		// Some other headers_list
		// http://mobiforge.com/developing/blog/useful-x-headers		

		return $this->pi_wrapInBaseClass($content); 
	}
	
}
 
if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/tk_mobiledetector/pi1/class.tx_tkmobiledetector_pi1.php'])) {
	require_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/tk_mobiledetector/pi1/class.tx_tkmobiledetector_pi1.php']);
} 
?>