<?php
/**
 * Joomla! extension - ScriptMerge
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

require_once __DIR__ . '/compress/interface.php';

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class ScriptMergeUtilitiesCompress
{
	/**
	 * Subclass to handle compression
	 *
	 * @var null
	 */
	protected $handler = null;

	/**
	 * Set the handler subclass
	 *
	 * @param string $handler
	 */
	public function setHandler($handler)
	{
		$this->handler = $handler;
	}

	/**
	 * Method to get the handler subclass instance
	 *
	 * @param string $type Either css or js
	 *
	 * @return bool
	 */
	public function getHandlerInstance($type)
	{
		if (empty($this->handler))
		{
			throw new Exception('Empty handler');
		}

		$handlerFile = __DIR__ . '/compress/' . $type . '/' . $this->handler . '.php';

		if (file_exists($handlerFile) == false)
		{
			throw new Exception('Unknown handler file: ' . $handlerFile);
		}

		require_once $handlerFile;

		$handlerClass = 'ScriptMergeUtilitiesCompress' . ucfirst($type) . ucfirst($this->handler);

		if (class_exists($handlerClass) == false)
		{
			throw new Exception('Unknown handler class: ' . $handlerClass);
		}

		$handlerInstance = new $handlerClass;

		return $handlerInstance;
	}

	/**
	 * Method to compress a certain JS or CSS string
	 *
	 * @param $type
	 * @param $string
	 *
	 * @return string
	 */
	public function compress($type, $string)
	{
		$instance = $this->getHandlerInstance($type);

		if (is_object($instance) == false)
		{
			throw new Exception('Unknown handler class: ' . $this->handler);
		}

		if (method_exists($instance, 'compress') == false)
		{
			throw new Exception('Handler is missing compress() method');
		}

		return $instance->compress($string);
	}

	/**
	 * Method to compress a certain JS or CSS string
	 *
	 * @param $string
	 *
	 * @return string
	 */
	public function compressCss($string)
	{
		return $this->compress('css', $string);
	}

	/**
	 * Method to compress a certain JS or CSS string
	 *
	 * @param $string
	 *
	 * @return string
	 */
	public function compressJs($string)
	{
		return $this->compress('js', $string);
	}
}