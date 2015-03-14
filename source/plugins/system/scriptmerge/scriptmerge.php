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

// Import the parent class
jimport( 'joomla.plugin.plugin' );

/**
 * ScriptMerge System Plugin
 */
class plgSystemScriptMerge extends JPlugin
{
    /**
     * Event onAfterRoute
     *
     * @param null
     * @return null
     */
    public function onAfterRoute()
    {
        // Don't do anything for non scriptmerge pages
        if(JRequest::getCmd('option') != 'com_scriptmerge') {
            return false;
        }

        // Check for frontend
        $app = JFactory::getApplication();
        if($app->isSite() == false) {
            return false;
        }

        // Require the helper
        require_once JPATH_SITE.'/components/com_scriptmerge/helpers/helper.php';
        $helper = new ScriptMergeHelper();

        // Send the content-type header
        $type = JRequest::getString('type');
        if ($type == 'css') {
            header('Content-Type: text/css');
        } else {
            header('Content-Type: application/javascript');
        }

        // Read the files parameter
        $files = JRequest::getString('files');
        $buffer = null;
        if (!empty($files)) {

            $files = $helper->decodeList($files);
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

        // Construct the expiration time
        $expires = (int)($helper->getParams()->get('expiration', 30) * 60);

        // Set the expiry in the future
        if ($expires > 0) {
            header('Cache-Control: public, max-age='.$expires);
            header('Expires: '.gmdate('D, d M Y H:i:s', time() + $expires));

        // Set the expiry in the past
        } else {
            header("Cache-Control: no-cache, no-store, must-revalidate");
            header('Expires: '.gmdate('D, d M Y H:i:s', time() - (60 * 60 * 24)));
        }

        header('Vary: Accept-Encoding');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()));
        header('ETag: '.md5($buffer));

        if (function_exists('gzencode') && ScriptMergeHelper::getParams()->get('force_gzip', 0) == 1) {
            header('Content-Encoding: gzip');
            print gzencode($buffer);
        } else {
            print $buffer;
        }

