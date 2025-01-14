<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/Admin.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/ECommerce/CertifiedPaymentsByDeluxeSetting.php';

class Admin_CertifiedPaymentsByDeluxeSettings extends ObjectEditor {
	function getObjectType(): string {
		return 'CertifiedPaymentsByDeluxeSetting';
	}

	function getToolName(): string {
		return 'CertifiedPaymentsByDeluxeSettings';
	}

	function getPageTitle(): string {
		return 'Certified Payments by Deluxe Settings';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$list = [];

		$object = new CertifiedPaymentsByDeluxeSetting();
		$object->orderBy($this->getSort());
		$this->applyFilters($object);
		$object->limit(($page - 1) * $recordsPerPage, $recordsPerPage);
		$object->find();
		while ($object->fetch()) {
			$list[$object->id] = clone $object;
		}

		return $list;
	}

	function getDefaultSort(): string {
		return 'name asc';
	}

	function getObjectStructure($context = ''): array {
		return CertifiedPaymentsByDeluxeSetting::getObjectStructure($context);
	}

	function getPrimaryKeyColumn(): string {
		return 'id';
	}

	function getIdKeyColumn(): string {
		return 'id';
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#ecommerce', 'eCommerce');
		$breadcrumbs[] = new Breadcrumb('', 'Certified Payments by Deluxe Settings');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'ecommerce';
	}

	function canView(): bool {
		return UserAccount::userHasPermission('Administer Certified Payments by Deluxe');
	}
}