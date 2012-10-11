<?php

$GLOBALS['TL_DCA']['tl_module']['palettes']['backboneit_navigation_menu']
	.= ';{bbit_navi_art_legend},bbit_navi_art_enable';

$GLOBALS['TL_DCA']['tl_module']['fields']['bbit_navi_art_enable'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_module']['bbit_navi_art_enable'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array(
		'tl_class'		=> 'cbx'
	)
);
