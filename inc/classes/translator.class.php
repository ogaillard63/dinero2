<?php
class Translator {

    private $pathFileLang	= '';
	private $lang 			= array();
	
	public function __construct($pathFileLang){
		$this->pathFileLang = $pathFileLang;
	}
	
    private function findString($str) {
        if (array_key_exists($str, $this->lang)) {
			return $this->lang[$str];
        }
		else return $str; // no translation
    }
    
	private function splitStrings($str) {
        if (strpos($str, '=') !== false) // avoids comments
			return array_map('trim', explode('=', $str));
    }
	
	public function __($str) {	
		if (file_exists($this->pathFileLang)) {
			$strings = array_map(array($this,'splitStrings'), file($this->pathFileLang));
			foreach ($strings as $k => $v) {
				$this->lang[$v[0]] = $v[1];
			}
			return $this->findString($str);
		}
		else {
			return $str; // no translation
		}
    }
}
?>