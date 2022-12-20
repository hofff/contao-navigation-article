<?php

declare(strict_types=1);

/*
 * Backend modules
 */

if (
    isset($GLOBALS['BE_MOD']['design']['page']['stylesheet'])
    && ! is_array($GLOBALS['BE_MOD']['design']['page']['css'])
) {
    $GLOBALS['BE_MOD']['design']['page']['css'] = [$GLOBALS['BE_MOD']['design']['page']['css']];
}

$GLOBALS['BE_MOD']['design']['page']['stylesheet'][] = 'bundles/hofffcontaonavigationarticle/css/backend.css';
