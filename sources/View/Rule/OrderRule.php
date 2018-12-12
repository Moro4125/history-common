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
use Moro\History\Common\Chain\Strategy\ListStrategy;
use Moro\History\Common\View\Action\OrderAction;
use Moro\History\Common\View\ViewActionInterface;

/**
 * Class OrderRule
 * @package Moro\History\Common\View\Rule
 */
class OrderRule extends AbstractRule
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

		$type = $this->getType();
		$factory = $type->getViewFactory();
		$id = reset($diff);
		$isChanged = false;

		if ($id === ListStrategy::ID) {
			foreach ($diff[ListStrategy::LISTS_ORDER] as list($from, $to)) {
				if ($from != $to) {
					$isChanged = true;
					break;
				}
			}
		}

		if ($id === HashStrategy::ID) {
			foreach ($diff[HashStrategy::LISTS_ORDER] as list($from, $to)) {
				if ($from != $to) {
					$isChanged = true;
					break;
				}
			}
		}

		if ($isChanged) {
			$params = [$this->_label, $this->_gender];
			$action = $factory->newViewAction(OrderAction::class, $params);

			return $action;
		}

		return null;
	}
}