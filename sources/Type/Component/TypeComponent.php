<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Type\Component;

use Moro\History\Common\Chain\ChainFactoryInterface;
use Moro\History\Common\Chain\ChainStrategyInterface;
use Moro\History\Common\Chain\Strategy\HashStrategy;
use Moro\History\Common\Chain\Strategy\ListStrategy;
use Moro\History\Common\Chain\Strategy\ScalarStrategy;
use Moro\History\Common\Chain\Strategy\TextStrategy;
use Moro\History\Common\Entity\EntityFactoryInterface;
use Moro\History\Common\Entity\EntityFieldInterface;
use Moro\History\Common\Entity\Field\HashField;
use Moro\History\Common\Entity\Field\ListField;
use Moro\History\Common\Entity\Field\ScalarField;
use Moro\History\Common\Entity\Field\TextField;
use Moro\History\Common\Type\Result\ChainFactoryWithType;
use Moro\History\Common\Type\TypeAwareInterface;
use Moro\History\Common\Type\TypeInterface;
use Moro\History\Common\View\ViewFactoryInterface;
use Moro\History\Common\View\ViewRuleInterface;

/**
 * Class TypeComponent
 * @package Moro\History\Common\Entity\Type
 */
class TypeComponent implements TypeInterface
{
	/** @var int */
	protected $_id;

	/** @var string */
	protected $_type;

	/** @var EntityFieldInterface[] */
	protected $_valuesPaths = [];

	/** @var array */
	protected $_valuesPatterns = [];

	/** @var ChainStrategyInterface[] */
	protected $_strategies;

	/** @var ChainFactoryInterface */
	protected $_chainFactory;

	/** @var EntityFactoryInterface */
	protected $_entityFactory;

	/** @var ViewFactoryInterface */
	protected $_viewFactory;

	/** @var ViewRuleInterface[] */
	protected $_viewRules;

	/**
	 * @param string $type
	 */
	public function __construct(string $type)
	{
		$this->_type = $type;

		$this->addEntityField('/*', new HashField());
		$this->addEntityField('/*', new ListField());
		$this->addEntityField('/*', new ScalarField());
		$this->addEntityField('/*', new TextField());

		$this->addChainStrategy(new HashStrategy());
		$this->addChainStrategy(new ListStrategy());
		$this->addChainStrategy(new ScalarStrategy());
		$this->addChainStrategy(new TextStrategy());
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->_type;
	}

	/**
	 * @param int $id
	 * @return TypeInterface
	 */
	public function setId(?int $id): TypeInterface
	{
		$this->_id = $id;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->_id ?? crc32($this->_type);
	}

	/**
	 * @param string $path
	 * @param EntityFieldInterface $interface
	 * @return TypeInterface
	 */
	public function addEntityField(string $path, EntityFieldInterface $interface): TypeInterface
	{
		if ($interface instanceof TypeAwareInterface) {
			$interface->setType($this);
		}

		if (strpos($path, '*')) {
			$chunks = [];

			foreach (explode('*', $path) as $chunk) {
				$chunks[] = preg_quote($chunk, '/');
			}

			$pattern = '/^' . implode('.*', $chunks) . '$/';
			array_unshift($this->_valuesPatterns, [$pattern, $interface]);
		} else {
			$this->_valuesPaths[$path] = $interface;
		}

		return $this;
	}

	/**
	 * @param ChainStrategyInterface $strategy
	 * @return \Moro\History\Common\Type\TypeInterface
	 */
	public function addChainStrategy(ChainStrategyInterface $strategy): TypeInterface
	{
		if ($strategy instanceof TypeAwareInterface) {
			$strategy->setType($this);
		}

		$this->_strategies[$strategy->getStrategyId()] = $strategy;

		return $this;
	}

	/**
	 * @param string $path
	 * @param mixed $a
	 * @param mixed $b
	 * @return EntityFieldInterface
	 *
	 * @throws \RuntimeException
	 */
	public function getEntityFieldByPath(string $path, $a, $b): EntityFieldInterface
	{
		$flag = true;

		if (isset($this->_valuesPaths[$path]) && $flag = $this->_valuesPaths[$path]->correspondsTo($a, $b)) {
			return $this->_valuesPaths[$path];
		}

		foreach ($this->_valuesPatterns as list($pattern, $interface)) {
			if (preg_match($pattern, $path) && $flag = $interface->correspondsTo($a, $b)) {
				return $interface;
			}
		}

		$message = $flag ? 'Wrong path "%1$s".' : 'Wrong value at path "%1$s".';
		throw new \RuntimeException(sprintf($message, $path));
	}

	/**
	 * @param int $id
	 * @return ChainStrategyInterface
	 */
	public function getChainStrategyById(int $id): ChainStrategyInterface
	{
		return $this->_strategies[$id];
	}

	/**
	 * @param string $path
	 * @param mixed $a
	 * @param mixed $b
	 * @return array
	 */
	public function callCalculateOnChainStrategy(string $path, $a, $b): array
	{
		$field = $this->getEntityFieldByPath($path, $a, $b);
		$action = $field->calculate($a, $b);

		if (!$strategyId = reset($action)) {
			return [];
		}

		if (!isset($this->_strategies[$strategyId])) {
			throw new \RuntimeException(sprintf('Wrong diff action: %1$s', json_encode($action)));
		}

		return $this->_strategies[$strategyId]->calculate($this, $path, $action, $a, $b);
	}

	/**
	 * @param ChainFactoryInterface $factory
	 * @return TypeInterface
	 */
	public function setChainFactory(ChainFactoryInterface $factory): TypeInterface
	{
		if ($factory instanceof TypeAwareInterface) {
			$factory->setType($this);
		}

		$this->_chainFactory = $factory;

		return $this;
	}

	/**
	 * @return ChainFactoryWithType
	 */
	public function getChainFactory(): ChainFactoryWithType
	{
		return new ChainFactoryWithType($this, $this->_chainFactory);
	}

	/**
	 * @param EntityFactoryInterface $factory
	 * @return TypeInterface
	 */
	public function setEntityFactory(EntityFactoryInterface $factory): TypeInterface
	{
		if ($factory instanceof TypeAwareInterface) {
			$factory->setType($this);
		}

		$this->_entityFactory = $factory;

		return $this;
	}

	/**
	 * @return EntityFactoryInterface
	 */
	public function getEntityFactory(): EntityFactoryInterface
	{
		return $this->_entityFactory;
	}

	/**
	 * @param ViewFactoryInterface $factory
	 * @return TypeInterface
	 */
	public function setViewFactory(ViewFactoryInterface $factory): TypeInterface
	{
		if ($factory instanceof TypeAwareInterface) {
			$factory->setType($this);
		}

		$this->_viewFactory = $factory;

		return $this;
	}

	/**
	 * @return ViewFactoryInterface
	 */
	public function getViewFactory(): ViewFactoryInterface
	{
		return $this->_viewFactory;
	}

	/**
	 * @param ViewRuleInterface $rule
	 * @return TypeInterface
	 */
	public function addViewRule(ViewRuleInterface $rule): TypeInterface
	{
		if ($rule instanceof TypeAwareInterface) {
			$rule->setType($this);
		}

		$this->_viewRules[] = $rule;

		return $this;
	}

	/**
	 * @return ViewRuleInterface[]
	 */
	public function getViewRules(): array
	{
		return $this->_viewRules ?? [];
	}
}