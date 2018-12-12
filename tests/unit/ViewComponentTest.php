<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

use Moro\History\Common\Chain\ChainElementInterface;
use Moro\History\Common\Chain\Component\ChainComponent;
use Moro\History\Common\Chain\Factory\ChainClassFactory;
use Moro\History\Common\Entity\Factory\EntityClassFactory;
use Moro\History\Common\Type\Component\TypeComponent;
use Moro\History\Common\View\Component\ViewComponent;
use Moro\History\Common\View\Factory\ViewClassFactory;
use Moro\History\Common\View\Rule\ActionRule;
use Moro\History\Common\View\Rule\AuthorRule;
use Moro\History\Common\View\Rule\HashRule;
use Moro\History\Common\View\Rule\ListRule;
use Moro\History\Common\View\Rule\OrderRule;
use Moro\History\Common\View\Rule\ScalarRule;
use Moro\History\Common\View\ViewActionInterface;
use Moro\History\Common\View\ViewRecordInterface;
use Moro\History\Test\SimpleEntity;

/**
 * Class ViewComponentTest
 */
class ViewComponentTest extends \PHPUnit\Framework\TestCase
{
	use Codeception\Specify;
	use Codeception\AssertThrows;

	public function testScalar()
	{
		$type = new TypeComponent('entity');
		$type->setChainFactory(new ChainClassFactory());
		$type->setEntityFactory(new EntityClassFactory());
		$type->setViewFactory(new ViewClassFactory(null, 'en'));
		$type->addViewRule(new ActionRule());
		$type->addViewRule(new AuthorRule($session = 60));
		$type->addViewRule(new ScalarRule('ID', '/'));

		$component = new ChainComponent($type, 1);

		$updatedBy = 'tester';
		$timestamp = gmmktime(12, 0, 0, 9, 28, 2018);

		$this->specify('Initial value', function () use ($component, $timestamp, $updatedBy) {
			$entity = new SimpleEntity('entity', 1, 1);

			$component->commit(1, $entity, $timestamp, $updatedBy);

			$view = new ViewComponent($component);
			$slice = $view->slice(0, 10);
			verify(count($slice))->same(1);

			/** @var ViewRecordInterface $record */
			$record = $slice[0];
			verify($record)->isInstanceOf(ViewRecordInterface::class);
			verify(count($record->getChangedFields()))->same(1);

			/** @var ViewActionInterface $field */
			$field = $record->getChangedFields()[0];
			verify($field)->isInstanceOf(ViewActionInterface::class);
			verify(trim($field->getText()))->same('ID is set to value 1');
		});

		$this->specify('Initial value (EX)', function () use ($component, $timestamp, $updatedBy) {
			$view = new ViewComponent($component);
			$slice = $view->slice(0, 10, true);
			verify(count($slice))->same(0);
		});

		$timestamp++;

		$this->specify('Second value', function () use ($component, $timestamp, $updatedBy) {
			$entity = new SimpleEntity('entity', 1, 2);

			$component->commit(2, $entity, $timestamp, $updatedBy);

			$view = new ViewComponent($component);
			$slice = $view->slice(0, 10);
			verify(count($slice))->same(1);

			/** @var ViewRecordInterface $record */
			$record = $slice[0];
			verify($record)->isInstanceOf(ViewRecordInterface::class);
			verify(count($record->getChangedFields()))->same(1);

			/** @var ViewActionInterface $field */
			$field = $record->getChangedFields()[0];
			verify($field)->isInstanceOf(ViewActionInterface::class);
			verify(trim($field->getText()))->same('ID is set to value 2');
		});

		$this->specify('Second value (EX)', function () use ($component, $timestamp, $updatedBy) {
			$view = new ViewComponent($component);
			$slice = $view->slice(0, 10, true);
			verify(count($slice))->same(1);

			/** @var ViewRecordInterface $record */
			$record = $slice[0];
			verify($record)->isInstanceOf(ViewRecordInterface::class);
			verify(count($record->getChangedFields()))->same(1);

			/** @var ViewActionInterface $field */
			$field = $record->getChangedFields()[0];
			verify($field)->isInstanceOf(ViewActionInterface::class);
			verify(trim($field->getText()))->same('ID is changed from 1 to 2');
		});

		$updatedBy .= '2';
		$timestamp++;

		$this->specify('Third value', function () use ($component, $timestamp, $updatedBy) {
			$entity = new SimpleEntity('entity', 1, 3);

			$component->commit(2, $entity, $timestamp, $updatedBy);

			$view = new ViewComponent($component);
			$slice = $view->slice(0, 10);
			verify(count($slice))->same(2);

			/** @var ViewRecordInterface $record */
			$record = $slice[0];
			verify($record)->isInstanceOf(ViewRecordInterface::class);
			verify(count($record->getChangedFields()))->same(1);

			/** @var ViewActionInterface $field */
			$field = $record->getChangedFields()[0];
			verify($field)->isInstanceOf(ViewActionInterface::class);
			verify(trim($field->getText()))->same('ID is changed from 2 to 3');
		});

		$timestamp += $session + 1;

		$this->specify('Forth value', function () use ($component, $timestamp, $updatedBy) {
			$entity = new SimpleEntity('entity', 1, 4);

			$component->commit(2, $entity, $timestamp, $updatedBy);

			$view = new ViewComponent($component);
			$slice = $view->slice(0, 10);
			verify(count($slice))->same(3);

			/** @var ViewRecordInterface $record */
			$record = $slice[0];
			verify($record)->isInstanceOf(ViewRecordInterface::class);
			verify(count($record->getChangedFields()))->same(1);

			/** @var ViewActionInterface $field */
			$field = $record->getChangedFields()[0];
			verify($field)->isInstanceOf(ViewActionInterface::class);
			verify(trim($field->getText()))->same('ID is changed from 3 to 4');
		});

		$timestamp += $session;

		$this->specify('Fifth value', function () use ($component, $timestamp, $updatedBy) {
			$entity = new SimpleEntity('entity', 1, 5);

			$component->commit(2, $entity, $timestamp, $updatedBy);

			$view = new ViewComponent($component);
			$slice = $view->slice(0, 10);
			verify(count($slice))->same(3);

			/** @var ViewRecordInterface $record */
			$record = $slice[0];
			verify($record)->isInstanceOf(ViewRecordInterface::class);
			verify(count($record->getChangedFields()))->same(1);

			/** @var ViewActionInterface $field */
			$field = $record->getChangedFields()[0];
			verify($field)->isInstanceOf(ViewActionInterface::class);
			verify(trim($field->getText()))->same('ID is changed from 3 to 5');
		});

		$timestamp++;

		$this->specify('Last value', function () use ($component, $timestamp, $updatedBy) {
			$entity = new SimpleEntity('entity', 1, null);

			$component->commit(3, $entity, $timestamp, $updatedBy);

			$view = new ViewComponent($component);
			$slice = $view->slice(0, 10);
			verify(count($slice))->same(3);

			/** @var ViewRecordInterface $record */
			$record = $slice[0];
			verify($record)->isInstanceOf(ViewRecordInterface::class);
			verify(count($record->getChangedFields()))->same(1);

			/** @var ViewActionInterface $field */
			$field = $record->getChangedFields()[0];
			verify($field)->isInstanceOf(ViewActionInterface::class);
			verify(trim($field->getText()))->same('ID was delete. The value was 3');
		});

		$this->specify('Last value (EX)', function () use ($component, $timestamp, $updatedBy) {
			$view = new ViewComponent($component);
			$slice = $view->slice(0, 10, true);
			verify(count($slice))->same(3);

			/** @var ViewRecordInterface $record */
			$record = $slice[0];
			verify($record)->isInstanceOf(ViewRecordInterface::class);
			verify(count($record->getChangedFields()))->same(1);

			/** @var ViewActionInterface $field */
			$field = $record->getChangedFields()[0];
			verify($field)->isInstanceOf(ViewActionInterface::class);
			verify(trim($field->getText()))->same('ID is changed from 3 to 5');
		});

		$this->specify('Test iterator', function () use ($component) {
			$view = new ViewComponent($component);
			$count = 0;

			foreach ($view as $item) {
				$count++;
				verify($item)->isInstanceOf(ViewRecordInterface::class);
			}

			verify($count)->same(3);
		});
	}

