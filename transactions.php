<?php
/**
* @project		Dinero
* @author		Olivier Gaillard
* @version		1.0 du 11/12/2014
* @desc			Controleur des objets : transactions
*/
use App\Utils;
use App\Transaction;
use App\TransactionManager;
use App\CategoryManager;
use App\AccountManager;
use App\Pagination;

require_once( "inc/prepend.php" );
$user->isLoggedIn(); // Espace privé
$user->isAllowed(SUPER_ADMIN);

// Récupération des variables
$action			= Utils::get_input('action','both');
$id				= Utils::get_input('id','both');
$page			= Utils::get_input('page','both');
$query			= Utils::get_input('query','both');
$account_id		= Utils::get_input('account_id','both');
$date			= Utils::get_input('date','post');
$title			= Utils::get_input('title','post');
$amount			= Utils::get_input('amount','post');
$category_id	= Utils::get_input('category_id','post');
$comment		= Utils::get_input('comment','post');
$internal		= Utils::get_input('internal','post');
$state			= Utils::get_input('state','post');
$import	= Utils::get_input('import','post');
$last_mod		= Utils::get_input('last_mod','post');
$import			= Utils::get_input('import','post');

$transactions_manager = new TransactionManager($bdd);
$categories_manager = new CategoryManager($bdd);
$account_manager = new AccountManager($bdd);
$smarty->assign("page_title", "Transactions");
if (empty($page)) $page = 1; // Display first pagination page
$smarty->assign("page", $page);

// Récupére l'id du compte
if (!empty($account_id)) $session->setValue("account_id", $account_id);
else $account_id = $session->getValue("account_id");
$smarty->assign("account_id", $account_id);

switch($action) {
	
	case "import" :
		$smarty->assign("content", "transactions/import_modal.tpl.html");
		$smarty->display("modal.tpl.html");
		break;

	case "import_save" :
		// reset import
		$transactions_manager->resetImport($account_id);
		
		$lines = explode("\n", $import);
		foreach ($lines as $line) {
			$parts = explode("|", $line);
			$data = array("id" => -1, "account_id" => $account_id, "date" => utils::dateToSql($parts[0]), 
			"title" => $parts[1], "amount" => str_replace(",", ".", $parts[2]), "category_id" => 0, 
			"comment" => "", "internal" => 0, "state" => 0, "import" => 1);
			$transactions_manager->saveTransaction(new Transaction($data));

		}
		// Update balance
		$account_manager->updateCurrentBalance($account_id);
		$log->notification($translate->__('new_transactions_have_been_imported'));
		Utils::redirection("transactions.php"); // retourne en page 1
		break;
	
	case "import_cancel" :
		$transactions_manager->deleteLastImport($account_id);
		// Update balance
		$account_manager->updateCurrentBalance($account_id);
		$log->notification($translate->__('last_import_has_been_canceled'));
		Utils::redirection("transactions.php"); // retourne en page 1
		break;
		
	case "add" :
		$smarty->assign("transaction", new Transaction(array("id" => -1)));
		$smarty->assign("categories", $categories_manager->getCategoriesForSelect());
		$smarty->assign("content", "transactions/edit_modal.tpl.html");
		$smarty->display("modal.tpl.html");
		break;
	
	case "edit" :
		$smarty->assign("transaction", $transactions_manager->getTransaction($id));
		$smarty->assign("categories", $categories_manager->getCategoriesForSelect());
		$smarty->assign("query",$query);
		$smarty->assign("content", "transactions/edit_modal.tpl.html");
		$smarty->display("modal.tpl.html");
		break;

	case "search" :
		$smarty->assign("accounts", $account_manager->getActiveAccounts());
		$smarty->assign("content","transactions/search.tpl.html");
		$smarty->display("main.tpl.html");
		break;

	case "search_results" :
		$smarty->assign("accounts", $account_manager->getActiveAccounts());
		if (strlen($query) > 2) {
			$smarty->assign("transactions", $transactions_manager->searchTransactions($query, $account_id));
		}
		else {
			$log->notification($translate->__('query_too_short'));
			Utils::redirection("transactions.php?action=search&account_id=". $account_id);
		}
		$smarty->assign("query",$query);
		$smarty->assign("content","transactions/search.tpl.html");
		$smarty->display("main.tpl.html");
		break;

	case "save" :
		$data = array("id" => $id, "account_id" => $account_id, "date" => utils::dateToSql($date), "title" => $title, 
		"amount" => $amount, "category_id" => $category_id, "comment" => $comment, "internal" => $internal, "state" => $state, 
		"import" => $import, "last_mod" => $last_mod);
		$transactions_manager->saveTransaction(new Transaction($data));
		$log->notification($translate->__('the_transaction_has_been_saved'));
		
		// Update balance
		$account_manager->updateCurrentBalance($account_id);
		if(!empty($query)) Utils::redirection("transactions.php?action=search_results&query=".$query);
		else Utils::redirection("transactions.php?page=".$page);
		break;

	case "delete" :
		$transaction = $transactions_manager->getTransaction($id);
		if ($transactions_manager->deleteTransaction($transaction)) {
			// Update balance
			$account_manager->updateCurrentBalance($account_id);
			$log->notification($translate->__('the_transaction_has_been_deleted'));
			}
		Utils::redirection("transactions.php?page=".$page);
		break;
	
	case "check" :
		$transaction = $transactions_manager->getTransaction($id);
		if ($transaction->state == 0) {
			$transaction->state = 1;
			$log->notification($translate->__('the_transaction_has_been_checked'));	
		}
		else {
			$transaction->state = 0;
			$log->notification($translate->__('the_transaction_has_been_unchecked'));	
			}
		$transactions_manager->saveTransaction($transaction);
		Utils::redirection("transactions.php?page=".$page);
		break;
	
	default:
		$rpp = 15;
		$smarty->assign("titre", $translate->__('list_of_transactions'));
		$smarty->assign("transactions", $transactions_manager->getTransactionsByPage($account_id, $page, $rpp));
		$pagination = new Pagination($page, $transactions_manager->getMaxTransactions($account_id), $rpp);
		$smarty->assign("btn_nav", $pagination->getNavigation());
		// Liste des comptes pour le menu déroulant
		$smarty->assign("accounts", $account_manager->getActiveAccounts());
		// Solde du compte
		$smarty->assign("balance", $account_manager->getCurrentBalance($account_id));
		// Date de la dernière opération
		$smarty->assign("last_date", $transactions_manager->getLastTransactionDate($account_id));
		$smarty->assign("content", "transactions/list.tpl.html");
		$smarty->display("main.tpl.html");
}
require_once( "inc/append.php" );
?>