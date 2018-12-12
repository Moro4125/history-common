<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Chain\Strategy;

use Moro\History\Common\Chain\ChainStrategyInterface;
use Moro\History\Common\Type\TypeInterface;

/**
 * Class ListStrategy
 * @package Moro\History\Common\Chain\Strategy
 */
class ListStrategy implements ChainStrategyInterface
{
	const ID = 2;

	const DELETE_LIST = 1;
	const UPDATE_LIST = 2;
	const CREATE_LIST = 3;
	const LISTS_ORDER = 4;

	const FROM_INDEX = 0;
	const TO_INDEX   = 1;

	const ADD_DEL_VALUE = 1;

	/**
	 * @return int
	 */
	static public function getStrategyId(): int
	{
		return self::ID;
	}

	/**
	 * @param TypeInterface $type
	 * @param string $path
	 * @param array $action
	 * @param mixed $a
	 * @param mixed $b
	 * @return array
	 */
	public function calculate(TypeInterface $type, string $path, array $action, $a, $b): array
	{
		$prefix = ($path == '/') ? '/' : $path . '/';
		$steps = [$path => $action];

		$steps[$path] = $action;
		$aMap = [];

		foreach ($action[self::LISTS_ORDER] as list($from, $to)) {
			$aMap[$to] = $from;
		}

		foreach ($action[self::DELETE_LIST] as $key) {
			$index = $prefix . ($key ^ -1);
			$steps[$index] = [self::DELETE, $a[$key]];
		}

		foreach ($action[self::UPDATE_LIST] as $key) {
			$diff = $type->callCalculateOnChainStrategy($prefix . $key, $a[$aMap[$key] ?? $key], $b[$key]);
			$steps = array_merge($steps, $diff);
		}

		foreach ($action[self::CREATE_LIST] as $key) {
			$steps[$prefix . $key] = [self::ADD, $b[$key]];
		}

		return $steps;
	}

	/**
	 * @param TypeInterface $type
	 * @param array $steps
	 * @param string $path
	 * @param mixed $value
	 */
	public function stepUp(TypeInterface $type, array $steps, string $path, &$value)
	{
		if (empty($steps[$path])) {
			throw new \RuntimeException(sprintf('History record is broken. Path: "%1$s".', $path));
		}

		$action = $steps[$path];
		$prefix = ($path == '/') ? '/' : $path . '/';

		// Check.
		if (!is_array($value)) {
			$message = 'Try change list, but entity is not array. Path: "%1$s".';
			throw new \RuntimeException(sprintf($message, $path));
		}

		foreach ($action[self::DELETE_LIST] as $key) {
			$index = $prefix . ($key ^ -1);

			if (!array_key_exists($key, $value)) {
				$message = 'Try change value, but entity has not have key "%1$s". Path: "%2$s".';
				throw new \RuntimeException(sprintf($message, $key, $path));
			}

			if (!isset($steps[$index]) || reset($steps[$index]) != self::DELETE) {
				$message = 'History record is broken. Path: "%1$s".';
				throw new \RuntimeException(sprintf($message, $index));
			}

			if (json_encode($value[$key]) != json_encode($steps[$index][1])) {
				$message = 'Try change value, but entity has wrong value for key "%1$s". Path: "%2$s".';
				throw new \RuntimeException(sprintf($message, $key, $path));
			}
		}

		foreach ($action[self::UPDATE_LIST] as $key) {
			if (!array_key_exists($key, $value)) {
				$message = 'Try change value, but entity has not have key "%1$s". Path: "%2$s".';
				throw new \RuntimeException(sprintf($message, $key, $path));
			}

			if (!isset($steps[$prefix . $key])) {
				$message = 'History record is broken. Path: "%1$s".';
				throw new \RuntimeException(sprintf($message, $prefix . $key));
			}
		}

		foreach ($action[self::CREATE_LIST] as $key) {
			$code = isset($steps[$prefix . $key]) ? reset($steps[$prefix . $key]) : null;

			if ($code != self::ADD) {
				$message = 'History record is broken. Path: "%1$s".';
				throw new \RuntimeException(sprintf($message, $prefix . $key));
			}
		}

		// Delete.
		foreach ($action[self::DELETE_LIST] as $key) {
			unset($value[$key]);
		}

		// Reorder.
		$map = [];

		foreach ($action[self::LISTS_ORDER] as list($from, $to)) {
			$map[$to] = $value[$from];
			unset($value[$from]);
		}

		foreach ($map as $k => $v) {
			$value[$k] = $v;
		}

		// Test create records.
		foreach ($action[self::CREATE_LIST] as $key) {
			if (array_key_exists($key, $value)) {
				$message = 'Try change value, but entity already have key "%1$s". Path: "%2$s".';
				throw new \RuntimeException(sprintf($message, $key, $path));
			}
		}

		// Update.
		foreach ($action[self::UPDATE_LIST] as $key) {
			$strategyId = reset($steps[$prefix . $key]);
			$strategy = $type->getChainStrategyById($strategyId);
			$strategy->stepUp($type, $steps, $prefix . $key, $value[$key]);
		}

		// Create.
		foreach ($action[self::CREATE_LIST] as $key) {
			$value[$key] = $steps[$prefix . $key][self::ADD_DEL_VALUE];
		}

		// Prepare result.
		ksort($value);
	}

