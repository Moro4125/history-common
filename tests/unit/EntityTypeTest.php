<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

use Moro\History\Common\Chain\Factory\ChainClassFactory;
use Moro\History\Common\Chain\Strategy\ListStrategy;
use Moro\History\Common\Entity\Field\ExtendedListField;
use Moro\History\Common\Entity\Field\HashField;
use Moro\History\Common\Entity\Field\ListField;
use Moro\History\Common\Entity\Field\ScalarField;
use Moro\History\Common\Type\Component\TypeComponent;
use Moro\History\Test\DummyObserver;

/**
 * Class EntityTypeTest
 */
class EntityTypeTest extends \PHPUnit\Framework\TestCase
{
	use Codeception\Specify;
	use Codeception\AssertThrows;

	public function testDefaultEntityTypes()
	{
		$type = new TypeComponent('entity');
		verify((string)$type)->same('entity');

		$field = $type->getEntityFieldByPath('/', 1, 2);
		verify($field)->isInstanceOf(ScalarField::class);

		$field = $type->getEntityFieldByPath('/a', [], []);
		verify($field)->isInstanceOf(ListField::class);

		$field = $type->getEntityFieldByPath('/b', [1], [2]);
		verify($field)->isInstanceOf(ListField::class);

		$field = $type->getEntityFieldByPath('/', ['a' => 1], ['b' => 2]);
		verify($field)->isInstanceOf(HashField::class);

		$this->specify('Test mixed array, that is not list and is not hash.', function () use ($type) {
			$this->assertThrows([RuntimeException::class, 'Wrong value at path "/".'], function () use ($type) {
				$type->getEntityFieldByPath('/', ['a' => 1, 2], ['b' => 2]);
			});
		});

		$this->specify('Test wrong path.', function () use ($type) {
			$this->assertThrows([RuntimeException::class, 'Wrong path "".'], function () use ($type) {
				$type->getEntityFieldByPath('', ['a' => 1, 2], ['b' => 2]);
			});
		});
	}

	public function testConcreteEntityTypes()
	{
		$type = new TypeComponent('entity');
		$type->addEntityField('/tags', new ExtendedListField());

		$a = [['id' => 1, 'name' => 'tag1']];
		$b = [['id' => 2, 'name' => 'tag2'], ['id' => 1, 'name' => 'tag3']];
		$field = $type->getEntityFieldByPath('/tags', $a, $b);
		verify($field)->isInstanceOf(ExtendedListField::class);

		$diff = $field->calculate($a, $b);
		verify($diff)->same([ListStrategy::ID, [], [1], [0], [[0, 1]]]);

		$a = [['id' => 1, 'name' => 'tag1']];
		$b = [['name' => 'tag2'], ['id' => 1, 'name' => 'tag3']];
		$field = $type->getEntityFieldByPath('/tags', $a, $b);
		verify($field)->isNotInstanceOf(ExtendedListField::class);

		$a = ['a' => 1];
		$b = ['b' => 2];
		$field = $type->getEntityFieldByPath('/tags', $a, $b);
		verify($field)->isNotInstanceOf(ExtendedListField::class);
	}

	public function testGetChainFactory()
	{
		$type = new TypeComponent('entity');
		$type->setChainFactory(new ChainClassFactory());
		$type->getChainFactory()
			->addObserver(new DummyObserver());
		verify($type->getId())->same(237519976);
	}
}