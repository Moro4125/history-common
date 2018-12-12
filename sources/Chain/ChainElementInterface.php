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
use Moro\History\Common\Entity\EntityRevisionInterface;

/**
 * Interface ChainElementInterface
 */
interface ChainElementInterface extends JsonSerializable, Countable
{
	const ENTITY_CREATE = 1;
	const ENTITY_UPDATE = 2;
	const ENTITY_DELETE = 3;
	const MAKE_SNAPSHOT = 4;
	const MAKE_ROLLBACK = 5;

	/**
	 * @return int
	 */
	function getAction(): int;

	/**
	 * @return int
	 */
	function getRevision(): int;

	/**
	 * @return int
	 */
	function getChangedAt(): int;

	/**
	 * @return string
	 */
	function getChangedBy(): string;

	/**
	 * @param EntityRevisionInterface $entity
	 * @return EntityRevisionInterface
	 */
	function stepUp(EntityRevisionInterface $entity): EntityRevisionInterface;

	/**
	 * @param EntityRevisionInterface $entity
	 * @return EntityRevisionInterface
	 */
	function stepDown(EntityRevisionInterface $entity): EntityRevisionInterface;

	/**
	 * @param string $path
	 * @return array|null
	 */
	function getDiffRecord(string $path): ?array;
}