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
 *   56: class tx_typo3blog_widget_tagcloud extends tslib_pibase
 *   80:     private function init()
 *  114:     public function main($content, $conf)
 *  208:     private function mergeConfiguration()
 *  222:     private function fetchConfigValue($param)
 *  244:     private function setTagcloudMax()
 *  257:     private function setTagcloudMin()
 *  271:     function calculateTagFontSize($count)
 *  289:     private function getPostByRootLine()
 *
 * TOTAL FUNCTIONS: 8
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
class tx_typo3blog_widget_tagcloud extends tslib_pibase
{
	public $prefixId = 'class.tx_typo3blog_widget_tagcloud'; // Same as class name
	public $scriptRelPath = 'pi1/class.tx_typo3blog_widget_tagcloud.php'; // Path to this script relative to the extension dir.
	public $extKey = 'typo3_blog'; // The extension key.
	public $pi_checkCHash = TRUE;

	private $tagcloud = array();
	private $maxcount = NULL;
	private $mincount = NULL;
	private $maxsize  = NULL;
	private $minsize = NULL;
	private $step = NULL;

	private $extConf = NULL;
	private $template = NULL;
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

		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typo3_blog']);

		// Merge current configuration from flexform and typoscript
		$this->mergeConfiguration();

		// Set doktype id from extension conf
		$this->blog_doktype_id = $this->extConf['doktypeId'];

		// Read template file
		$this->template = $this->cObj->fileResource($this->conf['tagcloud.']['templateFile']);

		$this->maxsize = 100;
		$this->minsize = 10;
		$this->step = 10;

	}

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content:    The PlugIn content
	 * @param	array		$conf:       The PlugIn configuration
	 * @return	string		$content:    The content that is displayed on the website
	 * @access public
	 */
	public function main($content, $conf)
	{
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->init();

		// Get subparts from HTML template BLOGLIST_TEMPLATE
		$template = $this->cObj->getSubpart($this->template, '###TAGCLOUD_TEMPLATE###');
		$subpartItem = $this->cObj->getSubpart($this->template, '###ITEM###');

		// Define array and vars for template
		$subparts = array();
		$subparts['###ITEM###'] = '';
		$markers=array();
		$markers['###TAGCLOUD_TITLE###'] = 'Tag Cloud';
		$markerArray = array(
			'###TAGCLOUD_COUNT###' => '',
			'###TAGCLOUD_NAME###' => '',
			'###TAGCLOUD_WEIGHT###' => '',
			'###TAGCLOUD_LINK###' => '',
		);

		// Query to load current category page with all post pages in rootline
		$sql = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray(array(
				'SELECT'	=> '*',
				'FROM'		=> 'pages',
				'WHERE'		=> 'pid IN (' . $this->getPostByRootLine() . ') AND hidden = 0 AND deleted = 0 AND doktype != ' . $this->blog_doktype_id,
				'GROUPBY'	=> '',
				'ORDERBY'	=> 'crdate DESC',
				'LIMIT'		=> ''
			)
		);

		// Execute sql and set retrieved records and extract the tagcloud values
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql)) {
			if (!empty($row['tx_typo3blog_tagcloud'])) {
				if (strstr($row['tx_typo3blog_tagcloud'], ',')) {
					$tags = explode(',',$row['tx_typo3blog_tagcloud']);
					foreach ($tags as $tag) {
						$this->tagcloud[] = strtolower(trim($tag));
					}
				} else {
					$this->tagcloud[] = strtolower(trim($row['tx_typo3blog_tagcloud']));
				}
			}
		}

		// Create counts for tagcloud values
		$this->tagcloud = array_count_values($this->tagcloud);

		// Set min and max count in tagcloud
		$this->setTagcloudMax();
		$this->setTagcloudMin();

		// Create tagcloud data array
		$tags = array();
		$i = 0;
		foreach ($this->tagcloud as $name => $count) {
			$tags[$i]['count']  = $count;
			$tags[$i]['name']   = $name;
			$tags[$i]['weight'] = $this->calculateTagFontSize($count);
			$tags[$i]['link']   = $this->cObj->getTypoLink_URL(intval($this->extConf['startPId']),array('tagsearch' => $name));
			$i++;
		}

		// Each tagcloud data array and add data in template
		foreach ($tags as $tag)
		{
			foreach ($tag as $column => $data) {
				if ($this->conf['tagcloud.']['marker.'][$column]) {
					$this->cObj->setCurrentVal($data);
					$data = $this->cObj->stdWrap($data, $this->conf['tagcloud.']['marker.'][$column . '.']);
					$this->cObj->setCurrentVal(false);
					$markerArray['###TAGCLOUD_' . strtoupper($column) . '###'] = $data;
				}
			}
			$subparts['###ITEM###'] .= $this->cObj->substituteMarkerArrayCached($subpartItem, $markerArray);
		}

		// Complete the template expansion by replacing the "content" marker in the template
		$content = $this->typo3BlogFunc->substituteMarkersAndSubparts($template, $markers, $subparts);

		// Return the content to display in frontend
		return $this->pi_wrapInBaseClass($content);
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

	/**
	 * Set the min count from all tagcloud entries
	 *
	 * @return	void
	 * @access private
	 */
	private function setTagcloudMax()
	{
		$tagcloud_temp = $this->tagcloud;
		asort($tagcloud_temp);
		$this->maxcount = array_pop($tagcloud_temp);
	}

	/**
	 * Set the min count from all tagcloud entries
	 *
	 * @return	void
	 * @access private
	 */
	private function setTagcloudMin()
	{
		$tagcloud_temp = $this->tagcloud;
		arsort($tagcloud_temp);
		$this->mincount = array_pop($tagcloud_temp);
	}

	/**
	 * Calc the font size for tagcloud
	 * possible value is 10,20,30,40,50,60,70,80,90,100
	 *
	 * @param	int		$count:   Count for tag in tagcloud
	 * @return	int		$weight:  Weight tag in tagloud
	 */
	function calculateTagFontSize($count) {

		//return floor(($this->minsize + ($count - $this->mincount) * $this->step)/10)*10;

		$treshold = ($this->maxsize-$this->minsize)/($this->step-1);
		$a = $step*log($count - $this->mincount+2)/log($this->maxcount - $this->mincount+2)-1;
		$weight = round(($this->minsize+round($a)*$treshold)/10)*10;;

		return $weight;
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
		$pidList = $this->pi_getPidList(intval(104), $this->cObj->data['recursive']);

		// return the string with all uid's and clean up
		return $GLOBALS['TYPO3_DB']->cleanIntList($pidList);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/pi1/class.tx_typo3blog_widget_tagcloud.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/pi1/class.tx_typo3blog_widget_tagcloud.php']);
}
?>