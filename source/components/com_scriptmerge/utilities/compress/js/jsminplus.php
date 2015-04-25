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

class ScriptMergeUtilitiesCompressJsJsminplus implements ScriptMergeUtilitiesCompressInterface
{
	public function compress($string)
	{
		// Compress the js-code
		$jsMinPhp = JPATH_SITE . '/components/com_scriptmerge/lib/jsminplus.php';

		if (file_exists($jsMinPhp))
		{
			include_once $jsMinPhp;

			if (class_exists('JSMinPlus'))
			{
				$string = JSMinPlus::minify($string);
			}
		}

		return $string;
	}
}