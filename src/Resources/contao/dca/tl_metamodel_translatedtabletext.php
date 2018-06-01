<?php

/**
 * This file is part of MetaModels/attribute_translatedtabletext.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage AttributeTranslatedTableText
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedtabletext/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Table tl_metamodel_translatedtabletext.php
 */
$GLOBALS['TL_DCA']['tl_metamodel_translatedtabletext'] = [
    // Config
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'att_id,item_id,row,col,langcode' => 'index'
            ]
        ]
    ],
    // Fields
    'fields' => [
        'id'       => [
            'sql'                     => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'tstamp'   => [
            'sql'                     => 'int(10) unsigned NOT NULL default \'0\''
        ],
        'att_id'   => [
            'sql'                     => 'int(10) unsigned NOT NULL default \'0\''
        ],
        'item_id'  => [
            'sql'                     => 'int(10) unsigned NOT NULL default \'0\''
        ],
        'langcode' => [
            'sql'                     => 'varchar(5) NOT NULL default \'\''
        ],
        'row'      => [
            'sql'                     => 'int(5) unsigned NOT NULL default \'0\''
        ],
        'col'      => [
            'sql'                     => 'int(5) unsigned NOT NULL default \'0\''
        ],
        'value'    => [
            'sql'                     => 'varchar(255) NOT NULL default \'\''
        ]
    ]
];
