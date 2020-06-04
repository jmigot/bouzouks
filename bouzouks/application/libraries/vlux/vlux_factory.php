<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Vlux_factory {

	private $CI;
	private $manager;
	public $vlux_config;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->manager = $this->get_manager();
		$this->vlux_config = $this->manager->get_config();
	}

	private function get_manager(){
		$this->CI->load->model('vlux_manager');
		return $this->CI->vlux_manager;
	}

	public function is_auth(){
		$server = $this->get_server();
		return $server->is_auth();
	}

	public function for_client(){
		foreach($this->vlux_config as $key => $value){
			$config[$key] = $value;
		}
		//Supression de l'état du serveur de la liste
		unset($config['server_etat']);
		return $config;
	}

	public function update_config($config){
		$this->manager->update($config);
	}

	public function get_bouzouk_rang($id){
		$rang = $this->CI->db->select('rang')->where('id', $id)->get('joueurs');
		$rang = $rang->row();
		$rang = $rang->rang;
		return $rang;
	}

	public function get_auth_level($id){
		$rang = $this->get_bouzouk_rang($id);
		if($this->CI->bouzouk->is_admin(null, $rang)){
			$auth_level = 3;
		}
		elseif($this->CI->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats, $id)){
			$auth_level = 2;
		}
		else{
			$auth_level = 1;
		}
		return $auth_level;
	}

	public function get_info() {
		$data['config'] = $this->vlux_config;
		$server = $this->get_server();
		foreach ($server->get_img(true) as $attribut => $valeur) {
			$data['etat_serveur'][$attribut]=$valeur;
		}
		return $data;
	}

	public function authentifier($id, $web_auth) {
		$query= $this->CI->db->select('id, mot_de_passe')
		->from('joueurs')
		->where('id', $id)
		->get();
		if ($query->num_rows() == 0 ){
			// La requête ne correspondond à aucun joueur
			return false;
		}
		else {
			$joueur = $query->row();
			
			// Authentification
			if(md5($joueur->id.':'.$joueur->mot_de_passe) == $web_auth) {
				return TRUE;
			}
		}
	}

	public function connecte_joueur($id, $mode) {
		if($mode !=1 && $mode !=2 && $mode !=10){
			return false;
		}
		$this->CI->db->set('map_connecte',$mode)
		->where('id', $id)
		->update('joueurs');
		return true;
	}

	public function is_connected($id){
		$query = $this->CI->db->select('map_connecte')
			->from('joueurs')
			->where('id', $id)
			->where('map_connecte >', 0)
			->get();
			if($query->num_rows() == 1){
				return true;
			}
			else{
				return false;
			}
	}
		
	public function deco_joueur($id, $map=null, $x=null, $y=null) {
		//Vérification si le joueur est valide

		// Passage au statut déconnecté
		$set['map_connecte'] = 0;
		// Déconnexion de tous les joueurs
		if($id =='all'){
			$where=null;
		}
		// D'un joueur spécifié
		elseif(ctype_digit($id)){
			$where['id'] = $id;
		}
		else{
			return log_message('error', "deconnection ratée, id invalide : $id");
		}
		//Avec mise à jour de la position du joueur ( en mode aventure uniquement)
		if(ctype_digit($id) && ctype_digit($map) && ctype_digit($x) && ctype_digit($y)){
			$set ['map_id']=$map;
			$set['map_x']=$x;
			$set['map_y']=$y;
		}
		if(!ctype_digit($map) && !is_null($map)){
			 return log_message('error',"deco joueur id de la map invalide : $map");
		}

		$this->manager->deco_joueur($set, $where);
	}

	public function get_server()
	{
		$etat = intval($this->vlux_config->server_etat);
		$this->CI->load->library('vlux/server', array('etat'=>$etat));
		$server = $this->CI->server;
		// On retourne un objet  Server_type . Le driver implémente les méthodes spécifiques au serveur cible
		 return $server;
	}

	public function update_etat_server($etat){
		$this->manager->update_server($etat);
	}

	// Récupère les info utiles sur le joueur
	public function get_joueur($id){
		$joueur = $this->CI->db->select('j.pseudo, j.rang, j.sexe, j.map_id, j.map_x, j.map_y, j.map_tchat_statut, j.interdit_tchat, j.map_avatar_id, a.*, m.type AS map_type')
							   ->from('joueurs j')
							   ->join('vlux_avatar a', 'a.id = j.map_avatar_id')
							   ->join('vlux_maps m', 'm.id = j.map_id' )
							   ->where('j.id', $id)
							   ->get();
		$joueur = $joueur->row();
		return $joueur;
	}
	
// Permet de localiser un bouzouk dans vlurx 3D ainsi que récupérer l'avatar pour la map.

	public  function vlux_gps($id){
		$bouzouk = $this->CI->db->select('joueurs.pseudo, joueurs.map_id, joueurs.rang, joueurs.map_x, joueurs.map_y, vlux_avatar.*')
								->where('joueurs.id', $id)
								->from('joueurs')
								->join('vlux_avatar','vlux_avatar.id = joueurs.map_avatar_id', 'inner')
								->get();
		$bouzouk = $bouzouk->row_array();
		if(!$this->CI->map_factory->is_map($bouzouk['map_id'])){
			$map_team = $this->CI->map_factory->get_team_maps();
			$key = rand(0, count($map_team));
			$bouzouk['map_id'] = $map_team[$key];
		}
		
		return $bouzouk;
	}

