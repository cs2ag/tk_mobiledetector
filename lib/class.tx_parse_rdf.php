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
require_once(t3lib_extMgm::extPath('tk_mobiledetector') . 'constants.php');
/**
 * Utility class for rdf-xml processing.
 *
 * $Id$
 *
 * @author	Tomasz Krawczyk <tomasz@typo3.pl>
 */
class tx_parse_rdf {

	private $strData;
	private $strScreen;
	private $strUrl;
	private $arCaps = array();

    /**
     * Class constructor
     *
     * @return void
     */
	public function __construct($strLink) {
		$this->strUrl = $strLink;
		return;
	}

	private function strToBool($strVal) {
		$bRes = 0;
		$strTmp = strtoupper($strVal);

		if ($strTmp == 'YES') {
			$bRes = 1;
		}

		return $bRes;
	}

	public function loadXmlFromUrl() {
		
		$bRes = FALSE;
		
		if ($this->strUrl == '') {
			return $bRes;
		}

		$strXml = t3lib_div::getURL($this->strUrl);		
		if ($strXml) {
			$this->strData = $strXml;
			$bRes = TRUE;
		}
		
		return $bRes;
	}

	public function checkXmlStr() {
	
		$bRes = FALSE;
		
		if ($this->strData == '') {
			return $bRes;
		}

		$oXml = @simplexml_load_string($this->strData);
		if (is_object($oXml)) {
			$bRes = TRUE;
		}
		
		return $bRes;
	}

	private function getXmlValues($strXpath) {
		
		$arrRes = array();
		
		if ($this->strData == '') {
			return $arrRes;
		}

		if (!$this->checkXmlStr($this->strData)) {
			$this->strUrl = '';
			return $arrRes;
		}
		
		$oXml = @simplexml_load_string($this->strData);
		if (!is_object($oXml)) {
			$this->strUrl = '';
			return $arrRes;
		}

		$result = $oXml->xpath($strXpath);

		while (list (, $node) = each($result)) {
			$arrRes[] = $node;
		}

		return $arrRes;
	}

	public function getVendor() {		

		$sTmp = '';		
		$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:Vendor';
		$a = $this->getXmlValues($strXp);

		if (!empty($a)) {
			$sTmp = trim(substr($a[0], 0, DB_VENDOR_LENGTH));
		}

		$this->arCaps['vendor'] = $sTmp;
		return $sTmp;
	}

	public function getModel() {

		$sTmp = '';		
		$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:Model';
		$a = $this->getXmlValues($strXp);

		if (!empty($a)) {
			$sTmp = trim(substr($a[0], 0, DB_MODEL_LENGTH));
		}

		$this->arCaps['model'] = $sTmp;
		return $sTmp;
	}

	public function getScreenWidth() {

		$iRes = 0;
		if ($this->strScreen == '') {
			$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:ScreenSize';
			$a = $this->getXmlValues($strXp);
			
			if (empty($a)) {
				$this->strScreen = '';
			} else {
				$this->strScreen = $a[0];
			}
		}

		if ($this->strScreen != '') {
			$arrTmp = t3lib_div::trimExplode('x', $this->strScreen, TRUE);
			$strTmp = substr($arrTmp[0], 0, DB_SCREEN_WIDTH_LENGTH);		
			$iRes = intval($strTmp);
		}
		
		$this->arCaps['screen_width'] = $iRes;
		return $iRes;
	}

	public function getScreenHeight() {

		$iRes = 0;

		if ($this->strScreen == '') {
			$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:ScreenSize';
			$a = $this->getXmlValues($strXp);

			if (empty($a)) {
				$this->strScreen = '';
			} else {
				$this->strScreen = $a[0];
			}
		}

		if ($this->strScreen != '') {		
			$arrTmp = t3lib_div::trimExplode('x', $this->strScreen, TRUE);
			$strTmp = substr($arrTmp[1], 0, DB_SCREEN_HEIGHT_LENGTH);
			$iRes = intval($strTmp);
		}		

		$this->arCaps['screen_height'] = $iRes;		
		return $iRes;
	}

    /**
     * Extracts browser(s) name(s).
     *
     *
     * @return	string	Browser name(s) delimited with '|'.
     */
	public function getBrowserName() {
		
		$sTmp = '';		
		$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:BrowserName';
		$a = $this->getXmlValues($strXp);

		if (!empty($a)) {
			while (list (, $node) = each($a)) {
				$sTmp .= $node . '|';
			}
			$sTmp = trim(substr($sTmp, 0, DB_BROWSER_NAME_LENGTH));
		}

		$this->arCaps['browser_name'] = $sTmp;
		return $sTmp;
	}

