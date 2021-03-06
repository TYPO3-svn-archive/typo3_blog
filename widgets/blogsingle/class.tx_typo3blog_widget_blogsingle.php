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
 *   51: class tx_typo3blog_widget_blogsingle extends tslib_pibase
 *   69:     private function init()
 *  105:     public function main($content, $conf)
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(t3lib_extMgm::extPath('typo3_blog') . 'lib/class.tx_typo3blog_func.php');
require_once(t3lib_extMgm::extPath('typo3_blog').'lib/class.typo3blog_pagerenderer.php');
include_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_content.php');


/**
 * Plugin 'Typo3 Blog' for the 'typo3_blog' extension.
 *
 * @author        Roland Schmidt <rsch73@gmail.com>
 * @package       TYPO3
 * @subpackage    tx_typo3blog
 */
class tx_typo3blog_widget_blogsingle extends tslib_pibase
{
	public $prefixId = 'tx_typo3blog_pi1'; // Same as class name
	public $scriptRelPath = 'widgets/blogsingle/class.tx_typo3blog_widget_blogsingle.php'; // Path to this script relative to the extension dir.
	public $extKey = 'typo3_blog'; // The extension key.
	public $pi_checkCHash = TRUE;

	private $template = NULL;
	private $extConf = NULL;
	private $page_uid = NULL;
	private $typo3BlogFunc = NULL;

	/**
	 * Initializes this class
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

		// Read template file
		$this->template = $this->cObj->fileResource($this->conf['templateFile']);

		// Make instance of tslib_cObj
		$this->typo3BlogFunc = t3lib_div::makeInstance('tx_typo3blog_func');
		$this->typo3BlogFunc->init(
			$this->cObj,
			$this->piVars);
	}

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content:      The PlugIn content
	 * @param	array		$conf:         The PlugIn configuration
	 * @return	string		$content:      That is displayed on the website
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

		// Get subparts from HTML template BLOGLIST_TEMPLATE
		$template = $this->cObj->getSubpart($this->template, '###BLOGSINGLE_TEMPLATE###');

		// Define array and vars for template
		$subparts = array();
		$markers = array();

		// Define return value
		$content = '';

		// Select the current blog post page
		$sql = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray(array(
				'SELECT'	=> 'pages.*',
				'FROM'		=> 'pages',
				'WHERE'		=> 'uid=' . intval($this->page_uid) . ' ' . $this->cObj->enableFields('pages'),
				'GROUPBY'	=> '',
				'ORDERBY'	=> 'sorting',
				'LIMIT'		=> ''
			)
		);

		// Execute the sql and each all selected fields
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql)) {
			if (is_array($row) && $this->typo3BlogFunc->getSysLanguageUid() > 0) {
				$row = $GLOBALS['TSFE']->sys_page->getPageOverlay($row, $this->typo3BlogFunc->getSysLanguageUid());
			}

			if (intval($row['tx_typo3blog_author'])) {
			$sql_user = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray(
				array(
					'SELECT'	=> '*',
					'FROM'		=> 'be_users',
					'WHERE'		=> "uid = ".intval($row['tx_typo3blog_author'])." ".$this->cObj->enableFields('be_users'),
				)
			);

			// Define additional fields for ts and add initialize this or add the content
			$row_user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql_user);
			$row['be_user_username']     = $row_user['username'];
			$row['be_user_realName']     = $row_user['realName'];
			$row['be_user_email']        = $row_user['email'];
			$row['be_user_email_secure'] = md5($row_user['email']);

			} else {
				$row['be_user_username']     = "";
				$row['be_user_realName']     = "";
				$row['be_user_email']        = "";
				$row['be_user_email_secure'] = md5("");
			}

			$row['category']			= $this->typo3BlogFunc->getPostCategoryName($row['pid'], 'title');
			$row['pagecontent']			= "";
			$row['comments']			= "";
			$row['gravatar']			= NULL;
			$row['additionalheader']	= '';
			$row['additionalfooter']	= '';

			// Add all fields in ts
			$this->cObj->data = $row;

			// Set all fields from ts in template marker
			foreach ($row as $column => $value) {
				$this->cObj->setCurrentVal($value);
				if ($this->conf['marker.'][$column]) {
					$value = $this->cObj->cObjGetSingle($this->conf['marker.'][$column], $this->conf['marker.'][$column . '.']);
				}
				$this->cObj->setCurrentVal(false);
				$markers['###' . strtoupper($column) . '###'] = $value;
			}
		}

		// Add all CSS and JS files
		if (T3JQUERY === true) {
			tx_t3jquery::addJqJS();
		} else {
			$this->pagerenderer->addJsFile($this->parentConf['jQueryLibrary']);
			$this->pagerenderer->addJsFile($this->parentConf['jQueryCookies']);
		}
		$this->pagerenderer->addResources();

		// Complete the template expansion by replacing the "content" marker in the template
		$content .= $this->typo3BlogFunc->substituteMarkersAndSubparts($template, $markers, $subparts);

		// Wrap the content and return the content to display in frontend
		if ($this->conf['baseWrap.'])  {
			return $this->cObj->stdWrap($content, $this->conf['baseWrap.']);
		} else {
			return $this->typo3BlogFunc->pi_wrapInBaseClass($content,"blogsingle-widget");
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/pi1/class.tx_typo3blog_widget_blogsingle.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/pi1/class.tx_typo3blog_widget_blogsingle.php']);
}

?>