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
use SplObserver;

/**
 * Interface ChainFactory
 * @package Moro\History\Common\Chain
 */
interface ChainFactoryInterface
{
	/**
	 * @param SplObserver $observer
	 * @return ChainFactoryInterface
	 */
	function addChainObserver(SplObserver $observer): ChainFactoryInterface;

	/**
	 * @param TypeInterface $type
	 * @param int $id
	 * @return ChainInterface
	 */
	function getChain(TypeInterface $type, int $id): ChainInterface;

	/**
	 * @param int $action
	 * @param int $revision
	 * @param int $updatedAt
	 * @param string $updatedBy
	 * @param array $diff
	 * @return ChainElementInterface
	 */
	function newElement(
		int $action,
		int $revision,
		int $updatedAt,
		string $updatedBy,
		array $diff
	): ChainElementInterface;
}