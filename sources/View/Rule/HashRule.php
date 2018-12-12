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
use Moro\History\Common\Chain\Strategy\HashStrategy;
use Moro\History\Common\Type\TypeInterface;
use Moro\History\Common\View\Action\MultipleAction;
use Moro\History\Common\View\ViewActionInterface;

/**
 * Class HashRule
 * @package Moro\History\Common\View\Rule
 */
class HashRule extends AbstractRule
{
	/** @var AbstractRule[] */
	protected $_rules;

	/**
	 * @param TypeInterface $type
	 */
	public function setType(TypeInterface $type)
	{
		parent::setType($type);

		if ($this->_rules) {
			foreach ($this->_rules as $rule) {
				$rule->setType($type);
			}
		}
	}

	/**
	 * @param AbstractRule $rule
	 */
	public function addRule(AbstractRule $rule)
	{
		$this->_rules[] = $rule;

		if ($type = $this->getType()) {
			$rule->setType($type);
		}
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

		if ($aCode !== HashStrategy::ID && $bCode !== HashStrategy::ID) {
			return false;
		}

		if ($aCode === HashStrategy::ID && $bCode === HashStrategy::ID && $this->_rules) {
			foreach ($this->_rules as $rule) {
				$rule = clone $rule;
				$rule->setPath(rtrim($this->_path, '/') . '/' . $rule->getPath());

				if (!$rule->canMerged($a, $b)) {
					return false;
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
		$list->setPrefix('hash.prefix.text', 'hash.prefix.html');
		$list->setGlue('hash.glue.text', 'hash.glue.html');
		$list->setSuffix('hash.suffix.text', 'hash.suffix.html');

		if ($this->_rules) {
			foreach ($this->_rules as $rule) {
				$rule = clone $rule;
				$rule->setPath(rtrim($this->_path, '/') . '/' . $rule->getPath());

				if ($action = $rule->getViewField($element)) {
					$list->addAction($action);
				}
			}
		}

		if (!$list->count()) {
			return null;
		}

		return $list;
	}
}