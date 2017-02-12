<?php
/**
* @project		
* @author		Olivier Gaillard
* @version		1.0 du 29/11/2014
* @desc			génération du breadcrumb
*/

class Breadcrumb {
	
	protected $bdd;
	public $id;
	public $items;
	public $action;

	public function __construct(PDO $bdd, $items = null, $id = null, $action = null) {
			$this->bdd = $bdd;
			if ($items != null) $this->items = $items;
			if ($action != null) $this->action = $action;
			if ($id != null) $this->id = $id;
		}
		
	public function getCrumb($part, $link, $active = false) {
		return array("part" => $part, "link" => $link, "active" => $active);
	}
	
	public function getCrumbs() {
		$crumbs = array();
		$crumbs[] = $this->getCrumb("Home", "index.php"); // base
		
		if (!empty($this->id)) switch ($this->items) {
		
			case "activities" :
				$manager = new PlacesManager($this->bdd);
				$place = $manager->getPlace($this->id);
				$this->id = $place->country_id;
				
				$manager = new CountriesManager($this->bdd);
				$country = $manager->getCountry($this->id);
				$this->id = $country->continent_id;
				
				$manager = new ContinentsManager($this->bdd);
				$continent = $manager->getContinent($this->id);


				$crumbs[] = $this->getCrumb($continent->name, "countries.php?continent_id=".$continent->id, true);
				$crumbs[] = $this->getCrumb($country->name, "places.php?country_id=".$country->id, true);
				$crumbs[] = $this->getCrumb($place->name, "", true);

			break;

			case "routes" :
			case "places" :
				$manager = new CountriesManager($this->bdd);
				$country = $manager->getCountry($this->id);
				$this->id = $country->continent_id;
				$manager = new ContinentsManager($this->bdd);
				$continent = $manager->getContinent($this->id);
				
				$crumbs[] = $this->getCrumb($continent->name, "countries.php?continent_id=".$continent->id, true);
				$crumbs[] = $this->getCrumb($country->name, "", true);
			break;
				
			case "countries" :
				$manager = new ContinentsManager($this->bdd);
				$continent = $manager->getContinent($this->id);
				$crumbs[] = $this->getCrumb($continent->name, "countries.php?continent_id=".$continent->id, true);
			break;

		}
		return $crumbs;
	}
	
	
	
}

?>