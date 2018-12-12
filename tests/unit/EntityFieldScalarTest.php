<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

use Moro\History\Common\Entity\Field\ScalarField;
use Moro\History\Common\Chain\Strategy\ScalarStrategy;

/**
 * Class EntityFieldScalarTest
 */
class EntityFieldScalarTest extends \PHPUnit\Framework\TestCase
{
	use Codeception\Specify;
	use Codeception\AssertThrows;

	/**
	 * @return array of arguments and results for test()
	 */
	public function dataProvider()
	{
		$obj = new \stdClass();
		$res = tmpfile();

		return [
			[null, null, true],
			[1, 2, true],
			['A', 'B', true],
			[null, [], true],
			[[], null, true],
			[1, [], true],
			['A', [], true],
			[[], 2, true],
			[[], 'B', true],
			[[], [], false],
			[[], $obj, false],
			[$obj, [], false],
			[1, $obj, false],
			['A', $obj, false],
			[$obj, 2, false],
			[$obj, 'B', false],
			[$obj, $obj, false],
			[[], $res, false],
			[$res, [], false],
			[1, $res, false],
			['A', $res, false],
			[$res, 2, false],
			[$res, 'B', false],
			[$res, $res, false],
			[$obj, $res, false],
			[$res, $obj, false],
		];
	}

	/**
	 * @dataProvider dataProvider
	 *
	 * @param $a
	 * @param $b
	 * @param $result
	 */
	public function test($a, $b, $result)
	{
		$field = new ScalarField();
		verify($field->correspondsTo($a, $b))->same($result);

		if ($result) {
			if ($a === $b) {
				verify($field->calculate($a, $b))->same([ScalarStrategy::NONE]);
			} else {
				verify($field->calculate($a, $b))->same([ScalarStrategy::ID, $a, $b]);
			}
		}
	}
}