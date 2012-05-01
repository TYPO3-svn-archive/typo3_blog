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
 *   57: class tx_typo3blog_listview extends tslib_pibase
 *   76:     private function init()
 *  109:     public function main($content, $conf)
 *  184:     private function getFilterQuery()
 *  196:     private function mergeConfiguration()
 *  215:     private function fetchConfigValue($param)
 *  237:     private function getPageBrowseLimit()
 *  255:     private function getListGetPageBrowser($numberOfPages)
 *  278:     private function getNumberOfPostsInCategoryPage($page_id)
 *  300:     private function getPostByRootLine()
 *
 * TOTAL FUNCTIONS: 9
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('typo3_blog') . 'lib/class.typo3blog_func.php');
include_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_content.php');


/**
 * Plugin 'Typo3 Blog ListView' for the 'typo3_blog' extension.
 *
 * @author        Roland Schmidt <rsch73@gmail.com>
 * @package       TYPO3
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
	 * @access    private
	 * @return    void
	 */
	private function init()
	{
		// Make instance of tslib_cObj
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');

		// Make instance of tslib_cObj
		$this->typo3BlogFunc = t3lib_div::makeInstance('typo3blog_func');
		$this->typo3BlogFunc->setCobj($this->cObj);

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
	}

	/**
	 * The main method of the PlugIn
	 *
	 * @access    public
	 * @param     string    $content:    The PlugIn content
	 * @param     array     $conf:       The PlugIn configuration
	 * @return    string    $content:    The content that is displayed on the website
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

		// Query to load current category page with all post pages in rootline
		$sql = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray(array(
				'SELECT'	=> '*',
				'FROM'		=> 'pages',
				'WHERE'		=> 'pid IN (' . $this->getPostByRootLine() . ') AND hidden = 0 AND deleted = 0 AND doktype != ' . $this->blog_doktype_id . ' ' . $this->typo3BlogFunc->getTagCloudFilterQuery(),
				'GROUPBY'	=> '',
				'ORDERBY'	=> 'crdate DESC',
				'LIMIT'		=> intval($this->getPageBrowseLimit()) . ',' . intval($this->conf['blogList.']['itemsToDisplay'])
			)
		);

		// Execute sql and set retrieved records in marker for bloglist
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql)) {

			// add additional data to ts template
			$row['category'] = $this->typo3BlogFunc->getPostCategoryName($row['pid'], 'title');
			$row['pagecontent'] = $this->typo3BlogFunc->getPageContent($row['uid'], $this->conf['blogList.']['contentItemsToDisplay']);

			// add data to ts template
			$this->cObj->data = $row;

			// Each all records and set data in HTML template marker
			foreach ($row as $column => $data) {

				if ($this->conf['blogList.']['marker.'][$column]) {
					$this->cObj->setCurrentVal($data);
					if ($column == 'pagecontent') {
						$data = $this->cObj->stdWrap($data, $this->conf['blogList.']['marker.'][$column . '.']);
					} else {
						$data = $this->cObj->cObjGetSingle($this->conf['blogList.']['marker.'][$column], $this->conf['blogList.']['marker.'][$column . '.']);
					}
					$this->cObj->setCurrentVal(false);
				}
				else {
					$data = $this->cObj->stdWrap($data, $this->conf['blogList.']['marker.'][$column . '.']);
					$this->cObj->setCurrentVal(false);
				}
				$markerArray['###BLOGLIST_' . strtoupper($column) . '###'] = $data;
			}
			$subparts['###ITEM###'] .= $this->cObj->substituteMarkerArrayCached($subpartItem, $markerArray);
		}

		// Set pagebrowser marker from HTML Template
		$markers['###BLOGLIST_PAGEBROWSER###'] = $this->getListGetPageBrowser(
			intval($this->getNumberOfPostsInCategoryPage(intval($GLOBALS['TSFE']->page['pid'])) / $this->conf['blogList.']['itemsToDisplay']) +
			((intval($this->getNumberOfPostsInCategoryPage(intval($GLOBALS['TSFE']->page['pid']))) % $this->conf['blogList.']['itemsToDisplay']) == 0 ? 0 : 1)
		);

		// Complete the template expansion by replacing the "content" marker in the template
		$content = $this->typo3BlogFunc->substituteMarkersAndSubparts($template, $markers, $subparts);

		// Return the content to display in frontend
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * THIS NICE PART IS FROM TYPO3 comments EXTENSION
	 * Merges TS configuration with configuration from flexform (latter takes precedence).
	 *
	 * @access    private
	 * @return    void
	 */
	private function mergeConfiguration()
	{
		$this->pi_initPIflexForm();

		$this->fetchConfigValue('blogList.itemsToDisplay');
		$this->fetchConfigValue('blogList.contentItemsToDisplay');
		$this->fetchConfigValue('blogList.gravatar');
		$this->fetchConfigValue('blogList.showMore');
	}

	/**
	 * THIS NICE FUNCTION IS FROM TYPO3 comments EXTENSION
	 * Fetches configuration value from flexform. If value exists, value in
	 * <code>$this->conf</code> is replaced with this value.
	 *
	 * @access    private
	 * @param     string    $param:    Parameter name. If <code>.</code> is found, the first part is section name, second is key (applies only to $this->conf)
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
	 * Return the start limit for pagebrowser
	 *
	 * @access    private
	 * @return    int       $limit:    The limit as start limit for bloglist
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
	 * @access    private
	 * @param     int       $numberOfPages:    The number of display page
	 * @return    string    $content:          The HTML output from pagebrowse plugin
	 *
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
		$content = $this->cObj->cObjGetSingle('USER', $conf);

		return $content;
	}

	/**
	 * Return the number of posts in category page
	 *
	 * @access    private
	 * @param     int       $page_id:    The category page id
	 * @return    int       $posts:      Count of current posts in category page
	 */
	private function getNumberOfPostsInCategoryPage($page_id)
	{
		// Query to load all blog post pages in rootline from current category page
		$sql = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray(array(
			'SELECT'	=> '*',
			'FROM'		=> 'pages',
			'WHERE'		=> 'pid IN (' . $this->getPostByRootLine($page_id) . ') AND doktype != ' . $this->extConf["doktypeId"],
			'GROUPBY'	=> '',
			'ORDERBY'	=> 'crdate DESC',
			'LIMIT'		=> ''
			)
		);
		// Execute sql and count the result
		$posts = $GLOBALS['TYPO3_DB']->sql_num_rows($sql);

		// Return count of result
		return $posts;
	}

	/**
	 * Get all sub pages from current page_id as string "123,124,125"
	 *
	 * @access    private
	 * @return    string
	 */
	private function getPostByRootLine()
	{
		// Read all post uid's from rootline by current category page
		$this->cObj->data['recursive'] = 4;
		$pidList = $this->pi_getPidList(intval($this->page_uid), $this->cObj->data['recursive']);

		// return the string with all uid's and clean up
		return $GLOBALS['TYPO3_DB']->cleanIntList($pidList);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/pi1/class.tx_typo3blog_listview.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/pi1/class.tx_typo3blog_listview.php']);
}
?>