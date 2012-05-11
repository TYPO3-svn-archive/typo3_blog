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
 *   49: class typo3blog_func
 *   60:     public function setCobj(tslib_cObj $cObj)
 *   76:     public function substituteMarkersAndSubparts($template, array $markers, array $subparts)
 *   96:     public function getPageContent($id, $limit)
 *  133:     public function getPostCategoryName($pid, $field = 'title')
 *  158:     public function getTagCloudFilterQuery()
 *
 * TOTAL FUNCTIONS: 5
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * Plugin 'Typo3 Blog lib' for the 'typo3_blog' extension.
 *
 * @author        Roland Schmidt <rsch73@gmail.com>
 * @package       TYPO3
 * @subpackage    tx_typo3blog
 */
class typo3blog_func
{
	private $cObj = NULL;

	/**
	 * Set the tslib_cObj class
	 *
	 * @param	object		$cObj		tslib_cObj class
	 * @return	void
	 * @access public
	 */
	public function setCobj(tslib_cObj $cObj)
	{
		$this->cObj = $cObj;
	}

	/**
	 * THIS NICE PART IS FROM TYPO3 comments EXTENSION
	 * Replaces $this->cObj->substituteArrayMarkerCached() because substitued
	 * function polutes cache_hash table a lot.
	 *
	 * @param	string		$template:		Template
	 * @param	array		$markers:		Markers
	 * @param	array		$subparts:		Subparts
	 * @return	string		$content:		HTML
	 * @access public
	 */
	public function substituteMarkersAndSubparts($template, array $markers, array $subparts)
	{
		$content = $this->cObj->substituteMarkerArray($template, $markers);
		if (count($subparts) > 0) {
			foreach ($subparts as $name => $subpart) {
				$content = $this->cObj->substituteSubpart($content, $name, $subpart);
			}
		}

		return $content;
	}

	/**
	 * Retrieve all records from tt_content by current page uid
	 *
	 * @param	integer		$id:		Page uid from blog post page
	 * @param	integer		$limit:		Limit to display content elements on list view
	 * @return	string
	 * @access public
	 */
	public function getPageContent($id, $limit)
	{
		// Define return value
		$content = '';

		// Select the uid from tt_content
		$sql = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray(array(
				'SELECT'	=> 'uid',
				'FROM'		=> 'tt_content',
				'WHERE'		=> 'pid=' . intval($id) . ' ' . $this->cObj->enableFields('tt_content'),
				'GROUPBY'	=> '',
				'ORDERBY'	=> 'sorting',
				'LIMIT'		=> intval($limit)
			)
		);

		// Execute sql and add tt_content entries in cObj RECORDS in return value $content
		while ($row = mysql_fetch_assoc($sql)) {
			$conf['tables'] = 'tt_content';
			$conf['source'] = intval($row['uid']);
			$conf['dontCheckPid'] = 1;
			$content .= $this->cObj->RECORDS($conf);
		}

		// return the content
		return $content;
	}

	/**
	 * Return the category name from parent page
	 * The parent page is the category page
	 *
	 * @param	integer		$pid:			Page ID from category page
	 * @param	string		$field:			Column from table pages
	 * @return	string		$page[$field]:	Value from field in table pages
	 * @access public
	 */
	public function getPostCategoryName($pid, $field = 'title')
	{
		// Select record from category page
		$sql = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray(array(
				'SELECT'	=> '*',
				'FROM'		=> 'pages',
				'WHERE'		=> 'uid=' . intval($pid) . ' '.$this->cObj->enableFields('pages'),
				'GROUPBY'	=> '',
				'ORDERBY'	=> '',
				'LIMIT'		=> ''
			)
		);
		// Execute SQL
		$page = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql);

		// return $field from result
		return $page[$field];
	}



	/**
	 * Returns the version of an extension (in 4.4 its possible to this with t3lib_extMgm::getExtensionVersion)
	 * @param string $key
	 * @return string
	 */
	public function getExtensionVersion($key) {
		if (! t3lib_extMgm::isLoaded($key)) {
			return '';
		}
		$_EXTKEY = $key;
		include(t3lib_extMgm::extPath($key) . 'ext_emconf.php');
		return $EM_CONF[$key]['version'];
	}

	/**
	 * Wraps the input string in a <div> tag with the class attribute set to the prefixId.
	 * All content returned from your plugins should be returned through this function so all content from your plugin is encapsulated in a <div>-tag nicely identifying the content of your plugin.
	 *
	 * @param	string		HTML content to wrap in the div-tags with the "main class" of the plugin
	 * @return	string		HTML content wrapped, ready to return to the parent object.
	 */
	function pi_wrapInBaseClass($str, $widgetId)	{
		$content = '<div class="'.str_replace('_','-',$widgetId).'">
		'.$str.'
	</div>
	';

		if(!$GLOBALS['TSFE']->config['config']['disablePrefixComment'])	{
			$content = '


	<!--

		BEGIN: Content of extension "'.$this->extKey.'", plugin "'.$widgetId.'"

	-->
	'.$content.'
	<!-- END: Content of extension "'.$this->extKey.'", plugin "'.$widgetId.'" -->

	';
		}

		return $content;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/lib/class.tx_typo3blog_func.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/lib/class.tx_typo3blog_func.php']);
}
?>
