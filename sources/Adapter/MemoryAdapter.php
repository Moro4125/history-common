<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Adapter;

use Moro\History\Common\Chain\ChainAdapterInterface;
use Moro\History\Common\Chain\ChainElementInterface;
use Moro\History\Common\Log\LogAdapterInterface;
use Moro\History\Common\Type\TypeInterface;

/**
 * Class MemoryAdapter
 * @package Moro\History\Common\Adapter
 */
class MemoryAdapter implements ChainAdapterInterface, LogAdapterInterface
{
	private $_records       = [];
	private $_lastRevisions = [];

	/**
	 * @param TypeInterface $type
	 * @param int $id
	 * @return int|null
	 */
	public function getLastRevisionForId(TypeInterface $type, int $id): ?int
	{
		return $this->_lastRevisions[$id] ?? null;
	}

	/**
	 * @param TypeInterface $type
	 * @param int $id
	 * @param int|null $fromRevision
	 * @param null $toRevision
	 * @return array
	 */
	public function loadElements(TypeInterface $type, int $id, int $fromRevision = null, $toRevision = null): array
	{
		$result = [];

		foreach ($this->_records as $record) {
			list($rType, $rId, $rJson) = $record;

			if ($type == $rType && $id == $rId) {
				list($action, $revision, $changedAt, $changedBy, $diff) = json_decode($rJson, true);

				if ($fromRevision === null || $fromRevision <= $revision) {
					if ($toRevision === null || $revision <= $toRevision) {
						$result[] = [$action, $revision, $changedAt, $changedBy, $diff];
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param TypeInterface $type
	 * @param ChainElementInterface $element
	 * @param int $id
	 */
	public function saveElement(TypeInterface $type, ChainElementInterface $element, int $id)
	{
		$this->_records[] = [(string)$type, $id, json_encode($element)];
		$this->_lastRevisions[$id] = max($this->_lastRevisions[$id] ?? -1, $element->getRevision());
	}

	/**
	 * @param int $from
	 * @param int $limit
	 * @return array
	 */
	public function select(int $from, int $limit): array
	{
		assert($from >= 0);
		assert($limit > 0);

		$records = [];
		$list = array_slice(array_reverse($this->_records), $from, $limit);

		foreach ($list as list($rType, $rId, $rJson)) {
			list($action, $revision, $changedAt, $changedBy,) = json_decode($rJson, true);

			$records[] = [$rType, $rId, $action, $revision, $changedAt, $changedBy];
		}

		return $records;
	}

	/**
	 * @param string $type
	 * @param int $from
	 * @param int $limit
	 * @return array
	 */
	public function selectByType(string $type, int $from, int $limit): array
	{
		assert($from >= 0);
		assert($limit > 0);

		$records = [];
		$offset = 0;

		foreach (array_reverse($this->_records) as list($rType, $rId, $rJson)) {
			if (!$limit) {
				break;
			}

			if ($type == $rType) {
				list($action, $revision, $changedAt, $changedBy,) = json_decode($rJson, true);

				if ($offset >= $from) {
					$records[] = [$rType, $rId, $action, $revision, $changedAt, $changedBy];
					$limit--;
				}

				$offset++;
			}
		}

		return $records;
	}
}