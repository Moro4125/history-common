<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Entity;

/**
 * Interface EntityField
 * @package Moro\History\Common\Entity
 */
interface EntityFieldInterface
{
	/**
	 * @param mixed $a
	 * @param mixed $b
	 * @return bool
	 */
	function correspondsTo($a, $b);

	/**
	 * @param mixed $a
	 * @param mixed $b
	 * @return array
	 */
	function calculate($a, $b);
}