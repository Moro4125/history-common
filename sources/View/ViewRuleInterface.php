<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\View;

use Moro\History\Common\Chain\ChainElementInterface;

/**
 * Interface ViewRuleInterface
 * @package Moro\History\Common\View
 */
interface ViewRuleInterface
{
	/**
	 * @param ChainElementInterface $a
	 * @param ChainElementInterface $b
	 * @return bool
	 */
	function canMerged(ChainElementInterface $a, ChainElementInterface $b): bool;

	/**
	 * @param ChainElementInterface $element
	 * @return ViewActionInterface|null
	 */
	function getViewField(ChainElementInterface $element): ?ViewActionInterface;
}