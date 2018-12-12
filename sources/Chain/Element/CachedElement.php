<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Chain\Element;

use Moro\History\Common\Accessory\Decorator;
use Moro\History\Common\Chain\ChainElementInterface;
use Moro\History\Common\Entity\EntityRevisionInterface;

/**
 * Class CachedElement
 * @package Moro\History\Common\Chain\Element
 */
class CachedElement implements ChainElementInterface
{
	use Decorator;

	/** @var array */
	protected $_diffCache;

	/**
	 * @param ChainElementInterface $element
	 */
	public function __construct(ChainElementInterface $element)
	{
		$this->_instance = $element;
	}

	/**
	 * @param string $path
	 * @return array|null
	 */
	public function getDiffRecord(string $path): ?array
	{
		if ($this->_diffCache && array_key_exists($path, $this->_diffCache)) {
			return $this->_diffCache[$path];
		}

		$diff = $this->_instance->getDiffRecord($path);
		$this->_diffCache[$path] = $diff;

		return $diff;
	}

	/**
	 * @return int
	 */
	public function getRevision(): int
	{
		return $this->_instance->getRevision();
	}

	/**
	 * @return int
	 */
	public function getRevision2(): int
	{
		return ($this->_instance instanceof MergedElement) ? $this->_instance->getRevision2() : $this->_instance->getRevision();
	}

	/**
	 * @param EntityRevisionInterface $entity
	 * @return EntityRevisionInterface
	 */
	public function stepDown(EntityRevisionInterface $entity): EntityRevisionInterface
	{
		return $this->_instance->stepDown($entity);
	}

	/**
	 * @param EntityRevisionInterface $entity
	 * @return EntityRevisionInterface
	 */
	public function stepUp(EntityRevisionInterface $entity): EntityRevisionInterface
	{
		return $this->_instance->stepUp($entity);
	}

	/**
	 * @return string
	 */
	public function getChangedBy(): string
	{
		return $this->_instance->getChangedBy();
	}

	/**
	 * @return int
	 */
	public function getChangedAt(): int
	{
		return $this->_instance->getChangedAt();
	}

	/**
	 * @return int
	 */
	public function getChangedAt2(): int
	{
		return ($this->_instance instanceof MergedElement) ? $this->_instance->getChangedAt2() : $this->_instance->getChangedAt();
	}

	/**
	 * @return int
	 */
	public function getAction(): int
	{
		return $this->_instance->getAction();
	}

	/**
	 * @return mixed
	 */
	public function jsonSerialize()
	{
		return $this->_instance->jsonSerialize();
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return $this->_instance->count();
	}
}