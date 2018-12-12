<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Log\Component;

use Moro\History\Common\Log\LogAdapterInterface;
use Moro\History\Common\Log\LogInterface;
use Moro\History\Common\Log\LogRecordInterface;
use Moro\History\Common\Log\Record\LogRecord;
use Moro\History\Common\Log\Tools\ChainsCache;
use Moro\History\Common\Type\TypeLocator;

/**
 * Class LogComponent
 * @package Moro\History\Common\Log\Component
 */
class LogComponent implements LogInterface
{
	protected $_adapter;
	protected $_locator;

	/**
	 * @param LogAdapterInterface $adapter
	 * @param TypeLocator $locator
	 */
	public function __construct(LogAdapterInterface $adapter, TypeLocator $locator)
	{
		$this->_adapter = $adapter;
		$this->_locator = $locator;
	}

	/**
	 * @param int $from
	 * @param int $limit
	 * @return LogRecordInterface[]
	 */
	public function select(int $from, int $limit): array
	{
		$records = [];
		$cache = ($limit > 1) ? new ChainsCache() : null;

		foreach ($this->_adapter->select($from, $limit) as $record) {
			list($type, $id, $action, $revision, $at, $by) = $record;
			$type = $this->_locator->getType($type);

			$record = new LogRecord($type, $id, $action, $at, $by, $revision);
			$record->setCache($cache);
			$records[] = $record;
		}

		return $records;
	}

	/**
	 * @param string $type
	 * @param int $from
	 * @param int $limit
	 * @return LogRecordInterface[]
	 */
	public function selectByType(string $type, int $from, int $limit): array
	{
		$records = [];
		$cache = ($limit > 1) ? new ChainsCache() : null;
		$type = $this->_locator->getType($type);

		foreach ($this->_adapter->selectByType((string)$type, $from, $limit) as $record) {
			list(, $id, $action, $revision, $at, $by) = $record;

			$record = new LogRecord($type, $id, $action, $at, $by, $revision);
			$record->setCache($cache);
			$records[] = $record;
		}

		return $records;
	}
}