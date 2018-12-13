<?php

declare(strict_types=1);

namespace Hofff\Contao\NavigationArticle\EventListener;

use Contao\Image;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use PDO;

final class NavigationArticleDCAListener
{
    /** @var Connection */
    private $connection;

    private $sets;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getModules(): array
    {
        $statement = $this->connection->query('SELECT id, name FROM tl_module');
        $options   = [];

        while ($objModules = $statement->fetch(PDO::FETCH_OBJ)) {
            $options[$objModules->id] = $objModules->name;
        }

        return $options;
    }

    public function editModule($objDC, $objWidget = null): string
    {
        if (is_object($objWidget)) { // comes from MCW
            $moduleId = (int) $objWidget->value;
        } else {
            $moduleId = (int) $objDC->value;
        }

        if ($moduleId < 1) {
            return '';
        }

        return sprintf(
            ' <a href="contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=%s" title="%s" style="padding-left:3px;">%s</a>',
            $moduleId,
            sprintf(specialchars($GLOBALS['TL_LANG']['tl_page']['editalias'][1]), $moduleId),
            Image::getHtml(
                'alias.gif',
                $GLOBALS['TL_LANG']['tl_page']['editalias'][0],
                'style="vertical-align:top;"'
            )
        );
    }

    public function loadForPage($arrRows, $objDC)
    {
        $statement = $this->connection->prepare(
            'SELECT	j.*
			FROM	tl_bbit_navi_art AS j
			JOIN	tl_module AS m ON m.id = j.module
			JOIN	tl_theme AS t ON t.id = m.pid
			WHERE	j.page = :page
			ORDER BY t.name, m.name'
        );

        $statement->bindValue('page', $objDC->id);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveForPage($rows, $objDC)
    {
        $rows                   = StringUtil::deserialize($rows, true);
        $this->sets[$objDC->id] = [];

        foreach (array_values($rows) as $i => $arrRow) {
            $arrRow['page']    = $objDC->id;
            $arrRow['sorting'] = $i;
            $arrRow['module']  = (int) $arrRow['module'];
            $arrRow['article'] = (int) $arrRow['article'];

            if ($arrRow['module'] > 0 && $arrRow['article'] > 0) {
                $this->sets[$objDC->id][] = $arrRow;
            }
        }

        return null;
    }

    public function submitPage($objDC)
    {
        if (!isset($this->sets[$objDC->id])) {
            return;
        }

        $statement = $this->connection->prepare('DELETE FROM tl_bbit_navi_art WHERE page = :page');
        $statement->bindValue('page', $objDC->id);
        $statement->execute();

        if (!isset($this->sets[$objDC->id])) {
            return;
        }

        foreach ($this->sets[$objDC->id] as $set) {
            $this->connection->insert('tl_bbit_navi_art', $set);
        }
    }
}
