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
 * Interface ChainAdapterInterface
 * @package Moro\History\Common\Chain
 */
interface ChainAdapterInterface
{
	/**
	 * @param TypeInterface $type
	 * @param int $id
	 * @return int|null
	 */
	function getLastRevisionForId(TypeInterface $type, int $id): ?int;

	/**
	 * @param \Moro\History\Common\Type\TypeInterface $type
	 * @param int $id
	 * @param int|null $fromRevision
	 * @param null $toRevision
	 * @return array
	 */
	function loadElements(TypeInterface $type, int $id, int $fromRevision = null, $toRevision = null): array;

	/**
	 * @param TypeInterface $type
	 * @param ChainElementInterface $element
	 * @param int $id
	 */
	function saveElement(TypeInterface $type, ChainElementInterface $element, int $id);
}