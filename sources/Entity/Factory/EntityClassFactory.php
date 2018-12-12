<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Entity\Factory;

use Moro\History\Common\Chain\ChainInterface;
use Moro\History\Common\Entity\EntityFactoryInterface;
use Moro\History\Common\Entity\EntityRevisionInterface;
use Moro\History\Common\Entity\Revision\EntityRevision;

/**
 * Class EntityClassFactory
 * @package Moro\History\Common\Entity\Factory
 */
class EntityClassFactory implements EntityFactoryInterface
{
	/** @var string */
	protected $_revisionClass = EntityRevision::class;

	/**
	 * @param string $class
	 * @return EntityClassFactory
	 */
	public function setRevisionClass(string $class): EntityClassFactory
	{
		$this->_revisionClass = $class;

		return $this;
	}

	/**
	 * @param ChainInterface $chain
	 * @param int $revision
	 * @param mixed $data
	 * @return EntityRevisionInterface
	 */
	public function newRevision(ChainInterface $chain, int $revision, $data): EntityRevisionInterface
	{
		return new $this->_revisionClass($chain, $revision, $data);
	}
}