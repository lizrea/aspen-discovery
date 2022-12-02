<?php

require_once ROOT_DIR . '/services/MyAccount/MyAccount.php';

class MyAccount_OverDriveOptions extends MyAccount {
	function launch() {
		global $interface;
		$user = UserAccount::getLoggedInUser();

		if ($user) {
			// Determine which user we are showing/updating settings for
			$linkedUsers = $user->getLinkedUsers();

			$patronId = isset($_REQUEST['patronId']) ? $_REQUEST['patronId'] : $user->id;
			/** @var User $patron */
			$patron = $user->getUserReferredTo($patronId);

			// Linked Accounts Selection Form set-up
			if (count($linkedUsers) > 0) {
				array_unshift($linkedUsers, $user); // Adds primary account to list for display in account selector
				$interface->assign('linkedUsers', $linkedUsers);
				$interface->assign('selectedUser', $patronId);
			}

			// Save/Update Actions
			global $offlineMode;
			if (isset($_POST['updateScope']) && !$offlineMode) {
				$patron->updateOverDriveOptions();

				session_write_close();
				$actionUrl = '/MyAccount/OverDriveOptions' . ($patronId == $user->id ? '' : '?patronId=' . $patronId); // redirect after form submit completion
				header("Location: " . $actionUrl);
				exit();
			} elseif (!$offlineMode) {
				$currentOptions = $patron->getOverDriveOptions();
				$interface->assign('options', $currentOptions);
				$interface->assign('edit', true);
			} else {
				$interface->assign('edit', false);
			}

			$interface->assign('profile', $patron);
		}

		$this->display('overDriveOptions.tpl', 'Account Settings');
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/MyAccount/Home', 'Your Account');
		$breadcrumbs[] = new Breadcrumb('', 'OverDrive Options');
		return $breadcrumbs;
	}
}