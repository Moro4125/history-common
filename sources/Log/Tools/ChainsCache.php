<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Log\Tools;

use Moro\History\Common\Chain\ChainInterface;

/**
 * Class ChainsCache
 * @package Moro\History\Common\Log\Tools
 */
class ChainsCache
{
	private $_cache;

	/**
	 * @param string $type
	 * @param int $id
	 * @return bool
	 */
	public function hasChain(string $type, int $id): bool
	{
		return !empty($this->_cache[$type][$id]);
	}

	/**
	 * @param ChainInterface $chain
	 */
	public function addChain(ChainInterface $chain)
	{
		$this->_cache[(string)$chain->getType()][$chain->getEntityId()] = $chain;
	}

	/**
	 * @param string $type
	 * @param int $id
	 * @return ChainInterface
	 */
	public function getChain(string $type, int $id): ChainInterface
	{
		return $this->_cache[$type][$id];
	}
}