<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Log;

/**
 * Interface LogInterface
 * @package Moro\History\Common\Log
 */
interface LogInterface
{
	/**
	 * @param int $from
	 * @param int $limit
	 * @return LogRecordInterface[]
	 */
	function select(int $from, int $limit): array;

	/**
	 * @param string $type
	 * @param int $from
	 * @param int $limit
	 * @return LogRecordInterface[]
	 */
	function selectByType(string $type, int $from, int $limit): array;
}