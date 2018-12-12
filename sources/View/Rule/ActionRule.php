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
use Moro\History\Common\View\ViewActionInterface;
use Moro\History\Common\View\ViewRuleInterface;

/**
 * Class ActionRule
 * @package Moro\History\Common\View\Rule
 */
class ActionRule implements ViewRuleInterface
{
	/**
	 * @param ChainElementInterface $a
	 * @param ChainElementInterface $b
	 * @return bool
	 */
	public function canMerged(ChainElementInterface $a, ChainElementInterface $b): bool
	{
		$aAction = $a->getAction();
		$bAction = $b->getAction();
		$cAction = ChainElementInterface::ENTITY_CREATE;
		$dAction = ChainElementInterface::ENTITY_DELETE;
		$uAction = ChainElementInterface::ENTITY_UPDATE;

		if ($aAction !== $uAction && $bAction !== $uAction) {
			return false;
		}

		if ($aAction !== $cAction && $aAction !== $dAction && $aAction !== $uAction) {
			return false;
		}

		if ($bAction !== $cAction && $bAction !== $dAction && $bAction !== $uAction) {
			return false;
		}

		return true;
	}

	/**
	 * @param ChainElementInterface $element
	 * @return ViewActionInterface|null
	 */
	public function getViewField(ChainElementInterface $element): ?ViewActionInterface
	{
		return null;
	}
}