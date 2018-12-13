<?php

$GLOBALS['TL_HOOKS']['bbit_navi_item'][] = [
    \Hofff\Contao\NavigationArticle\EventListener\NavigationArticleListener::class,
    '__invoke',
];
