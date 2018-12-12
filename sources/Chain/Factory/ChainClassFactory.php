<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Chain\Factory;

use Moro\History\Common\Chain\ChainAdapterInterface;
use Moro\History\Common\Chain\ChainElementInterface;
use Moro\History\Common\Chain\ChainFactoryInterface;
use Moro\History\Common\Chain\ChainInterface;
use Moro\History\Common\Chain\Component\ChainComponent;
use Moro\History\Common\Chain\Component\Observer\Chain2AdapterObserver;
use Moro\History\Common\Chain\Element\ChainElement;
use Moro\History\Common\Type\TypeInterface;
use SplObserver;

/**
 * Class ChainClassFactory
 * @package Moro\History\Common\Chain\Factory
 */
class ChainClassFactory implements ChainFactoryInterface
{
	/** @var ChainAdapterInterface */
	protected $_adapter;

	/** @var SplObserver[] */
	protected $_observers;

	/** @var string */
	protected $_chainClass = ChainComponent::class;

	/** @var string */
	protected $_elementClass = ChainElement::class;

	/**
	 * @param string $class
	 * @return ChainFactoryInterface
	 */
	public function setChainClass(string $class): ChainFactoryInterface
	{
		$this->_chainClass = $class;

		return $this;
	}

	/**
	 * @param string $class
	 * @return ChainFactoryInterface
	 */
	public function setElementClass(string $class): ChainFactoryInterface
	{
		$this->_elementClass = $class;

		return $this;
	}

	/**
	 * @param ChainAdapterInterface $adapter
	 * @return ChainFactoryInterface
	 */
	public function setAdapter(ChainAdapterInterface $adapter): ChainFactoryInterface
	{
		assert(is_null($this->_adapter));

		$this->_adapter = $adapter;
		$this->addChainObserver(new Chain2AdapterObserver($adapter));

		return $this;
	}

	/**
	 * @param SplObserver $observer
	 * @return ChainFactoryInterface
	 */
	public function addChainObserver(SplObserver $observer): ChainFactoryInterface
	{
		$this->_observers[] = $observer;

		return $this;
	}

	/**
	 * @param TypeInterface $type
	 * @param int $id
	 * @return ChainInterface
	 */
	public function getChain(TypeInterface $type, int $id): ChainInterface
	{
		assert(is_object($this->_adapter));

		$elements = [];

		if (null !== $lastRevision = $this->_adapter->getLastRevisionForId($type, $id)) {
			foreach ($this->_adapter->loadElements($type, $id, 0, $lastRevision) as $record) {
				list($action, $revision, $changedAt, $changedBy, $diff) = $record;
				$element = $this->newElement($action, $revision, $changedAt, $changedBy, $diff);
				$elements[$revision] = $element;
			}

			ksort($elements);
		}

		$chain = $this->newChain($type, $id, $elements);

		foreach ($this->_observers ?? [] as $observer) {
			$chain->attach(clone $observer);
		}

		return $chain;
	}

	/**
	 * @param int $action
	 * @param int $revision
	 * @param int $changedAt
	 * @param string $changedBy
	 * @param array $diff
	 * @return ChainElementInterface
	 */
	public function newElement(
		int $action,
		int $revision,
		int $changedAt,
		string $changedBy,
		array $diff
	): ChainElementInterface {
		return new $this->_elementClass($action, $revision, $changedAt, $changedBy, $diff);
	}

	/**
	 * @param \Moro\History\Common\Type\TypeInterface $type
	 * @param int $id
	 * @param array $elements
	 * @return ChainInterface
	 */
	protected function newChain(TypeInterface $type, int $id, array $elements): ChainInterface
	{
		return new $this->_chainClass($type, $id, $elements);
	}
}