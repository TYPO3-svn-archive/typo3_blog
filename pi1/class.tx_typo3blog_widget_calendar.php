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
 *
 * TOTAL FUNCTIONS: 4
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('typo3_blog') . 'lib/class.typo3blog_func.php');
include_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_content.php');
require_once(t3lib_extMgm::extPath('typo3_blog').'lib/class.typo3blog_pagerenderer.php');

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
	public $scriptRelPath = 'pi1/class.tx_typo3blog_widget_calendar.php';
	public $extKey = 'typo3_blog';
	public $pi_checkCHash = TRUE;

	private $template = NULL;
	private $extConf = NULL;
	private $page_uid = NULL;
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
		$this->template = $this->cObj->fileResource($this->conf['blogList.']['templateFile']);
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

		$this->pagerenderer = t3lib_div::makeInstance('typo3blog_pagerenderer');
		$this->pagerenderer->setConf($this->conf);

		// Make instance of tslib_cObj
		$this->typo3BlogFunc = t3lib_div::makeInstance('typo3blog_func');
		$this->typo3BlogFunc->setCobj($this->cObj);

		// Merge current configuration from flexform and typoscript
		$this->mergeConfiguration();

		// unserialize extension conf
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typo3_blog']);

		// Set current page id
		$this->page_uid = intval($GLOBALS['TSFE']->page['uid']);

		// Read template file
		$this->template = $this->cObj->fileResource($this->conf['calendar.']['templateFile']);

		// Check the environment for typo3blog listview
		if (NULL === $this->template) {
			return $this->pi_wrapInBaseClass(
				"Error :Template file " . $this->conf['calendar.']['templateFile'] . " not found.<br />Please check the typoscript configuration!"
			);
		}

		// Get subparts from HTML template BLOGLIST_TEMPLATE
		$templateCode = $this->cObj->getSubpart($this->template, '###TEMPLATE_CALENDAR_JS###');

		// Generate the language string
		$language = $this->cObj->cObjGetSingle($this->conf['calendar.']['language'], $this->conf['calendar.']['language.']);
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
		$blogdates = $this->getBlogDates();

		$dateArray = array();
		$key = 0;

		if (count($blogdates) > 0) {
			foreach ($blogdates as $date) {
				$link = $this->pi_getPageLink($this->conf['startPid'], '', array(
					$this->prefixId.'[datefrom]' => $date['day'],
					$this->prefixId.'[dateto]'   => $date['day'],
				));
				$markerArray = array();
				$markerArray["KEY"] = $key;
				$markerArray["DATE"] = $date['day'];
				$markerArray["LINK"] = t3lib_div::getIndpEnv("TYPO3_SITE_URL") . $link;
				$markerArray["COUNT"] = $date['counter'];
				$class = '';
				if (strtotime($date['day']) >= strtotime($this->piVars['blogList']['datefrom']) && strtotime($date['day']) <= strtotime($this->piVars['blogList']['dateto'])) {
					$class = 'ui-state-highlight';
				}
				$markerArray["CLASS"] = $class;
				$dateArray[] = $this->cObj->substituteMarkerArray($dateItem, $markerArray, '###|###', 0);
				$key ++;
			}
		}
		$templateCode = trim($this->cObj->substituteSubpart($templateCode, '###DATES_ITEM###', implode('', $dateArray), 0));

		// Add all CSS and JS files
		if (T3JQUERY === true) {
			tx_t3jquery::addJqJS();
		} else {
			$this->pagerenderer->addJsFile($this->conf['calendar.']['jQueryLibrary']);
			$this->pagerenderer->addJsFile($this->conf['calendar.']['jQueryUI']);
			$this->pagerenderer->addJsFile(str_replace('###LANGUAGE###', $language, $this->conf['calendar.']['jQueryUIl18n']));
		}
		$this->pagerenderer->addCssFile($this->conf['calendar.']['jQueryUIstyle']);
		$this->pagerenderer->addJS($templateCode);

		$this->pagerenderer->addResources();

		$content = $this->cObj->cObjGetSingle($this->conf['calendar.']['datepicker'], $this->conf['calendar.']['datepicker.']);

		// Return the content to display in frontend
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Get all Dates with count of blogs
	 * 
	 * @return array
	 */
	private function getBlogDates() {
		$uids = $this->pi_getPidList($this->conf['startPid'], 100);
		if ($uids) {
			$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'count(*) AS counter, DATE(FROM_UNIXTIME(crdate)) as day',
				'pages',
				"uid IN (".$uids.") AND doktype != '".($this->extConf['doktypeId'] ? $this->extConf['doktypeId'] : 73)."'",
				'day',
				'',
				'',
				'day'
			);
			return $rows;
		}
		return array();
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
	}

	/**
	 * THIS NICE FUNCTION IS FROM TYPO3 comments EXTENSION
	 * Fetches configuration value from flexform. If value exists, value in
	 * <code>$this->conf</code> is replaced with this value.
	 *
	 * @param	string		$param:    Parameter name. If <code>.</code> is found, the first part is section name, second is key (applies only to $this->conf)
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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/pi1/class.tx_typo3blog_widget_calendar.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/pi1/class.tx_typo3blog_widget_calendar.php']);
}

?>