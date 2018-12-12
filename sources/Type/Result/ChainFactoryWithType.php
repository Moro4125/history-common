<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Type\Result;

use Moro\History\Common\Chain\ChainElementInterface;
use Moro\History\Common\Chain\ChainFactoryInterface;
use Moro\History\Common\Chain\ChainInterface;
use Moro\History\Common\Type\TypeInterface;
use SplObserver;

/**
 * Class ChainFactoryWithType
 * @package Moro\History\Common\Type
 */
class ChainFactoryWithType
{
	/** @var TypeInterface */
	protected $_type;
	/** @var ChainFactoryInterface */
	protected $_factory;

	/**
	 * @param TypeInterface $type
	 * @param ChainFactoryInterface $factory
	 */
	public function __construct(TypeInterface $type, ChainFactoryInterface $factory)
	{
		$this->_type = $type;
		$this->_factory = $factory;
	}

	/**
	 * @param SplObserver $observer
	 * @return ChainFactoryWithType
	 */
	public function addObserver(SplObserver $observer): ChainFactoryWithType
	{
		$this->_factory->addChainObserver($observer);

		return $this;
	}

	/**
	 * @param int $id
	 * @return ChainInterface
	 */
	public function getChain(int $id): ChainInterface
	{
		return $this->_factory->getChain($this->_type, $id);
	}

	/**
	 * @param int $action
	 * @param int $revision
	 * @param int $updatedAt
	 * @param string $updatedBy
	 * @param array $diff
	 * @return ChainElementInterface
	 */
	public function newElement(
		int $action,
		int $revision,
		int $updatedAt,
		string $updatedBy,
		array $diff
	): ChainElementInterface {
		return $this->_factory->newElement($action, $revision, $updatedAt, $updatedBy, $diff);
	}
}