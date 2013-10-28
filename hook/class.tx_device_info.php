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

require_once(t3lib_extMgm::extPath('tk_mobiledetector') . 'lib/class.tx_parse_rdf.php');

 /**
 * Class to handle and determine device specific information.
 *
 * $Id$
 *
 * @author	Tomasz Krawczyk <tomasz@typo3.pl>
 */
class tx_device_info {

	private $extKey  = 'tk_mobiledetector';
	private $extConf;
	private $dbTable = 'tx_tkmobiledetector_devices';
	private $xmlLink = '';
	private $acceptHeaders;
	private $userAgent;
	private $deviceCaps = array();
	private $fixCaps = TRUE;

	public function __construct() {
	
		// get extension config
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);

		$this->readUserAgent();
		
		// get accept header
		$this->readAcceptHeader();
		
		// check some known UAProf headers
		$this->readUAProfHeader();
	}
	
	
	private function readUserAgent() {
		$this->userAgent = t3lib_div::getIndpEnv('HTTP_USER_AGENT');
	}
	
	private function readAcceptHeader() {

		$strHeaderAccept = t3lib_div::getIndpEnv('HTTP_ACCEPT');
		$this->acceptHeaders = t3lib_div::trimExplode(',', $strHeaderAccept, TRUE);		
	}

	
	public function disableBrowserHeaders() {

		$this->acceptHeaders = '';
	}


	public function disableFixingCaps() {
		$this->fixCaps = FALSE;
	}

	
	private function readUAProfHeader() {
	
		$this->checkSetLink($_SERVER['HTTP_PROFILE']);
		
		if ($this->xmlLink === '') 
			$this->checkSetLink($_SERVER['HTTP_X_WAP_PROFILE']);
			
		if ($this->xmlLink === '')
			$this->checkSetLink($_SERVER['WAP-PROFILE']);
			
		if ($this->xmlLink === '') 
			$this->checkSetLink($_SERVER['X-WAP-PROFILE']);

		if ($this->xmlLink === '') {			
				// search for '19-PROFILE' like header
			foreach($_SERVER as $key => $val) {
				if (preg_match('/^\d\d\-PROFILE$/', $key, $matches)) {
					$this->checkSetLink($val);
					break;
				}
			}
		}
	}
	

	public function checkSetLink($strLink) {
		
		$correctUrl = '';
				
		if ((substr($strLink, 0, 7) === 'http://') && 
		    ((substr($strLink, -4) === '.xml') || (substr($strLink, -4) === '.rdf'))) {

			if (strlen($strLink) > 10 ) {

				$arUrl = array();
				$arUrl = @parse_url($strLink);

				if (!empty($arUrl['scheme']) && !empty($arUrl['host']) && !empty($arUrl['path'])) {
					$correctUrl = $arUrl['scheme'] . '://' . $arUrl['host'] . $arUrl['path'];
					$this->xmlLink = $correctUrl;
				}
			}
		}		
		
		return $correctUrl;
	}


	public function loadProfileFromDB() {
		
		$bRes = FALSE;
		
		if ($this->xmlLink === '')
			return $bRes; //$deviceData;
		
		$strFields = 'uaprof,vendor,model,screen_width,screen_height,browser_name,browser_version,';
		$strFields .= 'frames,html_tables,java,javascript,html_version,xhtml_version,os,os_version,';
		$strFields .= 'os_vendor,gif,jpg,png,call_str';
		
		$strWhere = 'uaprof = \'' . $GLOBALS['TYPO3_DB']->quoteStr($this->xmlLink, $this->dbTable);
		$strWhere .= '\' AND NOT deleted AND NOT hidden';
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			$strFields,
			$this->dbTable,
			$strWhere,
			'', //group by
			'', // order by
			'1'// limit
		);
		
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		if ($row) {
			$this->deviceCaps = $row;
			$bRes = TRUE;
		}

		return $bRes;
	}


	public function getDeviceData() {
		
		return $this->deviceCaps;
	}


	private function checkProfileInDB() {
	
		$bRes = TRUE;
		
		$strFields = 'uid';
		$strWhere = 'uaprof = \'' . $GLOBALS['TYPO3_DB']->quoteStr($this->xmlLink, $this->dbTable);
		$strWhere .= '\' AND NOT deleted AND NOT hidden';
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			$strFields,
			$this->dbTable,
			$strWhere,
			'', //group by
			'', // order by
			'1'// limit
		);
		
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);		
		if (!$row)
			$bRes = FALSE;

		return $bRes;
	}
	

	public function saveProfileToDB($arrData) {
		
		$iRes = 1;
		
		if (!empty($arrData['uaprof']) && !empty($arrData['vendor']) && !empty($arrData['model'])) {
			
			if (($arrData['screen_width'] >= MIN_SCREEN_WH) && ($arrData['screen_height'] >= MIN_SCREEN_WH)) {

				if (!$this->checkProfileInDB()) {
					
					$arrData['crdate'] = date('U');
					if ($GLOBALS['TYPO3_DB']->exec_INSERTquery($this->dbTable, $arrData)) {
						$iRes = 0; // success saving data
					}
				}
			} else {
				$iRes = 2; // too small device screen to save into database
			}
		} else {
			$iRes = 3; // couldn't get data in ccppschema-2021212 format
		}
		
		return $iRes;
	}

	
	private function fixUAProfByMime($devData) {
		
		if (!empty($devData) && ($this->acceptHeaders != '')) {
			
			if (in_array('image/png', $this->acceptHeaders))
				$devData['png'] = 1;
			
			if (in_array('image/gif', $this->acceptHeaders))
				$devData['gif'] = 1;

			if ((in_array('image/jpg', $this->acceptHeaders)) || (in_array('image/jepg', $this->acceptHeaders)))
				$devData['jpg'] = 1;
		}
	}


	public function getExternalData(&$arrData) {
		
		$rdf = t3lib_div::makeInstance('tx_parse_rdf', $this->xmlLink);
		if (!$rdf->loadXmlFromUrl())
			return 1;
			
		if (!$rdf->checkXmlStr())
			return 2;
		
		$arrData = $rdf->getCapabilities();
		
		return 0;
	}


  	/**
	  * Detects mobile device using http://detectmobilebrowsers.com/ method.
	  *
	  * @param	string		$strUA: The useragent string, t3lib_div::getIndpEnv('HTTP_USER_AGENT').
	  * @return	boolean		True if mobile device is detected or false. 
	  */
	private function doTest1($strUA) {
		
		$bRes = FALSE;

		if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $strUA) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($strUA, 0, 4))) {
		
			$bRes = TRUE;
		}
		return $bRes;
	}

	
    /**
     * Detects Apple iPad tablets
     * 
     * @param string $strUA - User-Agent
     * 
     * @return bool
	 * @see http://developer.apple.com/library/safari/#technotes/tn2010/tn2262/index.html
     */
	private function is_iPad($strUA) {
		
		$bRes = FALSE;

		if (preg_match('/\(iPad;/', substr($strUA, 0, 20))) {
			$bRes = TRUE;
		}
		return $bRes;
	}


    /**
     * Detects Android mobiles
     * 
     * @param string $strUA - User-Agent
     * 
     * @return bool
	 * @see http://googlewebmastercentral.blogspot.com/2012/11/giving-tablet-users-full-sized-web.html
     */
	private function is_AndroidMobile($strUA) {
		
		$sRes = FALSE;

		if (preg_match('/android/i', $strUA)) {
		
			if (preg_match('/mobile/i', $strUA)) {
				$bRes = TRUE;
			}
		}
		return $bRes;
	}

		
    /**
     * Detects Android tablets
     * 
     * @param string $strUA - User-Agent
     * 
     * @return bool
	 * @see http://googlewebmastercentral.blogspot.com/2012/11/giving-tablet-users-full-sized-web.html
     */
	private function is_AndroidTablet($strUA) {
		
		$sRes = FALSE;

		if (preg_match('/android/i', $strUA)) {
		
			if (preg_match('/mobile/i', $strUA) == 0) {
				$bRes = TRUE;
			}
		}
		return $bRes;
	}
	

    /**
     * Detects Windows tablets
     * 
     * @param string $strUA - User-Agent
     * 
     * @return bool
	 * @see http://msdn.microsoft.com/en-us/library/ie/hh920767%28v=vs.85%29.aspx
	 * @see http://msdn.microsoft.com/en-us/library/windows/desktop/ms700675%28v=vs.85%29.aspx
     */
	private function is_WindowsTablet($strUA) {
		
		$bRes = FALSE;

		if ( 
			(preg_match('/Touch/', $strUA)) || // Internet Explorer 10 on Windows 8 and newer
			(preg_match('/Tablet PC 2.0/', $strUA)) // Tablets Windows XP, Vista
		) {
			$bRes = TRUE;
		}
		return $bRes;
	}
	
	
	public function getDeviceType($params) {

		if (TYPO3_DLOG) t3lib_div::devLog(__METHOD__, $this->extKey, 0, $params);
						
		$deviceType = '';
		$userAgent = $params['userAgent'];

		if ($this->xmlLink !== '') {
			
			if (TYPO3_DLOG) t3lib_div::devLog('Has UAProf header', $this->extKey);
			return $this->extConf['DetectedDeviceType'];
		}
		
		// Should detect tablets
		if (intval($this->extConf['DetectTablets']) != 0) {

			if ($this->is_AndroidTablet($userAgent)) {
				if (TYPO3_DLOG) t3lib_div::devLog('Found Android tablet', $this->extKey);
				return 'tablet';
			}

			if ($this->is_iPad($userAgent)) {
				if (TYPO3_DLOG) t3lib_div::devLog('Found iPad tablet', $this->extKey);
				return 'tablet';
			}
			
			if ($this->is_WindowsTablet($userAgent)) {
				if (TYPO3_DLOG) t3lib_div::devLog('Found Windows tablet', $this->extKey);
				return 'tablet';
			}
		}
		
		if ($this->is_AndroidMobile($userAgent)) {
			if (TYPO3_DLOG) t3lib_div::devLog('Found Android mobile device', $this->extKey);
			return $this->extConf['DetectedDeviceType'];		
		}
	
		if ( $this->doTest1($userAgent)) {
			if (TYPO3_DLOG) t3lib_div::devLog('doTest1() is TRUE', $this->extKey);
			return $this->extConf['DetectedDeviceType'];
		}

		if (TYPO3_DLOG) t3lib_div::devLog('Trying TYPO3 method', $this->extKey);
		// Class t3lib_utility_Client::getDeviceType - BEGIN
			// pda
		if (strstr($userAgent, 'avantgo')) {
			$deviceType = 'pda';
		}
			// wap
		$browser = substr($userAgent, 0, 4);
		$wapviwer = substr(stristr($userAgent, 'wap'), 0, 3);
		if ($wapviwer == 'wap' ||
			$browser == 'noki' ||
			$browser == 'eric' ||
			$browser == 'r380' ||
			$browser == 'up.b' ||
			$browser == 'winw' ||
			$browser == 'wapa') {
			$deviceType = 'wap';
		}
			// grabber
		if (strstr($userAgent, 'g.r.a.b.') ||
			strstr($userAgent, 'utilmind httpget') ||
			strstr($userAgent, 'webcapture') ||
			strstr($userAgent, 'teleport') ||
			strstr($userAgent, 'Wget/') ||
			strstr($userAgent, 'webcopier')) {
			$deviceType = 'grabber';
		}
			// robots
		if (strstr($userAgent, 'crawler') ||
			strstr($userAgent, 'spider') ||
			strstr($userAgent, 'googlebot') ||
			strstr($userAgent, 'searchbot') ||
			strstr($userAgent, 'infoseek') ||
			strstr($userAgent, 'altavista') ||
			strstr($userAgent, 'diibot')) {
			$deviceType = 'robot';
		}
		// Class t3lib_utility_Client::getDeviceType - END

		return $deviceType;
	}

	
	public function getBrowserInfo($params) {
		
		$browserInfo = array();

		if ($this->xmlLink !== '') {
		
			$this->loadProfileFromDB();			
			
			if (empty($this->deviceCaps)) {

				$dd = array();
				// unknown device
				$this->getExternalData($dd);
				if (!empty($dd)) {

					//$dd['uaprof'] = $this->xmlLink;						
					if (TYPO3_DLOG)
						t3lib_div::devLog('Device data from xml profile', $this->extKey, 0, $dd);

					// save new mobile device
					$this->saveProfileToDB($dd);
					$this->deviceCaps = $dd;
					
					if($this->fixCaps)
						$this->fixUAProfByMime($this->deviceCaps);
					
					$browserInfo = $this->deviceCaps;
					$browserInfo['useragent'] = $params['userAgent'];						
				}
			}
			else {
					// known mobile device
				if($this->fixCaps)
					$this->fixUAProfByMime($this->deviceCaps);

				$browserInfo = $this->deviceCaps;
				$browserInfo['useragent'] = $params['userAgent'];
			}
		}
		else {

			$userAgent = trim($params['userAgent']);
			$browserInfo['useragent'] = $userAgent;

			// Class t3lib_utility_Client::getBrowserInfo - END				

				// Analyze the userAgent string
				// Declare known browsers to look for

			$known = array('msie', 'firefox', 'webkit', 'opera', 'netscape', 'konqueror',
						   'gecko', 'chrome', 'safari', 'seamonkey', 'navigator', 'mosaic',
						   'lynx', 'amaya', 'omniweb', 'avant', 'camino', 'flock', 'aol');
			$matches = array();

			$pattern = '#(?P<browser>' . join('|', $known) . ')[/ ]+(?P<version>[0-9]+(?:\.[0-9]+)?)#';
				// Find all phrases (or return empty array if none found)
			if (!preg_match_all($pattern, strtolower($userAgent), $matches)) {
				$browserInfo['browser'] = 'unknown';
				$browserInfo['version'] = '';
				$browserInfo['all'] = array();
			} else {
					// Since some UAs have more than one phrase (e.g Firefox has a Gecko phrase,
					// Opera 7,8 have a MSIE phrase), use the last one found (the right-most one
					// in the UA).  That's usually the most correct.
					// For IE use the first match as IE sends multiple MSIE with version, from higher to lower.
				$lastIndex = count($matches['browser']) - 1;
				$browserInfo['browser'] = $matches['browser'][$lastIndex];
				$browserInfo['version'] = $browserInfo['browser'] === 'msie' ? $matches['version'][0] : $matches['version'][$lastIndex];
					//But return all parsed browsers / version in an extra array
				for ($i = 0; $i <= $lastIndex; $i++) {
					if (!isset($browserInfo['all'][$matches['browser'][$i]])) {
						$browserInfo['all'][$matches['browser'][$i]] = $matches['version'][$i];
					}
				}
					//Replace gecko build date with version given by rv
				if (isset($browserInfo['all']['gecko'])) {
					preg_match_all('/rv:([0-9\.]*)/', strtolower($userAgent), $version);
					if ($version[1][0]) {
						$browserInfo['all']['gecko'] = $version[1][0];
					}
				}
			}

				// Microsoft Documentation about Platform tokens: http://msdn.microsoft.com/en-us/library/ms537503(VS.85).aspx
				// 'system' is deprecated, use 'all_systems' (array) in future!
			$browserInfo['system'] = '';
			$browserInfo['all_systems'] = array();
			if (strstr($userAgent, 'Win')) {
					// windows
				if (strstr($userAgent, 'Windows NT 6.1')) {
					$browserInfo['system'] = 'winNT'; // backwards compatible
					$browserInfo['all_systems'][] = 'win7';
					$browserInfo['all_systems'][] = 'winNT';
				} elseif (strstr($userAgent, 'Windows NT 6.0')) {
					$browserInfo['system'] = 'winNT'; // backwards compatible
					$browserInfo['all_systems'][] = 'winVista';
					$browserInfo['all_systems'][] = 'winNT';
				} elseif (strstr($userAgent, 'Windows NT 5.1')) {
					$browserInfo['system'] = 'winNT'; // backwards compatible
					$browserInfo['all_systems'][] = 'winXP';
					$browserInfo['all_systems'][] = 'winNT';
				} elseif (strstr($userAgent, 'Windows NT 5.0')) {
					$browserInfo['system'] = 'winNT'; // backwards compatible
					$browserInfo['all_systems'][] = 'win2k';
					$browserInfo['all_systems'][] = 'winNT';
				} elseif (strstr($userAgent, 'Win98') || strstr($userAgent, 'Windows 98')) {
					$browserInfo['system'] = 'win98';
					$browserInfo['all_systems'][] = 'win98';
				} elseif (strstr($userAgent, 'Win95') || strstr($userAgent, 'Windows 95')) {
					$browserInfo['system'] = 'win95';
					$browserInfo['all_systems'][] = 'win95';
				} elseif (strstr($userAgent, 'WinNT') || strstr($userAgent, 'Windows NT')) {
					$browserInfo['system'] = 'winNT';
					$browserInfo['all_systems'][] = 'winNT';
				} elseif (strstr($userAgent, 'Win16') || strstr($userAgent, 'Windows 311')) {
					$browserInfo['system'] = 'win311';
					$browserInfo['all_systems'][] = 'win311';
				}
			} elseif (strstr($userAgent, 'Mac')) {
				if (strstr($userAgent, 'iPad') || strstr($userAgent, 'iPhone') || strstr($userAgent, 'iPod')) {
					$browserInfo['system'] = 'mac'; // backwards compatible
					$browserInfo['all_systems'][] = 'iOS';
					$browserInfo['all_systems'][] = 'mac';
				} else {
					$browserInfo['system'] = 'mac';
					$browserInfo['all_systems'][] = 'mac';
				}
					// unixes
			} elseif (strstr($userAgent, 'Android')) {
				$browserInfo['system'] = 'linux'; // backwards compatible
				$browserInfo['all_systems'][] = 'android';
				$browserInfo['all_systems'][] = 'linux';
			} elseif (strstr($userAgent, 'Linux')) {
				$browserInfo['system'] = 'linux';
				$browserInfo['all_systems'][] = 'linux';
			} elseif (strstr($userAgent, 'BSD')) {
				$browserInfo['system'] = 'unix_bsd';
				$browserInfo['all_systems'][] = 'unix_bsd';
			} elseif (strstr($userAgent, 'SGI') && strstr($userAgent, ' IRIX ')) {
				$browserInfo['system'] = 'unix_sgi';
				$browserInfo['all_systems'][] = 'unix_sgi';
			} elseif (strstr($userAgent, ' SunOS ')) {
				$browserInfo['system'] = 'unix_sun';
				$browserInfo['all_systems'][] = 'unix_sun';
			} elseif (strstr($userAgent, ' HP-UX ')) {
				$browserInfo['system'] = 'unix_hp';
				$browserInfo['all_systems'][] = 'unix_hp';
			} elseif (strstr($userAgent, 'CrOS')) {
				$browserInfo['system'] = 'linux';
				$browserInfo['all_systems'][] = 'chrome';
				$browserInfo['all_systems'][] = 'linux';
			}				
			// Class t3lib_utility_Client::getBrowserInfo - END

		}

		return $browserInfo;
	}

}
?>