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
 *   56: class tx_typo3blog_widget_listview extends tslib_pibase
 *   76:     private function init()
 *  109:     public function main($content, $conf)
 *  198:     private function mergeConfiguration()
 *  217:     private function fetchConfigValue($param)
 *  239:     private function getPageBrowseLimit()
 *  256:     private function getListGetPageBrowser($numberOfPages)
 *  279:     private function getNumberOfPostsInCategoryPage($page_id)
 *  304:     private function getPostByRootLine()
 *
 * TOTAL FUNCTIONS: 8
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('typo3_blog') . 'lib/class.typo3blog_func.php');
include_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_content.php');


/**
 * Plugin 'Typo3 Blog bloglist' for the 'typo3_blog' extension.
 *
 * @author			Roland Schmidt <rsch73@gmail.com>
 * @package			TYPO3
 * @subpackage		tx_typo3blog
 */
class tx_typo3blog_widget_bloglist extends tslib_pibase
{
	public $prefixId = 'tx_typo3blog_pi1'; // Same as class name
	public $scriptRelPath = 'widgets/bloglist/class.tx_typo3blog_widget_bloglist.php'; // Path to this script relative to the extension dir.
	public $extKey = 'typo3_blog'; // The extension key.
	public $pi_checkCHash = TRUE;
	private $envErrors = array();

	private $template = NULL;
	private $extConf = NULL;
	private $page_uid = NULL;
	private $blog_doktype_id = NULL;
	private $typo3BlogFunc = NULL;

