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
 *   59: class tx_typo3blog_listview extends tslib_pibase
 *   77:     private function init()
 *  108:     public function main($content, $conf)
 *  171:     private function getFilterQuery()
 *  182:     private function mergeConfiguration()
 *  201:     private function fetchConfigValue($param)
 *  224:     private function getContentElementsAsPreview($pid, $limit = 1)
 *  256:     private function getPageBrowseLimit()
 *  273:     private function getListGetPageBrowser($numberOfPages)
 *  294:     private function getNumberOfPosts($page_id)
 *  312:     private function getPostByRootLine()
 *  331:     private function substituteMarkersAndSubparts($template, array $markers, array $subparts)
 *
 * TOTAL FUNCTIONS: 11
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('typo3_blog') . 'lib/class.typo3blog_func.php');
include_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_content.php');


/**
 * Plugin 'Typo3 Blog ListView' for the 'typo3_blog' extension.
 *
 * @author    Roland Schmidt <rsch73@gmail.com>
 * @package    TYPO3
 * @subpackage    tx_typo3blog
 */
class tx_typo3blog_listview extends tslib_pibase
{
	public $prefixId = 'tx_typo3blog_pi1'; // Same as class name
	public $scriptRelPath = 'pi1/class.tx_typo3blog_listview.php'; // Path to this script relative to the extension dir.
	public $extKey = 'typo3_blog'; // The extension key.
	public $pi_checkCHash = TRUE;

	private $template = NULL;
	private $extConf = NULL;
	private $page_uid = NULL;
	private $blog_doktype_id = NULL;
	private $typo3BlogFunc = NULL;

	/**
	 * initializes this class
	 *
	 * @return    void
	 */
	private function init()
	{
		// Make instance of tslib_cObj
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');

		// Merge current configuration from flexform and typoscript
		$this->mergeConfiguration();

		// unserialize extension conf
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typo3_blog']);

		// Set current page id
		$this->page_uid = intval($GLOBALS['TSFE']->page['uid']);

		// Set doktype id from extension conf
		$this->blog_doktype_id = $this->extConf['doktypeId'];

		// Read template file
		$this->template = $this->cObj->fileResource($this->conf['blogList.']['templateFile']);

		// Make instance of tslib_cObj
		$this->typo3BlogFunc = t3lib_div::makeInstance('typo3blog_func');
	}

	/**
	 * The main method of the PlugIn
	 *
	 * @param    string        $content: The PlugIn content
	 * @param    array        $conf: The PlugIn configuration
	 * @return    string        content that is displayed on the website
	 */
	public function main($content, $conf)
	{
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->init();

		// Get subparts from HTML template BLOGLIST_TEMPLATE
		$template = $this->cObj->getSubpart($this->template, '###BLOGLIST_TEMPLATE###');
		$subpartItem = $this->cObj->getSubpart($template, '###ITEM###');

		// Define array and vars for template
		$subparts = array();
		$subparts['###ITEM###'] = '';
		$markerArray = array();
		$markers = array();

		// Query to load all blog pages
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			"*", "pages",
			"pid IN (" . $this->getPostByRootLine() . ")
			AND doktype != " . $this->blog_doktype_id . " AND hidden = 0 AND deleted = 0 " . $this->getFilterQuery() . "
			ORDER BY crdate DESC
			LIMIT " . intval($this->getPageBrowseLimit()) . "," . intval($this->conf['blogList.']['itemsToDisplay'])
		);