/**
 * Mise à jour de la position d'un bouzouk dans vlurx 3D
 * @param array 		$coord = array(
 *			'map_id'	=> $teleport->map_id,
 *			'map_x'		=> $teleport->x,
 *			'map_y'		=> $teleport->y
 *			);
 * 
 * @return void
 **/

	public function vlux_gps_update($coord, $id){
		$query = $this->CI->db->where('id', $id)->update('joueurs', $coord);
	}

/**
 * Liste des objets servant de monnaie pour les items de la map
 * 
 * @return array $objets;
 **/
 
 	public function monnaie_select(){

 		$monnaie_select = array(
 			'strul'	=>'Strul(s)',
 			'fragment'=>'Fragment(s)'
 			);
 		// On récupère la liste des objets du jeu
 		$query = $this->CI->db->select('id, nom')->get('objets');
 		$objects_list = $query->result();
 		foreach ($objects_list as $object) {
 			$monnaie_select[$object->id] = $object->nom;
 		}
 		// On rajoute les strulls et les fragments
 		return $monnaie_select;
 	}

 	public function make_zone($tuiles){
 		$this->CI->load->library('vlux/item_factory');
 		//converstion des tuiles en tableau
 		$zone = $this->CI->map_factory->ppmap2array($tuiles);
 		// récupération des tuiles du jeu
 		$list_tuiles = $this->CI->item_factory->get_tuiles();
 		// gnérération de la carte de zone
 		foreach ($zone as $rzi => $row_zone) {
 			foreach ($row_zone as $zi => $z) {
 				foreach($list_tuiles as $tuile){
 					if($tuile['img']== $z){
 						if($tuile['infranchissable']==1){
 							$zone[$rzi][$zi]=0;
 						}
 						else{
 							$zone[$rzi][$zi]=1;
 						}
 					}
 				}
 			}
 		}
 		// conversion au format pour pp3Diso
 		$zone = $this->CI->map_factory->array2ppmap($zone);
 		return $zone;
 	}

 	public function make_object_map($map_size){
 		// Définition du tileset
 		$tile_set = array(
 			68, // arbre
 			101, //fleur
 			1, //tulipe
 			array(110, 111, 112, 113, 114), // chemin
 			85 // herbe
 			);
 		$map_objects = array();
 		mt_srand();
 		$nb_objects = mt_rand(($map_size/2), $map_size);
 		$no = 0;
 		while($no<=$nb_objects){
 			// On prend une case au hazard
 			$ox = mt_rand(1, ($map_size));
 			$oy = mt_rand(1, ($map_size));
 			// S'il n'y a rien sur cette case
 			if(!isset($map_objects[$ox][$oy])){
 				// On choisie un objet
 				$objet = mt_rand(0, 100);
 				if(in_array($objet, array(50,60,70))){
 					$objet_id = $tile_set[0];
 				}

 				elseif(in_array($objet, array(1,2,71,72))){
 					$objet_id = $tile_set[1];
 				}
 				elseif(in_array($objet, array(3,4,33,34,43,44))){
 					$objet_id = $tile_set[2];
 				}
 				elseif(in_array($objet, array(5,6,15,16,25,26))){
 					$ic = mt_rand(0,4);
 					$objet_id = $tile_set[3][$ic];
 				}
 				elseif(in_array($objet, array(7,8,9,10,11,17,18,19,20,21,87,88,89,90,91))){
 					$objet_id = $tile_set[4];
 				}
 				else{
 					$objet_id = null;
 				}

 				if(isset($objet_id) && $objet_id!=null){
 					$map_objects[] = array("x"=>$ox, "y"=>$oy, "z"=>0, "vid"=>$objet_id);
 					$no++;	
 				}	
 			}
 		}
 		return $map_objects;
 	}

 	public function nouveau_joueur($id, $pseudo){
 		$this->CI->load->library('vlux/map_factory');
 		$this->CI->load->library('vlux/vlux_param_joueur');
 		$map_id = $this->CI->map_factory->nouveau_joueur($id, $pseudo);
 		$this->CI->vlux_param_joueur->nouveau_joueur($id);
 		$coord = array(
 			'map_id'	=> $map_id,
 			'map_x'		=> 5,
 			'map_y'		=> 5
 			);
 		$this->CI->vlux_param_joueur->set_res_principale($map_id, $id);
 		$this->vlux_gps_update($coord, $id);
 	}

 	public function supprimer_joueur($id){
 		$this->CI->load->library('vlux/map_factory');
 		$this->CI->load->library('vlux/vlux_param_joueur');
 		$this->CI->map_factory->supprimer_joueur($id);
 		$this->CI->vlux_param_joueur->supprimer_joueur($id);
 	}
}