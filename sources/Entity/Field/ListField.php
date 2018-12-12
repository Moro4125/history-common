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
use Moro\History\Common\Chain\Strategy\ListStrategy;
use Moro\History\Common\Entity\EntityFieldInterface;

/**
 * Class ListField
 * @package Moro\History\Common\Entity\Field
 */
class ListField implements EntityFieldInterface
{
	/**
	 * @param mixed $a
	 * @param mixed $b
	 * @return bool
	 */
	public function correspondsTo($a, $b)
	{
		if (is_array($a) && is_array($b)) {
			foreach ([$a, $b] as $c) {
				$indexesC = array_fill(null, count($c), null);

				if (array_diff_key($indexesC, $c)) {
					return false;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * @param array $a
	 * @param array $b
	 * @return array
	 */
	public function calculate($a, $b)
	{
		$aj = $a;
		$bj = $b;
		$aMap = [];

		$delList = [];
		$updList = [];
		$addList = [];
		$moveList = [];

		foreach ($aj as $aIndex => $valueA) {
			foreach ($bj as $bIndex => &$valueB) {
				if ($valueB !== null && $this->childIsEqual($valueA, $valueB)) {
					if ($aIndex != $bIndex) {
						$moveList[] = [$aIndex, $bIndex];
					}

					$updList[] = $bIndex;
					$aMap[$bIndex] = $aIndex;

					$valueB = null;
					unset($valueB);
					continue 2;
				}
			}

			unset($valueB);
			$delList[] = $aIndex;
		}

		foreach ($bj as $bIndex => $valueB) {
			if ($valueB !== null) {
				$addList[] = $bIndex;
			}
		}

		foreach ($updList as $index => $key) {
			if ($this->childIsSame($a[$aMap[$key]], $b[$key])) {
				unset($updList[$index]);
			}
		}

		if ($delList || $updList || $addList || $moveList) {
			return [ListStrategy::ID, $delList, array_values($updList), $addList, $moveList];
		}

		return [ChainStrategyInterface::NONE];
	}

	/**
	 * @param mixed $a
	 * @param mixed $b
	 * @return bool
	 */
	public function childIsEqual($a, $b)
	{
		return serialize($a) === serialize($b);
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