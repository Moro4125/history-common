<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Chain\Strategy;

use Moro\History\Common\Chain\ChainStrategyInterface;
use Moro\History\Common\Tools\DiffMatchPatch;
use Moro\History\Common\Type\TypeInterface;

/**
 * Class TextStrategy
 * @package Moro\History\Common\Chain\Strategy
 */
class TextStrategy implements ChainStrategyInterface
{
	const ID          = 4;
	const KEY_DIFF_OP = 0;

	const DIFF = 1;

	/**
	 * @return int
	 */
	public static function getStrategyId(): int
	{
		return self::ID;
	}

	/**
	 * @param TypeInterface $type
	 * @param string $path
	 * @param array $action
	 * @param string $a
	 * @param string $b
	 * @return array
	 */
	public function calculate(TypeInterface $type, string $path, array $action, $a, $b): array
	{
		return [$path => $action];
	}

	/**
	 * @param TypeInterface $type
	 * @param array $steps
	 * @param string $path
	 * @param mixed $value
	 * @throws \Exception
	 */
	public function stepUp(TypeInterface $type, array $steps, string $path, &$value)
	{
		if (empty($steps[$path])) {
			throw new \RuntimeException(sprintf('History record is broken. Path: "%1$s".', $path));
		}

		$service = new DiffMatchPatch();
		$patches = $service->patchFromText($steps[$path][self::DIFF]);
		list($value, $flags) = $service->patchApply($patches, $value);

		if (false !== array_search(false, $flags)) {
			$message = 'Try change text, but entity is broken. Path: "%1$s".';
			throw new \RuntimeException(sprintf($message, $path));
		}
	}

	/**
	 * @param TypeInterface $type
	 * @param array $steps
	 * @param string $path
	 * @param mixed $value
	 * @throws \Exception
	 */
	public function stepDown(TypeInterface $type, array $steps, string $path, &$value)
	{
		if (empty($steps[$path])) {
			throw new \RuntimeException(sprintf('History record is broken. Path: "%1$s".', $path));
		}

		$service = new DiffMatchPatch();
		$patches = array_reverse($service->patchFromText($steps[$path][self::DIFF]));

		foreach ($patches as $patch) {
			$start = $patch->start1;
			$patch->start1 = $patch->start2;
			$patch->start2 = $start;

			$length = $patch->length1;
			$patch->length1 = $patch->length2;
			$patch->length2 = $length;

			foreach ($patch->diffs as &$diff) {
				if ($diff[self::KEY_DIFF_OP]) {
					$diff[self::KEY_DIFF_OP] ^= 1;
				}
			}

			unset($diff);
		}

		list($value, $flags) = $service->patchApply($patches, $value);

		if (false !== array_search(false, $flags)) {
			$message = 'Try change text, but entity is broken. Path: "%1$s".';
			throw new \RuntimeException(sprintf($message, $path));
		}
	}
}