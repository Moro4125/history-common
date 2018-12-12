<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Chain\Component\Observer;

use Moro\History\Common\Accessory\Observer;
use Moro\History\Common\Chain\ChainAdapterInterface;
use Moro\History\Common\Chain\ChainInterface;
use SplObserver;

/**
 * Class Chain2AdapterObserver
 * @package Moro\History\Common\Chain\Adapter\Observer
 */
class Chain2AdapterObserver implements SplObserver
{
	use Observer;

	/** @var ChainAdapterInterface */
	protected $_adapter;

	/** @var integer */
	protected $_count;

	/**
	 * @param ChainAdapterInterface $adapter
	 */
	public function __construct(ChainAdapterInterface $adapter)
	{
		$this->_adapter = $adapter;
	}

	/**
	 * @param ChainInterface $chain
	 */
	public function handle(ChainInterface $chain)
	{
		if ($this->_count !== $count = $chain->count()) {
			$this->onChangeCount($chain);
			$this->_count = $count;
		}
	}

	/**
	 * @param ChainInterface $chain
	 */
	public function onAttach(ChainInterface $chain)
	{
		$this->_count = $chain->count();
	}

	/**
	 * @param ChainInterface $chain
	 */
	public function onChangeCount(ChainInterface $chain)
	{
		for ($revision = $this->_count, $iLen = $chain->count(); $revision < $iLen; $revision++) {
			$element = $chain->getElement($revision);
			$this->_adapter->saveElement($chain->getType(), $element, $chain->getEntityId());
		}
	}

	/**
	 * @param ChainInterface $chain
	 */
	public function onDetach(ChainInterface $chain)
	{
		$this->_count = null;
		unset($chain);
	}
}