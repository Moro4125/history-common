<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\View\Factory;

use Moro\History\Common\Chain\ChainInterface;
use Moro\History\Common\View\Action\AbstractAction;
use Moro\History\Common\View\Component\ViewComponent;
use Moro\History\Common\View\Record\ViewRecord;
use Moro\History\Common\View\ViewActionInterface;
use Moro\History\Common\View\ViewFactoryInterface;
use Moro\History\Common\View\ViewInterface;
use Moro\History\Common\View\ViewRecordInterface;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ViewClassFactory
 * @package Moro\History\Common\View\Factory
 */
class ViewClassFactory implements ViewFactoryInterface
{
	/** @var string */
	protected $_recordClass = ViewRecord::class;

	/** @var string */
	protected $_viewClass = ViewComponent::class;

	/** @var null|TranslatorInterface */
	protected $_translator;

	/** @var string|null */
	protected $_locale;

	/**
	 * @param TranslatorInterface|null $translator
	 * @param string|null $locale
	 */
	public function __construct(TranslatorInterface $translator = null, string $locale = null)
	{
		$this->_translator = $translator;
		$this->_locale = $locale;
	}

	/**
	 * @param string $class
	 * @return $this
	 */
	public function setRecordClass(string $class)
	{
		$this->_recordClass = $class;

		return $this;
	}

	/**
	 * @param string $class
	 * @return $this
	 */
	public function setViewClass(string $class)
	{
		$this->_viewClass = $class;

		return $this;
	}

	/**
	 * @param ChainInterface $chain
	 * @return ViewInterface
	 */
	public function newView(ChainInterface $chain): ViewInterface
	{
		return new $this->_viewClass($chain);
	}

	/**
	 * @param int $startedAt
	 * @param int $updatedAt
	 * @param string $updatedBy
	 * @param array $fields
	 * @return ViewRecordInterface
	 */
	public function newViewRecord(int $startedAt, int $updatedAt, string $updatedBy, array $fields): ViewRecordInterface
	{
		return new $this->_recordClass($startedAt, $updatedAt, $updatedBy, $fields);
	}

	/**
	 * @param string $class
	 * @param null|array $arguments
	 * @return ViewActionInterface
	 * @throws \ReflectionException
	 */
	public function newViewAction(string $class, array $arguments = null): ViewActionInterface
	{
		$arguments = (array)$arguments;

		if (is_a($class, AbstractAction::class, true)) {
			array_unshift($arguments, $this->_translator ?? $this->_getTranslator());
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		$reflection = new \ReflectionClass($class);

		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $reflection->newInstanceArgs($arguments);
	}

	/**
	 * @return TranslatorInterface
	 */
	private function _getTranslator(): TranslatorInterface
	{
		$pathEn = __DIR__ . '/../../Resources/translations/history.en.php';

		$translator = new Translator($this->_locale ?? 'en');
		$translator->addLoader('php', new PhpFileLoader());
		$translator->addResource('php', $pathEn, 'en', 'history');
		$translator->setFallbackLocales(['en']);

		return $this->_translator = $translator;
	}
}