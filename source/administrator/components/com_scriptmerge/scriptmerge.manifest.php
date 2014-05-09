<?php
/**
 * Joomla! component ScriptMerge
 *
 * @author Yireo
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com/
 */

// Check to ensure this file is included in Joomla!  
defined('_JEXEC') or die();

class com_scriptmergeInstallerScript
{
	public function postflight($action, $installer)
	{
        $query = 'DELETE FROM `#__menu` WHERE `link`="index.php?option=com_scriptmerge"';
        $db = JFactory::getDBO();
        $db->setQuery($query);
        $db->query();
	}
}
