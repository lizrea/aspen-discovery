<?php
require_once ROOT_DIR . '/GroupedWorkSubRecordHomeAction.php';
require_once ROOT_DIR . '/sys/RBdigital/RBdigitalMagazine.php';
require_once ROOT_DIR . '/RecordDrivers/RBdigitalMagazineDriver.php';

class RBdigitalMagazine_Home extends GroupedWorkSubRecordHomeAction{

	function launch(){
		global $interface;

		if (!$this->recordDriver->isValid()){
			$this->display('../Record/invalidRecord.tpl', 'Invalid Record');
			die();
		}

		$groupedWork = $this->recordDriver->getGroupedWorkDriver();
		if (is_null($groupedWork) || !$groupedWork->isValid()){  // initRecordDriverById itself does a validity check and returns null if not.
			$this->display('../Record/invalidRecord.tpl', 'Invalid Record');
			die();
		}else{
			$interface->assign('recordDriver', $this->recordDriver);
			$interface->assign('groupedWorkDriver', $this->recordDriver->getGroupedWorkDriver());

			//Load status summary
			$holdingsSummary = $this->recordDriver->getStatusSummary();
			$interface->assign('holdingsSummary', $holdingsSummary);

			//Load the citations
			$this->loadCitations($this->recordDriver);

			// Retrieve User Search History
			$this->lastSearch = isset($_SESSION['lastSearchURL']) ? $_SESSION['lastSearchURL'] : false;
			$interface->assign('lastSearch', $this->lastSearch);

			//Get Next/Previous Links
			$searchSource = !empty($_REQUEST['searchSource']) ? $_REQUEST['searchSource'] : 'local';
			$searchObject = SearchObjectFactory::initSearchObject();
			$searchObject->init($searchSource);
			$searchObject->getNextPrevLinks();

			// Set Show in Main Details Section options for templates
			// (needs to be set before moreDetailsOptions)
			global $library;
			foreach ($library->getGroupedWorkDisplaySettings()->showInMainDetails as $detailoption) {
				$interface->assign($detailoption, true);
			}

			$interface->assign('moreDetailsOptions', $this->recordDriver->getMoreDetailsOptions());

			$interface->assign('semanticData', json_encode($this->recordDriver->getSemanticData()));

			// Display Page
			$this->display('full-record.tpl', $this->recordDriver->getTitle(), 'Search/home-sidebar.tpl', false);

		}
	}

	function loadRecordDriver($id){
		$this->recordDriver = new RBdigitalMagazineDriver($id);
	}

}