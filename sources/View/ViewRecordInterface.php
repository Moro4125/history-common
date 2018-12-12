<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\View;

/**
 * Interface ViewRecordInterface
 * @package Moro\History\Common\View
 */
interface ViewRecordInterface
{
	/**
	 * @return int
	 */
	function getStartedAt(): int;

	/**
	 * @return int
	 */
	function getUpdatedAt(): int;

	/**
	 * @return string
	 */
	function getUpdatedBy(): string;

	/**
	 * @return ViewActionInterface[]
	 */
	function getChangedFields(): array;
}