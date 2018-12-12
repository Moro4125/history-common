<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

use Moro\History\Common\Chain\ChainElementInterface;
use Moro\History\Common\Chain\ChainStrategyInterface;
use Moro\History\Common\Chain\Component\ChainComponent;
use Moro\History\Common\Chain\Element\ChainElement;
use Moro\History\Common\Chain\Factory\ChainClassFactory;
use Moro\History\Common\Chain\Strategy\HashStrategy;
use Moro\History\Common\Chain\Strategy\ScalarStrategy;
use Moro\History\Common\Entity\Factory\EntityClassFactory;
use Moro\History\Common\Entity\Field\ExtendedHashField;
use Moro\History\Common\Entity\Field\ExtendedListField;
use Moro\History\Common\Type\Component\TypeComponent;
use Moro\History\Test\BadEntityField;
use Moro\History\Test\SimpleEntity;

/**
 * Class ChainComponentTest
 */
class ChainComponentTest extends \PHPUnit\Framework\TestCase
{
	use Codeception\Specify;
	use Codeception\AssertThrows;

	public function testGood()
	{
		$type = new TypeComponent('entity');
		$type->setChainFactory(new ChainClassFactory());
		$type->setEntityFactory(new EntityClassFactory());
		$type->addEntityField('/stars', new ExtendedListField('id'));
		$type->addEntityField('/meta/person', new ExtendedHashField('id'));
		$component = new ChainComponent($type, 1);

		$this->specify('Test empty history chain', function () use ($type, $component) {
			$entity = $component->getEntity();

			verify($entity->getTypeComponent())->same($type);
			verify($entity->getId())->same(1);
			verify($entity->getRevisionId())->same(-1);
			verify($entity->getData())->null();

			$this->assertThrows([RuntimeException::class, 'Revision "-1" is not exists.'], function () use ($entity) {
				$entity->getChanges();
			});

			$this->assertThrows([RuntimeException::class, 'Revision "-1" is not exists.'], function () use ($entity) {
				$entity->getUpdatedAt();
			});

			$this->assertThrows([RuntimeException::class, 'Revision "-1" is not exists.'], function () use ($entity) {
				$entity->getUpdatedBy();
			});

			verify(json_encode($component))->same('["entity",1,[]]');
		});

		$record = null;

		$this->specify('Add initial revision', function () use ($component, &$record) {
			$record = [
				'id'    => 1,
				'title' => 'New article',
				'tags'  => ['test', 'people', '2018'],
				'stars' => [['id' => 1, 'name' => 'Alpha'], ['id' => 2, 'name' => 'Beta']],
				'body'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse vestibulum magna nunc, quis tempor felis ultricies sed. Nulla a hendrerit lorem. Integer imperdiet nisi consectetur, mollis neque ullamcorper, molestie odio. Nullam eu volutpat diam. Aenean tincidunt, ex fringilla facilisis sodales, felis arcu iaculis nunc, eu pulvinar ante nibh eget nisi. Nulla facilisi. Curabitur nec leo viverra, ultrices elit id, efficitur nisi. Curabitur turpis urna, dignissim vitae fringilla vel, viverra cursus diam. Etiam tincidunt euismod lacus egestas congue.',
				'i18n'  => ['ru' => ['hello' => 'Privet']],
			];

			$updatedBy = 'tester';
			$timestamp = time();
			$entity = new SimpleEntity($component->getType(), 1, $record);

			$diff = $component->commit(2, $entity, $timestamp, $updatedBy);
			verify($diff->getRevision())->same(0);
			verify($diff->getChangedAt())->same($timestamp);
			verify($diff->getChangedBy())->same($updatedBy);

			verify($diff->getDiffRecord('/id'))->same([ChainStrategyInterface::ADD, 1]);
			verify($diff->getDiffRecord('/i18n/ru/hello'))->same([ChainStrategyInterface::ADD, 'Privet']);
			verify($diff->getDiffRecord('/title/ru'))->null();
			verify($diff->getDiffRecord('/tags/0'))->same([-2, 'test']); // ->null();
			verify($diff->getDiffRecord('/tags'))->same([ChainStrategyInterface::ADD, ['test', 'people', '2018']]);

			$entity = $component->getEntity(0);
			verify($entity->getRevisionId())->same(0);
			verify($entity->getUpdatedAt()
				->getTimestamp())->same($timestamp);
			verify($entity->getUpdatedBy())->same($updatedBy);
		});

		$rev0 = $record;

		$this->specify('Add first revision', function () use ($component, &$record, $rev0) {
			$record['title'] = 'Hello, world';
			$record['tags'] = ['2018', 'people'];
			$record['stars'][1]['name'] = 'Omega';
			$record['body'] = 'Lorem ipsum dolor serenity amet, consectetur adipiscing elit. Suspendisse vestibulum magna nunc, quis tempor felis ultricies sed. Nulla a hendrerit lorem. Integer imperdiet nisi consectetur, mollis neque ullamcorper, molestie odio. Nullam eu volutpat diam. Ex fringilla facilisis sodales, felis arcu iaculis nunc, eu pulvinar ante nibh eget nisi. Nulla facilisi. Curabitur nec leo viverra, ultrices elit id, efficitur nisi. Curabitur turpis urna, dignissim vitae fringilla vel, viverra cursus diam. Etiam tincidunt euismod lacus egestas congue. Morbi non consectetur leo. Aliquam laoreet tristique sapien, ut pulvinar massa rutrum id.';
			// change 'sit' to 'serenity'
			// replace 'Aenean tincidunt, e' to 'E'
			// add 'Morbi non consectetur leo. Aliquam laoreet tristique sapien, ut pulvinar massa rutrum id.'
			$record['i18n']['en']['hello'] = 'Hello!';

			$updatedBy = 'tester';
			$timestamp = time() + 1;
			$entity = new SimpleEntity($component->getType(), 1, $record);

			$diff = $component->commit(2, $entity, $timestamp, $updatedBy);
			verify($diff->getRevision())->same(1);
			verify($diff->getChangedAt())->same($timestamp);
			verify($diff->getChangedBy())->same($updatedBy);

			verify($diff->getDiffRecord('/i18n/ru/hello'))->null();
			verify($diff->getDiffRecord('/i18n/en/hello'))->same([ChainStrategyInterface::ADD, 'Hello!']);
			verify($diff->getDiffRecord('/i18n/en/hello/world'))->null();

			$entity = $component->getEntity(1);
			verify($entity->getData())->same($record);

			$entity = $component->getEntity(0);
			verify($entity->getData())->same($rev0);
		});

		$rev1 = $record;

		$this->specify('Add second revision', function () use ($component, &$record, $rev0, $rev1) {
			$record['meta'] = [
				'published_at' => '2018/07/30T12:50:00+00:00',
				'authors'      => ['Andrey'],
				'person'       => ['id' => 7, 'name' => 'UFO'],
			];
			$record['tags'] = ['2019', 'people'];
			$record['stars'] = [['id' => 2, 'name' => 'Beta'], ['id' => 1, 'name' => 'Alpha']];
			unset($record['i18n']['ru']);

			$updatedBy = 'tester';
			$timestamp = time() + 2;
			$entity = new SimpleEntity($component->getType(), 1, $record);

			$diff = $component->commit(2, $entity, $timestamp, $updatedBy);
			verify($diff->getRevision())->same(2);
			verify($diff->getChangedAt())->same($timestamp);
			verify($diff->getChangedBy())->same($updatedBy);
			verify($diff->getDiffRecord('/'))->same([
				HashStrategy::ID,
				[],
				['tags', 'stars', 'i18n'],
				[['meta', 6]],
				[]
			]);
			verify($diff->getDiffRecord('/unknown'))->null();

			verify($diff->getDiffRecord('/i18n/ru/hello'))->same([ChainStrategyInterface::DELETE, 'Privet']);

			$entity = $component->getEntity(2);
			verify($component->getCursor())->same(2);
			verify($entity->getData())->same($record);

			$entity = $component->getEntity(1);
			verify($component->getCursor())->same(1);
			verify($entity->getData())->same($rev1);

			$entity = $component->getEntity(0);
			verify($component->getCursor())->same(0);
			verify($entity->getData())->same($rev0);
		});

		$rev2 = $record;

		$this->specify('Add third revision', function () use ($component, &$record, $rev0, $rev1, $rev2) {
			$record['meta']['authors'] = ['Andrey', 'Boris'];
			$record['meta']['images'] = [
				'img1' => '/pic/image1.jpg',
				'img2' => '/pic/image2.jpg',
				'img3' => '/pic/image3.jpg'
			];
			$record['meta']['person'] = ['id' => 9, 'name' => 'Enemy'];
			unset($record['meta']['published_at']);

			$updatedBy = 'tester';
			$timestamp = time() + 3;
			$entity = new SimpleEntity($component->getType(), 1, $record);

			$diff = $component->commit(2, $entity, $timestamp, $updatedBy);
			verify($diff->getRevision())->same(3);
			verify($diff->getChangedAt())->same($timestamp);
			verify($diff->getChangedBy())->same($updatedBy);

			verify($diff->count())->same(7);
			verify($diff->getDiffRecord('/meta/person/name'))->same([ScalarStrategy::ID, 'UFO', 'Enemy']);
			verify($diff->getDiffRecord('/meta/person'))->same([
				ScalarStrategy::ID,
				['id' => 7, 'name' => 'UFO'],
				['id' => 9, 'name' => 'Enemy']
			]);

			$entity = $component->getEntity(3);
			verify($entity->getData())->same($record);

			$entity = $component->getEntity(2);
			verify($entity->getData())->same($rev2);

			$entity = $component->getEntity(1);
			verify($entity->getData())->same($rev1);

			$entity = $component->getEntity(0);
			verify($entity->getData())->same($rev0);
		});

		$rev3 = $record;

		$this->specify('Add fourth revision', function () use ($component, &$record, $rev0, $rev1, $rev2, $rev3) {
			$record['meta']['images'] = ['img2' => '/pic/image2.jpg', 'img1' => '/pic/image1.jpg'];
			$record['meta']['person']['name'] = 'Unknown';

			$updatedBy = 'tester';
			$timestamp = time() + 4;
			$entity = new SimpleEntity($component->getType(), 1, $record);

			$diff = $component->commit(2, $entity, $timestamp, $updatedBy);
			verify($diff->getRevision())->same(4);
			verify($diff->getChangedAt())->same($timestamp);
			verify($diff->getChangedBy())->same($updatedBy);

			verify($diff->getDiffRecord('/meta/person/name'))->same([ScalarStrategy::ID, 'Enemy', 'Unknown']);
			verify($diff->getDiffRecord('/meta/person'))->same([HashStrategy::ID, [], ['name'], [], []]);

			$entity = $component->getEntity(4);
			verify($entity->getData())->same($record);

			$entity = $component->getEntity(3);
			verify($entity->getData())->same($rev3);

			$entity = $component->getEntity(2);
			verify($entity->getData())->same($rev2);

			$entity = $component->getEntity(1);
			verify($entity->getData())->same($rev1);

			$entity = $component->getEntity(0);
			verify($entity->getData())->same($rev0);
		});

		$this->specify('Add empty revision', function () use ($component, &$record) {
			$updatedBy = 'tester';
			$timestamp = time() + 5;
			$entity = new SimpleEntity($component->getType(), 1, $record);

			$diff = $component->commit(2, $entity, $timestamp, $updatedBy);
			verify($diff->getAction())->same(2);
			verify($diff->getRevision())->same(-1);
			verify($diff->getChangedAt())->same($timestamp);
			verify($diff->getChangedBy())->same($updatedBy);
			verify($component->count())->same(5);

			verify($diff->getDiffRecord('/id'))->null();
		});

		$this->specify('Delete entity', function () use ($component) {
			$updatedBy = 'tester';
			$timestamp = time() + 6;
			$entity = new SimpleEntity($component->getType(), 1, null);

			$diff = $component->commit(ChainElementInterface::ENTITY_DELETE, $entity, $timestamp, $updatedBy);
			verify($diff->getAction())->same(ChainElementInterface::ENTITY_DELETE);
			verify($diff->getRevision())->same(5);
			verify($diff->getChangedAt())->same($timestamp);
			verify($diff->getChangedBy())->same($updatedBy);
			verify($component->count())->same(6);

			verify($diff->getDiffRecord('/id'))->same([ChainStrategyInterface::DELETE, 1]);
			verify($diff->getDiffRecord('/i18n/en/hello'))->same([ChainStrategyInterface::DELETE, 'Hello!']);
		});

		$this->specify('Test getMergedElement method (1)', function () use ($component) {
			$element = $component->getMergedElement(0, 5);

			verify($element->getRevision())->same(0);
			verify($element->getRevision2())->same(5);

			verify($element->getDiffRecord('/'))->null();
		});

		$this->specify('Test getMergedElement method (2)', function () use ($component) {
			$element = $component->getMergedElement(2, 4);

			verify($element->getRevision())->same(2);
			verify($element->getRevision2())->same(4);

			$entity1 = $component->getEntity(1);
			$entity2 = $component->getEntity(4);
			$entityR = $element->stepUp($entity1);
			verify($entityR->getData())->same($entity2->getData());
		});
	}

	public function testBad()
	{
		$type = new TypeComponent('entity');
		$type->setChainFactory(new ChainClassFactory());
		$type->setEntityFactory(new EntityClassFactory());
		$component = new ChainComponent($type, 1);

		$this->specify('Adding wrong revision', function () use ($type, $component) {
			$this->assertThrows([RuntimeException::class, 'Wrong revision order'], function () use ($component) {
				$component = clone $component;
				$diff = ['/' => [ScalarStrategy::ID, null, ['id' => 1]]];

				$element = new ChainElement(2, 1, time(), 'tester', $diff);
				$component->push($element);
			});

			$this->assertThrows([RuntimeException::class, 'Wrong diff action: [-1]'],
				function () use ($type, $component) {
					$type->addEntityField('/id', new BadEntityField());
					$component = clone $component;

					$entity = new SimpleEntity($type, 1, ['id' => 1]);
					$component->commit(2, $entity, time(), 'tester');

					$entity = new SimpleEntity($type, 1, ['id' => 2]);
					$component->commit(2, $entity, time(), 'tester');
				});
		});
	}
}