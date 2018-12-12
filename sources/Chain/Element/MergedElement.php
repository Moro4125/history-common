<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Chain\Element;

use Moro\History\Common\Entity\EntityRevisionInterface;

/**
 * Class MergedElement
 * @package Moro\History\Common\Chain\Element
 */
class MergedElement extends ChainElement
{
	/** @var int */
	protected $_rev2;

	/** @var int */
	protected $_upd2;

	/**
	 * @param int $action
	 * @param int $rev1
	 * @param int $rev2
	 * @param int $upd1
	 * @param int $upd2
	 * @param string $by
	 * @param array $diff
	 */
	public function __construct(int $action, int $rev1, int $rev2, int $upd1, int $upd2, string $by, array $diff)
	{
		assert($rev1 < $rev2);
		assert($upd1 <= $upd2);

		$this->_rev2 = $rev2;
		$this->_upd2 = $upd2;

		parent::__construct($action, $rev1, $upd1, $by, $diff);
	}

	/**
	 * @return int
	 */
	public function getRevision2(): int
	{
		return $this->_rev2;
	}

	/**
	 * @return int
	 */
	public function getChangedAt2(): int
	{
		return $this->_upd2;
	}

	/**
	 * @param EntityRevisionInterface $entity
	 * @return EntityRevisionInterface
	 */
	public function stepUp(EntityRevisionInterface $entity): EntityRevisionInterface
	{
		$rev = $this->_revision;

		try {
			$this->_revision = $this->_rev2;
			$revision = parent::stepUp($entity);
		}
		finally {
			$this->_revision = $rev;
		}

		return $revision;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [
			$this->_action,
			$this->_revision,
			$this->_rev2,
			$this->_changedAt,
			$this->_upd2,
			$this->_changedBy,
			$this->_diff
		];
	}
}