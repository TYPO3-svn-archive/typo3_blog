<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Hooks
/*
$TYPO3_CONF_VARS['EXTCONF']['indexed_search']['pi1_hooks'] = array (
	'getDisplayResults' => 'EXT:typo3_blog/hooks/class.tx_typo3_blog_indexedsearch_hooks.php:&tx_typo3_blog_indexedsearch_hooks',
);
*/
$TYPO3_CONF_VARS['FE']['pageOverlayFields'] .= ',tx_typo3blog_author,tx_typo3blog_allow_comments,tx_typo3blog_create_datetime,tx_typo3blog_tags,tx_typo3blog_exclude_page,tx_typo3blog_blogrolls';
//$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][typo3_blog] =  'EXT:typo3_blog/lib/class.tx_typo3blog_func.php:tx_typo3log_func->user_includeJS';

t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_typo3blog_blogroll=1
');

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_typo3blog_pi1.php', '_pi1', 'list_type', 1);
//t3lib_extMgm::addPItoST43($_EXTKEY, 'widgets/calendar/class.tx_typo3blog_widget_calendar.php', '_pi1', 'list_type', 1);

$_EXTCONF = unserialize($_EXTCONF);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['doktypeId'] = intval($_EXTCONF['doktypeId']) ? intval($_EXTCONF['doktypeId']) : 73;

// Exclude the blog category pages from indexed_search
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['excludeDoktype'][] = intval($_EXTCONF['doktypeId']) ? intval($_EXTCONF['doktypeId']) : 73;

$GLOBALS["TYPO3_CONF_VARS"]["FE"]["addRootLineFields"] .= ',tx_typo3blog_blogrolls';

$extensionPath = t3lib_extMgm::extPath('typo3_blog');
require_once($extensionPath . 'lib/user_typo3blog.php');

?>