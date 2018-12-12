<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Test;

use Moro\History\Common\Entity\Field\ExtendedListField;

/**
 * Class BadEntityField
 * @package Moro\History\Test
 */
class BadEntityField extends ExtendedListField
{
	public function correspondsTo($a, $b)
	{
		return true;
	}

	public function calculate($a, $b)
	{
		return [-1];
	}
}