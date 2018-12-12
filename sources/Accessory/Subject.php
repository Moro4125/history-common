<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Accessory;

use SplObserver;
use SplObjectStorage;

/**
 * Trait Subject
 * @package Moro\History\Common\Accessory
 */
trait Subject
{
	/** @var SplObserver[]|SplObjectStorage */
	private $_observers;

	/**
	 * @param SplObserver $observer
	 */
	public function attach(SplObserver $observer)
	{
		$this->_observers || $this->_observers = new SplObjectStorage();

		if (!$this->_observers->contains($observer)) {
			$this->_observers->attach($observer);
			$this->notify();
		}
	}

	/**
	 * @param SplObserver $observer
	 * @return bool
	 */
	public function contains(SplObserver $observer): bool
	{
		return $this->_observers && $this->_observers->contains($observer);
	}

	/**
	 * @param SplObserver $observer
	 */
	public function detach(SplObserver $observer)
	{
		/** @var \SplSubject $self */
		$self = $this;

		if ($this->_observers) {
			if ($this->_observers->contains($observer)) {
				$this->_observers->detach($observer);
				$observer->update($self);
				$this->notify();
			}

			if (!$this->_observers->count()) {
				$this->_observers = null;
			}
		}
	}

	/**
	 * Notify all observers.
	 */
	public function notify()
	{
		/** @var \SplSubject $self */
		$self = $this;

		if ($this->_observers) {
			foreach (clone $this->_observers as $observer) {
				$observer->update($self);
			}
		}
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		if ($this->_observers) {
			foreach (clone $this->_observers as $observer) {
				$this->detach($observer);
			}
		}
	}
}