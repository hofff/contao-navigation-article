<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package Hofff_navigation_article
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	'NavigationArticle'    => 'system/modules/hofff_navigation_article/NavigationArticle.php',
	'NavigationArticleDCA' => 'system/modules/hofff_navigation_article/NavigationArticleDCA.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'nav_bbit_navi_art' => 'system/modules/hofff_navigation_article/templates',
));
