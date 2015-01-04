<?php
/**
 * Joomla! System plugin - ScriptMerge
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$strings = array(
    'templates/system/css/system.css' => null,
    '/media/system/js/mootools-core.js' => null,
    '../../../../media/system/css/system.css' => JPATH_ADMINISTRATOR.'/templates/system/css/system.css',
    '../images/calendar.png' => JPATH_ADMINISTRATOR.'/templates/system/css/',
    '../images/none.png' => JPATH_ADMINISTRATOR.'/templates/system/css/',
);

foreach($strings as $file => $basepath) {
    $path = ScriptMergeHelper::getFilePath($file, $basepath);

    echo "$file = $path\n";
    echo (is_file($path)) ? 'OK' : 'FAIL';
    echo " = ".ScriptMergeHelper::getFileUrl($path);
    echo "\n\n";
}

exit;
