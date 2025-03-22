<?php

/**
 * This file is part of MetaModels/attribute_translatedtabletext.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_translatedtabletext
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedtabletext/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTranslatedTableTextBundle\Test\Attribute;

use MetaModels\AttributeTranslatedTableTextBundle\Attribute\AttributeTypeFactory;
use MetaModels\AttributeTranslatedTableTextBundle\DatabaseAccessor;
use MetaModels\IMetaModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use MetaModels\AttributeTranslatedTableTextBundle\Attribute\TranslatedTableText;

/**
 * Test the attribute factory.
 *
 * @covers \MetaModels\AttributeTranslatedTableTextBundle\Attribute\AttributeTypeFactory
 */
class AttributeTypeFactoryTest extends TestCase
{
    /**
     * Mock a MetaModel.
     *
     * @param string $tableName        The table name.
     * @param string $language         The language.
     * @param string $fallbackLanguage The fallback language.
     *
     * @return IMetaModel
     */
    protected function mockMetaModel($tableName, $language, $fallbackLanguage)
    {
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);

        $metaModel
            ->method('getTableName')
            ->willReturn($tableName);

        $metaModel
            ->method('getActiveLanguage')
            ->willReturn($language);

        $metaModel
            ->method('getFallbackLanguage')
            ->willReturn($fallbackLanguage);

        return $metaModel;
    }

    /**
     * Mock the database connection.
     *
     * @return MockObject|DatabaseAccessor
     */
    private function mockAccessor()
    {
        return $this->getMockBuilder(DatabaseAccessor::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test creation of an translated select.
     *
     * @return void
     */
    public function testCreateAttribute()
    {
        $factory   = new AttributeTypeFactory($this->mockAccessor());
        $values    = [
            'translatedtabletext_cols' => \serialize(
                [
                    'langcode'  => 'en',
                    'rowLabels' => [
                        [
                            'rowLabel' => 'rowlabel',
                            'rowStyle' => 'rowstyle'
                        ]
                    ]
                ]
            )
        ];
        $attribute = $factory->createInstance(
            $values,
            $this->mockMetaModel('mm_test', 'de', 'en')
        );

        $check                             = $values;
        $check['translatedtabletext_cols'] = \unserialize($check['translatedtabletext_cols']);

        self::assertInstanceOf(TranslatedTableText::class, $attribute);

        foreach ($check as $key => $value) {
            self::assertEquals($value, $attribute->get($key), $key);
        }
    }
}
