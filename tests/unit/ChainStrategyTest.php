<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

use Moro\History\Common\Chain\ChainStrategyInterface;
use Moro\History\Common\Chain\Element\ChainElement;
use Moro\History\Common\Chain\Strategy\HashStrategy;
use Moro\History\Common\Chain\Strategy\ListStrategy;
use Moro\History\Common\Chain\Strategy\ScalarStrategy;
use Moro\History\Common\Chain\Strategy\TextStrategy;
use Moro\History\Common\Type\Component\TypeComponent;

/**
 * Class ChainStrategyTest
 */
class ChainStrategyTest extends \PHPUnit\Framework\TestCase
{
	use Codeception\Specify;
	use Codeception\AssertThrows;

	public function testSerialization()
	{
		$strategy = new HashStrategy();

		$steps = [
			'/'   => [$strategy->getStrategyId(), [], [], ['id']],
			'/id' => [ChainStrategyInterface::ADD, 1],
		];
		$timestamp = time();

		$element = new ChainElement(2, 0, $timestamp, 'tester', $steps);
		$dump = '[2,0,' . $timestamp . ',"tester",{"\/":[3,[],[],["id"]],"\/id":[-2,1]}]';

		verify(json_encode($element))->same($dump);
	}

	public function testHashCommit()
	{
		$type = new TypeComponent('entity');

		$this->specify('Test wrong path in broken steps.', function () use ($type) {
			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/unknown".'],
				function () use ($type) {
					$strategy = new HashStrategy();
					$steps = [
						'/'   => [$strategy->getStrategyId(), [], [], ['id' => 0], []],
						'/id' => [ChainStrategyInterface::ADD, 1],
					];

					$value = [];
					$strategy->stepUp($type, $steps, '/unknown', $value);
				});
		});

