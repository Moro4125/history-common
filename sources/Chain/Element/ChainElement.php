<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Chain\Element;

use Moro\History\Common\Chain\ChainElementInterface;
use Moro\History\Common\Chain\ChainStrategyInterface;
use Moro\History\Common\Chain\Strategy\ScalarStrategy;
use Moro\History\Common\Entity\EntityRevisionInterface;

/**
 * Class ChainElement
 * @package Moro\History\Common\Chain\Element
 */
class ChainElement implements ChainElementInterface
{
	/** @var int */
	protected $_action;

	/** @var int */
	protected $_revision;

	/** @var int */
	protected $_changedAt;

	/** @var string */
	protected $_changedBy;

	/** @var array */
	protected $_diff;

	/**
	 * @param int $action
	 * @param int $revision
	 * @param int $changedAt
	 * @param string $changedBy
	 * @param array $diff
	 */
	public function __construct(int $action, int $revision, int $changedAt, string $changedBy, array $diff)
	{
		$this->_action = $action;
		$this->_revision = $revision;
		$this->_changedAt = $changedAt;
		$this->_changedBy = $changedBy;
		$this->_diff = $diff;
	}

	/**
	 * @return int
	 */
	public function getAction(): int
	{
		return $this->_action;
	}

	/**
	 * @return int
	 */
	public function getRevision(): int
	{
		return $this->_revision;
	}

	/**
	 * @return int
	 */
	public function getChangedAt(): int
	{
		return $this->_changedAt;
	}

	/**
	 * @return string
	 */
	public function getChangedBy(): string
	{
		return $this->_changedBy;
	}

	/**
	 * @param EntityRevisionInterface $entity
	 * @return EntityRevisionInterface
	 */
	public function stepUp(EntityRevisionInterface $entity): EntityRevisionInterface
	{
		$type = $entity->getTypeComponent();
		$data = $entity->getData();

		$strategy = $type->getChainStrategyById(reset($this->_diff['/']));
		$strategy->stepUp($type, $this->_diff, '/', $data);

		$factory = $type->getEntityFactory();
		$revision = $factory->newRevision($entity->getChain(), $this->_revision, $data);

		return $revision;
	}

	/**
	 * @param EntityRevisionInterface $entity
	 * @return EntityRevisionInterface
	 */
	public function stepDown(EntityRevisionInterface $entity): EntityRevisionInterface
	{
		$type = $entity->getTypeComponent();
		$data = $entity->getData();

		$strategy = $type->getChainStrategyById(reset($this->_diff['/']));
		$strategy->stepDown($type, $this->_diff, '/', $data);

		$factory = $type->getEntityFactory();
		$revision = $factory->newRevision($entity->getChain(), $this->_revision - 1, $data);

		return $revision;
	}

	/**
	 * @param string $path
	 * @return array|null
	 */
	public function getDiffRecord(string $path): ?array
	{
		if (!empty($this->_diff[$path])) {
			return $this->_diff[$path];
		}

		if (false === $pos = strrpos($path, '/')) {
			return null;
		}

		$prefix = $pos ? substr($path, 0, $pos) : '/';
		// $key = substr($prefix, strrpos($prefix, '/') + 1);

		if ($prefix === $path || !$diff = $this->getDiffRecord($prefix)) { // is_numeric($key) ||
			return null;
		}

		$code = reset($diff);

		if ($code === ChainStrategyInterface::ADD || $code === ChainStrategyInterface::DELETE) {
			$suffix = substr($path, $pos + 1);

			if (is_array($diff[ChainStrategyInterface::VALUE])) { // !is_numeric($suffix) &&
				if (array_key_exists($suffix, $diff[ChainStrategyInterface::VALUE])) {
					return [$code, $diff[ChainStrategyInterface::VALUE][$suffix]];
				}
			}

			return null;
		}

		if ($code !== ScalarStrategy::ID) {
			return null;
		}

		$suffix = substr($path, $pos + 1);

		//		if (is_numeric($suffix)) {
		//			return null;
		//		}

		if (isset($diff[ScalarStrategy::OLD_VALUE][$suffix]) && !isset($diff[ScalarStrategy::NEW_VALUE][$suffix])) {
			return [
				ChainStrategyInterface::DELETE,
				$diff[ScalarStrategy::OLD_VALUE][$suffix],
			];
		}

		if (!isset($diff[ScalarStrategy::OLD_VALUE][$suffix]) && isset($diff[ScalarStrategy::NEW_VALUE][$suffix])) {
			return [
				ChainStrategyInterface::ADD,
				$diff[ScalarStrategy::NEW_VALUE][$suffix],
			];
		}

		if (isset($diff[ScalarStrategy::OLD_VALUE][$suffix]) && isset($diff[ScalarStrategy::NEW_VALUE][$suffix])) {
			return [
				ScalarStrategy::ID,
				$diff[ScalarStrategy::OLD_VALUE][$suffix],
				$diff[ScalarStrategy::NEW_VALUE][$suffix],
			];
		}

		return null;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [$this->_action, $this->_revision, $this->_changedAt, $this->_changedBy, $this->_diff];
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->_diff);
	}
}