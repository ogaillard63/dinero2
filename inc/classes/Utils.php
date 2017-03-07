<?php
namespace App;

class Utils {

	// Fonction de debug
	/**
	 *  Affiche un tableau de façon claire (debug)
	 **/
	 public static function println($str) {
		echo $str . "<br/>";
	}
	/**
	 * Affiche un tableau de façon claire (debug)
	 **/
	 public static function print_tab($tab) {
		echo "<pre>";
		print_r($tab);
		echo "</pre>";
	}
    

	// Traitement des images
	/**
	* Retaille une image JPEG en conservant les proportions
	* @param string $filename
	* @param integer $max_width
	* @param integer $max_height
	* @return image
	*/
	 public static function resizeImage($filename, $max_width, $max_height) {
	    list($orig_width, $orig_height) = getimagesize($filename);
	
	    $width = $orig_width;
	    $height = $orig_height;
	
	    # taller
	    if ($height > $max_height) {
	        $width = ($max_height / $height) * $width;
	        $height = $max_height;
	    }
	
	    # wider
	    if ($width > $max_width) {
	        $height = ($max_width / $width) * $height;
	        $width = $max_width;
	    }
	
	    $image_p = imagecreatetruecolor($width, $height);
	    $image = imagecreatefromjpeg($filename);
	    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);
	    imagejpeg($image_p, $filename); // Enregistre l'image
	    //return $image_p;
	}
	
	
	// Conversion date/time
	/**
	 * Conversion de datetime (sans seconde) au format SQL
	 */
	 public static function dateTimeToSqlNoSec($datetime) {
	 	if ($datetime <> '') {
	 		return date_format(date_create_from_format('d/m/Y H:i', $datetime), 'Y-m-d H:i:00');
	 		}
	 	return false;
	 }

	
	/**
	 * Conversion d'un time (HH:MM:SS) en secondes
	 **/
	 public static function TimeToSec($time) {
		if ($time <> '') {
			list($h, $m, $s) = explode(":", $time);
			return intval($s)+(intval($m)*60)+(intval($h)*3600);
		}
		return false;
	}
	
	
	/**
	 * Conversion d'un datetime (dd/mmd/yyyy HH:MM:SS) en (yyyy-mm-dd HH:MM:SS)
	 **/
	 public static function dateTimeToSql($datetime) {
		if ($datetime <> '') {
			list($date, $time) = explode(" ", $datetime);
			list($d, $m, $y) = explode("/", $date);
			list($hh, $mm, $ss) = explode(":", $time);
			if ($ss == "")
				$ss = 0;
			// Si pas les secondes
			return date("Y-m-d H:i:s", mktime($hh, $mm, $ss, $m, $d, $y));
		}
	}
	
	/**
	 * Conversion d'une date (yyyy-mm-dd) en (dd/mmd/yyyy)
	 **/
	 public static function sqlToDate($date) {
		if ($date <> '') {
			list($y, $m, $d) = explode("-", $date);
			return date("d/m/Y", mktime(0, 0, 0, $m, $d, $y));
		}
	}
	
	/**
	 * Conversion d'une date (dd/mmd/yyyy) en (yyyy-mm-dd)
	 **/
	 public static function dateToSql($date) {
		if ($date <> '') {
			list($d, $m, $y) = explode("/", $date);
			return date("Y-m-d", mktime(0, 0, 0, $m, $d, $y));
		}
	}
	
	
	// Divers	 
	/**
	 * @desc 	Nettoie une chaine de caractères pour en faire un id
	 **/
	
	 public static function encodeString($s) {
		return strtolower(
		preg_replace(array('/[^a-zA-Z0-9. -]/', '/[ -]+/', '/^-|-$/'),
		array('', '_', ''), self::cleanSpecialChars($s)));
	}
	
	 // nettoyage des accents et autres caractère
	 public static function cleanSpecialChars($s, $d = false) {
		if ($d) $s = utf8_decode($s);
	
		$chars = array('_' => '/`|´|\^|~|¨|ª|º|©|®/', 'a' => '/à|á|â|ã|ä|å|æ/',
				'e' => '/è|é|ê|ë/', 'i' => '/ì|í|î|ĩ|ï/', 'o' => '/ò|ó|ô|õ|ö|ø/',
				'u' => '/ù|ú|û|ű|ü|ů/', 'A' => '/À|Á|Â|Ã|Ä|Å|Æ/',
				'E' => '/È|É|Ê|Ë/', 'I' => '/Ì|Í|Î|Ĩ|Ï/', 'O' => '/Ò|Ó|Ô|Õ|Ö|Ø/',
				'U' => '/Ù|Ú|Û|Ũ|Ü|Ů/', 'c' => '/ć|ĉ|ç/', 'C' => '/Ć|Ĉ|Ç/',
				'n' => '/ñ/', 'N' => '/Ñ/', 'y' => '/ý|ŷ|ÿ/', 'Y' => '/Ý|Ŷ|Ÿ/');
	
		return preg_replace($chars, array_keys($chars), $s);
	}
	 
	// Fichiers et dossiers
	/**
	 * Supprime récursivement ou non tous les fichiers d'un dossier sans suivre les liens symboliques
	 * @param string $path : chemin du dossier à supprimer.
	 * @param boolean $recursive : effacement récursif des fichiers
	 */
	 public static function removeDir($path, $recursive = true) {
		$dir = new DirectoryIterator($path);
		foreach ($dir as $fileinfo) {
			if ($fileinfo->isFile() || $fileinfo->isLink()) {
				unlink($fileinfo->getPathName());
			} elseif (!$fileinfo->isDot() && $fileinfo->isDir() && $recursive) {
				self::removeDir($fileinfo->getPathName());
			}
		}
		rmdir($path);
	}
	
	/**
	 * copyDir : Copie récursive
	 */
	 public static function copyDir($dir2copy, $dir_paste) {
		if (is_dir($dir2copy)) {
			if ($dh = opendir($dir2copy)) {
				while (($file = readdir($dh)) !== false) {
					if (!is_dir($dir_paste))
					mkdir($dir_paste, 0777);
					if (is_dir($dir2copy . $file) && $file != '..' && $file != '.')
					self::copyDir($dir2copy . $file . '/', $dir_paste . $file . '/');
					elseif ($file != '..' && $file != '.')
					copy($dir2copy . $file, $dir_paste . $file);
				}
				closedir($dh);
			}
		}
	}
	
	// copies files and non-empty directories
	 public static function rcopy($src, $dst) {
		if (file_exists($dst)) self::removeDir($dst);
		if (is_dir($src)) {
			mkdir($dst, 0777, true);
			$files = scandir($src);
			foreach ($files as $file)
			if ($file != "." && $file != "..") {
				self::rcopy("$src/$file", "$dst/$file");
			}
		} else if (file_exists($src))
		copy($src, $dst);
	}
	
	// IO
	/**
	 * @desc récupère une variable d'un POST ou GET (post, get, both)
	 */
	 public static function get_input($name = "", $type = "") {
		//global $_POST, $_GET;
	
		$tdis = "";
		$magic = get_magic_quotes_gpc();
	
		if (($type == "get") || ($type == "both")) {
			if (isset($_GET["$name"])) {
				$tdis = $_GET["$name"];
				if ($magic && !is_array($tdis)) {
					$tdis = trim(stripslashes($tdis));
				}
			}
		}
		if (($type == "post") || ($type == "both")) {
			if (isset($_POST["$name"])) {
				$tdis = $_POST["$name"];
				if ($magic && !is_array($tdis)) {
					$tdis = trim(stripslashes($tdis));
				}
			}
		}
		return $tdis;
	}
	
	/**
	 * @desc 	Redirige le navigateur vers la page $page.
	 **/
	 public static function redirection($page) {
		header("Location: " . $page);
		exit();
	}
	
	/**
	 * @desc 	Conversion d'une date (dd/mmd/yyyy) en (yyyy-mm-dd)
	 **/
	 public static function date2sql($date) {
		if ($date <> '') {
			list($d, $m, $y) = explode("/", $date);
			return date("Y-m-d", mktime(0, 0, 0, $m, $d, $y));
		}
	}
	
	/**
	 * Formate un chemin windows en format unix
	 * @param $path
	 */
	 public static function unixPath($path) {
		return str_replace('\\', '/', $path);
	}
		
	
	/**
	 * Enregistre les données dans un fichier
	 * @param $file
	 * @param $data
	 */
	 public static function saveDataToFile($file, $data) {
		$fh = fopen($file, 'w');
		fputs($fh, $data);
		fclose($fh);
		if (LINUX) chmod($file, 0777);
	}
	
	/**
	 * Retourne la liste des fichiers d'un repertoire
	 * @param $dir_path
	 */
	 public static function getDirFileList($dir_path) {
		if (is_dir($dir_path)) {
			$dir = opendir($dir_path);
			$files = array();
			while ($file = readdir($dir)) {
				if ($file == '.' || $file == '..') continue;
		        if(is_dir($dir_path.'/'.$file)) {
		            Utils::getDirFileList($dir_path.'/'.$file);
		        }
		        else {
		           $files[] = $file;
		        }
			}
			closedir($dir);
			return $files;
		}
		return null;
	}
	
	/**
	 * Retourne une liste rescursive du contenu d'un dossier 
	 * @param $exclude : liste des exclusions
	 */
	 public static function listDir($startpath, $exclude = ".|..", $separator = "\n") {
		$list = "";
		$exlude_list = explode('|', $exclude);
		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($startpath, RecursiveDirectoryIterator::FOLLOW_SYMLINKS), RecursiveIteratorIterator::CHILD_FIRST) as $splFileInfo) { 
			// TODO : exclusion des noms de dossiers ?
			if (!in_array($splFileInfo->getFilename(), $exlude_list)) {
			    $list.=str_replace($startpath, '', $splFileInfo).$separator;
			}
		} 
		return str_replace('\\', '/', $list); // conversion des slash
	}

}

?>