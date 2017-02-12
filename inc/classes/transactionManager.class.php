<?php
/**
* @project		Dinero
* @author		Olivier Gaillard
* @version		1.0 du 11/12/2014
* @desc			Gestion des transactions
*/

class TransactionManager {
	protected $bdd;

	public function __construct(PDO $bdd) {
		$this->bdd = $bdd;
	}

	/**
	* Retourne l'objet transaction correspondant à l'Id
	* @param $id
	*/
	public function getTransaction($id) {
	if ($id) {
		$q = $this->bdd->prepare("SELECT * FROM transactions WHERE id = :id");
		$q->bindValue(':id', $id, PDO::PARAM_INT);
		$q->execute();
		return new Transaction($q->fetch(PDO::FETCH_ASSOC));
		}
	}

	/**
	* Retourne la liste des transactions
	*/
	public function getTransactions() {
		$transactions = array();
		$q = $this->bdd->prepare('SELECT * FROM transactions ORDER BY id');
		$q->execute();
		while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
			$transactions[] = new Transaction($data);
		}
		return $transactions;
	}
	
	/**
	* Retourne la date de la transaction la plus récente
	*/
	public function getLastTransactionDate($account_id) {
		$q = $this->bdd->prepare('SELECT MAX(date) FROM transactions WHERE account_id = :account_id');
		$q->bindValue(':account_id', $account_id, PDO::PARAM_INT);
		$q->execute();
		return $q->fetch(PDO::FETCH_COLUMN);
	}	


	/**
	* Retourne le solde courant du compte
	*/
	public function getBalance($account_id) {
		$q = $this->bdd->prepare('SELECT SUM(amount) FROM transactions WHERE account_id = :account_id');
		$q->bindValue(':account_id', $account_id, PDO::PARAM_INT);
		$q->execute();
		return $q->fetch(PDO::FETCH_COLUMN);
	}

	/* public function getTransactions($isEagerFetch = true) {
		$transactions = array();
		$q = $this->bdd->prepare('SELECT * FROM transactions ORDER BY account_id');
		$q->execute();
		while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
			$transaction = new Transaction($data);
			if ($isEagerFetch) {
				$account_manager = new AccountManager($this->bdd);
				$transaction->setAccount($account_manager->getAccount($transaction->getAccountId()));
				}
			$transactions[] = $transaction;
		}
		return $transactions;
	} */

	/**
	* Retourne la liste des transactions par page
	*/
	 public function getTransactionsByPage($account_id, $page_num, $lpp, $isEagerFetch = true) {
		$transactions = array();
		$start = ($page_num-1)*$lpp;
		if ($account_id > 0) {
			$q = $this->bdd->prepare('SELECT * FROM transactions WHERE account_id = :account_id 
				ORDER BY date DESC, id DESC LIMIT :start, :lpp');
			$q->bindValue(':account_id', $account_id, PDO::PARAM_INT);
			}
		else {
			$q = $this->bdd->prepare('SELECT * FROM transactions ORDER BY id DESC LIMIT :start, :lpp');
		}
		$q->bindValue(':start', $start, PDO::PARAM_INT);
		$q->bindValue(':lpp', $lpp, PDO::PARAM_INT);
		$q->execute();
		while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
			$transaction = new Transaction($data);
			if ($isEagerFetch) {
				$account_manager = new AccountManager($this->bdd);
				if($transaction) $transaction->setAccount($account_manager->getAccount($transaction->getAccountId()));
				}
			$transactions[] = $transaction;
		}
		return $transactions;

	}
	
	/**
	* Recherche les transactions
	*/
	public function searchTransactions($query, $account_id) {
		$transactions = array();
		$q = $this->bdd->prepare('SELECT * FROM transactions WHERE account_id = :account_id 
		AND title LIKE :query OR amount LIKE :query OR comment LIKE :query ORDER BY date DESC'); //  LIMIT 100
		$q->bindValue(':account_id', $account_id, PDO::PARAM_INT);
		$q->bindValue(':query', '%'.$query.'%', PDO::PARAM_STR);
		$q->execute();
		while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
			$transaction = new Transaction($data);
			$account_manager = new AccountManager($this->bdd);
			if($transaction) $transaction->setAccount($account_manager->getAccount($transaction->getAccountId()));
			$transactions[] = $transaction;
		}
		return $transactions;
	}

	/**
	 * Retourne une liste des transactions formatée pour peupler un menu déroulant
	 */
	 public function getTransactionsForSelect() {
		$transactions = array();
		$q = $this->bdd->prepare('SELECT id, name FROM transactions ORDER BY id');
		$q->execute();
		while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
			$transactions[$row["id"]] =  $row["name"];
		}
		return $transactions;
	}
	
	/**
	 * Retourne le nombre max de places
	 */
	public function getMaxTransactions($account_id) {
		if ($account_id > 0) {
			$q = $this->bdd->prepare('SELECT count(1) FROM transactions WHERE account_id = :account_id');
			$q->bindValue(':account_id', $account_id, PDO::PARAM_INT);
			}
		else {
			$q = $this->bdd->prepare('SELECT count(1) FROM transactions');
		}
		$q->execute();
		return intval($q->fetch(PDO::FETCH_COLUMN));
	}
	
	/**
	* Efface l'objet transaction de la bdd
	* @param Transaction $transaction
	*/
	public function deleteTransaction(Transaction $transaction) {
		try {	
			$q = $this->bdd->prepare("DELETE FROM transactions WHERE id = :id");
			$q->bindValue(':id', $transaction->getId(), PDO::PARAM_INT);
			return $q->execute();
			}
		catch( PDOException $Exception ) {
			    return false;
			}
	}
	
	/**
	* Efface toutes les transactions du compte
	* @param $account_id
	*/
	public function deleteAllTransactions($account_id) {
		try {	
			$q = $this->bdd->prepare("DELETE FROM transactions WHERE account_id = :account_id");
			$q->bindValue(':account_id', $account_id, PDO::PARAM_INT);
			return $q->execute();
			}
		catch( PDOException $Exception ) {
			    return false;
			}
	}

	/**
	* Enregistre l'objet transaction en bdd
	* @param Transaction $transaction
	*/
	public function saveTransaction(Transaction $transaction) {
		if ($transaction->getId() == -1) {
			$q = $this->bdd->prepare('INSERT INTO transactions SET account_id = :account_id, date = :date, title = :title, amount = :amount, category_id = :category_id, comment = :comment, internal = :internal, state = :state, import = :import, last_mod = :last_mod');
		} else {
			$q = $this->bdd->prepare('UPDATE transactions SET account_id = :account_id, date = :date, title = :title, amount = :amount, category_id = :category_id, comment = :comment, internal = :internal, state = :state, import = :import, last_mod = :last_mod WHERE id = :id');
			$q->bindValue(':id', $transaction->getId(), PDO::PARAM_INT);
		}
		$q->bindValue(':account_id', $transaction->getAccountId(), PDO::PARAM_INT);
		$q->bindValue(':date', $transaction->getDate(), PDO::PARAM_STR);
		$q->bindValue(':title', $transaction->getTitle(), PDO::PARAM_STR);
		$q->bindValue(':amount', $transaction->getAmount(), PDO::PARAM_STR);
		$q->bindValue(':category_id', $transaction->getCategoryId(), PDO::PARAM_INT);
		$q->bindValue(':comment', $transaction->getComment(), PDO::PARAM_STR);
		$q->bindValue(':internal', $transaction->getInternal(), PDO::PARAM_INT);
		$q->bindValue(':state', $transaction->getState(), PDO::PARAM_INT);
		$q->bindValue(':import', $transaction->getImport(), PDO::PARAM_INT);
		$q->bindValue(':last_mod', $transaction->getLastMod(), PDO::PARAM_STR);	
		$q->execute();
		if ($transaction->getId() == -1) $transaction->setId($this->bdd->lastInsertId());
	}

	/**
	* Retourne le nombre d'operation non catégorisé
	*/
	public function getNbUncategorizedTransactions(){
		$q = $this->bdd->prepare('SELECT count(1) FROM transactions WHERE category_id = 0');
		$q->execute();
		return intval($q->fetch(PDO::FETCH_COLUMN));
	}

	/**
	* Retourne le nombre d'operations non rapproché
	*/
	public function getNbUnreconciledTransactions(){
		$q = $this->bdd->prepare('SELECT count(1) FROM transactions WHERE state = 0');
		$q->execute();
		return intval($q->fetch(PDO::FETCH_COLUMN));
	}
	
	/**
	* Retourne le cumul mensuel des revenus du compte
	*/
	public function getMonthlyIncome() {
		$date = new DateTime();
		$q = $this->bdd->prepare('SELECT SUM(amount) FROM transactions 
			WHERE internal = 0 AND amount > 0 AND date BETWEEN :dateDeb AND :dateFin');
		//$q->bindValue(':account_id', $account_id, PDO::PARAM_INT);
		$q->bindValue(':dateDeb', $date -> format('Y-m-01'), PDO::PARAM_STR);
		$q->bindValue(':dateFin', $date -> format('Y-m-d'), PDO::PARAM_STR);
		$q->execute();
		return $q->fetch(PDO::FETCH_COLUMN);
	}
	
	/**
	* Retourne le cumul mensuel des dépenses du compte
	*/
	public function getMonthlyExpense(){
		$date = new DateTime();
		$q = $this->bdd->prepare('SELECT SUM(amount) FROM transactions 
			WHERE internal = 0 AND amount < 0 AND date BETWEEN :dateDeb AND :dateFin');
		//$q->bindValue(':account_id', $account_id, PDO::PARAM_INT);
		$q->bindValue(':dateDeb', $date -> format('Y-m-01'), PDO::PARAM_STR);
		$q->bindValue(':dateFin', $date -> format('Y-m-d'), PDO::PARAM_STR);
		$q->execute();
		return $q->fetch(PDO::FETCH_COLUMN);	
	}

	/**
	* Retourne le solde des transactions à une date
	* @param $queryDate : Y-m-d
	*/
	public function getBalanceAtDate($queryDate, $account_id){
		$q = $this->bdd->prepare('SELECT SUM(amount) FROM transactions 
			WHERE date < :qdate AND account_id = :account_id');
		$q->bindValue(':qdate', $queryDate, PDO::PARAM_STR);
		$q->bindValue(':account_id', $account_id, PDO::PARAM_INT);
		$q->execute();
		return $q->fetch(PDO::FETCH_COLUMN);	
	}

	/**
	* Remet à 0 le flag d'import de toutes les opérations
	*/
	public function resetImport($account_id){
		$q = $this->bdd->prepare('UPDATE transactions SET import=0 
			WHERE account_id = :account_id');
		$q->bindValue(':account_id', $account_id, PDO::PARAM_INT);
		$q->execute();
	}
	/**
	* Efface les dernières opérations importées
	*/
	public function deleteLastImport($account_id){
		$q = $this->bdd->prepare('DELETE FROM transactions 
			WHERE account_id = :account_id AND import=1');
		$q->bindValue(':account_id', $account_id, PDO::PARAM_INT);
		$q->execute();
	}
		



}
?>