	public function testList()
	{
		$type = new TypeComponent('entity');
		$type->setChainFactory(new ChainClassFactory());
		$type->setEntityFactory(new EntityClassFactory());
		$type->setViewFactory(new ViewClassFactory(null, 'en'));
		$type->addViewRule(new ActionRule());
		$type->addViewRule(new AuthorRule($session = 60));
		$type->addViewRule(new ListRule('Letters', '/', new ScalarRule()));
		$type->addViewRule(new OrderRule('letters', '/'));

		$component = new ChainComponent($type, 1);

		$updatedBy = 'tester';
		$timestamp = gmmktime(12, 0, 0, 9, 28, 2018);

		$this->specify('Initial value', function () use ($component, $timestamp, $updatedBy) {
			$entity = new SimpleEntity('entity', 1, []);

			$component->commit(ChainElementInterface::ENTITY_CREATE, $entity, $timestamp, $updatedBy);

			$view = new ViewComponent($component);
			$slice = $view->slice(0, 10);
			verify(count($slice))->same(0);
		});

		$timestamp++;

		$this->specify('First value', function () use ($component, $timestamp, $updatedBy) {
			$entity = new SimpleEntity('entity', 1, ['A']);

			$component->commit(ChainElementInterface::ENTITY_UPDATE, $entity, $timestamp, $updatedBy);

			$view = new ViewComponent($component);
			$slice = $view->slice(0, 10, false);
			verify(count($slice))->same(1);

			/** @var ViewRecordInterface $record */
			$record = $slice[0];
			verify($record)->isInstanceOf(ViewRecordInterface::class);
			verify(count($record->getChangedFields()))->same(1);

			/** @var ViewActionInterface $field */
			$field = $record->getChangedFields()[0];
			verify($field)->isInstanceOf(ViewActionInterface::class);
			verify(trim($field->getText()))->same('Letters is changed: push "A".');
		});

		$timestamp++;

		$this->specify('Second and third values', function () use ($component, $timestamp, $updatedBy) {
			$entity = new SimpleEntity('entity', 1, ['A', 'B', 'C']);

			$component->commit(ChainElementInterface::ENTITY_UPDATE, $entity, $timestamp, $updatedBy);

			$view = new ViewComponent($component);
			$slice = $view->slice(0, 10, false);
			verify(count($slice))->same(1);

			/** @var ViewRecordInterface $record */
			$record = $slice[0];
			verify($record)->isInstanceOf(ViewRecordInterface::class);
			verify(count($record->getChangedFields()))->same(1);

			/** @var ViewActionInterface $field */
			$field = $record->getChangedFields()[0];
			verify($field)->isInstanceOf(ViewActionInterface::class);
			verify(trim($field->getText()))->same('Letters is changed: push "A", push "B", push "C".');
		});

		$timestamp++;

		$this->specify('Remove second value, add "D"', function () use ($component, $timestamp, $updatedBy) {
			$entity = new SimpleEntity('entity', 1, ['D', 'A', 'C']);

			$component->commit(0, $entity, $timestamp, $updatedBy);

			$view = new ViewComponent($component);
			$slice = $view->slice(0, 10, false);
			verify(count($slice))->same(2);

			/** @var ViewRecordInterface $record */
			$record = $slice[0];
			verify($record)->isInstanceOf(ViewRecordInterface::class);
			verify(count($record->getChangedFields()))->same(2);

			/** @var ViewActionInterface $field */
			$field = $record->getChangedFields()[0];
			verify($field)->isInstanceOf(ViewActionInterface::class);
			verify(trim($field->getText()))->same('Letters is changed: remove "B", push "D".');

			/** @var ViewActionInterface $field */
			$field = $record->getChangedFields()[1];
			verify($field)->isInstanceOf(ViewActionInterface::class);
			verify(trim($field->getText()))->same('The order of letters has been changed.');
		});
	}

