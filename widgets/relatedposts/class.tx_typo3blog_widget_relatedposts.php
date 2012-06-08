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
 *   52: class tx_typo3blog_widget_relatedposts extends tslib_pibase
 *   72:     private function init()
 *  105:     public function main($content, $conf)
 *  204:     private function getPostByRootLine()
 *  220:     private function getKeywordFilterQuery()
 *
 * TOTAL FUNCTIONS: 4
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('typo3_blog') . 'lib/class.typo3blog_func.php');
include_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_content.php');


/**
 * Plugin 'Typo3 Blog relatedposts' for the 'typo3_blog' extension.
 *
 * @author			Roland Schmidt <rsch73@gmail.com>
 * @package			TYPO3
 * @subpackage		tx_typo3blog
 */
class tx_typo3blog_widget_relatedposts extends tslib_pibase
{
	public $prefixId = 'tx_typo3blog_pi1'; // Same as class name
	public $scriptRelPath = 'widgets/bloglist/class.tx_typo3blog_widget_relatedposts.php'; // Path to this script relative to the extension dir.
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
	 * @return	void
	 * @access private
	 */
	private function init()
	{
		// Make instance of tslib_cObj
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');

		// get the startPid from PI1
		$this->parentConf = $GLOBALS["TSFE"]->tmpl->setup['plugin.']['tx_typo3blog_pi1.'];

		// Make instance of tslib_cObj
		$this->typo3BlogFunc = t3lib_div::makeInstance('typo3blog_func');
		$this->typo3BlogFunc->setCobj($this->cObj);

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
				"Error :Template file for relatedPosts widget not found.<br />Please check the typoscript configuration or constants!"
			);
		}

		if (!intval($this->blog_doktype_id)) {
			return $this->pi_wrapInBaseClass(
				"ERROR: doktype Id for page type blog not found.<br />Please set the doktype ID in extension conf!"
			);
		}

		// Get subparts from HTML template RELATEDPOSTS_TEMPLATE
		$template = $this->cObj->getSubpart($this->template, '###RELATEDPOSTS_TEMPLATE###');
		$subpartItem = $this->cObj->getSubpart($template, '###ITEM###');

		// Define array and vars for template
		$subparts = array();
		$subparts['###ITEM###'] = '';
		$markerArray = array();
		$markers = array();

		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
		$GLOBALS['TYPO3_DB']->debugOutput = TRUE;

		// Query to load current category page with all post pages in rootline
		$sql = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray(
			array(
				'SELECT'  => '*',
				'FROM'    => 'pages',
				'WHERE'   => 'uid IN (' . $this->getPostByRootLine() . ') '.$this->cObj->enableFields('pages').' AND doktype != ' . $this->blog_doktype_id . ' ' . $this->getKeywordFilterQuery(),
				'GROUPBY' => '',
				'ORDERBY' => 'tx_typo3blog_create_datetime DESC',
				'LIMIT'   => 5
			)
		);

		// Execute sql and set retrieved records from be_users
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql)) {
			//echo $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;exit;
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

		//additional header and footer in HTML Template marker
		$markers['###ADDITIONALHEADER###'] = $this->cObj->cObjGetSingle($this->conf['marker.']['additionalheader'], $this->conf['marker.']['additionalheader' . '.']);
		$markers['###ADDITIONALFOOTER###'] = $this->cObj->cObjGetSingle($this->conf['marker.']['additionalfooter'], $this->conf['marker.']['additionalfooter' . '.']);

		// Complete the template expansion by replacing the "content" marker in the template
		$content = $this->typo3BlogFunc->substituteMarkersAndSubparts($template, $markers, $subparts);

		// wrap the content
		$content = $this->cObj->stdWrap($content, $this->conf['stdWrap.']);

		// Return the content to display in frontend
		return $content;
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
		$pidList = $this->pi_getPidList(intval($this->conf['startPID']), $this->cObj->data['recursive']);
		$pidList = t3lib_div::rmFromList($this->page_uid, $pidList);

		// return the string with all uid's and clean up
		return $GLOBALS['TYPO3_DB']->cleanIntList($pidList);
	}

	/**
	 * Get the where clause to filter in bloglist
	 *
	 * @return	string
	 * @access public
	 */
	private function getKeywordFilterQuery()
	{
		$where = '';
		// ignore excluded page
		$where .= ' AND tx_typo3blog_exclude_page = 0';

		// Query to load current page with all post pages in rootline
		$currentPageSql = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray(
			array(
				'SELECT'  => 'uid,tx_typo3blog_tags',
				'FROM'    => 'pages',
				'WHERE'   => 'uid IN (' . $this->page_uid . ') '.$this->cObj->enableFields('pages').' AND doktype != ' . $this->blog_doktype_id,
				'GROUPBY' => '',
				'ORDERBY' => 'tx_typo3blog_create_datetime DESC',
				'LIMIT'   => 1
			)
		);
		$currentPage = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($currentPageSql);

		if ($currentPage['tx_typo3blog_tags']) {
			$tags = explode(',', trim($currentPage['tx_typo3blog_tags']));
			$where .= ' AND (';
			for ($i = 0; $i < count($tags); $i++) {
				if ($i < 1) {
					$andOr = '';
				} else {
					$andOr = 'OR';
				}

				// Trim tx_typo3blog_tags and replace " ," and ", " with "," for clean list without spaces
				$where .= " ". $andOr." FIND_IN_SET('".trim($tags[$i])."',TRIM(REPLACE(REPLACE(LOWER(tx_typo3blog_tags), ', ', ','), ' ,', ','))) > 0";
			}
			$where .= ")";
		}

		// Return empty string if GET param tagsearch not exist
		return $where;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/widgets/bloglist/class.tx_typo3blog_widget_bloglist.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/widgets/bloglist/class.tx_typo3blog_widget_bloglist.php']);
}

?>