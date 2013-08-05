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
class ParseRdf {
	
	private $strData;
	private $strScreen;
	private $strUrl;
	private $arCaps = array();

	public function __construct($strLink) {
		$this->strUrl = $strLink;
	}
	
	
	private function strToBool($strVal) {
		$bRes = 0;
		$strTmp = strtoupper($strVal);
		
		if ($strTmp == 'YES')
			$bRes = 1;
			
		return $bRes;
	}


	public function loadXmlFromUrl() {
		
		$bRes = FALSE;
		
		if ($this->strUrl == '')
			return $bRes;
		
		$strXML = \TYPO3\CMS\Core\Utility\GeneralUtility::getURL($this->strUrl);		
		if ($strXML) {

			$this->strData = $strXML;
			$bRes = TRUE;
		}
		
		return $bRes;
	}
	
	
	public function checkXmlStr() {
	
		$bRes = FALSE;
		
		if ($this->strData == '')
			return $bRes;

		$oXml = @simplexml_load_string($this->strData);
		if(is_object($oXml)) {
			$bRes = TRUE;
		}
		
		return $bRes;
	}


	private function getXmlValues($strXPath) {
		
		$arrRes = array();
		
		if ($this->strData == '')
			return $arrRes;

		if (!$this->checkXmlStr($this->strData)) {

			$this->strUrl = '';
			return $arrRes;
		}
		
		$oXml = @simplexml_load_string($this->strData);
		if(!is_object($oXml)) {
			$this->strUrl = '';
			return $arrRes;
		}

		$result = $oXml->xpath($strXPath);

		while(list( , $node) = each($result)) {
			$arrRes[] = $node;
		}

		return $arrRes;
	}


	public function getVendor() {		

		$sTmp = '';	
		
		$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:Vendor';
		$a = $this->getXmlValues($strXP);
		
		if (!empty($a))
			$sTmp =  substr($a[0], 0, DB_VENDOR_LENGTH);
		
		$this->arCaps['vendor'] = trim($sTmp);
		return $sTmp;
	}
	

	public function getModel() {
		
		$sTmp = '';
		
		$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:Model';
		$a = $this->getXmlValues($strXP);

		if (!empty($a))
			$sTmp =  substr($a[0], 0, DB_MODEL_LENGTH);
			
		$this->arCaps['model'] = trim($sTmp);
		return $sTmp;
	}


	public function getScreenWidth() {

		$iRes = 0;
		
		if ($this->strScreen == '') {
		
			$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:ScreenSize';
			$a = $this->getXmlValues($strXP);
			
			if (empty($a)) 			
				$this->strScreen = '';
			else
				$this->strScreen = $a[0];			
		}
		
		if ($this->strScreen != '') {
		
			$arrTmp = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('x', $this->strScreen, true);
			$strTmp = substr($arrTmp[0], 0, DB_SCREEN_WIDTH_LENGTH);		
			$iRes = intval($strTmp);
		}
		
		$this->arCaps['screen_width'] = $iRes;
		return $iRes;
	}


