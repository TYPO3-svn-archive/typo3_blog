<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';


t3lib_extMgm::addPlugin(array(
	'LLL:EXT:typo3_blog/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');


if (TYPO3_MODE == 'BE') {
	//$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_typo3blog_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_typo3blog_pi1_wizicon.php';
}

//$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] ='pi_flexform';
//t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', t3lib_extMgm::extRelPath($_EXTKEY) . 'pi1/flexform_ds.xml');
//t3lib_extMgm::addPiFlexFormValue($_EXTKEY .'_pi1', 'FILE:EXT:typo3_blog/pi1/flexform_ds.xml');



$tempColumns = array (
	'tx_typo3blog_allow_comments' => array (
		'exclude' => 0,
		'label' => 'LLL:EXT:typo3_blog/locallang_db.xml:pages.tx_typo3blog_allow_comments',
		'config' => array (
			'type' => 'check',
		)
	),
	'tx_typo3blog_tags' => array (
		'exclude' => 0,
		'label' => 'LLL:EXT:typo3_blog/locallang_db.xml:pages.tx_typo3blog_tags',
		'config' => array (
			'type' => 'input',
			'size' => '30',
		)
	),
);

t3lib_div::loadTCA('pages');
t3lib_extMgm::addTCAcolumns('pages',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('pages', '--div--;Blog Settings, tx_typo3blog_allow_comments, tx_typo3blog_tags');


// Define Page type ID
$doktype = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['doktypeId'];

$TCA['pages']['columns']['doktype']['config']['items'][] = Array ('Blog', $doktype, t3lib_extMgm::extRelPath($_EXTKEY). 'res/pageicon.png');

if (t3lib_div::int_from_ver(TYPO3_version) >= 4004000) {
	t3lib_SpriteManager::addTcaTypeIcon('pages', $doktype, t3lib_extMgm::extRelPath($_EXTKEY) . 'res/pageicon.png');
}else{
	$PAGES_TYPES[$doktype] = Array ('icon' => t3lib_extMgm::extRelPath($_EXTKEY) . 'res/pageicon.gif');
}

$GLOBALS['TYPO3_CONF_VARS']['BE']['defaultUserTSconfig'] .= '
options.pageTree {
doktypesToShowInNewPageDragArea = 1,6,4,7,3,'.$doktype.',254,255,199
}
';


t3lib_extMgm::addStaticFile($_EXTKEY,'typoscript/', 'Typo3Blog Setup');
t3lib_extMgm::addStaticFile($_EXTKEY,'typoscript/layout', 'Typo3Blog Setup Sample layout');

?>