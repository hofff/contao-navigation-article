<?php

$GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'][] = array('NavigationArticleDCA', 'submitPage');

foreach($GLOBALS['TL_DCA']['tl_page']['palettes'] as $strSelector => &$strPalette) if($strSelector != '__selector__') {
	$strPalette .= ';{bbit_navi_art_legend},bbit_navi_art_articles';
}

$GLOBALS['TL_DCA']['tl_page']['fields']['bbit_navi_art_articles'] = array(
	'label'			=> &$GLOBALS['TL_LANG']['tl_page']['bbit_navi_art_articles'],
	'inputType'		=> 'multiColumnWizard',
	'eval'			=> array(
		'doNotSaveEmpty'=> true,
		'columnFields'	=> array(
			'module' => array(
				'label'		=> &$GLOBALS['TL_LANG']['tl_page']['bbit_navi_art_module'],
				'inputType'	=> 'select',
				'options_callback'	=> array('NavigationArticleDCA', 'getModules'),
				'eval'		=> array(
					'includeBlankOption'=> true,
					'chosen'		=> true,
					'style'			=> 'width: 180px;'
				),
				'wizard'			=> array(
					array('NavigationArticleDCA', 'editModule')
				),
			),
			'article' => array(
				'label'		=> &$GLOBALS['TL_LANG']['tl_page']['bbit_navi_art_article'],
				'inputType'	=> 'select',
				'options_callback'	=> array('IncludeArticleDCA', 'getArticles'),
				'eval'		=> array(
					'includeBlankOption'=> true,
					'chosen'		=> true,
					'style'			=> 'width: 180px;'
				),
				'wizard'			=> array(
					array('IncludeArticleDCA', 'editArticle')
				),
			),
			'cssID' => array(
				'label'		=> &$GLOBALS['TL_LANG']['tl_page']['bbit_navi_art_cssID'],
				'exclude'	=> true,
				'inputType'	=> 'text',
				'eval'		=> array(
					'multiple'		=> true,
					'size'			=> 2,
					'style'			=> 'width: 55px;'
				)
			),
			'nosearch' => array(
				'label'		=> &$GLOBALS['TL_LANG']['tl_page']['bbit_navi_art_nosearch_short'],
				'inputType'	=> 'checkbox',
				'eval'		=> array(
				)
			),
			'container' => array(
				'label'		=> &$GLOBALS['TL_LANG']['tl_page']['bbit_navi_art_container_short'],
				'inputType'	=> 'checkbox',
				'eval'		=> array(
				)
			),
		),
// 		'tl_class'			=> 'clr',
	),
	'load_callback'	=> array(
		array('NavigationArticleDCA', 'loadForPage')
	),
	'save_callback'	=> array(
		array('NavigationArticleDCA', 'saveForPage')
	),
);