	public function testHash()
	{
		$hash = new HashRule('Meta', '/');
		$hash->addRule(new ScalarRule('Alpha', 'A'));
		$hash->addRule(new ScalarRule('Beta', 'B'));

		$type = new TypeComponent('entity');
		$type->setChainFactory(new ChainClassFactory());
		$type->setEntityFactory(new EntityClassFactory());
		$type->setViewFactory(new ViewClassFactory(null, 'en'));
		$type->addViewRule(new ActionRule());
		$type->addViewRule(new AuthorRule($session = 60));
		$type->addViewRule($hash);
		$type->addViewRule(new OrderRule('meta', '/'));

		$component = new ChainComponent($type, 1);

		$updatedBy = 'tester';
		$timestamp = gmmktime(12, 0, 0, 9, 28, 2018);

		$this->specify('Initial value', function () use ($component, $timestamp, $updatedBy) {
			$entity = new SimpleEntity('entity', 1, []);

			$component->commit(ChainElementInterface::ENTITY_CREATE, $entity, $timestamp, $updatedBy);

			$view = new ViewComponent($component);
			$slice = $view->slice(0, 10);
			verify(count($slice))->same(0);
		});

		$timestamp++;

		$this->specify('First value', function () use ($component, $timestamp, $updatedBy) {
			$entity = new SimpleEntity('entity', 1, ['A' => 1]);

			$component->commit(ChainElementInterface::ENTITY_UPDATE, $entity, $timestamp, $updatedBy);

			$view = new ViewComponent($component);
			$slice = $view->slice(0, 10, false);
			verify(count($slice))->same(1);

			/** @var ViewRecordInterface $record */
			$record = $slice[0];
			verify($record)->isInstanceOf(ViewRecordInterface::class);
			verify(count($record->getChangedFields()))->same(1);

			/** @var ViewActionInterface $field */
			$field = $record->getChangedFields()[0];
			verify($field)->isInstanceOf(ViewActionInterface::class);
			verify(trim($field->getText()))->same('Meta is changed: Alpha is set to value 1.');
		});

		$timestamp++;

		$this->specify('Second value', function () use ($component, $timestamp, $updatedBy) {
			$entity = new SimpleEntity('entity', 1, ['A' => 1, 'B' => 2]);

			$component->commit(ChainElementInterface::ENTITY_UPDATE, $entity, $timestamp, $updatedBy);

			$view = new ViewComponent($component);
			$slice = $view->slice(0, 10, false);
			verify(count($slice))->same(1);

			/** @var ViewRecordInterface $record */
			$record = $slice[0];
			verify($record)->isInstanceOf(ViewRecordInterface::class);
			verify(count($record->getChangedFields()))->same(1);

			/** @var ViewActionInterface $field */
			$field = $record->getChangedFields()[0];
			verify($field)->isInstanceOf(ViewActionInterface::class);
			verify(trim($field->getText()))->same('Meta is changed: Alpha is set to value 1, Beta is set to value 2.');
		});

		$timestamp++;

		$this->specify('Reorder', function () use ($component, $timestamp, $updatedBy) {
			$entity = new SimpleEntity('entity', 1, ['B' => 2, 'A' => 1]);

			$component->commit(0, $entity, $timestamp, $updatedBy);

			$view = new ViewComponent($component);
			$slice = $view->slice(0, 10, false);
			verify(count($slice))->same(2);

			/** @var ViewRecordInterface $record */
			$record = $slice[0];
			verify($record)->isInstanceOf(ViewRecordInterface::class);
			verify(count($record->getChangedFields()))->same(1);

			/** @var ViewActionInterface $field */
			$field = $record->getChangedFields()[0];
			verify($field)->isInstanceOf(ViewActionInterface::class);
			verify(trim($field->getText()))->same('The order of meta has been changed.');
		});

	}
}