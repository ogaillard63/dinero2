<?php
/**
* @project		Dinero
* @author		Olivier Gaillard
* @version		1.0 du 11/12/2014
* @desc			Gestion des categories
*/
namespace App;
use PDO;

class CategoryManager {
	protected $bdd;

	public function __construct(PDO $bdd) {
		$this->bdd = $bdd;
	}

	/**
	* Retourne l'objet category correspondant à l'Id
	* @param $id
	*/
	public function getCategory($id) {
	if ($id) {
		$q = $this->bdd->prepare("SELECT * FROM categories WHERE id = :id");
		$q->bindValue(':id', $id, PDO::PARAM_INT);
		$q->execute();
		return new Category($q->fetch(PDO::FETCH_ASSOC));
		}
	}

	/**
	* Retourne la liste des categories
	*/
	public function getCategories() {
		$categories = array();
		$subs = array();
		$q = $this->bdd->prepare('SELECT * FROM categories WHERE parent_id = 0 ORDER BY id');
		$q->execute();
		while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
			$category = new Category($data);
			$subs = $this->getSubCategories($category->getId());
			$category->setSubCategories($subs);
			$categories[] = $category;
		}
		return $categories;
	}
	/**
	 * Retourne la liste des sous categories d'une catégorie
	 */
	public function getSubCategories($parent_id) {
		$categories = array();
		$q = $this->bdd->prepare('SELECT * FROM categories WHERE parent_id = '.$parent_id);
		$q->execute();
		while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
			$categories[] = new Category($data);
		}
		return $categories;
	}

	/* public function getCategories($isEagerFetch = true) {
		$categories = array();
		$q = $this->bdd->prepare('SELECT * FROM categories ORDER BY category_id');
		$q->execute();
		while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
			$category = new Category($data);
			if ($isEagerFetch) {
				$category_manager = new CategoryManager($this->bdd);
				$category->setCategory($category_manager->getCategory($category->getCategoryId()));
				}
			$categories[] = $category;
		}
		return $categories;
	} */

	/**
	* Retourne la liste des categories par page
	*/
	/* public function getCategoriesByPage($category_id, $page_num, $lpp, $isEagerFetch = true) {
		$categories = array();
		$start = ($page_num-1)*$lpp;
		if ($category_id > 0) {
			$q = $this->bdd->prepare('SELECT * FROM categories WHERE category_id = :category_id ORDER BY id DESC LIMIT :start, :lpp');
			$q->bindValue(':category_id', $category_id, PDO::PARAM_INT);
			}
		else {
			$q = $this->bdd->prepare('SELECT * FROM categories ORDER BY id DESC LIMIT :start, :lpp');
		}
		$q->bindValue(':start', $start, PDO::PARAM_INT);
		$q->bindValue(':lpp', $lpp, PDO::PARAM_INT);
		$q->execute();
		while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
			$category = new Category($data);
			if ($isEagerFetch) {
				$category_manager = new CategoryManager($this->bdd);
				$category->setCategory($category_manager->getCategory($category->getCategoryId()));
				}
			$categories[] = $category;
		}
		return $categories;
	} */
	


	/**
	 * Retourne une liste des categories formatée pour peupler un menu déroulant
	 */
	public function getParentCategoriesForSelect() {
		$categories = array();
		$categories[0] =  " "; // no parent
		$q = $this->bdd->prepare('SELECT id, name FROM categories WHERE parent_id = 0 ORDER BY id');
		$q->execute();
		while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
			$categories[$row["id"]] =  $row["name"];
		}

		 return $categories;
	}

	public function getSubCategoriesForSelect($parent_id) {
		$categories = array();
		$q = $this->bdd->prepare('SELECT id, name FROM categories WHERE parent_id = :parent_id ORDER BY id');
		$q->bindValue(':parent_id', $parent_id, PDO::PARAM_INT);
		$q->execute();
		while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
			$categories[$row["id"]] =  $row["name"];
		}

		 return $categories;
	}
	
	public function getCategoriesForSelect() {
		$categories = array();
		$q1 = $this->bdd->prepare('SELECT id, name FROM categories WHERE parent_id = 0 ORDER BY id');
		$q1->execute();
		$categories[0] = "Selectionnez une catégorie ...";
		while ($row1 = $q1->fetch(PDO::FETCH_ASSOC)) {
			$categories[$row1["name"]] = self::getSubCategoriesForSelect($row1["id"]);
		}
		 return $categories;
	}

	/**
	 * Retourne le nombre max de places
	 */
	public function getMaxCategories($category_id) {
		if ($category_id > 0) {
			$q = $this->bdd->prepare('SELECT count(1) FROM categories WHERE category_id = :category_id');
			$q->bindValue(':category_id', $category_id, PDO::PARAM_INT);
			}
		else {
			$q = $this->bdd->prepare('SELECT count(1) FROM categories');
		}
		$q->execute();
		return intval($q->fetch(PDO::FETCH_COLUMN));
	}
	
	/**
	* Efface l'objet category de la bdd
	* @param Category $category
	*/
	public function deleteCategory(Category $category) {
		$q = $this->bdd->prepare("DELETE FROM categories WHERE id = :id");
		$q->bindValue(':id', $category->getId(), PDO::PARAM_INT);
		return $q->execute();
	}

	/**
	* Enregistre l'objet category en bdd
	* @param Category $category
	*/
	public function saveCategory(Category $category) {
		if ($category->getId() == -1) {
			$q = $this->bdd->prepare('INSERT INTO categories SET parent_id = :parent_id, name = :name, type_flag = :type_flag, active_flag = :active_flag');
		} else {
			$q = $this->bdd->prepare('UPDATE categories SET parent_id = :parent_id, name = :name, type_flag = :type_flag, active_flag = :active_flag WHERE id = :id');
			$q->bindValue(':id', $category->getId(), PDO::PARAM_INT);
		}
		$q->bindValue(':parent_id', $category->getParentId(), PDO::PARAM_STR);
		$q->bindValue(':name', $category->getName(), PDO::PARAM_STR);
		$q->bindValue(':type_flag', $category->getTypeFlag(), PDO::PARAM_STR);
		$q->bindValue(':active_flag', $category->getActiveFlag(), PDO::PARAM_STR);	
		$q->execute();
		if ($category->getId() == -1) $category->setId($this->bdd->lastInsertId());
	}
}
?>
