<?php
/**
* @project		Dinero
* @author		Olivier Gaillard
* @version		1.0 du 11/12/2014
* @desc			Controleur des objets : banks
*/
use App\Utils;
use App\BankManager;

require_once( "inc/prepend.php" );
$user->isLoggedIn(); // Espace privé
//$user->isAllowed(SUPER_ADMIN);

// Récupération des variables
$action			= Utils::get_input('action','both');
$id				= Utils::get_input('id','both');
$name			= Utils::get_input('name','post');
$logo			= Utils::get_input('logo','post');
$color			= Utils::get_input('color','post');

$banks_manager = new BankManager($bdd);
$smarty->assign("page_title", "Banques");

switch($action) {
	
	case "add" :
		$smarty->assign("bank", new Bank(array("id" => -1)));
		$smarty->assign("content", "banks/edit.tpl.html");
		$smarty->display("main.tpl.html");
		break;
	
	case "edit" :
		$smarty->assign("bank", $banks_manager->getBank($id));
		$smarty->assign("content","banks/edit.tpl.html");
		$smarty->display("main.tpl.html");
		break;

	case "save" :
		$data = array("id" => $id, "name" => $name, "logo" => $logo, "color" => $color);
		$banks_manager->saveBank(new Bank($data));
		$log->notification($translate->__('the_bank_has_been_saved'));
		Utils::redirection("banks.php");
		break;

	case "delete" :
		$bank = $banks_manager->getBank($id);
		if ($banks_manager->deleteBank($bank))
			$log->notification($translate->__('the_bank_has_been_deleted'));
		else 
			$log->notification($translate->__('deletion_is_not_possible'), "error");
		Utils::redirection("banks.php");
		break;

	default:
		$smarty->assign("titre", $translate->__('list_of_banks'));
		$smarty->assign("banks", $banks_manager->getBanks());
		$smarty->assign("content", "banks/list.tpl.html");
		$smarty->display("main.tpl.html");
}
require_once( "inc/append.php" );
?>