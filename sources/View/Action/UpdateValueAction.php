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
 * Class UpdateValueField
 * @package Moro\History\Common\View\Field
 */
class UpdateValueAction extends AbstractAction implements ViewActionInterface
{
	private $_label;
	private $_oldValue;
	private $_newValue;
	private $_gender;

	/**
	 * @param TranslatorInterface $translator
	 * @param string $label
	 * @param string $oldValue
	 * @param string $newValue
	 * @param int|null $gender
	 */
	public function __construct(
		TranslatorInterface $translator,
		string $label,
		string $oldValue,
		string $newValue,
		?int $gender
	) {
		parent::__construct($translator);

		$this->_label = $this->_trans($label);
		$this->_oldValue = $oldValue;
		$this->_newValue = $newValue;
		$this->_gender = $gender;
	}

	/**
	 * @return string
	 */
	public function getHtml(): string
	{
		$params = [
			'%label%'     => htmlspecialchars($this->_label),
			'%old_value%' => htmlspecialchars($this->_oldValue),
			'%new_value%' => htmlspecialchars($this->_newValue),
		];

		return $this->_trans('update_value.html', $params, $this->_gender);
	}

	/**
	 * @return string
	 */
	public function getText(): string
	{
		$params = [
			'%label%'     => $this->_label,
			'%old_value%' => $this->_oldValue,
			'%new_value%' => $this->_newValue,
		];

		return $this->_trans('update_value.text', $params, $this->_gender);
	}
}