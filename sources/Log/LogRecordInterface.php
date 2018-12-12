<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Log;

use Moro\History\Common\Chain\ChainInterface;
use Moro\History\Common\Entity\EntityRevisionInterface;
use Moro\History\Common\Type\TypeInterface;
use DateTime;

/**
 * Interface LogRecordInterface
 * @package Moro\History\Common\Log
 */
interface LogRecordInterface
{
	/**
	 * @return int
	 */
	function getAction(): int;

	/**
	 * @return TypeInterface
	 */
	function getType(): TypeInterface;

	/**
	 * @return int
	 */
	function getEntityId(): int;

	/**
	 * @return DateTime
	 */
	function getUpdatedAt(): DateTime;

	/**
	 * @return string
	 */
	function getUpdatedBy(): string;

	/**
	 * @return EntityRevisionInterface|null
	 */
	function getRevision(): ?EntityRevisionInterface;

	/**
	 * @return ChainInterface|null
	 */
	function getChain(): ?ChainInterface;
}