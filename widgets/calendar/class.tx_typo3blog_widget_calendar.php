<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Juergen Furrer (juergen.furrer@gmail.com)
 *  All rights reserved
 *
 *  This script is part of the Typo3 project. The Typo3 project is
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
 *   52: class tx_typo3blog_widget_calendar extends tslib_pibase
 *   73:     private function init()
 *  111:     public function main($content, $conf)
 *  201:     private function getPostsInRootLine()
 *  225:     private function getBlogDates()
 *
 * TOTAL FUNCTIONS: 4
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('typo3_blog') . 'lib/class.tx_typo3blog_func.php');
require_once(t3lib_extMgm::extPath('typo3_blog').'lib/class.typo3blog_pagerenderer.php');
include_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_content.php');

/**
 * Plugin 'Typo3 Blog' for the 'typo3_blog' extension.
 *
 * @author        Juergen Furrer <juergen.furrer@gmail.com>
 * @package       TYPO3
 * @subpackage    tx_typo3blog
 */
class tx_typo3blog_widget_calendar extends tslib_pibase
{
	public $prefixId = 'tx_typo3blog_pi1';
	public $scriptRelPath = 'widgets/calendar/class.tx_typo3blog_widget_calendar.php';
	public $extKey = 'typo3_blog';
	public $pi_checkCHash = TRUE;

	private $template = NULL;
	private $extConf = NULL;
	private $page_uid = NULL;
	private $blog_doktype_id = NULL;
	private $typo3BlogFunc = NULL;
	private $parentConf = array();
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
		$this->page_uid = intval($this->parentConf['startPid']);

		// Set doktype id from extension conf
		$this->blog_doktype_id = $this->extConf['doktypeId'];

		// Read template file
		$this->template = $this->cObj->fileResource($this->conf['templateFile']);

		// Make instance of tslib_cObj
		$this->typo3BlogFunc = t3lib_div::makeInstance('tx_typo3blog_func');
		$this->typo3BlogFunc->init($this->cObj, $this->piVars);

	}

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content:      The PlugIn content
	 * @param	array		$conf:         The PlugIn configuration
	 * @return	string		$content:      That is displayed on the website
	 * @access public
	 */
	public function main($content, $conf) {
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

		// Get subparts from HTML template BLOGLIST_TEMPLATE
		$templateCode = $this->cObj->getSubpart($this->template, '###TEMPLATE_CALENDAR_JS###');

		// Generate the language string
		$language = $this->cObj->cObjGetSingle($this->conf['language'], $this->conf['language.']);
		// Language fallback, if not set, try to guess the language from config
		if (! $language) {
			$language = $GLOBALS['TSFE']->tmpl->setup['config.']['language'];
			if (! $language) {
				$language = "en";
			}
		}

		$markerArray = array();
		$markerArray["LANGUAGE"] = $language;
		$markerArray["DATE_FROM"] = $this->piVars['datefrom'];
		$markerArray["DATE_TO"]   = $this->piVars['dateto'];
		// $markerArray["DATE_TO"] = date("Y-m-d", time());
		$templateCode = $this->cObj->substituteMarkerArray($templateCode, $markerArray, '###|###', 0);

		$dateItem = trim($this->cObj->getSubpart($templateCode, "###DATES_ITEM###"));
		$sql = $this->getBlogDates();
		$dateArray = array();
		$key = 0;

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql)) {

			// add data to ts template
			$this->cObj->data = $row;

			$markerArray = array();
			$markerArray["KEY"] = $key;
			$markerArray["DATE"] = $row['day'];
			$markerArray["LINK"] = t3lib_div::getIndpEnv("TYPO3_SITE_URL") . $this->cObj->cObjGetSingle($this->conf['marker.']['link'], $this->conf['marker.']['link.']);
			$markerArray["COUNT"] = $row['counter'];
			$markerArray["DATE_TO"] = $this->piVars['dateto'];
			$class = '';

			if (strtotime($row['day']) >= strtotime($this->piVars['datefrom']) && strtotime($row['day']) <= strtotime($this->piVars['dateto'])) {
				$class = 'ui-state-highlight';
			}

			$markerArray["CLASS"] = $class;
			$dateArray[] = $this->cObj->substituteMarkerArray($dateItem, $markerArray, '###|###', 0);
			$key ++;
		}
		$templateCode = trim($this->cObj->substituteSubpart($templateCode, '###DATES_ITEM###', implode('', $dateArray), 0));

		// Add all CSS and JS files
		if (T3JQUERY === true) {
			tx_t3jquery::addJqJS();
		} else {
			$this->pagerenderer->addJsFile($this->parentConf['jQueryLibrary']);
			$this->pagerenderer->addJsFile($this->parentConf['jQueryUI']);
			$this->pagerenderer->addJsFile(str_replace('###LANGUAGE###', $language, $this->parentConf['jQueryUIl18n']));
		}
		$this->pagerenderer->addCssFile($this->parentConf['jQueryUIstyle']);
		$this->pagerenderer->addJS($templateCode);

		$this->pagerenderer->addResources();

		$content .= $this->cObj->cObjGetSingle($this->conf['datepicker'], $this->conf['datepicker.']);

		// Wrap the content and return the content to display in frontend
		if ($this->conf['baseWrap.'])  {
			return $this->cObj->stdWrap($content, $this->conf['baseWrap.']);
		} else {
			return $this->typo3BlogFunc->pi_wrapInBaseClass($content,"latestposts-widget");
		}

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
			// Read all post uid's from rootline by current category page
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
	 * Get all Dates with count of blogs
	 *
	 * @return	array
	 */
	private function getBlogDates() {
			$sql_array = array(
				'SELECT'	=> 'pages.uid,pages.l18n_cfg,DATE(FROM_UNIXTIME(pages.tx_typo3blog_create_datetime)) as day, count(*) AS counter',
				'FROM'		=> 'pages',
				'WHERE'		=> '('.$this->getPostsInRootLine().') '.$this->cObj->enableFields('pages').' AND doktype != '.$this->blog_doktype_id.' ' . $this->typo3BlogFunc->getWhereFilterQuery(),
				'GROUPBY'	=> 'day',
				'ORDERBY'	=> '',
				'LIMIT'		=> ''
			);

			if ($this->typo3BlogFunc->getSysLanguageUid() > 0 && $GLOBALS['TYPO3_CONF_VARS']['FE']['hidePagesIfNotTranslatedByDefault'] > 0) {
				$sql_array['SELECT']  = 'pages.uid,pages.l18n_cfg,DATE(FROM_UNIXTIME(pages_language_overlay.tx_typo3blog_create_datetime)) as day, count(*) AS counter';
				$sql_array['FROM']    = 'pages, pages_language_overlay';
				$sql_array['WHERE']   = 'pages_language_overlay.pid = pages.uid AND ('.$this->getPostsInRootLine().') '.$this->cObj->enableFields('pages').' AND pages.doktype != '.$this->blog_doktype_id.' ' . $this->typo3BlogFunc->getWhereFilterQuery();
				$sql_array['ORDERBY'] = '';
			}

			// Return count of result
			$rows = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($sql_array);

			return $rows;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/widgets/calendar/class.tx_typo3blog_widget_calendar.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/widgets/calendar/class.tx_typo3blog_widget_calendar.php']);
}

?>