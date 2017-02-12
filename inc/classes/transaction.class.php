<?php
/**
* @project		Dinero
* @author		Olivier Gaillard
* @version		1.0 du 11/12/2014
* @desc			Objet transaction
*/

class Transaction {
	public $id;
	public $account_id;
	public $account;
	public $date;
	public $title;
	public $amount;
	public $category_id;
	public $comment;
	public $internal;
	public $state;
	public $import;
	public $last_mod;

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

	/* --- Getters et Setters --- */

	// id
	public function setId($id) {
		$this->id = $id;
	}
	public function getId() {
		return $this->id;
	}
	// account_id
	public function setAccountId($account_id) {
		$this->account_id = $account_id;
	}
	public function getAccountId() {
		return $this->account_id;
	}
	// account
	public function setAccount(Account $account) {
		$this->account = $account;
	}
	public function getAccount() {
		return $this->account;
	}
	// date
	public function setDate($date) {
		$this->date = $date;
	}
	public function getDate() {
		return $this->date;
	}
	// title
	public function setTitle($title) {
		$this->title = $title;
	}
	public function getTitle() {
		return $this->title;
	}
	// amount
	public function setAmount($amount) {
		$this->amount = $amount;
	}
	public function getAmount() {
		return $this->amount;
	}
	// category_id
	public function setCategoryId($category_id) {
		$this->category_id = $category_id;
	}
	public function getCategoryId() {
		return $this->category_id;
	}
	// comment
	public function setComment($comment) {
		$this->comment = $comment;
	}
	public function getComment() {
		return $this->comment;
	}
	// internal
	public function setInternal($internal) {
		$this->internal = $internal;
	}
	public function getInternal() {
		return $this->internal;
	}
	// state
	public function setState($state) {
		$this->state = $state;
	}
	public function getState() {
		return $this->state;
	}
	// import
	public function setImport($import) {
		$this->import = $import;
	}
	public function getImport() {
		return $this->import;
	}
	// last_mod
	public function setLastMod($last_mod) {
		$this->last_mod = $last_mod;
	}
	public function getLastMod() {
		return $this->last_mod;
	}
}
?>
