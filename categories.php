<?php
/**
* @project		Dinero
* @author		Olivier Gaillard
* @version		1.0 du 11/12/2014
* @desc			Controleur des objets : categories
*/
use App\Utils;
use App\CategoryManager;

require_once( "inc/prepend.php" );
$user->isLoggedIn(); // Espace privé
$user->isAllowed(SUPER_ADMIN);

// Récupération des variables
$action			= Utils::get_input('action','both');
$id				= Utils::get_input('id','both');
$page			= Utils::get_input('page','both');
$parent_id		= Utils::get_input('parent_id','post');
$name			= Utils::get_input('name','post');
$type_flag		= Utils::get_input('type_flag','post');
$active_flag	= Utils::get_input('active_flag','post');

$categories_manager = new CategoryManager($bdd);
$smarty->assign("page_title", "Categories");

switch($action) {
	
	case "add" :
		$smarty->assign("category", new Category(array("id" => -1)));
		$smarty->assign("categories", $categories_manager->getParentCategoriesForSelect());
		$smarty->assign("content", "categories/edit.tpl.html");
		$smarty->display("main.tpl.html");
		break;
	
	case "edit" :
		$smarty->assign("category", $categories_manager->getCategory($id));
		$smarty->assign("categories", $categories_manager->getParentCategoriesForSelect());
		$smarty->assign("content","categories/edit.tpl.html");
		$smarty->display("main.tpl.html");
		break;

	case "save" :
		$data = array("id" => $id, "parent_id" => $parent_id, "name" => $name, "type_flag" => $type_flag, "active_flag" => $active_flag);
		$categories_manager->saveCategory(new Category($data));
		$log->notification($translate->__('the_category_has_been_saved'));
		Utils::redirection("categories.php");
		break;

	case "delete" :
		$category = $categories_manager->getCategory($id);
		if ($categories_manager->deleteCategory($category)) {
			$log->notification($translate->__('the_category_has_been_deleted'));
		}
		Utils::redirection("categories.php");
		break;

	default:
		$smarty->assign("titre", $translate->__('list_of_categories'));
		/*$rpp = 5;
		if (empty($page)) $page = 1; // Display first pagination page
		$smarty->assign("categories", $categories_manager->getCategoriesByPage($category_id, $page, $rpp));
		$pagination = new Pagination($page, $categories_manager->getMaxCategories($category_id), $rpp);
		$smarty->assign("btn_nav", $pagination->getNavigation());
		//$smarty->assign("category_id", $category_id);
		*/
		$smarty->assign("categories", $categories_manager->getCategories());
		$smarty->assign("content", "categories/list.tpl.html");
		$smarty->display("main.tpl.html");
}
require_once( "inc/append.php" );
?>