<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Roland Hensch <rsch73@gmail.com>
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


require_once(t3lib_extMgm::extPath('typo3_blog') . 'lib/class.tx_typo3blog_func.php');
include_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_content.php');


/**
 * Plugin 'Typo3 Blog author' for the 'typo3_blog' extension.
 *
 * @author			Roland Hensch <rsch73@gmail.com>
 * @package			TYPO3
 * @subpackage		tx_typo3blog
 */
class tx_typo3blog_widget_author extends tslib_pibase
{
    public $prefixId = 'tx_typo3blog_pi1'; // Same as class name
    public $scriptRelPath = 'widgets/bloglist/class.tx_typo3blog_widget_author.php'; // Path to this script relative to the extension dir.
    public $extKey = 'typo3_blog'; // The extension key.
    public $pi_checkCHash = TRUE;

    private $template = NULL;
    private $extConf = NULL;
    private $parentConf = NULL;
    private $startPid = NULL;
    private $blog_doktype_id = NULL;
    private $typo3BlogFunc = NULL;
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

        // unserialize extension conf
        $this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typo3_blog']);

        // Set blog start page uid
        $this->startPid = intval($this->parentConf['startPid']);

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

        // Check the environment for typo3blog author
        if (NULL === $this->template) {
            return $this->pi_wrapInBaseClass(
                "Error :Template file for author widget not found.<br />Please check the typoscript configuration or constants!"
            );
        }

        if (!intval($this->blog_doktype_id)) {
            return $this->pi_wrapInBaseClass(
                "ERROR: doktype Id for page type blog not found.<br />Please set the doktype ID in extension conf!"
            );
        }

        // Get subparts from HTML template AUTHOR_TEMPLATE
        $template = $this->cObj->getSubpart($this->template, '###AUTHOR_TEMPLATE###');
        $subpartItem = $this->cObj->getSubpart($template, '###ITEM###');

        // Define array and vars for template
        $subparts = array();
        $subparts['###ITEM###'] = '';
        $markerArray = array();
        $markers = array();

        $markers['###WIDGET_TITLE###'] = $this->cObj->cObjGetSingle($this->conf['marker.']['widgetTitle'], $this->conf['marker.']['widgetTitle.']);

        // Query to load current category page with all post pages in rootline
        $sql = $this->getAuthorPosts();

        // Execute sql and set retrieved records from be_users
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql)) {
            if (is_array($row) && $this->typo3BlogFunc->getSysLanguageUid() > 0) {
//                $row = $GLOBALS['TSFE']->sys_page->getPageOverlay($row, $GLOBALS['TSFE']->sys_language_uid);
            }

            $sql_user = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray(
                array(
                    'SELECT' => '*',
                    'FROM'   => 'be_users',
                    'WHERE'  => "uid = '" . intval($row['tx_typo3blog_author']) . "' " . $this->cObj->enableFields('be_users'),
                )
            );

            // add additional data to ts template
            $row_user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql_user);
            $row['be_user_uid']          = $row_user['uid'];
            $row['be_user_username']     = $row_user['username'];
            $row['be_user_realName']     = $row_user['realName'];
            $row['be_user_email']        = $row_user['email'];
            $row['be_user_email_secure'] = md5($row_user['email']);

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


        // Wrap the content and return the content to display in frontend
        if ($this->conf['baseWrap.'])  {
            return $this->cObj->stdWrap($content, $this->conf['baseWrap.']);
        } else {
            return $this->typo3BlogFunc->pi_wrapInBaseClass($content,"author-widget");
        }


    }

    /**
     * Return  MySQL select result pointer of authors in posts
     *
     * @return	bool
     * @access private
     */
    private function getAuthorPosts()
    {
        $sql_array = array(
            'SELECT'  => 'pages.tx_typo3blog_author',
            'FROM'    => 'pages',
            'WHERE'   => '(' . $this->getPostsInRootLine() . ') '.$this->cObj->enableFields('pages').' AND doktype = 1 ' .$this->typo3BlogFunc->getWhereFilterQuery(),
            'GROUPBY' => 'pages.tx_typo3blog_author',
            'ORDERBY' => '',
            'LIMIT'   => ''
        );

        if ($this->typo3BlogFunc->getSysLanguageUid() > 0 && $GLOBALS['TYPO3_CONF_VARS']['FE']['hidePagesIfNotTranslatedByDefault'] > 0)	{
            $sql_array['FROM'] = 'pages, pages_language_overlay';
            $sql_array['WHERE'] = 'pages_language_overlay.pid = pages.uid AND (' . $this->getPostsInRootLine() . ') '.$this->cObj->enableFields('pages').' AND pages.doktype = 1 '.$this->typo3BlogFunc->getWhereFilterQuery();
            $sql_array['GROUPBY'] = 'pages_language_overlay.tx_typo3blog_author';
        }

        $sql = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($sql_array);

        return $sql;
    }

    /**
     * Get all sub pages from current page_id as string "page.uid = 1 OR page.uid = 2 OR ..."
     *
     * @return	string
     * @access private
     */
    private function getPostsInRootLine()
    {
        if (is_null($this->postsInRootLine)) {
            $this->cObj->data['recursive'] = 4;
            $pidList = $this->pi_getPidList(intval($this->startPid), $this->cObj->data['recursive']);
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

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/widgets/bloglist/class.tx_typo3blog_widget_bloglist.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3_blog/widgets/bloglist/class.tx_typo3blog_widget_bloglist.php']);
}

?>