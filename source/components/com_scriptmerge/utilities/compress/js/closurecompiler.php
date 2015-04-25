<?php
/**
 * Joomla! extension - ScriptMerge
 *
 * @author    Yireo (info@yireo.com)
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

class ScriptMergeUtilitiesCompressJsClosurecompiler implements ScriptMergeUtilitiesCompressInterface
{
	public function compress($string)
	{
		// Compress the js-code through the Google Closure Compiler API
		$url = 'http://closure-compiler.appspot.com/compile';

		// Set the POST-variables
		$post = array(
			'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
			'output_format' => 'json',
			'output_info' => 'compiled_code',
			'js_code' => urlencode($string),
		);

		// Initialize CURL
		$handle = curl_init($url);

		curl_setopt_array($handle, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_MAXREDIRS => 0,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_TIMEOUT => 10)
		);

		curl_setopt($handle, CURLOPT_POST, true);
		curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($post));

		// Only proceed with under 200.000 bytes
		if (strlen($string) < 200000)
		{
			$data = curl_exec($handle);
			$json = json_decode($data, true);

			if (!empty($json['compiledCode']))
			{
				$string = $json['compiledCode'];
			}
		}

		return $string;
	}
}