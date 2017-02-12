<?php
/**
 * @project		Travel Planner
 *
 * @author		Olivier Gaillard <olivier.gaillard@centrefrance.com>
 * @version		1.0 du 18/04/2014
 * @desc	   	Objet User
 */

class User {
	
	public $id; 
	public $name;
	public $firstname;
	public $login;
	public $email;
	public $profil_id;
	public $password;
	public $expiration;
	public $last_cnx;
	
	public function __construct(array $data) {
		$this->hydrate($data);
	}

	public function hydrate(array $data){
		foreach ($data as $key => $value) {
			if (strpos($key, "_") !== false) {
				$method = 'set';
				foreach (explode("_", $key) as $part) {
					$method .= ucfirst($part);
				}
			}
			else $method = 'set'.ucfirst($key);
			if (method_exists($this, $method)) {
				$this->$method($value);
			}
		}
	}

	/* --- getters & setters --- */
	
	// id	
	public function setId($id) {
		$this->id = (integer)$id;
	}
	public function getId() {
		return $this->id;
	}
	// name
	public function setName($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}
	// firstname
	public function setFirstname($firstname) {
	    $this->firstname = $firstname;
	}
	public function getFirstname() {
	    return $this->firstname;
	}
	// login
	public function setLogin($login) {
	    $this->login = $login;
	}
	public function getLogin() {
	    return $this->login;
	}
	// email
	public function setEmail($email) {
	    $this->email = $email;
	}
	public function getEmail() {
	    return $this->email;
	}
	// profil_id
	public function setProfilId($profil_id) {
	    $this->profil_id = $profil_id;
	}
	public function getProfilId() {
	    return $this->profil_id;
	}
	// password
	public function setMdp($password) {
	    $this->password = $password;
	}
	public function getMdp() {
	    return $this->password;
	}
	// expiration
	public function setExpiration($expiration) {
	    $this->expiration = $expiration;
	}
	public function getExpiration() {
	    return $this->expiration;
	}
	// last_cnx
	public function setLastCnx($last_cnx) {
	    $this->last_cnx = $last_cnx;
	}
	public function getLastCnx() {
	    return $this->last_cnx;
	}
	
	/* --- methodes --- */
	
}
?>