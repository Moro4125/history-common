<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Entity\Field;

use Moro\History\Common\Chain\Strategy\TextStrategy;
use Moro\History\Common\Entity\EntityFieldInterface;
use Moro\History\Common\Tools\DiffMatchPatch;

/**
 * Class TextField
 * @package Moro\History\Common\Entity\Field
 */
class TextField implements EntityFieldInterface
{
	const MIN_TEXT_LENGTH = 64;

	/**
	 * @param mixed $a
	 * @param mixed $b
	 * @return bool
	 */
	public function correspondsTo($a, $b)
	{
		if (is_string($a) && is_string($b)) {
			return min(mb_strlen($a, 'UTF-8'), mb_strlen($b, 'UTF-8')) > self::MIN_TEXT_LENGTH;
		}

		return false;
	}

	/**
	 * @param string $a
	 * @param string $b
	 * @return array
	 *
	 * @throws \Exception
	 */
	public function calculate($a, $b)
	{
		$service = new DiffMatchPatch();

		$patches = $service->patchMake($a, $b);
		$patch = $service->patchToText($patches);

		return [TextStrategy::ID, $patch];
	}
}