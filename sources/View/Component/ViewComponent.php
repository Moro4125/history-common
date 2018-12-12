<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\View\Component;

use Moro\History\Common\Chain\ChainElementInterface;
use Moro\History\Common\Chain\ChainInterface;
use Moro\History\Common\Chain\Element\CachedElement;
use Moro\History\Common\View\ViewInterface;
use Moro\History\Common\View\ViewRecordInterface;

/**
 * Class ViewComponent
 * @package Moro\History\Common\View\Component
 */
class ViewComponent implements ViewInterface
{
	/** @var ChainInterface */
	protected $_chain;

	/**
	 * @param ChainInterface $chain
	 */
	public function __construct(ChainInterface $chain)
	{
		$this->_chain = $chain;
	}

	/**
	 * @param int $from
	 * @param int $limit
	 * @param null|bool $onlyUpdates
	 * @return ViewRecordInterface[]
	 */
	public function slice(int $from, int $limit, bool $onlyUpdates = null): array
	{
		$type = $this->_chain->getType();
		$factory = $type->getViewFactory();
		$rules = $type->getViewRules();

		$list = [];
		$topRevision = $this->_chain->count();

		for ($revision = $topRevision - $from; $revision && $limit; $limit--) {
			/** @var CachedElement $element */
			$element = null;
			$revStart = null;

			while ($revision) {
				$revision--;
				$e = $e ?? new CachedElement($this->_chain->getElement($revision));

				if ($element === null) {
					$revStart = $revision;
					$element = $e;
					unset($e);
					continue;
				}

				if ($onlyUpdates && $element->getAction() !== $e->getAction()) {
					$revision++;
					break;
				}

				foreach ($rules as $rule) {
					if (!$rule->canMerged($element, $e)) {
						$revision++;
						break 2;
					}
				}

				$element = $e;
				unset($e);
			}

			if ($onlyUpdates && $element->getAction() !== ChainElementInterface::ENTITY_UPDATE) {
				continue;
			}

			if ($revStart !== $element->getRevision()) {
				$element = $this->_chain->getMergedElement($element->getRevision(), $revStart);
			}

			$startedAt = $element->getChangedAt();
			$updatedAt = $element->getChangedAt2();
			$updatedBy = $element->getChangedBy();
			$fields = [];

			foreach ($rules as $rule) {
				if ($field = $rule->getViewField($element)) {
					$fields[] = $field;
				}
			}

			if ($fields) {
				$record = $factory->newViewRecord($startedAt, $updatedAt, $updatedBy, $fields);
				$list[] = $record;
			}
		}

		return $list;
	}

	/**
	 * @return \Closure|\Traversable
	 */
	public function getIterator()
	{
		$generator = function () {
			$position = 0;
			$limit = 100;

			while (true) {
				$cache = $this->slice($position, $limit, true);

				foreach ($cache as $item) {
					yield $item;
				}

				$count = count($cache);

				if ($count < $limit) {
					break;
				}

				$position += $count;
			}
		};

		return $generator();
	}
}