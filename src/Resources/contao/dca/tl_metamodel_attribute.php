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
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     David Greminger <david.greminger@1up.io>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedtabletext/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['metapalettes']['translatedtabletext extends _complexattribute_'] = [
    '+advanced' => [
        'tabletext_quantity_cols',
        'translatedtabletext_cols',
        'translatedtabletext_minCount',
        'translatedtabletext_maxCount',
        'translatedtabletext_disable_sorting'
    ],
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['dca_config']['data_provider']['tl_metamodel_translatedtabletext'] = [
    'source' => 'tl_metamodel_translatedtabletext'
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['dca_config']['childCondition'][] = [
    'from'   => 'tl_metamodel_attribute',
    'to'     => 'tl_metamodel_translatedtabletext',
    'setOn'  => [
        [
            'to_field'   => 'att_id',
            'from_field' => 'id',
        ],
    ],
    'filter' => [
        [
            'local'     => 'att_id',
            'remote'    => 'id',
            'operation' => '=',
        ],
    ]
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['tabletext_quantity_cols'] = [
    'label'       => 'translatedtabletext_quantity_cols.label',
    'description' => 'translatedtabletext_quantity_cols.description',
    'exclude'     => true,
    'inputType'   => 'select',
    'default'     => 1,
    'options'     => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
    'eval'        => [
        'tl_class'       => 'clr w50 m12',
        'alwaysSave'     => true,
        'submitOnChange' => true
    ],
    'sql'         => 'varchar(2) NOT NULL default \'\''
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['translatedtabletext_cols'] = [
    'label'       => 'translatedtabletext_cols.label',
    'description' => 'translatedtabletext_cols.description',
    'exclude'     => true,
    'inputType'   => 'multiColumnWizard',
    'eval'        => [
        'hideButtons'    => true,
        'disableSorting' => true,
        'useTranslator'  => true,
        'tl_class'       => 'clr w50',
        'columnFields'   => [
            'langcode'  => [
                'label'     => 'translatedtabletext_langcode.label',
                'exclude'   => true,
                'inputType' => 'justtextoption',
                'eval'      => [
                    'style'  => 'min-width:75px;display:block;padding-top:28px;',
                    'valign' => 'top',
                ],
            ],
            'rowLabels' => [
                'label'       => 'translatedtabletext_rowLabels.label',
                'description' => 'translatedtabletext_rowLabels.description',
                'exclude'     => true,
                'inputType'   => 'multiColumnWizard',
                'eval'        => [
                    'hideButtons'    => true,
                    'disableSorting' => true,
                    'useTranslator'  => true,
                    'tl_class'       => 'clr',
                    'columnFields'   => [
                        'rowLabel' => [
                            'label'       => 'translatedtabletext_rowLabel.label',
                            'description' => 'translatedtabletext_rowLabel.description',
                            'exclude'     => true,
                            'inputType'   => 'text',
                            'eval'        => [
                                'allowHtml' => true,
                                'style'     => 'width: 400px;',
                            ],
                        ],
                        'rowStyle' => [
                            'label'       => 'translatedtabletext_rowStyle.label',
                            'description' => 'translatedtabletext_rowStyle.description',
                            'exclude'     => true,
                            'inputType'   => 'text',
                            'eval'        => [
                                'allowHtml' => false,
                                'style'     => 'width: 90px;',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'sql'         => 'blob NULL'
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['translatedtabletext_minCount'] = [
    'label'       => 'translatedtabletext_minCount.label',
    'description' => 'translatedtabletext_minCount.description',
    'exclude'     => true,
    'inputType'   => 'text',
    'eval'        => ['rgxp' => 'natural', 'maxlength' => 255, 'tl_class' => 'clr w50'],
    'sql'         => 'smallint(5) NOT NULL default \'0\''
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['translatedtabletext_maxCount'] = [
    'label'       => 'translatedtabletext_maxCount.label',
    'description' => 'translatedtabletext_maxCount.description',
    'exclude'     => true,
    'inputType'   => 'text',
    'eval'        => ['rgxp' => 'natural', 'maxlength' => 255, 'tl_class' => 'w50'],
    'sql'         => 'smallint(5) NOT NULL default \'0\''
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['translatedtabletext_disable_sorting'] = [
    'label'       => 'translatedtabletext_disable_sorting.label',
    'description' => 'translatedtabletext_disable_sorting.description',
    'exclude'     => true,
    'inputType'   => 'checkbox',
    'eval'        => ['tl_class' => 'clr w50 cbx m12'],
    'sql'         => 'char(1) NOT NULL default \'\''
];
