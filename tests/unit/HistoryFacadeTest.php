<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

use Moro\History\Common\Adapter\MemoryAdapter;
use Moro\History\Common\Chain\ChainElementInterface;
use Moro\History\Common\Chain\ChainInterface;
use Moro\History\Common\Chain\Component\ChainComponent;
use Moro\History\Common\Chain\Element\ChainElement;
use Moro\History\Common\Chain\Factory\ChainClassFactory;
use Moro\History\Common\Entity\EntityRevisionInterface;
use Moro\History\Common\HistoryFacade;
use Moro\History\Common\Log\Component\LogComponent;
use Moro\History\Common\Log\LogRecordInterface;
use Moro\History\Common\Type\Component\TypeComponent;
use Moro\History\Common\Type\TypeLocator;
use Moro\History\Test\SimpleEntity;
use Moro\History\Common\Entity\Factory\EntityClassFactory;
use Moro\History\Common\Entity\Revision\EntityRevision;

/**
 * Class HistoryFacadeTest
 */
class HistoryFacadeTest extends \PHPUnit\Framework\TestCase
{
	use Codeception\Specify;
	use Codeception\AssertThrows;

	const TYPE    = 'entity';
	const AUTHOR1 = 'Andrey';
	const AUTHOR2 = 'Boris';

	/** @var HistoryFacade */
	protected $_facade;

	/**
	 * This method is called before each test.
	 */
	public function setUp()
	{
		parent::setUp();

		$adapter = new MemoryAdapter();
		$type = new TypeComponent(self::TYPE);

		$factory = new ChainClassFactory();
		$factory->setAdapter($adapter);
		$factory->setChainClass(ChainComponent::class);
		$factory->setElementClass(ChainElement::class);
		$type->setChainFactory($factory);

		$factory = new EntityClassFactory();
		$factory->setRevisionClass(EntityRevision::class);
		$type->setEntityFactory($factory);

		$locator = (new TypeLocator())->addType($type);
		$log = new LogComponent($adapter, $locator);
		$facade = new HistoryFacade($locator, $log);

		$this->_facade = $facade;
	}

