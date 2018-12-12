<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Type;

use Moro\History\Common\Chain\ChainFactoryInterface;
use Moro\History\Common\Chain\ChainStrategyInterface;
use Moro\History\Common\Entity\EntityFactoryInterface;
use Moro\History\Common\Entity\EntityFieldInterface;
use Moro\History\Common\Type\Result\ChainFactoryWithType;
use Moro\History\Common\View\ViewFactoryInterface;
use Moro\History\Common\View\ViewRuleInterface;

/**
 * Interface TypeInterface
 * @package Moro\History\Common\Entity
 */
interface TypeInterface
{
	/**
	 * @return string
	 */
	function __toString();

	/**
	 * @param int $id
	 * @return TypeInterface
	 */
	function setId(int $id): TypeInterface;

	/**
	 * @return int
	 */
	function getId(): int;

	/**
	 * @param string $path
	 * @param EntityFieldInterface $interface
	 * @return TypeInterface
	 */
	function addEntityField(string $path, EntityFieldInterface $interface): TypeInterface;

	/**
	 * @param string $path
	 * @param mixed $a
	 * @param mixed $b
	 * @return EntityFieldInterface
	 *
	 * @throws \RuntimeException
	 */
	function getEntityFieldByPath(string $path, $a, $b): EntityFieldInterface;

	/**
	 * @param ChainStrategyInterface $strategy
	 * @return TypeInterface
	 */
	function addChainStrategy(ChainStrategyInterface $strategy): TypeInterface;

	/**
	 * @param int $id
	 * @return ChainStrategyInterface
	 */
	function getChainStrategyById(int $id): ChainStrategyInterface;

	/**
	 * @param string $path
	 * @param mixed $a
	 * @param mixed $b
	 * @return array
	 */
	function callCalculateOnChainStrategy(string $path, $a, $b): array;

	/**
	 * @param ChainFactoryInterface $factory
	 * @return TypeInterface
	 */
	function setChainFactory(ChainFactoryInterface $factory): TypeInterface;

	/**
	 * @return ChainFactoryWithType
	 */
	function getChainFactory(): ChainFactoryWithType;

	/**
	 * @param EntityFactoryInterface $factory
	 * @return TypeInterface
	 */
	function setEntityFactory(EntityFactoryInterface $factory): TypeInterface;

	/**
	 * @return EntityFactoryInterface
	 */
	function getEntityFactory(): EntityFactoryInterface;

	/**
	 * @param ViewFactoryInterface $factory
	 * @return TypeInterface
	 */
	function setViewFactory(ViewFactoryInterface $factory): TypeInterface;

	/**
	 * @return ViewFactoryInterface
	 */
	function getViewFactory(): ViewFactoryInterface;

	/**
	 * @param ViewRuleInterface $rule
	 * @return TypeInterface
	 */
	function addViewRule(ViewRuleInterface $rule): TypeInterface;

	/**
	 * @return ViewRuleInterface[]
	 */
	function getViewRules(): array;
}