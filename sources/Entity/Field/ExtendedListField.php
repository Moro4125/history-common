<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Entity\Field;

/**
 * Class ExtendedListField
 * @package Moro\History\Common\Entity\Field
 */
class ExtendedListField extends ListField
{
	/** @var string */
	protected $_idField;

	/**
	 * @param string $childIdentifierField
	 */
	public function __construct($childIdentifierField = 'id')
	{
		$this->_idField = $childIdentifierField;
	}

	/**
	 * @param mixed $a
	 * @param mixed $b
	 * @return bool
	 */
	public function correspondsTo($a, $b)
	{
		if (!parent::correspondsTo($a, $b)) {
			return false;
		}

		foreach ([$a, $b] as $c) {
			foreach ($c as $e) {
				if (!isset($e[$this->_idField])) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * @param mixed $a
	 * @param mixed $b
	 * @return bool
	 */
	public function childIsEqual($a, $b)
	{
		return $a[$this->_idField] == $b[$this->_idField];
	}
}