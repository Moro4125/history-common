<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Accessory;

use SplSubject;

/**
 * Trait Observer
 * @package Moro\History\Common\Accessory
 */
trait Observer
{
	/** @var boolean */
	private $_attached;

	/**
	 * @param SplSubject $subject
	 */
	public function update(SplSubject $subject)
	{
		$attached = $this->_attached;

		if (false === $attached) {
			return;
		}

		try {
			$this->_attached = false;

			if (null === $attached) {
				$this->onAttach($subject);
				$attached = true;

				return;
			}

			/** @noinspection PhpUndefinedMethodInspection */
			if (!$subject->contains($this)) {
				$this->onDetach($subject);
				$attached = null;

				return;
			}

			$this->handle($subject);
		}
		finally {
			$this->_attached = $attached;
		}
	}

	/**
	 * @param $subject
	 */
	abstract function handle($subject);

	/**
	 * @param $subject
	 */
	abstract function onAttach($subject);

	/**
	 * @param $subject
	 */
	abstract function onDetach($subject);
}