<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Accessory;

/**
 * Trait Decorator
 * @package Moro\History\Common\Accessory
 */
trait Decorator
{
	/** @var object */
	protected $_instance;

	/**
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		return call_user_func_array([$this->_instance, $name], $arguments);
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		$instance = $this->_instance;

		return isset($instance->{$name});
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		$this->_instance->{$name} = $value;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->_instance->{$name};
	}

	/**
	 * @param string $name
	 */
	public function __unset($name)
	{
		$instance = $this->_instance;

		unset($instance->{$name});
	}
}