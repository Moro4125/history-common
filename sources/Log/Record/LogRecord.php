<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Log\Record;

use DateTime;
use Moro\History\Common\Chain\ChainInterface;
use Moro\History\Common\Entity\EntityRevisionInterface;
use Moro\History\Common\Log\LogRecordInterface;
use Moro\History\Common\Log\Tools\ChainsCache;
use Moro\History\Common\Type\TypeInterface;

/**
 * Class LogRecord
 * @package Moro\History\Common\Log\Record
 */
class LogRecord implements LogRecordInterface
{
	/** @var ChainsCache */
	private $_cache;
	private $_type;
	private $_entityId;
	private $_action;
	private $_updatedAt;
	private $_updatedBy;
	private $_revision;

	/**
	 * @param TypeInterface $type
	 * @param int $id
	 * @param int $action
	 * @param int $at
	 * @param string $by
	 * @param int|null $revision
	 */
	public function __construct(TypeInterface $type, int $id, int $action, int $at, string $by, int $revision = null)
	{
		$this->_type = $type;
		$this->_entityId = $id;
		$this->_action = $action;
		$this->_updatedAt = $at;
		$this->_updatedBy = $by;
		$this->_revision = $revision;
	}

	/**
	 * @param ChainsCache|null $cache
	 */
	public function setCache(?ChainsCache $cache)
	{
		$this->_cache = $cache;
	}

	/**
	 * @return TypeInterface
	 */
	public function getType(): TypeInterface
	{
		return $this->_type;
	}

	/**
	 * @return int
	 */
	public function getEntityId(): int
	{
		return $this->_entityId;
	}

	/**
	 * @return int
	 */
	public function getAction(): int
	{
		return $this->_action;
	}

	/**
	 * @return DateTime
	 */
	public function getUpdatedAt(): DateTime
	{
		return new DateTime('@' . $this->_updatedAt);
	}

	/**
	 * @return string
	 */
	public function getUpdatedBy(): string
	{
		return $this->_updatedBy;
	}

	/**
	 * @return ChainInterface|null
	 */
	public function getChain(): ?ChainInterface
	{
		if ($this->_cache) {
			if ($this->_cache->hasChain((string)$this->_type, $this->_entityId)) {
				return $this->_cache->getChain((string)$this->_type, $this->_entityId);
			}

			$factory = $this->_type->getChainFactory();
			$chain = $factory->getChain($this->_entityId);
			$this->_cache->addChain($chain);

			return $chain;
		} else {
			$factory = $this->_type->getChainFactory();

			return $factory->getChain($this->_entityId);
		}
	}

	/**
	 * @return EntityRevisionInterface|null
	 */
	public function getRevision(): ?EntityRevisionInterface
	{
		return (is_null($this->_revision) || !$chain = $this->getChain()) ? null : $chain->getEntity($this->_revision);
	}
}