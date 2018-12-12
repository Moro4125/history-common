<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\View\Action;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Moro\History\Common\View\ViewActionInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class MultipleAction
 * @package Moro\History\Common\View\Action
 */
class MultipleAction extends AbstractAction implements ViewActionInterface, IteratorAggregate, Countable
{
	/** @var ViewActionInterface[] */
	private $_collection;
	private $_textPrefix;
	private $_htmlPrefix;
	private $_textGlue;
	private $_htmlGlue;
	private $_textSuffix;
	private $_htmlSuffix;
	private $_label;
	private $_gender;

	/**
	 * MultipleAction constructor.
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
	 * @param string $textPrefix
	 * @param string|null $htmlPrefix
	 */
	public function setPrefix(string $textPrefix, string $htmlPrefix = null)
	{
		$this->_textPrefix = $textPrefix;
		$this->_htmlPrefix = $htmlPrefix;
	}

	/**
	 * @param string $textGlue
	 * @param string|null $htmlGlue
	 */
	public function setGlue(string $textGlue, string $htmlGlue = null)
	{
		$this->_textGlue = $textGlue;
		$this->_htmlGlue = $htmlGlue;
	}

	/**
	 * @param string $textSuffix
	 * @param string|null $htmlSuffix
	 */
	public function setSuffix(string $textSuffix, string $htmlSuffix = null)
	{
		$this->_textSuffix = $textSuffix;
		$this->_htmlSuffix = $htmlSuffix;
	}

	/**
	 * @param ViewActionInterface $action
	 */
	public function addAction(ViewActionInterface $action)
	{
		$this->_collection[] = $action;
	}

	/**
	 * @return string
	 */
	public function getText(): string
	{
		$params = [
			'%label%' => $this->_label,
		];

		$result = $this->_trans((string)$this->_textPrefix, $params, $this->_gender);
		$pieces = [];

		foreach ($this->_collection ?? [] as $action) {
			$pieces[] = $action->getText();
		}

		$result .= implode($this->_trans((string)$this->_textGlue), $pieces);
		$result .= $this->_trans((string)$this->_textSuffix, $params, $this->_gender);

		return $result;
	}

	/**
	 * @return string
	 */
	public function getHtml(): string
	{
		$params = [
			'%label%' => htmlspecialchars($this->_label),
		];

		$result = $this->_trans((string)($this->_htmlPrefix ?? $this->_textPrefix), $params, $this->_gender);
		$pieces = [];

		foreach ($this->_collection ?? [] as $action) {
			$pieces[] = $action->getHtml();
		}

		$result .= implode((string)($this->_htmlGlue ?? $this->_textGlue), $pieces);
		$result .= $this->_trans(($this->_htmlSuffix ?? $this->_textSuffix), $params, $this->_gender);

		return $result;
	}

	/**
	 * @return ArrayIterator|\Traversable
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->_collection ?? []);
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->_collection ?? []);
	}
}