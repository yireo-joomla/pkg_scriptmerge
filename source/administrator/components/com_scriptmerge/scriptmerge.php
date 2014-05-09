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

// Require the plugin-class
require_once JPATH_SITE.'/components/com_scriptmerge/helpers/helper.php';

// Read the parameters
$files = JRequest::getString('files');
$type = JRequest::getString('type');

// Check for the default backend task
if(empty($type) && empty($files)) {

    // Require the base controller
    require_once (JPATH_COMPONENT.'/controller.php');
    $controller	= new ScriptMergeController( );

    // Perform the Request task
    $controller->execute(JRequest::getCmd('task'));
    $controller->redirect();
    return;
}

// Initialize files
if(!empty($files)) {
    $files = base64_decode($files);
    $files = explode(',', $files);
}

$buffer = null;
if(!empty($files)) {

    // Instantiate the helper
    $helper = new ScriptMergeHelper();

    foreach($files as $file) {

        // Basic security check
        if(!preg_match('/\.(css|js)$/', $file)) {
            continue;
        }

        // CSS-code
        if($type == 'css') {
            header('Content-Type: text/css');
            $buffer .= $helper->getCssContent($file);

        // JS-code
        } else {
            header('Content-Type: application/javascript');
            $buffer .= $helper->getJsContent($file);
        }
    }
}

echo $buffer;
$application = JFactory::getApplication();
$application->close();
