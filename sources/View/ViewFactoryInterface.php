<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\View;

use Moro\History\Common\Chain\ChainInterface;

/**
 * Interface ViewFactoryInterface
 * @package Moro\History\Common\View
 */
interface ViewFactoryInterface
{
	/**
	 * @param ChainInterface $chain
	 * @return ViewInterface
	 */
	function newView(ChainInterface $chain): ViewInterface;

	/**
	 * @param int $startedAt
	 * @param int $updatedAt
	 * @param string $updatedBy
	 * @param ViewActionInterface[] $fields
	 * @return ViewRecordInterface
	 */
	function newViewRecord(int $startedAt, int $updatedAt, string $updatedBy, array $fields): ViewRecordInterface;

	/**
	 * @param string $class
	 * @param null|array $arguments
	 * @return ViewActionInterface
	 */
	function newViewAction(string $class, array $arguments = null): ViewActionInterface;
}