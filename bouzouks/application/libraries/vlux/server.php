<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Server {

	const Etat_Offline = 0;
	const Etat_Lock = 1;
	const Etat_Beta = 2;
	const Etat_Open = 3;

	public $etat;
	private $CI;
	private $_pid;// chemin du fichier pid
	private $_sh;// Emplacement du fichier sh 
	
	public function __construct($e)
	{
		$this->etat = $e['etat'];
		$this->_pid = APPPATH.'servers/map_serveur.pid';
		$this->_sh = APPPATH.'servers/map_serveur.sh';
		$this->CI =& get_instance();
	}

	public function is_auth(){
		// Serveur arrêté
		if($this->etat == self::Etat_Offline){
			return false;
		}
		// Serveur en accès admin uniquement
		elseif($this->etat == self::Etat_Lock && $this->CI->bouzouk->is_admin()){
			return true;
		}
		// Serveur ouvert au béta-testeurs
		elseif($this->etat == self::Etat_Beta && $this->CI->bouzouk->is_beta_testeur()){
			return true;
		}
		// Serveur ouvert, accès au étudiant et joueur actif
		elseif($this->etat == self::Etat_Open){
			//Le joueur est étudiant ou actif
			$statut = $this->CI->session->userdata('statut');
			if($statut == Bouzouk::Joueur_Actif || $statut == Bouzouk::Joueur_Etudiant || $statut == Bouzouk::Joueur_ChoixPerso){
				return true;
			}
		}
		// Par défaut, on bloque l'accès.
		else{
			return false;
		}
	}

	/**
	 * Permet de charger les images correspondante au serveur actif pour les différentes UI.
	 * <code> $this->get_img(true); </code>
	 * @param string $mini 
	 *  Si true, ce sont les miniature qui sont servies.
	 * @return array()
	 */
	public function get_img($mini = false) {

		//@TODO vérification existance du fichier et fall back.
		if ($mini == true) {
			$prefix='_mini';
		}
		else {
			$prefix='';
		}
		if ($this->etat == self::Etat_Offline) {
			return array(
				'img'	=>'staff/node'.$prefix.'_start_0.png',
				'title'	=> 'Arrêt Complet',
				'alt' 	=> 'Arrêt');
		}
		elseif($this->etat == self::Etat_Lock) {
			return array(
				'img'	=>'staff/node'.$prefix.'_online_0.png',
				'title'	=> 'Accés Admin',
				'alt' 	=> 'Accés Admin');
		}
		elseif($this->etat == self::Etat_Beta) {
			return array(
				'img'	=>'staff/node'.$prefix.'_online_1.png',
				'title'	=> 'Mode test',
				'alt' 	=> 'Mode test');
		}
		elseif($this->etat == self::Etat_Open) {
			return array(
				'img'	=>'staff/node'.$prefix.'_online_2.png',
				'title'	=> 'Ouvert',
				'alt' 	=> 'Ouvert');
		}
		else {
			show_error("Probleme de chargement d'image : ".$this->etat,500);
		}
	}
	
	public function switch_state($etat){
		if($etat == self::Etat_Offline){
			$this->stop();
		}
		elseif($etat>= self::Etat_Lock && $etat <= self::Etat_Open){
			$this->start();
		}
		$this->etat = $etat;
		$this->CI->vlux_factory->update_etat_server($etat);
	}
	
	private function start()
	{
		// On arrête le serveur
		exec('bash '.$this->_sh.' stop');

		// On lance le serveur
		exec('bash '.$this->_sh.' start');
	}
	
	private function stop() {
		// On arrête le serveur
		exec('bash '.$this->_sh.' stop');
		// On déconnecte tous le monde
		$this->CI->vlux_factory->deco_joueur('all');
	}
	
	public function emit($e, $c) {
		
			$r ="socket.emit('";
			$r .= $e;
			$r .= "',";
			$r .= ($c);
			$r .= "); ";
			return $r;
	}

	public function join($room){
		$r="socket.join('".$room."'); ";
		return $r;
	}

	public function leave($room){
		$r = "socket.leave('".$room."'); ";
		return $r;
	}

	public function disconnect(){
		$r = "socket.disconnect(true); ";
		return $r;
	}

	public function broadcast($room, $event, $data){
		$r = "socket.broadcast.in('".$room."').emit('".$event."',".$data."); ";
		return $r;
	}
	
	public function set_data ($n, $v) {
		
		$r="socket.";
		$r.=$n;
		$r.=" = ";
		// Si le contenu est un tableau, on le transpose au format json
		if (is_array($v)){
			$v= json_encode($v);
		}
		// Sinon, c'est traité comme une chaine de caractère
		elseif(is_string($v)) {
			$v='"'.$v.'"';
		}
		$r.=$v;
		$r.="; ";
		return $r;
	}

	public function set_action($actions){
		//Reset
		$auth_methods = array(
			"authentifier"			=> 1,
			"afficher_map"			=> 0,
			"deco_walker"			=> 0,
			"deco_creator"			=> 0,
			"enregistrer_map"		=> 0,
			"new_gate"				=> 0,
			"new_dest"				=> 0,
			"new_gate"				=> 0,
			"next_dest"				=> 0,
			"create_gate"			=> 0,
			"append_dest"			=> 0,
			"create_dest"			=> 0,
			"add_dest"				=> 0,
			"supression_teleport"	=> 0,
			"teleportation_request"	=> 0,
			"update_teleport"		=> 0,
			"tchat_message"			=> 0
			);
		//Modfication des droits
		foreach ($actions as $action) {
			$auth_methods[$action] = 1;
		}
		$r='';
		foreach ($auth_methods as $methode => $value) {
			$r = $r.'socket.auth_methods.'.$methode.' = '.$value.'; ';
		}
		
		return $r;
	}
	
	public function user_session($user_session){
		$user_session = urldecode($user_session);
		$user_session = json_decode($user_session);
		return $user_session;
	}

	public function set_user_session ($user_session){
		$r ='';
		foreach($user_session as $key=>$value){
			if(is_string($value)){
				$value = '"'.$value.'"';
			}
			$r = $r.'socket.user_session.'.$key." = ".$value."; ";
		}
		return $r;
	}

	public function set_player_session ($pseudo, $params){
		$r = "set_one_client('".$pseudo."', ".json_encode($params).") ;";
		return $r;
	}
}