	public function getBrowserVersion() {
		
		$sTmp = '';		
		$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:BrowserVersion';
		$a = $this->getXmlValues($strXp);

		if (!empty($a)) {
			while ( list(, $node) = each($a)) {
				$sTmp .= trim($node) . '|';
			}
			$sTmp = substr($sTmp, 0, DB_BROWSER_VERSION_LENGTH);
		}

		$this->arCaps['browser_version'] = $sTmp;
		return $sTmp;
	}

	public function getFrames() {

		$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:FramesCapable';
		$a = $this->getXmlValues($strXp);

		$intTmp = 0;
		if (!empty($a)) {
			$intTmp = $this->strToBool(substr($a[0], 0, DB_FRAMES_LENGTH));
		}

		$this->arCaps['frames'] = $intTmp;	
		return $intTmp;
	}

	public function getTables() {

		$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:TablesCapable';
		$a = $this->getXmlValues($strXp);

		$intTmp = 0;
		if (!empty($a)) {
			$intTmp = $this->strToBool(substr($a[0], 0, DB_TABLES_LENGTH));
		}

		$this->arCaps['html_tables'] = $intTmp;
		return $intTmp;
	}

	public function getJava() {

		$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:JavaEnabled';
		$a = $this->getXmlValues($strXp);
		
		$intTmp = 0;
		if (!empty($a)) {
			$intTmp = $this->strToBool(substr($a[0], 0, DB_JAVA_LENGTH));
		}

		$this->arCaps['java'] = $intTmp;	
		return $intTmp;
	}

	public function getJavaScript() {
		
		$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:JavaScriptEnabled';
		$a = $this->getXmlValues($strXp);
		
		$intTmp = 0;
		if (!empty($a)) {
			$intTmp = $this->strToBool(substr($a[0], 0, DB_JAVASCRIPT_LENGTH));
		}

		$this->arCaps['javascript'] = $intTmp;	
		return $intTmp;
	}
	
	public function getHtmlVersion() {

		$sTmp = '';
		$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:HtmlVersion';
		$a = $this->getXmlValues($strXp);

		if (!empty($a)) {
			$sTmp = trim(substr($a[0], 0, DB_HTML_VERSION_LENGTH));
		}

		$this->arCaps['html_version'] = $sTmp;
		return $sTmp;
	}

	public function getXhtmlVersion() {
		
		$sTmp = '';
		$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:XhtmlVersion';
		$a = $this->getXmlValues($strXp);
		
		if (empty($a)) {
			$sTmp = trim(substr($a[0], 0, DB_XHTML_VERSION_LENGTH));
		}
			
		$this->arCaps['xhtml_version'] = $sTmp;
		return $sTmp;
	}

	public function getOsName() {

		$sTmp = '';
		$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:OSName';
		$a = $this->getXmlValues($strXp);

		if (!empty($a)) {
			$sTmp = trim(substr($a[0], 0, DB_OS_NAME_LENGTH));
		}

		$this->arCaps['os'] = $sTmp;
		return $sTmp;
	}

	public function getOsVersion() {

		$sTmp = '';
		$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:OSVersion';
		$a = $this->getXmlValues($strXp);

		if (!empty($a)) {
			$sTmp = trim(substr($a[0], 0, DB_OS_VERSION_LENGTH));
		}			

		$this->arCaps['os_version'] = $sTmp;
		return $sTmp;
	}

	public function getOsVendor() {

		$sTmp = '';
		$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:OSVendor';
		$a = $this->getXmlValues($strXp);

		if (!empty($a)) {
			$sTmp = trim(substr($a[0], 0, DB_OS_VENDOR_LENGTH));
		}

		$this->arCaps['os_vendor'] = $sTmp;
		return $sTmp;
	}

	public function getGIF() {

		$iRes = 0;
		$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:CcppAccept/rdf:Bag/rdf:li';
		$a = $this->getXmlValues($strXp);

		if (!empty($a)) {

			$sTmp = '';
			while (list (, $node) = each($a)) {
				$sTmp .= $node . '|';
			}
			$a = t3lib_div::trimExplode('|', $sTmp, TRUE);
			
			if (in_array('image/gif', $a, TRUE)) {
				$iRes = 1;
			}
		}

		$this->arCaps['gif'] = $iRes;		
		return $iRes;
	}

