<?php
/**
 * @project		WebApp Generator
 * @author		Olivier Gaillard
 * @version		1.0 du 04/06/2012
 * @desc	   	Initialisation des ressources
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

setlocale(LC_ALL, 'fr_FR');
date_default_timezone_set('Europe/Paris');

define('DS', '/');
define('PATH_APP', 			realpath(dirname (__FILE__).'/..'));
define('PATH_INC', 			PATH_APP.'/inc');
define('PATH_LANG',			PATH_APP.'/lang');
define('PATH_PROPERTIES', 	PATH_INC.'/properties');
define('PATH_CLASSES', 		PATH_INC.'/classes');
define('PATH_SMARTY', 		PATH_INC.'/smarty');

// autoloader de classes
function autoloader($class) {
	if (substr($class, 0, 7) == 'Smarty_') {
		require_once PATH_SMARTY.DS.'sysplugins'.DS.strtolower($class).'.php';
	}
	else {
		if ($class == 'Smarty') require_once PATH_SMARTY.DS.$class.'.class.php';
		else require_once PATH_CLASSES.DS.lcfirst($class).'.class.php';
	}
}
spl_autoload_register('autoloader');

// Paramètres de l'application
$properties_filepath = PATH_PROPERTIES.DS. $_SERVER["HTTP_HOST"].DS.'properties.ini';
if (!is_file($properties_filepath)) die("Unbale to find :  ".$properties_filepath);
$prop = parse_ini_file($properties_filepath);

// Paramètres des chemins d'accès
define('THEME',	'base'); 
define('PATH_TPL_RELATIVE',		'tpl'.DS.THEME); //
define('PATH_TPL', 				PATH_APP.DS.PATH_TPL_RELATIVE.DS); // Dossier des templates de vues

// Session
$session = Session::getInstance();

// Initialisation du gestionnaire de templates
$smarty = new Smarty;
$smarty->setTemplateDir(PATH_TPL);
$smarty->config_dir  =  'lang';
$smarty->compile_dir  = 'tpl_cache';
$smarty->compile_check = true;
$smarty->force_compile = false;
$smarty->assign('tpl', PATH_TPL_RELATIVE);

// Connexion à la base de données
$bdd = new PDO("mysql:host=".$prop['db_hostname'].";port=".$prop['db_port'].";dbname=".$prop['db_name'].";charset=utf8", $prop['db_username'], $prop['db_password']);
$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Log
$log = new Logger($bdd, $smarty, $session);

// Authentification
$user = new UserAuth($bdd);
// Profils
define('SUPER_ADMIN', 		300);
define('ADMIN', 			200);
define('USER', 				100);

$smarty->assign("login_name", $session->getValue("firstname")." ".$session->getValue("name"));


// Gestion des traductions
$_SESSION['filePathLang'] = PATH_LANG.DS."fr.txt";
if(isset($_GET['cnt'])) {
	if (in_array($_GET['cnt'], array('fr','en')))
		$_SESSION['filePathLang'] = PATH_LANG.DS.$_GET['cnt'].".txt";
	}
$translate = new Translator($_SESSION['filePathLang']); 

// Gestion des notifications
if ($alert = $session->getValue("alert")) {
	$smarty->assign("alert", $alert);
	$session->unsetKey("alert");
}
// Génération du menu dynamique
foreach (glob(PATH_APP. "/*.php") as $filename) {
	$fi = pathinfo($filename);
	if ($fi['filename'] != "index") {
		$smarty->append("choices", array("link" => $fi['basename'],"title" => $fi['filename']));
	}
}

?>