	/**
	 * initializes this class
	 *
	 * @return	void
	 * @access private
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
		$this->template = $this->cObj->fileResource($this->conf['templateFile']);
	}

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content:		The PlugIn content
	 * @param	array		$conf:			The PlugIn configuration
	 * @return	string		$content:		The content that is displayed on the website
	 * @access public
	 */
	public function main($content, $conf)
	{
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->init();

		// Check the environment for typo3blog listview
		if (NULL === $this->template) {
			return $this->pi_wrapInBaseClass(
				"Error :Template file " . $this->conf['templateFile'] . " not found.<br />Please check the typoscript configuration!"
			);
		}

		if (!t3lib_div::testInt($this->blog_doktype_id)) {
			return $this->pi_wrapInBaseClass(
				"ERROR: doktype Id for page type blog not found.<br />Please set the doktype ID in extension conf!"
			);
		}

		// Get subparts from HTML template BLOGLIST_TEMPLATE
		$template = $this->cObj->getSubpart($this->template, '###BLOGLIST_TEMPLATE###');
		$subpartItem = $this->cObj->getSubpart($template, '###ITEM###');

		// Define array and vars for template
		$subparts = array();
		$subparts['###ITEM###'] = '';
		$markerArray = array();
		$markers = array();

		// Query to load current category page with all post pages in rootline
		$sql = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray(
			array(
				'SELECT'  => '*',
				'FROM'    => 'pages',
				'WHERE'   => 'pid IN (' . $this->getPostByRootLine() . ') '.$this->cObj->enableFields('pages').' AND doktype != ' . $this->blog_doktype_id . ' ' . $this->getWhereFilterQuery(),
				'GROUPBY' => '',
				'ORDERBY' => 'crdate DESC',
				'LIMIT'   => intval($this->getPageBrowseLimit()) . ',' . intval($this->conf['itemsToDisplay'])
			)
		);

		// Execute sql and set retrieved records in marker for bloglist
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql)) {
			$sql_user = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray(
				array(
					'SELECT' => '*',
					'FROM'   => 'be_users',
					'WHERE'  => "uid = '" . intval($row['tx_typo3blog_author']) . "' " . $this->cObj->enableFields('be_users'),
				)
			);
			// add additional data to ts template
			$row_user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql_user);
			$row['be_user_username']     = $row_user['username'];
			$row['be_user_realName']     = $row_user['realName'];
			$row['be_user_email']        = $row_user['email'];
			$row['be_user_email_secure'] = md5($row_user['email']);
			$row['category']             = $this->typo3BlogFunc->getPostCategoryName($row['pid'], 'title');
			$row['pagecontent']          = $this->typo3BlogFunc->getPageContent($row['uid'], $this->conf['contentItemsToDisplay']);
			$row['showmore']             = $GLOBALS['LANG']->sL('LLL:EXT:typo3_blog/pi1/locallang.xml:tx_typo3blog_widget_bloglist.showmore');
			$row['gravatar']             = NULL;
			$row['additionalheader']     = '';
			$row['additionalfooter']     = '';

			// add data to ts template
			$this->cObj->data = $row;

			// Each all records and set data in HTML template marker
			foreach ($row as $column => $data) {
				$this->cObj->setCurrentVal($data);
				if ($this->conf['marker.'][$column]) {
					$data = $this->cObj->cObjGetSingle($this->conf['marker.'][$column], $this->conf['marker.'][$column . '.']);
				}
				$this->cObj->setCurrentVal(false);
				$markerArray['###BLOGLIST_' . strtoupper($column) . '###'] = $data;
			}
			$subparts['###ITEM###'] .= $this->cObj->substituteMarkerArrayCached($subpartItem, $markerArray);
		}

		// Set pagebrowser marker from HTML Template
		$poststotal = intval($this->getNumberOfPostsInCategoryPage(intval($this->page_uid)));
		$itemstodisplay = intval($this->conf['itemsToDisplay']);

		// calc pages for pagebrowser
		$pagestodisplay = ($poststotal - ($poststotal % $itemstodisplay)) / $itemstodisplay + (($poststotal % $itemstodisplay) == 0 ? 0 : 1);
		$markers['###BLOGLIST_PAGEBROWSER###'] = $this->getListGetPageBrowser($pagestodisplay);

		// Complete the template expansion by replacing the "content" marker in the template
		$content = $this->typo3BlogFunc->substituteMarkersAndSubparts($template, $markers, $subparts);

		// wrap the content
		$content = $this->cObj->stdWrap($content, $this->conf['stdWrap.']);

		// Return the content to display in frontend
		return $content;
	}

	/**
	 * THIS NICE PART IS FROM TYPO3 comments EXTENSION
	 * Merges TS configuration with configuration from flexform (latter takes precedence).
	 *
	 * @return	void
	 * @access private
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
	 * @param	string		$param:		Parameter name. If <code>.</code> is found, the first part is section name, second is key (applies only to $this->conf)
	 * @return	void
	 * @access private
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
	 * @return	integer		$limit:		The limit as start limit for bloglist
	 * @access private
	 */
	private function getPageBrowseLimit()
	{
		if (!$this->piVars['page']) {
			$limit = 0;
		} else {
			$limit = $this->piVars['page'] * $this->conf['itemsToDisplay'];
		}
		return $limit;
	}

	/**
	 * Return pagebrowse
	 *
	 * @param	integer		$numberOfPages:		The number of display page
	 * @return	string		$content:			The HTML output from pagebrowse plugin
	 * @access private
	 */
	private function getListGetPageBrowser($numberOfPages)
	{
		$conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_pagebrowse_pi1.'];
		// Modify this configuration
		$conf['pageParameterName'] = $this->prefixId . '|page';
		$conf['numberOfPages'] = $numberOfPages;

		//echo "<pre>"; print_r($conf);exit;
		// Get page browser
		$this->cObj->start(array(), '');
		$content = $this->cObj->cObjGetSingle('USER', $conf);

		return $content;
	}

	/**
	 * Return the number of posts in category page
	 *
	 * @param	integer		$page_id:		The category page id
	 * @return	integer		$posts:			Count of current posts in category page
	 * @access private
	 */
	private function getNumberOfPostsInCategoryPage($page_id)
	{
		// Query to load all blog post pages in rootline from current category page
		$sql = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray(array(
			'SELECT'	=> '*',
			'FROM'		=> 'pages',
			'WHERE'		=> 'pid IN (' . $this->getPostByRootLine($page_id) . ') '.$this->cObj->enableFields('pages').' AND doktype != ' . $this->extConf["doktypeId"] . ' ' . $this->getWhereFilterQuery(),
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
	 * @return	string
	 * @access private
	 */
	private function getPostByRootLine()
	{
		// Read all post uid's from rootline by current category page
		$this->cObj->data['recursive'] = 4;
		$pidList = $this->pi_getPidList(intval($this->page_uid), $this->cObj->data['recursive']);

		// return the string with all uid's and clean up
		return $GLOBALS['TYPO3_DB']->cleanIntList($pidList);
	}

	/**
	 * Get the where clause to filter in bloglist
	 *
	 * @return	string
	 * @access public
	 */
	public function getWhereFilterQuery()
	{
		$where = '';
		// Get GET param tagsearch from url
		if (strlen($this->piVars['tagsearch']) > 0) {
			$tag = htmlspecialchars(trim($this->piVars['tagsearch']));

			// return the where query string
			$where .= " AND tx_typo3blog_tags LIKE '%".$tag."%'";
		}

		// Get GET param datefrom and dateto from url
		if (strlen($this->piVars['datefrom']) > 0 && strlen($this->piVars['dateto']) > 0) {
			$datefrom = htmlspecialchars(trim($this->piVars['datefrom']));
			$dateto   = htmlspecialchars(trim($this->piVars['dateto']));

			if (($datefrom != false) AND ($dateto != false)) {
				$where .= " AND DATE(FROM_UNIXTIME(crdate)) >= '".$datefrom."' AND DATE(FROM_UNIXTIME(crdate)) <= '".$dateto."'";
			}
		}

		// Return empty string if GET param tagsearch not exist
		return $where;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/widgets/bloglist/class.tx_typo3blog_widget_bloglist.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/widgets/bloglist/class.tx_typo3blog_widget_bloglist.php']);
}

?>