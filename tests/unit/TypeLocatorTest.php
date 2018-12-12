<?php
/**
 * This file is part of the package moro/history-common
 *
 * @see https://github.com/Moro4125/history-common
 * @license http://opensource.org/licenses/MIT
 * @author Morozkin Andrey <andrey.dmitrievich@gmail.com>
 */

use Moro\History\Common\Chain\Strategy\ListStrategy;
use Moro\History\Common\Entity\Field\ExtendedListField;
use Moro\History\Common\Entity\Field\HashField;
use Moro\History\Common\Entity\Field\ListField;
use Moro\History\Common\Entity\Field\ScalarField;
use Moro\History\Common\Type\Component\TypeComponent;
use Moro\History\Common\Type\TypeLocator;

/**
 * Class TypeLocatorTest
 */
class TypeLocatorTest extends \PHPUnit\Framework\TestCase
{
	use Codeception\Specify;
	use Codeception\AssertThrows;

	public function test()
	{
		$type1 = new TypeComponent('oxueekz');
		$type2 = new TypeComponent('pyqptgs');

		$locator = new TypeLocator();
		$locator->addType($type1);
		verify($type1->getId())->same(1122772949);

		$locator = new TypeLocator();
		$locator->addType($type2);
		verify($type2->getId())->same(1122772949);

		$locator = new TypeLocator();
		$locator->addType($type1);
		$locator->addType($type2);

		verify($type1->getId())->same(895695053);
		verify($type2->getId())->same(2700836119);
	}
}