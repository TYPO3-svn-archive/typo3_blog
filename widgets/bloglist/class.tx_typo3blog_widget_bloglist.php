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
 *   55: class tx_typo3blog_widget_bloglist extends tslib_pibase
 *   75:     private function init()
 *  112:     public function main($content, $conf)
 *  226:     private function getPageBrowseLimit()
 *  243:     private function getListGetPageBrowser($numberOfPages)
 *  264:     private function getNumberOfPostsInCategoryPage($page_id)
 *  289:     private function getPostByRootLine()
 *  305:     public function getWhereFilterQuery()
 *
 * TOTAL FUNCTIONS: 7
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('typo3_blog') . 'lib/class.tx_typo3blog_func.php');
require_once(t3lib_extMgm::extPath('typo3_blog').'lib/class.typo3blog_pagerenderer.php');
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
	private $template = NULL;
	private $extConf = NULL;
	private $page_uid = NULL;
	private $blog_doktype_id = NULL;
	private $typo3BlogFunc = NULL;

	private $postsInRootLine = NULL;

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

		// get the startPid from PI1
		$this->parentConf = $GLOBALS["TSFE"]->tmpl->setup['plugin.']['tx_typo3blog_pi1.'];

		// define the pagerenderer
		$this->pagerenderer = t3lib_div::makeInstance('typo3blog_pagerenderer');
		$this->pagerenderer->setConf($this->conf);

		// unserialize extension conf
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typo3_blog']);

		// Set current page id
		$this->page_uid = intval($GLOBALS['TSFE']->page['uid']);

		// Set doktype id from extension conf
		$this->blog_doktype_id = $this->extConf['doktypeId'];

		// Read template file
		$this->template = $this->cObj->fileResource($this->conf['templateFile']);

		// Make instance of tslib_cObj
		$this->typo3BlogFunc = t3lib_div::makeInstance('tx_typo3blog_func');
		$this->typo3BlogFunc->init(
			$this->cObj,
			$this->piVars,
			$this->getPostsInRootLine());
	}

	/**
	 * The main method of the Plugin
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

		if (!intval($this->blog_doktype_id)) {
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
		$sql = $this->findBlogListPages();

		// Execute sql and set retrieved records in marker for bloglist
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql)) {
			if (is_array($row) && $this->typo3BlogFunc->getSysLanguageUid() > 0) {
				$row = $GLOBALS['TSFE']->sys_page->getPageOverlay($row, $this->typo3BlogFunc->getSysLanguageUid());
			}

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
			$row['pagecontent']          = NULL;
			$row['showmore']             = NULL;
			$row['gravatar']             = NULL;

			// add data to ts template
			$this->cObj->data = $row;

			// Each all records and set data in HTML template marker
			foreach ($row as $column => $data) {
				$this->cObj->setCurrentVal($data);
				if ($this->conf['marker.'][$column]) {
					$data = $this->cObj->cObjGetSingle($this->conf['marker.'][$column], $this->conf['marker.'][$column . '.']);
				}
				$this->cObj->setCurrentVal(false);
				$markerArray['###' . strtoupper($column) . '###'] = $data;
			}
			$subparts['###ITEM###'] .= $this->cObj->substituteMarkerArrayCached($subpartItem, $markerArray);
		}

		// Set pagebrowser marker in HTML Template marker
		$poststotal = $this->getCountOfPosts();
		$itemstodisplay = intval($this->conf['itemsToDisplay']);

		//additional header and footer in HTML Template marker
		$markers['###ADDITIONALHEADER###'] = $this->cObj->cObjGetSingle($this->conf['marker.']['additionalheader'], $this->conf['marker.']['additionalheader' . '.']);
		$markers['###ADDITIONALFOOTER###'] = $this->cObj->cObjGetSingle($this->conf['marker.']['additionalfooter'], $this->conf['marker.']['additionalfooter' . '.']);

		// calc pages for pagebrowser
		$pagestodisplay = ($poststotal - ($poststotal % $itemstodisplay)) / $itemstodisplay + (($poststotal % $itemstodisplay) == 0 ? 0 : 1);
		$markers['###PAGEBROWSER###'] = $this->getListGetPageBrowser($pagestodisplay);

		// Add all CSS and JS files
		if (T3JQUERY === true) {
			tx_t3jquery::addJqJS();
		} else {
			$this->pagerenderer->addJsFile($this->parentConf['jQueryLibrary']);
			$this->pagerenderer->addJsFile($this->parentConf['jQueryCookies']);
		}
		$this->pagerenderer->addResources();

		// Complete the template expansion by replacing the "content" marker in the template
		$content = $this->typo3BlogFunc->substituteMarkersAndSubparts($template, $markers, $subparts);

		// wrap the content
		$content = $this->cObj->stdWrap($content, $this->conf['stdWrap.']);

		// Return the content to display in frontend
		return $content;
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

		// Get page browser
		$this->cObj->start(array(), '');
		$content = $this->cObj->cObjGetSingle('USER', $conf);

		return $content;
	}

	/**
	 * Get all sub pages from current page_id as string "123,124,125"
	 *
	 * @return	string
	 * @access private
	 */
	private function getPostsInRootLine()
	{
		if (is_null($this->postsInRootLine)) {
			$this->cObj->data['recursive'] = 4;
			$pidList = $this->pi_getPidList(intval($this->page_uid), $this->cObj->data['recursive']);
			$addWhereParts = array();
			$pidArray = explode(',', $GLOBALS['TYPO3_DB']->cleanIntList($pidList));
			foreach ($pidArray as $pid) {
				$addWhereParts[] = "pages.uid = {$pid}";
			}

			$this->postsInRootLine = implode(' OR ', $addWhereParts);
			return $this->postsInRootLine;
		} else {
			return $this->postsInRootLine;
		}
	}

	/**
	 * Return a Posts of current category Page
	 *
	 * @return array
	 * @access public
	 */
	private function findBlogListPages()
	{
		$sql_array = array(
			'SELECT'  => 'pages.*',
			'FROM'    => 'pages',
			'WHERE'   => '('.$this->getPostsInRootLine().') '.$this->cObj->enableFields('pages').' AND pages.doktype != ' . $this->blog_doktype_id . ' ' . $this->typo3BlogFunc->getWhereFilterQuery().' '.$this->getDateWhere(),
			'GROUPBY' => '',
			'ORDERBY' => 'pages.tx_typo3blog_create_datetime DESC',
			'LIMIT'   => intval($this->getPageBrowseLimit()) . ',' . intval($this->conf['itemsToDisplay'])
		);

		if ($this->typo3BlogFunc->getSysLanguageUid() > 0 && $GLOBALS['TYPO3_CONF_VARS']['FE']['hidePagesIfNotTranslatedByDefault'] > 0) {
			$sql_array['FROM'] = 'pages, pages_language_overlay';
			$sql_array['WHERE'] = 'pages_language_overlay.pid = pages.uid AND ('.$this->getPostsInRootLine().') '.$this->cObj->enableFields('pages').' AND pages.doktype != ' . $this->blog_doktype_id . ' ' . $this->typo3BlogFunc->getWhereFilterQuery().' '.$this->getDateWhere();
			$sql_array['ORDERBY'] = 'pages_language_overlay.tx_typo3blog_create_datetime DESC';
		}

		$sql = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($sql_array);

		return $sql;
	}

	/**
	 * Return the start limit for pagebrowser
	 *
	 * @return	integer		$limit:		The limit as start limit for bloglist
	 * @access	private
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

	public function getCountOfPosts() {
		$sql_array = array(
			'SELECT'	=> 'pages.uid',
			'FROM'		=> 'pages',
			'WHERE'		=> '('.$this->getPostsInRootLine().') '.$this->cObj->enableFields('pages').' AND pages.doktype != '.$this->blog_doktype_id.' ' . $this->typo3BlogFunc->getWhereFilterQuery().' '.$this->getDateWhere(),
			'GROUPBY'	=> '',
			'ORDERBY'	=> '',
			'LIMIT'		=> ''
		);

		if ($this->typo3BlogFunc->getSysLanguageUid() > 0 && $GLOBALS['TYPO3_CONF_VARS']['FE']['hidePagesIfNotTranslatedByDefault'])	{
			$sql_array['FROM']    = 'pages, pages_language_overlay';
			$sql_array['WHERE']   = 'pages_language_overlay.pid = pages.uid AND ('.$this->getPostsInRootLine().') '.$this->cObj->enableFields('pages').' AND pages.doktype != ' . $this->blog_doktype_id . ' ' . $this->typo3BlogFunc->getWhereFilterQuery().' '.$this->getDateWhere();
			$sql_array['ORDERBY'] = '';
		}

		// Return count of result
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($sql_array);
		$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);

		return intval($count);
	}

	/**
	 * Return SQL date where
	 *
	 * @return	$where		SQL datefrom and dateto
	 * @access private
	 */
	private function getDateWhere()	{
		$where = '';
		// Get GET param datefrom and dateto from url
		if (strlen($this->piVars['datefrom']) > 0 && strlen($this->piVars['dateto']) > 0) {
			if ($this->typo3BlogFunc->getSysLanguageUid() > 0 && $GLOBALS['TYPO3_CONF_VARS']['FE']['hidePagesIfNotTranslatedByDefault']) {
				$datefrom = $GLOBALS['TYPO3_DB']->quoteStr(trim($this->piVars['datefrom']), 'pages_language_overlay');
				$dateto   = $GLOBALS['TYPO3_DB']->quoteStr(trim($this->piVars['dateto']), 'pages_language_overlay');
			} else {
				$datefrom = $GLOBALS['TYPO3_DB']->quoteStr(trim($this->piVars['datefrom']), 'pages');
				$dateto   = $GLOBALS['TYPO3_DB']->quoteStr(trim($this->piVars['dateto']), 'pages');
			}

			if (($datefrom != false) && ($dateto != false)) {
				if ($this->typo3BlogFunc->getSysLanguageUid() > 0) {
					$where .= " AND DATE(FROM_UNIXTIME(pages_language_overlay.tx_typo3blog_create_datetime)) >= '".$datefrom."' AND DATE(FROM_UNIXTIME(pages_language_overlay.tx_typo3blog_create_datetime)) <= '".$dateto."'";
				} else {
					$where .= " AND DATE(FROM_UNIXTIME(tx_typo3blog_create_datetime)) >= '".$datefrom."' AND DATE(FROM_UNIXTIME(tx_typo3blog_create_datetime)) <= '".$dateto."'";
				}
			}
		}
		return $where;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/widgets/bloglist/class.tx_typo3blog_widget_bloglist.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/widgets/bloglist/class.tx_typo3blog_widget_bloglist.php']);
}

?>