<?php

declare(strict_types=1);

namespace Hofff\Contao\NavigationArticle\EventListener;

use Contao\BackendUser;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\User;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use MenAtWork\MultiColumnWizardBundle\Contao\Widgets\MultiColumnWizard;

use function array_merge;
use function array_unique;
use function array_unshift;
use function array_values;
use function in_array;
use function sprintf;

final class NavigationArticleDCAListener
{
    /** @var array<int,list<array<string,mixed>>> */
    private array $sets = [];

    /** If true only reference articles (hofff_content_hide) are provides as options. */
    private bool $referencesOnly;

    public function __construct(private Connection $connection, bool $referencesOnly)
    {
        $this->referencesOnly = $referencesOnly;
    }

    /** @return array<string, array<int, string>> */
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

        $result  = $this->connection->executeQuery($query, ['type' => 'hofff_navigation_menu']);
        $options = [];

        while ($row = $result->fetchAssociative()) {
            $theme                             = sprintf('%s [%s]', $row['theme'], $row['pid']);
            $options[$theme][(int) $row['id']] = sprintf('%s [%s]', $row['name'], $row['id']);
        }

        return $options;
    }

    /**
     * @return array<string, array<int, string>>
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getArticles(MultiColumnWizard $dataContainer): array
    {
        $articles  = [];
        $pageId    = $this->getPageId((int) $dataContainer->activeRecord->pid);
        $user      = BackendUser::getInstance();
        $rootPages = $this->getRootPages($pageId);

        $query = $this->connection->createQueryBuilder()
            ->select('a.id, a.pid, a.title, a.inColumn, p.title AS parent')
            ->from('tl_article', 'a')->leftJoin('a', 'tl_page', 'p', 'p.id=a.pid')
            ->orderBy('parent, a.sorting');

        if ($this->referencesOnly) {
            $query->andWhere('a.hofff_content_hide=:active')->setParameter('active', '1');
        }

        // Limit pages to the user's pagemounts
        if ($user instanceof BackendUser && $user->isAdmin) {
            if ($rootPages) {
                $query->andWhere('a.pid IN(:root)');
                $query->setParameter('root', array_unique($rootPages), ArrayParameterType::INTEGER);
            }
        } else {
            $pids = $this->filterPids($rootPages, $user);
            if (empty($pids)) {
                return $articles;
            }

            $query->andWhere('a.pid IN(:root)');
            $query->setParameter('root', $pids, ArrayParameterType::INTEGER);
        }

        // Edit the result
        $statement = $query->executeQuery();
        System::loadLanguageFile('tl_article');

        while ($article = $statement->fetchAssociative()) {
            $key                                  = $article['parent'] . ' (ID ' . $article['pid'] . ')';
            $articles[$key][(int) $article['id']] = $article['title']
                . ' ('
                . ($GLOBALS['TL_LANG']['COLS'][$article['inColumn']] ?? $article['inColumn'])
                . ', ID '
                . $article['id']
                . ')';
        }

        return $articles;
    }

    /**
     * @param array<int,array<string,mixed>>|string $rows
     *
     * @return list<array<string,mixed>>
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadForPage(array|string|null $rows, DataContainer $dataContainer): array
    {
        $query = 'SELECT	j.*
			FROM	tl_hofff_navi_art AS j
			JOIN	tl_module AS m ON m.id = j.module
			JOIN	tl_theme AS t ON t.id = m.pid
			WHERE	j.page = :page
			ORDER BY t.name, m.name';

        return $this->connection->fetchAllAssociative($query, ['page' => $dataContainer->id]);
    }

    /** @param array<int,array<string,mixed>>|string $rows */
    public function saveForPage(array|string $rows, DataContainer $dataContainer): void
    {
        $rows                                 = StringUtil::deserialize($rows, true);
        $this->sets[(int) $dataContainer->id] = [];

        /** @psalm-var array<string,mixed> $row */
        foreach (array_values($rows) as $i => $row) {
            $row['page']    = $dataContainer->id;
            $row['sorting'] = $i;
            $row['module']  = (int) $row['module'];
            $row['article'] = (int) $row['article'];

            if ($row['module'] < 1 || $row['article'] < 1) {
                continue;
            }

            $this->sets[(int) $dataContainer->id][] = $row;
        }
    }

    public function submitPage(DataContainer $dataContainer): void
    {
        $this->connection->executeStatement(
            'DELETE FROM tl_hofff_navi_art WHERE page = :page',
            ['page' => $dataContainer->id],
        );

        if (! isset($this->sets[(int) $dataContainer->id])) {
            return;
        }

        foreach ($this->sets[(int) $dataContainer->id] as $set) {
            $this->connection->insert('tl_hofff_navi_art', $set);
        }
    }

    /** @return list<int> */
    private function getRootPages(int $intPid): array
    {
        // Limit pages to the website root
        $database = Database::getInstance();

        /** @psalm-suppress TooManyArguments */
        $article   = $database->prepare('SELECT pid FROM tl_article WHERE id=?')->limit(1)->execute($intPid);
        $rootPages = [];

        if ($article->numRows) {
            $objPage = PageModel::findWithDetails($article->pid);
            if ($objPage === null) {
                return $rootPages;
            }

            $rootPages = $database->getChildRecords($objPage->rootId, 'tl_page');
            /** @psalm-var list<int> $rootPages */
            array_unshift($rootPages, $objPage->rootId);
        }

        return $rootPages;
    }

    private function getPageId(int $pid): int
    {
        if (Input::get('act') === 'overrideAll') {
            /** @psalm-suppress RiskyCast */
            return (int) Input::get('id');
        }

        return $pid;
    }

    /**
     * @param list<int> $rootIds
     *
     * @return list<int>
     */
    private function filterPids(array $rootIds, User $user): array
    {
        $database = Database::getInstance();
        $pids     = [];

        foreach ((array) $user->pagemounts as $id) {
            if (! in_array($id, $rootIds)) {
                continue;
            }

            $pids[] = [(int) $id];
            $pids[] = $database->getChildRecords($id, 'tl_page');
        }

        return array_values(array_unique(array_merge(...$pids)));
    }
}
