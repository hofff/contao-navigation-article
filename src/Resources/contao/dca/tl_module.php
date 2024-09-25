<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_module']['palettes']['hofff_navigation_menu']
    .= ';{hofff_navi_art_legend},hofff_navi_art_enable';

$GLOBALS['TL_DCA']['tl_module']['fields']['hofff_navi_art_enable'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['hofff_navi_art_enable'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'cbx'],
];
