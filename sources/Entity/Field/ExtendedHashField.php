<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Entity\Field;

use Moro\History\Common\Chain\Strategy\ScalarStrategy;

/**
 * Class ExtendedHashField
 * @package Moro\History\Common\Entity\Field
 */
class ExtendedHashField extends HashField
{
	/** @var string */
	protected $_idField;

	/**
	 * @param string $identifierField
	 */
	public function __construct($identifierField = 'id')
	{
		$this->_idField = $identifierField;
	}

	/**
	 * @param array $a
	 * @param array $b
	 * @return array
	 */
	public function calculate($a, $b)
	{
		if (isset($a[$this->_idField]) && isset($b[$this->_idField]) && $a[$this->_idField] == $b[$this->_idField]) {
			return parent::calculate($a, $b);
		}

		return [ScalarStrategy::ID, $a, $b];
	}
}