<?php
/**
 * @project		Affichage dynamique
 *
 * @author		Olivier Gaillard <olivier.gaillard@centrefrance.com>
 * @version		1.1 du 27/04/2013
 * @desc	   	Gestion des users
 */

class UsersManager {
	protected $bdd;
	protected $profils = array("100" => "User",
			"200" => "Administrateur", "300" => "Super-Administrateur");

	public function __construct(PDO $bdd) {
		$this->bdd = $bdd;
	}

	/**
	 * Retourne l'objet user correspondant à l'Id
	 * @param $id
	 */
	public function getUser($id) {
		if ($id) {
			$q = $this->bdd->prepare("SELECT * FROM users WHERE id = :id");
			$q->bindValue(':id', $id, PDO::PARAM_INT);
			$q->execute();
			return new User($q->fetch(PDO::FETCH_ASSOC));
		}
	}

	/**
	 * Retourne la liste des users
	 */
	public function getUsers() {
		$users = array();
		$q = $this->bdd->prepare('SELECT * FROM users ORDER BY profil_id DESC, name');
		$q->execute();
		while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
			$users[] = new User($data);
		}
		return $users;
	}

	/**
	 * Efface l'objet user de la bdd
	 * @param User $user
	 */
	public function deleteUser(User $user) {
		$q = $this->bdd->prepare("DELETE FROM users WHERE id = :id");
		$q->bindValue(':id', $user->getId(), PDO::PARAM_INT);
		return $q->execute();
	}

	/**
	 * Enregistre l'user en bdd
	 * @param User $user
	 */
	public function saveUser(User $user) {
		if ($user->getId() == -1) {
			$q = $this->bdd->prepare('INSERT INTO users SET name = :name, firstname = :firstname, login = :login, 
			email = :email, profil_id = :profil_id, password = :password, expiration = :expiration');
		} else {
			$q = $this->bdd->prepare('UPDATE users SET name = :name, firstname = :firstname, login = :login, 
			email = :email, profil_id = :profil_id, password = :password, expiration = :expiration WHERE id = :id');
			$q->bindValue(':id', $user->getId(), PDO::PARAM_INT);
		}
		$q->bindValue(':name', $user->getNom(), PDO::PARAM_STR);
		$q->bindValue(':firstname', $user->getFirstname(), PDO::PARAM_STR);
		$q->bindValue(':login', $user->getLogin(), PDO::PARAM_STR);
		$q->bindValue(':email', $user->getEmail(), PDO::PARAM_STR);
		$q->bindValue(':profil_id', $user->getProfilId(), PDO::PARAM_INT);
		$q->bindValue(':password', $user->getPassword(), PDO::PARAM_STR);
		$q->bindValue(':expiration', $user->getExpiration(), PDO::PARAM_STR);
		$q->execute();
		//$q->debugDumpParams();
		//die();
		if ($user->getId() == -1)
			$user->setId($this->bdd->lastInsertId());
	}

	/**
	 * Renvoi true si la date d'expiration du compte user est dépassée
	 * @param User $user
	 */
	public function isExpired(User $user) {
		// TODO : Methode à ecrire
		return false;
	}

	/**
	 * Retourne la liste des profils
	 */
	public function getProfilsList() {
		return $this->profils;
	}

}
?>