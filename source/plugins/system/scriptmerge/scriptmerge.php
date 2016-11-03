<?php
/**
 * Joomla! System plugin - ScriptMerge
 *
 * @author    Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Import the parent class
jimport('joomla.plugin.plugin');

/**
 * ScriptMerge System Plugin
 */
class PlgSystemScriptMerge extends JPlugin
{
	/**
	 * @var JApplicationCms
	 */
	protected $app;

	/**
	 * @var JInput
	 */
	protected $input;

	/**
	 * @var string
	 */
	protected $helperFile;

	/**
	 * @var ScriptMergeHelper
	 */
	protected $helper;

	/**
	 * PlgSystemScriptMerge constructor
	 *
	 * @param $subject mixed
	 * @param $config array
	 *
	 * @return mixed
	 */
	public function __construct(&$subject, $config = array())
	{
		// Set the helper file and include the helper class
		$this->helperFile = JPATH_SITE . '/components/com_scriptmerge/helpers/helper.php';
		$this->includeHelper();

		$rt = parent::__construct($subject, $config);

		/** @var JInput input */
		$this->input = $this->app->input;

		return $rt;
	}

	/**
	 * Event onAfterRoute
	 */
	public function onAfterRoute()
	{
		// Don't do anything for non scriptmerge pages
		if ($this->input->getCmd('option') != 'com_scriptmerge')
		{
			return;
		}

		// Check for frontend
		if ($this->app->isSite() == false)
		{
			return;
		}

		// Require the helper
		if (empty($this->helper))
		{
			return;
		}

		// Output CSS or JavaScript
		$this->printBuffer();
	}

