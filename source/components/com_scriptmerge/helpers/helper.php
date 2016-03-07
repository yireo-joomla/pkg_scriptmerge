<?php
/**
 * Joomla! System plugin - ScriptMerge
 *
 * @author    Yireo (info@yireo.com)
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Import the parent class
jimport('joomla.plugin.plugin');

require_once JPATH_SITE . '/components/com_scriptmerge/utilities/compress.php';

/**
 * ScriptMerge Helper
 */
class ScriptMergeHelper
{
	/**
	 * Method to return the output of a JavaScript file
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	static public function getJsContent($file)
	{
		// Don't try to parse empty (or non-existing) files
		if (empty($file))
		{
			return null;
		}

		if (file_exists($file) == false || is_readable($file) == false)
		{
			return null;
		}

		// Initialize the buffer
		$buffer = file_get_contents($file);

		if (empty($buffer))
		{
			return null;
		}

		if (substr($buffer, 0, 5) === '<?php')
		{
			return null;
		}

		// Initialize the basepath
		$basefile = self::getFileUrl($file, false);

		// If compression is enabled
		$application = JFactory::getApplication();
		$compress_js = self::getParams()
			->get('compress_js');

		if ($application->isSite() && !empty($compress_js))
		{
			// JsMinPlus definitely does not work with MooTools (for now)
			if ($compress_js == 'jsminplus' && stristr($file, 'mootools') == true)
			{
				$compress_js = 'simple';
			}

			// Load the compression class and run the right compression method
			$compressor = new ScriptMergeUtilitiesCompress;
			$compressor->setHandler($compress_js);
			$buffer = $compressor->compressJs($buffer);

			// Make sure the JS-content ends with ;
			$buffer = trim($buffer);

			if (preg_match('/;\$/', $buffer) == false)
			{
				$buffer .= ';' . "\n";
			}

			// Append the filename to the JS-code
			if (self::getParams()
				->get('use_comments', 1)
			)
			{
				$start = "/* [scriptmerge/start] JavaScript file: $basefile */\n\n";
				$end = "/* [scriptmerge/end] JavaScript file: $basefile */\n\n";
				$buffer = $start . $buffer . "\n" . $end;
			}
			else
			{
				$buffer .= "\n";
			}

			// If compression is disabled
		}
		else
		{
			// Make sure the JS-content ends with ;
			$buffer = trim($buffer);

			if (preg_match('/;\$/', $buffer) == false)
			{
				$buffer .= ';' . "\n";
			}

			// Remove extra semicolons
			$buffer = preg_replace("/;;\n/", ';', $buffer);

			// Append the filename to the JS-code

			if (self::getParams()
				->get('use_comments', 1)
			)
			{
				$start = "/* [scriptmerge/start] Uncompressed JavaScript file: $basefile */\n\n";
				$end = "/* [scriptmerge/end] Uncompressed JavaScript file: $basefile */\n\n";
				$buffer = $start . $buffer . "\n" . $end;
			}
		}

		// Detect jQuery
		if (strstr($buffer, 'define("jquery",'))
		{
			$buffer .= "jQuery.noConflict();\n";
		}

		return $buffer;
	}

	/**
	 * Method to clean the final JS
	 *
	 * @param string $buffer
	 *
	 * @return string
	 */
	static public function cleanJsContent($buffer)
	{
		return $buffer;
	}

	/**
	 * Method to return the output of a CSS file
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	static public function getCssContent($file)
	{
		// Only inlude a file once
		static $parsed_files = array();

		if (in_array($file, $parsed_files))
		{
			return " ";
		}

		$parsed_files[] = $file;

		// Don't try to parse empty (or non-existing) files
		if (empty($file) || file_exists($file) == false)
		{
			return null;
		}

		if (is_readable($file) == false)
		{
			return null;
		}

		// Skip files that have already been included
		static $files = array();

		if (in_array($file, $files))
		{
			return null;
		}
		else
		{
			$files[] = $file;
		}

		// Initialize the buffer
		$buffer = file_get_contents($file);

		if (empty($buffer))
		{
			return null;
		}

		// Create a raw buffer with comments stripped
		$regex = array(
			"`^([\t\s]+)`ism" => '',
			"`^\/\*(.+?)\*\/`ism" => "",
			"`([\n\A;]+)\/\*(.+?)\*\/`ism" => "$1",
			"`([\n\A;\s]+)//(.+?)[\n\r]`ism" => "$1\n",
			"`(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+`ism" => "\n");
		$rawBuffer = preg_replace(array_keys($regex), $regex, $buffer);

		// Initialize the basepath
		$basefile = self::getFileUrl($file, false);

		// Follow all @import rules
		$imports = array();

		if (self::getParams()
				->get('follow_imports', 1) == 1
		)
		{
			if (preg_match_all('/@import\ (.*);/i', $rawBuffer, $matches))
			{
				foreach ($matches[1] as $index => $match)
				{
					// Strip quotes
					$match = str_replace('url(', '', $match);
					$match = str_replace('\'', '', $match);
					$match = str_replace('"', '', $match);
					$match = str_replace(')', '', $match);
					$match = trim($match);

					// Skip URLs and data-URIs
					if (preg_match('/^(http|https):\/\//', $match))
					{
						continue;
					}

					$importFile = self::getFilePath($match, $file);

					if (empty($importFile) && strstr($importFile, '/') == false)
					{
						$importFile = dirname($file) . '/' . $match;
					}

					$importBuffer = self::getCssContent($importFile);
					$importUrl = self::getFileUrl($importFile, false);

					if (!empty($importBuffer))
					{
						if (self::getParams()
							->get('use_comments', 1)
						)
						{
							$buffer .= "\n/* [scriptmerge/notice] CSS import of $importUrl */\n\n" . $buffer;
						}

						$buffer .= "\n" . $importBuffer . "\n";
						$buffer = str_replace($matches[0][$index], "\n", $buffer);
						$imports[] = $matches[1][$index];

					}
					else
					{
						$buffer .= "\n/* [scriptmerge/error] CSS import of $importUrl returned empty */\n\n" . $buffer;
					}
				}
			}
		}

		// Replace all relative paths with absolute paths
		if (preg_match_all('/url\(([^\(]+)\)/i', $rawBuffer, $url_matches))
		{
			foreach ($url_matches[1] as $url_index => $url_match)
			{
				// Strip quotes
				$url_match = str_replace('\'', '', $url_match);
				$url_match = str_replace('"', '', $url_match);

				// Skip CSS-stylesheets which need to be followed differently anyway
				if (strstr($url_match, '.css'))
				{
					continue;
				}

				// Skip URLs and data-URIs
				if (preg_match('/^(http|https):\/\//', $url_match))
				{
					continue;
				}

				if (preg_match('/^\/\//', $url_match))
				{
					continue;
				}

				if (preg_match('/^data\:/', $url_match))
				{
					continue;
				}

				// Normalize this path
				$url_match_path = self::getFilePath($url_match, $file);

				if (empty($url_match_path) && strstr($url_match, '/') == false)
				{
					$url_match_path = dirname($file) . '/' . $url_match;
				}

				if (!empty($url_match_path))
				{
					$url_match = self::getFileUrl($url_match_path);
				}

				// Replace image URLs
				$imageContent = self::getImageUrl($url_match_path);

				if (!empty($imageContent))
				{
					$url_match = $imageContent;
				}

				$buffer = str_replace($url_matches[0][$url_index], 'url(' . $url_match . ')', $buffer);
			}
		}

		// Detect PNG-images and try to replace them with WebP-images
		if (preg_match_all('/([a-zA-Z0-9\-\_\/]+)\.(png|jpg|jpeg)/i', $rawBuffer, $matches))
		{
			foreach ($matches[0] as $index => $image)
			{
				$webp = self::getWebpImage($image);

				if ($webp != false && !empty($webp))
				{
					$buffer = str_replace($image, $webp, $buffer);
				}
			}
		}

		// Move all @import-lines to the top of the CSS-file
		$regexp = '/@import (.*);/i';

		if (preg_match_all($regexp, $rawBuffer, $matches))
		{
			$buffer = preg_replace($regexp, '', $buffer);
			$matches[0] = array_unique($matches[0]);

			foreach ($matches[0] as $index => $match)
			{
				if (in_array($matches[1][$index], $imports))
				{
					unset($matches[0][$index]);
				}
			}

			$buffer = implode("\n", $matches[0]) . "\n" . $buffer;
		}

		// If compression is enabled
		$compress_css = self::getParams()
			->get('compress_css', 0);

		if ($compress_css > 0)
		{
			switch ($compress_css)
			{
				case 1:
					$buffer = preg_replace('#[\r\n\t\s]+//[^\n\r]+#', ' ', $buffer);
					$buffer = preg_replace('/[\r\n\t\s]+/s', ' ', $buffer);
					$buffer = preg_replace('#/\*.*?\*/#', '', $buffer);
					$buffer = preg_replace('/[\s]*([\{\},;:])[\s]*/', '\1', $buffer);
					$buffer = preg_replace('/^\s+/', '', $buffer);
					$buffer .= "\n";
					break;

				case 2:
					// Compress the CSS-code
					$cssMin = JPATH_SITE . '/components/com_scriptmerge/lib/cssmin.php';

					if (file_exists($cssMin))
					{
						include_once $cssMin;
					}
					if (class_exists('CssMin'))
					{
						$buffer = CssMin::minify($buffer);
					}
					break;

				case 0:
				default:
					break;
			}

			// If compression is disabled
		}
		else
		{
			// Append the filename to the CSS-code
			if (self::getParams()
				->get('use_comments', 1)
			)
			{
				$start = "/* [scriptmerge/start] CSS-stylesheet: $basefile */\n\n";
				$end = "/* [scriptmerge/end] CSS-stylesheet: $basefile */\n\n";
				$buffer = $start . $buffer . "\n" . $end;
			}
		}

		return $buffer;
	}

	/**
	 * Method to clean the final CSS
	 *
	 * @param string $buffer
	 *
	 * @return string
	 */
	static public function cleanCssContent($buffer)
	{
		// Move all @import-lines to the top of the CSS-file
		$regexp = '/@import[^;]+;/i';

		if (preg_match_all($regexp, $buffer, $matches))
		{
			$buffer = preg_replace($regexp, '', $buffer);
			$buffer = implode("\n", $matches[0]) . "\n" . $buffer;
		}

		return $buffer;
	}

	/**
	 * Method to return the WebP-equivalent of an image, if possible
	 *
	 * @param string $imageUrl
	 *
	 * @return string
	 */
	static public function getWebpImage($imageUrl)
	{
		// Check if WebP support is enabled
		if (self::getParams()
				->get('use_webp', 0) == 0
		)
		{
			return false;
		}

		// Check for WebP support
		$webp_support = false;

		// Check for the "webp" cookie
		if (isset($_COOKIE['webp']) && $_COOKIE['webp'] == 1)
		{
			$webp_support = true;

			// Check for Chrome 9 or higher
		}
		else
		{
			if (preg_match('/Chrome\/([0-9]+)/', $_SERVER['HTTP_USER_AGENT'], $match) && $match[1] > 8)
			{
				$webp_support = true;
			}
		}

		if ($webp_support == false)
		{
			return false;
		}

		// Check for the cwebp binary
		$cwebp = self::getParams()
			->get('cwebp', '/usr/local/bin/cwebp');

		if (empty($cwebp) || file_exists($cwebp) == false)
		{
			return false;
		}

		if (function_exists('exec') == false)
		{
			return false;
		}

		if (preg_match('/^(http|https):\/\//', $imageUrl) && strstr($imageUrl, JURI::root()))
		{
			$imageUrl = str_replace(JURI::root(), '', $imageUrl);
		}

		$imagePath = JPATH_ROOT . '/' . $imageUrl;

		if (file_exists($imagePath) && @is_file($imagePath))
		{
			// Detect alpha-transparency in PNG-images and skip it
			if (preg_match('/\.png$/', $imagePath))
			{
				$imageContents = @file_get_contents($imagePath);
				$colorType = ord(@file_get_contents($imagePath, null, null, 25, 1));

				if ($colorType == 6 || $colorType == 4)
				{
					return false;
				}
				else
				{
					if (stripos($imageContents, 'PLTE') !== false && stripos($imageContents, 'tRNS') !== false)
					{
						return false;
					}
				}
			}

			$webpPath = preg_replace('/\.(png|jpg|jpeg|gif)$/', '.webp', $imagePath);

			if (@is_file($webpPath) == false)
			{
				$cmd = "$cwebp -q 100 $imagePath -o $webpPath";
				exec($cmd);
			}

			if (@is_file($webpPath))
			{
				$webpUrl = str_replace(JPATH_ROOT, '', $webpPath);
				$webpUrl = preg_replace('/^\//', '', $webpUrl);
				$webpUrl = preg_replace('/^\//', '', $webpUrl);
				$webpUrl = JURI::root() . $webpUrl;

				return $webpUrl;
			}
		}

		return false;
	}

	/**
	 * Method to translate an image into data URI
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	static public function getImageUrl($file = null)
	{
		// If this is not a file, do not continue
		if (file_exists($file) == false || @is_readable($file) == false)
		{
			return null;
		}

		// If this is not an image, do not continue
		if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file) == false)
		{
			return null;
		}

		$image_domain = self::getParams()
			->get('image_domain');

		if (!empty($image_domain))
		{
			$image_url_replace = true;

			if (preg_match('/^(http|https)\:\/\//', $image_domain, $image_domain_match))
			{
				if ($image_domain_match[1] == 'http' && JURI::getInstance()
						->isSSL()
				)
				{
					$image_url_replace = false;
				}
			}
			if ($image_url_replace == true)
			{
				$image_url = str_replace(JPATH_SITE, $image_domain, $file);

				return $image_url;
			}
		}

		// Disable further processing
		if (self::getParams()
				->get('data_uris', 0) == 0
		)
		{
			return self::getFileUrl($file);
		}

		// Check the file-length
		if (filesize($file) > self::getParams()
				->get('data_uris_filesize', 2000)
		)
		{
			return null;
		}

		// Fetch the content
		$content = @file_get_contents($file);

		if (empty($content))
		{
			return null;
		}

		$mimetype = null;

		if (preg_match('/\.gif$/i', $file))
		{
			$mimetype = 'image/gif';
		}
		else
		{
			if (preg_match('/\.png$/i', $file))
			{
				$mimetype = 'image/png';
			}
			else
			{
				if (preg_match('/\.webp$/i', $file))
				{
					$mimetype = 'image/webp';
				}
				else
				{
					if (preg_match('/\.(jpg|jpeg)$/i', $file))
					{
						$mimetype = 'image/jpg';
					}
				}
			}
		}

		if (!empty($content) && !empty($mimetype))
		{
			return 'data:' . $mimetype . ';base64,' . base64_encode($content);
		}

		return null;
	}

	/**
	 * Check if the cache has expired
	 *
	 * @param string $timestampFile
	 * @param string $cacheFile
	 *
	 * @return null
	 */
	static public function hasExpired($timestampFile, $cacheFile)
	{
		// Check for browser request
		if (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] == 'no-cache')
		{
			return true;
		}

		// Check if the expiration file exists
		if (file_exists($timestampFile) && @is_file($timestampFile))
		{
			$time = (int) @file_get_contents($timestampFile);

			if ($time < time())
			{
				jimport('joomla.filesystem.file');
				JFile::delete($timestampFile);
				JFile::delete($cacheFile);

				return true;
			}

			return false;
		}

		return true;
	}

	/**
	 * Set a new cache expiration
	 *
	 * @param string $file
	 *
	 * @return null
	 */
	private function setCacheExpire($file)
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
	 * Get a valid file URL
	 *
	 * @param string $path
	 * @param bool   $include_url
	 *
	 * @return string
	 */
	static public function getFileUrl($path, $include_url = true)
	{
		$uri = JURI::getInstance();
		$path = str_replace(JPATH_SITE . '/', '', $path);

		if ($include_url)
		{
			$path = JURI::root() . $path;
		}

		if ($uri->isSSL())
		{
			$path = str_replace('http://', 'https://', $path);
		}
		else
		{
			$path = str_replace('https://', 'http://', $path);
		}

		return $path;
	}

	/**
	 * realpath() replacement
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	static public function realpath($file)
	{
		// Return the file (Windows differently than Linux)
		if (DIRECTORY_SEPARATOR == '\\')
		{
			return $file;
		}

		return realpath($file);
	}

	/**
	 * Get a valid filename
	 *
	 * @param string $file
	 * @param string $base_path
	 *
	 * @return string
	 */
	static public function getFilePath($file, $base_path = null)
	{
		$app = JFactory::getApplication();

		// If this begins with a data URI, skip it
		if (preg_match('/^data\:/', $file))
		{
			return null;
		}

		// Strip any URL parameter from this
		$file = preg_replace('/\?(.*)/', '', $file);

		// If this is already a correct path, return it
		if (@is_file($file) && @is_readable($file))
		{
			return self::realpath($file);
		}

		// Strip the base-URL from this path
		$file = str_replace(JURI::root(), '', $file);

		// Determine the application path
		$appId = $app->input->getInt('app', $app->getClientId());

		if ($appId == 1)
		{
			$app_path = JPATH_ADMINISTRATOR;
		}
		else
		{
			$app_path = JPATH_SITE;
		}

		// Make sure the basepath is not a file
		if (@is_file($base_path))
		{
			$base_path = dirname($base_path);
		}

		// Determine the basepath
		if (empty($base_path))
		{
			if (substr($file, 0, 1) == '/')
			{
				$base_path = JPATH_SITE;
			}
			else
			{
				$base_path = $app_path;
			}
		}

		// Append the root
		if (@is_file(JPATH_SITE . '/' . $file))
		{
			return self::realpath(JPATH_SITE . '/' . $file);
		}

		// Append the base_path
		if (strstr($file, $base_path) == false && !empty($base_path))
		{
			$file = $base_path . '/' . $file;

			if (@is_file($file))
			{
				return self::realpath($file);
			}
		}

		// Detect the right application-path
		if ($app->isAdmin())
		{
			if (strstr($file, JPATH_ADMINISTRATOR) == false && @is_file(JPATH_ADMINISTRATOR . '/' . $file))
			{
				$file = JPATH_ADMINISTRATOR . '/' . $file;
			}
			else
			{
				if (strstr($file, JPATH_SITE) == false && @is_file(JPATH_SITE . '/' . $file))
				{
					$file = JPATH_SITE . '/' . $file;
				}
			}
		}
		else
		{
			if (strstr($file, JPATH_SITE) == false && @is_file(JPATH_SITE . '/' . $file))
			{
				$file = JPATH_SITE . '/' . $file;
			}
		}

		// If this is not a file, return empty
		if (@is_file($file) == false || @is_readable($file) == false)
		{
			return null;
		}

		return self::realpath($file);
	}

	/**
	 * Encode the file-list
	 *
	 * @param array $files
	 *
	 * @return string
	 */
	static public function encodeList($files)
	{
		$files = implode(',', $files);
		$files = str_replace(JPATH_ADMINISTRATOR . '/', '$B', $files);
		$files = str_replace(JPATH_SITE . '/', '$F', $files);
		$files = str_replace('template', '$T', $files);
		$files = str_replace('js', '$J', $files);
		$files = str_replace('media', '$M', $files);
		$files = str_replace('css', '$C', $files);
		$files = str_replace('system', '$S', $files);
		$files = str_replace('layout', '$l', $files);
		$files = str_replace('cache', '$c', $files);
		$files = str_replace('font', '$f', $files);
		$files = str_replace('tools', '$t', $files);
		$files = str_replace('widgetkit', '$w', $files);
		$files = base64_encode($files);

		return $files;
	}

	/**
	 * Decode the file-list
	 *
	 * @param string $files
	 *
	 * @return array
	 */
	static public function decodeList($files)
	{
		$files = base64_decode($files);
		$files = str_replace('$F', JPATH_SITE . '/', $files);
		$files = str_replace('$B', JPATH_ADMINISTRATOR . '/', $files);
		$files = str_replace('$T', 'template', $files);
		$files = str_replace('$J', 'js', $files);
		$files = str_replace('$M', 'media', $files);
		$files = str_replace('$C', 'css', $files);
		$files = str_replace('$S', 'system', $files);
		$files = str_replace('$l', 'layout', $files);
		$files = str_replace('$c', 'cache', $files);
		$files = str_replace('$f', 'font', $files);
		$files = str_replace('$t', 'tools', $files);
		$files = str_replace('$w', 'widgetkit', $files);
		$files = explode(',', $files);

		return $files;
	}

	/**
	 * Send HTTP headers
	 *
	 * @param string                    $buffer
	 * @param \Joomla\Registry\Registry $params
	 * @param bool                      $gzip
	 *
	 * @return array
	 */
	static public function sendHttpHeaders($buffer, $params, $gzip = false)
	{
		// Send the content-type header
		$app = JFactory::getApplication();
		$type = $app->input->getString('type');

		if ($type == 'css')
		{
			header('Content-Type: text/css');
		}
		else
		{
			header('Content-Type: application/javascript');
		}

		// Construct the expiration time
		$expires = (int) $params->get('expiration', 30) * 60;

		// Set the expiry in the future
		if ($expires > 0)
		{
			header('Cache-Control: public, max-age=' . $expires);
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires));

			// Set the expiry in the past
		}
		else
		{
			header("Cache-Control: no-cache, no-store, must-revalidate");
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() - (60 * 60 * 24)));
		}

		header('Vary: Accept-Encoding');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()));
		header('ETag: ' . md5($buffer));

		if ($gzip == true)
		{
			header('Content-Encoding: gzip');
		}
	}

	/**
	 * Load the parameters
	 *
	 * @param null
	 *
	 * @return JRegistry
	 */
	static public function getParams()
	{
		$plugin = JPluginHelper::getPlugin('system', 'scriptmerge');
		$params = new JRegistry($plugin->params);

		return $params;
	}
}
