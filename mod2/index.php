<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Tomasz Krawczyk <tomasz@typo3.pl>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
$GLOBALS['LANG']->includeLLFile('EXT:tk_mobiledetector/Language/Backend/mod2/locallang.xml');
$GLOBALS['BE_USER']->modAccess($MCONF, 1);	// This checks permissions and exits if the users has no permission for entry.

require_once(t3lib_extMgm::extPath('tk_mobiledetector') . 'hook/class.tx_device_info.php');

/**
 * Module 'Mobile devices' for the 'tk_mobiledetector' extension.
 *
 * @author	Tomasz Krawczyk <tomasz@typo3.pl>
 * @package	TYPO3
 * @subpackage	tx_tkmobiledetector
 */
class tx_tkmobiledetector_module2 extends t3lib_SCbase {

	protected $pageinfo;
	protected $extKey = 'tk_mobiledetector';
	protected $extConf = array(); // Extension configuration
	protected $tableName = 'tx_tkmobiledetector_devices';

	/**
	 * Initializes the module.
	 *
	 * @return void
	 */
	public function init() {

		parent::init();

			// Get extension configuration
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	public function menuConfig() {

		$this->MOD_MENU = array(
			'function' => array(
				'func_menu'  => $GLOBALS['LANG']->getLL('func_menu'),
				'func_stats' => $GLOBALS['LANG']->getLL('func_stats'),
				'func_check' => $GLOBALS['LANG']->getLL('func_check'),
				'func_exch'  => $GLOBALS['LANG']->getLL('func_exch'),
			)
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the 
	 * $this->id parameter which will contain the uid-number of the page
	 * clicked in the page tree.
	 *
	 * @return void
	 */
	public function main() {
			// Access check!
			// The page will show only if there is a valid page and if this
			// page may be viewed by the user.
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

			// Initialize doc
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->setModuleTemplate(t3lib_extMgm::extPath('tk_mobiledetector') . 'mod2/mod_template.html');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];
		$this->doc->docType = 'xhtml_trans';

		if (($this->id && $access) || ($GLOBALS['BE_USER']->user['admin'] && !$this->id)) {
				// Draw the form
			$this->doc->form = '<form action="" method="post" enctype="multipart/form-data">';
				// JavaScript
			$this->doc->JScode = '<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>';

			$this->doc->postCode='<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>';

			$docHeaderButtons = $this->getButtons();
				// Render content:
			$strBody = $this->moduleContent();			
				// Compile document
			$markers = array();
			$markers['FUNC_MENU'] = t3lib_BEfunc::getFuncMenu(0, 'SET[function]', 
				$this->MOD_SETTINGS['function'], 
				$this->MOD_MENU['function']);
			$markers['CONTENT'] = $strBody;

				// Build the <body> for the module
			$this->content = $this->doc->startPage($GLOBALS['LANG']->getLL('title'));
			$this->content .= $this->doc->moduleBody($this->pageinfo, $docHeaderButtons, $markers);
			$this->content .= $this->doc->endPage();
			//$this->content .= $this->doc->insertStylesAndJS($this->content);
		}
	}

	/**
	 * Prints out the module HTML.
	 *
	 * @return void
	 */
	public function printContent() {

		//$this->content .= $this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content.
	 *
	 * @return string
	 */
	protected function moduleContent() {

		switch ((string)$this->MOD_SETTINGS['function']) {
			case 'func_menu':
				$strCnt = $this->functionMenu();
				$content .= $this->doc->section($GLOBALS['LANG']->getLL('function0'), $strCnt, 0, 1);
				break;
			case 'func_stats':
				$strCnt = $this->functionDeviceStatistics();
				$content .= $this->doc->section($GLOBALS['LANG']->getLL('function1'), $strCnt, 0, 1);
				break;
			case 'func_check':
				$strCnt = $this->functionCheckAndAddDeviceCaps();
				$content .= $this->doc->section($GLOBALS['LANG']->getLL('function2'), $strCnt, 0, 1);
				break;
			case 'func_exch':
				$strCnt = $this->functionExchangeDevices();
				$content .= $this->doc->section($GLOBALS['LANG']->getLL('function1'), $strCnt, 0, 1);
				break;
			default:
				$content = '';
				break;
		}
		return $content;
	}

	/**
	 * Creates the panel of buttons for submitting the form or otherwise
	 * perform operations.
	 *
	 * @return array All available buttons as an assoc.
	 */
	protected function getButtons() {

		$buttons = array(
			'csh' => '',
			'shortcut' => '',
		);

			// CSH
		$buttons['csh'] = t3lib_BEfunc::cshItem('_MOD_web_func', '', $GLOBALS['BACK_PATH']);

			// Shortcut
		if ($GLOBALS['BE_USER']->mayMakeShortcut())	{
			$buttons['shortcut'] = $this->doc->makeShortcutIcon('', 'function', $this->MCONF['name']);
		}

		return $buttons;
	}


	protected function functionMenu() {

		$availableModFuncs = array('func_stats', 'func_check', 'func_exch');
		$moduleTitle = $GLOBALS['LANG']->getLL('func_menu_description');

		$content = $this->doc->section($moduleTitle, $content, FALSE, TRUE); 
		$content .= '<dl class="t3-overview-list">';

		foreach ($availableModFuncs as $modFunc) {
			$link        = 'mod.php?id=0&amp;M=tools_txtkmobiledetectorM2&amp;SET[function]=' . $modFunc;
			$title       = $GLOBALS['LANG']->getLL($modFunc);
			$description = $GLOBALS['LANG']->getLL($modFunc . '_description');

			$icon = '<img src="' . t3lib_extMgm::extRelPath($this->extKey) . 'ext_icon.gif" width="18" height="16" title="' . 
				$title . '" alt="' . $title . '" />';

			$content .= '<dt><a href="' . $link . '">' . $icon . $title . '</a></dt><dd>' . $description . '</dd>';
		}
		$content .= '</dl>';

		return $content;
	}


	protected function functionDeviceStatistics() {

		$moduleTitle = $GLOBALS['LANG']->getLL('func_stats');
		$content = $this->doc->section($moduleTitle, $content, FALSE, TRUE);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'ast, Count( uid )',
			$this->tableName,
			'',
			'ast',
			'ast',
			'0,3'
		);

		if ($res) {
			$content .= '<table width="400" cellspacing="0" cellpadding="10" border="0" class="typo3-dblist">';

			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res)) {

				$content .= '<tr>';
				$content .= '<td><strong>';
				switch ((int)$row[0]) {
					case 0:
						$content .= $GLOBALS['LANG']->getLL('stat0');
						break;
					case 1:
						$content .= $GLOBALS['LANG']->getLL('stat1');
						break;
					case 2:
						$content .= $GLOBALS['LANG']->getLL('stat2');
						break;
					default:
						$content .= '&nbsp;';
						break;
				}
				$content .= '</strong></td>';
				$content .= '<td>' . $row[1] . '</td>';
				$content .= '</tr>';			
			}
			$content .= '</table>';
			
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}

		return $content;
	}

