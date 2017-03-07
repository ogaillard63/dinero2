<?php
/**
* @project		Dinero
* @author		Olivier Gaillard
* @version		1.0 du 11/12/2014
* @desc			Objet category
*/
namespace App;

class Category {
	public $id;
	public $parent_id;
	public $sub_categories;
	public $name;
	public $type_flag;
	public $active_flag;

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
	// parent_id
	public function setParentId($parent_id) {
		$this->parent_id = $parent_id;
	}
	public function getParentId() {
		return $this->parent_id;
	}
	// sub_categories
	public function setSubCategories($sub_categories) {
		$this->sub_categories = $sub_categories;
	}
	public function getSubCategories() {
		return $this->sub_categories;
	}
	// name
	public function setName($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}
	// type_flag
	public function setTypeFlag($type_flag) {
		$this->type_flag = $type_flag;
	}
	public function getTypeFlag() {
		return $this->type_flag;
	}
	// active_flag
	public function setActiveFlag($active_flag) {
		$this->active_flag = $active_flag;
	}
	public function getActiveFlag() {
		return $this->active_flag;
	}
}
?>
