<?php

class NavigationArticle extends Frontend {

	public function hook_bbit_navi_item($objNavi, &$arrPage) {
		$arrArticle = $this->getNavigationArticle($objNavi->id, $arrPage['id']);
		$arrArticle && $arrPage['bbit_navi_art'] = IncludeArticleUtils::generateArticle(
			$arrArticle['article'],
			$arrArticle['nosearch'],
			$arrArticle['container'],
			null,
			$arrArticle['cssID']
		);
	}

	protected $arrArticles;

	protected function getNavigationArticle($intModuleID, $intPageID) {
		if(!isset($this->arrArticles[$intModuleID])) {
			$this->arrArticles[$intModuleID] = $this->fetchNavigationArticlesForModule($intModuleID);
		}
		return $this->arrArticles[$intModuleID][$intPageID];
	}

	protected function fetchNavigationArticlesForModule($intModuleID) {
		$objArticles = Database::getInstance()->prepare(
			'SELECT	*
			FROM	tl_bbit_navi_art
			WHERE	module = ?'
		)->execute($intModuleID);

		$arrArticles = array();

		while($objArticles->next()) {
			$arrArticles[$objArticles->page] = $objArticles->row();
		}

		return $arrArticles;
	}

	protected function __construct() {
		parent::__construct();
	}

	private static $objInstance;

	public static function getInstance() {
		if(!isset(self::$objInstance))
			self::$objInstance = new self();

		return self::$objInstance;
	}

}
