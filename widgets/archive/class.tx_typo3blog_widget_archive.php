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
 *   53: class tx_typo3blog_widget_archive extends tslib_pibase
 *   73:     private function init()
 *  110:     public function main($content, $conf)
 *  267:     private function getPostByRootLine()
 *  283:     public function getWhereFilterQuery()
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
 * Plugin 'Typo3 Blog Archive' for the 'typo3_blog' extension.
 *
 * @author			Roland Schmidt <rsch73@gmail.com>
 * @package			TYPO3
 * @subpackage		tx_typo3blog
 */
class tx_typo3blog_widget_archive extends tslib_pibase
{
	public $prefixId = 'tx_typo3blog_pi1';
	public $scriptRelPath = 'widgets/archive/class.tx_typo3blog_widget_archive.php';
	public $extKey = 'typo3_blog';
	public $pi_checkCHash = TRUE;

	private $template = NULL;
	private $extConf = NULL;
	private $startPid = NULL;
	private $blog_doktype_id = NULL;
	private $typo3BlogFunc = NULL;
	private $parentConf = array();

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

		// Make instance of tslib_cObj
		$this->typo3BlogFunc = t3lib_div::makeInstance('tx_typo3blog_func');
		$this->typo3BlogFunc->init($this->cObj,$this->piVars, $this->getPostsInRootLine);

		// unserialize extension conf
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typo3_blog']);

		// Set blog start page uid
		$this->startPid = intval($this->parentConf['startPid']);

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

		// Check the environment for typo3blog archive view
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
		$template   = $this->cObj->getSubpart($this->template, '###ARCHIVE_TEMPLATE###');
		$subpartArchiveItems = $this->cObj->getSubpart($template, '###ARCHIVE_ITEMS###');


		// Define array and vars for template
		$subparts = array();
		$subparts['###ARCHIVE_ITEMS###'] = '';
		$subparts['###ARCHIVE_YEAR###'] = '';
		$subparts['###ARCHIVE_MONTH_ITEMS###'] = '';
		$subparts['###ARCHIVE_MONTH###'] = '';
		$subparts['###ARCHIVE_POST###'] = '';

		$markerArray = array();
		$markers = array();

		$markers['###ARCHIVE_TITLE###'] = $this->cObj->cObjGetSingle($this->conf['marker.']['widgetTitle'], $this->conf['marker.']['widgetTitle.']);;

		$subpartArchiveYear = $this->cObj->getSubpart($subpartArchiveItems, '###ARCHIVE_YEAR###');
		$currentYear = NULL;

		$sql = $this->getDateFromPages();

