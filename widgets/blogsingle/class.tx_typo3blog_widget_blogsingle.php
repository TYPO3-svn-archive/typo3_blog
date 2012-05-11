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
 *   52: class tx_typo3blog_widget_blogsingle extends tslib_pibase
 *   70:     private function init()
 *  100:     public function main($content, $conf)
 *  174:     private function mergeConfiguration()
 *  188:     private function fetchConfigValue($param)
 *
 * TOTAL FUNCTIONS: 4
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('typo3_blog') . 'lib/class.typo3blog_func.php');
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
		$this->template = $this->cObj->fileResource($this->conf['templateFile']);
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
				'SELECT'	=> '*',
				'FROM'		=> 'pages',
				'WHERE'		=> 'uid=' . intval($this->page_uid) . ' ' . $this->cObj->enableFields('pages'),
				'GROUPBY'	=> '',
				'ORDERBY'	=> 'sorting',
				'LIMIT'		=> ''
			)
		);

		// Execute the sql and each all selected fields
		while ($row = mysql_fetch_assoc($sql)) {

			// Define additional fields for ts and add initialize this or add the content
			$row['category'] = $this->typo3BlogFunc->getPostCategoryName($row['pid'], 'title');
			$row['pagecontent'] = "";
			$row['comments'] = "";


			// Add all fields in ts
			$this->cObj->data = $row;

			// Set all fields from ts in template marker
			foreach ($row as $column => $value) {
				if ($this->conf['blogList.']['marker.'][$column]) {
					$this->cObj->setCurrentVal($value);
					$value = $this->cObj->cObjGetSingle($this->conf['marker.'][$column], $this->conf['marker.'][$column . '.']);

					$this->cObj->setCurrentVal(false);
				}
				else {
					$value = $this->cObj->cObjGetSingle($this->conf['marker.'][$column], $this->conf['marker.'][$column . '.']);
				}
				$markers['###BLOGSINGLE_' . strtoupper($column) . '###'] = $value;
			}
		}

		// Complete the template expansion by replacing the "content" marker in the template
		$content .= $this->typo3BlogFunc->substituteMarkersAndSubparts($template, $markers, $subparts);

		// Return the content to display in frontend
		return $this->typo3BlogFunc->pi_wrapInBaseClass($content,$this->prefixId.'-widget_single');
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/pi1/class.tx_typo3blog_widget_blogsingle.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/pi1/class.tx_typo3blog_widget_blogsingle.php']);
}

?>