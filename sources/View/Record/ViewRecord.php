<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\View\Record;

use Moro\History\Common\View\ViewActionInterface;
use Moro\History\Common\View\ViewRecordInterface;

/**
 * Class ViewRecord
 * @package Moro\History\Common\View\Record
 */
class ViewRecord implements ViewRecordInterface
{
	/** @var int */
	protected $_startedAt;

	/** @var int */
	protected $_updatedAt;

	/** @var string */
	protected $_updatedBy;

	/** @var ViewActionInterface[] */
	protected $_fields;

	/**
	 * @param int $startedAt
	 * @param int $updatedAt
	 * @param string $updatedBy
	 * @param ViewActionInterface[] $fields
	 */
	public function __construct(int $startedAt, int $updatedAt, string $updatedBy, array $fields)
	{
		$this->_startedAt = $startedAt;
		$this->_updatedAt = $updatedAt;
		$this->_updatedBy = $updatedBy;
		$this->_fields = $fields;
	}

	/**
	 * @return int
	 */
	public function getStartedAt(): int
	{
		return $this->_startedAt;
	}

	/**
	 * @return int
	 */
	public function getUpdatedAt(): int
	{
		return $this->_updatedAt;
	}

	/**
	 * @return string
	 */
	public function getUpdatedBy(): string
	{
		return $this->_updatedBy;
	}

	/**
	 * @return array
	 */
	public function getChangedFields(): array
	{
		return $this->_fields;
	}
}