		$this->specify('Test wrong entity record.', function () use ($type) {
			$this->assertThrows([
				RuntimeException::class,
				'Try change value, but entity has not have key "id". Path: "/".'
			], function () use ($type) {
				$strategy = new HashStrategy();
				$steps = [
					'/'   => [$strategy->getStrategyId(), [], ['id'], []],
					'/id' => [ScalarStrategy::ID, 1, 2],
				];

				$value = [];
				$strategy->stepUp($type, $steps, '/', $value);
			});

			$this->assertThrows([
				RuntimeException::class,
				'History record is broken. Path: "/id".'
			], function () use ($type) {
				$strategy = new HashStrategy();
				$steps = [
					'/' => [$strategy->getStrategyId(), [], ['id'], []],
				];

				$value = ['id' => 1];
				$strategy->stepUp($type, $steps, '/', $value);
			});

			$this->assertThrows([RuntimeException::class, 'Try replace value, but entity is broken. Path: "/id".'],
				function () use ($type) {
					$strategy = new HashStrategy();
					$steps = [
						'/'   => [$strategy->getStrategyId(), [], ['id'], []],
						'/id' => [ScalarStrategy::ID, 1, 2],
					];

					$value = ['id' => 3];
					$strategy->stepUp($type, $steps, '/', $value);
				});

			$this->assertThrows([RuntimeException::class, 'Try change hash, but entity is not array. Path: "/".'],
				function () use ($type) {
					$strategy = new HashStrategy();
					$steps = [
						'/'   => [$strategy->getStrategyId(), [], ['id'], []],
						'/id' => [ScalarStrategy::ID, 1, 2],
					];
					$value = null;
					$strategy->stepUp($type, $steps, '/', $value);
				});

			$this->assertThrows([
				RuntimeException::class,
				'Try change value, but entity already have key "id". Path: "/".'
			], function () use ($type) {
				$strategy = new HashStrategy();
				$steps = [
					'/'   => [$strategy->getStrategyId(), [], [], [['id', 0]]],
					'/id' => [ChainStrategyInterface::ADD, 1],
				];
				$value = ['id' => 1];
				$strategy->stepUp($type, $steps, '/', $value);
			});

			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/id".'],
				function () use ($type) {
					$strategy = new HashStrategy();
					$steps = [
						'/' => [$strategy->getStrategyId(), [['id', 0]], [], []],
					];
					$value = ['id' => 1];
					$strategy->stepUp($type, $steps, '/', $value);
				});

			$this->assertThrows([
				RuntimeException::class,
				'Try change value, but entity has wrong value for key "id". Path: "/".'
			], function () use ($type) {
				$strategy = new HashStrategy();
				$steps = [
					'/'   => [$strategy->getStrategyId(), [['id', 0]], [], []],
					'/id' => [ChainStrategyInterface::DELETE, 2],
				];
				$value = ['id' => 3];
				$strategy->stepUp($type, $steps, '/', $value);
			});

			$this->assertThrows([
				RuntimeException::class,
				'Try change value, but entity has not have key "id". Path: "/".'
			], function () use ($type) {
				$strategy = new HashStrategy();
				$steps = [
					'/'   => [$strategy->getStrategyId(), [['id', 0]], [], [], []],
					'/id' => [ScalarStrategy::ID, 1, 2],
				];
				$value = [];
				$strategy->stepUp($type, $steps, '/', $value);
			});

			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/id".'],
				function () use ($type) {
					$strategy = new HashStrategy();
					$steps = [
						'/'   => [$strategy->getStrategyId(), [], [], [['id', 0]], []],
						'/id' => [ChainStrategyInterface::DELETE, 2],
					];
					$value = [];
					$strategy->stepUp($type, $steps, '/', $value);
				});
		});
	}

	public function testListCommit()
	{
		$type = new TypeComponent('entity');

		$this->specify('Test wrong path in broken steps.', function () use ($type) {
			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/1".'],
				function () use ($type) {
					$strategy = new ListStrategy();
					$steps = [
						'/'  => [$strategy->getStrategyId(), [], [], [0], []],
						'/0' => [ChainStrategyInterface::ADD, 1],
					];

					$value = [];
					$strategy->stepUp($type, $steps, '/1', $value);
				});
		});

		$this->specify('Test wrong entity record.', function () use ($type) {
			$this->assertThrows([RuntimeException::class, 'Try change list, but entity is not array. Path: "/".'],
				function () use ($type) {
					$strategy = new ListStrategy();
					$steps = [
						'/'  => [$strategy->getStrategyId(), [], [0], [], []],
						'/0' => [ScalarStrategy::ID, 1, 2],
					];
					$value = null;
					$strategy->stepUp($type, $steps, '/', $value);
				});

			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/0".'],
				function () use ($type) {
					$strategy = new ListStrategy();
					$steps = [
						'/' => [$strategy->getStrategyId(), [], [0], [], []],
					];
					$value = [1];
					$strategy->stepUp($type, $steps, '/', $value);
				});

			$this->assertThrows([
				RuntimeException::class,
				'Try change value, but entity has not have key "0". Path: "/".'
			], function () use ($type) {
				$strategy = new ListStrategy();
				$steps = [
					'/'  => [$strategy->getStrategyId(), [0], [], [], []],
					'/0' => [ChainStrategyInterface::DELETE, 1],
				];
				$value = [];
				$strategy->stepUp($type, $steps, '/', $value);
			});

			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/-1".'],
				function () use ($type) {
					$strategy = new ListStrategy();
					$steps = [
						'/'   => [$strategy->getStrategyId(), [0], [], [], []],
						'/-1' => [ChainStrategyInterface::ADD, 1],
					];
					$value = [1];
					$strategy->stepUp($type, $steps, '/', $value);
				});

			$this->assertThrows([
				RuntimeException::class,
				'Try change value, but entity has wrong value for key "0". Path: "/".'
			], function () use ($type) {
				$strategy = new ListStrategy();
				$steps = [
					'/'   => [$strategy->getStrategyId(), [0], [], [], []],
					'/-1' => [ChainStrategyInterface::DELETE, 2],
				];
				$value = [1];
				$strategy->stepUp($type, $steps, '/', $value);
			});

			$this->assertThrows([
				RuntimeException::class,
				'Try change value, but entity has not have key "0". Path: "/".'
			], function () use ($type) {
				$strategy = new ListStrategy();
				$steps = [
					'/'  => [$strategy->getStrategyId(), [], [0], [], []],
					'/0' => [ScalarStrategy::ID, 1, 2],
				];
				$value = [];
				$strategy->stepUp($type, $steps, '/', $value);
			});

			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/0".'],
				function () use ($type) {
					$strategy = new ListStrategy();
					$steps = [
						'/' => [$strategy->getStrategyId(), [], [], [0], []],
					];
					$value = [];
					$strategy->stepUp($type, $steps, '/', $value);
				});

			$this->assertThrows([
				RuntimeException::class,
				'Try change value, but entity already have key "0". Path: "/".'
			], function () use ($type) {
				$strategy = new ListStrategy();
				$steps = [
					'/'  => [$strategy->getStrategyId(), [], [], [0], []],
					'/0' => [ChainStrategyInterface::ADD, 1],
				];
				$value = [1];
				$strategy->stepUp($type, $steps, '/', $value);
			});
		});
	}

	public function testScalarCommit()
	{
		$type = new TypeComponent('entity');

		$this->specify('Test wrong path in broken steps.', function () use ($type) {
			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/1".'],
				function () use ($type) {
					$strategy = new ScalarStrategy();
					$steps = [
						'/' => [$strategy->getStrategyId(), null, 1],
					];

					$value = [];
					$strategy->stepUp($type, $steps, '/1', $value);
				});
		});
	}

	public function testTextCommit()
	{
		$type = new TypeComponent('entity');

		$this->specify('Test wrong path in broken steps.', function () use ($type) {
			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/1".'],
				function () use ($type) {
					$strategy = new TextStrategy();
					$steps = [
						'/' => [$strategy->getStrategyId(), ''],
					];

					$value = [];
					$strategy->stepUp($type, $steps, '/1', $value);
				});
		});

		$this->specify('Test wrong entity record.', function () use ($type) {
			$this->assertThrows([RuntimeException::class, 'Try change text, but entity is broken. Path: "/".'],
				function () use ($type) {
					$strategy = new TextStrategy();
					$steps = [
						'/' => [$strategy->getStrategyId(), "@@ -115,16 +115,17 @@\n icies se\n+e\n d. Nulla\n"],
					];

					$value = '';
					$strategy->stepUp($type, $steps, '/', $value);
				});
		});
	}

	public function testHashRollback()
	{
		$type = new TypeComponent('entity');

		$this->specify('Test wrong path in broken steps.', function () use ($type) {
			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/unknown".'],
				function () use ($type) {
					$strategy = new HashStrategy();
					$steps = [
						'/'   => [$strategy->getStrategyId(), [], [], ['id' => 0], []],
						'/id' => [ChainStrategyInterface::ADD, 1],
					];
					$value = [];
					$strategy->stepDown($type, $steps, '/unknown', $value);
				});
		});

		$this->specify('Test wrong entity record.', function () use ($type) {
			$this->assertThrows([RuntimeException::class, 'Try replace value, but entity is broken. Path: "/id".'],
				function () use ($type) {
					$strategy = new HashStrategy();
					$steps = [
						'/'   => [$strategy->getStrategyId(), [], ['id'], [], []],
						'/id' => [ScalarStrategy::ID, 1, 2],
					];
					$value = ['id' => 3];
					$strategy->stepDown($type, $steps, '/', $value);
				});

			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/id".'],
				function () use ($type) {
					$strategy = new HashStrategy();
					$steps = [
						'/' => [$strategy->getStrategyId(), [], ['id'], [], []],
					];
					$value = ['id' => 3];
					$strategy->stepDown($type, $steps, '/', $value);
				});

			$this->assertThrows([RuntimeException::class, 'Try change hash, but entity is not array. Path: "/".'],
				function () use ($type) {
					$strategy = new HashStrategy();
					$steps = [
						'/'   => [$strategy->getStrategyId(), [], ['id'], [], []],
						'/id' => [ScalarStrategy::ID, 1, 2],
					];
					$value = null;
					$strategy->stepDown($type, $steps, '/', $value);
				});

			$this->assertThrows([
				RuntimeException::class,
				'Try change value, but entity has not have key "id". Path: "/".'
			], function () use ($type) {
				$strategy = new HashStrategy();
				$steps = [
					'/'   => [$strategy->getStrategyId(), [], [], [['id', 0]], []],
					'/id' => [ScalarStrategy::ID, 1, 2],
				];
				$value = [];
				$strategy->stepDown($type, $steps, '/', $value);
			});

			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/id".'],
				function () use ($type) {
					$strategy = new HashStrategy();
					$steps = [
						'/'   => [$strategy->getStrategyId(), [], [], [['id', 0]], []],
						'/id' => [ScalarStrategy::ID, 1, 2],
					];
					$value = ['id' => 3];
					$strategy->stepDown($type, $steps, '/', $value);
				});

			$this->assertThrows([
				RuntimeException::class,
				'Try change value, but entity has wrong value for key "id". Path: "/".'
			], function () use ($type) {
				$strategy = new HashStrategy();
				$steps = [
					'/'   => [$strategy->getStrategyId(), [], [], [['id', 0]], []],
					'/id' => [ChainStrategyInterface::ADD, 2],
				];
				$value = ['id' => 3];
				$strategy->stepDown($type, $steps, '/', $value);
			});

			$this->assertThrows([
				RuntimeException::class,
				'Try change value, but entity has not have key "id". Path: "/".'
			], function () use ($type) {
				$strategy = new HashStrategy();
				$steps = [
					'/'   => [$strategy->getStrategyId(), [], ['id'], [], []],
					'/id' => [ScalarStrategy::ID, 1, 2],
				];
				$value = [];
				$strategy->stepDown($type, $steps, '/', $value);
			});

			$this->assertThrows([
				RuntimeException::class,
				'Try change value, but entity already have key "id". Path: "/".'
			], function () use ($type) {
				$strategy = new HashStrategy();
				$steps = [
					'/'   => [$strategy->getStrategyId(), [['id', 0]], [], [], []],
					'/id' => [ChainStrategyInterface::DELETE, 2],
				];
				$value = ['id' => 1];
				$strategy->stepDown($type, $steps, '/', $value);
			});

			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/id".'],
				function () use ($type) {
					$strategy = new HashStrategy();
					$steps = [
						'/'   => [$strategy->getStrategyId(), [['id', 0]], [], [], []],
						'/id' => [ScalarStrategy::ID, 1, 2],
					];
					$value = [];
					$strategy->stepDown($type, $steps, '/', $value);
				});
		});
	}

	public function testListRollback()
	{
		$type = new TypeComponent('entity');

		$this->specify('Test wrong path in broken steps.', function () use ($type) {
			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/1".'],
				function () use ($type) {
					$strategy = new ListStrategy();
					$steps = [
						'/'  => [$strategy->getStrategyId(), [], [], [0], []],
						'/0' => [ChainStrategyInterface::ADD, 1],
					];
					$value = [];
					$strategy->stepDown($type, $steps, '/1', $value);
				});
		});

		$this->specify('Test wrong entity record.', function () use ($type) {
			$this->assertThrows([RuntimeException::class, 'Try change list, but entity is not array. Path: "/".'],
				function () use ($type) {
					$strategy = new ListStrategy();
					$steps = [
						'/'  => [$strategy->getStrategyId(), [0], [], [], []],
						'/0' => [ChainStrategyInterface::DELETE, 1],
					];
					$value = null;
					$strategy->stepDown($type, $steps, '/', $value);
				});

			$this->assertThrows([
				RuntimeException::class,
				'Try change value, but entity has not have key "0". Path: "/".'
			], function () use ($type) {
				$strategy = new ListStrategy();
				$steps = [
					'/'  => [$strategy->getStrategyId(), [], [], [0], []],
					'/0' => [ChainStrategyInterface::ADD, 1],
				];
				$value = [];
				$strategy->stepDown($type, $steps, '/', $value);
			});

			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/0".'],
				function () use ($type) {
					$strategy = new ListStrategy();
					$steps = [
						'/' => [$strategy->getStrategyId(), [], [0], [], []],
					];
					$value = [1];
					$strategy->stepDown($type, $steps, '/', $value);
				});

			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/0".'],
				function () use ($type) {
					$strategy = new ListStrategy();
					$steps = [
						'/'  => [$strategy->getStrategyId(), [], [], [0], []],
						'/0' => [ScalarStrategy::ID, 1],
					];
					$value = [1];
					$strategy->stepDown($type, $steps, '/', $value);
				});

			$this->assertThrows([
				RuntimeException::class,
				'Try change value, but entity has wrong value for key "0". Path: "/".'
			], function () use ($type) {
				$strategy = new ListStrategy();
				$steps = [
					'/'  => [$strategy->getStrategyId(), [], [], [0], []],
					'/0' => [ChainStrategyInterface::ADD, 2],
				];
				$value = [1];
				$strategy->stepDown($type, $steps, '/', $value);
			});

			$this->assertThrows([
				RuntimeException::class,
				'Try change value, but entity has not have key "0". Path: "/".'
			], function () use ($type) {
				$strategy = new ListStrategy();
				$steps = [
					'/'  => [$strategy->getStrategyId(), [], [0], [], []],
					'/0' => [ScalarStrategy::ID, 1, 2],
				];
				$value = [];
				$strategy->stepDown($type, $steps, '/', $value);
			});

			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/-1".'],
				function () use ($type) {
					$strategy = new ListStrategy();
					$steps = [
						'/'   => [$strategy->getStrategyId(), [0], [], [], []],
						'/-1' => [ScalarStrategy::ID, 1, 2],
					];
					$value = [2];
					$strategy->stepDown($type, $steps, '/', $value);
				});

			$this->assertThrows([
				RuntimeException::class,
				'Try change value, but entity already have key "0". Path: "/".'
			], function () use ($type) {
				$strategy = new ListStrategy();
				$steps = [
					'/'   => [$strategy->getStrategyId(), [0], [], [], []],
					'/-1' => [ChainStrategyInterface::DELETE, 1],
				];
				$value = [2];
				$strategy->stepDown($type, $steps, '/', $value);
			});
		});
	}

	public function testScalarRollback()
	{
		$type = new TypeComponent('entity');

		$this->specify('Test wrong path in broken steps.', function () use ($type) {
			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/2".'],
				function () use ($type) {
					$strategy = new ScalarStrategy();
					$steps = [
						'/' => [$strategy->getStrategyId(), null, 1],
					];
					$value = [];
					$strategy->stepDown($type, $steps, '/2', $value);
				});
		});
	}

	public function testTextRollback()
	{
		$type = new TypeComponent('entity');

		$this->specify('Test wrong path in broken steps.', function () use ($type) {
			$this->assertThrows([RuntimeException::class, 'History record is broken. Path: "/1".'],
				function () use ($type) {
					$strategy = new TextStrategy();
					$steps = [
						'/' => [$strategy->getStrategyId(), ''],
					];

					$value = [];
					$strategy->stepDown($type, $steps, '/1', $value);
				});
		});

		$this->specify('Test wrong entity record.', function () use ($type) {
			$this->assertThrows([RuntimeException::class, 'Try change text, but entity is broken. Path: "/".'],
				function () use ($type) {
					$strategy = new TextStrategy();
					$steps = [
						'/' => [$strategy->getStrategyId(), "@@ -115,16 +115,17 @@\n icies se\n+e\n d. Nulla\n"],
					];

					$value = '';
					$strategy->stepDown($type, $steps, '/', $value);
				});
		});
	}
}