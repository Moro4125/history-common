<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\View\Action;

use Moro\History\Common\View\ViewActionInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class OrderAction
 * @package Moro\History\Common\View\Field
 */
class OrderAction extends AbstractAction implements ViewActionInterface
{
	private $_label;
	private $_gender;

	/**
	 * @param TranslatorInterface $translator
	 * @param string $label
	 * @param int|null $gender
	 */
	public function __construct(TranslatorInterface $translator, string $label, ?int $gender)
	{
		parent::__construct($translator);

		$this->_label = $this->_trans($label);
		$this->_gender = $gender;
	}

	/**
	 * @return string
	 */
	public function getHtml(): string
	{
		$params = [
			'%label%' => htmlspecialchars($this->_label),
		];

		return $this->_trans('order_is_changed.html', $params, $this->_gender);
	}

	/**
	 * @return string
	 */
	public function getText(): string
	{
		$params = [
			'%label%' => $this->_label,
		];

		return $this->_trans('order_is_changed.text', $params, $this->_gender);
	}
}