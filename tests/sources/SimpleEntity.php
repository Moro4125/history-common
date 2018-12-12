<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Test;

use Moro\History\Common\Entity\EntityInterface;
use Moro\History\Common\Type\TypeInterface;

/**
 * Class SimpleEntity
 * @package Moro\History\Test
 */
class SimpleEntity implements EntityInterface
{
	private $_type;
	private $_id;
	private $_data;

	public function __construct(string $type, int $id, $data)
	{
		$this->_type = $type;
		$this->_id = $id;
		$this->_data = $data;
	}

	public function getType(): string
	{
		return (string)$this->_type;
	}

	public function getId(): int
	{
		return $this->_id;
	}

	public function getData()
	{
		return $this->_data;
	}
}