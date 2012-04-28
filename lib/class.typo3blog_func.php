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
 *   36: class typo3blog_func
 *   43:     public function getContentFromBlogPost($pid, $limit = 1)
 *
 * TOTAL FUNCTIONS: 1
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class typo3blog_func {

    /**
 * @param	type		$pid
 * @param	type		$limit
 * @return	type
 */
    public function getContentFromBlogPost($pid, $limit = 1)
    {
        $content_sql = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                    'uid',
                    'tt_content',
                    'deleted=0 AND hidden=0 AND  pid='.intval($pid),
                    '',
                    '',
                    $limit
                    );
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($content_sql))  {
            $content_ids[] = $row['uid'];
        }

        if (count($content_ids) < 1) {
            return $contend_ids = NULL;
        }
        return implode(',',$content_ids);

    }

}
?>
