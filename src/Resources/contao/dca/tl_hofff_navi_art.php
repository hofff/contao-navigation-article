<?php

declare(strict_types=1);

use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_hofff_navi_art'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'sql'           => [
            'keys' => [
                'page,sorting' => 'primary',
                'module'       => 'index',
                'article'      => 'index',
            ],
        ],
    ],
    'fields' => [
        'page'      => ['sql' => 'int(10) unsigned NOT NULL default 0'],
        'sorting'   => ['sql' => 'int(10) unsigned NOT NULL default 0'],
        'module'    => ['sql' => 'int(10) unsigned NOT NULL default 0'],
        'article'   => ['sql' => 'int(10) unsigned NOT NULL default 0'],
        'cssId'     => ['sql' => 'varchar(255) NOT NULL default \'\''],
        'cssClass'  => ['sql' => 'varchar(255) NOT NULL default \'\''],
        'nosearch'  => ['sql' => 'char(1) NOT NULL default \'\''],
        'container' => ['sql' => 'char(1) NOT NULL default \'\''],
    ],
];
