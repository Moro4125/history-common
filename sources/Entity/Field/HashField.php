<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Entity\Field;

use Moro\History\Common\Chain\ChainStrategyInterface;
use Moro\History\Common\Chain\Strategy\HashStrategy;
use Moro\History\Common\Entity\EntityFieldInterface;

/**
 * Class HashField
 * @package Moro\History\Common\Entity\Field
 */
class HashField implements EntityFieldInterface
{
	/**
	 * @param mixed $a
	 * @param mixed $b
	 * @return bool
	 */
	public function correspondsTo($a, $b)
	{
		if (!is_array($a) || !is_array($b)) {
			return false;
		}

		return !array_filter(array_keys($a), 'is_numeric') && !array_filter(array_keys($b), 'is_numeric');
	}

	/**
	 * @param array $a
	 * @param array $b
	 * @return array
	 */
	public function calculate($a, $b)
	{
		$delList = array_keys(array_diff_key($a, $b));
		$addList = array_keys(array_diff_key($b, $a));
		$updList = array_keys(array_intersect_key($a, $b));

		$updListA = array_flip($updList);
		$updListB = array_flip(array_keys(array_intersect_key($b, $a)));

		$moveList = [];
		$ak = array_flip(array_keys($a));
		$bk = array_flip(array_keys($b));

		foreach ($delList as &$item) {
			$key = $item;
			$item = [$key, $ak[$key]];
		}

		unset($item);

		foreach ($updList as $key) {
			if ($updListA[$key] != $updListB[$key]) {
				$moveList[$key] = [$updListA[$key], $updListB[$key]];
			}
		}

		foreach ($addList as &$item) {
			$key = $item;
			$item = [$key, $bk[$key]];
		}

		unset($item);

		foreach ($updList as $index => $key) {
			if ($this->childIsSame($a[$key], $b[$key])) {
				unset($updList[$index]);
			}
		}

		if ($delList || $updList || $addList || $moveList) {
			return [HashStrategy::ID, $delList, array_values($updList), $addList, $moveList];
		}

		return [ChainStrategyInterface::NONE];
	}

	/**
	 * @param mixed $a
	 * @param mixed $b
	 * @return bool
	 */
	public function childIsSame($a, $b)
	{
		return serialize($a) === serialize($b);
	}
}