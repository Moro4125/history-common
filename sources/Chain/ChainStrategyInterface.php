<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Chain;

use Moro\History\Common\Type\TypeInterface;

/**
 * Interface ChainStrategyInterface
 * @package Moro\History\Common\Chain
 */
interface ChainStrategyInterface
{
	const NONE   = 0;
	const DELETE = -1;
	const ADD    = -2;
	const VALUE  = 1;

	/**
	 * @return int
	 */
	static function getStrategyId(): int;

	/**
	 * @param TypeInterface $type
	 * @param string $path
	 * @param array $action
	 * @param mixed $a
	 * @param mixed $b
	 * @return array
	 */
	function calculate(TypeInterface $type, string $path, array $action, $a, $b): array;

	/**
	 * @param TypeInterface $type
	 * @param array $steps
	 * @param string $path
	 * @param mixed $value
	 */
	function stepUp(TypeInterface $type, array $steps, string $path, &$value);

	/**
	 * @param TypeInterface $type
	 * @param array $steps
	 * @param string $path
	 * @param mixed $value
	 */
	function stepDown(TypeInterface $type, array $steps, string $path, &$value);
}