		// Set retrieved records in marker for bloglist
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$row['category'] = $this->typo3BlogFunc->getPostCategoryName($row['pid'], 'title');
			$row['pagecontent'] = $this->getContentElementsAsPreview($row['uid'], $this->conf['blogList.']['contentItemsToDisplay']);
			$this->cObj->data = $row;
			foreach ($row as $column => $value) {
				if ($this->conf['blogList.'][$column]) {
					$this->cObj->setCurrentVal($value);
					if ($column == 'pagecontent') {
						$value = $this->cObj->stdWrap($value, $this->conf['blogList.'][$column . '.']);
					} else {
						$value = $this->cObj->cObjGetSingle($this->conf['blogList.'][$column], $this->conf['blogList.'][$column . '.']);
					}

					$this->cObj->setCurrentVal(false);
				}
				else {
					$value = $this->cObj->stdWrap($value, $this->conf['blogList.'][$column . '.']);
				}
				$markerArray['###BLOGLIST_' . strtoupper($column) . '###'] = $value;
			}
			$subparts['###ITEM###'] .= $this->cObj->substituteMarkerArrayCached($subpartItem, $markerArray);
		}

		// Set pagebrowser marker from HTML Template
		$markers['###BLOGLIST_PAGEBROWSER###'] = $this->getListGetPageBrowser(
			intval($this->getNumberOfPosts(intval($GLOBALS['TSFE']->page['pid'])) / $this->conf['blogList.']['itemsToDisplay']) +
				((intval($this->getNumberOfPosts(intval($GLOBALS['TSFE']->page['pid']))) % $this->conf['blogList.']['itemsToDisplay']) == 0 ? 0 : 1)
		);

		// Complete the template expansion by replacing the "content" marker in the template
		$content = $this->substituteMarkersAndSubparts($template, $markers, $subparts);

		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * [Describe function...]
	 *
	 * @return    [type]        ...
	 */
	private function getFilterQuery()
	{
		return "";
	}

	/**
	 * THIS NICE PART IS FROM TYPO3 comments EXTENSION
	 * Merges TS configuration with configuration from flexform (latter takes precedence).
	 *
	 * @return    void
	 */
	private function mergeConfiguration()
	{
		$this->pi_initPIflexForm();

		$this->fetchConfigValue('blogList.itemsToDisplay');
		$this->fetchConfigValue('blogList.contentItemsToDisplay');
		$this->fetchConfigValue('blogList.viewcount');
		$this->fetchConfigValue('blogList.gravatar');
		$this->fetchConfigValue('blogList.showMore');
	}

	/**
	 * THIS NICE FUNCTION IS FROM TYPO3 comments EXTENSION
	 * Fetches configuration value from flexform. If value exists, value in
	 * <code>$this->conf</code> is replaced with this value.
	 *
	 * @param    string        $param    Parameter name. If <code>.</code> is found, the first part is section name, second is key (applies only to $this->conf)
	 * @return    void
	 */
	private function fetchConfigValue($param)
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
	 * Get tt_content elements for post page with a limit
	 *
	 * @param    $pid
	 * @param    int        $limit
	 * @return    string
	 */
	private function getContentElementsAsPreview($pid, $limit = 1)
	{
		if (!t3lib_div::testInt($limit)) {
			$limit = 1;
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			"uid", "tt_content",
			"pid = " . $pid . " LIMIT " . intval($limit)
		);

		$content = '';
		$i = 0;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if ($i == intval($limit)) {
				$i = 0;
				continue;
			}
			$conf['tables'] = 'tt_content';
			$conf['source'] = intval($row['uid']);
			$conf['dontCheckPid'] = 1;
			$content .= $this->cObj->RECORDS($conf);
			$i++;
		}

		return $content;
	}

	/**
	 * Return the start limit for pagebrowser
	 *
	 * @return    int
	 */
	private function getPageBrowseLimit()
	{
		if (!$_GET['tx_typo3blog_pi1']['page']) {
			$limit = 0;
		} else {
			$limit = $_GET['tx_typo3blog_pi1']['page'] * $this->conf['blogList.']['itemsToDisplay'];
		}

		return $limit;
	}

	/**
	 * Return pagebrowse
	 *
	 * @param    int        $numberOfPages
	 * @return    string
	 */
	private function getListGetPageBrowser($numberOfPages)
	{
		// Get default configuration
		$conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_pagebrowse_pi1.'];
		// Modify this configuration
		$conf += array(
			'pageParameterName' => $this->prefixId . '|page',
			'numberOfPages' => $numberOfPages,
		);
		// Get page browser
		$this->cObj->start(array(), '');

		return $this->cObj->cObjGetSingle('USER', $conf);
	}

	/**
	 * Return the number of posts in category page
	 *
	 * @param    integer        $page_id
	 * @return    integer
	 */
	private function getNumberOfPosts($page_id)
	{
		$sql = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			"*", "pages",
			"pid IN (" . $this->getPostByRootLine($page_id) . ")"
				. " AND doktype !=" . $this->extConf['doktypeId']
				. " ORDER BY crdate DESC"
		);
		$posts = $GLOBALS['TYPO3_DB']->sql_num_rows($sql);

		return $posts;
	}

	/**
	 * Get all pages from current page_id as string "123,124,125"
	 *
	 * @return    string
	 */
	private function getPostByRootLine()
	{
		// Read all posts (pages with doktype 1) from rootline
		$this->cObj->data['recursive'] = 4;
		$pidList = $this->pi_getPidList(intval($this->page_uid), $this->cObj->data['recursive']);

		return $GLOBALS['TYPO3_DB']->cleanIntList($pidList);
	}

	/**
	 * THIS NICE PART IS FROM TYPO3 comments EXTENSION
	 * Replaces $this->cObj->substituteArrayMarkerCached() because substitued
	 * function polutes cache_hash table a lot.
	 *
	 * @param    string        $template    Template
	 * @param    array        $markers    Markers
	 * @param    array        $subparts    Subparts
	 * @return    string        HTML
	 */
	private function substituteMarkersAndSubparts($template, array $markers, array $subparts)
	{
		$content = $this->cObj->substituteMarkerArray($template, $markers);
		if (count($subparts) > 0) {
			foreach ($subparts as $name => $subpart) {
				$content = $this->cObj->substituteSubpart($content, $name, $subpart);
			}
		}

		return $content;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/pi1/class.tx_typo3blog_listview.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/pi1/class.tx_typo3blog_listview.php']);
}

?>