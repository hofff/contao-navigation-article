<?php

declare(strict_types=1);

namespace Hofff\Contao\NavigationArticle\EventListener;

use function array_map;
use function array_unique;
use Contao\ArticleModel;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface as ContaoFramework;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Hofff\Contao\Content\Renderer\ArticleRenderer;
use PDO;
use function strpos;

final class NavigationArticleListener
{
    /** @var Connection */
    private $connection;

    /** @var ContaoFramework */
    private $contaoFramework;

    private $articles = [];

    public function __construct(Connection $connection, ContaoFramework $contaoFramework)
    {
        $this->connection = $connection;
        $this->contaoFramework = $contaoFramework;
    }

    public function __invoke($navi, &$page): void
    {
        $articles = $this->getNavigationArticles((int) $navi->id, (int) $page['id']);
        if (!$articles) {
            return;
        }

        $articlesIds = $this->getArticleIdsFromConfiguration($articles);
        $repository  = $this->contaoFramework->getAdapter(ArticleModel::class);
        $collection  = $repository->__call('findMultipleByIds', [$articlesIds]) ?: [];
        $models      = [];

        foreach ($collection as $model) {
            $models[$model->id] = $model;
        }

        foreach ($articles as $article) {
            if (!isset($models[$article['article']])) {
                continue;
            }

            $renderer = new ArticleRenderer();
            $renderer->setArticle($models[$article['article']]);
            $renderer->setRenderContainer((bool) $article['container']);
            $renderer->setExcludeFromSearch((bool) $article['nosearch']);
            $renderer->setColumn(null);
            $renderer->setCSSID($article['cssId']);

            if (isset($article['cssClass'])) {
                $renderer->addCSSClasses($article['cssClass']);
            }

            $page['hofff_navi_arts'][] = $renderer->render();
        }

        $page['hofff_navi_art'] = implode('', $page['hofff_navi_arts']);

        if (strpos($navi->cssID[1], 'hofff-navi-art') === false) {
            $data        = $navi->cssID;
            $data[1]     = trim($data[1] . ' hofff-navi-art');
            $navi->cssID = $data;
        }
    }

    private function getNavigationArticles(int $moduleId, int $pageId): array
    {
        if (!isset($this->articles[$moduleId])) {
            $this->articles[$moduleId] = $this->fetchNavigationArticlesForModule($moduleId);
        }

        if (isset($this->articles[$moduleId][$pageId])) {
            return (array) $this->articles[$moduleId][$pageId];
        }

        return [];
    }

    private function fetchNavigationArticlesForModule(int $moduleId): array
    {
        $query     = 'SELECT * FROM tl_hofff_navi_art WHERE module = :module ORDER BY sorting';
        $statement = $this->connection->prepare($query);
        $statement->bindValue('module', $moduleId);
        $statement->execute();

        $articles = [];

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $articles[$row['page']][] = $row;
        }

        return $articles;
    }

    private function getArticleIdsFromConfiguration(array $articles): array
    {
        return array_unique(
            array_map(
                function ($article) {
                    return $article['article'];
                },
                $articles
            )
        );
    }
}
