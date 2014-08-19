<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "typo3_blog".
 *
 * Auto generated 27-03-2013 15:05
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Typo3 blog extension',
	'description' => 'Flexible blog extension for typo3',
	'category' => 'fe',
	'author' => 'Roland Hensch',
	'author_email' => 'rsch73@gmail.com',
	'shy' => '',
	'dependencies' => 'pagebrowse',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '1.2.0',
	'constraints' => array(
		'depends' => array(
			'php' => '5.3.0-0.0.0',
			'typo3' => '6.0.0-6.2.99',
			'pagebrowse' => '1.3.3-100.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:157:{s:9:"ChangeLog";s:4:"552f";s:21:"ext_conf_template.txt";s:4:"55b2";s:12:"ext_icon.gif";s:4:"d6a5";s:17:"ext_localconf.php";s:4:"5474";s:15:"ext_php_api.dat";s:4:"87dd";s:14:"ext_tables.php";s:4:"96e3";s:14:"ext_tables.sql";s:4:"026a";s:13:"locallang.xml";s:4:"b68e";s:16:"locallang_db.xml";s:4:"9cd0";s:10:"README.txt";s:4:"d41d";s:24:"RealURLConfiguration.txt";s:4:"1685";s:12:"t3jquery.txt";s:4:"d2ce";s:7:"tca.php";s:4:"fd3c";s:14:"doc/manual.sxw";s:4:"f47e";s:43:"hooks/class.tx_typo3blog_comments_hooks.php";s:4:"bf2d";s:31:"lib/class.tx_typo3blog_func.php";s:4:"6a5d";s:36:"lib/class.typo3blog_pagerenderer.php";s:4:"eacb";s:22:"lib/user_typo3blog.php";s:4:"cdaf";s:14:"pi1/ce_wiz.gif";s:4:"d6a5";s:30:"pi1/class.tx_typo3blog_pi1.php";s:4:"16f2";s:38:"pi1/class.tx_typo3blog_pi1_wizicon.php";s:4:"599a";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.xml";s:4:"d43a";s:20:"res/blogrollicon.gif";s:4:"4422";s:16:"res/pageicon.gif";s:4:"d6a5";s:16:"res/pageicon.png";s:4:"c51f";s:55:"res/jquery/css/custom-theme/jquery-ui-1.8.16.custom.css";s:4:"a0d2";s:51:"res/jquery/css/custom-theme/jquery-ui-1.8.16.ie.css";s:4:"e514";s:65:"res/jquery/css/custom-theme/images/ui-bg_flat_0_aaaaaa_40x100.png";s:4:"2a44";s:66:"res/jquery/css/custom-theme/images/ui-bg_glass_55_fbf9ee_1x400.png";s:4:"f8f4";s:66:"res/jquery/css/custom-theme/images/ui-bg_glass_65_ffffff_1x400.png";s:4:"e5a8";s:66:"res/jquery/css/custom-theme/images/ui-bg_glass_75_dadada_1x400.png";s:4:"c12c";s:66:"res/jquery/css/custom-theme/images/ui-bg_glass_75_e6e6e6_1x400.png";s:4:"f425";s:66:"res/jquery/css/custom-theme/images/ui-bg_glass_75_ffffff_1x400.png";s:4:"97b1";s:75:"res/jquery/css/custom-theme/images/ui-bg_highlight-soft_75_cccccc_1x100.png";s:4:"72c5";s:71:"res/jquery/css/custom-theme/images/ui-bg_inset-soft_95_fef1ec_1x100.png";s:4:"61ce";s:62:"res/jquery/css/custom-theme/images/ui-icons_222222_256x240.png";s:4:"ebe6";s:62:"res/jquery/css/custom-theme/images/ui-icons_2e83ff_256x240.png";s:4:"2b99";s:62:"res/jquery/css/custom-theme/images/ui-icons_454545_256x240.png";s:4:"119d";s:62:"res/jquery/css/custom-theme/images/ui-icons_888888_256x240.png";s:4:"9c46";s:62:"res/jquery/css/custom-theme/images/ui-icons_cd0a0a_256x240.png";s:4:"3e45";s:62:"res/jquery/css/custom-theme/images/ui-icons_f6cf3b_256x240.png";s:4:"6dea";s:47:"res/jquery/css/treeview/jquery.treeview-1.5.css";s:4:"f8d3";s:46:"res/jquery/css/treeview/images/ajax-loader.gif";s:4:"30d8";s:39:"res/jquery/css/treeview/images/file.gif";s:4:"9ab0";s:48:"res/jquery/css/treeview/images/folder-closed.gif";s:4:"262d";s:41:"res/jquery/css/treeview/images/folder.gif";s:4:"9f41";s:40:"res/jquery/css/treeview/images/minus.gif";s:4:"e009";s:39:"res/jquery/css/treeview/images/plus.gif";s:4:"6c46";s:54:"res/jquery/css/treeview/images/treeview-black-line.gif";s:4:"0cdd";s:49:"res/jquery/css/treeview/images/treeview-black.gif";s:4:"a3ff";s:56:"res/jquery/css/treeview/images/treeview-default-line.gif";s:4:"5e3c";s:51:"res/jquery/css/treeview/images/treeview-default.gif";s:4:"4687";s:58:"res/jquery/css/treeview/images/treeview-famfamfam-line.gif";s:4:"18b3";s:53:"res/jquery/css/treeview/images/treeview-famfamfam.gif";s:4:"dc33";s:53:"res/jquery/css/treeview/images/treeview-gray-line.gif";s:4:"9c26";s:48:"res/jquery/css/treeview/images/treeview-gray.gif";s:4:"02b4";s:52:"res/jquery/css/treeview/images/treeview-red-line.gif";s:4:"feda";s:47:"res/jquery/css/treeview/images/treeview-red.gif";s:4:"c94a";s:33:"res/jquery/js/jquery-1.7.2.min.js";s:4:"b8d6";s:44:"res/jquery/js/jquery-ui-1.8.20.custom.min.js";s:4:"f7f0";s:30:"res/jquery/js/jquery.cookie.js";s:4:"a118";s:34:"res/jquery/js/jquery.easing-1.3.js";s:4:"6516";s:36:"res/jquery/js/jquery.treeview-1.5.js";s:4:"69a1";s:36:"res/jquery/js/i18n/jquery-ui-i18n.js";s:4:"8595";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-af.js";s:4:"3f6d";s:48:"res/jquery/js/i18n/jquery.ui.datepicker-ar-DZ.js";s:4:"75fc";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-ar.js";s:4:"bd15";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-az.js";s:4:"d137";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-bg.js";s:4:"8098";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-bs.js";s:4:"1a61";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-ca.js";s:4:"b9f0";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-cs.js";s:4:"d974";s:48:"res/jquery/js/i18n/jquery.ui.datepicker-cy-GB.js";s:4:"3ebd";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-da.js";s:4:"a20a";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-de.js";s:4:"2312";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-el.js";s:4:"46b8";s:48:"res/jquery/js/i18n/jquery.ui.datepicker-en-AU.js";s:4:"4a38";s:48:"res/jquery/js/i18n/jquery.ui.datepicker-en-GB.js";s:4:"24a2";s:48:"res/jquery/js/i18n/jquery.ui.datepicker-en-NZ.js";s:4:"af98";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-eo.js";s:4:"ae01";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-es.js";s:4:"469e";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-et.js";s:4:"91f5";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-eu.js";s:4:"80ad";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-fa.js";s:4:"4fd1";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-fi.js";s:4:"fff0";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-fo.js";s:4:"c236";s:48:"res/jquery/js/i18n/jquery.ui.datepicker-fr-CH.js";s:4:"4c40";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-fr.js";s:4:"59cc";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-ge.js";s:4:"4c66";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-gl.js";s:4:"948d";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-he.js";s:4:"3937";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-hi.js";s:4:"1e26";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-hr.js";s:4:"593a";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-hu.js";s:4:"dee2";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-hy.js";s:4:"64b7";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-id.js";s:4:"cc32";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-is.js";s:4:"c1da";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-it.js";s:4:"b1dc";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-ja.js";s:4:"c38e";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-kk.js";s:4:"016c";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-km.js";s:4:"f5c6";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-ko.js";s:4:"6851";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-kz.js";s:4:"be24";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-lb.js";s:4:"642a";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-lt.js";s:4:"ab35";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-lv.js";s:4:"2062";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-mk.js";s:4:"cdfd";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-ml.js";s:4:"8037";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-ms.js";s:4:"85de";s:48:"res/jquery/js/i18n/jquery.ui.datepicker-nl-BE.js";s:4:"60b6";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-nl.js";s:4:"f754";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-no.js";s:4:"dcb1";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-pl.js";s:4:"fbe2";s:48:"res/jquery/js/i18n/jquery.ui.datepicker-pt-BR.js";s:4:"4f41";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-pt.js";s:4:"2e4a";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-rm.js";s:4:"4158";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-ro.js";s:4:"f2c1";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-ru.js";s:4:"1789";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-sk.js";s:4:"8b44";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-sl.js";s:4:"72d8";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-sq.js";s:4:"3493";s:48:"res/jquery/js/i18n/jquery.ui.datepicker-sr-SR.js";s:4:"1a58";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-sr.js";s:4:"4065";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-sv.js";s:4:"8c79";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-ta.js";s:4:"da76";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-th.js";s:4:"ac63";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-tj.js";s:4:"af2f";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-tr.js";s:4:"9718";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-uk.js";s:4:"ef99";s:45:"res/jquery/js/i18n/jquery.ui.datepicker-vi.js";s:4:"be31";s:48:"res/jquery/js/i18n/jquery.ui.datepicker-zh-CN.js";s:4:"26ec";s:48:"res/jquery/js/i18n/jquery.ui.datepicker-zh-HK.js";s:4:"3b93";s:48:"res/jquery/js/i18n/jquery.ui.datepicker-zh-TW.js";s:4:"ef1e";s:24:"typoscript/constants.txt";s:4:"d631";s:20:"typoscript/setup.txt";s:4:"c6af";s:53:"widgets/archive/class.tx_typo3blog_widget_archive.php";s:4:"e1a7";s:25:"widgets/archive/setup.txt";s:4:"ca4b";s:29:"widgets/archive/template.html";s:4:"999e";s:55:"widgets/bloglist/class.tx_typo3blog_widget_bloglist.php";s:4:"a49f";s:26:"widgets/bloglist/setup.txt";s:4:"f76c";s:30:"widgets/bloglist/template.html";s:4:"04cf";s:55:"widgets/blogroll/class.tx_typo3blog_widget_blogroll.php";s:4:"4afb";s:26:"widgets/blogroll/setup.txt";s:4:"a397";s:59:"widgets/blogsingle/class.tx_typo3blog_widget_blogsingle.php";s:4:"8d16";s:28:"widgets/blogsingle/setup.txt";s:4:"abf2";s:32:"widgets/blogsingle/template.html";s:4:"f325";s:55:"widgets/calendar/class.tx_typo3blog_widget_calendar.php";s:4:"e47f";s:26:"widgets/calendar/setup.txt";s:4:"c72e";s:30:"widgets/calendar/template.html";s:4:"a038";s:26:"widgets/category/setup.txt";s:4:"4e94";s:61:"widgets/latestposts/class.tx_typo3blog_widget_latestposts.php";s:4:"4c35";s:29:"widgets/latestposts/setup.txt";s:4:"35b5";s:33:"widgets/latestposts/template.html";s:4:"6f52";s:63:"widgets/relatedposts/class.tx_typo3blog_widget_relatedposts.php";s:4:"5e82";s:30:"widgets/relatedposts/setup.txt";s:4:"72eb";s:34:"widgets/relatedposts/template.html";s:4:"8709";}',
	'suggests' => array(
	),
);

?>