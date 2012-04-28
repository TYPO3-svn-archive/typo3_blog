<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Roland Schmidt <rsch73@gmail.com>
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
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   52: class tx_typo3blog_pi1 extends tslib_pibase
 *   65:     function init()
 *   78:     function mergeConfiguration()
 *   93:     public function fetchConfigValue($param)
 *  115:     function main($content, $conf)
 *
 * TOTAL FUNCTIONS: 4
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('typo3_blog') . 'lib/class.typo3blog_func.php');
include_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_content.php');


/**
 * Plugin 'Typo3 Blog' for the 'typo3_blog' extension.
 *
 * @author    Roland Schmidt <rsch73@gmail.com>
 * @package    TYPO3
 * @subpackage    tx_typo3blog
 */
class tx_typo3blog_pi1 extends tslib_pibase
{
	public $prefixId = 'tx_typo3blog_pi1'; // Same as class name
	public $scriptRelPath = 'pi1/class.tx_typo3blog_pi1.php'; // Path to this script relative to the extension dir.
	public $extKey = 'typo3_blog'; // The extension key.
	public $pi_checkCHash = TRUE;
	protected $typo3blog_func = NULL;


	/**
	 * initializes this class
	 *
	 * @return    void
	 */
	function init()
	{
		$this->typo3blog_func = new typo3blog_func();
		$this->mergeConfiguration();
		$this->fetchConfigValue('what_to_display');
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typo3_blog']);
	}

	/**
	 * THIS NICE PART IS FROM TYPO3 comments EXTENSION
	 * Merges TS configuration with configuration from flexform (latter takes precedence).
	 *
	 * @return    void
	 */
	function mergeConfiguration()
	{
		$this->pi_initPIflexForm();

		$this->fetchConfigValue('what_to_display');
	}


	/**
	 * THIS NICE FUNCTION IS FROM TYPO3 comments EXTENSION
	 * Fetches configuration value from flexform. If value exists, value in
	 * <code>$this->conf</code> is replaced with this value.
	 *
	 * @param    string        $param    Parameter name. If <code>.</code> is found, the first part is section name, second is key (applies only to $this->conf)
	 * @return    void
	 */
	public function fetchConfigValue($param)
	{
		if (strchr($param, '.')) {
			list($section, $param) = explode('.', $param, 2);
		}
		$value = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'], $param, ($section ? 's' . ucfirst($section) : 'sDEF')));
		if (!is_null($value) && $value != '') {
			if ($section) {
				$this->conf[$section . '.'][$param] = $value;
			}
			else {
				$this->conf[$param] = $value;
			}
		}
	}

	/**
	 * The main method of the PlugIn
	 *
	 * @param    string        $content: The PlugIn content
	 * @param    array        $conf: The PlugIn configuration
	 * @return    The        content that is displayed on the website
	 */
	function main($content, $conf)
	{
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->init();

		switch ($this->conf['what_to_display']) {
			case "BLOGLIST":
				require_once(t3lib_extMgm::extPath('typo3_blog') . 'pi1/class.tx_typo3blog_listview.php');
				$view = t3lib_div::makeInstance('tx_typo3blog_listview');
				return $view->main($content, $conf);
				break;

			case "BLOGSINGLE":
				require_once(t3lib_extMgm::extPath('typo3_blog') . 'pi1/class.tx_typo3blog_singleview.php');
				$view = t3lib_div::makeInstance('tx_typo3blog_singleview');
				return $view->main($content, $conf);
				break;

		}
		$content = "No View defined";
		return $content;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/pi1/class.tx_typo3blog_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/pi1/class.tx_typo3blog_pi1.php']);
}

?>