	/**
	 * @param TypeInterface $type
	 * @param array $steps
	 * @param string $path
	 * @param mixed $value
	 */
	public function stepDown(TypeInterface $type, array $steps, string $path, &$value)
	{
		if (empty($steps[$path])) {
			throw new \RuntimeException(sprintf('History record is broken. Path: "%1$s".', $path));
		}

		$action = $steps[$path];
		$prefix = ($path == '/') ? '/' : $path . '/';

		// Check.
		if (!is_array($value)) {
			$message = 'Try change list, but entity is not array. Path: "%1$s".';
			throw new \RuntimeException(sprintf($message, $path));
		}

		foreach ($action[self::CREATE_LIST] as $key) {
			if (!array_key_exists($key, $value)) {
				$message = 'Try change value, but entity has not have key "%1$s". Path: "%2$s".';
				throw new \RuntimeException(sprintf($message, $key, $path));
			}

			$code = isset($steps[$prefix . $key]) ? reset($steps[$prefix . $key]) : null;

			if ($code != self::ADD) {
				$message = 'History record is broken. Path: "%1$s".';
				throw new \RuntimeException(sprintf($message, $prefix . $key));
			}

			if (json_encode($value[$key]) != json_encode($steps[$prefix . $key][1])) {
				$message = 'Try change value, but entity has wrong value for key "%1$s". Path: "%2$s".';
				throw new \RuntimeException(sprintf($message, $key, $path));
			}
		}

		foreach ($action[self::UPDATE_LIST] as $key) {
			if (!array_key_exists($key, $value)) {
				$message = 'Try change value, but entity has not have key "%1$s". Path: "%2$s".';
				throw new \RuntimeException(sprintf($message, $key, $path));
			}

			if (!isset($steps[$prefix . $key])) {
				$message = 'History record is broken. Path: "%1$s".';
				throw new \RuntimeException(sprintf($message, $prefix . $key));
			}
		}

		foreach ($action[self::DELETE_LIST] as $key) {
			$index = $prefix . ($key ^ -1);

			if (!isset($steps[$index]) || reset($steps[$index]) != self::DELETE) {
				$message = 'History record is broken. Path: "%1$s".';
				throw new \RuntimeException(sprintf($message, $index));
			}
		}

		// Delete.
		foreach ($action[self::CREATE_LIST] as $key) {
			unset($value[$key]);
		}

		// Update.
		foreach ($action[self::UPDATE_LIST] as $key) {
			$strategyId = reset($steps[$prefix . $key]);
			$strategy = $type->getChainStrategyById($strategyId);
			$strategy->stepDown($type, $steps, $prefix . $key, $value[$key]);
		}

		// Reorder.
		$map = [];

		foreach ($action[self::LISTS_ORDER] as list($from, $to)) {
			$map[$from] = $value[$to];
			unset($value[$to]);
		}

		foreach ($map as $k => $v) {
			$value[$k] = $v;
		}

		// Create.
		foreach ($action[self::DELETE_LIST] as $key) {
			if (array_key_exists($key, $value)) {
				$message = 'Try change value, but entity already have key "%1$s". Path: "%2$s".';
				throw new \RuntimeException(sprintf($message, $key, $path));
			}

			$index = $prefix . ($key ^ -1);
			$value[$key] = $steps[$index][self::ADD_DEL_VALUE];
		}

		// Prepare result.
		ksort($value);
	}
}