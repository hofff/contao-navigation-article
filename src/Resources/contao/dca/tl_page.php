<?php

declare(strict_types=1);

use Hofff\Contao\NavigationArticle\EventListener\NavigationArticleDCAListener;

$GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'][] = [
    NavigationArticleDCAListener::class,
    'submitPage',
];

foreach ($GLOBALS['TL_DCA']['tl_page']['palettes'] as $strSelector => &$strPalette) {
    if ($strSelector === '__selector__') {
        continue;
    }

    $strPalette .= ';{hofff_navi_art_legend},hofff_navi_art_articles';
}

unset($strPalette);

$GLOBALS['TL_DCA']['tl_page']['fields']['hofff_navi_art_articles'] = [
    'label'         => &$GLOBALS['TL_LANG']['tl_page']['hofff_navi_art_articles'],
    'inputType'     => 'multiColumnWizard',
    'eval'          => [
        'tl_class'       => 'hofff-navi-art-articles',
        'doNotSaveEmpty' => true,
        'columnFields'   => [
            'module'    => [
                'label'            => &$GLOBALS['TL_LANG']['tl_page']['hofff_navi_art_module'],
                'inputType'        => 'select',
                'options_callback' => [NavigationArticleDCAListener::class, 'getModules'],
                'eval'             => [
                    'includeBlankOption' => true,
                    'chosen'             => true,
                    'style'              => 'width:100%',
                ],
            ],
            'article'   => [
                'label'            => &$GLOBALS['TL_LANG']['tl_page']['hofff_navi_art_article'],
                'inputType'        => 'select',
                'options_callback' => [NavigationArticleDCAListener::class, 'getArticles'],
                'eval'             => [
                    'includeBlankOption' => true,
                    'chosen'             => true,
                    'style'              => 'width:100%',
                ],
            ],
            'cssId'     => [
                'label'     => &$GLOBALS['TL_LANG']['tl_page']['hofff_navi_art_cssId'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => [],
            ],
            'cssClass'  => [
                'label'     => &$GLOBALS['TL_LANG']['tl_page']['hofff_navi_art_cssClass'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => [],
            ],
            'nosearch'  => [
                'label'     => &$GLOBALS['TL_LANG']['tl_page']['hofff_navi_art_nosearch_short'],
                'inputType' => 'checkbox',
                'eval'      => [],
            ],
            'container' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_page']['hofff_navi_art_container_short'],
                'inputType' => 'checkbox',
                'eval'      => [],
            ],
        ],
    ],
    'load_callback' => [[NavigationArticleDCAListener::class, 'loadForPage']],
    'save_callback' => [[NavigationArticleDCAListener::class, 'saveForPage']],
];