        // Close the application
        $application = JFactory::getApplication();
        $application->close();
    }

    /**
     * Event onAfterRender
     *
     * @param null
     * @return null
     */
    public function onAfterRender()
    {
        // Check if this plugin is enabled
        if($this->isEnabled() == false) return false;

        // Get the body and fetch a list of files
        $body = JResponse::getBody();
        $application = JFactory::getApplication();

        // Fetch all the matches
        $matches = array();
        if($this->getParams()->get('enable_css', 1) == 1) $matches['css'] = $this->getCssMatches($body);
        if($this->getParams()->get('enable_js', 1) == 1) $matches['js'] = $this->getJsMatches($body);

        // Remove all current links from the document
        $body = $this->cleanup($body, $matches);

        // Parse images
        $body = $this->parseImages($body);

        // Add the new URL to the document
        $body = $this->addMergeUrl($body, $matches);

        // Make sure all MooTools scripts are loaded first
        if($application->isAdmin() && $this->getParams()->get('backend') == 1) {
            if(preg_match_all('/\<script([^\>]+)mootools(.*).js([^\>]+)\>\<\/script\>/', $body, $matches)) {
                $scripts = null;
                foreach($matches[0] as $match) {
                    $body = str_replace($match, '', $body);
                    $scripts .= $match."\n";
                }
                $body = str_replace('<head>', '<head>'.$scripts, $body);
            }
        }

        JResponse::setBody($body);
    }

    /**
     * Method to detect all the CSS stylesheets in the HTML-body
     *
     * @param string $body 
     * @return array
     */
    private function getCssMatches($body = null)
    {
        // Remove conditional comments from matching
        $buffer = preg_replace('/<!--(.*)-->/msU', '', $body);

        // Detect all CSS 
        preg_match_all('/<link(.*)href=(["\']+)([^\"\']+)(["\']+)([^\>]+)>/msU', $buffer, $matches);

        // Parse all the matched entries
        $files = array();
        if(isset($matches[3])) {

            // Get the exclude-matches
            $exclude_css = explode(',', $this->getParams()->get('exclude_css'));
            if(!empty($exclude_css)) {
                foreach($exclude_css as $i => $e) {
                    $e = trim($e);
                    if(empty($e)) {
                        unset($exclude_css[$i]);
                    } else {
                        $exclude_css[$i] = $e;
                    }
                }
            }

            // Loop through the rules
            foreach($matches[3] as $index => $match) {

                // Skip certain entries
                if(stripos($matches[0][$index], 'stylesheet') == false && stripos($matches[0][$index], 'css') == false) continue;
                if(stripos($matches[0][$index], 'media="print"')) continue;

                // Only try to match local CSS
                $match = str_replace(JURI::base(), '', $match);
                $match = preg_replace('/^'.str_replace('/', '\/', JURI::base(true)).'/', '', $match);
                if(preg_match('/\.css(\?\w+=\w+)?$/', $match) && !preg_match('/^http:\/\//', $match)) {

                    // Only include files that can be read
                    $file = preg_replace('/\?(.*)/', '', $match);

                    // Check for excludes
                    if(!empty($exclude_css)) {
                        $match = false;
                        foreach($exclude_css as $exclude) {
                            if(strstr($file, $exclude)) {
                                $match = true;
                                break;
                            }
                        }
                        if($match == true) continue;
                    }

                    // Try to determine the path to this file
                    $filepath = ScriptMergeHelper::getFilePath($file);
                    if(!empty($filepath)) {
                        $files[] = array(
                            'remote' => 0,
                            'file' => $filepath,
                            'html' => $matches[0][$index],
                        );
                    }
                }
            }
        }

        return $files;
    }

    /**
     * Method to detect all the JavaScript-scripts in the HTML-body
     *
     * @param string $body 
     * @return array
     */
    private function getJsMatches($body = null)
    {
        // Remove conditional comments from matching
        $buffer = preg_replace('/<!--(.*)-->/msU', '', $body);

        // Detect all JavaScripts
        preg_match_all('/<script([^\>]+)src="([^\"]+)"(.*)><\/script>/msU', $buffer, $matches);

        // Build the list of files to include
        $excludes = trim($this->getParams()->get('exclude_js'));
        $excludes = (!empty($excludes)) ? explode(',', $excludes) : array();
        foreach($excludes as $i => $e) $excludes[$i] = trim($e);

        // Add extra scripts in the backend
        $application = JFactory::getApplication();
        if($application->isAdmin() && $this->getParams()->get('backend') == 1) {
            $excludes[] = 'mootools.js';
            $excludes[] = 'mootools-core.js';
            $excludes[] = 'mootools-more.js';
            $excludes[] = 'mootools-uncompressed.js';
            $excludes[] = 'joomla.javascript.js';
            $excludes[] = 'menu.js';
        }

        // Parse all the matched entries
        $files = array();
        if(isset($matches[2])) {
            foreach($matches[2] as $index => $match) {

                // Only try to match local JavaScript
                $match = str_replace(JURI::base(), '', $match);
                $match = preg_replace('/^'.str_replace('/', '\/', JURI::base(true)).'/', '', $match);
                if(empty($match)) continue;
    
                // Skip already compressed files
                if($this->getParams()->get('skip_compressed', 0) == 1) {
                    if(preg_match('/\.(pack|min)\.js/', $match)) continue;
                }

                // Match files that should be excluded
                if(!empty($excludes) && !empty($match)) {
                    $e = false;
                    foreach($excludes as $exclude) {
                        if(empty($match) || empty($exclude)) continue;
                        if(strstr($match, $exclude) || stripos($match, $exclude)) {
                            $e = true;
                            break;
                        }
                    }
                    if($e == true) continue;
                }
                
                // Only try to match local JS
                if(preg_match('/\.js(\?\w+=\w+)?$/', $match) && !preg_match('/^http:\/\//', $match)) {

                    // Only include files that can be read
                    $match = preg_replace('/\?(.*)/', '', $match);
                    $filepath = ScriptMergeHelper::getFilePath($match);

                    if(!empty($filepath)) {

                        $add = true;
                        if($this->getParams()->get('remove_mootools') == 1 && stristr($filepath, 'mootools')) {
                            $add = false;
                        }

                        if($add) {
                            $files[] = array(
                                'remote' => 0,
                                'file' => $filepath,
                                'html' => $matches[0][$index],
                            );
                        }
                    }
                }
            }
        }

        return $files;
    }

    /**
     * Method to add the merged URL to the HTML-document
     *
     * @param string $body
     * @param array $matches
     * @return string
     */
    private function addMergeUrl($body = null, $matches = array())
    {
        // Treat CSS and JS seperately
        foreach($matches as $type => $list) {
            if(!empty($list)) {

                // Create a base64-encoded list of the merged files
                if($this->getParams()->get('merge_type') == 'files') {
                    $url = $this->buildMergeUrl($type, $list);

                // Create an unique signature for this filelist
                } else {
                    $url = $this->buildCacheUrl($type, $list);
                }

                if($type == 'css') {
                    $tag = '<link rel="stylesheet" href="'.$url.'" type="text/css" />';
                    $tag_position = $this->getParams()->get('css_position');
                } else {
                    $async = ($this->getParams()->get('async_merged', 0) == 1) ? ' async' : '';
                    $tag = '<script src="'.$url.'"'.$async.' type="text/javascript"></script>';
                    $tag_position = $this->getParams()->get('js_position');
                }

                switch($tag_position) {
                    case 'body_end': 
                        $body = str_replace('</body>', $tag.'</body>', $body);
                        $body = str_replace('<!-- plg_scriptmerge_'.md5($type).' -->', '', $body);
                        break;

                    case 'head_end': 
                        $body = str_replace('</head>', $tag.'</head>', $body);
                        $body = str_replace('<!-- plg_scriptmerge_'.md5($type).' -->', '', $body);
                        break;

                    default:
                        $body = str_replace('<!-- plg_scriptmerge_'.md5($type).' -->', $tag, $body);
                        break;
                }
            }
        }

        return $body;
    }

    /**
     * Method to build the merged URL
     *
     * @param string $type
     * @param array $list
     * @return string
     */
    private function buildMergeUrl($type, $list = array())
    {
        // Append the list as arguments to the URL
        $files = array();
        foreach($list as $file) {
            $files[] = $file['file'];
        }

        $app = JFactory::getApplication()->getClientId(); // 0 for site, 1 for admin
        $version = $this->getParams()->get('version', 1);
        $files = ScriptMergeHelper::encodeList($files);
        $url = 'index.php?option=com_scriptmerge&format=raw&amp;tmpl=component&amp;type='.$type.'&app='.$app.'&version='.$version.'&files='.$files;

        // Determine the right URL, based on the frontend or backend
        if(JFactory::getApplication()->isSite() == true) {
            $url = JRoute::_($url);
        } else {
            $url = JURI::root().$url;
        }

        // Domainname sharding
        $domain = ($type == 'js') ? $this->getParams()->get('js_domain') : $this->getParams()->get('css_domain');
        if(!empty($domain)) {
            $domain = preg_replace('/\/$/', '', $domain);
            $applyDomain = true;
            if(preg_match('/^(http|https)\:\/\//', $domain, $domainMatch)) {
                if($domainMatch[1] == 'http' && JURI::getInstance()->isSSL()) {
                    $applyDomain = false;
                } else {
                    $oldDomain = JURI::root();
                    $oldDomain = preg_replace('/\/$/', '', $oldDomain);
                }
            } else {
                $oldDomain = JURI::getInstance()->toString(array('host'));
            }

            if($applyDomain) {
                if(preg_match('/^(http|https)\:\/\//', $url) == false) {
                    $url = JURI::root().preg_replace('/^\//', '', $url);
                }
                $url = str_replace($oldDomain, $domain, $url);
            }
        }

        if(JURI::getInstance()->isSSL()) {
            $url = str_replace('http://', 'https://', $url);
        } else {
            $url = str_replace('https://', 'http://', $url);
        }

        return $url;
    }

    /**
     * Method to build the CSS / JavaScript cache
     *
     * @param string $type
     * @param array $matches
     * @return string
     */
    private function buildCacheUrl($type, $list = array())
    {
        // Check for the cache-path
        $tmp_path = JPATH_SITE.'/cache/plg_scriptmerge/';
        if(@is_dir($tmp_path) == false) {
            jimport( 'joomla.filesystem.folder' );
            JFolder::create($tmp_path);
        }

        if(!empty($list)) {
    
            $cacheId = md5(var_export($list, true));
            $cacheFile = $cacheId.'.'.$type;
            $cachePath = $tmp_path.'/'.$cacheFile;
            $cacheExpireFile = $cachePath.'_expire';

            $hasExpired = false;
            if(ScriptMergeHelper::hasExpired($cacheExpireFile, $cachePath)) {
                $hasExpired = true;
            }

            // @todo: Make this optional
            foreach($list as $file) {
                if(@filemtime($file['file'] > @filemtime($cachePath))) {
                    $hasExpired = true;
                    break;
                }
            }

            // Check the cache
            if($hasExpired) {

                $buffer = null;
                foreach($list as $file) {
                    if(isset($file['file'])) {

                        // CSS-code
                        if($type == 'css') {
                            $buffer .= ScriptMergeHelper::getCssContent($file['file']);

                        // JS-code
                        } else {
                            $buffer .= ScriptMergeHelper::getJsContent($file['file']);
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

                // Write this buffer to a file
                jimport( 'joomla.filesystem.file' );
                JFile::write($cachePath, $buffer);

                // Create a minified version of this file
                $this->createMinified($type, $cachePath);

                // Set the cache parameter
                $this->createCacheExpireFile($cacheExpireFile);
            }
        }

        // Construct the minified version
        if($type == 'js') {
            $minifiedFile = preg_replace('/\.js$/', '.min.js', $cacheFile);
        } else {
            $minifiedFile = preg_replace('/\.css$/', '.min.css', $cacheFile);
        }

        // Return the minified version if it exists
        if(file_exists(JPATH_SITE.'/cache/plg_scriptmerge/'.$minifiedFile)) {
            $url = JURI::root().'cache/plg_scriptmerge/'.$minifiedFile;

        // Return the cache-file itself
        } else {
            $url = JURI::root().'cache/plg_scriptmerge/'.$cacheFile;
        }

        if(JURI::getInstance()->isSSL()) {
            $url = str_replace('http://', 'https://', $url);
        } else {
            $url = str_replace('https://', 'http://', $url);
        }

        return $url;
    }
    
    /**
     * Method to remove obsolete tags in the HTML body
     *
     * @param string $body 
     * @param array $files
     * @return string
     */
    private function cleanup($body = null, $matches = array())
    {
        foreach($matches as $typename => $type) {
            if(!empty($type)) {

                $first = true;
                foreach($type as $file) {

                    if($first) {
                        $replacement = '<!-- plg_scriptmerge_'.md5($typename).' -->';
                        $first = false;
                    } else {
                        $replacement = '';
                    }

                    $body = str_replace($file['html'], $replacement, $body);
                }
            }
        }

        if($this->getParams()->get('compress_html') == 1) {
            $body = str_replace("\n\n", "\n", $body);
            $body = str_replace("\r\r", "\r", $body);
            $body = preg_replace('/\>[^\S ]+/s', '>', $body);
            $body = preg_replace('/[^\S ]+\</s', '<', $body);
            $body = preg_replace('/\>[\s]+\</s', '><', $body);
        }

        return $body;
    }

    /**
     * Method to translate images into data URIs
     *
     * @param string $text
     * @return string
     */
    private function parseImages($text = null)
    {
        if($this->getParams()->get('data_uris', 0) != 1) {
            return $text;
        }

        if(preg_match_all('/src=([\'\"]{1})([^\'\"]+)([\'\"]{1})/i', $text, $matches)) {
            foreach($matches[2] as $index => $match) {
                $match = preg_replace('/([\'\"\ ]+)/', '', $match);
                $path = ScriptMergeHelper::getFilePath($match);
                $content = ScriptMergeHelper::getImageUrl($path);
                if(!empty($content)) {
                    $text = str_replace($matches[0][$index], 'src='.$content, $text);
                }
            }
        }

        if(preg_match_all('/url\(([a-zA-Z0-9\.\-\_\/\ \' \"]+)\)/i', $text, $matches)) {
            foreach($matches[1] as $index => $match) {
                $match = preg_replace('/([\'\"\ ]+)/', '', $match);
                $path = ScriptMergeHelper::getFilePath($match);
                $content = ScriptMergeHelper::getImageUrl($path);
                if(!empty($content)) {
                    $text = str_replace($matches[0][$index], 'url('.$content.')', $text);
                }
            }
        }

        if(preg_match_all('/url\(([a-zA-Z0-9\.\-\_\/\ \' \"]+)\)/i', $text, $matches)) {
            foreach($matches[1] as $index => $match) {
                $match = preg_replace('/([\'\"\ ]+)/', '', $match);
                $path = ScriptMergeHelper::getFilePath($match);
                $content = ScriptMergeHelper::getImageUrl($path);
                if(!empty($content)) {
                    $text = str_replace($matches[0][$index], 'url('.$content.')', $text);
                }
            }
        }
        return $text;
    }

    /**
     * Create a minified version of the file
     *
     * @param string $type
     * @param string $file
     * @return null
     */
    private function createMinified($type, $file)
    {
        if($type == 'js') {

            // Construct the new filename
            $newFile = preg_replace('/\.js$/', '.min.js', $file);

            // Try to use JSMIN
            $jsmin = $this->getParams()->get('jsmin');
            if(!empty($jsmin) && $this->getParams()->get('use_jsmin', 0) == 1) {
                exec("$jsmin < $file > $newFile");
            }
        } else {

            // Construct the new filename
            $newFile = preg_replace('/\.css$/', '.min.css', $file);
        }
    }

    /**
     * Set a new cache expiration
     *
     * @param string $cache
     * @return null
     */
    private function createCacheExpireFile($file)
    {
        $config = JFactory::getConfig();
        if(method_exists($config, 'getValue')) {
            $lifetime = (int)$config->getValue('config.lifetime');
        } else {
            $lifetime = (int)$config->get('config.lifetime');
        }
        if(empty($lifetime) || $lifetime < 120) $lifetime = 120;
        $time = time() + $lifetime;
        jimport( 'joomla.filesystem.file' );
        JFile::write($file, $time);
    }

    /**
     * Load the parameters
     *
     * @param null
     * @return JParameter
     */
    private function getParams()
    {
        return $this->params;
    }

    /**
     * Check if this plugin is enabled
     *
     * @param null
     * @return boolean
     */
    private function isEnabled()
    {
        // Only continue in the right application, if enabled so
        $application = JFactory::getApplication();
        if($application->isAdmin() && $this->getParams()->get('backend', 0) == 0) {
            return false;
        } elseif($application->isSite() && $this->getParams()->get('frontend', 1) == 0) {
            return false;
        }
    
        // Dont do anything for the ScriptMerge component and the Plugin Manager
        if(in_array(JRequest::getCmd('option'), array('com_plugins', 'com_scriptmerge'))) {
            return false;
        }

        // Disable through URL
        if(JRequest::getInt('scriptmerge', 1) == 0) {
            return false;
        }

        // Try to include the helper
        $helper = JPATH_SITE.'/components/com_scriptmerge/helpers/helper.php';
        if(@is_readable($helper) == false) {
            return false;
        }

        // Exclude for menus
        $menu = JFactory::getApplication()->getMenu('site');
		$current_menuitem = $menu->getActive();
        if(!empty($current_menuitem)) {
            $exclude_menuitems = $this->getParams()->get('exclude_menuitems');
            if(!is_array($exclude_menuitems)) $exclude_menuitems = explode(',', trim($exclude_menuitems));
            foreach($exclude_menuitems as $index => $exclude_menuitem) {
                if($exclude_menuitem == $current_menuitem->id) {
                    return false;
                }
            }
        }

        // Exclude components
        $components = $this->getParams()->get('exclude_components');
        if(empty($components)) $components = array();
        if(!is_array($components)) $components = explode(',', $components);
        if(in_array(JRequest::getCmd('option'), $components)) {
            return false;
        }

        require_once $helper;
        return true;
    }
}
