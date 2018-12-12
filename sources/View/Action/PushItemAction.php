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
 * Class PushItemAction
 * @package Moro\History\Common\View\Field
 */
class PushItemAction extends AbstractAction implements ViewActionInterface
{
	private $_label;
	private $_value;
	private $_gender;

	/**
	 * @param TranslatorInterface $translator
	 * @param string $label
	 * @param string|ViewActionInterface $value
	 * @param int|null $gender
	 */
	public function __construct(TranslatorInterface $translator, string $label, $value, ?int $gender = null)
	{
		parent::__construct($translator);

		$this->_label = $this->_trans($label);
		$this->_value = $value;
		$this->_gender = $gender;
	}

	/**
	 * @return string
	 */
	public function getHtml(): string
	{
		$value = $this->_value;

		$params = [
			'%label%' => htmlspecialchars($this->_label),
			'%value%' => $value instanceof ViewActionInterface ? $value->getHtml() : htmlspecialchars($value),
		];

		return $this->_trans('push_item.html', $params, $this->_gender);
	}

	/**
	 * @return string
	 */
	public function getText(): string
	{
		$value = $this->_value;

		$params = [
			'%label%' => $this->_label,
			'%value%' => $value instanceof ViewActionInterface ? $value->getText() : $this->_value,
		];

		return $this->_trans('push_item.text', $params, $this->_gender);
	}
}