	public function getJPG() {
		
		$iRes = 0;
		$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:CcppAccept/rdf:Bag/rdf:li';
		$a = $this->getXmlValues($strXp);

		if (!empty($a)) {
			$sTmp = '';
			while (list (, $node) = each($a)) {
				$sTmp .= $node . '|';
			}
			$a = t3lib_div::trimExplode('|', $sTmp, TRUE);

			if (in_array('image/jpg', $a, TRUE) || in_array('image/jpeg', $a, TRUE)) {
				$iRes = 1;
			}
		}

		$this->arCaps['jpg'] = $iRes;		
		return $iRes;
	}

	public function getPNG() {

		$iRes = 0;
		$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:CcppAccept/rdf:Bag/rdf:li';
		$a = $this->getXmlValues($strXp);

		if (!empty($a)) {

			$sTmp = '';
			while ( list(, $node) = each($a)) {
				$sTmp .= $node . '|';
			}
			$a = t3lib_div::trimExplode('|', $sTmp, TRUE);

			if (in_array('image/png', $a, TRUE)) {
				$iRes = 1;
			}
		}

		$this->arCaps['png'] = $iRes;		
		return $iRes;
	}

    /**
     * Get mobile possibility to make phone calls directly from the web page.
     * 
     * @return	string	phone call string
     */
	public function getCallStr() {
		
		$sRes = 'tel:';
		$strXp = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:WtaiLibraries/rdf:Bag/rdf:li';
		$a = $this->getXmlValues($strXp);

		if (!empty($a)) {

			$sTmp = '';
			while ( list(, $node) = each($a)) {
				$sTmp .= $node . '|';
			}
			$a = t3lib_div::trimExplode('|', $sTmp, TRUE);

			if (in_array('WTA.Public.makeCall', $a, TRUE)) {
				$sRes = 'wtai://wp/mc;';
			}
		}
		
		$this->arCaps['call_str'] = $sRes;
		return $sRes;
	}
	
	private function fixByBrowserName() {

		if ($this->arCaps['browser_name'] != '') {

			$arBrowsers = array();
			$arTmpBrowsers = t3lib_div::trimExplode('|', $this->arCaps['browser_name'], TRUE);

			foreach ($arTmpBrowsers as $browser) {

				if (strstr($browser, 'Internet Explorer') !== FALSE) {

					$this->arCaps['os'] = 'Windows Mobile OS';
					$this->arCaps['os_vendor'] = 'Microsoft';
					$browser = 'Microsoft Mobile Explorer';
				}
				$arBrowsers[] = $browser;
			}
			$this->arCaps['browser_name'] = implode('|', $arBrowsers);
		}
		return;
	}

    /**
     * Windows Mobile 6 and newer supports both strings 'tel:' and 'wtai://wp/mc;'
	 * http://msdn.microsoft.com/en-us/library/dd938878.aspx
	 * We will use shorter version.
	 *
	 * http://www.mobilexweb.com/blog/click-to-call-links-mobile-browsers
	 *
     * @return	void
     */
	private function fixCallStrByOs() {

		if (($this->arCaps['os'] == 'Windows Mobile OS') || 
			($this->arCaps['os'] == 'webOS') || 
			($this->arCaps['os'] == 'iPhone OS')) {

			$this->arCaps['call_str'] = 'tel:';
		}

		if ($this->arCaps['os'] == 'Android') {

			$this->arCaps['call_str'] = 'wtai://wp/mc;';
		}
		return;
	}

	
	public function getCapabilities() {

		$arrCaps = array();

		if ($this->strUrl == '') {
			return $arrCaps;
		}

		$this->getVendor();		
		$this->getModel();
		$this->getScreenWidth();
		$this->getScreenHeight();
		$this->getBrowserName();
		$this->getBrowserVersion();
		$this->getFrames();
		$this->getTables();
		$this->getJava();
		$this->getJavaScript();
		$this->getHtmlVersion();
		$this->getXhtmlVersion();
		$this->getOsName();
		$this->getOsVersion();
		$this->getOsVendor();
		$this->getGIF();
		$this->getJPG();
		$this->getPNG();
		$this->getCallStr();

		$this->fixByBrowserName();
		$this->fixCallStrByOs();

		$this->arCaps['uaprof'] = $this->strUrl;

		return $this->arCaps;
	}	
}
 
 if (defined('TYPO3_MODE') && 
	isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/tk_mobiledetector/lib/class.parse_rdf.php'])) {
	require_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/tk_mobiledetector/lib/class.parse_rdf.php']);
}
?>