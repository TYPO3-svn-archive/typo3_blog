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

if(!is_object($GLOBALS['BE_USER']))  {
	define('TYPO3_PROCEED_IF_NO_USER', true);
	$GLOBALS['BE_USER'] = t3lib_div::makeInstance('t3lib_beUserAuth');
	$GLOBALS['BE_USER']->start();
	$GLOBALS['BE_USER']->backendCheckLogin();
	define('TYPO3_PROCEED_IF_NO_USER', false);
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
	'tx_typo3blog_tags' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:typo3_blog/locallang_db.xml:pages.tx_typo3blog_tags',
		'config' => array (
			'type' => 'input',
			'size' => '30',
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
			'wizards' => array(
				'_PADDING'  => 2,
				'_VERTICAL' => 1,
				'add' => array(
					'type'   => 'script',
					'title'  => 'Create new record',
					'icon'   => 'add.gif',
					'params' => array(
						'table'    => 'tx_typo3blog_blogroll',
						'pid'      => '###CURRENT_PID###',
						'setValue' => 'prepend'
					),
					'script' => 'wizard_add.php',
				),
				'edit' => array(
					'type'                     => 'popup',
					'title'                    => 'Edit',
					'script'                   => 'wizard_edit.php',
					'popup_onlyOpenIfSelected' => 1,
					'icon'                     => 'edit2.gif',
					'JSopenParams'             => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
				),
			),
		)
	),
);

t3lib_div::loadTCA('pages');
t3lib_extMgm::addTCAcolumns('pages',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('pages', '--div--;LLL:EXT:typo3_blog/locallang_db.xml:pages.tx_typo3blog_tab, tx_typo3blog_author, tx_typo3blog_allow_comments, tx_typo3blog_tags, tx_typo3blog_blogrolls');

// Define Page type ID
$doktype = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['doktypeId'];

$TCA['pages']['columns']['doktype']['config']['items'][] = Array ('Blog', $doktype, t3lib_extMgm::extRelPath($_EXTKEY). 'res/pageicon.png');



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