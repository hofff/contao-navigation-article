<?php

class NavigationArticle extends Frontend
{

    public function hook_bbit_navi_item($navi, &$page)
    {
        $articles = $this->getNavigationArticles($navi->id, $page['id']);
        if (!$articles) {
            return;
        }
        foreach ($articles as $article) {
            $page['bbit_navi_arts'][] = IncludeArticleUtils::generateArticle(
                $article['article'],
                $article['nosearch'],
                $article['container'],
                null,
                $article['cssID']
            );
        }
        $page['bbit_navi_art'] = implode('', $page['bbit_navi_arts']);
    }

    protected $arrArticles;

    protected function getNavigationArticles($intModuleID, $intPageID)
    {
        if (!isset($this->arrArticles[$intModuleID])) {
            $this->arrArticles[$intModuleID] = $this->fetchNavigationArticlesForModule($intModuleID);
        }

        return (array) $this->arrArticles[$intModuleID][$intPageID];
    }

    protected function fetchNavigationArticlesForModule($intModuleID)
    {
        $objArticles = Database::getInstance()->prepare(
            'SELECT		*
			FROM		tl_bbit_navi_art
			WHERE		module = ?
			ORDER BY	sorting'
        )->execute($intModuleID);

        $arrArticles = [];

        while ($objArticles->next()) {
            $arrArticles[$objArticles->page][] = $objArticles->row();
        }

        return $arrArticles;
    }

    /**
     * @param integer $intModuleID
     * @param integer $intPageID
     *
     * @return array
     * @deprecated use getNavigationArticles
     */
    protected function getNavigationArticle($intModuleID, $intPageID)
    {
        $articles = $this->getNavigationArticles($intModuleID, $intPageID);

        return $articles[0];
    }

    public function __construct()
    {
        parent::__construct();
    }

    private static $objInstance;

    public static function getInstance()
    {
        if (!isset(self::$objInstance)) {
            self::$objInstance = new self();
        }

        return self::$objInstance;
    }

}
