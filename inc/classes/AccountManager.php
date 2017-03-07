<?php
/**
* @project		Dinero
* @author		Olivier Gaillard
* @version		1.0 du 11/12/2014
* @desc			Gestion des accounts
*/
namespace App;
use PDO;

class AccountManager {
	protected $bdd;

	public function __construct(PDO $bdd) {
		$this->bdd = $bdd;
	}

	/**
	* Retourne l'objet account correspondant à l'Id
	* @param $id
	*/
	public function getAccount($id) {
		$q = $this->bdd->prepare("SELECT * FROM accounts WHERE id = :id");
		$q->bindValue(':id', $id, PDO::PARAM_INT);
		$q->execute();
		return new Account($q->fetch(PDO::FETCH_ASSOC));
	}

	/**
	* Retourne la liste des accounts
	*/

	public function getAccounts($isEagerFetch = true, $isActive = null) {
		$accounts = array();
		if ( $isActive == null) 
			$q = $this->bdd->prepare('SELECT * FROM accounts ORDER BY state DESC, sort ASC');
		else {
			$q = $this->bdd->prepare('SELECT * FROM accounts WHERE state = :state 
			ORDER BY state DESC, sort ASC ');
			$q->bindValue(':state', $isActive, PDO::PARAM_STR);
		}
		$q->execute();
		while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
			$account = new Account($data);
			if ($isEagerFetch) {
				$bank_manager = new BankManager($this->bdd);
				$account->setBank($bank_manager->getBank($account->getBankId()));
				}
			$accounts[] = $account;
		}
		return $accounts;
	}
	
	/**
	* Retourne la liste des accounts actifs
	*/
	public function getActiveAccounts($isEagerFetch = true) {
		return self::getAccounts($isEagerFetch = true, $isActive = true);
		}
	
	public function getAccountsByPage($bank_id, $page_num, $lpp, $isEagerFetch = true) {
		$accounts = array();
		$start = ($page_num-1)*$lpp;
		if ($bank_id > 0) {
			$q = $this->bdd->prepare('SELECT * FROM accounts WHERE bank_id = :bank_id 
				ORDER BY state DESC, sort ASC LIMIT :start, :lpp');
			$q->bindValue(':bank_id', $bank_id, PDO::PARAM_INT);
			}
		else {
			$q = $this->bdd->prepare('SELECT * FROM accounts ORDER BY state DESC, sort ASC LIMIT :start, :lpp');
		}
		$q->bindValue(':start', $start, PDO::PARAM_INT);
		$q->bindValue(':lpp', $lpp, PDO::PARAM_INT);
		$q->execute();
		while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
			$account = new Account($data);
			if ($isEagerFetch) {
				$bank_manager = new BankManager($this->bdd);
				$account->setBank($bank_manager->getBank($account->getBankId()));
				}
			$accounts[] = $account;
		}
		return $accounts;
	}
	
	/**
	 * Retourne le nombre max de places
	 */
	public function getMaxAccounts($bank_id) {
		if ($bank_id > 0) {
			$q = $this->bdd->prepare('SELECT count(1) FROM accounts WHERE bank_id = :bank_id');
			$q->bindValue(':bank_id', $bank_id, PDO::PARAM_INT);
			}
		else {
			$q = $this->bdd->prepare('SELECT count(1) FROM accounts');
		}
		$q->execute();
		return intval($q->fetch(PDO::FETCH_COLUMN));
	}
	
	/**
	 * Retourne une liste des accounts formatée pour peupler un menu déroulant
	 */
	public function getAccountsForSelect() {
		$accounts = array();
		$q = $this->bdd->prepare('SELECT id, name FROM accounts WHERE state = "1" ORDER BY id');
		$q->execute();
		while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
			$accounts[$row["id"]] =  $row["name"];
		}
		return $accounts;
	}
	
	
	/**
	* Efface l'objet account de la bdd
	* @param Account $account
	*/
	public function deleteAccount(Account $account) {
		try {	
			$q = $this->bdd->prepare("DELETE FROM accounts WHERE id = :id");
			$q->bindValue(':id', $account->getId(), PDO::PARAM_INT);
			return $q->execute();
			}
		catch( PDOException $Exception ) {
			    return false;
			}
	}


	/**
	* Enregistre l'objet account en bdd
	* @param Account $account
	*/
	public function saveAccount(Account $account) {
		if ($account->getId() == -1) {
			$q = $this->bdd->prepare('INSERT INTO accounts SET bank_id = :bank_id, user_id = :user_id, name = :name, account_number = :account_number, sort = :sort, state = :state, initial_balance = :initial_balance, current_balance = :current_balance');
		} else {
			$q = $this->bdd->prepare('UPDATE accounts SET bank_id = :bank_id, user_id = :user_id, name = :name, account_number = :account_number, sort = :sort, state = :state, initial_balance = :initial_balance, current_balance = :current_balance WHERE id = :id');
			$q->bindValue(':id', $account->getId(), PDO::PARAM_INT);
		}
		$q->bindValue(':bank_id', $account->getBankId(), PDO::PARAM_STR);
		$q->bindValue(':user_id', $account->getUserId(), PDO::PARAM_STR);
		$q->bindValue(':name', $account->getName(), PDO::PARAM_STR);
		$q->bindValue(':account_number', $account->getAccountNumber(), PDO::PARAM_STR);
		$q->bindValue(':sort', $account->getSort(), PDO::PARAM_STR);
		$q->bindValue(':state', $account->getState(), PDO::PARAM_STR);
		$q->bindValue(':initial_balance', $account->getInitialBalance(), PDO::PARAM_STR);
		$q->bindValue(':current_balance', $account->getCurrentBalance(), PDO::PARAM_STR);	
		$q->execute();
		if ($account->getId() == -1) $account->setId($this->bdd->lastInsertId());
	}

	/**
	* Retourne le solde courant du compte
	* @param $id : id du compte
	*/
	public function getCurrentBalance($id) {
	if ($id) {
		$q = $this->bdd->prepare("SELECT current_balance FROM accounts WHERE id = :id");
		$q->bindValue(':id', $id, PDO::PARAM_INT);
		$q->execute();
		return $q->fetch(PDO::FETCH_COLUMN);
		}
	}

	/**
	* Met à jour le solde courant du compte
	* @param $id : id du compte
	*/
	public function updateCurrentBalance($id) {
	if ($id) {
		$q = $this->bdd->prepare("SELECT initial_balance FROM accounts WHERE id = :id");
		$q->bindValue(':id', $id, PDO::PARAM_INT);
		$q->execute();
		$initial_balance = $q->fetch(PDO::FETCH_COLUMN);
		$transaction_manager = new TransactionManager($this->bdd);
		$balance = $transaction_manager->getBalance($id);
		$account = $this->getAccount($id);
		$account->setCurrentBalance($balance + $initial_balance);
		$this->saveAccount($account);

		}
	}
}

?>