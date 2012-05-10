<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_typo3blog_blogroll=1
');

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_typo3blog_pi1.php', '_pi1', 'list_type', 1);

$_EXTCONF = unserialize($_EXTCONF);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['doktypeId'] = intval($_EXTCONF['doktypeId']) ? intval($_EXTCONF['doktypeId']) : 73;

$GLOBALS["TYPO3_CONF_VARS"]["FE"]["addRootLineFields"] .= ',tx_typo3blog_blogrolls';

?>