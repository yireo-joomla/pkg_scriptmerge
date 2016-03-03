<?php
/**
 * Joomla! component ScriptMerge
 *
 * @author    Yireo (info@yireo.com)
 * @package   ScriptMerge
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * HTML View class
 *
 * @static
 * @package ScriptMerge
 */
class ScriptMergeViewHome extends YireoViewHome
{
	/*
	 * Display method
	 *
	 * @param string $tpl
	 *
	 * @return null
	 */
	public function display($tpl = null)
	{
		$icons = array();
		$this->assignRef('icons', $icons);

		$urls = array();
		$urls['twitter'] = 'http://twitter.com/yireo';
		$urls['facebook'] = 'http://www.facebook.com/yireo';
		$urls['tutorials'] = 'https://www.yireo.com/tutorials/other-extensions';
		$urls['jed'] = 'http://extensions.joomla.org/extensions/owner/yireo';
		$this->assignRef('urls', $urls);

		parent::display($tpl);
	}
}