		// Execute sql and set retrieved records in marker for bloglist
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql)) {
			if (is_array($row) && $this->typo3BlogFunc->getSysLanguageUid() > 0) {
				$row = $GLOBALS['TSFE']->sys_page->getPageOverlay($row, $GLOBALS['TSFE']->sys_language_uid);
			}

			// add data to ts template
			$this->cObj->data = $row;
			$this->cObj->data['datefrom'] = date(mktime(0, 0, 0, $row['month'],   1, $row['year']));
			$this->cObj->data['dateto']   = date(mktime(0, 0, 0, $row['month']+1, 0, $row['year']));

			// Get data year, month and count
			if ($currentYear != $row['year']) {
				if ($currentYear != NULL) {
					$subparts['###ARCHIVE_ITEMS###'] .=  $this->cObj->substituteSubpartArray($subpartArchiveItems, $subparts);
				}
				// Set Year in template
				$currentYear = $row['year'];
				$year = $this->cObj->cObjGetSingle($this->conf['marker.']['year'], $this->conf['marker.']['year.']);
				$markerArray['###' . strtoupper('year') . '###'] = $year;
				$subparts['###ARCHIVE_YEAR###'] = $this->cObj->substituteMarkerArrayCached($subpartArchiveYear, $markerArray);

				// Get subpart month items
				$subpartArchiveMonthItems = $this->cObj->getSubpart($subpartArchiveYear, '###ARCHIVE_MONTH_ITEMS###');

				// clear data ARCHIVE_MONTH_ITEMS for the next year run
				$subparts['###ARCHIVE_MONTH_ITEMS###'] = "";
			}

			$subpartArchiveMonth = $this->cObj->getSubpart($subpartArchiveYear, '###ARCHIVE_MONTH###');
			$month = $this->cObj->cObjGetSingle($this->conf['marker.']['month'], $this->conf['marker.']['month.']);
			$quantity = $this->cObj->cObjGetSingle($this->conf['marker.']['quantity'], $this->conf['marker.']['quantity' . '.']);

			// Add data in $markerArray for subpart ARCHIVE_YEAR
			$markerArray['###' . strtoupper('month') . '###'] = $month;
			$markerArray['###' . strtoupper('quantity') . '###'] = $quantity;

			// Set Data in subpart Template
			$subparts['###ARCHIVE_MONTH###'] = $this->cObj->substituteMarkerArrayCached($subpartArchiveMonth, $markerArray);

			// Query to load pages for archive
			$sqlquery = $this->getArchivePostPages($row);

			// Each all post from result $sqlquery
			while ($res = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sqlquery)) {
				if (is_array($res) && $this->typo3BlogFunc->getSysLanguageUid() > 0) {
					$res = $GLOBALS['TSFE']->sys_page->getPageOverlay($res, $GLOBALS['TSFE']->sys_language_uid);
				}

				// add data to ts template
				$this->cObj->data = $res;
				$subpartArchivePost = $this->cObj->getSubpart($subpartArchiveMonth, '###ARCHIVE_POST###');

				// Each all records and set data in HTML template marker
				foreach ($res as $column => $data) {
					if ($this->conf['marker.'][$column]) {
						$this->cObj->setCurrentVal($data);
						$data = $this->cObj->cObjGetSingle($this->conf['marker.'][$column], $this->conf['marker.'][$column . '.']);
						$this->cObj->setCurrentVal(false);
					} else {
						$this->cObj->setCurrentVal($data);
						$data = $this->cObj->stdWrap($data, $this->conf['marker.'][$column . '.']);
						$this->cObj->setCurrentVal(false);
					}
					$markerArray['###' . strtoupper($column) . '###'] = $data;
				}
				$subparts['###ARCHIVE_POST###'] .= $this->cObj->substituteMarkerArrayCached($subpartArchivePost, $markerArray);
			}

			$subparts['###ARCHIVE_MONTH_ITEMS###'] .=  $this->cObj->substituteSubpartArray($subpartArchiveMonthItems, $subparts);
			$subparts['###ARCHIVE_POST###'] = '';
		}
		$subparts['###ARCHIVE_ITEMS###'] .=  $this->cObj->substituteSubpartArray($subpartArchiveItems, $subparts);

		// Add all CSS and JS files
		if (T3JQUERY === true) {
			tx_t3jquery::addJqJS();
		} else {
			$this->pagerenderer->addJsFile($this->parentConf['jQueryLibrary']);
			$this->pagerenderer->addJsFile($this->parentConf['jQueryCookies']);
		}
		$this->pagerenderer->addJsFile($this->parentConf['jQueryTreeView']);
		$this->pagerenderer->addCssFile($this->parentConf['jQueryTreeViewStyle']);

		$templateJs = $this->cObj->getSubpart($this->template, '###ARCHIVE_TEMPLATE_JS###');
		$this->pagerenderer->addJS($templateJs);

		$this->pagerenderer->addResources();

		// Complete the template expansion by replacing the "content" marker in the template
		$content .= $this->typo3BlogFunc->substituteMarkersAndSubparts($template, $markers, $subparts);

		// wrap the content
		$content = $this->cObj->stdWrap($content, $this->conf['stdWrap.']);

		// Return the content to display in frontend
		return $content;
	}

	/**
	 * Return all Post pages for archive
	 *
	 * @return array()
	 * @access private
	 */
	private function getArchivePostPages($row)
	{
		$sql_array = array(
			'SELECT'	=> 'pages.*',
			'FROM'		=> 'pages',
			'WHERE'		=> '('.$this->getPostsInRootLine().') AND pages.doktype != '.$this->blog_doktype_id.' AND MONTH(FROM_UNIXTIME(pages.tx_typo3blog_create_datetime)) = '.intval($row['month']) . ' AND YEAR(FROM_UNIXTIME(pages.tx_typo3blog_create_datetime)) = ' . intval($row['year']) .$this->cObj->enableFields('pages') . ' '.$this->typo3BlogFunc->getWhereFilterQuery(),
			'GROUPBY'	=> '',
			'ORDERBY'	=> 'tx_typo3blog_create_datetime DESC',
			'LIMIT'		=> ''
		);

		if ($this->typo3BlogFunc->getSysLanguageUid() > 0  && $GLOBALS['TYPO3_CONF_VARS']['FE']['hidePagesIfNotTranslatedByDefault'] > 0) {
			$sql_array['FROM'] = 'pages, pages_language_overlay';
			$sql_array['WHERE'] = 'pages_language_overlay.pid = pages.uid AND ('.$this->getPostsInRootLine().') AND pages.doktype != '.$this->blog_doktype_id.' AND MONTH(FROM_UNIXTIME(pages.tx_typo3blog_create_datetime)) = '.intval($row['month']) . ' AND YEAR(FROM_UNIXTIME(pages.tx_typo3blog_create_datetime)) = ' . intval($row['year']) .$this->cObj->enableFields('pages') . ' '.$this->typo3BlogFunc->getWhereFilterQuery();
			$sql_array['ORDERBY'] = 'pages_language_overlay.tx_typo3blog_create_datetime DESC';
		}

		$sql = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($sql_array);

		return $sql;
	}

	/**
	 * Return the Date from all Post pages
	 *
	 * @return array()
	 * @access private
	 */
	private function getDateFromPages()
	{
		$sql_array = array(
			'SELECT'  => 'MONTH(FROM_UNIXTIME(pages.tx_typo3blog_create_datetime)) as month, YEAR(FROM_UNIXTIME(pages.tx_typo3blog_create_datetime)) as year, count(*) as quantity, pages.tx_typo3blog_create_datetime',
			'FROM'    => 'pages',
			'WHERE'   => '('.$this->getPostsInRootLine().') '.$this->cObj->enableFields('pages').' AND pages.doktype != ' . $this->blog_doktype_id . ' ' . $this->typo3BlogFunc->getWhereFilterQuery(),
			'GROUPBY' => 'year, month',
			'ORDERBY' => 'pages.tx_typo3blog_create_datetime DESC',
			'LIMIT'   => ''
		);

		if ($this->typo3BlogFunc->getSysLanguageUid() > 0  && $GLOBALS['TYPO3_CONF_VARS']['FE']['hidePagesIfNotTranslatedByDefault'] > 0) {
			$sql_array['FROM'] = 'pages, pages_language_overlay';
			$sql_array['WHERE'] = 'pages_language_overlay.pid = pages.uid AND ('.$this->getPostsInRootLine().') '.$this->cObj->enableFields('pages').' AND pages.doktype != ' . $this->blog_doktype_id . ' ' . $this->typo3BlogFunc->getWhereFilterQuery();
			$sql_array['ORDERBY'] = 'pages_language_overlay.tx_typo3blog_create_datetime DESC';
		}

		$sql = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($sql_array);

		return $sql;
	}

	/**
	 * Get all post pages from current startpid as sql string
	 *
	 * @return	string
	 * @access private
	 */
	private function getPostsInRootLine()
	{
		// Read all post uid's from rootline by current category page
		$this->cObj->data['recursive'] = 4;
		$pidList = $this->pi_getPidList(intval($this->startPid), $this->cObj->data['recursive']);
		$addWhereParts = array();
		$pidArray = explode(',', $GLOBALS['TYPO3_DB']->cleanIntList($pidList));
		foreach ($pidArray as $pid) {
			$addWhereParts[] = "pages.uid = {$pid}";
		}
		$pidWhere = implode(' OR ', $addWhereParts);

		return $pidWhere;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/widgets/archive/class.tx_typo3blog_widget_archive.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/widgets/archive/class.tx_typo3blog_widget_archive.php']);
}

?>