	/**
	 * Output CSS or JavaScript when requested
	 */
	protected function printBuffer()
	{
		// Add the HTTP Content-Type header
		$this->addContentTypeHeader();

		$type = $this->input->getString('type');

		// Read the files parameter
		$files = $this->input->getString('files');
		$buffer = null;

		if (!empty($files))
		{
			$files = $this->helper->decodeList($files);

			foreach ($files as $file)
			{
				if ($type == 'css')
				{
					if (!preg_match('/\.css$/', $file))
					{
						continue;
					}

					$buffer .= $this->helper->getCssContent($file) . PHP_EOL;
				}
				else
				{
					if (!preg_match('/\.js$/', $file))
					{
						continue;
					}

					$buffer .= $this->helper->getJsContent($file) . PHP_EOL;
				}
			}
		}

		if ($type == 'css')
		{
			// Clean up CSS-code
			$buffer = ScriptMergeHelper::cleanCssContent($buffer);

		}
		else
		{
			// Clean up JS-code
			$buffer = ScriptMergeHelper::cleanJsContent($buffer);
		}

		// Construct the expiration time
		$helperParams = $this->helper->getParams();
		$expires = (int) ($helperParams->get('expiration', 30) * 60);

		// Set the expiry in the future
		if ($expires > 0)
		{
			header('Cache-Control: public, max-age=' . $expires);
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires));
		}
		// Set the expiry in the past
		else
		{
			header("Cache-Control: no-cache, no-store, must-revalidate");
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() - (60 * 60 * 24)));
		}

		header('Vary: Accept-Encoding');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()));
		header('ETag: ' . md5($buffer));

		if (function_exists('gzencode') && $helperParams->get('force_gzip', 0) == 1)
		{
			header('Content-Encoding: gzip');
			print gzencode($buffer);
		}
		else
		{
			print $buffer;
		}

		// Close the application
		$this->app->close();
	}

	/**
	 * Event onAfterRender
	 *
	 * @param null
	 *
	 * @return null
	 */
	public function onAfterRender()
	{
		// Check if this plugin is enabled
		if ($this->isEnabled() == false)
		{
			return;
		}

		// Get the body and fetch a list of files
		$body = JResponse::getBody();

		// Fetch all the matches
		$matches = array();

		if ($this->isEnabledCss())
		{
			$matches['css'] = $this->getCssMatches($body);
		}

		if ($this->isEnabledJs())
		{
			$matches['js'] = $this->getJsMatches($body);
		}

		if ($this->isEnabledImg())
		{
			$matches['img'] = $this->getImgMatches($body);

			foreach ($matches['img'] as $img)
			{
				$newImageTag = $img['html'];

				if (stristr($newImageTag, 'width=') == false)
				{
					$newImageTag = str_replace('src=', 'width="' . $img['width'] . '" src=', $newImageTag);
				}

				if (stristr($newImageTag, 'height=') == false)
				{
					$newImageTag = str_replace('src=', 'height="' . $img['height'] . '" src=', $newImageTag);
				}

				$body = str_replace($img['html'], $newImageTag, $body);
			}
		}

		// Remove all current links from the document
		$body = $this->cleanup($body, $matches);

		// Parse images
		$body = $this->parseImages($body);

		// Add the new URL to the document
		$body = $this->addMergeUrl($body, $matches);

		// Make sure all MooTools scripts are loaded first
		if ($this->app->isAdmin() && $this->params->get('backend') == 1)
		{
			if (preg_match_all('/\<script([^\>]+)mootools(.*).js([^\>]+)\>\<\/script\>/', $body, $matches))
			{
				$scripts = null;

				foreach ($matches[0] as $match)
				{
					$body = str_replace($match, '', $body);
					$scripts .= $match . "\n";
				}

				$body = str_replace('<head>', '<head>' . $scripts, $body);
			}
		}

		JResponse::setBody($body);
	}

	/**
	 * Method to detect all the CSS stylesheets in the HTML-body
	 *
	 * @param string $body
	 *
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

		if (isset($matches[3]))
		{
			// Get the exclude-matches
			$exclude_css = explode(',', $this->params->get('exclude_css'));

			if (!empty($exclude_css))
			{
				foreach ($exclude_css as $i => $e)
				{
					$e = trim($e);

					if (empty($e))
					{
						unset($exclude_css[$i]);
					}
					else
					{
						$exclude_css[$i] = $e;
					}
				}
			}

			// Loop through the rules
			foreach ($matches[3] as $index => $match)
			{
				// Skip certain entries
				if (stripos($matches[0][$index], 'stylesheet') == false && stripos($matches[0][$index], 'css') == false)
				{
					continue;
				}

				if (stripos($matches[0][$index], 'media="print"'))
				{
                    $this->addLinkHeader($match);
					continue;
				}

				if (preg_match('/\.php\?(.*)/', $match))
				{
                    $this->addLinkHeader($match);
					continue;
				}

				// Only try to match local CSS
				$match = str_replace(JURI::base(), '', $match);
				$match = preg_replace('/^' . str_replace('/', '\/', JURI::base(true)) . '/', '', $match);

				if (preg_match('/^(?:https?:)?\/\//', $match))
				{
                    $this->addLinkHeader($match);
					continue;
				}

				if (!preg_match('/\.css(?:\?(?:\w+=)?(?:\w+|[0-9a-z\.\-]+))?$/', $match))
				{
					continue;
				}

				// Only include files that can be read
				$file = preg_replace('/\?(.*)/', '', $match);

				// Check for excludes
				if (!empty($exclude_css))
				{
					$hasMatch = false;

					foreach ($exclude_css as $exclude)
					{
						if (strstr($file, $exclude))
						{
							$hasMatch = true;
							break;
						}
					}

					if ($hasMatch == true)
					{
                        $this->addLinkHeader($match);
						continue;
					}
				}

				// Try to determine the path to this file
				$filepath = ScriptMergeHelper::getFilePath($file);

				if (empty($filepath))
				{
					continue;
				}

				$files[] = array(
					'remote' => 0,
					'file' => $filepath,
					'html' => $matches[0][$index],);
			}
		}

		return $files;
	}

	/**
	 * Method to detect all the JavaScript-scripts in the HTML-body
	 *
	 * @param string $body
	 *
	 * @return array
	 */
	private function getJsMatches($body = null)
	{
		// Remove conditional comments from matching
		$buffer = preg_replace('/<!--(.*)-->/msU', '', $body);

		// Detect all JavaScripts
		preg_match_all('/<script([^\>]+)src="([^\"]+)"(.*)><\/script>/msU', $buffer, $matches);

		// Build the list of files to include
		$excludes = trim($this->params->get('exclude_js'));
		$excludes = (!empty($excludes)) ? explode(',', $excludes) : array();

		foreach ($excludes as $i => $e)
		{
			$excludes[$i] = trim($e);
		}

		// Add extra scripts in the backend
		if ($this->app->isAdmin() && $this->params->get('backend') == 1)
		{
			$excludes[] = 'mootools.js';
			$excludes[] = 'mootools-core.js';
			$excludes[] = 'mootools-more.js';
			$excludes[] = 'mootools-uncompressed.js';
			$excludes[] = 'joomla.javascript.js';
			$excludes[] = 'menu.js';
		}

		// Parse all the matched entries
		$files = array();

		if (isset($matches[2]))
		{
			foreach ($matches[2] as $index => $match)
			{
				// Only try to match local JavaScript
				$match = str_replace(JURI::base(), '', $match);
				$match = preg_replace('/^' . str_replace('/', '\/', JURI::base(true)) . '/', '', $match);

				if (empty($match))
				{
					continue;
				}

				// Skip already compressed files
				if ($this->params->get('skip_compressed', 0) == 1)
				{
					if (preg_match('/\.(pack|min)\.js/', $match))
					{
                        $this->addLinkHeader($match);
						continue;
					}
				}

				if (preg_match('/\.php\?(.*)/', $match))
				{
                    $this->addLinkHeader($hasMatch);
					continue;
				}

				// Match files that should be excluded
				if (!empty($excludes) && !empty($match))
				{
					$doExclude = false;

					foreach ($excludes as $exclude)
					{
						if (empty($match) || empty($exclude))
						{
							continue;
						}

						if (strstr($match, $exclude) || stripos($match, $exclude))
						{
							$doExclude = true;
							break;
						}
					}

					if ($doExclude == true)
					{
                        $this->addLinkHeader($match);
						continue;
					}
				}

				// Only try to match local JS
				if (preg_match('/^(?:https?:)?\/\//', $match))
				{
                    $this->addLinkHeader($match);
					continue;
				}

				if (!preg_match('/\.js(?:\?(?:\w+=)?(?:\w+|[0-9a-z\.\-]+))?$/', $match))
				{
					continue;
				}

				// Only include files that can be read
				$match = preg_replace('/\?(.*)/', '', $match);
				$filepath = ScriptMergeHelper::getFilePath($match);

				if (empty($filepath))
				{
					continue;
				}

				if ($this->params->get('remove_mootools') == 1 && stristr($filepath, 'mootools'))
				{
					continue;
				}

				$files[] = array(
					'remote' => 0,
					'file' => $filepath,
					'html' => $matches[0][$index],);
			}
		}

		return $files;
	}

	/**
	 * Method which detects images and their size in the HTML body
	 *
	 * @param string $body
	 *
	 * @return array
	 */
	private function getImgMatches($body = null)
	{
		$files = array();

		if (preg_match_all('/<img(.*?)src=("|\'|)(.*?(png|jpg|jpeg|gif))("|\'| )(.*?)>/s', $body, $matches))
		{
			preg_match('/https?:(.*)/', JURI::base(), $uri_base);
			preg_match('/\/([a-zA-Z0-9\-\_\.]+)$/', JPATH_SITE, $root_dir);

			foreach ($matches[3] as $matchIndex => $imagePath)
			{
				$imagePath = str_replace($uri_base[1], '', $imagePath);
				$relativeImagePath = $imagePath;

				if (!empty($root_dir[0]))
				{
					$relativeImagePath = str_replace($root_dir[0], '', $relativeImagePath);
				}

				$imageDir = str_replace('//', '/', JPATH_SITE . '/' . $relativeImagePath);

				// Validates that is readable
				if (is_readable($imageDir) == true)
				{
					$img = getimagesize($imageDir);
					$toAdd = array(
						'file' => $imagePath,
						'width' => $img[0],
						'height' => $img[1],
						'html' => $matches[0][$matchIndex]);

					if (!in_array($toAdd, $files))
					{
						$files[] = $toAdd;
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
	 * @param array  $matches
	 *
	 * @return string
	 */
	private function addMergeUrl($body = null, $matches = array())
	{
		// Treat CSS and JS seperately
		foreach ($matches as $type => $list)
		{
			if (!empty($list))
			{
				// Create a base64-encoded list of the merged files
				if ($this->params->get('merge_type') == 'files')
				{
					$url = $this->buildMergeUrl($type, $list);

					// Create an unique signature for this filelist
				}
				else
				{
					$url = $this->buildCacheUrl($type, $list);
				}

				if ($type == 'css')
				{
                    $this->addLinkHeader($url);
					$tag = '<link rel="stylesheet" href="' . $url . '" type="text/css" />';
					$tag .= $this->getIncludeCss();
					$tag_position = $this->params->get('css_position');
				}
				else
				{
                    $this->addLinkHeader($url);
					$async = ($this->params->get('async_merged', 0) == 1) ? ' async' : '';
					$tag = '<script src="' . $url . '"' . $async . ' type="text/javascript"></script>';
					$tag_position = $this->params->get('js_position');
				}

				switch ($tag_position)
				{
					case 'body_end':
						$body = str_replace('</body>', $tag . '</body>', $body);
						$body = str_replace('<!-- plg_scriptmerge_' . md5($type) . ' -->', '', $body);
						break;

					case 'head_end':
						$body = str_replace('</head>', $tag . '</head>', $body);
						$body = str_replace('<!-- plg_scriptmerge_' . md5($type) . ' -->', '', $body);
						break;

					default:
						$body = str_replace('<!-- plg_scriptmerge_' . md5($type) . ' -->', $tag, $body);
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
	 * @param array  $list
	 *
	 * @return string
	 */
	private function buildMergeUrl($type, $list = array())
	{
		// Append the list as arguments to the URL
		$files = array();

		foreach ($list as $file)
		{
			$files[] = $file['file'];
		}

		$appId = $this->app->getClientId();
		$version = $this->params->get('version', 1);
		$files = ScriptMergeHelper::encodeList($files);
		$url = 'index.php?option=com_scriptmerge&format=raw&tmpl=component';
		$url .= '&type=' . $type . '&app=' . $appId . '&version=' . $version . '&files=' . $files;

		// Determine the right URL, based on the frontend or backend
		if ($this->app->isSite() == true)
		{
			$url = JRoute::_($url);
		}
		else
		{
			$url = JURI::root() . $url;
		}

		// Domainname sharding
		$domain = ($type == 'js') ? $this->params->get('js_domain') : $this->params->get('css_domain');
		$url = $this->replaceUrlDomain($url, $domain);
		$uri = JURI::getInstance();

		// Protocol change
		if ($uri->isSSL())
		{
			$url = str_replace('http://', 'https://', $url);
		}
		else
		{
			$url = str_replace('https://', 'http://', $url);
		}

		return $url;
	}

    private function addLinkHeader($link)
    {
        header('Link: <'.$link.'>; rel=preload', false);
    }

	/**
	 * Method to build the CSS / JavaScript cache
	 *
	 * @param string $type
	 * @param array  $list
	 *
	 * @return string
	 */
	private function buildCacheUrl($type, $list = array())
	{
		$tmp_path = JPATH_SITE . '/cache/plg_scriptmerge/';
		$cacheFile = null;

		// Check for the cache-path
		if (is_dir($tmp_path) == false)
		{
			jimport('joomla.filesystem.folder');
			JFolder::create($tmp_path);
		}

		if (!empty($list))
		{
			$cacheId = $this->getHashFromList($list);
			$cacheFile = $cacheId . '.' . $type;
			$cachePath = $tmp_path . '/' . $cacheFile;
			$cacheExpireFile = $cachePath . '_expire';

			$hasExpired = false;

			if (ScriptMergeHelper::hasExpired($cacheExpireFile, $cachePath))
			{
				$hasExpired = true;
			}

			if (file_exists($cachePath))
			{
				foreach ($list as $file)
				{
					if (file_exists($file['file']) == false)
					{
						$hasExpired = true;
						break;
					}

					if (filemtime($file['file'] > filemtime($cachePath)))
					{
						$hasExpired = true;
						break;
					}
				}
			}
			else
			{
				$hasExpired = true;
			}

			// Check the cache
			if ($hasExpired)
			{
				$buffer = null;

				foreach ($list as $file)
				{
					if (isset($file['file']))
					{
						// CSS-code
						if ($type == 'css')
						{
							$buffer .= ScriptMergeHelper::getCssContent($file['file']);

							// JS-code
						}
						else
						{
							$buffer .= ScriptMergeHelper::getJsContent($file['file']);
						}
					}
				}

				// Clean up CSS-code
				if ($type == 'css')
				{
					$buffer = ScriptMergeHelper::cleanCssContent($buffer);

					// Clean up JS-code
				}
				else
				{
					$buffer = ScriptMergeHelper::cleanJsContent($buffer);
				}

				// Write this buffer to a file
				jimport('joomla.filesystem.file');
				JFile::write($cachePath, $buffer);

				// Create a minified version of this file
				$this->createMinified($type, $cachePath);

				// Set the cache parameter
				$this->createCacheExpireFile($cacheExpireFile);
			}
		}

		if (empty($cacheFile))
		{
			return null;
		}

		// Construct the minified version
		if ($type == 'js')
		{
			$minifiedFile = preg_replace('/\.js$/', '.min.js', $cacheFile);
		}
		else
		{
			$minifiedFile = preg_replace('/\.css$/', '.min.css', $cacheFile);
		}

		// Return the minified version if it exists
		if (file_exists(JPATH_SITE . '/cache/plg_scriptmerge/' . $minifiedFile))
		{
			$url = JURI::root() . 'cache/plg_scriptmerge/' . $minifiedFile;

			// Return the cache-file itself
		}
		else
		{
			$url = JURI::root() . 'cache/plg_scriptmerge/' . $cacheFile;
		}

		// Domainname sharding
		$domain = ($type == 'js') ? $this->params->get('js_domain') : $this->params->get('css_domain');
		$url = $this->replaceUrlDomain($url, $domain);

		$uri = JURI::getInstance();

		// Protocol change
		if ($uri->isSSL())
		{
			$url = str_replace('http://', 'https://', $url);
		}
		else
		{
			$url = str_replace('https://', 'http://', $url);
		}

		return $url;
	}

	/**
	 * Method to return a hash representing the list of files
	 *
	 * @param $list
	 *
	 * @return string
	 */
	private function getHashFromList($list)
	{
		if ($this->params->get('hash_method') == 'simple')
		{
			return md5(serialize($list));
		}

		$hashes = array();

		foreach ($list as $item)
		{
			if (!empty($item['file']) && file_exists($item['file']) && is_readable($item['file']))
			{
				$hashes[] = md5_file($item['file']);
			}
		}

		return md5(serialize($hashes));
	}

	/**
	 * Method to replace the domain in an URL
	 *
	 * @param string $url
	 * @param string $domain
	 *
	 * @return string
	 */
	private function replaceUrlDomain($url, $domain)
	{
		$uri = JURI::getInstance();

		if (!empty($domain))
		{
			$domain = preg_replace('/\/$/', '', $domain);
			$applyDomain = true;
			$oldDomain = null;

			if (preg_match('/^(http|https)\:\/\//', $domain, $domainMatch))
			{
				if ($domainMatch[1] == 'http' && $uri->isSSL())
				{
					$applyDomain = false;
				}
				else
				{
					$oldDomain = JURI::root();
					$oldDomain = preg_replace('/\/$/', '', $oldDomain);
				}
			}
			else
			{
				$oldDomain = $uri->toString(array('host'));
			}

			if ($applyDomain && !empty($oldDomain))
			{
				if (preg_match('/^(http|https)\:\/\//', $url) == false)
				{
					$url = JURI::root() . preg_replace('/^\//', '', $url);
				}

				$url = str_replace($oldDomain, $domain, $url);
			}
		}

		return $url;
	}

	/**
	 * Method to remove obsolete tags in the HTML body
	 *
	 * @param string $body
	 * @param array  $matches
	 *
	 * @return string
	 */
	private function cleanup($body = null, $matches = array())
	{
		foreach ($matches as $typename => $files)
		{
			if (empty($files))
			{
				continue;
			}

			$first = true;

			foreach ($files as $file)
			{
				if ($first)
				{
					$body = str_replace($file['html'], '<!-- plg_scriptmerge_' . md5($typename) . ' -->', $body);
					$first = false;

					continue;
				}

				$body = preg_replace('/\s*' . preg_quote($file['html'], '/') . '/s', '', $body);
			}
		}

		if ($this->params->get('compress_html') == 1)
		{
			$body = $this->compressHtml($body);
		}

		return $body;
	}

	/**
	 * @param $html
	 *
	 * @return mixed
	 */
	private function compressHtml($html)
	{
		$html = str_replace("\n\n", "\n", $html);
		$html = str_replace("\r\r", "\r", $html);
		$html = preg_replace('/\>[^\S ]+/s', '>', $html);
		$html = preg_replace('/[^\S ]+\</s', '<', $html);
		$html = preg_replace('/\>[\s]+\</s', '><', $html);

		return $html;
	}

	/**
	 * Method to translate images into data URIs
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	private function parseImages($text = null)
	{
		if ($this->params->get('data_uris', 0) != 1)
		{
			return $text;
		}

		if (preg_match_all('/src=([\'\"]{1})([^\'\"]+)([\'\"]{1})/i', $text, $matches))
		{
			foreach ($matches[2] as $index => $match)
			{
				$match = preg_replace('/([\'\"\ ]+)/', '', $match);
				$path = ScriptMergeHelper::getFilePath($match);
				$content = ScriptMergeHelper::getImageUrl($path);

				if (!empty($content))
				{
					$text = str_replace($matches[0][$index], 'src=' . $content, $text);
				}
			}
		}

		if (preg_match_all('/url\(([a-zA-Z0-9\.\-\_\/\ \' \"]+)\)/i', $text, $matches))
		{
			foreach ($matches[1] as $index => $match)
			{
				$match = preg_replace('/([\'\"\ ]+)/', '', $match);
				$path = ScriptMergeHelper::getFilePath($match);
				$content = ScriptMergeHelper::getImageUrl($path);

				if (!empty($content))
				{
					$text = str_replace($matches[0][$index], 'url(' . $content . ')', $text);
				}
			}
		}

		if (preg_match_all('/url\(([a-zA-Z0-9\.\-\_\/\ \' \"]+)\)/i', $text, $matches))
		{
			foreach ($matches[1] as $index => $match)
			{
				$match = preg_replace('/([\'\"\ ]+)/', '', $match);
				$path = ScriptMergeHelper::getFilePath($match);
				$content = ScriptMergeHelper::getImageUrl($path);

				if (!empty($content))
				{
					$text = str_replace($matches[0][$index], 'url(' . $content . ')', $text);
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
	 *
	 * @return null
	 */
	private function createMinified($type, $file)
	{
		if ($type == 'js')
		{
			// Construct the new filename
			$newFile = preg_replace('/\.js$/', '.min.js', $file);

			// Try to use JSMIN
			$jsmin = $this->params->get('jsmin');

			if (!empty($jsmin) && $this->params->get('use_jsmin', 0) == 1)
			{
				exec("$jsmin < $file > $newFile");
			}
		}
		else
		{
			// Construct the new filename
			$newFile = preg_replace('/\.css$/', '.min.css', $file);
		}

		return $newFile;
	}

	/**
	 * Set a new cache expiration
	 *
	 * @param string $file
	 *
	 * @return null
	 */
	private function createCacheExpireFile($file)
	{
		$config = JFactory::getConfig();

		if (method_exists($config, 'getValue'))
		{
			$lifetime = (int) $config->getValue('config.lifetime');
		}
		else
		{
			$lifetime = (int) $config->get('config.lifetime');
		}

		if (empty($lifetime) || $lifetime < 120)
		{
			$lifetime = 120;
		}

		$time = time() + $lifetime;
		jimport('joomla.filesystem.file');
		JFile::write($file, $time);
	}

	/**
	 * Get an array from a parameter
	 *
	 * @param string $param
	 *
	 * @return array
	 */
	private function getArrayFromParam($param)
	{
		$data = $this->params->get($param);

		if (is_array($data))
		{
			return $data;
		}

		$data = trim($data);

		if (empty($data))
		{
			return array();
		}

		$data = explode(',', $data);

		$newData = array();

		foreach ($data as $value)
		{
			$value = trim($value);

			if (!empty($value))
			{
				$newData[] = $value;
			}
		}

		return $newData;
	}

	/**
	 * Check if this plugin is enabled
	 *
	 * @return boolean
	 */
	private function isEnabled()
	{
		// Only continue in the right application, if enabled so
		if ($this->app->isAdmin() && $this->params->get('backend', 0) == 0)
		{
			return false;
		}

		if ($this->app->isSite() && $this->params->get('frontend', 1) == 0)
		{
			return false;
		}

		// Dont do anything for the ScriptMerge component and the Plugin Manager
		if (in_array($this->input->getCmd('option'), array('com_plugins', 'com_scriptmerge')))
		{
			return false;
		}

		// Disable through URL
		if ($this->input->getInt('scriptmerge', 1) == 0)
		{
			return false;
		}

		// Require the helper
		if (empty($this->helper))
		{
			return false;
		}

		// Exclude for menus
		$menu = $this->app->getMenu('site');
		$current_menuitem = $menu->getActive();

		if (!empty($current_menuitem))
		{
			$exclude_menuitems = $this->getArrayFromParam('exclude_menuitems');

			foreach ($exclude_menuitems as $exclude_menuitem)
			{
				if ($exclude_menuitem == $current_menuitem->id)
				{
					return false;
				}
			}
		}

		// Exclude components
		$components = $this->params->get('exclude_components');

		if (empty($components))
		{
			$components = array();
		}

		if (!is_array($components))
		{
			$components = explode(',', $components);
		}

		if (in_array($this->input->getCmd('option'), $components))
		{
			return false;
		}

		return true;
	}

	/**
	 * Include the helper class and instantiate the helper
	 */
	private function includeHelper()
	{
		if (file_exists($this->helperFile) == false)
		{
			return;
		}

		// Include the helper
		require_once $this->helperFile;

		$this->helper = new ScriptMergeHelper;
	}

	/**
	 * Check if this plugin is enabled
	 *
	 * @return boolean
	 */
	private function isEnabledCss()
	{
		if ($this->params->get('enable_css', 1) == 0)
		{
			return false;
		}

		// Exclude user-agents
		$exclude_css_useragents = $this->getArrayFromParam('exclude_css_useragents');

		if (!empty($exclude_css_useragents) && !empty($_SERVER['HTTP_USER_AGENT']))
		{
			foreach ($exclude_css_useragents as $exclude_css_useragent)
			{
				if (stristr($_SERVER['HTTP_USER_AGENT'], $exclude_css_useragent))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Check if this plugin is enabled
	 *
	 * @return boolean
	 */
	private function isEnabledJs()
	{
		if ($this->params->get('enable_js', 0) == 0)
		{
			return false;
		}

		// Exclude user-agents
		$exclude_js_useragents = $this->getArrayFromParam('exclude_js_useragents');

		if (!empty($exclude_js_useragents) && !empty($_SERVER['HTTP_USER_AGENT']))
		{
			foreach ($exclude_js_useragents as $exclude_js_useragent)
			{
				if (stristr($_SERVER['HTTP_USER_AGENT'], $exclude_js_useragent))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Check if this plugin is enabled
	 *
	 * @return boolean
	 */
	private function isEnabledImg()
	{
		if ($this->params->get('enable_img', 0) == 0)
		{
			return false;
		}

		return true;
	}

	/**
	 * Return the configured include CSS if any
	 *
	 * @return string
	 */
	protected function getIncludeCss()
	{
		$includeCss = $this->params->get('include_css');
		$includeCss = trim($includeCss);

		if (!empty($includeCss))
		{
			$tag = "\n<style>" . $includeCss . "</style>";

			return $tag;
		}

		return '';
	}

	/**
	 * Add the Content Type header
	 *
	 * @return string
	 */
	public function addContentTypeHeader()
	{
		// Send the content-type header
		$type = $this->input->getString('type');

		if ($type == 'css')
		{
			header('Content-Type: text/css');

			return;
		}

		header('Content-Type: application/javascript');
	}
}
