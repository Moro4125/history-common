<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

/**
 * Diff Match and Patch
 *
 * Copyright 2006 Google Inc.
 * http://code.google.com/p/google-diff-match-patch/
 *
 * php port by Tobias Buschor shwups.ch
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Moro\History\Common\Tools;

use Exception;

/**
 * Class representing one patch operation.
 * @constructor
 */
class DiffPatchObject
{
	/** @type array {Array.<Array.<number|string>>} */
	public $diffs = array();

	/** @type integer */
	public $start1 = null;

	/** @type integer */
	public $start2 = null;

	/** @type integer */
	public $length1 = 0;

	/** @type integer */
	public $length2 = 0;

	/**
	 * Emulate GNU diff's format.
	 * Header: @@ -382,8 +481,9 @@
	 * Indices are printed as 1-based, not 0-based.
	 *
	 * @return string The GNU diff string.
	 * @throws Exception
	 */
	function toString()
	{
		if ($this->length1 === 0) {
			$coordinates1 = $this->start1 . ',0';
		} elseif ($this->length1 == 1) {
			$coordinates1 = $this->start1 + 1;
		} else {
			$coordinates1 = ($this->start1 + 1) . ',' . $this->length1;
		}

		if ($this->length2 === 0) {
			$coordinates2 = $this->start2 . ',0';
		} elseif ($this->length2 == 1) {
			$coordinates2 = $this->start2 + 1;
		} else {
			$coordinates2 = ($this->start2 + 1) . ',' . $this->length2;
		}

		$text = array('@@ -' . $coordinates1 . ' +' . $coordinates2 . " @@\n");

		// Escape the body of the patch with %xx notation.
		for ($x = 0, $l = count($this->diffs); $x < $l; $x++) {
			switch ($this->diffs[$x][0]) {
				case DiffMatchPatch::DIFF_INSERT :
					$op = '+';
					break;

				case DiffMatchPatch::DIFF_DELETE :
					$op = '-';
					break;

				case DiffMatchPatch::DIFF_EQUAL :
					$op = ' ';
					break;

				default:
					throw new Exception('Unknown mode ' . var_export($this->diffs[$x][0], true));
			}

			$text[$x + 1] = $op . DiffMatchPatch::encodeURI($this->diffs[$x][1]) . "\n";
		}

		return str_replace('%20', ' ', implode('', $text));
	}

	function __toString()
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return $this->toString();
	}
}