	public function getScreenHeight() {
		
		$iRes = 0;

		if ($this->strScreen == '') {
			$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:ScreenSize';
			$a = $this->getXmlValues($strXP);

			if (empty($a))
				$this->strScreen = '';
			else	
				$this->strScreen = $a[0];
		}
		if ($this->strScreen != '') {
		
			$arrTmp = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('x', $this->strScreen, true);
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
		
		$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:BrowserName';
		$a = $this->getXmlValues($strXP);

		if (!empty($a)) {
			$sTmp = '';
			while(list( , $node) = each($a)) {
				$sTmp .= $node . '|';
			}
			$sTmp = substr($sTmp, 0, DB_BROWSER_NAME_LENGTH);
		}

		$this->arCaps['browser_name'] = trim($sTmp);
		return $sTmp;
	}


	public function getBrowserVersion() {
		
		$sTmp = '';
		
		$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:BrowserVersion';
		$a = $this->getXmlValues($strXP);

		if (!empty($a)) {
			while(list( , $node) = each($a)) {
				$sTmp .= $node . '|';
			}
			$sTmp = substr($sTmp, 0, DB_BROWSER_VERSION_LENGTH);
		}

		$this->arCaps['browser_version'] = trim($sTmp);
		return $sTmp;
	}

	
	public function getFrames() {
		
		$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:FramesCapable';
		$a = $this->getXmlValues($strXP);
		
		if (empty($a))
			$intTmp = 0;
		else
			$intTmp = $this->strToBool(substr($a[0], 0, DB_FRAMES_LENGTH));
		
		$this->arCaps['frames'] = $intTmp;	
		return $intTmp;
	}
	

	public function getTables() {
		
		$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:TablesCapable';
		$a = $this->getXmlValues($strXP);
		
		if (empty($a))
			$intTmp = 0;
		else
			$intTmp = $this->strToBool(substr($a[0], 0, DB_TABLES_LENGTH));
		
		$this->arCaps['html_tables'] = $intTmp;	
		return $intTmp;
	}
	

	public function getJava() {
		
		$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:JavaEnabled';
		$a = $this->getXmlValues($strXP);
		
		if (empty($a))
			$intTmp = 0;
		else
			$intTmp = $this->strToBool(substr($a[0], 0, DB_JAVA_LENGTH));
		
		$this->arCaps['java'] = $intTmp;	
		return $intTmp;
	}

	
	public function getJavaScript() {
		
		$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:JavaScriptEnabled';
		$a = $this->getXmlValues($strXP);
		
		if (empty($a))
			$intTmp = 0;
		else
			$intTmp = $this->strToBool(substr($a[0], 0, DB_JAVASCRIPT_LENGTH));
		
		$this->arCaps['javascript'] = $intTmp;	
		return $intTmp;
	}
	
		
	public function getHtmlVersion() {
		
		$sTmp = '';

		$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:HtmlVersion';
		$a = $this->getXmlValues($strXP);
		
		if (!empty($a)) {
			$sTmp =  substr($a[0], 0, DB_HTML_VERSION_LENGTH);
		}
		
		$this->arCaps['html_version'] = trim($sTmp);
		return $sTmp;
	}


	public function getXhtmlVersion() {
		
		$sTmp = '';

		$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:XhtmlVersion';
		$a = $this->getXmlValues($strXP);
		
		if (empty($a)) {
			$sTmp =  substr($a[0], 0, DB_XHTML_VERSION_LENGTH);
		}
			
		$this->arCaps['xhtml_version'] = trim($sTmp);
		return $sTmp;
	}


	public function getOSName() {
		
		$sTmp = '';

		$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:OSName';
		$a = $this->getXmlValues($strXP);
		
		if (!empty($a)) {
			$sTmp =  substr($a[0], 0, DB_OS_NAME_LENGTH);
		}
			
		$this->arCaps['os'] = trim($sTmp);
		return $sTmp;
	}


	public function getOSVersion() {
		
		$sTmp = '';
		
		$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:OSVersion';
		$a = $this->getXmlValues($strXP);
		
		if (!empty($a)) {
			$sTmp =  substr($a[0], 0, DB_OS_VERSION_LENGTH);
		}			
	
		$this->arCaps['os_version'] = trim($sTmp);
		return $sTmp;
	}
	
	
	public function getOSVendor() {
		
		$sTmp = '';
		
		$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:OSVendor';
		$a = $this->getXmlValues($strXP);
		
		if (!empty($a)) {
			$sTmp = substr($a[0], 0, DB_OS_VENDOR_LENGTH);
		}
		
		$this->arCaps['os_vendor'] = trim($sTmp);
		return $sTmp;
	}


	public function getGIF() {
		
		$iRes = 0;
		$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:CcppAccept/rdf:Bag/rdf:li';
		$a = $this->getXmlValues($strXP);
		
		if (!empty($a)) {

			$sTmp = '';
			while(list( , $node) = each($a)) {
				$sTmp .= $node . '|';
			}
			$a = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('|', $sTmp, true);
			
			if (in_array('image/gif', $a, true)) {
				$iRes = 1;
			}
		}

		$this->arCaps['gif'] = $iRes;		
		return $iRes;
	}


	public function getJPG() {
		
		$iRes = 0;
		$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:CcppAccept/rdf:Bag/rdf:li';
		$a = $this->getXmlValues($strXP);
		
		if (!empty($a)) {
			$sTmp = '';
			while(list( , $node) = each($a)) {
				$sTmp .= $node . '|';
			}
			$a = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('|', $sTmp, true);

			if (in_array('image/jpg', $a, true) || in_array('image/jpeg', $a, true)) {
				$iRes = 1;
			}
		}
		
		$this->arCaps['jpg'] = $iRes;		
		return $iRes;
	}


	public function getPNG() {
		
		$iRes = 0;
		$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:CcppAccept/rdf:Bag/rdf:li';
		$a = $this->getXmlValues($strXP);
		
		if (!empty($a)) {
		
			$sTmp = '';
			while(list( , $node) = each($a)) {
				$sTmp .= $node . '|';
			}
			$a = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('|', $sTmp, true);

			if (in_array('image/png', $a, true)) {
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
		$strXP = '/rdf:RDF/rdf:Description/prf:component/rdf:Description/prf:WtaiLibraries/rdf:Bag/rdf:li';
		$a = $this->getXmlValues($strXP);

		if (!empty($a)) {

			$sTmp = '';
			while(list( , $node) = each($a)) {
				$sTmp .= $node . '|';
			}
			$a = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('|', $sTmp, true);

			if (in_array('WTA.Public.makeCall', $a, true)) {
				$sRes = 'wtai://wp/mc;';
			}
		}
		
		$this->arCaps['call_str'] = $sRes;
		return $sRes;
	}
	

	private function fixByBrowserName() {
	
		if ($this->arCaps['browser_name'] != '') {
			
			$arBrowsers = array();
			$arTmpBrowsers = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('|', $this->arCaps['browser_name'], true);
			
			foreach($arTmpBrowsers as $browser) {
			
				if (strstr($browser, 'Internet Explorer') !== FALSE) {
				
					$this->arCaps['os'] = 'Windows Mobile OS';
					$this->arCaps['os_vendor'] = 'Microsoft';
					$browser = 'Microsoft Mobile Explorer';
				}
				$arBrowsers[] = $browser;
			}
			$this->arCaps['browser_name'] = implode('|', $arBrowsers);
		} 
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
	private function fixCallStrByOS() {
		
		if (($this->arCaps['os'] == 'Windows Mobile OS') || 
			($this->arCaps['os'] == 'webOS') ||
			($this->arCaps['os'] == 'iPhone OS') ) {
			
			$this->arCaps['call_str'] = 'tel:';
		}
		
		if ($this->arCaps['os'] == 'Android') {
		
			$this->arCaps['call_str'] = 'wtai://wp/mc;';
		}
	}

	
	public function getCapabilities() {

		$arrCaps = array();
		
		if ($this->strUrl == '')
			return $arrCaps;
				
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
		$this->getOSName();
		$this->getOSVersion();
		$this->getOSVendor();
		$this->getGIF();
		$this->getJPG();
		$this->getPNG();
		$this->getCallStr();
		
		$this->fixByBrowserName();
		$this->fixCallStrByOS();

		$this->arCaps['uaprof'] = $this->strUrl;

		return $this->arCaps;
	}
	
}

 
 if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/tk_mobiledetector/lib/class.parse_rdf.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/tk_mobiledetector/lib/class.parse_rdf.php']);
}
?>