	// tests
	public function test()
	{
		$this->specify('Test empty history list', function () {
			$list = $this->_facade->select(null, 0, 10);
			verify($list)->same([]);

			$list = $this->_facade->select(self::TYPE, 0, 10);
			verify($list)->same([]);
		});

		$this->specify('Create new entity', function () {
			$record = ['id' => 1, 'name' => 'Hello, world!'];
			$entity = new SimpleEntity(self::TYPE, 1, $record);
			$updated = new DateTime('2018-08-05T12:00:00+00:00');

			$this->_facade->create($entity, self::AUTHOR1, $updated);

			$entity = $this->_facade->getEntity(self::TYPE, 1);
			verify($entity->getType())->same(self::TYPE);
			verify($entity->getId())->same(1);
			verify($entity->getData())->same($record);
			verify($entity->getRevisionId())->same(0);
			verify($entity->getUpdatedBy())->same(self::AUTHOR1);
			verify($entity->getUpdatedAt())->equals($updated);
		});

		$this->specify('Test history list with one revision', function () {
			$updated = new DateTime('2018-08-05T12:00:00+00:00');

			foreach ([$this->_facade->select(null, 0, 10), $this->_facade->select(self::TYPE, 0, 10)] as $list) {
				/** @var LogRecordInterface $record */
				$record = array_shift($list);
				verify($record)->isInstanceOf(LogRecordInterface::class);
				verify((string)$record->getType())->same(self::TYPE);
				verify($record->getEntityId())->same(1);
				verify($record->getAction())->same(ChainElementInterface::ENTITY_CREATE);
				verify($record->getUpdatedAt())->equals($updated);
				verify($record->getUpdatedBy())->same(self::AUTHOR1);
				verify($record->getRevision())->isInstanceOf(EntityRevisionInterface::class);

				/** @var LogRecordInterface $record */
				$record = array_shift($list);
				verify($record)->same(null);
			}
		});

		$this->specify('Update exists entity', function () use (&$record) {
			$record = ['id' => 1, 'name' => 'Welcome to another world'];
			$entity = new SimpleEntity(self::TYPE, 1, $record);
			$updated = new DateTime('2018-08-05T12:00:01+00:00');

			$this->_facade->update($entity, self::AUTHOR2, $updated);

			$entity = $this->_facade->getEntity(self::TYPE, 1);
			verify($entity->getType())->same(self::TYPE);
			verify($entity->getId())->same(1);
			verify($entity->getData())->same($record);
			verify($entity->getRevisionId())->same(1);
			verify($entity->getUpdatedBy())->same(self::AUTHOR2);
			verify($entity->getUpdatedAt())->equals($updated);
		});

		$this->specify('Test history list with two revisions', function () {
			$updated1 = new DateTime('2018-08-05T12:00:00+00:00');
			$updated2 = new DateTime('2018-08-05T12:00:01+00:00');

			foreach ([
						 $this->_facade->select(null, 0, 10),
						 $this->_facade->select(self::TYPE, 0, 10)
					 ] as $index => $list) {
				$index++;

				$this->specify("($index)", function () use (&$list, $updated1, $updated2) {
					/** @var LogRecordInterface $record */
					$record = array_shift($list);
					verify($record)->isInstanceOf(LogRecordInterface::class);
					verify((string)$record->getType())->same(self::TYPE);
					verify($record->getEntityId())->same(1);
					verify($record->getAction())->same(ChainElementInterface::ENTITY_UPDATE);
					verify($record->getUpdatedAt())->equals($updated2);
					verify($record->getUpdatedBy())->same(self::AUTHOR2);
					verify($record->getRevision())->isInstanceOf(EntityRevisionInterface::class);

					/** @var LogRecordInterface $record */
					$record = array_shift($list);
					verify($record)->isInstanceOf(LogRecordInterface::class);
					verify((string)$record->getType())->same(self::TYPE);
					verify($record->getEntityId())->same(1);
					verify($record->getAction())->same(ChainElementInterface::ENTITY_CREATE);
					verify($record->getUpdatedAt())->equals($updated1);
					verify($record->getUpdatedBy())->same(self::AUTHOR1);
					verify($record->getRevision())->isInstanceOf(EntityRevisionInterface::class);

					/** @var LogRecordInterface $record */
					$record = array_shift($list);
					verify($record)->same(null);
				});
			}
		});

		$this->specify('Delete entity', function () use ($record) {
			$entity = new SimpleEntity(self::TYPE, 1, $record);
			$updated = new DateTime('2018-08-05T12:00:02+00:00');

			$this->_facade->delete($entity, self::AUTHOR1, $updated);

			$entity = $this->_facade->getEntity(self::TYPE, 1);
			verify($entity->getType())->same(self::TYPE);
			verify($entity->getId())->same(1);
			verify($entity->getData())->same(null);
			verify($entity->getRevisionId())->same(2);
			verify($entity->getUpdatedBy())->same(self::AUTHOR1);
			verify($entity->getUpdatedAt())->equals($updated);
		});

		$this->specify('Test history list for deleted entity', function () {
			$updated1 = new DateTime('2018-08-05T12:00:00+00:00');
			$updated2 = new DateTime('2018-08-05T12:00:01+00:00');
			$updated3 = new DateTime('2018-08-05T12:00:02+00:00');

			$list = $this->_facade->select(self::TYPE, 0, 1);
			verify(count($list))->same(1);
			/** @var LogRecordInterface $record */
			$record = array_shift($list);
			verify($record->getChain())->isInstanceOf(ChainInterface::class);

			foreach ([
						 $this->_facade->select(null, 0, 10),
						 $this->_facade->select(self::TYPE, 0, 10)
					 ] as $index => $list) {
				$index++;

				$this->specify("($index)", function () use (&$list, $updated1, $updated2, $updated3) {
					/** @var LogRecordInterface $record */
					$record = array_shift($list);
					verify($record)->isInstanceOf(LogRecordInterface::class);
					verify((string)$record->getType())->same(self::TYPE);
					verify($record->getEntityId())->same(1);
					verify($record->getAction())->same(ChainElementInterface::ENTITY_DELETE);
					verify($record->getUpdatedAt())->equals($updated3);
					verify($record->getUpdatedBy())->same(self::AUTHOR1);
					verify($record->getRevision())->isInstanceOf(EntityRevisionInterface::class);

					/** @var LogRecordInterface $record */
					$record = array_shift($list);
					verify($record)->isInstanceOf(LogRecordInterface::class);
					verify((string)$record->getType())->same(self::TYPE);
					verify($record->getEntityId())->same(1);
					verify($record->getAction())->same(ChainElementInterface::ENTITY_UPDATE);
					verify($record->getUpdatedAt())->equals($updated2);
					verify($record->getUpdatedBy())->same(self::AUTHOR2);
					verify($record->getRevision())->isInstanceOf(EntityRevisionInterface::class);

					/** @var LogRecordInterface $record */
					$record = array_shift($list);
					verify($record)->isInstanceOf(LogRecordInterface::class);
					verify((string)$record->getType())->same(self::TYPE);
					verify($record->getEntityId())->same(1);
					verify($record->getAction())->same(ChainElementInterface::ENTITY_CREATE);
					verify($record->getUpdatedAt())->equals($updated1);
					verify($record->getUpdatedBy())->same(self::AUTHOR1);
					verify($record->getRevision())->isInstanceOf(EntityRevisionInterface::class);

					/** @var LogRecordInterface $record */
					$record = array_shift($list);
					verify($record)->same(null);
				});
			}
		});
	}
}