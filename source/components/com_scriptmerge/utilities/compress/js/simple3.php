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

class ScriptMergeUtilitiesCompressJsSimple3 implements ScriptMergeUtilitiesCompressInterface
{
	public function compress($string)
	{
		$string = preg_replace("/(\*\/\s*)\/\/(?!(\*\/|[^\r\n]*?[\\\\n\\\\r]+\s*\"\s*\+|[^\r\n]*?[\\\\n\\\\r]+\s*\'\s*\+))[^\n\r\;]*?[^\;]\s*[\n\r]/", "$1\n", $string);
		do
		{
			$string = preg_replace("/(http(s)?\:)([^\r\n]*?)(\/\/)/", "$1$3qDdXX", $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace("/(^^\s*\/)(\/).*/", "\n", $string, 1, $count);
		} while ($count);
		$string = preg_replace("/([\r\n]+?\s*|\,\s*|\;\s*|\|\s*|\)\s*|\+\s*|\&\s*|\{\s*|\}\s*|\]\s*|\[\s*|\+\s*|\'\s*|\"\s*|\:\s*|-\s*)((\/)(\/)+)([^\r\n\'\"]*?[nte]'[a-z])*?(?!([^\r\n]*?)([\'\"]|[\\\\]|\*\/|[=]+\s*\";|[=]+\s*\';)).*/", "$1\n", $string);
		$string = preg_replace("/(^^\s*\/\*)(?!([\'\"]))[\s\S]*?(\*\/)/", "\n \n", $string);
		$string = preg_replace("/(\|\|\s*|=\s*|[\n\r]|\;\s*|\,\s*|\{\s*|\}\s*|\+\s*|\?\s*)((?!([\'\"]))\/\*)(?!(\*\/))[\s\S]*?(\*\/)/", "$1\n", $string);
		$string = preg_replace("/(\;\s*|\,\s*|\{\s*|\}\s*|\+\s*|\?\s*|[\n\r]\s*)((?!([\'\"]))\/\*)(?!(\*\/))[\s\S]*?(\*\/)/", "$1\n", $string);
		do
		{
			$string = preg_replace('/([^\/\"\'\*a-zA-Z0-9\>])\/\*(?!(\*\/))[^\n\r@]*?\*\/(?=([\/\"\'\\\\\*a-zA-Z0-9\>\s=\)\(\,:;\.\}\{\|\]\[]))/', "$1", $string, 1, $count);
		} while ($count);
		$string = preg_replace("/([\;\n\r]\s*)\/\/.*/", "$1\n", $string);
		$string = preg_replace("/(\/\*)(\\\\n)/", "pDdYXAQerT", $string);
		$string = preg_replace("/(\*\/)(\\\\n)/", "ODdPKAQerT", $string);
		$string = preg_replace("/(\/\*)(\\\\r)/", "pDdYXBQerT", $string);
		$string = preg_replace("/(\*\/)(\\\\r)/", "ODdPKBQerT", $string);
		$string = preg_replace("/(\/\s\/)([g][\W])/", "ZUQQ$2", $string);
		$string = preg_replace("/\\\\n/", "AQerT", $string);
		$string = preg_replace("/\\\\r/", "BQerT", $string);
		$string = preg_replace("/([^\*])(\*|[\r\n]|\'|\"|\,|\+|\{|;|\(|\)|\[|\]|\{|\}|\?|[^p|s]:|\&|\%|[^\\\\][a-m-o-u-s-zA-Z]|\||-|=|[0-9])(\s*)(?!([^=\\\\\&\/\"\'\^\*:]))(\/)(\/)+(?!([\r\n\*\+\"]*?([^\r\n]*?\*\/|[^\r\n]*?\"\s*\+|([^\r\n]*?=\";))))([^\n\r]*)([^;\"\'\{\(\}\,]\s*[\\\\\[])(?=([\r\n]+))/", "$1$2$3", $string);
		$string = preg_replace("/((([\r\n]\s*)(\/\*[^\r\n]*?\*\/(?!([^\n\r]*?\"\s*\+)))([^\n\r]*?\/\*[^\n\r]*?\*\/(?!([^\n\r]*?\"\s*\+))[^\n\r]*?\/\*[^\n\r]*?\*\/(?!([^\n\r]*?\"\s*\+)))+)+(?!([\*]))(?=([^\n\r\/]*?\/\/\/)))/", "$3", $string);
		$string = preg_replace("/([\r\n]+?\s*)((\/)(\/)+)(?!([^\r\n]*?)([\\\\]|\*\/|[=]+\s*\";|[=]+\s*\';)).*/", "$1\n", $string);
		$string = preg_replace("/(\'\s*)(\/\/\*)([^\r\n\*]*?(?!(\*\/))(\'))/", "$1TDdXX$3", $string);
		$string = preg_replace("/(\"\s*)(\/\/\*)([^\r\n\*]*?(?!(\*\/))(\"))/", "$1TDdXX$3", $string);
		$string = preg_replace("/(\'\s*)(\/\*)([^\r\n\*]*?(?!(\*\/))(\'))/", "$1pDdYX$3", $string);
		$string = preg_replace("/(\"\s*)(\/\*)([^\r\n\*]*?(?!(\*\/))(\"))/", "$1pDdYX$3", $string);
		$string = preg_replace("/(\,\s*)(\*\/\*)(\s*[\}\"\'\;\)])/", "$1RDdPK$3", $string); // , */* '
		$string = preg_replace('/(\n|\r|\+|\&|\=|\|\||\(|[^\)]\:[^\=\,\/\$\\\\\<]|\(|return(?!(\/[a-zA-Z]+))|\!|\,)(?!(\s*\/\/|\n))(\s*\/)([^\]\)\}\*\;\,gi\.]\s*)([^\/\n]*?)(\*\/)/', '$1$4$5$6ODdPK', $string);
		$string = preg_replace("/[\/][\/]+(AQerTBQerT)(\s*[\"]\s*[\+])/", "WQerT", $string);
		$string = preg_replace("/[\/][\/]+(\*\/AQerTBQerT)(\s*[\"]\s*[\+])/", "YQerT", $string);
		$string = preg_replace("/([\r\n]\s*\/\/)[^\r\n]*?\/\*(?=(\/))[^\r\n]*?([\r\n])/", "$1 */$3", $string);
		$string = preg_replace("/([\)]|[^\/|\\\\|\"])(\/\*)(?=([^\r\n]*?[\\\\][rn]([\\\\][nr])?\s*\"\s*\+\s*(\n|\r)?\s*\"))/", "$1pDdYX", $string);
		$string = preg_replace('/([\"]\s*[\,\+][\r\n]\s*[\"])(\s*\/\/)((\/\/)|(\/))*/', '$1qDdXX', $string);
		$string = preg_replace('/([\"]\s*[\,\+][\r\n]\s*[\"](qDdXX))[\\\\]*(\s*\/\/)*((\/\/)|(\/))*/', '$1', $string);
		$string = preg_replace("/([\r\n]\s*)(?=([^\r\n\*\,\:\;a-zA-Z\"]*?))(\/)+(\/)[^\r\n\/][^\r\n\*\,]*?[\*]+(?!([^\r\n]*?(([^\r\n]*?\/|\"\s*\)\s*\;|\"\s*\;|\"\s*\,|\'\s*\)\s*\;|\'\s*\;|\'\s*\,))))[^\r\n]*(?!([\/\r\n]))[^\r\n]*/", "$1", $string);
		$string = preg_replace("/([\r\n](\/)*[^:\;\,\.\+])(\/\/[^\r\n]*?)(\*)?([^\r\n]+?)(\*)+([^\r\n\*\/])+?(\/[^\*])(?!([^\r\n]*?((\"\s*\)\s*\;|\"\s*\;|\"\s*\,|\'\s*\)\s*\;|\'\s*\;|\'\s*\,))))/", "$1$3$7$8", $string);
		do
		{
			$string = preg_replace("/([\r\n])((\/)*[^:\;\,\.\+])(\/\/[^\r\n]*?)(\*)?([^\r\n]+?)(\/|\*)([^\r\n]*?)(\*)[\r\n]/", "$1", $string, 1, $count);
		} while ($count);
		$string = preg_replace("/(((([\r\n](?=([^:;,\.\+])))(\/)+(\/))(\*))([^\r\n]*?)(\/\*)*([^\r\n])*?(\*\/)(?!([^\r\n]*?((\"\s*\)\s*\;|\"\s*\;|\"\s*\,|\'\s*\)\s*\;|\'\s*\;|\'\s*\,))))(((?=([^:\;\,\.\+])))(\/)*([^\r\n]*?)(\*|\/)?([^\r\n]*?)(\/\*)([^\r\n])*?(\*\/)(?!([^\r\n]*?((\"\s*\)\s*\;|\"\s*\;|\"\s*\,|\'\s*\)\s*\;|\'\s*\;|\'\s*\,)))))*)+[^\r\n]*/", "$2$7$9$10$11$12", $string);
		$string = preg_replace("/(\/\*[\r\n]\s*)(?!([^\/<>;:%~`#@&-_=,\.\$\^\{\[\(\|\)\*\+\?\'\"\a-zA-Z0-9]))(((\/\*)[^\r\n]*?(\*\/)?[^\r\n]*?(\/\*)[^\r\n]*?(\*\/))*((\/\*)[^\r\n]*?(\*\/)))+(?!([^\r\n]*?(\*\/|\/\*)))[^\r\n]*?[\r\n]/", "\n", $string);
		$string = preg_replace("/(?!([\r\n]))([^a-zA-Z0-9]\+|\?|&|\=|\|\||\!|\(|,|return(?!(\/[a-zA-Z]+))|[^\)]\:)(?!(\s*\/\/|\n|\/\*[^\r\n\*]*?\*\/))(\s*\/([\*\^]?))(?!([\r\n\*\/]|[\*]))(?!(\<\!\-\-))(([^\^\]\)\}\*;,g&\.\"\']?\s*)(?=([\]\)\}\*;,g&\.\/\"\']))?)(([^\r\n]*?)(([\w\W])([\*]?\/\s*)(\})|([^\\\\])([\*]?\/\s*)(\))|([\w\W])([\*]?\/\s*)([i][g]?[\W])|([\w\W])([\*]?\/\s*)([g][i]?[\W])|([\w\W])([\*]?\/\s*)(?=(\,))|([^\\\\]|[\/])([\*]?\/\s*)(;)|([\w\W])([\*]?\/\:\s)(?!([@\]\[\)\(\}\{\.,#%\+-\=`~\*&\^;\:\'\"]))|([^\\\\])([\*]?\/\s*)(\.[^\/])|([^\\\\])([\*]?\/\s*)([\r\n]\s*[;\.,\)\}\]]\s*[^\/]|[\r\n]\s*([i][g]?[\W])|[\r\n]\s*([g][i]?[\W])))|([^\\\\])([\*]?\/\s*)([;\.,\)\}\]]\s*[^\/]|([i][g]?[\W])|([g][i]?[\W])))/", "$2$3$5AwTc$7$8$10$13$15$18$21$24$27$30$33$36$39$44CwRc$16$17$19$20$22$23$25$26$28$31$32$34$35$37$38$40$41$45$46", $string);
		$string = preg_replace("/([^;\"\'\{\(\}\,\/]\s*[^\/][\\\\\[]\s?)\s*([\r\n]+)/", "$1", $string);
		$string = preg_replace("/([\|\[])\s*([\|\]])/", "$1$2", $string);
		do
		{
			$string = preg_replace('/(AwTc)([^\r\nC]*?)(\/\*)(?=([^\r\n]*?CwRc))/', '$1$2pDdYX', $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace('/(AwTc)([^\r\nC]*?)(\*\/)(?=([^\r\n]*?CwRc))/', '$1$2ODdPK', $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace('/(AwTc)([^\r\nC]*?)(\/\/)(?=([^\r\n]*?CwRc))/', '$1$2qDdXX', $string, 1, $count);
		} while ($count);
		$string = preg_replace("/([^\(\/\"\']\s*)(?!\(\s*function)((\()(?=([^\n\r\)]*?[\'\"]))(?!([^\r\n]*?\"\s*\<[^\r\n]*?\>\s*\"|[^\r\n]*?\"\s*\\\\\s*\"|[^\r\n]*?\"\s*\[[^\r\n]*?\]\s*\"))((?>[^()]+)|(?2))*?\))(?!(\s*\"\s*\;|\s*\'\s*\;|\s*\/|\s*\)|\s*\"|[^\n\r]*?\"\s*\+\s*(\n|\r)?\s*\"))/", "$1 /*Yu*/ $2 /*Zu*/ ", $string);
		do
		{
			$string = preg_replace('/(\/\*Yu\*\/)([^\r\n]*?)(\/)(\/)(?=([^\r\n]*?\/\*Zu\*\/))/', '$1$2qDdXX', $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace("/(\/\*Yu\*\/)([^\n\r\'\"]*?[\"\'])([^\n\r\)]*?)(\/\*)([^\n\r\'\"\)]*?[\"\'])([^\n\r]*?\/\*Zu\*\/)/", "$1$2$3pDdYX$5$6", $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace("/(\/\*Yu\*\/)([^\n\r\'\"]*?[\"\'])([^\n\r\)]*?)(\*\/)([^\n\r\'\"\)]*?[\"\'])([^\n\r]*?\/\*Zu\*\/)/", "$1$2$3ODdPK$5$6", $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace("/(=|\+|\(|[a-z]|\,)(\s*)(\")([^\r\n\;\/\'\)\,\]\}\*]*?)(\/)(\/)([^\r\n\;\"\*]*?)(\")/", "$1$2$3$4qDdXX$7$8", $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace("/(=|\+|\(|[a-z]|\,)(\s*)(\')([^\r\n\;\/\'\)\,\]\}\*]*?)(\/)(\/)([^\r\n\*\;\']*?)(\')/", "$1$2$3$4qDdXX$7$8", $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace("/(\"[^\r\n\;]*?)(\/)(\/)([^\r\n\"\;]*?([\"]\s*(\;|\)|\,)))/", "$1qDdXX$4", $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace("/(\'[^\r\n\;]*?)(\/)(\/)([^\r\n\'\;]*?([\']\s*(\;|\)|\,)))/", "$1qDdXX$4", $string, 1, $count);
		} while ($count);
		$string = preg_replace("/([\n\r])([^\n\r\*\,\"\']*?)(?=([^\*\,\:\;a-zA-Z\"]*?))(\/)(\/)+(?=([^\n\r]*?\*\/))([^\n\r]*?(\*\/)).*/", "$1$4$5 $8", $string);
		do
		{
			$string = preg_replace("/([\r\n]\s*)((\/\*(?!(\*\/)))([^\r\n]+?)(\*\/))(?!([^\n\r\/]*?(\/)(\/)+\*))/", "$1$3$6", $string, 1, $count);
		} while ($count);
		$string = preg_replace("/([\n\r]\/)(\/)+([^\n\r]*?)(\*\/)([^\n\r]*?(\*\/))(?!([^\n\r]*?(\*\/)|[^\n\r]*?(\/\*))).*/", "$1/ $4", $string);
		do
		{
			$string = preg_replace("/([\n\r]\s*\/\*\*\/)([^\n\r=]*?\/\*[^\n\r]*?\*\/)(?=([\n\r]|\/\/))/", "$1", $string, 1, $count);
		} while ($count);
		$string = preg_replace("/([\n\r]\s*\/\*\*\/)([^\n\r=]*?)(\/\/.*)/", "$1$2", $string);
		do
		{
			$string = preg_replace("/(\=\s*)(?=([^\r\n\'\"]*?\'[^\n\r\']*?\'))([^\n\r;]*?[;]\s*)(\/\/[^\r\n][^\r\n]*)[\n\r]/", "$1$3", $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace("/(\=)(\s*\')([^\r\n\'\"]*?)(\/)(\/)([^\r\n]*?[\'])/", "$1$2$3qDdXX$6", $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace("/(\"[^\r\n\;\,\"]*?)(\/)(\*)(?!([YZ]u\*\/))([^\r\n;\,\"]*?)(\")/", "$1pDdYX$5$6", $string, 1, $count);
		} while ($count);   // open
		do
		{
			$string = preg_replace("/([^\"]\"[^\r\n\;\/\,\"]*?)(\s*)(\*)(\/)([^\r\n;\,\"=]*?)(\")/", "$1$2ODdPK$5$6", $string, 1, $count);
		} while ($count); // close
		do
		{
			$string = preg_replace("/(\'[^\r\n\;\,\']*?)(\/)(\*)(?!([YZ]u\*\/))([^\r\n;\,\']*?)(\')/", "$1pDdYX$5$6", $string, 1, $count);
		} while ($count);   // open
		do
		{
			$string = preg_replace("/(\'[^\r\n\;\/\,\']*?)(\s*)(\*)(\/)([^\r\n;\,\']*?)(\')/", "$1$2ODdPK$5$6", $string, 1, $count);
		} while ($count); // close
		do
		{
			$string = preg_replace("/(\'[^\r\n\;\,\']*?)(\*)(\/)([^\r\n;\,\']*?)(\')(?!([^\n\r\+]*?[\']))/", "$1ODdPK$4$5", $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace("/(\"[^\r\n\;\,\"]*?)(\*)(\/)([^\r\n;\,\"]*?)(\")(?!([^\n\r\+]*?[\"]))/", "$1ODdPK$4$5", $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace("/(=\s*\"[^\n\r\"]*?)(\/\/)(?=([^\n\r]*?\"\s*;))/", "$1qDdXX", $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace("/(=\s*\"[^\n\r\"]*?)(\/\*)(?!([YZ]u\*\/))(?=([^\n\r]*?\"\s*;))/", "$1pDdYX", $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace("/(=\s*\"[^\n\r\"]*?)(\*\/)(?=([^\n\r]*?\"\s*;))/", "$1ODdPK", $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace("/(=\s*\'[^\n\r\']*?)(\/\/)(?=([^\n\r]*?\'\s*;))/", "$1qDdXX", $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace("/(=\s*\'[^\n\r\']*?)(\/\*)(?!([YZ]u\*\/))(?=([^\n\r]*?\'\s*;))/", "$1pDdYX", $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace("/(=\s*\'[^\n\r\']*?)(\*\/)(?=([^\n\r]*?\'\s*;))/", "$1ODdPK", $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace("/(\=|\()(\s*\")([^\r\n\'\"]*?[\'][^\r\n\'\"]*?)(\/)(\/)([^\r\n\'\"]*?[\'])(\s*\'[^\r\n\'\"]*?)(\/\/|qDdXX)?([^\r\n\'\"]*?[\'][^\r\n\'\"]*?[\"])(?!(\'\)|\s*[\)]?\s*\+|\'))/", "$1$2$3qDdXX$6$7qDdXX$9$10", $string, 1, $count);
		} while ($count);
		do
		{
			$string = preg_replace("/(\=|\()(\s*\')([^\r\n\'\"]*?[\"][^\r\n\'\"]*?)(\/)(\/)([^\r\n\'\"]*?[\"])(\s*\"[^\r\n\'\"]*?)(\/\/|qDdXX)?([^\r\n\'\"]*?[\"][^\r\n\'\"]*?[\'])(?!(\'\)|\s*[\)]?\s*\+|\'))/", "$1$2$3qDdXX$6$7qDdXX$9$10", $string, 1, $count);
		} while ($count);
		$string = preg_replace("/([^\*])(\*|[\r\n]|[^\\\\]\'|[^\\\\]\"|\,|\+|\{|;|\(|\)|\[|\]|\{|\}|\?|[^p|s]:|\&|\%|[^\\\\][a-m-o-u-s-zA-Z]|\||-|=|[0-9])(\s*)(?!([^=\\\\\&\/\"\'\^\*:]))(\/)(\/)+(?!([\r\n\*\+\"]*?([^\r\n]*?\*\/|[^\r\n]*?\"\s*\+|([^\r\n]*?=\";)))).*/", "$1$2$3", $string);
		$string = preg_replace("/(\/\/\*\/)(?!([\r\n\*\+\"]*?([^\r\n]*?\*\/|[^\r\n]*?\"\s*\+|([^\r\n]*?=\";)))).*/", "", $string);
		$string = preg_replace("/(?!([^\n\r]*?[\'\"]))(\s*<!--.*-->)(?!(<\/div>))[^\n\r]*?.*/", "$2$4", $string);
		$string = preg_replace("/([\n\r][^\n\r\*\,\"\']*?)(?=([^\*\,\:\;a-zA-Z\"]*?))(\/)(\/)+(?!([\r\n\*\+\"]*?([^\r\n]*?\*\/|[^\r\n]*?\"\s*\+|([^\r\n]*?=\";)))).*/", "$1", $string);
		$string = preg_replace("/(?!([^\n\r]*?[\'\"]))(\s*<!--.*-->)(?!(<\/div>))[^\n\r]*?(\*\/)?.*/", "", $string);
		$string = preg_replace("/qDdXX/", "//", $string);
		$string = preg_replace("/pDdYX/", "/*", $string);
		$string = preg_replace("/ODdPK/", "*/", $string);
		$string = preg_replace("/RDdPK/", "*/*", $string);
		$string = preg_replace("/TDdXX/", "//*", $string);
		$string = preg_replace('/WQerT/', '\\\\r\\\\n" +', $string);
		$string = preg_replace('/YQerT/', '//*/\\\\r\\\\n" +', $string);
		$string = preg_replace('/AQerT/', '\\\\n', $string);
		$string = preg_replace('/BQerT/', '\\\\r', $string);
		$string = preg_replace("/ZUQQ/", "/ /", $string);
		$string = preg_replace('/\s\/\*Zu\*\/\s/', '', $string);
		$string = preg_replace('/\s\/\*Yu\*\/\s/', '', $string);
		$string = preg_replace('/(AwTc)/', '', $string);
		$string = preg_replace('/(CwRc)/', '', $string);
		$string = preg_replace("/([a-zA-Z0-9]\s?)\s*[\n\r]+(\s*[\)\,&]\s?)(\s*[\r\n]+\s*[\{])/", "$1$2$3", $string);
		$string = preg_replace("/([a-zA-Z0-9\(]\s?)\s*[\n\r]+(\s*[;\)\,&\+\-a-zA-Z0-9]\s?)(\s*[\{;a-zA-Z0-9\,&\n\r])/", "$1$2$3", $string);
		$string = preg_replace("/(\(\s?)\s*[\n\r]+(\s*function)/", "$1$2", $string);
		$string = preg_replace("/(=\s*\[[a-zA-Z0-9]\s?)\s*([\r\n]+)/", "$1", $string);
		$string = preg_replace("/([^\*\/\'\"]\s*)(\/\/\s*\*\/)/", "$1", $string);
		$string = preg_replace("/(\/\*\*\/)(\/\/(?!([^\n\r]*?\*\/)).*)/", "$1", $string);
		$string = preg_replace("/(\;\/\*\*\/)(?!([^\n\r]*?\*\/)).*/", "", $string);
		$string = preg_replace("/(\/\/\\\\\*[^\n\r\"\'\/]*?[\n\r])/", "\r\n", $string);
		$string = preg_replace("/([\r\n]\s*)(\/\*[^\r\n]*?\*\/(?!([^\r\n]*?\"\s*\+)))/", "$1", $string);
		$string = preg_replace("/\/\*\*\/\s/", " ", $string);
		$string = preg_replace("/(\=\s*)(?=([^\r\n\'\"]*?\'[^\n\r\'\"]*?\'))([^\n\r\/]*?)(\/\/[^\r\n\"\'][^\r\n]*[\'\"])(\/\*\*\/)[\n\r]/", "$1$3$4\n", $string);
		$string = preg_replace("/(\=\s*)(?=([^\r\n\'\"]*?\"[^\n\r\'\"]*?\"))([^\n\r\/]*?)(\/\/[^\r\n\"\'][^\r\n]*[\'\"])(\/\*\*\/)[\n\r]/", "$1$3$4\n", $string);
		$string = preg_replace("/([^\'\"ps\s]\s*)(\:[^\r\n\'\"\[\]]*?\'[^\n\r\'\"]*?\')([^\n\r\/a-zA-Z0-9]*?)(\/\/)[^\r\n\/\'][^\r\n]*/", "$1$2", $string);
		$string = preg_replace("/([^\'\"ps\s]\s*)(\:[^\r\n\'\"\[\]]*?\"[^\n\r\'\"]*?\")([^\n\r\/a-zA-Z0-9]*?)(\/\/)[^\r\n\/\"][^\r\n]*/", "$1$2", $string);
		$string = preg_replace("/(\"[^\n\r\'\"\+]*?\")([^\n\r\/a-zA-Z0-9]*?)(\/\/)(?!(\*|[^\r\n]*?[\\\\n\\\\r]+\s*\"\s*\+|[^\r\n]*?[\\\\n\\\\r]+\s*\'\s*\+))[^\r\n\/\"][^\r\n]*/", "$1$2", $string);
		$string = preg_replace("/(;\s*)\/\/(?!([^\n\r]*?\"\s*;)).*/", "$1 \n", $string);
		$string = preg_replace('/([\n\r][^\n\r\"]*?)([^\/\"\'\*\>])\/\*(?!(\*\/))[^\n\r\"]*?[^@]\*\//', "$1$2", $string);
		$string = preg_replace("/(\|\||[\?]|\,)(\s*)\/\/(?!([^\n\r]*?\*\/|\"|\')).*/", "$1$2", $string);
		$string = preg_replace("/([\|\[\;\,\:\=\-\{\}\]\[\?\)\(])\s*[\n\r]\s*[\n\r](\s*[\n\r])+/", "$1\n", $string);
		$string = preg_replace('/(--\s+\>)/', 'HwRc', $string);
		$string = preg_replace('/\s+/', ' ', $string);
		$string = preg_replace('/\s*(?:(?=[=\-\+\|%&\*\)\[\]\{\};:\,\.\<\>\!\@\#\^`~]))/', '', $string);
		$string = preg_replace('/(?:(?<=[=\-\+\|%&\*\)\[\]\{\};:\,\.\<\>\?\!\@\#\^`~]))\s*/', '', $string);
		$string = preg_replace('/([^a-zA-Z0-9\s\-=+\|!@#$%^&*()`~\[\]{};:\'",<.>\/?])\s+([^a-zA-Z0-9\s\-=+\|!@#$%^&*()`~\[\]{};:\'",<.>\/?])/', '$1$2', $string);
		$string = preg_replace('/(HwRc)/', '-- >', $string);

		return $string;
	}
}
