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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedtabletext/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTranslatedTableTextBundle\Test;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\QueryBuilder;
use MetaModels\AttributeTranslatedTableTextBundle\DatabaseAccessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests to test class GeoProtection.
 *
 * @covers \MetaModels\AttributeTranslatedTableTextBundle\DatabaseAccessor
 */
class DatabaseAccessorTest extends TestCase
{
    /**
     * Mock the database connection.
     *
     * @return MockObject|Connection
     */
    private function mockConnection()
    {
        return $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
    /**
     * Test that the class can be instantiated.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        self::assertInstanceOf(DatabaseAccessor::class, new DatabaseAccessor($this->mockConnection()));
    }

    /**
     * Test storing of a data row.
     *
     * @return void
     */
    public function testSetDataRow(): void
    {
        $insertBuilder = $this
            ->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $insertBuilder
            ->expects(self::once())
            ->method('insert')
            ->with('tl_metamodel_translatedtabletext')
            ->willReturn($insertBuilder);
        $insertBuilder
            ->expects(self::once())
            ->method('values')
            ->with([
                'tl_metamodel_translatedtabletext.tstamp'   => ':tstamp',
                'tl_metamodel_translatedtabletext.value'    => ':value',
                'tl_metamodel_translatedtabletext.att_id'   => ':att_id',
                'tl_metamodel_translatedtabletext.row'      => ':row',
                'tl_metamodel_translatedtabletext.col'      => ':col',
                'tl_metamodel_translatedtabletext.item_id'  => ':item_id',
                'tl_metamodel_translatedtabletext.langcode' => ':langcode',
            ])
            ->willReturn($insertBuilder);
        $insertBuilder
            ->expects(self::once())
            ->method('setParameters')
            ->with([
                'tstamp'   => \time(),
                'value'    => 'value',
                'att_id'   => 42,
                'row'      => 0,
                'col'      => 0,
                'item_id'  => 21,
                'langcode' => 'en',
            ])
            ->willReturn($insertBuilder);
        $insertBuilder
            ->expects(self::once())
            ->method('execute');

        $connection = $this->mockConnection();
        $connection->expects(self::once())->method('createQueryBuilder')->willReturn($insertBuilder);

        $accessor = new DatabaseAccessor($connection);
        $accessor->setDataRow(42, 21, 'en', 0, 0, 'value');
    }

    /**
     * Test removal of data.
     *
     * @return void
     */
    public function testRemoveDataForIds(): void
    {
        $deleteBuilder = $this
            ->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $deleteBuilder
            ->expects(self::once())
            ->method('delete')
            ->with('tl_metamodel_translatedtabletext')
            ->willReturn($deleteBuilder);
        $deleteBuilder
            ->expects(self::exactly(3))
            ->method('andWhere')
            ->withConsecutive(
                ['tl_metamodel_translatedtabletext.att_id=:att_id'],
                ['tl_metamodel_translatedtabletext.item_id IN (:item_ids)'],
                ['tl_metamodel_translatedtabletext.langcode=:langcode']
            )
            ->willReturn($deleteBuilder);
        $deleteBuilder
            ->expects(self::exactly(3))
            ->method('setParameter')
            ->withConsecutive(
                ['att_id', 42],
                ['item_ids', [21], Connection::PARAM_STR_ARRAY],
                ['langcode', 'en']
            )
            ->willReturn($deleteBuilder);

        $connection = $this->mockConnection();
        $connection->expects(self::once())->method('createQueryBuilder')->willReturn($deleteBuilder);

        $accessor = new DatabaseAccessor($connection);
        $accessor->removeDataForIds(42, [21], 'en');
    }

    /**
     * Test that fetching of data works.
     *
     * @return void
     */
    public function testFetchDataFor(): void
    {
        $queryBuilder = $this
            ->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connection = $this->mockConnection();
        $connection->expects(self::once())->method('createQueryBuilder')->willReturn($queryBuilder);

        $queryBuilder
            ->expects(self::once())
            ->method('select')
            ->with('*')
            ->willReturn($queryBuilder);
        $queryBuilder
            ->expects(self::once())
            ->method('from')
            ->with('tl_metamodel_translatedtabletext')
            ->willReturn($queryBuilder);
        $queryBuilder
            ->expects(self::once())
            ->method('orderBy')
            ->with('t.item_id', 'ASC')
            ->willReturn($queryBuilder);
        $queryBuilder
            ->expects(self::exactly(2))
            ->method('addOrderBy')
            ->withConsecutive(['t.row', 'ASC'], ['t.col', 'ASC'])
            ->willReturn($queryBuilder);
        $queryBuilder
            ->expects(self::exactly(3))
            ->method('andWhere')
            ->withConsecutive(
                ['t.att_id=:att_id'],
                ['t.item_id IN (:item_ids)'],
                ['t.langcode=:langcode']
            )
            ->willReturn($queryBuilder);
        $queryBuilder
            ->expects(self::exactly(3))
            ->method('setParameter')
            ->withConsecutive(
                ['att_id', 42],
                ['item_ids', [21], Connection::PARAM_STR_ARRAY],
                ['langcode', 'en']
            )
            ->willReturn($queryBuilder);

        $mockResult = $this->getMockBuilder(Statement::class)->disableOriginalConstructor()->getMock();
        $queryBuilder
            ->expects(self::once())
            ->method('execute')
            ->willReturn($mockResult);

        $mockResult
            ->expects(self::exactly(6))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                $this->langRow(1, '1', '42', 0, 0, 21, 'en'),
                $this->langRow(1, '2', '42', 0, 1, 21, 'en'),
                $this->langRow(1, '3', '42', 0, 2, 21, 'en'),
                $this->langRow(1, '4', '42', 2, 0, 21, 'en'),
                $this->langRow(1, '6', '42', 2, 2, 21, 'en'),
                null
            );

        $accessor = new DatabaseAccessor($connection);
        self::assertSame(
            [21 => [
                0 => [
                    $this->langRow(1, '1', '42', 0, 0, 21, 'en'),
                    $this->langRow(1, '2', '42', 0, 1, 21, 'en'),
                    $this->langRow(1, '3', '42', 0, 2, 21, 'en'),
                ],
                1 => [
                    $this->langRow(0, '', '42', 1, 0, 21, 'en'),
                    $this->langRow(0, '', '42', 1, 1, 21, 'en'),
                    $this->langRow(0, '', '42', 1, 2, 21, 'en'),
                ],
                2 => [
                    $this->langRow(1, '4', '42', 2, 0, 21, 'en'),
                    $this->langRow(0, '', '42', 2, 1, 21, 'en'),
                    $this->langRow(1, '6', '42', 2, 2, 21, 'en'),
                ]
            ]],
            $accessor->fetchDataFor(42, [21], 'en', 3)
        );
    }

    /**
     * Build a database row from the passed values.
     *
     * @param int    $tstamp   The timestamp.
     * @param string $value    The value.
     * @param string $attId    The attribute id.
     * @param int    $row      The row index.
     * @param int    $col      The column index.
     * @param string $itemId   The item id.
     * @param string $langcode The language code.
     *
     * @return array
     */
    private function langRow($tstamp, $value, $attId, $row, $col, $itemId, $langcode)
    {
        return [
            'tstamp'   => $tstamp,
            'value'    => $value,
            'att_id'   => $attId,
            'row'      => $row,
            'col'      => $col,
            'item_id'  => $itemId,
            'langcode' => $langcode,
        ];
    }
}
