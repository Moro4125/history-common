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
 * Class AuthorRule
 * @package Moro\History\Common\View\Rule
 */
class AuthorRule implements ViewRuleInterface
{
	/** @var int */
	protected $_session;

	/**
	 * @param int $session
	 */
	public function __construct(int $session)
	{
		$this->_session = $session;
	}

	/**
	 * @param ChainElementInterface $a
	 * @param ChainElementInterface $b
	 * @return bool
	 */
	public function canMerged(ChainElementInterface $a, ChainElementInterface $b): bool
	{
		if ($a->getChangedBy() != $b->getChangedBy()) {
			return false;
		}

		if (abs($a->getChangedAt() - $b->getChangedAt()) > $this->_session) {
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