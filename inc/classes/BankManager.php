<?php
/**
* @project		Dinero
* @author		Olivier Gaillard
* @version		1.0 du 11/12/2014
* @desc			Gestion des banks
*/
namespace App;
use PDO;

class BankManager {
	protected $bdd;

	public function __construct(PDO $bdd) {
		$this->bdd = $bdd;
	}

	/**
	* Retourne l'objet bank correspondant à l'Id
	* @param $id
	*/
	public function getBank($id) {
	if ($id) {
		$q = $this->bdd->prepare("SELECT * FROM banks WHERE id = :id");
		$q->bindValue(':id', $id, PDO::PARAM_INT);
		$q->execute();
		return new Bank($q->fetch(PDO::FETCH_ASSOC));
		}
	}

	/**
	* Retourne la liste des banks
	*/
	public function getBanks() {
		$banks = array();
		$q = $this->bdd->prepare('SELECT * FROM banks ORDER BY id');
		$q->execute();
		while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
			$banks[] = new Bank($data);
		}
		return $banks;
	}
	
	/*public function getBanks($isEagerFetch = true) {
		$banks = array();
		$q = $this->bdd->prepare('SELECT * FROM banks ORDER BY #objet2#_id');
		$q->execute();
		while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
			$bank = new Bank($data);
			if ($isEagerFetch) {
				$#objet2#_manager = new #Objet2#Manager($this->bdd);
				$bank->set#Objet2#($#objet2#_manager->get#Objet2#($bank->get#Objet2#Id()));
				}
			$banks[] = $bank;
		}
		return $banks;
	}*/
	
	
	/**
	 * Retourne une liste des banks formatée pour peupler un menu déroulant
	 */
	public function getBanksForSelect() {
		$banks = array();
		$q = $this->bdd->prepare('SELECT id, name FROM banks ORDER BY id');
		$q->execute();
		while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
			$banks[$row["id"]] =  $row["name"];
		}
		return $banks;
	}
	
	
	/**
	* Efface l'objet bank de la bdd
	* @param Bank $bank
	*/
	public function deleteBank(Bank $bank) {
		try {
    		$q = $this->bdd->prepare("DELETE FROM banks WHERE id = :id");
			$q->bindValue(':id', $bank->getId(), PDO::PARAM_INT);
			return $q->execute();
		}
		catch( PDOException $Exception ) {
		    return false;
		}
	}

	/**
	* Enregistre l'objet bank en bdd
	* @param Bank $bank
	*/
	public function saveBank(Bank $bank) {
		if ($bank->getId() == -1) {
			$q = $this->bdd->prepare('INSERT INTO banks SET name = :name, logo = :logo, color = :color');
		} else {
			$q = $this->bdd->prepare('UPDATE banks SET name = :name, logo = :logo, color = :color WHERE id = :id');
			$q->bindValue(':id', $bank->getId(), PDO::PARAM_INT);
		}
		$q->bindValue(':name', $bank->getName(), PDO::PARAM_STR);
		$q->bindValue(':logo', $bank->getLogo(), PDO::PARAM_STR);
		$q->bindValue(':color', $bank->getColor(), PDO::PARAM_STR);	
		$q->execute();
		if ($bank->getId() == -1) $bank->setId($this->bdd->lastInsertId());
	}
}
?>
