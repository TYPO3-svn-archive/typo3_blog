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
 *   48: class typo3blog_func
 *   59:     public function setCobj(tslib_cObj $cObj)
 *   75:     public function substituteMarkersAndSubparts($template, array $markers, array $subparts)
 *   95:     public function getPageContent($id, $limit)
 *  135:     public function getPostCategoryName($pid, $field = 'title')
 *
 * TOTAL FUNCTIONS: 4
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
	 * @param	tslib_cObj		$cObj    tslib_cObj class
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
	 * @param	string		$template    Template
	 * @param	array		$markers     Markers
	 * @param	array		$subparts    Subparts
	 * @return	string		$content     HTML
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
	 * @param	[type]		$id: ...
	 * @param	[type]		$limit: ...
	 * @return	string
	 * @access public
	 */
	public function getPageContent($id, $limit)
	{
		if (!t3lib_div::testInt($limit)) {
			$limit = false;
		}

		$sql = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray(array(
				'SELECT'	=> 'uid',
				'FROM'		=> 'tt_content',
				'WHERE'		=> 'pid=' . intval($id) . ' ' . $this->cObj->enableFields('tt_content'),
				'GROUPBY'	=> '',
				'ORDERBY'	=> 'sorting',
				'LIMIT'		=> (false !== $limit)?$limit:''
			)
		);
		$res = mysql(TYPO3_db, $sql);

		$content = '';
		while ($row = mysql_fetch_assoc($sql)) {
			$conf['tables'] = 'tt_content';
			$conf['source'] = intval($row['uid']);
			$conf['dontCheckPid'] = 1;
			if (false !== $limit) {
				$conf['max'] = intval($limit);
			}

			$content .= $this->cObj->RECORDS($conf);
		}
		return $content;
	}

	/**
	 * Return the category name from parent page
	 * The parent page is the category page
	 *
	 * @param	int		$pid             Page ID
	 * @param	string		$field           Column from table pages
	 * @return	string		$page[$field]    Value from field in table pages
	 * @access public
	 */
	public function getPostCategoryName($pid, $field = 'title')
	{
		$sql = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray(array(
				'SELECT'	=> '*',
				'FROM'		=> 'pages',
				'WHERE'		=> 'uid=' . intval($pid),
				'GROUPBY'	=> '',
				'ORDERBY'	=> '',
				'LIMIT'		=> ''
			)
		);
		$page = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql);

		return $page[$field];
	}
}
?>
