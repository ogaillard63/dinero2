<?php
/**
* @project		Dinero
* @author		Olivier Gaillard
* @version		1.0 du 11/12/2014
* @desc			Controleur des objets : utilisateurs
 */

require_once( "inc/prepend.php" );
$user->isLoggedIn(); // Espace privé
$user->isAllowed(SUPER_ADMIN);


// Récupération des variables
$action 			= Utils::get_input('action','both');
$id	 				= Utils::get_input('id','both');
$nom 				= Utils::get_input('nom','post');
$prenom 			= Utils::get_input('prenom','post');
$identifiant 		= Utils::get_input('identifiant','post');
$email 				= Utils::get_input('email','post');
$profil_id			= Utils::get_input('profil_id','post');
$mdp1 				= Utils::get_input('mdp1','post');
$mdp2 				= Utils::get_input('mdp2','post');
$expiration			= Utils::date2sql(Utils::get_input('expiration','post'));

$users_manager 		= new UsersManager($bdd);

$smarty->assign("page_title", "Utilisateurs");


switch($action) {
	
	case "add" :
		$dans_un_an = date("Y-m-d", time()+365*24*60*60); // dans 1 an
		$smarty->assign("user", new User(array("id" => -1, "expiration" => $dans_un_an)));
		$smarty->assign("profils", $users_manager->getProfilsList());
		$smarty->assign("form",	array("action" => "save", "bouton" => "Enregistrer"));
		$smarty->assign("content", "users/edit.tpl.html");
		$smarty->display("main.tpl.html");
		break;
	
	case "edit" :
		$smarty->assign("form",	array("action" => "save", "bouton" => "Enregistrer les modifications"));
		$smarty->assign("user", $users_manager->getUser($id));
		$smarty->assign("profils", $users_manager->getProfilsList());
		$smarty->assign("content","users/edit.tpl.html");
		$smarty->display("main.tpl.html");
		break;

	case "save" :
		if ((strlen($mdp1)) && ($mdp1 == $mdp2)) {
			$data = array("id" => $id, "nom" => $nom, "prenom" => $prenom, "identifiant" => $identifiant, 
				"email" => $email, "mdp" =>  MD5($mdp1), "profil_id" => $profil_id, "expiration" => $expiration);
			$users_manager->saveUser(new User($data));
			$log->notification($translate->__('the_user_account_has_been_saved'));
		}
		else {
			$log->notification($translate->__('check password'), "error");
		}
		Utils::redirection("users.php");
		break;

	case "delete" :
		$user = $users_manager->getUser($id);
		if ($users_manager->deleteUser($user))
			$log->notification($translate->__('the_user_account_has_been_deleted'));
		else 
			$log->notification($translate->__('deletion_is_not_possible'), "error");
		Utils::redirection("users.php");
		break;

	default:
		$smarty->assign("titre", $translate->__('list_of_users'));
		$smarty->assign("users", $users_manager->getUsers());
		$smarty->assign("content", "users/list.tpl.html");
		$smarty->display("main.tpl.html");
}
require_once( "inc/append.php" );
?>