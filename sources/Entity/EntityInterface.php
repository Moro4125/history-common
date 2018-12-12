<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Entity;

/**
 * Interface EntityInterface
 * @package Moro\History\Common\Entity
 */
interface EntityInterface
{
	/**
	 * @return int
	 */
	function getId(): int;

	/**
	 * @return string
	 */
	function getType(): string;

	/**
	 * @return mixed
	 */
	function getData();
}