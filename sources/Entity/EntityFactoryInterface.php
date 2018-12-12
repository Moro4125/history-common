<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Entity;

use Moro\History\Common\Chain\ChainInterface;

/**
 * Interface EntityFactoryInterface
 * @package Moro\History\Common\Entity
 */
interface EntityFactoryInterface
{
	/**
	 * @param ChainInterface $chain
	 * @param int $revision
	 * @param mixed $data
	 * @return EntityRevisionInterface
	 */
	function newRevision(ChainInterface $chain, int $revision, $data): EntityRevisionInterface;
}