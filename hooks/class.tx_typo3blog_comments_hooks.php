<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Roland Hensch
 * Date: 13.01.13
 * Time: 13:21
 * To change this template use File | Settings | File Templates.
 */

class tx_typo3blog_comments_hooks
{
	function addNewMarker($params, &$Obj)
	{
		$markerArray = $params['markers'];
		$markerArray['###UID###'] = $params['row']['uid'];
		return $markerArray;
	}
}