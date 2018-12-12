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
use Moro\History\Common\Chain\Strategy\ScalarStrategy;
use Moro\History\Common\View\Action\DelValueAction;
use Moro\History\Common\View\Action\PopItemAction;
use Moro\History\Common\View\Action\PushItemAction;
use Moro\History\Common\View\Action\SetValueAction;
use Moro\History\Common\View\Action\UpdateValueAction;
use Moro\History\Common\View\ViewActionInterface;

/**
 * Class ScalarRule
 * @package Moro\History\Common\View\Rule
 */
class ScalarRule extends AbstractRule
{
	/**
	 * @param ChainElementInterface $element
	 * @return ViewActionInterface|null
	 */
	public function getViewField(ChainElementInterface $element): ?ViewActionInterface
	{
		if (!$diff = $element->getDiffRecord($this->_path)) {
			return null;
		}

		$code = reset($diff);

		if ($code === ChainStrategyInterface::ADD) {
			$value = $this->_escapeValue($diff[ChainStrategyInterface::VALUE]);

			return $this->_setValue($this->_label, $value, $this->_gender);
		}

		if ($code === ScalarStrategy::ID) {
			switch ($element->getAction()) {
				case ChainElementInterface::ENTITY_CREATE:
					$newValue = $this->_escapeValue($diff[ScalarStrategy::NEW_VALUE]);

					return $this->_setValue($this->_label, $newValue, $this->_gender);

				case ChainElementInterface::ENTITY_DELETE:
					$oldValue = $this->_escapeValue($diff[ScalarStrategy::OLD_VALUE]);

					return $this->_delValue($this->_label, $oldValue, $this->_gender);
			}

			$oldValue = $this->_escapeValue($diff[ScalarStrategy::OLD_VALUE]);
			$newValue = $this->_escapeValue($diff[ScalarStrategy::NEW_VALUE]);

			return $this->_updateValue($this->_label, $oldValue, $newValue, $this->_gender);
		}

		if ($code === ChainStrategyInterface::DELETE) {
			$value = $this->_escapeValue($diff[ChainStrategyInterface::VALUE]);

			return $this->_delValue($this->_label, $value, $this->_gender);
		}

		return null;
	}

	/**
	 * @param mixed $value
	 * @return string|ViewActionInterface
	 */
	protected function _escapeValue($value)
	{
		return json_encode($value, JSON_UNESCAPED_UNICODE);
	}

	/**
	 * @param string $label
	 * @param mixed $value
	 * @param int $gender
	 * @return ViewActionInterface
	 */
	protected function _setValue($label, $value, $gender)
	{
		$type = $this->getType();
		$factory = $type->getViewFactory();
		$params = [$label, $value, $gender];
		$class = $this->_label ? SetValueAction::class : PushItemAction::class;

		return $factory->newViewAction($class, $params);
	}

	/**
	 * @param string $label
	 * @param mixed $value
	 * @param int $gender
	 * @return ViewActionInterface
	 */
	protected function _delValue($label, $value, $gender)
	{
		$type = $this->getType();
		$factory = $type->getViewFactory();
		$params = [$label, $value, $gender];
		$class = $this->_label ? DelValueAction::class : PopItemAction::class;

		return $factory->newViewAction($class, $params);
	}

	/**
	 * @param string $label
	 * @param mixed $oldValue
	 * @param mixed $newValue
	 * @param int $gender
	 * @return ViewActionInterface
	 */
	protected function _updateValue($label, $oldValue, $newValue, $gender)
	{
		$type = $this->getType();
		$factory = $type->getViewFactory();
		$params = [$label, $oldValue, $newValue, $gender];

		return $factory->newViewAction(UpdateValueAction::class, $params);
	}
}