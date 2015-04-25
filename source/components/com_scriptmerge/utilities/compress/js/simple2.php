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

class ScriptMergeUtilitiesCompressJsSimple2 implements ScriptMergeUtilitiesCompressInterface
{
	public function compress($string)
	{
		$string = str_replace('/// ', '///', $string);
		$string = str_replace(',//', ', //', $string);
		$string = str_replace('{//', '{ //', $string);
		$string = str_replace('}//', '} //', $string);
		$string = str_replace('*//*', '*/  /*', $string);
		$string = str_replace('/**/', '/*  */', $string);
		$string = str_replace("*/", "\n */", $string);
		$string = str_replace('*///', '*/ //', $string);
		$string = preg_replace("/([0-9]\s*)(\/\/)(\s*[0-9]+\s*\")/", "$1/dxzdxz/$3dxzdxz", $string);
		$string = preg_replace("/\/\/.*\n\/\/.*\n/", "", $string);
		$string = preg_replace("/\s\/\/\".*/", "", $string);
		$string = preg_replace("/\/\/\n/", "\n", $string);
		$string = preg_replace("/\/\/\s[a-zA-Z0-9\-=+\|!@#$%^&()`~\[\]{};:\'\",<.>?]*[\n\r]/", "\n  \n", $string);
		$string = preg_replace('/\/\/w[^w].*/', '', $string);
		$string = preg_replace('/\/\/s[^s].*/', '', $string);
		$string = preg_replace('/\/\/\*\*\*.*/', '', $string);
		$string = preg_replace('/\/\/\*\s\*\s\*.*/', '', $string);
		$string = preg_replace('/[^\*]\/\/[*].*/', '', $string);
		$string = preg_replace('/([;])\/\/.*/', '$1', $string);
		$string = preg_replace('/((\r)|(\n)|(\R)|([^0]1)|([^\"]\s*\-))(\/\/)(.*)/', '$1', $string);
		$string = preg_replace("/([^\*])[\/]+\/\*.*[^a-zA-Z0-9\s\-=+\|!@#$%^&()`~\[\]{};:\'\",<.>?]/", "$1", $string);
		$string = preg_replace("/\/\*/", "\n/*dddpp", $string);
		$string = preg_replace('/((\{\s*|:\s*)[\"\']\s*)(([^\{\};\"\']*)dddpp)/', '$1$4', $string);
		$string = preg_replace("/\*\//", "xxxpp*/\n", $string);
		$string = preg_replace('/((\{\s*|:\s*|\[\s*)[\"\']\s*)(([^\};\"\']*)xxxpp)/', '$1$4', $string);
		$string = preg_replace('/([\"\'])\s*\/\*/', '$1/*', $string);
		$string = preg_replace('/(\n)[^\'"]?\/\*dddpp.*?xxxpp\*\//s', '', $string);
		$string = preg_replace('/\n\/\*dddpp([^\s]*)/', '$1', $string);
		$string = preg_replace('/xxxpp\*\/\n([^\s]*)/', '*/$1', $string);
		$string = preg_replace('/xxxpp\*\/\n([\"])/', '$1', $string);
		$string = preg_replace('/(\*)\n*\s*(\/\*)\s*/', '$1$2$3', $string);
		$string = preg_replace('/(\*\/)\s*(\")/', '$1$2', $string);
		$string = preg_replace('/\/\*dddpp(\s*)/', '/*', $string);
		$string = preg_replace('/\n\s*\n/', "\n", $string);
		$string = preg_replace("/([^\'\"]\s*)<!--.*-->(?!(<\/div>)).*/", "$1", $string);
		$string = preg_replace('/([^\n\w\-=+\|!@#$%^&*()`~\[\]{};:\'",<.>\/?\\\\])(\/\/)(.*)/', '$1', $string);
		$string = preg_replace("/\/\/\s.*[\n|\r]/", "\n  \n", $string);
		$string = preg_replace('/dxzdxz/', '', $string);
		$string = preg_replace('/\s+(\*\/)/', '$1', $string);

		// Remove all whitespaces
		$string = preg_replace('/\s+/', ' ', $string);
		$string = preg_replace('/\s*(?:(?=[=\-\+\|%&\*\)\[\]\{\};:\,\.\<\>\!\@\#\^`~]))/', '', $string);
		$string = preg_replace('/(?:(?<=[=\-\+\|%&\*\)\[\]\{\};:\,\.\<\>\?\!\@\#\^`~]))\s*/', '', $string);
		$string = preg_replace('/([^a-zA-Z0-9\s\-=+\|!@#$%^&*()`~\[\]{};:\'",<.>\/?])\s+([^a-zA-Z0-9\s\-=+\|!@#$%^&*()`~\[\]{};:\'",<.>\/?])/', '$1$2', $string);

		return $string;
	}
}