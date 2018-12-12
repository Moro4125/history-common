<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

use Moro\History\Common\Chain\ChainStrategyInterface;
use Moro\History\Common\Chain\Strategy\ListStrategy;
use Moro\History\Common\Entity\Field\ListField;

/**
 * Class EntityFieldListTest
 */
class EntityFieldListTest extends \PHPUnit\Framework\TestCase
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
			[['a' => 1, 2], ['a' => 1, 2], false],
			[[], ['a' => 1], false],
			[['a' => 1], ['b' => 2], false],
			[['a' => 1], [], false],
			[[], ['A'], true, [ListStrategy::ID, [], [], [0], []]],
			[['A'], [], true, [ListStrategy::ID, [0], [], [], []]],
			[['A'], ['B', 'A'], true, [ListStrategy::ID, [], [], [0], [[0, 1]]]],
			[['A'], [null, 'A'], true, [ListStrategy::ID, [], [], [], [[0, 1]]]],
			[['A', 0], [0, 'A'], true, [ListStrategy::ID, [], [], [], [[0, 1], [1, 0]]]],
			[['A', 0], [null, 'A'], true, [ListStrategy::ID, [1], [], [], [[0, 1]]]],
			[['A'], ['A', false], true, [ListStrategy::ID, [], [], [1], []]],
			[['A'], ['B'], true, [ListStrategy::ID, [0], [], [0], []]],
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
		$field = new ListField();
		verify($field->correspondsTo($a, $b))->same($flag);

		if ($flag) {
			verify($field->calculate($a, $b))->same($step);
		}
	}

	public function testChildIsSame()
	{
		$field = new ListField();
		verify($field->childIsSame('A', 'A'))->true();
		verify($field->childIsSame('A', 'B'))->false();
	}

	public function testChildIsEqual()
	{
		$field = new ListField();
		verify($field->childIsEqual('A', 'A'))->true();
		verify($field->childIsEqual('A', 'B'))->false();
	}
}