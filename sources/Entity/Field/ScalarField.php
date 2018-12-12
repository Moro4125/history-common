<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Entity\Field;

use Moro\History\Common\Chain\Strategy\ScalarStrategy;
use Moro\History\Common\Entity\EntityFieldInterface;

/**
 * Class ScalarField
 * @package Moro\History\Common\Entity\Field
 */
class ScalarField implements EntityFieldInterface
{
	/**
	 * @param mixed $a
	 * @param mixed $b
	 * @return bool
	 */
	public function correspondsTo($a, $b)
	{
		return is_array($a) ? (!is_array($b) && !is_object($b) && !is_resource($b)) : (is_array($b) ? (!is_object($a) && !is_resource($a)) : !is_object($a) && !is_resource($a) && !is_object($b) && !is_resource($b));
	}

	/**
	 * @param mixed $a
	 * @param mixed $b
	 * @return array
	 */
	public function calculate($a, $b)
	{
		if (json_encode($a) == json_encode($b)) {
			return [ScalarStrategy::NONE];
		}

		return [ScalarStrategy::ID, $a, $b];
	}
}