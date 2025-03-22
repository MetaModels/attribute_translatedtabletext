<?php

/**
 * This file is part of MetaModels/attribute_translatedtabletext.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_translatedtabletext
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedtabletext/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTranslatedTableTextBundle\Test\DependencyInjection;

use MetaModels\AttributeTranslatedTableTextBundle\Attribute\AttributeTypeFactory;
use MetaModels\AttributeTranslatedTableTextBundle\DatabaseAccessor;
use MetaModels\AttributeTranslatedTableTextBundle\DependencyInjection\MetaModelsAttributeTranslatedTableTextExtension;
use MetaModels\AttributeTranslatedTableTextBundle\EventListener\DcGeneral\Table\BackendTableListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This test case test the extension.
 *
 * @covers \MetaModels\AttributeTranslatedTableTextBundle\DependencyInjection\MetaModelsAttributeTranslatedTableTextExtension
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class AttributeTranslatedTableTextExtensionTest extends TestCase
{
    /**
     * Test that extension can be instantiated.
     *
     * @return void
     */
    public function testInstantiation()
    {
        $extension = new MetaModelsAttributeTranslatedTableTextExtension();

        self::assertInstanceOf(MetaModelsAttributeTranslatedTableTextExtension::class, $extension);
        self::assertInstanceOf(ExtensionInterface::class, $extension);
    }

    /**
     * Test that the services are loaded.
     *
     * @return void
     */
    public function testFactoryIsRegistered()
    {
        $container = new ContainerBuilder();

        $extension = new MetaModelsAttributeTranslatedTableTextExtension();
        $extension->load([], $container);

        self::assertTrue($container->hasDefinition('metamodels.attribute_translatedtabletext.factory'));
        $definition = $container->getDefinition('metamodels.attribute_translatedtabletext.factory');
        self::assertCount(1, $definition->getTag('metamodels.attribute_factory'));
        self::assertCount(1, $arguments = $definition->getArguments());
        self::assertInstanceOf(Reference::class, $arguments[0]);
        self::assertSame(DatabaseAccessor::class, (string) $arguments[0]);

        self::assertTrue($container->hasDefinition(DatabaseAccessor::class));
        $definition = $container->getDefinition(DatabaseAccessor::class);
        self::assertCount(1, $arguments = $definition->getArguments());
        self::assertInstanceOf(Reference::class, $arguments[0]);
        self::assertSame('database_connection', (string) $arguments[0]);
        // phpcs:disable
        self::assertTrue($container->hasDefinition('metamodels.attribute_translatedtabletext.listeners.translated_alias_options'));
        $definition = $container->getDefinition('metamodels.attribute_translatedtabletext.listeners.translated_alias_options');
        self::assertCount(3, $definition->getTag('kernel.event_listener'));
        // phpcs:enable
    }
}
