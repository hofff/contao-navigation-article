<?php

class NavigationArticleDCA extends Backend {

	public function getModules($objDC) {
		$objModules = Database::getInstance()->prepare(
			'SELECT	id, name
			FROM	tl_module'
		)->execute();

		$arrModules = array();
		while($objModules->next()) {
			$arrModules[$objModules->id] = $objModules->name;
		}
		return $arrModules;
	}

	public function editModule($objDC, $objWidget = null) {
		if(is_object($objWidget)) { // comes from MCW
			$intModuleID = $objWidget->value;
		} else {
			$intModuleID = $objDC->value;
		}

		if($intModuleID < 1) {
			return '';
		}

		return sprintf(
			' <a href="contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=%s" title="%s" style="padding-left:3px;">%s</a>',
			$intArticleID,
			sprintf(specialchars($GLOBALS['TL_LANG']['tl_page']['editalias'][1]), $intArticleID),
			$this->generateImage('alias.gif', $GLOBALS['TL_LANG']['tl_page']['editalias'][0], 'style="vertical-align:top;"')
		);
	}

	public function loadForPage($arrRows, $objDC) {
		return Database::getInstance()->prepare(
			'SELECT	j.*
			FROM	tl_bbit_navi_art AS j
			JOIN	tl_module AS m ON m.id = j.module
			JOIN	tl_theme AS t ON t.id = m.pid
			WHERE	j.page = ?
			ORDER BY t.name, m.name'
		)->execute($objDC->id)->fetchAllAssoc();
	}

	protected $arrSets;

	public function saveForPage($arrRows, $objDC) {
		$arrRows = deserialize($arrRows);
		$this->arrSets[$objDC->id] = array();

		foreach(array_values($arrRows) as $i => $arrRow) {
			$arrRow['page'] = $objDC->id;
			$arrRow['sorting'] = $i;
			$arrRow['module'] = intval($arrRow['module']);
			$arrRow['article'] = intval($arrRow['article']);
			if($arrRow['module'] > 0 && $arrRow['article'] > 0) {
				$this->arrSets[$objDC->id][] = $arrRow;
			}
		}

		return null;
	}

	public function submitPage($objDC) {
		if(!isset($this->arrSets[$objDC->id])) {
			return;
		}

		$objDB = Database::getInstance();
		$objDB->prepare(
			'DELETE FROM tl_bbit_navi_art WHERE page = ?'
		)->execute($objDC->id);

		if($this->arrSets[$objDC->id]) foreach($this->arrSets[$objDC->id] as $arrSet) {
			$objDB->prepare(
				'INSERT INTO tl_bbit_navi_art %s'
			)->set($arrSet)->execute();
		}
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
