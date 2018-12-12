<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

use Moro\History\Common\Chain\Strategy\HashStrategy;
use Moro\History\Common\Chain\ChainStrategyInterface;
use Moro\History\Common\Entity\Field\HashField;

/**
 * Class EntityFieldHashTest
 */
class EntityFieldHashTest extends \PHPUnit\Framework\TestCase
{
	use Codeception\Specify;
	use Codeception\AssertThrows;

	/**
	 * @return array of arguments and results for test()
	 */
	public function dataProvider()
	{
		$obj = new \stdClass();

		return [
			[null, null, false],
			[1, 2, false],
			['A', 'B', false],
			[null, [], false],
			[[], null, false],
			[1, [], false],
			['A', [], false],
			[[], 2, false],
			[[], 'B', false],
			[1, $obj, false],
			['A', $obj, false],
			[$obj, 2, false],
			[$obj, 'B', false],
			[$obj, $obj, false],
			[[], [], true, [ChainStrategyInterface::NONE]],
			[[], [1], false],
			[[1], [], false],
			[[1], [2], false],
			//[[], ['a' => 1], true, [HashStrategy::ID, [], [], ['a' => 0], []]],
			[[], ['a' => 1], true, [HashStrategy::ID, [], [], [['a', 0]], []]],
			//[['a' => 1], ['b' => 2], true, [HashStrategy::ID, ['a' => 0], [], ['b' => 0], []]],
			[['a' => 1], ['b' => 2], true, [HashStrategy::ID, [['a', 0]], [], [['b', 0]], []]],
			//[['a' => 1], ['a' => 1, 'b' => 2], true, [HashStrategy::ID, [], [], ['b' => 1], []]],
			[['a' => 1], ['a' => 1, 'b' => 2], true, [HashStrategy::ID, [], [], [['b', 1]], []]],
			[['a' => 1], ['a' => 2], true, [HashStrategy::ID, [], ['a'], [], []]],
		];
	}

	/**
	 * @dataProvider dataProvider
	 *
	 * @param $a
	 * @param $b
	 * @param $flag
	 * @param $step
	 */
	public function test($a, $b, $flag, $step = null)
	{
		$field = new HashField();
		verify($field->correspondsTo($a, $b))->same($flag);

		if ($flag) {
			verify($field->calculate($a, $b))->same($step);
		}
	}

	public function testChildIsSame()
	{
		$field = new HashField();
		verify($field->childIsSame(1, 1))->true();
		verify($field->childIsSame(1, 2))->false();
	}

	public function testMoveInChild()
	{
		$field = new HashField();

		$a = ['e1' => 1, 'e2' => ['i1' => 1, 'i2' => 2], 'e3' => 3];
		$b = ['e1' => 1, 'e2' => ['i2' => 2, 'i1' => 1], 'e3' => 3];
		$c = [HashStrategy::ID, [], ['e2'], [], []];

		verify($field->calculate($a, $b))->same($c);
	}
}