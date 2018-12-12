<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Chain\Component;

use Moro\History\Common\Accessory\Subject;
use Moro\History\Common\Chain\ChainElementInterface;
use Moro\History\Common\Chain\ChainInterface;
use Moro\History\Common\Chain\Element\MergedElement;
use Moro\History\Common\Entity\EntityInterface;
use Moro\History\Common\Entity\EntityRevisionInterface;
use Moro\History\Common\Entity\Revision\EntityRevision;
use Moro\History\Common\Type\TypeInterface;

/**
 * Class ChainComponent
 * @package Moro\History\Common\Chain\Component
 */
class ChainComponent implements ChainInterface
{
	use Subject;

	/** @var TypeInterface */
	protected $_type;

	/** @var integer */
	protected $_id;

	/** @var ChainElementInterface[] */
	protected $_chain;

	/** @var integer */
	protected $_count;

	/** @var null|EntityRevisionInterface */
	protected $_cache;

	/**
	 * @param TypeInterface $type
	 * @param integer $id
	 * @param array|null $chain
	 */
	public function __construct(TypeInterface $type, int $id, array $chain = null)
	{
		$this->_type = $type;
		$this->_id = $id;
		$this->_chain = $chain ?? [];
		$this->_count = $chain ? max(array_keys($chain)) : -1;
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
		return $this->_id;
	}

	/**
	 * @return int|null
	 */
	public function getCursor(): ?int
	{
		return $this->_cache ? $this->_cache->getRevisionId() : null;
	}

	/**
	 * @param ChainElementInterface $element
	 */
	public function push(ChainElementInterface $element)
	{
		if ($rev = $element->getRevision()) {
			if (!isset($this->_chain[$rev - 1])) {
				throw new \RuntimeException('Wrong revision order');
			}
		}

		$this->_count = max($this->_count, $rev);
		$this->_chain[$rev] = $element;
		$this->notify();
	}

	/**
	 * @param int $action
	 * @param EntityInterface $entity
	 * @param int $at
	 * @param string $by
	 * @return ChainElementInterface
	 */
	public function commit(int $action, EntityInterface $entity, int $at, string $by): ChainElementInterface
	{
		$prev = $this->getEntity();
		$diff = $this->_type->callCalculateOnChainStrategy('/', $prev->getData(), $entity->getData());

		$factory = $this->_type->getChainFactory();

		if (count($diff)) {
			$element = $factory->newElement($action, count($this->_chain), $at, $by, $diff);
			$this->push($element);
		} else {
			$element = $factory->newElement($action, -1, $at, $by, $diff);
		}

		return $element;
	}

	/**
	 * @param int|null $revision
	 * @return EntityRevisionInterface
	 */
	public function getEntity(int $revision = null): EntityRevisionInterface
	{
		$revision = $revision ?? count($this->_chain) - 1;
		$entity = $this->_cache ?? new EntityRevision($this, -1, null);
		$rev = $entity->getRevisionId();

		if ($rev == $revision) {
			return $entity;
		}

		while ($rev < $revision) {
			$element = $this->getElement($rev + 1);
			$entity = $this->_cache = $element->stepUp($entity);
			$rev = $entity->getRevisionId();

			$this->notify();
		}

		while ($rev > $revision) {
			$element = $this->getElement($rev);
			$entity = $this->_cache = $element->stepDown($entity);
			$rev = $entity->getRevisionId();

			$this->notify();
		}

		return $entity;
	}

	/**
	 * @param int $revision
	 * @return ChainElementInterface
	 */
	public function getElement(int $revision): ChainElementInterface
	{
		if (!isset($this->_chain[$revision])) {
			throw new \RuntimeException(sprintf('Revision "%1$s" is not exists.', $revision));
		}

		return $this->_chain[$revision];
	}

	/**
	 * @param integer $rev1
	 * @param integer $rev2
	 * @return MergedElement
	 */
	public function getMergedElement(int $rev1, int $rev2): MergedElement
	{
		assert($rev1 < $rev2, sprintf('%1$s %2$s', $rev1, $rev2));

		$a = $this->getElement($rev1);
		$b = $this->getElement($rev2);

		$e1 = $a->stepDown($this->getEntity($rev1));
		$e2 = $this->getEntity($rev2);

		$diff = $this->_type->callCalculateOnChainStrategy('/', $e1->getData(), $e2->getData());

		$upd1 = $a->getChangedAt();
		$upd2 = $b->getChangedAt();
		$changedBy = $a->getChangedBy();

		if ($a->getAction() !== ChainElementInterface::ENTITY_UPDATE) {
			$action = $a->getAction();
		} elseif ($b->getAction() !== ChainElementInterface::ENTITY_UPDATE) {
			$action = $b->getAction();
		} else {
			$action = ChainElementInterface::ENTITY_UPDATE;
		}

		return new MergedElement($action, $rev1, $rev2, $upd1, $upd2, $changedBy, $diff);
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [(string)$this->_type, $this->_id, $this->_chain];
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->_chain) ? $this->_count + 1 : 0;
	}
}