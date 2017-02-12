<?php
/**
* @project		
* @author		Olivier Gaillard
* @version		1.0 du 01/12/2014
* @desc			Gestion de la pagination
*/

// https://github.com/BenGriffiths/pdo-mysqli-pagination

// http://codecanyon.net/item/zpager-php-pagination-with-bootstrap/full_screen_preview/6801168
define('MAX_BTN', 5); // maximum number of buttons in the navigation bar

class Pagination {
	 protected $rpp; 
	 protected $current;
	 protected $total;
	 protected $number_of_pages;
	 protected $max_nav_btn;

	 public function __construct($current, $total, $rpp) {
		$this->setCurrent($current);
		$this->setTotal($total);
		$this->setRpp($rpp);
		$this->setNumberOfPages(((int) ceil($total / $rpp)));
		$this->setMaxNavBtn(MAX_BTN);
     }

	public function setCurrent($current) {
		$this->current = $current;
	}
	
	public function setTotal($total) {
            $this->total = $total;
        }
		
	public function setRpp($rpp) {
            $this->rpp = $rpp;
        }

	public function setNumberOfPages($number_of_pages) {
            $this->number_of_pages = $number_of_pages;
        }

	public function setMaxNavBtn($max_nav_btn) {
            if ($max_nav_btn < 3) $max_nav_btn = 3; // 3 buttons minimum
			if ($max_nav_btn < $this->number_of_pages) $this->max_nav_btn = $max_nav_btn;
			else $this->max_nav_btn = $this->number_of_pages;
        }
		
		
	function getNavigation() {
		if ( $this->number_of_pages > 1) {
			$navs = array();
			$previousPage 	= ($this->current < 0) ? 1 : $this->current - 1;
			$nextPage 		= ($this->current >= $this->number_of_pages) ? $this->number_of_pages : $this->current + 1;

			$midPageNav 	= ceil($this->max_nav_btn/2);
			
			$firstPageNav = 1;
			$lastPageNav = $this->max_nav_btn;
			
			if ($this->current > $midPageNav) {
				$firstPageNav = $this->current +1 - $midPageNav;
				$lastPageNav = $firstPageNav + $this->max_nav_btn - 1;
			}
			if ($lastPageNav > $this->number_of_pages) {
				$lastPageNav = $this->number_of_pages;
				$firstPageNav = $lastPageNav - $this->max_nav_btn + 1;
			}

			  // prev
			 if ($previousPage > 0) $navs[] = array("active" => false, "link" => "page=$previousPage", "label" => "&laquo;");
			 
			 for($i=$firstPageNav; $i<=$lastPageNav; $i++) {
				$navs[] = array("active" => ($this->current == $i)?true:false, "link" => "page=$i", "label" => $i);	
			 }
			 // next
			 if ($nextPage > $this->current) $navs[] = array("active" => false, "link" => "page=$nextPage", "label" => "&raquo;");	
			return $navs;
		}
	return null;
	}

}
?>
