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
 * Class ScalarStrategy
 * @package Moro\History\Common\Chain\Strategy
 */
class ScalarStrategy implements ChainStrategyInterface
{
	const ID = 1;

	const OLD_VALUE = 1;
	const NEW_VALUE = 2;

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
		return [$path => $action];
	}

	/**
	 * @param \Moro\History\Common\Type\TypeInterface $type
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

		if (json_encode($value) != json_encode($action[1])) {
			$message = 'Try replace value, but entity is broken. Path: "%1$s".';
			throw new \RuntimeException(sprintf($message, $path));
		}

		$value = $action[self::NEW_VALUE];
	}

	/**
	 * @param \Moro\History\Common\Type\TypeInterface $type
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

		if (json_encode($value) != json_encode($action[2])) {
			$message = 'Try replace value, but entity is broken. Path: "%1$s".';
			throw new \RuntimeException(sprintf($message, $path));
		}

		$value = $action[self::OLD_VALUE];
	}
}