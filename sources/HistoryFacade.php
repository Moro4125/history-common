<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common;

use DateTime;
use DateTimeZone;
use Moro\History\Common\Chain\ChainElementInterface;
use Moro\History\Common\Entity\EntityInterface;
use Moro\History\Common\Entity\EntityRevisionInterface;
use Moro\History\Common\Entity\Revision\EntityRevision;
use Moro\History\Common\Log\LogInterface;
use Moro\History\Common\Log\LogRecordInterface;
use Moro\History\Common\Type\TypeLocator;
use Moro\History\Common\View\ViewInterface;

/**
 * Class HistoryFacade
 * @package Moro\History\Common
 */
class HistoryFacade
{
	/** @var TypeLocator */
	protected $_types;

	/** @var LogInterface */
	protected $_log;

	/**
	 * @param TypeLocator $locator
	 * @param LogInterface $log
	 */
	public function __construct(TypeLocator $locator, LogInterface $log)
	{
		$this->_types = $locator;
		$this->_log = $log;
	}

	/**
	 * @param EntityInterface $entity
	 * @param string $updatedBy
	 * @param DateTime|null $updatedAt
	 */
	public function create(EntityInterface $entity, string $updatedBy, DateTime $updatedAt = null)
	{
		$updatedAt = $updatedAt ?? new DateTime('now', new DateTimeZone('UTC'));
		$type = $this->_types->getType($entity->getType());
		$factory = $type->getChainFactory();
		$chain = $factory->getChain($entity->getId());
		$chain->commit(ChainElementInterface::ENTITY_CREATE, $entity, $updatedAt->getTimestamp(), $updatedBy);
	}

	/**
	 * @param EntityInterface $entity
	 * @param string $updatedBy
	 * @param DateTime|null $updatedAt
	 */
	public function update(EntityInterface $entity, string $updatedBy, DateTime $updatedAt = null)
	{
		$updatedAt = $updatedAt ?? new DateTime('now', new DateTimeZone('UTC'));
		$type = $this->_types->getType($entity->getType());
		$factory = $type->getChainFactory();
		$chain = $factory->getChain($entity->getId());
		$chain->commit(ChainElementInterface::ENTITY_UPDATE, $entity, $updatedAt->getTimestamp(), $updatedBy);
	}

	/**
	 * @param EntityInterface $entity
	 * @param string $updatedBy
	 * @param DateTime|null $updatedAt
	 */
	public function delete(EntityInterface $entity, string $updatedBy, DateTime $updatedAt = null)
	{
		$updatedAt = $updatedAt ?? new DateTime('now', new DateTimeZone('UTC'));
		$type = $this->_types->getType($entity->getType());
		$factory = $type->getChainFactory();
		$chain = $factory->getChain($entity->getId());
		$entity = new EntityRevision($chain, $chain->count(), null);
		$chain->commit(ChainElementInterface::ENTITY_DELETE, $entity, $updatedAt->getTimestamp(), $updatedBy);
	}

	/**
	 * @param string|null $type
	 * @param int|null $from
	 * @param int|null $limit
	 * @return LogRecordInterface[]
	 */
	public function select(string $type = null, int $from = null, int $limit = null)
	{
		if (is_null($type)) {
			return $this->_log->select($from, $limit);
		} else {
			return $this->_log->selectByType($type, $from, $limit);
		}
	}

	/**
	 * @param string $type
	 * @param int $id
	 * @param int|null $revision
	 * @return EntityRevisionInterface
	 */
	public function getEntity(string $type, int $id, int $revision = null): EntityRevisionInterface
	{
		return $this->_types->getType($type)
			->getChainFactory()
			->getChain($id)
			->getEntity($revision);
	}

	/**
	 * @param string $type
	 * @param int $id
	 * @return ViewInterface
	 */
	public function getHistory(string $type, int $id): ViewInterface
	{
		$type = $this->_types->getType($type);
		$chainFactory = $type->getChainFactory();
		$chain = $chainFactory->getChain($id);
		$viewFactory = $type->getViewFactory();
		$view = $viewFactory->newView($chain);

		return $view;
	}
}