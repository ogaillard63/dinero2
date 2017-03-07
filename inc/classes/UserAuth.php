<?php

/**
 * @project		WebApp Base
 *
 * @author		Olivier Gaillard <ogaillard63@gmail.com>
 * @version		1.0 du 13/03/2014
 * @desc	   	Authentification des users
 *
 **/

namespace App;
use PDO;

class UserAuth extends User {
	
	// Déclaration des variables
	protected $bdd;

	const MEMBER_PAGE			= "index.php";
	const LOGIN_PAGE			= "identification.php";
	const LOGOUT_PAGE 			= "index.php";
	const MSG_ERROR_FORM		= "Merci de compléter le formulaire";
	const MSG_SESSION_EXPIRED	= "Désolé, votre session a expirée !";
	const MSG_ERROR_USER 		= "Identifiant et/ou mot de passe incorrect !";
	const IDLE_TIME 			= 900; // Expiration de la session aprés 15*60 secondes

	/**
	 * Constructeur
	 **/
	public function __construct(PDO $bdd) {
		$this->bdd = $bdd;
	}
	
	/**
	 * Vérifie si l'user est enregistrée en session
	 * @return 	bool
	 **/
	function verifySession() {
		if (!isset($_SESSION["login"]))
		return false;
		else {
			if ( time() - $_SESSION["idle"] > self::IDLE_TIME ) {
				$_SESSION["idle"] = 0;
				return false;
			}
			else {
				$_SESSION["idle"] = time();
				return true;
			}
		}

	}

	/**
	 * Redirection du navigateur
	 **/
	function redirect($page) {
		header("Location: ".$page);
		exit();
	}

	/**
	 * @desc 	Verifie la validité de l'login et du mot de passe avec la Bdd.
	 * @return 	bool
	 **/
	function verifyDB() {
		$q = $this->bdd->prepare("SELECT * FROM users WHERE login = :login AND password = :password AND expiration > CURRENT_DATE");
		$q->bindValue(':login', $this->login, PDO::PARAM_INT);
		$q->bindValue(':password', $this->password, PDO::PARAM_INT);
		if ($q->execute()) {
			if ($data = $q->fetch(PDO::FETCH_ASSOC)) {
				$this->hydrate($data);
				return true;
			}
		}
		return false;
	}

	/**
	 * @desc Sauvegarde en session des données user
	 **/
	function writeSession() {
		$q = $this->bdd->prepare('UPDATE users SET last_cnx = CURRENT_TIMESTAMP WHERE id = :id');
		$q->bindValue(':id', $this->getId(), PDO::PARAM_INT);
		$q->execute();	
		
		$_SESSION["id"]   				= $this->getId();
		$_SESSION["login"]  			= $this->getLogin();
		$_SESSION["name"]  				= $this->getName();
		$_SESSION["firstname"]  		= $this->getFirstname();
		$_SESSION["email"] 				= $this->getEmail();
		$_SESSION["profil_id"]			= $this->getProfilId();
		$_SESSION["idle"]      			= time();
	}


	/**
	 * @desc 	Vérifie la validité des informations saisies dans le formulaire
	 * @return 	bool
	 **/
	function verifyForm() {
		if (isset($_POST["login"]) && isset($_POST["password"]) && $_POST["login"] != "" && $_POST["password"] != "") {
			$this->login = trim($_POST["login"]);
			$this->password = trim(md5($_POST["password"]));
			if (isset($_POST["referer"])) $this->referer = $_POST["referer"];
			return true;
		}
		else return false;
	}

	/**
	 * Connexion de l'user
	 **/
	function login($referer = null) {
		// si l'user est déjà connecté
		if ($this->verifySession()) {
			$this->redirect(self::MEMBER_PAGE);
		}
		// vérification du formulaire de connexion
		if (!$this->verifyForm()) {
			if (isset($_POST["login"]) && isset($_POST["password"])) return self::MSG_ERROR_FORM;
			else if (isset($_SESSION)) return null;
		}
		// vérification des données du formulaire avec la bdd
		else {
			if (!$this->verifyDB())
			return self::MSG_ERROR_USER;
			else {
				$this->writeSession();
				if ($this->referer != null) $this->redirect($this->referer);
				else $this->redirect(self::MEMBER_PAGE);
			}
		}
	}

	/**
	 * Deconnexion de l'user
	 **/
	function logout() {
		$_SESSION = array();
		session_unset();
		session_destroy();
		unset($_SESSION); // TODO : est ce vraiment utile ?
		$this->redirect(self::LOGOUT_PAGE);
	}

	/**
	 * Vérifie si l'user est connecté
	 **/
	function isLoggedIn() {
		// verify if user is already logged in
		if (!$this->verifySession()) {
			$this->redirect(self::LOGIN_PAGE);
		}
	}
	/*function isLoggedIn() {
		if ($this->verifySession()) {
			return true;
		}
		return false;
	}*/



	/**
	 * @desc 	Retourne le profil de l'user
	 * @return 	login
	 **/
	function getProfil() {
		if (isset($_SESSION["profil_id"]))
		return $_SESSION["profil_id"];
	}

	/**
	 * Vérifie les droits de l'user
	 * @return 	login
	 **/
	function isAllowed($allow_profil) {
		if (isset($_SESSION["profil_id"])) {
			if 	($allow_profil <=$_SESSION["profil_id"]) return true;
		}
		$this->logout(); // L'user n'a pas les droits, il est deconnecté
		exit();
	}
}
?>