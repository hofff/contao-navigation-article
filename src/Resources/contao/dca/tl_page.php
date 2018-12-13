<?php

use Hofff\Contao\NavigationArticle\EventListener\NavigationArticleDCAListener;

$GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'][] = [NavigationArticleDCAListener::class, 'submitPage'];

foreach ($GLOBALS['TL_DCA']['tl_page']['palettes'] as $strSelector => &$strPalette) {
    if ($strSelector !== '__selector__') {
        $strPalette .= ';{bbit_navi_art_legend},bbit_navi_art_articles';
    }
}
unset($strPalette);

$GLOBALS['TL_DCA']['tl_page']['fields']['bbit_navi_art_articles'] = [
    'label'         => &$GLOBALS['TL_LANG']['tl_page']['bbit_navi_art_articles'],
    'inputType'     => 'multiColumnWizard',
    'eval'          => [
        'doNotSaveEmpty' => true,
        'columnFields'   => [
            'module'    => [
                'label'            => &$GLOBALS['TL_LANG']['tl_page']['bbit_navi_art_module'],
                'inputType'        => 'select',
                'options_callback' => [NavigationArticleDCAListener::class, 'getModules'],
                'eval'             => [
                    'includeBlankOption' => true,
                    'chosen'             => true,
                    'style'              => 'width: 180px;',
                ],
                'wizard'           => [
                    [NavigationArticleDCAListener::class, 'editModule'],
                ],
            ],
            'article'   => [
                'label'            => &$GLOBALS['TL_LANG']['tl_page']['bbit_navi_art_article'],
                'inputType'        => 'select',
                'options_callback' => [NavigationArticleDCAListener::class, 'getArticles'],
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
    ],
    'load_callback' => [
        [NavigationArticleDCAListener::class, 'loadForPage'],
    ],
    'save_callback' => [
        [NavigationArticleDCAListener::class, 'saveForPage'],
    ],
];
