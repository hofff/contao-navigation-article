<?php

declare(strict_types=1);

namespace Hofff\Contao\NavigationArticle\EventListener;

use function array_merge;
use function array_unique;
use Contao\BackendUser;
use Contao\Database;
use Contao\Input;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use PDO;

final class NavigationArticleDCAListener
{
    /** @var Connection */
    private $connection;

    private $sets;

    /**
     * If true only reference articles (hofff_content_hide) are provides as options.
     *
     * @var bool
     */
    private $referenceArticlesOnly;

    public function __construct(Connection $connection, bool $referenceArticlesOnly)
    {
        $this->connection            = $connection;
        $this->referenceArticlesOnly = $referenceArticlesOnly;
    }

    public function getModules(): array
    {
        $query = <<< 'SQL'
SELECT 
  m.id, m.name, t.name AS theme, m.pid
FROM
  tl_module m
INNER JOIN
  tl_theme t
  ON
    t.id = m.pid
WHERE 
  m.type = :type
ORDER BY 
  m.name
SQL;

        $statement = $this->connection->prepare($query);
        $statement->bindValue('type', 'backboneit_navigation_menu');
        $statement->execute();

        $options = [];

        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $theme                     = sprintf('%s [%s]', $row->theme, $row->pid);
            $options[$theme][$row->id] = sprintf('%s [%s]', $row->name, $row->id);
        }

        return $options;
    }

    public function getArticles($objDC): array
    {
        $arrPids    = [];
        $arrArticle = [];
        $arrRoot    = [];
        $intPid     = $objDC->activeRecord->pid;
        $database   = Database::getInstance();
        $user       = BackendUser::getInstance();

        if (Input::get('act') === 'overrideAll') {
            $intPid = Input::get('id');
        }

        // Limit pages to the website root
        $objArticle = $database->prepare("SELECT pid FROM tl_article WHERE id=?")
            ->limit(1)
            ->execute($intPid);

        if ($objArticle->numRows) {
            $objPage = PageModel::findWithDetails($objArticle->pid);
            $arrRoot = $database->getChildRecords($objPage->rootId, 'tl_page');
            array_unshift($arrRoot, $objPage->rootId);
        }

        unset($objArticle);

        $query = $this->connection->createQueryBuilder()
            ->select('a.id, a.pid, a.title, a.inColumn, p.title AS parent')
            ->from('tl_article', 'a')
            ->leftJoin('a', 'tl_page', 'p', 'p.id=a.pid')
            ->orderBy('parent, a.sorting');

        if ($this->referenceArticlesOnly) {
            $query
                ->andWhere('a.hofff_content_hide=:active')
                ->setParameter('active', '1');
        }

        // Limit pages to the user's pagemounts
        if ($user->isAdmin) {
            if ($arrRoot) {
                $query->andWhere('a.pid IN(:root)');
                $query->setParameter('root', array_unique($arrRoot), Connection::PARAM_INT_ARRAY);
            }
        } else {
            foreach ($user->pagemounts as $id) {
                if (!\in_array($id, $arrRoot)) {
                    continue;
                }

                $arrPids[] = [$id];
                $arrPids[] = $database->getChildRecords($id, 'tl_page');
            }

            $arrPids = array_unique(array_merge(...$arrPids));

            if (empty($arrPids)) {
                return $arrArticle;
            }

            $query->andWhere('a.pid IN(:root)');
            $query->setParameter('root', $arrPids, Connection::PARAM_INT_ARRAY);
        }

        // Edit the result
        $statement = $query->execute();
        System::loadLanguageFile('tl_article');

        while ($objArticle = $statement->fetch(PDO::FETCH_OBJ)) {
            $key                               = $objArticle->parent . ' (ID ' . $objArticle->pid . ')';
            $arrArticle[$key][$objArticle->id] = $objArticle->title . ' (' . ($GLOBALS['TL_LANG']['COLS'][$objArticle->inColumn] ?: $objArticle->inColumn) . ', ID ' . $objArticle->id . ')';
        }

        return $arrArticle;
    }

    public function loadForPage($arrRows, $objDC)
    {
        $statement = $this->connection->prepare(
            'SELECT	j.*
			FROM	tl_hofff_navi_art AS j
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

        $statement = $this->connection->prepare('DELETE FROM tl_hofff_navi_art WHERE page = :page');
        $statement->bindValue('page', $objDC->id);
        $statement->execute();

        if (!isset($this->sets[$objDC->id])) {
            return;
        }

        foreach ($this->sets[$objDC->id] as $set) {
            $this->connection->insert('tl_hofff_navi_art', $set);
        }
    }
}
