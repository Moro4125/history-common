<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\View\Action;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AbstractField
 * @package Moro\History\Common\View\Field
 */
class AbstractAction
{
	const GENDER_MALE     = 1;
	const GENDER_FEMININE = 2;
	const GENDER_NEUTER   = 3;

	/** @var TranslatorInterface */
	private   $_translator;
	protected $_domain;
	protected $_locale;

	/**
	 * @param TranslatorInterface $translator
	 */
	public function __construct(TranslatorInterface $translator)
	{
		$this->_translator = $translator;
		$this->_domain = 'history';
		$this->_locale = $translator->getLocale() ?: null;
	}

	/**
	 * @param string $id
	 * @param array|null $params
	 * @param int|null $gender
	 * @return string
	 */
	protected function _trans(string $id, array $params = null, int $gender = null): string
	{
		$gender = (int)$gender;

		if (($gender & self::GENDER_NEUTER) === self::GENDER_NEUTER) {
			$idA = $id . '.n';

			if ($idA !== $result = $this->_translator->trans($idA, (array)$params, $this->_domain, $this->_locale)) {
				return $result;
			}
		} elseif ($gender & self::GENDER_NEUTER) {
			$idB = $id . '.' . (($gender & self::GENDER_MALE) ? 'm' : 'f');

			if ($idB !== $result = $this->_translator->trans($idB, (array)$params, $this->_domain, $this->_locale)) {
				return $result;
			}
		}

		return $this->_translator->trans($id, (array)$params, $this->_domain, $this->_locale);
	}

	/**
	 * @param string $id
	 * @param int $number
	 * @param array|null $params
	 * @param int|null $gender
	 * @return string
	 */
	protected function _transChoice(string $id, int $number, array $params = null, int $gender = null): string
	{
		$gender = (int)$gender;
		$i18n = $this->_translator;

		if (($gender & self::GENDER_NEUTER) === self::GENDER_NEUTER) {
			$idA = $id . '.n';

			if ($idA !== $result = $i18n->transChoice($idA, $number, (array)$params, $this->_domain, $this->_locale)) {
				return $result;
			}
		} elseif ($gender & self::GENDER_NEUTER) {
			$idB = $id . '.' . (($gender & self::GENDER_MALE) ? 'm' : 'f');

			if ($idB !== $result = $i18n->transChoice($idB, $number, (array)$params, $this->_domain, $this->_locale)) {
				return $result;
			}
		}

		return $i18n->transChoice($id, $number, (array)$params, $this->_domain, $this->_locale);
	}
}