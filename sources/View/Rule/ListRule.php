<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\View\Rule;

use Moro\History\Common\Chain\ChainElementInterface;
use Moro\History\Common\Chain\ChainStrategyInterface;
use Moro\History\Common\Chain\Strategy\ListStrategy;
use Moro\History\Common\Type\TypeInterface;
use Moro\History\Common\View\Action\MultipleAction;
use Moro\History\Common\View\ViewActionInterface;

/**
 * Class ListRule
 * @package Moro\History\Common\View\Rule
 */
class ListRule extends AbstractRule
{
	/** @var AbstractRule */
	protected $_rule;

	/**
	 * @param string $label
	 * @param string $path
	 * @param AbstractRule $itemRule
	 * @param int|null $gender
	 */
	public function __construct(string $label, string $path, AbstractRule $itemRule, int $gender = null)
	{
		parent::__construct($label, $path, $gender);

		$this->_rule = $itemRule;
	}

	/**
	 * @param TypeInterface $type
	 */
	public function setType(TypeInterface $type)
	{
		parent::setType($type);
		$this->_rule->setType($type);
	}

	/**
	 * @param ChainElementInterface $a
	 * @param ChainElementInterface $b
	 * @return bool
	 */
	public function canMerged(ChainElementInterface $a, ChainElementInterface $b): bool
	{
		if (!parent::canMerged($a, $b)) {
			return false;
		}

		$aDiff = $a->getDiffRecord($this->_path);
		$bDiff = $b->getDiffRecord($this->_path);

		$aCode = reset($aDiff);
		$bCode = reset($bDiff);

		if ($aCode !== ListStrategy::ID && $bCode !== ListStrategy::ID) {
			return false;
		}

		if ($aCode === ListStrategy::ID && $bCode === ListStrategy::ID) {
			$map = [];

			foreach ($bDiff[ListStrategy::LISTS_ORDER] as list($from, $to)) {
				$map[$from] = $to;
			}

			foreach ($aDiff[ListStrategy::UPDATE_LIST] as $aKey) {
				if (isset($map[$aKey])) {
					return false;
				}

				if (isset($bDiff[ListStrategy::UPDATE_LIST][$aKey])) {
					$this->_rule->setPath($this->_path . '/' . $aKey);

					if (!$this->_rule->canMerged($a, $b)) {
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * @param ChainElementInterface $element
	 * @return ViewActionInterface|null
	 */
	public function getViewField(ChainElementInterface $element): ?ViewActionInterface
	{
		if (!$diff = $element->getDiffRecord($this->_path)) {
			return null;
		}

		$type = $this->getType();
		$factory = $type->getViewFactory();
		$id = reset($diff);

		/** @var MultipleAction $list */
		$list = $factory->newViewAction(MultipleAction::class, [$this->_label, $this->_gender]);
		$list->setPrefix('list.prefix.text', 'list.prefix.html');
		$list->setGlue('list.glue.text', 'list.glue.html');
		$list->setSuffix('list.suffix.text', 'list.suffix.html');

		switch ($id) {
			case ChainStrategyInterface::ADD:
			case ChainStrategyInterface::DELETE:
				if (!is_array($diff[ChainStrategyInterface::VALUE])) {
					return null;
				}

				$keys = array_keys($diff[ChainStrategyInterface::VALUE]);
				break;

			case ListStrategy::ID:
				$keys = [];

				foreach ($diff[ListStrategy::DELETE_LIST] as $index) {
					$keys[] = ($index ^ -1);
				}

				$keys = array_merge($keys, $diff[ListStrategy::UPDATE_LIST], $diff[ListStrategy::CREATE_LIST]);
				break;

			default:
				return null;
		}

		foreach ($keys as $key) {
			$this->_rule->setPath(rtrim($this->_path, '/') . '/' . $key);

			if ($action = $this->_rule->getViewField($element)) {
				$list->addAction($action);
			}
		}

		if (!$list->count()) {
			return null;
		}

		return $list;
	}
}