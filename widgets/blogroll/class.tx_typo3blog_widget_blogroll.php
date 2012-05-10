<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Juergen Furrer <juergen.furrer@gmail.com>
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
 *
 * TOTAL FUNCTIONS: 5
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('typo3_blog') . 'lib/class.typo3blog_func.php');
require_once(t3lib_extMgm::extPath('typo3_blog').'lib/class.typo3blog_pagerenderer.php');
include_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_content.php');


/**
 * Plugin 'Typo3 Blog Blogroll' for the 'typo3_blog' extension.
 *
 * @author			Juergen Furrer <juergen.furrer@gmail.com>
 * @package			TYPO3
 * @subpackage		tx_typo3blog
 */
class tx_typo3blog_widget_blogroll
{
	public $prefixId = 'tx_typo3blog_pi1';
	public $scriptRelPath = 'widgets/blogroll/class.tx_typo3blog_widget_blogroll.php';
	public $extKey = 'typo3_blog';
	public $pi_checkCHash = TRUE;

	/**
	 * Returns all uid's of the blogrolls from rootline
	 *
	 * @param	string		$content:		The PlugIn content
	 * @param	array		$conf:			The PlugIn configuration
	 * @return	string		$content:		The content that is displayed on the website
	 * @access public
	 */
	public function getBlogRollIds($content, $conf) {
		$blogrollArray = array();
		foreach ($GLOBALS['TSFE']->rootLine as $page) {
			$blogrollArray = array_merge($blogrollArray, t3lib_div::trimExplode(",", $page['tx_typo3blog_blogrolls'], TRUE));
			if ($page['uid'] == $GLOBALS["TSFE"]->tmpl->setup['plugin.']['tx_typo3blog_pi1.']['startPid']) {
				break;
			}
		}
		array_unique($blogrollArray);

		return implode(",", $blogrollArray);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/widgets/blogroll/class.tx_typo3blog_widget_blogroll.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/widgets/blogroll/class.tx_typo3blog_widget_blogroll.php']);
}

?>