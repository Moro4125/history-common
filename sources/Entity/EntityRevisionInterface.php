<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Entity;

use DateTime;
use Moro\History\Common\Chain\ChainElementInterface;
use Moro\History\Common\Chain\ChainInterface;
use Moro\History\Common\Type\TypeInterface;

/**
 * Interface EntityRevisionInterface
 * @package Moro\History\Common\Entity
 */
interface EntityRevisionInterface extends EntityInterface
{
	/**
	 * @return TypeInterface
	 */
	function getTypeComponent(): TypeInterface;

	/**
	 * @return int
	 */
	function getRevisionId(): int;

	/**
	 * @return DateTime
	 */
	function getUpdatedAt(): DateTime;

	/**
	 * @return string
	 */
	function getUpdatedBy(): string;

	/**
	 * @return ChainElementInterface
	 */
	function getChanges(): ChainElementInterface;

	/**
	 * @return ChainInterface
	 */
	function getChain(): ChainInterface;
}