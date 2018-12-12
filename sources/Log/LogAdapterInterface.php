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
 * Interface LogAdapterInterface
 * @package Moro\History\Common\Log
 */
interface LogAdapterInterface
{
	/**
	 * @param int $from
	 * @param int $limit
	 * @return array
	 */
	function select(int $from, int $limit): array;

	/**
	 * @param string $type
	 * @param int $from
	 * @param int $limit
	 * @return array
	 */
	function selectByType(string $type, int $from, int $limit): array;
}