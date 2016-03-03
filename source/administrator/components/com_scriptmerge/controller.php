<?php
/**
 * Joomla! component ScriptMerge
 *
 * @author    Yireo
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Include the loader
require_once JPATH_COMPONENT . '/lib/loader.php';

class ScriptMergeController extends YireoController
{
	/**
	 * Constructor
	 *
	 * @param null
	 *
	 * @return null
	 */
	public function __construct()
	{
		$this->_default_view = 'home';
		parent::__construct();
	}
}
