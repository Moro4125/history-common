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
use Moro\History\Common\Type\TypeAwareInterface;
use Moro\History\Common\Type\TypeInterface;
use Moro\History\Common\View\ViewActionInterface;
use Moro\History\Common\View\ViewRuleInterface;
use Moro\History\Common\Chain\ChainStrategyInterface;

/**
 * Class AbstractRule
 * @package Moro\History\Common\View\Rule
 */
abstract class AbstractRule implements ViewRuleInterface, TypeAwareInterface
{
	/** @var TypeInterface */
	private $_type;

	/** @var string */
	protected $_label;

	/** @var int */
	protected $_gender;

	/** @var string */
	protected $_path;

	/**
	 * @param string|null $label
	 * @param string|null $path
	 * @param int $gender
	 */
	public function __construct(string $label = null, string $path = null, int $gender = null)
	{
		$this->_label = (string)$label;
		$this->_path = (string)$path;
		$this->_gender = $gender;
	}

	/**
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->_path;
	}

	/**
	 * @param string $path
	 */
	public function setPath(string $path)
	{
		$this->_path = $path;
	}

	/**
	 * @return TypeInterface|null
	 */
	public function getType(): ?TypeInterface
	{
		return $this->_type;
	}

	/**
	 * @param TypeInterface $type
	 */
	public function setType(TypeInterface $type)
	{
		$this->_type = $type;
	}

	/**
	 * @param ChainElementInterface $a
	 * @param ChainElementInterface $b
	 * @return bool
	 */
	public function canMerged(ChainElementInterface $a, ChainElementInterface $b): bool
	{
		$aDiff = $a->getDiffRecord($this->_path);
		$bDiff = $b->getDiffRecord($this->_path);

		if (is_null($aDiff) || is_null($bDiff)) {
			return true;
		}

		$aCode = reset($aDiff);
		$bCode = reset($bDiff);

		if ($aCode == ChainStrategyInterface::ADD || $bCode == ChainStrategyInterface::ADD) {
			return $bCode != ChainStrategyInterface::ADD;
		}

		if ($aCode == ChainStrategyInterface::DELETE || $bCode == ChainStrategyInterface::DELETE) {
			return $aCode != ChainStrategyInterface::DELETE;
		}

		if ($aCode != $bCode) {
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