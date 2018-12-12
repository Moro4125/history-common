<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Test;

use Moro\History\Common\Accessory\Observer;
use SplObserver;

/**
 * Class DummyObserver
 * @package Moro\History\Test
 */
class DummyObserver implements SplObserver
{
	use Observer;

	/**
	 * @param $subject
	 */
	public function handle($subject)
	{
	}

	/**
	 * @param $subject
	 */
	public function onAttach($subject)
	{
	}

	/**
	 * @param $subject
	 */
	public function onDetach($subject)
	{
	}
}