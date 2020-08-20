<?php

require_once ROOT_DIR  . '/Action.php';
class GroupedWork_Home extends Action{
	/** @var GroupedWorkDriver $recordDriver */
	private $recordDriver;
	private $lastSearch;

	function launch() {
		global $interface;
		global $timer;
		global $logger;

		$id = strip_tags($_REQUEST['id']);

		require_once ROOT_DIR . '/RecordDrivers/GroupedWorkDriver.php';
		$this->recordDriver = new GroupedWorkDriver($id);
		if (!$this->recordDriver->isValid){
			$interface->assign('id', $id);
			$logger->log("Did not find a record for id {$id} in solr." , Logger::LOG_DEBUG);
			$this->display('../Record/invalidRecord.tpl', 'Invalid Record');
			die();
		}
		$interface->assign('recordDriver', $this->recordDriver);
		$timer->logTime('Loaded Grouped Work Driver');

		//For display in metadata
		$interface->assign('description', $this->recordDriver->getDescriptionFast(true));

		// Set Show in Search Results Main Details Section options for template
		// (needs to be set before moreDetailsOptions)
		global $library;
		foreach ($library->getGroupedWorkDisplaySettings()->showInMainDetails as $detailOption) {
			$interface->assign($detailOption, true);
		}

		$this->recordDriver->assignBasicTitleDetails();
		$timer->logTime('Initialized the Record Driver');

		// Retrieve User Search History
		$this->lastSearch = isset($_SESSION['lastSearchURL']) ? $_SESSION['lastSearchURL'] : false;
		$interface->assign('lastSearch', $this->lastSearch);

		//Get Next/Previous Links
		$searchSource = !empty($_REQUEST['searchSource']) ? $_REQUEST['searchSource'] : 'local';
		/** @var SearchObject_GroupedWorkSearcher $searchObject */
		$searchObject = SearchObjectFactory::initSearchObject();
		$searchObject->init($searchSource);
		$searchObject->getNextPrevLinks();
		$timer->logTime('Got next and previous links');

		$interface->assign('moreDetailsOptions', $this->recordDriver->getMoreDetailsOptions());
		$timer->logTime('Got more details options');

		$exploreMoreInfo = $this->recordDriver->getExploreMoreInfo();
		$interface->assign('exploreMoreInfo', $exploreMoreInfo);
		$timer->logTime('Got explore more information');

		$interface->assign('metadataTemplate', 'GroupedWork/metadata.tpl');

		$interface->assign('semanticData', json_encode($this->recordDriver->getSemanticData()));
		$timer->logTime('Loaded semantic data');

		// Display Page
		$this->display('full-record.tpl', $this->recordDriver->getTitle(),'Search/home-sidebar.tpl', false);
	}

	function getBreadcrumbs()
	{
		$breadcrumbs = [];
		if (!empty($this->lastSearch)){
			$breadcrumbs[] = new Breadcrumb($this->lastSearch, 'Catalog Search Results');
		}
		$breadcrumbs[] = new Breadcrumb('', $this->recordDriver->getTitle(), false);
		return $breadcrumbs;
	}
}