<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\View;

use IteratorAggregate;

/**
 * Interface ViewInterface
 * @package Moro\History\Common\View
 */
interface ViewInterface extends IteratorAggregate
{
	/**
	 * @param int $from
	 * @param int $limit
	 * @return ViewRecordInterface[]
	 */
	function slice(int $from, int $limit): array;
}