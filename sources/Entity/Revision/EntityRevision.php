<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Entity\Revision;

use DateTime;
use Moro\History\Common\Chain\ChainElementInterface;
use Moro\History\Common\Chain\ChainInterface;
use Moro\History\Common\Entity\EntityRevisionInterface;
use Moro\History\Common\Type\TypeInterface;

/**
 * Class EntityRevision
 * @package Moro\History\Common\Entity\Revision
 */
class EntityRevision implements EntityRevisionInterface
{
	/** @var ChainInterface */
	protected $_chain;
	/** @var int */
	protected $_revision;
	/** @var mixed */
	protected $_data;

	/**
	 * @param ChainInterface $chain
	 * @param int $revision
	 * @param mixed $data
	 */
	public function __construct(ChainInterface $chain, int $revision, $data)
	{
		$this->_chain = $chain;
		$this->_revision = $revision;
		$this->_data = $data;
	}

	/**
	 * @return \Moro\History\Common\Type\TypeInterface
	 */
	public function getTypeComponent(): TypeInterface
	{
		return $this->_chain->getType();
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return (string)$this->_chain->getType();
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->_chain->getEntityId();
	}

	/**
	 * @return int
	 */
	public function getRevisionId(): int
	{
		return $this->_revision;
	}

	/**
	 * @return DateTime
	 */
	public function getUpdatedAt(): DateTime
	{
		$diff = $this->_chain->getElement($this->_revision);
		$timestamp = $diff->getChangedAt();

		return new DateTime("@$timestamp");
	}

	/**
	 * @return string
	 */
	public function getUpdatedBy(): string
	{
		$diff = $this->_chain->getElement($this->_revision);

		return $diff->getChangedBy();
	}

	/**
	 * @return mixed
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * @return ChainElementInterface
	 */
	public function getChanges(): ChainElementInterface
	{
		return $this->_chain->getElement($this->_revision);
	}

	/**
	 * @return ChainInterface
	 */
	public function getChain(): ChainInterface
	{
		return $this->_chain;
	}
}