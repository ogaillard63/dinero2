<?php
class Logger {
	
	protected $bdd;
	protected $smarty;
	protected $session;
	
	const DEBUG='DEBUG';
	const ERROR='ERROR';
	const INFO='INFO';

	public function __construct(PDO $bdd, $smarty, $session) {
		$this->bdd = $bdd;
		$this->smarty = $smarty;
		$this->session = $session;
	}

	// Notification de l'utilisateur
	public function notification($message, $type="success") {
		if ($type == "error") $type = "danger";
		$this->session->setValue("alert", array("type" => $type, "message" => $message));
	}
	
	public function debug($contexte, $message) {
		$this->write($contexte, $message, self::DEBUG);
	}

	public function info($contexte, $message) {
		$this->write($contexte, $message, self::INFO);
	}

	public function error($contexte, $message) {
		$this->write($contexte, $message, self::ERROR);
	}

	/**
	 * Enregistre les messages de debug et log
	 */
	public function write($contexte, $message, $type=self::INFO) {
		if(!is_string($message))
			throw new NotAStringException('$message n\'est pas une chaine de caractères');
		if($type != self::DEBUG && $type != self::INFO && $type != self::ERROR)
			throw new InvalidMessageTypeException('Le type de message de log est inconnu !');
		
		$q = $this->bdd->prepare("INSERT INTO logs SET contexte = :contexte, msg = :msg, type = :type");
		$q->bindValue(':contexte', $contexte, PDO::PARAM_STR);
		$q->bindValue(':msg', $message, PDO::PARAM_STR);
		$q->bindValue(':type', $type, PDO::PARAM_STR);
		$q->execute();
		
	}
	
	/**
	 * Retourne une page de log
	 */
	public function getLogsByPage($num_page, $lpp) {
		$logs = array();
		$start = ($num_page-1)*$lpp;
		$q = $this->bdd->prepare('SELECT * FROM logs ORDER BY id DESC LIMIT :start, :lpp');
		$q->bindValue(':start', $start, PDO::PARAM_INT);
		$q->bindValue(':lpp', $lpp, PDO::PARAM_INT);
		
		$q->execute();
		while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
			$logs[] = $data;
		}
		return $logs;
	}
	
	/**
	 * Retourne le nombre de ligne de log
	 */
	public function getLogSize() {
		$q = $this->bdd->prepare("SELECT count(*) FROM logs");
		$q->execute();
		return intval($q->fetch(PDO::FETCH_COLUMN));
	}
	
	/**
	 * Efface le log
	 */
	public function deleteOldLog($max_lines) {
		$taille_log = $this->getLogSize();
		if ($taille_log > $max_lines) {
			$q = $this->bdd->prepare("DELETE FROM logs ORDER BY creation ASC LIMIT :limit");
			$q->bindValue(':limit', $taille_log-$max_lines, PDO::PARAM_INT);
			$q->execute();
		}
	}
	
}
?>