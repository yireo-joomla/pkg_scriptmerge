<?php
/**
 * Joomla! System plugin - ScriptMerge
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// Require the helper
require_once JPATH_SITE.'/components/com_scriptmerge/helpers/helper.php';
$helper = new ScriptMergeHelper();

// Test
if (JRequest::getInt('test', 0) == 1) {
    require_once 'test.php';
}

// Read the files parameter
$files = JRequest::getString('files');
if (!empty($files)) {

    $files = $helper->decodeList($files);
    $buffer = null;
    foreach ($files as $file) {
        if ($type == 'css') {
            if (!preg_match('/\.css$/', $file)) continue;
            $buffer .= $helper->getCssContent($file);
        } else {
            if (!preg_match('/\.js$/', $file)) continue;
            $buffer .= $helper->getJsContent($file);
        }
    }
}
                
// Clean up CSS-code
if($type == 'css') {
    $buffer = ScriptMergeHelper::cleanCssContent($buffer);

// Clean up JS-code
} else {
    $buffer = ScriptMergeHelper::cleanJsContent($buffer);
}

// Handle GZIP support
$compression = false;
if (function_exists('gzencode') && ScriptMergeHelper::getParams()->get('force_gzip', 0) == 1) {
    $gzip = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');
    $deflate = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate');
    $compression = $gzip ? 'gzip' : ($deflate ? 'deflate' : false);
    $buffer = gzencode($buffer, 9, $gzip ? FORCE_GZIP : FORCE_DEFLATE); 
}

// Send HTTP-headers
ScriptMergeHelper::sendHttpHeaders($buffer, $helper->getParams(), $compression);

// Print the buffer    
print $buffer;

// Close the application
$application = JFactory::getApplication();
$application->close();
