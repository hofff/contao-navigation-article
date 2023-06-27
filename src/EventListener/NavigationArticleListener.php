<?php

declare(strict_types=1);

namespace Hofff\Contao\NavigationArticle\EventListener;

use Contao\ArticleModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Hofff\Contao\Content\Renderer\ArticleRenderer;
use Hofff\Contao\Navigation\Event\ItemEvent;

use function array_map;
use function array_unique;
use function array_values;
use function implode;
use function strpos;
use function trim;

final class NavigationArticleListener
{
    private Connection $connection;

    private ContaoFramework $contaoFramework;

    /** @var array<int, array<int, list<array<string, mixed>>>> */
    private array $articles = [];

    public function __construct(Connection $connection, ContaoFramework $contaoFramework)
    {
        $this->connection      = $connection;
        $this->contaoFramework = $contaoFramework;
    }

    public function __invoke(ItemEvent $event): void
    {
        $navi = $event->moduleModel();
        $page = $event->item();

        $articles = $this->getNavigationArticles((int) $navi->id, (int) $page['id']);
        if (! $articles) {
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
            if (! isset($models[$article['article']])) {
                continue;
            }

            $renderer = new ArticleRenderer();
            $renderer->setArticle($models[$article['article']]);
            $renderer->setRenderContainer((bool) $article['container']);
            $renderer->setExcludeFromSearch((bool) $article['nosearch']);
            $renderer->setColumn('');
            $renderer->setCSSID($article['cssId']);

            if (isset($article['cssClass'])) {
                $renderer->addCSSClasses($article['cssClass']);
            }

            $page['hofff_navi_arts'][] = $renderer->render();
        }

        $page['hofff_navi_art'] = implode('', $page['hofff_navi_arts']);

        $cssId = StringUtil::deserialize($navi->cssID, true);

        if (strpos($cssId[1] ?? '', 'hofff-navi-art') === false) {
            $cssId[1]    = trim(($cssId[1] ?? '') . ' hofff-navi-art');
            $navi->cssID = $cssId;
        }

        $event->changeItem($page);
    }

    /** @return list<array<string,mixed>> */
    private function getNavigationArticles(int $moduleId, int $pageId): array
    {
        if (! isset($this->articles[$moduleId])) {
            $this->articles[$moduleId] = $this->fetchNavigationArticlesForModule($moduleId);
        }

        if (isset($this->articles[$moduleId][$pageId])) {
            return $this->articles[$moduleId][$pageId];
        }

        return [];
    }

    /** @return array<int,list<array<string,mixed>>> */
    private function fetchNavigationArticlesForModule(int $moduleId): array
    {
        $query    = 'SELECT * FROM tl_hofff_navi_art WHERE module = :module ORDER BY sorting';
        $result   = $this->connection->executeQuery($query, ['module' => $moduleId]);
        $articles = [];

        while ($row = $result->fetchAssociative()) {
            $articles[(int) $row['page']][] = $row;
        }

        return $articles;
    }

    /**
     * @param list<array<string,mixed>> $articles
     *
     * @return list<int>
     */
    private function getArticleIdsFromConfiguration(array $articles): array
    {
        return array_values(
            array_unique(
                array_map(
                    static fn (array $article) => (int) $article['article'],
                    $articles
                )
            )
        );
    }
}
