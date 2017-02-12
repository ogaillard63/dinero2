<?php
/**
* @project		Dinero
* @author		Olivier Gaillard
* @version		1.0 du 11/12/2014
* @desc			Objet bank
*/

class Bank {
	public $id;
	public $name;
	public $logo;
	public $color;

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
	// name
	public function setName($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}
	// logo
	public function setLogo($logo) {
		$this->logo = $logo;
	}
	public function getLogo() {
		return $this->logo;
	}
	// color
	public function setColor($color) {
		$this->color = $color;
	}
	public function getColor() {
		return $this->color;
	}
}
?>
