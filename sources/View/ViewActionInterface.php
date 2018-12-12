<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\View;

/**
 * Interface ViewFieldInterface
 * @package Moro\History\Common\View
 */
interface ViewActionInterface
{
	/**
	 * @return string
	 */
	function getHtml(): string;

	/**
	 * @return string
	 */
	function getText(): string;
}