	private function buildInputForm() {

		$content = '<fieldset>';		
		$content .= '<table width="700" border="0" cellpadding="5" cellspacing="0">';
		$content .= '<tr><td width="180">';
		$content .= '<label for="furl" stle="width: 170px;">' . $GLOBALS['LANG']->getLL('func_check_enter_url') . '</label>';
		$content .= '</td>';
		$content .= '<td width="520">';
		$content .= '<input type="text" name="furl" id="furl" size="70" maxlength=255"  stle="width: 510px;" />';
		$content .= '<input type="submit" value="' . $GLOBALS['LANG']->getLL('submit') . '" />';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '</table>';		
		$content .= '</fieldset>';

		return $content;
	}

	private function buildDeviceCapsTable($arrData, $isNew = 0) {

		if ($isNew == 1) {
			$moduleTitle = $GLOBALS['LANG']->getLL('new_device_added');
		} else {
			$moduleTitle = $GLOBALS['LANG']->getLL('known_device');
		}

		$content = $this->doc->section($moduleTitle, $content, FALSE, TRUE); 
		
		if (!empty($arrData)) {

			$content .= '<table width="700" border="0" cellpadding="5" cellspacing="0" class="typo3-dblist">';

			foreach ($arrData as $key => $value) {
				$content .= '<tr>';
				$content .= '<td><strong>' . $key . '</strong></td>';
				$content .= '<td>' . $value . '</td>';
				$content .= '</tr>';
			}
			$content .= '</table>';
		}

		return $content;
	}

	protected function functionCheckAndAddDeviceCaps() {

		$moduleTitle = $GLOBALS['LANG']->getLL('func_check');
		$content = $this->doc->section($moduleTitle, $content, FALSE, TRUE);		
		$content .= $this->buildInputForm();

		$strUrl = t3lib_div::_POST('furl');

		if ($strUrl != '') {

			$pObj = new tx_device_info();
			$pObj->disableBrowserHeaders();
			$strUrl = $pObj->checkSetLink($strUrl);

			if ($strUrl != '') {

				if ($pObj->loadProfileFromDB()) {

					$arrCaps = $pObj->getDeviceData();
					if (!empty($arrCaps)) {
						$content .= $this->buildDeviceCapsTable($arrCaps);
					}
				} else {

					$arrCaps = array();
					$iEx = $pObj->getExternalData($arrCaps);
					if ($iEx == 0) {

						$savRes = $pObj->saveProfileToDB($arrCaps);
						switch($savRes) {
							case 0:
								$content .= $this->buildDeviceCapsTable($arrCaps, 1);
								break;

							case 2:
								$content .= $GLOBALS['LANG']->getLL('too_small_device');
								break;
							
							case 3:
								$content .= $GLOBALS['LANG']->getLL('incorrect_data_format');
								break;
							
							default:
								$content .= $GLOBALS['LANG']->getLL('db_save_error');
								break;
						}
					} else {

						switch($iEx) {
							case 1:
								$sMsg = sprintf($GLOBALS['LANG']->getLL('download_device_caps_error'), $strUrl, $strUrl);
								$content .= $sMsg;
								break;

							case 2:
								$content .= $GLOBALS['LANG']->getLL('xml_syntax_error');
								break;

							default:
								$content .= '';
								break;
						}					
					}
				}
			} else {
				$content .= $GLOBALS['LANG']->getLL('incorrect_url');
			}
		}

		return $content;
	}

	protected function functionExchangeDevices() {

		$moduleTitle = $GLOBALS['LANG']->getLL('func_exch');
		$content = $this->doc->section($moduleTitle, $content, FALSE, TRUE); 
		$content .= $GLOBALS['LANG']->getLL('not_finished');

		return $content;
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/tk_mobiledetector/mod2/index.php'])) {
	require_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/tk_mobiledetector/mod2/index.php']);
}

	// Make instance:
/** @var $SOBE tx_tkmobiledetector_module2 */
$SOBE = t3lib_div::makeInstance('tx_tkmobiledetector_module2');
$SOBE->init();

	// Include files?
foreach ($SOBE->include_once as $INC_FILE) {
	require_once($INC_FILE);
}

$SOBE->main();
$SOBE->printContent();
?>