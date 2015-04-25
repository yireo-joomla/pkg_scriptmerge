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
		$string = preg_replace("/(\/\*\*\/)(\/\/(?!([^\n\r]*?\*\/)).*)/", "$1", $string);

		do
		{
			$string = preg_replace("/(http(s)?\:)([^\r\n]*?)(\/\/)/", "$1$3qDdXX", $string, 1, $count);
		} while ($count);

		// Remove all extra new lines after [ and \
		$string = preg_replace("/(\*|[\r\n]|\'|\"|\,|\+|\{|;|\(|\)|\[|\]|\{|\}|\?|[^p|s]:|\&|\%|([^\\\\])[a-m-o-u-s-zA-Z]|\||-|=|[0-9])(\s*)(?!([^=\\\\\&\/\"\'\^\*:]))(\/)(\/)+(?!([\r\n\*\+\"]*?([^\r\n]*?\*\/|[^\r\n]*?\"\s*\+|([^\r\n]*?=\";))))([^\n\r]*)([^;\"\'\{\(\}\,]\s*[\\\\\[])(?=([\r\n]+))/", "$1$2$3", $string);

		// Slash star followed by all except */ and star slash */ remove add start document!
		$string = preg_replace("/(^^\/\*)[\s\S]*?(\*\/)/", "\n \n", $string);

		// A /* followed by (not new line but) ... */ ... /* ... till */
		$string = preg_replace("/((([\r\n]\s*)(\/\*[^\r\n]*?\*\/(?!([^\n\r]*?\"\s*\+)))([^\n\r]*?\/\*[^\n\r]*?\*\/(?!([^\n\r]*?\"\s*\+))[^\n\r]*?\/\*[^\n\r]*?\*\/(?!([^\n\r]*?\"\s*\+)))+)+(?!([\*]))(?=([^\n\r\/]*?\/\/\/)))/", "$3", $string);

		// Slash slash remove start document! folowed by all exept new line!
		$string = preg_replace("/(^^\/)+(\/)[^\r\n]*?[\r\n]/", "\n ", $string);

		// (slash slash) remove everything behinde it not if its followed by */ and /n/r or " + and /n/r
		$string = preg_replace("/([\r\n]+?\s*)((\/)(\/)+)(?!([^\r\n]*?)([\\\\]|\*\/|[=]+\s*\";|[=]+\s*\';)).*/", "$1", $string);

		// slash slash star between collons protect like: ' //* ' by TDdXX
		$string = preg_replace("/(\'\s*)(\/\/\*)([^\r\n\*]*?(?!(\*\/))(\'))/", "$1TDdXX$3", $string);

		// slash slash star between collons protect like: ' //* ' by TDdXX
		$string = preg_replace("/(\"\s*)(\/\/\*)([^\r\n\*]*?(?!(\*\/))(\"))/", "$1TDdXX$3", $string);

		// in regex star slash protect by: ODdPK
		$string = preg_replace("/(\,\s*)(\*\/\*)(\s*[\}\"\'\;\)])/", "$1RDdPK$3", $string); // , */* '
		$string = preg_replace('/(\n|\r|\+|\&|\=|\|\||\(|[^\)]\:[^\=\,\/\$\\\\\<]|\(|return(?!(\/[a-zA-Z]+))|\!|\,)(?!(\s*\/\/|\n))(\s*\/)([^\]\)\}\*\;\)\,gi\.]\s*)([^\/\n]*?)(\*\/)/', '$1$4$5$6ODdPK', $string);

		//// (slash r) (slash n) protect if followed by " + and new line
		$string = preg_replace("/[\/][\/]+([\\\\r]+[\\\\n]+[\"]\s*[\+])/", "*/WQerT", $string);

		// Html Text protection!
		$string = preg_replace("/([\r\n]\s*\/\/)[^\r\n]*?\/\*(?=(\/))[^\r\n]*?([\r\n])/", "$1 */$3", $string);
		$string = preg_replace("/([\)]|[^\/|\\\\|\"])(\/\*)(?=([^\r\n]*?[\\\\][rn]([\\\\][nr])?\s*\"\s*\+\s*(\n|\r)?\s*\"))/", "$1pDdYX", $string);
		$string = preg_replace('/([\"]\s*[\,\+][\r\n]\s*[\"])(\s*\/\/)((\/\/)|(\/))*/', '$1qDdXX', $string);
		$string = preg_replace('/([\"]\s*[\,\+][\r\n]\s*[\"](qDdXX))[\\\\]*(\s*\/\/)*((\/\/)|(\/))*/', '$1', $string);

		// started by new line slash slash remove all not followed by */ and new line!
		$string = preg_replace("/([\r\n]\s*)(?=([^\r\n\*\,\:\;a-zA-Z\"]*?))(\/)+(\/)[^\r\n\/][^\r\n\*\,]*?[\*]+(?!([^\r\n]*?(([^\r\n]*?\/|\"\s*\)\s*\;|\"\s*\;|\"\s*\,|\'\s*\)\s*\;|\'\s*\;|\'\s*\,))))[^\r\n]*(?!([\/\r\n]))[^\r\n]*/", "$1", $string);

		// removes all *.../ achter // leaves the ( // /* staan en */ ) 1 off 2
		$string = preg_replace("/([\r\n](\/)*[^:\;\,\.\+])(\/\/[^\r\n]*?)(\*)?([^\r\n]+?)(\*)+([^\r\n\*\/])+?(\/[^\*])(?!([^\r\n]*?((\"\s*\)\s*\;|\"\s*\;|\"\s*\,|\'\s*\)\s*\;|\'\s*\;|\'\s*\,))))/", "$1$3$7$8", $string);

		// removes all /* after // leaves the ( // */ staan ) 2 off 2
		do
		{
			$string = preg_replace("/([\r\n])((\/)*[^:\;\,\.\+])(\/\/[^\r\n]*?)(\*)?([^\r\n]+?)(\/|\*)([^\r\n]*?)(\*)[\r\n]/", "$1", $string, 1, $count);
		} while ($count);

		// removes all (/* and */) combinations after // and everything behinde it! but leaves  ///* */ or example. ///*//*/ one times.
		$string = preg_replace("/(((([\r\n](?=([^:;,\.\+])))(\/)+(\/))(\*))([^\r\n]*?)(\/\*)*([^\r\n])*?(\*\/)(?!([^\r\n]*?((\"\s*\)\s*\;|\"\s*\;|\"\s*\,|\'\s*\)\s*\;|\'\s*\;|\'\s*\,))))(((?=
    ([^:\;\,\.\+])))(\/)*([^\r\n]*?)(\*|\/)?([^\r\n]*?)(\/\*)([^\r\n])*?(\*\/)(?!([^\r\n]*?((\"\s*\)\s*\;|\"\s*\;|\"\s*\,|\'\s*\)\s*\;|\'\s*\;|\'\s*\,)))))*)+[^\r\n]*/", "$2$7$9$10$11$12", $string);

		// removes /* ... followed by */ repeat even pairs till new line!
		$string = preg_replace("/(\/\*[\r\n]\s*)(?!([^\/<>;:%~`#@&-_=,\.\$\^\{\[\(\|\)\*\+\?\'\"\a-zA-Z0-9]))(((\/\*)[^\r\n]*?(\*\/)?[^\r\n]*?(\/\*)[^\r\n]*?(\*\/))*((\/\*)[^\r\n]*?(\*\/)))+(?!([^\r\n]*?(\*\/|\/\*)))[^\r\n]*?[\r\n]/", "\n ", $string);

		// (Mark) Regex Find all "  Mark with = AwTc  and  CwRc // special cahacers are:  . \ + * ? ^ $ [ ] ( ) { } < > = ! | : " '
		$string = preg_replace("/(?!([\r\n]))(\+|\?|&|\=|\|\||\(|\!|,|return(?!(\/[a-zA-Z]+))|[^\)]\:)(?!(\s*\/\/|\n|\/\*[^\r\n\*]*?\*\/))(\s*\/([\*\^]?))(?!([\r\n\*\/]))(?!(\<\!\-\-))(([^\]\)\}\*;,g&\.\"\']?\s*)(?=([\]\)\}\*;,g&\.\/\"\']))?)(([^\r\n]*?)(([\w\W])([\*]?\/\s*)(\})|([^\\\\])([\*]?\/\s*)(\))|([\w\W])([\*]?\/\s*)([i][g]?[\W])|([\w\W])([\*]?\/\s*)([g][i]?[\W])|([\w\W])([\*]?\/\s*)(\,)|([^\\\\]|[\/])([\*]?\/\s*)(;)|([\w\W])([\*]?\/\:\s)(?!([@\]\[\)\(\}\{\.,#%\+-\=`~\*&\^;\:\'\"]))|([^\\\\])([\*]?\/\s*)(\.[^\/])|([^\\\\])([\*]?\/\s*)([\r\n]\s*[;\.,\)}]\s*[^\/]|[\r\n]\s*([i][g]?[\W])|[\r\n]\s*([g][i]?[\W])))|([^\\\\])([\*]?\/\s*)([;\.,\)}]\s*[^\/]|([i][g]?[\W])|([g][i]?[\W])))/", "$2$3$5AwTc$7$8$10$13$15$18$21$24$27$30$33$36$39$44CwRc$16$17$19$20$22$23$25$26$28$29$31$32$34$35$37$38$40$41$45$46", $string);

		// Remove all extra new lines after [ and \
		$string = preg_replace("/([^;\"\'\{\(\}\,]\s*[\\\\\[]\s?)\s*([\r\n]+)/", "$1", $string);

		// (Mark) Regex Find all "  Mark With :  YuKt  and   ZuKd
		$string = preg_replace("/((join|split|match|replace|RegExp|return|regex)\s*)(\(\s*(\")?)(([^\r\n]*?)((\")?\s*\))(?!(\"\)|\[|\")|\())/", "$1$3YuKt$6ZuKd$7", $string);

		// (star slash) or (slash star) 1 sentence! Protect! With pDdYX and ODdPK
		do
		{
			$string = preg_replace('/((\")?YuKt)([^\r\n]*?)(\/)(\*)(?=([^\r\n]*?ZuKd))/', '$1$3pDdYX', $string, 1, $count);
		} while ($count);

		do
		{
			$string = preg_replace('/((\")?YuKt)([^\r\n]*?)(\*)(\/)(?=([^\r\n]*?ZuKd))/', '$1$3ODdPK', $string, 1, $count);
		} while ($count);

		// (slash slash) 1 sentence! Protect with: qDdXX
		do
		{
			$string = preg_replace('/((\")?YuKt)([^\r\n]*?)(\/)(\/)(?=([^\r\n]*?ZuKd))/', '$1$3qDdXX', $string, 1, $count);
		} while ($count);

		// (slash slash) 2 sentences! Protect ' and "
		do
		{
			$string = preg_replace("/(=|\+|\(|[a-z]|\,)(\s*)(\")([^\r\n\;\/\'\)\,\]\}\*]*?)(\/)(\/)([^\r\n\;\"\*]*?)(\")/", "$1$2$3$4qDdXX$7$8", $string, 1, $count);
		} while ($count);

		do
		{
			$string = preg_replace("/(=|\+|\(|[a-z]|\,)(\s*)(\')([^\r\n\;\/\'\)\,\]\}\*]*?)(\/)(\/)([^\r\n\*\;\']*?)(\')/", "$1$2$3$4qDdXX$7$8", $string, 1, $count);
		} while ($count);

		// (slash slash) 2 sentences! Protect ' and "
		do
		{
			$string = preg_replace("/(\"[^\r\n\;]*?)(\/)(\/)([^\r\n\"\;]*?([\"]\s*(\;|\)|\,)))/", "$1qDdXX$4", $string, 1, $count);
		} while ($count);

		do
		{
			$string = preg_replace("/(\'[^\r\n\;]*?)(\/)(\/)([^\r\n\'\;]*?([\']\s*(\;|\)|\,)))/", "$1qDdXX$4", $string, 1, $count);
		} while ($count);

		// Remove all slar slash achter \n
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

		// Remove all slash slash achter = '...'; //......
		do
		{
			$string = preg_replace("/(\=\s*)(?=([^\r\n\'\"]*?\'[^\n\r\']*?\'))([^\n\r;]*?[;]\s*)(\/\/[^\r\n][^\r\n]*)[\n\r]/", "$1$3", $string, 1, $count);
		} while ($count);

		// protect slash slash '...abc//...abc'!
		do
		{
			$string = preg_replace("/(\=)(\s*\')([^\r\n\'\"]*?)(\/)(\/)([^\r\n]*?[\'])/", "$1$2$3qDdXX$6", $string, 1, $count);
		} while ($count);

		//(slash star) or (star slash) : no dubble senteces here! Protect with: pDdYX and ODdPK
		do
		{
			$string = preg_replace("/(\"[^\r\n\;\,\"]*?)(\/)(\*)([^\r\n;\,\"]*?)(\")/", "$1pDdYX$4$5", $string, 1, $count);
		} while ($count); // open

		do
		{
			$string = preg_replace("/([^\"]\"[^\r\n\;\/\,\"]*?)(\s*)(\*)(\/)([^\r\n;\,\"=]*?)(\")/", "$1$2ODdPK$5$6", $string, 1, $count);
		} while ($count); // close

		do
		{
			$string = preg_replace("/(\'[^\r\n\;\,\']*?)(\/)(\*)([^\r\n;\,\']*?)(\')/", "$1pDdYX$4$5", $string, 1, $count);
		} while ($count); // open

		do
		{
			$string = preg_replace("/(\'[^\r\n\;\/\,\']*?)(\s*)(\*)(\/)([^\r\n;\,\']*?)(\')/", "$1$2ODdPK$5$6", $string, 1, $count);
		} while ($count); // close

		// protect star slash '...abc*/...abc'!
		do
		{
			$string = preg_replace("/(\'[^\r\n\;\,\']*?)(\*)(\/)([^\r\n;\,\']*?)(\')(?!([^\n\r\+]*?[\']))/", "$1ODdPK$4$5", $string, 1, $count);
		} while ($count);

		// protect star slash '...abc*/...abc'!
		do
		{
			$string = preg_replace("/(\"[^\r\n\;\,\"]*?)(\*)(\/)([^\r\n;\,\"]*?)(\")(?!([^\n\r\+]*?[\"]))/", "$1ODdPK$4$5", $string, 1, $count);
		} while ($count);
		//---------------------------------------------------------------------------------------------------------
		//// \n protect
		$string = preg_replace("/\\\\n/", "VQerT", $string);

		do
		{
			$string = preg_replace("/(=\s*\"[^\n\r\"]*?)(\/\/)(?=([^\n\r]*?\"\s*;))/", "$1qDdXX", $string, 1, $count);
		} while ($count);

		do
		{
			$string = preg_replace("/(=\s*\"[^\n\r\"]*?)(\/\*)(?=([^\n\r]*?\"\s*;))/", "$1pDdYX", $string, 1, $count);
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
			$string = preg_replace("/(=\s*\'[^\n\r\']*?)(\/\*)(?=([^\n\r]*?\'\s*;))/", "$1pDdYX", $string, 1, $count);
		} while ($count);

		do
		{
			$string = preg_replace("/(=\s*\'[^\n\r\']*?)(\*\/)(?=([^\n\r]*?\'\s*;))/", "$1ODdPK", $string, 1, $count);
		} while ($count);

		// (Slash Slash) alle = " // " and = ' // ' replace by! qDdXX
		do
		{
			$string = preg_replace("/(\=|\()(\s*\")([^\r\n\'\"]*?[\'][^\r\n\'\"]*?)(\/)(\/)([^\r\n\'\"]*?[\'])(\s*\'[^\r\n\'\"]*?)(\/\/|qDdXX)?([^\r\n\'\"]*?[\'][^\r\n\'\"]*?[\"])(?!(\'\)|\s*[\)]?\s*\+|\'))/", "$1$2$3qDdXX$6$7qDdXX$9$10", $string, 1, $count);
		} while ($count);

		do
		{
			$string = preg_replace("/(\=|\()(\s*\')([^\r\n\'\"]*?[\"][^\r\n\'\"]*?)(\/)(\/)([^\r\n\'\"]*?[\"])(\s*\"[^\r\n\'\"]*?)(\/\/|qDdXX)?([^\r\n\'\"]*?[\"][^\r\n\'\"]*?[\'])(?!(\'\)|\s*[\)]?\s*\+|\'))/", "$1$2$3qDdXX$6$7qDdXX$9$10", $string, 1, $count);
		} while ($count);

		// (slash slash) Remove all also , or + not followed by */ and newline
		$string = preg_replace("/(\*|[\r\n]|[^\\\\]\'|[^\\\\]\"|\,|\+|\{|;|\(|\)|\[|\]|\{|\}|\?|[^p|s]:|\&|\%|([^\\\\])[a-m-o-u-s-zA-Z]|\||-|=|[0-9])(\s*)(?!([^=\\\\\&\/\"\'\^\*:]))(\/)(\/)+(?!([\r\n\*\+\"]*?([^\r\n]*?\*\/|[^\r\n]*?\"\s*\+|([^\r\n]*?=\";)))).*/", "$1", $string);

		// (slash slash star slash) Remove everhing behinde it not followed by */ or new line
		$string = preg_replace("/(\/\/\*\/)(?!([\r\n\*\+\"]*?([^\r\n]*?\*\/|[^\r\n]*?\"\s*\+|([^\r\n]*?=\";)))).*/", "", $string);

		// Remove almost all star comments except colon/**/
		$string = preg_replace("/(?!([^\n\r]*?[\'\"]))(\s*<!--.*-->)(?!(<\/div>))[^\n\r]*?(\*\/)?.*/", "$2$4", $string);
		$string = preg_replace("/\/\*/", "\n/*dddpp", $string);
		$string = preg_replace('/((\{\s*|\(\s*|:\s*)[\"\']\s*)(([^\{\};\"\']*)dddpp)/', '$1$4', $string);
		$string = preg_replace("/\*\//", "xxxpp*/\n", $string);
		$string = preg_replace('/([^\"\'](\(\s*|:\s*|\[\s*)[\"\']\s*)(([^\};\"\']*)xxxpp(?=([^\n\r]*?[\"\'])))/', '$1$4', $string);
		$string = preg_replace('/([\"\'])\s*\/\*/', '$1/*', $string);
		$string = preg_replace('/(\n)[^\'"]?\/\*dddpp.*?xxxpp\*\//s', '', $string);
		$string = preg_replace('/\n\/\*dddpp([^\s]*)/', '$1', $string);
		$string = preg_replace('/xxxpp\*\/\n([^\s]*)/', '*/$1', $string);
		$string = preg_replace('/xxxpp\*\/\n([\"])/', '$1', $string);
		$string = preg_replace('/(\*)\n*\s*(\/\*)\s*/', '$1$2$3', $string);
		$string = preg_replace('/(\*\/)\s*(\")/', '$1$2', $string);
		$string = preg_replace('/\/\*dddpp(\s*)/', '/*', $string);
		$string = preg_replace('/\n\s*\n/', "\n", $string);
		$string = preg_replace('/\s+(\*\/)\s*/', '$1', $string);
		$string = preg_replace("/([\n\r][^\n\r\*\,\"\']*?)(?=([^\*\,\:\;a-zA-Z\"]*?))(\/)(\/)+(?!([\r\n\*\+\"]*?([^\r\n]*?\*\/|[^\r\n]*?\"\s*\+|([^\r\n]*?=\";)))).*/", "$1", $string);
		$string = preg_replace("/(?!([^\n\r]*?[\'\"]))(\s*<!--.*-->)(?!(<\/div>))[^\n\r]*?(\*\/)?.*/", "", $string);

		// Restore all
		$string = preg_replace('/TOtX/', '"', $string); // Restore "
		$string = preg_replace("/TOtH/", "'", $string); // Restore '
		$string = preg_replace("/qDdXX/", "//", $string); // Restore //
		$string = preg_replace("/pDdYX/", "/*", $string); // Restore
		$string = preg_replace("/ODdPK/", "*/", $string); // Restore
		$string = preg_replace("/RDdPK/", "*/*", $string); // Restore
		$string = preg_replace("/TDdXX/", "//*", $string); // Restore */
		$string = preg_replace('/\*\/WQerT/', '\\\\r\\\\n" +', $string); // Restore \r\n" +
		$string = preg_replace('/VQerT/', '\\\\n', $string); // Restore \n"

		// Remove all markings!
		$string = preg_replace('/(AwTc)/', '', $string); // Start most Regex!
		$string = preg_replace('/(CwRc)/', '', $string); // End Most regex!
		$string = preg_replace('/(qDdu)/', '', $string); // // 
		$string = preg_replace('/ZXKd/', '', $string); // End Rexex (join|split|match|replace|RegExp|return|regex)
		$string = preg_replace('/(YuKt)/', '', $string); //   Start Regex (join|split|match|replace|RegExp|return|regex)
		$string = preg_replace('/(ZuKd)/', '', $string); //  End Rexex (join|split|match|replace|RegExp|return|regex)

		// all \s and [\n\r] repair like they where!
		$string = preg_replace("/([a-zA-Z0-9]\s?)\s*[\n\r]+(\s*[\)\,&]\s?)(\s*[\r\n]+\s*[\{])/", "$1$2$3", $string);
		$string = preg_replace("/([a-zA-Z0-9\(]\s?)\s*[\n\r]+(\s*[;\)\,&\+\-a-zA-Z0-9]\s?)(\s*[\{;a-zA-Z0-9\,&\n\r])/", "$1$2$3", $string);
		$string = preg_replace("/(\(\s?)\s*[\n\r]+(\s*function)/", "$1$2", $string);
		$string = preg_replace("/(=\s*\[[a-zA-Z0-9]\s?)\s*([\r\n]+)/", "$1", $string);

		$string = preg_replace("/([^\*\/\'\"]\s*)(\/\/\s*\*\/)/", "$1", $string);

		// Remove all /**/// .... Remove expept /**/ and followed by */ till newline!
		$string = preg_replace("/(\/\*\*\/)(\/\/(?!([^\n\r]*?\*\/)).*)/", "$1", $string);
		$string = preg_replace("/(\/\/\\\\\*[^\n\r\"\'\/]*?[\n\r])/", "\r\n", $string);
		$string = preg_replace("/([\r\n]\s*)(\/\*[^\r\n]*?\*\/(?!([^\r\n]*?\"\s*\+)))/", "$1", $string);

		//Remove colon /**/
		$string = preg_replace("/(\=\s*)(?=([^\r\n\'\"]*?\'[^\n\r\'\"]*?\'))([^\n\r\/]*?)(\/\/[^\r\n\"\'][^\r\n]*[\'\"])(\/\*\*\/)[\n\r]/", "$1$3$4\n", $string);
		$string = preg_replace("/(\=\s*)(?=([^\r\n\'\"]*?\"[^\n\r\'\"]*?\"))([^\n\r\/]*?)(\/\/[^\r\n\"\'][^\r\n]*[\'\"])(\/\*\*\/)[\n\r]/", "$1$3$4\n", $string);

		//Remove colon //
		$string = preg_replace("/([^\'\"ps\s]\s*)(\:[^\r\n\'\"\[\]]*?\'[^\n\r\'\"]*?\')([^\n\r\/a-zA-Z0-9]*?)(\/\/)[^\r\n\/\'][^\r\n]*/", "$1$2", $string);
		$string = preg_replace("/([^\'\"ps\s]\s*)(\:[^\r\n\'\"\[\]]*?\"[^\n\r\'\"]*?\")([^\n\r\/a-zA-Z0-9]*?)(\/\/)[^\r\n\/\"][^\r\n]*/", "$1$2", $string);

		//Remove all after ; slah slah+
		$string = preg_replace("/([^\n\r;]*?[;]\s*)(\/\/[^\r\n](?!([^\n\r]*?\"\s*;))[^\r\n]*?)[\n\r]/", "$1", $string);

		// Remove all whitespaces
		$string = preg_replace('/\s+/', ' ', $string);
		$string = preg_replace('/\s*(?:(?=[=\-\+\|%&\*\)\[\]\{\};:\,\.\<\>\!\@\#\^`~]))/', '', $string);
		$string = preg_replace('/(?:(?<=[=\-\+\|%&\*\)\[\]\{\};:\,\.\<\>\?\!\@\#\^`~]))\s*/', '', $string);
		$string = preg_replace('/([^a-zA-Z0-9\s\-=+\|!@#$%^&*()`~\[\]{};:\'",<.>\/?])\s+([^a-zA-Z0-9\s\-=+\|!@#$%^&*()`~\[\]{};:\'",<.>\/?])/', '$1$2', $string);

		return $string;
	}
}