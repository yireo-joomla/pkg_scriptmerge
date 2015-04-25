<?php
/**
 * Joomla! extension - ScriptMerge
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class ScriptMergeUtilitiesCompressJsSimple implements ScriptMergeUtilitiesCompressInterface
{
	public function compress($string)
	{
		$string = str_replace('/// ', '///', $string);
		$string = str_replace(',//', ', //', $string);
		$string = str_replace('{//', '{ //', $string);
		$string = str_replace('}//', '} //', $string);
		$string = str_replace('/**/', '/*  */', $string);
		$string = preg_replace("/\/\/.*\n\/\/.*\n\/\/.*\n\/\/.*\n\/\/.*\n\/\/.*\n\/\/.*\n\/\/.*\n/", "", $string);
		$string = preg_replace("/\/\/.*\n\/\/.*\n\/\/.*\n\/\/.*\n\/\/.*\n\/\/.*\n\/\/.*\n/", "", $string);
		$string = preg_replace("/\/\/.*\n\/\/.*\n\/\/.*\n\/\/.*\n\/\/.*\n\/\/.*\n/", "", $string);
		$string = preg_replace("/\/\/.*\n\/\/.*\n\/\/.*\n\/\/.*\n\/\/.*\n/", "", $string);
		$string = preg_replace("/\/\/.*\n\/\/.*\n\/\/.*\n\/\/.*\n/", "", $string);
		$string = preg_replace("/\/\/.*\n\/\/.*\n\/\/.*\n/", "", $string);
		$string = preg_replace('/\/\/.*\/\/\n/', '', $string);
		$string = preg_replace("/\s\/\/\".*/", "", $string);
		$string = preg_replace("/\/\/\n/", "\n", $string);
		$string = preg_replace("/\/\/\s.*.\n/", "\n  \n", $string);
		$string = preg_replace('/\/\/w[^w].*/', '', $string);
		$string = preg_replace('/\/\/s[^s].*/', '', $string);
		$string = preg_replace('/\/\/\*\*\*.*/', '', $string);
		$string = preg_replace('/\/\/\*\s\*\s\*.*/', '', $string);
		$string = preg_replace('!/\*[^\'."].*?\*/!s', '', $string);
		$string = preg_replace('/\n\s*\n/', "\n", $string);
		$string = preg_replace("/<!--.*-->/Us", "", $string);

		return $string;
	}
}