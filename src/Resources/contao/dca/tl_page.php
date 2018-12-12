<?php

$GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'][] = ['NavigationArticleDCA', 'submitPage'];

foreach ($GLOBALS['TL_DCA']['tl_page']['palettes'] as $strSelector => &$strPalette) {
    if ($strSelector != '__selector__') {
        $strPalette .= ';{bbit_navi_art_legend},bbit_navi_art_articles';
    }
}

$GLOBALS['TL_DCA']['tl_page']['fields']['bbit_navi_art_articles'] = [
    'label'         => &$GLOBALS['TL_LANG']['tl_page']['bbit_navi_art_articles'],
    'inputType'     => 'multiColumnWizard',
    'eval'          => [
        'doNotSaveEmpty' => true,
        'columnFields'   => [
            'module'    => [
                'label'            => &$GLOBALS['TL_LANG']['tl_page']['bbit_navi_art_module'],
                'inputType'        => 'select',
                'options_callback' => ['NavigationArticleDCA', 'getModules'],
                'eval'             => [
                    'includeBlankOption' => true,
                    'chosen'             => true,
                    'style'              => 'width: 180px;',
                ],
                'wizard'           => [
                    ['NavigationArticleDCA', 'editModule'],
                ],
            ],
            'article'   => [
                'label'            => &$GLOBALS['TL_LANG']['tl_page']['bbit_navi_art_article'],
                'inputType'        => 'select',
                'options_callback' => ['IncludeArticleDCA', 'getArticles'],
                'eval'             => [
                    'includeBlankOption' => true,
                    'chosen'             => true,
                    'style'              => 'width: 180px;',
                ],
                'wizard'           => [
                    ['IncludeArticleDCA', 'editArticle'],
                ],
            ],
            'cssID'     => [
                'label'     => &$GLOBALS['TL_LANG']['tl_page']['bbit_navi_art_cssID'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => [
                    'multiple' => true,
                    'size'     => 2,
                    'style'    => 'width: 55px;',
                ],
            ],
            'nosearch'  => [
                'label'     => &$GLOBALS['TL_LANG']['tl_page']['bbit_navi_art_nosearch_short'],
                'inputType' => 'checkbox',
                'eval'      => [
                ],
            ],
            'container' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_page']['bbit_navi_art_container_short'],
                'inputType' => 'checkbox',
                'eval'      => [
                ],
            ],
        ],
// 		'tl_class'			=> 'clr',
    ],
    'load_callback' => [
        ['NavigationArticleDCA', 'loadForPage'],
    ],
    'save_callback' => [
        ['NavigationArticleDCA', 'saveForPage'],
    ],
];
