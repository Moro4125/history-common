<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

namespace Moro\History\Common\Type;

/**
 * Class HistoryOptions
 * @package Moro\History\Common
 */
class TypeLocator
{
	/** @var TypeInterface[] */
	protected $_typesByName;

	/** @var TypeInterface[] */
	protected $_typesByClass;

	/** @var TypeInterface[] */
	protected $_typesById;

	/**
	 * @param TypeInterface $type
	 * @return TypeLocator
	 */
	public function addType(TypeInterface $type): TypeLocator
	{
		$this->_typesByClass[get_class($type)] = $type;
		$this->_typesByName[$type->__toString()] = $type;

		$idSteps = array_fill_keys(array_keys($this->_typesByName), 0);

		do {
			$this->_typesById = [];
			$idDuplicates = [];

			foreach ($this->_typesByName as $name => $type) {
				$code = $name;

				for ($i = 0; $i < $idSteps[$name]; $i++) {
					$char = substr($code, -1);
					$code = $char . substr($code, 0, -1);
				}

				$id = max(1, abs(crc32($code)));

				if (isset($this->_typesById[$id])) {
					$idDuplicates[(string)$this->_typesById[$id]] = true;
					$idDuplicates[$name] = true;
				}

				$this->_typesById[$id] = $type;
			}

			foreach ($idDuplicates as $name => $flag) {
				$idSteps[$name]++;
			}
		} while ($idDuplicates);

		foreach ($this->_typesById as $id => $type) {
			$type->setId($id);
		}

		return $this;
	}

	/**
	 * @param string $type
	 * @return TypeInterface|null
	 */
	public function getType(string $type): ?TypeInterface
	{
		return $this->_typesByName[$type] ?? $this->_typesByClass[$type] ?? $this->_typesById[$type] ?? null;
	}
}