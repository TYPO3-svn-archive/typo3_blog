<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

require_once(t3lib_extMgm::extPath('typo3_blog') . 'hooks/class.tx_typo3blog_comments_hooks.php');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';

/*
t3lib_extMgm::addPlugin(array(
	'LLL:EXT:typo3_blog/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');
*/

if (TYPO3_MODE == 'BE') {

}

// Get extConf
$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

$tempColumns = array (
	'tx_typo3blog_author' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:typo3_blog/locallang_db.xml:pages.tx_typo3blog_author',
		'config'  => array (
			'type' => 'select',
			'foreign_table' => 'be_users',
			'foreign_table_where' => 'ORDER BY be_users.username',
			'items' => array (
				array('', ''),
			),
			'size' => 1,
			'minitems' => 0,
			'maxitems' => 1,
			'default' => $GLOBALS['BE_USER']->user['uid']
		)
	),
	'tx_typo3blog_allow_comments' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:typo3_blog/locallang_db.xml:pages.tx_typo3blog_allow_comments',
		'config' => array (
			'type' => 'check',
			'default' => $extConf['allowComments'],
		)
	),
	'tx_typo3blog_create_datetime' => array (
		'l10n_mode' => 'mergeIfNotBlank',
		'exclude' => 1,
		'label' => 'LLL:EXT:typo3_blog/locallang_db.xml:pages.tx_typo3blog_create_datetime',
		'config' => Array (
			'type' => 'input',
			'size' => '10',
			'max' => '20',
			'eval' => 'datetime',
			'default' => mktime(date("H"),date("i"),0,date("m"),date("d"),date("Y"))
		)
	),
	'tx_typo3blog_tags' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:typo3_blog/locallang_db.xml:pages.tx_typo3blog_tags',
		'config' => array (
			'type' => 'input',
			'size' => '30',
		)
	),
	'tx_typo3blog_exclude_page' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:typo3_blog/locallang_db.xml:pages.tx_typo3blog_exclude_page',
		'config' => array (
			'type' => 'check',
			'default' => '0',
		)
	),
	'tx_typo3blog_blogrolls' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:typo3_blog/locallang_db.xml:pages.tx_typo3blog_blogrolls',
		'config' => array (
			'type' => 'select',
			'foreign_table' => 'tx_typo3blog_blogroll',
			'foreign_table_where' => 'ORDER BY tx_typo3blog_blogroll.uid',
			'size' => 10,
			'minitems' => 0,
			'maxitems' => 100,
		)
	),
);

t3lib_div::loadTCA('pages');
t3lib_extMgm::addTCAcolumns('pages',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('pages', '--div--;LLL:EXT:typo3_blog/locallang_db.xml:pages.tx_typo3blog_tab, tx_typo3blog_author, tx_typo3blog_allow_comments, tx_typo3blog_create_datetime, tx_typo3blog_tags, tx_typo3blog_exclude_page, tx_typo3blog_blogrolls');

t3lib_div::loadTCA('pages_language_overlay');
t3lib_extMgm::addTCAcolumns('pages_language_overlay',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('pages_language_overlay', '--div--;LLL:EXT:typo3_blog/locallang_db.xml:pages.tx_typo3blog_tab, tx_typo3blog_author, tx_typo3blog_allow_comments, tx_typo3blog_create_datetime, tx_typo3blog_tags, tx_typo3blog_exclude_page, tx_typo3blog_blogrolls');

// Define Page type ID
$doktype = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['doktypeId'];

$TCA['pages']['columns']['doktype']['config']['items'][] = Array ('Blog Category', $doktype, t3lib_extMgm::extRelPath($_EXTKEY). 'res/pageicon.png');
$TCA['pages_language_overlay']['columns']['doktype']['config']['items'][] = Array ('Blog Category', $doktype, t3lib_extMgm::extRelPath($_EXTKEY). 'res/pageicon.png');



t3lib_extMgm::allowTableOnStandardPages('tx_typo3blog_blogroll');
t3lib_extMgm::addToInsertRecords('tx_typo3blog_blogroll');
$TCA['tx_typo3blog_blogroll'] = array (
	'ctrl' => array (
		'title'        => 'LLL:EXT:typo3_blog/locallang_db.xml:tx_typo3blog_blogroll',
		'label'        => 'name',
		'tstamp'       => 'tstamp',
		'crdate'       => 'crdate',
		'cruser_id'    => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid'      => 't3_origuid',
		'default_sortby' => 'ORDER BY crdate',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled'  => 'hidden',
			'starttime' => 'starttime',
			'endtime'   => 'endtime',
			'fe_group'  => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'res/blogrollicon.gif',
	),
);


if (class_exists(t3lib_utility_VersionNumber)) {
	$TYPO3_version = t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version);
} else {
	$TYPO3_version = t3lib_div::int_from_ver(TYPO3_version);
}

if ($TYPO3_version >= 4004000) {
	t3lib_SpriteManager::addTcaTypeIcon('pages', $doktype, t3lib_extMgm::extRelPath($_EXTKEY) . 'res/pageicon.png');
}else{
	$PAGES_TYPES[$doktype] = Array ('icon' => t3lib_extMgm::extRelPath($_EXTKEY) . 'res/pageicon.gif');
}

$GLOBALS['TYPO3_CONF_VARS']['BE']['defaultUserTSconfig'] .= ' options.pageTree {
	doktypesToShowInNewPageDragArea = 1,6,4,7,3,'.$doktype.',254,255,199
}';


t3lib_extMgm::addStaticFile($_EXTKEY,'typoscript/', 'Typo3Blog Setup');

?>