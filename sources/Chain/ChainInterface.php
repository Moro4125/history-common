<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Chain;

use Countable;
use JsonSerializable;
use Moro\History\Common\Chain\Element\MergedElement;
use Moro\History\Common\Entity\EntityInterface;
use Moro\History\Common\Entity\EntityRevisionInterface;
use Moro\History\Common\Type\TypeInterface;
use SplObserver;
use SplSubject;

/**
 * Interface ChainInterface
 * @package Moro\History\Common\Chain
 */
interface ChainInterface extends SplSubject, JsonSerializable, Countable
{
	/**
	 * @param SplObserver $observer
	 * @return bool
	 */
	function contains(SplObserver $observer): bool;

	/**
	 * @param int $action
	 * @param EntityInterface $entity
	 * @param int $updatedAt
	 * @param string $updatedBy
	 * @return ChainElementInterface
	 */
	function commit(int $action, EntityInterface $entity, int $updatedAt, string $updatedBy): ChainElementInterface;

	/**
	 * @return \Moro\History\Common\Type\TypeInterface
	 */
	function getType(): TypeInterface;

	/**
	 * @return int
	 */
	function getEntityId(): int;

	/**
	 * @param ChainElementInterface $element
	 */
	function push(ChainElementInterface $element);

	/**
	 * @param int $rev1
	 * @param int $rev2
	 * @return MergedElement
	 */
	function getMergedElement(int $rev1, int $rev2): MergedElement;

	/**
	 * @param int|null $revision
	 * @return EntityRevisionInterface
	 */
	function getEntity(int $revision = null): EntityRevisionInterface;

	/**
	 * @param int $revision
	 * @return ChainElementInterface
	 */
	function getElement(int $revision): ChainElementInterface;
}