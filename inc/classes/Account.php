<?php
/**
* @project		Dinero
* @author		Olivier Gaillard
* @version		1.0 du 11/12/2014
* @desc			Objet account
*/
namespace App;

class Account {
	public $id;
	public $bank_id;
	public $bank;
	public $user_id;
	public $name;
	public $account_number;
	public $sort;
	public $state;
	public $initial_balance;
	public $current_balance;

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
	// bank_id
	public function setBankId($bank_id) {
		$this->bank_id = $bank_id;
	}
	public function getBankId() {
		return $this->bank_id;
	}
	// bank
	public function setBank(Bank $bank) {
		$this->bank= $bank;
	}
	public function getBank() {
		return $this->bank;
	}
	// user_id
	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}
	public function getUserId() {
		return $this->user_id;
	}
	// name
	public function setName($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}
	// account_number
	public function setAccountNumber($account_number) {
		$this->account_number = $account_number;
	}
	public function getAccountNumber() {
		return $this->account_number;
	}
	// sort
	public function setSort($sort) {
		$this->sort = $sort;
	}
	public function getSort() {
		return $this->sort;
	}
	// state
	public function setState($state) {
		$this->state = $state;
	}
	public function getState() {
		return $this->state;
	}
	// initial_balance
	public function setInitialBalance($initial_balance) {
		$this->initial_balance = $initial_balance;
	}
	public function getInitialBalance() {
		return $this->initial_balance;
	}
	// current_balance
	public function setCurrentBalance($current_balance) {
		$this->current_balance = $current_balance;
	}
	public function getCurrentBalance() {
		return $this->current_balance;
	}
}
?>
