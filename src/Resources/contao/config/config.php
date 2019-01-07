<?php

/*
 * Backend modules
 */

if (isset($GLOBALS['BE_MOD']['design']['page']['stylesheet']) && !is_array($GLOBALS['BE_MOD']['design']['page']['css'])) {
    $GLOBALS['BE_MOD']['design']['page']['css'] = [$GLOBALS['BE_MOD']['design']['page']['css']];
}

$GLOBALS['BE_MOD']['design']['page']['stylesheet'][] = 'bundles/hofffcontaonavigationarticle/css/backend.css';

/*
 * Hooks
 */

$GLOBALS['TL_HOOKS']['hofff_navi_item'][] = [
    \Hofff\Contao\NavigationArticle\EventListener\NavigationArticleListener::class,
    '__invoke',
];
