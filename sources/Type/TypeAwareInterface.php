<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Type;

/**
 * Interface TypeAwareInterface
 * @package Moro\History\Common\Type
 */
interface TypeAwareInterface
{
	/**
	 * @param TypeInterface $type
	 */
	function setType(TypeInterface $type);

	/**
	 * @return TypeInterface|null
	 */
	function getType(): ?TypeInterface;
}