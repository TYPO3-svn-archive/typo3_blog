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
 *   54: class tx_typo3blog_singleview extends tslib_pibase
 *   72:     function init()
 *   98:     function main($content, $conf)
 *  122:     function mergeConfiguration()
 *  160:     public function fetchConfigValue($param)
 *  181:     private function getPostByRootLine()
 *  197:     private function getPostCategoryName($pid, $field = 'title')
 *
 * TOTAL FUNCTIONS: 6
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
class tx_typo3blog_singleview extends tslib_pibase
{
	public $prefixId = 'tx_typo3blog_pi1'; // Same as class name
	public $scriptRelPath = 'pi1/class.tx_typo3blog_pi1.php'; // Path to this script relative to the extension dir.
	public $extKey = 'typo3_blog'; // The extension key.
	public $pi_checkCHash = TRUE;

	private $typo3blog_func = NULL;
	private $template = NULL;
	private $extConf = NULL;
	private $page_uid = NULL;
	private $blog_doktype_id = NULL;

	/**
	 * initializes this class
	 *
	 * @return	void
	 */
	function init()
	{
		// Make instance of tslib_cObj
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');

		// Merge current configuration from flexform and typoscript
		$this->mergeConfiguration();

		// unserialize extension conf
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typo3_blog']);

		// Set current page id
		$this->page_uid = intval($GLOBALS['TSFE']->page['uid']);

		// Read template file
		$this->template = $this->cObj->fileResource($this->conf['blogList.']['templateFile']);
	}

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	string
	 */
	function main($content, $conf)
	{
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->init();

		$query = 'SELECT uid FROM tt_content WHERE pid = ' . $this->page_uid . ' ' . $this->cObj->enableFields('tt_content') . ' ORDER BY sorting' ;
		$res = mysql(TYPO3_db, $query) ;

		$content = 'Typo3 Blog Single View' ;
		while ($row = mysql_fetch_assoc($res)) {
			$conf['tables'] = 'tt_content';
			$conf['source'] = intval($row['uid']);
			$conf['dontCheckPid'] = 1;

			$content .= $this->cObj->RECORDS($conf);
		}

		return $content;
	}

	/**
	 * THIS NICE PART IS FROM TYPO3 comments EXTENSION
	 * Merges TS configuration with configuration from flexform (latter takes precedence).
	 *
	 * @return	void
	 */
	function mergeConfiguration()
	{
		$this->pi_initPIflexForm();
	}

	/**
	 * THIS NICE FUNCTION IS FROM TYPO3 comments EXTENSION
	 * Fetches configuration value from flexform. If value exists, value in
	 * <code>$this->conf</code> is replaced with this value.
	 *
	 * @param	string	$param    Parameter name. If <code>.</code> is found, the first part is section name, second is key (applies only to $this->conf)
	 * @return	void
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
	 * Get all pages from current page_id as string (123,124,125)
	 *
	 * @return	string
	 */
	private function getPostByRootLine()
	{
		// Read all posts (pages with doktype 1) from rootline
		$this->cObj->data['recursive'] = 4;
		$pidList = $this->pi_getPidList(intval($this->page_uid), $this->cObj->data['recursive']);

		return $GLOBALS['TYPO3_DB']->cleanIntList($pidList);
	}

	/**
	 * Return the category name
	 *
	 * @param	int		$pid
	 * @param	string	$field
	 * @return	string
	 */
	private function getPostCategoryName($pid, $field = 'title')
	{
		$categoryPage_sql = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'uid=' . intval($pid), '', '');
		$categoryPage = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($categoryPage_sql);

		return $categoryPage[$field];
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/pi1/class.tx_typo3blog_listview.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/pi1/class.tx_typo3blog_listview.php']);
}

?>