<?php
/**
* @project		Dinero
* @author		Olivier Gaillard
* @version		1.0 du 11/12/2014
* @desc			Controleur des objets : accounts
*/

require_once( "inc/prepend.php" );
$user->isLoggedIn(); // Espace privé
$user->isAllowed(SUPER_ADMIN);

// Récupération des variables
$action				= Utils::get_input('action','both');
$id					= Utils::get_input('id','both');
$page				= Utils::get_input('page','both');
$bank_id			= Utils::get_input('bank_id','both');
$user_id			= Utils::get_input('user_id','post');
$name				= Utils::get_input('name','post');
$account_number		= Utils::get_input('account_number','post');
$sort				= Utils::get_input('sort','post');
$state				= Utils::get_input('state','post');
$initial_balance	= Utils::get_input('initial_balance','post');
$current_balance	= Utils::get_input('current_balance','post');

$account_manager = new AccountManager($bdd);
$smarty->assign("page_title", "Comptes");

switch($action) {
	
	case "add" :
		$smarty->assign("account", new Account(array("id" => -1)));
		$banks_manager = new BankManager($bdd);
		$smarty->assign("banks", $banks_manager->getBanksForSelect());
		$smarty->assign("content", "accounts/edit.tpl.html");
		$smarty->display("main.tpl.html");
		break;
	
	case "edit" :
		$smarty->assign("account", $account_manager->getAccount($id));
		$banks_manager = new BankManager($bdd);
		$smarty->assign("banks", $banks_manager->getBanksForSelect());
		$smarty->assign("content","accounts/edit.tpl.html");
		$smarty->display("main.tpl.html");
		break;

	case "save" :
		$data = array("id" => $id, "bank_id" => $bank_id, "user_id" => $user_id, "name" => $name, "account_number" => $account_number, "sort" => $sort, "state" => $state, "initial_balance" => $initial_balance, "current_balance" => $current_balance);
		$account_manager->saveAccount(new Account($data));
		// Update balance
		if ($id > 0) $account_manager->updateCurrentBalance($id);

		$log->notification($translate->__('the_account_has_been_saved'));
		Utils::redirection("accounts.php");
		break;

	case "delete" :
		$account = $account_manager->getAccount($id);
		if ($account_manager->deleteAccount($account) != false)
			$log->notification($translate->__('the_account_has_been_deleted'));
		else 
			$log->notification($translate->__('deletion_is_not_possible'), "error");
		Utils::redirection("accounts.php");
		break;

	case "delete" :
		$account = $account_manager->getAccount($id);
		if ($account_manager->deleteAccount($account) != false)
			$log->notification($translate->__('the_account_has_been_deleted'));
		else 
			$log->notification($translate->__('deletion_is_not_possible'), "error");
		Utils::redirection("accounts.php");
		break;
	
	case "delete_transactions" :
		$transaction_manager = new TransactionManager($bdd);
		if ($transaction_manager->deleteAllTransactions($id) != false) {
			$account_manager->updateCurrentBalance($id);
			$log->notification($translate->__('the_transactions_have_been_deleted'));
		}
		else 
			$log->notification($translate->__('deletion_is_not_possible'), "error");
		Utils::redirection("accounts.php");
		break;

	default:
		$rpp = 15;
		if (empty($page)) $page = 1; // default page
		$smarty->assign("titre", $translate->__('list_of_accounts'));
		$smarty->assign("accounts", $account_manager->getAccountsByPage($bank_id, $page, $rpp));
		$pagination = new Pagination($page, $account_manager->getMaxAccounts($bank_id), $rpp);
		$smarty->assign("btn_infos", $pagination->getNavigation());
		$smarty->assign("bank_id", $bank_id);
		$smarty->assign("content", "accounts/list.tpl.html");
		$smarty->display("main.tpl.html");
}
require_once( "inc/append.php" );
?>