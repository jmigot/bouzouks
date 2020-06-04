<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : webservices privés pour la map 3D isométrique (controller appelé par Node.js)
 *
 * @Autor      : Jean-Luc Migot (jluc.migot@gmail.com)
 * @Contributor : Hikingyo
 * @Date        : juin 2015
 *
 * Copyright (C) 2012-2015 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les 
 * auteurs.
 * 
 * @version 0.2.3
 */

class Webservices_map extends CI_Controller
{
    // Définition des attributs du webservices
	private $rep ;
	private $ordres;
	private $vlux_server;
	private $user_session;

	const Mode_Aventure = 1;
	const Mode_Crea = 2;
	const Mode_Tchat = 10;

	const Notice 	= 'notice';
	const Succes 	='succes';
	const Alerte 	= 'alerte';
	const Erreur	='erreur';
	const Save_map_ok ='La carte a bien été enregistrée.';
	const Del_teleport_ok = 'Le téléport a été supprimé.';

        public function __construct()
    {
        parent::__construct();

        // Ce controller ne peut être appelé qu'en console
        if ( ! $this->input->is_cli_request()){
            show_404();
		}
		$this->load->library(array('vlux/vlux_factory', 'vlux/map_factory', 'vlux/item_factory', 'vlux/teleport_factory'));
		$this->vlux_server = $this->vlux_factory->get_server();
    }
    	
	// 													Méthodes API

    /**
     * send-serv()
     *
     * @return string $rep : les instruction à exécuter par le server node
     */
    private function send_serv() 
    {
		
		// Préparation du changement de salon
		if(isset($this->ordres['join']))
		{
			foreach($this->ordres['join'] as $room_to_join){
				$this->rep.= $room_to_join;
			}
		}
		
		// Préparation du broadcast
		if(isset($this->ordres['broad']))
		{
			foreach ($this->ordres['broad'] as $room) {
				foreach ($room as $event) {
					$this->rep.= $event;
				}
			}
		}

    	// Préparation des signaux
    	if (isset($this->ordres['signals']))
		{
			foreach ($this->ordres['signals'] as $value) {
				$this->rep.=$value;
			}
		}		
		// Préparation des données
		// On retourne au script directement les instructions pour les nouvelles valeurs pour la socket en cours
		if(isset($this->ordres['data']))
		{
			foreach($this->ordres['data'] as $v){
				$this->rep.=$v;
			}
		}

		if(isset($this->ordres['disconnect'])){
			$this->rep.= $this->ordres['disconnect'];
		}

		// Préparation de la session 
		if(isset($this->ordres['user_session'])){
			$this->rep.= $this->ordres['user_session'];
		}

		// On envoie la réponse formaté qui sera exécuter automatiquemnt par le serveur node.
		// Ainsi, on peut programmer l'ensemble depuis ici ^^
		//@TODO boucler sur les array pour éditer les sous ensembles
        echo $this->rep;
    }

	private function emit($e, $c){
		$r = $this->vlux_server->emit($e,$c);
		$this->ordres['signals'][]= $r;
	}

	private function join($room){
		$r = $this->vlux_server->join($room);
		$this->ordres['join'][$room]= $r;
	}

	private function leave($room){
		$r = $this->vlux_server->leave($room);
		$this->ordres['join'][$room] = $r;
	}

	private function broadcast($room, $event, $content){
		$r = $this->vlux_server->broadcast($room, $event, $content);
		$this->ordres['broad'][$room][] = $r;
	}
	
	// Cette fonction permet de modifer les variables du serveur
	private function set_data($n, $v){
		$r = $this->vlux_server->set_data($n, $v);
		$this->ordres['data'][$n]=$r;
	}

	// Fonction de gestion des droits d'action
	private function set_action($actions){
		$r = $this->vlux_server->set_action($actions);
		$this->ordres['data']['auth_methods']= $r;
	}

	private function disconnect(){
		$r = $this->vlux_server->disconnect();
		$this->ordres['disconnect']= $r;
	}

	private function get_user_session($user_session){
		$this->user_session = $this->vlux_server->user_session($user_session);
	}

	private function set_user_session(){
		$r = $this->vlux_server->set_user_session($this->user_session);
		$this->ordres['user_session'] = $r;
	}

	private function set_player_session($pseudo, $params){
		$r = $this->vlux_server->set_player_session($pseudo, $params);
		$this->ordres['data'][$pseudo] = $r;
	}

	// Validation des teleports
	private function teleport_validation($teleport){
		// On utilise la validation de formulaire pour les données reçu en cli
		$this->load->helpers('form');
		$this->load->library('form_validation');
		//transposition des info reçu pour la validation
		foreach ($teleport as $key => $value) {
			$_POST[$key] = $value;
		}
		// définition des règles de validation
		$rules = array(
			array(
				'field'		=>'map_id',
				'label'		=>'identifiant de la map',
				'rules'		=>'is_natural_no_zero|required'
				),
			array('item_id', "identifiant de l'item",'required|is_natural'),
			array('type', 'type de téléport', 'requiered|alpha'),
			array('x', 'coordonnée en x', 'required|is_natural'),
			array('y', 'coordonnée en y', 'required|is_natural')
			);
		$this->form_validation->set_rules($rules);

		// Si le formulaire n'est pas valide
		if(!$this->form_validation->run()){
			return FALSE;
		}
		else{
			return TRUE;
		}
	}

	private function for_client($map_id){
		//Récupération de la map
		$data['map'] = $this->map_factory->for_client($map_id);
		//Récupération de la config
		$data['config'] = $this->vlux_factory->for_client();
		//Récupération des items
		$data['items'] = $this->item_factory->for_client($data['map']['type']);
		if(!$data['map'] || !$data['items']){
			return FALSE;
		}
		return $data;
	}

	public function deconnexion($user_session){
		$this->get_user_session($user_session);
		// Si le joueur est en mode aventure
		if($this->user_session->mode == self::Mode_Aventure){
			$this->deco_walker();
		}
		elseif($this->user_session->mode == self::Mode_Crea){
			$this->deco_creator();
		}
		elseif($this->user_session->mode == self::Mode_Tchat){
			$this->deco_creator();
		}
	}

	private function deco_creator(){
		$this->vlux_factory->deco_joueur($this->user_session->id);
		$this->disconnect();
		$this->send_serv();
	}

	private function deco_walker(){
		$this->vlux_factory->deco_joueur($this->user_session->id, $this->user_session->map_id, $this->user_session->map_x, $this->user_session->map_y);
		// Avis à la population !!
		$this->load->library('vlux/tchat_factory');
		$this->broadcast('chan_map_room_'.$this->user_session->map_id, 'outcoming_player', "'".$this->user_session->pseudo."'");
		$this->broadcast('chan_modo', 'outcoming_player' ,json_encode(array('chan_id'=>$this->user_session->map_id, 'id'=>$this->user_session->id, 'pseudo'=>$this->tchat_factory->get_profil($this->user_session->id, $this->user_session->pseudo, $this->user_session->rang))));
		$this->broadcast('chan_map_room_'.$this->user_session->map_id , 'tchat', json_encode(array('chan'=>'chan_map_room_'.$this->user_session->map_id , 'content'=>$this->user_session->pseudo." s'en va.", 'id'=>0, 'date'=>tchat_datetime(bdd_datetime()), 'pseudo'=>Tchat_factory::PSEUDO_INFO)));
		$this->leave('chan_map_room_'.$this->user_session->map_id);
		$this->broadcast('chan_global' , 'tchat', json_encode(array('chan'=>'chan_global' , 'content'=>$this->user_session->pseudo." vient de partir.", 'id'=>0, 'date'=>tchat_datetime(bdd_datetime()), 'pseudo'=>Tchat_factory::PSEUDO_INFO)));
		$this->leave('chan_global');
		$this->disconnect();
		$this->send_serv();
	}

	public function authentifier($joueur_id, $websocket_auth, $mode, $user_session){
		// On récupère la session de la socket
		$this->get_user_session($user_session);
		if(!ctype_digit($mode) && ($mode != self::Mode_Crea || $mode != self::Mode_Aventure || $mode != self::Mode_Tchat)){
			log_message('error', __FILE__.' line : '.__LINE__.' mode incorrect émit par '.$joueur_id);
		}
		// Il ne peut y avoir qu'une seule connection à la map par compte. 
		//Les admins ne peuvent pas se connecter à la map sous une autre identité si le joueur y est connecté.	
		if(ctype_digit($joueur_id) && $this->vlux_factory->authentifier($joueur_id, $websocket_auth) /*&& !$this->vlux_factory->is_connected($joueur_id)*/){
			if(!$this->vlux_factory->connecte_joueur($joueur_id, $mode)){
				$this->disconnect();
				log_message('error', __FILE__.' line : '.__LINE__.' erreur d\'authentification émise par '. $joueur_id);
				return;
			}
			else
			{
				// On récupère les info du joueur
				$joueur = $this->vlux_factory->get_joueur($joueur_id);
				$auth_level = $this->vlux_factory->get_auth_level($joueur_id);
				// On met à jour la session
				$this->user_session->mode = $mode;
				$this->user_session->id = $joueur_id;
				$this->user_session->map_id = $joueur->map_id;
				$this->user_session->map_type = $joueur->map_type;
				$this->user_session->map_x = $joueur->map_x;
				$this->user_session->map_y = $joueur->map_y;
				$this->user_session->pseudo = $joueur->pseudo;
				$this->user_session->avatar_img = $joueur->img;
				$this->user_session->avatar_decx = $joueur->dec_x;
				$this->user_session->avatar_decy = $joueur->dec_y;
				$this->user_session->sexe = $joueur->sexe;
				$this->user_session->tchat_statut =  strtotime($joueur->map_tchat_statut);
				$this->user_session->rang = $joueur->rang;
				$this->user_session->interdit_tchat = $joueur->interdit_tchat;
				$this->set_user_session();
				// Modfication des droits d'action
				if($mode != self::Mode_Tchat){
					// Niveau d'acréditation pour l'affichage des éléments de modé
					$this->emit('authentifie', $auth_level);
					$this->set_action(array('afficher_map'));
				}
				else{
					return $this->connexion_tchat_moderation();
				}
			}
		}
		else{
			$this->emit('alert','"Connexion refusée."');
			$this->disconnect();
		}
		return $this->send_serv();
	}

	public function connexion_tchat_moderation(){
		$this->load->library('vlux/tchat_factory');
		$this->set_action(array('tchat_message', 'get_histo', 'suppr_msg'));
		$this->join('chan_global');
		$this->join('chan_modo');// room pour transmettre les données réserver à la modération.
		$this->emit('tchat', json_encode(array('chan' =>'chan_global' ,'content'=>"Vous êtes connecté au chan général.", 'id'=>0, 'date'=>tchat_datetime(bdd_datetime()), 'pseudo'=>Tchat_factory::PSEUDO_INFO)));
		// On récupère la liste des déconnecté
		$chans = $this->tchat_factory->get_chans();
		$connectes = $this->tchat_factory->get_connectes('all');
		$con = array();
		$con['list_connectes'] = array();
		$chans = $this->tchat_factory->get_chans();
		$connectes = $this->tchat_factory->get_connectes('all');
		foreach ($chans as $key => $chan) {
			$con[$key]= array(
				'chan_id'	=>$chan['id'],
				'connectes'	=>0
				);
			foreach ($connectes as $kc => $connecte) {
				if($connecte->map_id == $chan['id']){
					$con[$key]['connectes']++;
					$con['list_connectes'][] = array('map_id'=>$connecte->map_id, 'id'=>$connecte->id, 'pseudo'=>$this->tchat_factory->get_profil($connecte->id, $connecte->pseudo, $connecte->rang));
				}
			}
		}
		$this->emit('tchat_connecte', json_encode($con));
		return $this->send_serv();
	}

	public function afficher_map( $socket_id, $map_id, $map_type='exterieur', $joueur_id=false, $user_session){
		$this->get_user_session($user_session);
		$data = $this->for_client($map_id);
		if(!$data){
			return NULL;
		}
		// Si on est en mode aventure, on récupère les coordonnée du joueur
		if($joueur_id != false && is_numeric($joueur_id)){
			$joueur = $this->vlux_factory->vlux_gps($joueur_id);
			// Ainsi que ses paramètres personnalisés
			$this->load->library('vlux/vlux_param_joueur');
			$data['param_joueur'] = $this->vlux_param_joueur->get_param($this->user_session->id);
			// En mode aventure, on vérifie que la map demandée correspond à celle stockée en bdd
			if($map_id != $joueur['map_id']){
				$this->disconnect();
				return $this->send_serv();
			}
			$this->load->library('vlux/tchat_factory');
			$data['avat'] = $joueur;
			if($this->user_session->interdit_tchat == 1){
				$this->set_action(array('teleportation_request', 'teleportation', 'avatar_info', 'move_player', 'get_histo'));
			}
			else{
				$this->set_action(array('teleportation_request', 'teleportation', 'tchat_message', 'avatar_info', 'move_player', 'get_histo'));
			}
			
			//On le connecte au chan global et au chan de la map et on émet les messages de connexion
			$this->join('chan_global');
			$this->join('chan_map_room_'.$map_id);
			$this->broadcast('chan_global', 'tchat', json_encode(array('chan'=>'chan_global', 'content'=>$joueur['pseudo']." vient d'arriver.", 'id'=>0, 'date'=>tchat_datetime(bdd_datetime()), 'pseudo'=>Tchat_factory::PSEUDO_INFO)));
			$this->broadcast('chan_map_room_'.$map_id , 'tchat', json_encode(array('chan'=>'chan_map_room_'.$map_id , 'content'=>$joueur['pseudo']." est là !", 'id'=>0, 'date'=>tchat_datetime(bdd_datetime()), 'pseudo'=>Tchat_factory::PSEUDO_INFO)));
			$this->emit('tchat', json_encode(array('chan' =>'chan_map_room_'.$map_id ,'content'=>"Vous parlez sur le chan '".$data['map']['nom']."'.", 'id'=>0, 'date'=>tchat_datetime(bdd_datetime()), 'pseudo'=>Tchat_factory::PSEUDO_INFO)));
			$this->emit('tchat', json_encode(array('chan' =>'chan_global' ,'content'=>"Vous parlez sur le chan général.", 'id'=>0, 'date'=>tchat_datetime(bdd_datetime()), 'pseudo'=>Tchat_factory::PSEUDO_INFO)));
			// On affiche son avatar sur la map des joueurs présents
			$player = array(
			'socket_id'=>$socket_id,
			'map_x'=>$joueur['map_x'],
			'map_y'=>$joueur['map_y'],
			'pseudo'=>$joueur['pseudo'],
			'decx'=>$joueur['dec_x'],
			'decy'=>$joueur['dec_y'],
			'img'=>$joueur['img']
			);
			$this->broadcast('chan_map_room_'.$map_id,'incoming_player', json_encode($player));
			$this->broadcast('chan_modo', 'incoming_player' ,json_encode(array('chan_id'=>$map_id, 'id'=>$joueur_id, 'pseudo'=>$this->tchat_factory->get_profil($joueur_id, $joueur['pseudo'], $joueur['rang']))));
		}
		else{
			// En mode édition, on donne au joueur les autorisations pour l'édition
			$this->set_action(array('enregistrer_map', 'new_teleport', 'new_dest','supression_teleport', 'update_teleport'));
		}
		$this->emit('afficher_map', json_encode($data));
		return $this->send_serv();	
	}

	public function change_map($map_id){
		$map = $this->map_factory->get_map($map_id);
		$data = $this->for_client($map->id);
		
		//On prépare la réponse à envoyer au créateur
		$this->emit('change_map', json_encode($data));
	}

	public function enregistrer_map($map_id, $tuiles, $decor=0, $user_session){
		$this->get_user_session($user_session);
		//check 
		if($this->map_factory->is_map($map_id)){
			$map = $this->map_factory->get_map($map_id);
		}
		$tuiles = $this->item_factory->check_tuiles($tuiles, $map->size);
		$decor = $this->item_factory->check_decor($decor, $map->size, $map->type, $this->user_session->rang);
		if(!$map_id || !$tuiles || !is_array($decor)){
			return false;
		}
		else{
			$map->tuiles = $tuiles;
			$map->zone = $this->vlux_factory->make_zone($tuiles);
			$map->decor = $decor;
			$this->map_factory->update_map($map);
		}
		$this->emit('message',json_encode(array(self::Notice, self::Save_map_ok)));
		return $this->send_serv();		
	}

	/*************************
	 * 	Methodes téléports 	 *
	 ************************/
	public function new_teleport($id_bouzouk, $map_id){

		$layout = $this->new_teleport_layout($id_bouzouk, $map_id);
		$this->emit('afficher_form_porte', json_encode(array($layout)));
		$this->set_action(array('next_gate', 'abort_form_teleport'));
		//Changement des droits d'action
		return $this->send_serv();
	}

	private function new_teleport_layout($id_bouzouk, $map_id){
		// Config du formulaire
		$vars['opt_form'] = array('id'=> 'new_teleport');
		$vars['opt_select_dest'] = $this->destination_map_select($map_id);
		// Génération du code html
		$layout = $this->load->view('vlux/new_gate_form', $vars, true);
		return $layout;
	}

	public function new_dest($map_id, $map_x, $map_y, $bouzouk_id){
		// Récupération de la porte
		$teleport = $this->teleport_factory->get_teleport_by_coord($map_id, $map_x, $map_y);
		if($teleport){
			$message = $this->new_dest_layout($bouzouk_id, $teleport);
			$this->set_action(array('next_dest', 'abort_form_teleport'));
			$this->emit('afficher_form_porte', json_encode(array($message)));
			return $this->send_serv();
		}
	}


	private function new_dest_layout($bouzouk_id, $teleport){
		// Config du formulaire
		$vars['opt_form'] = array('id'=> 'new_dest');
		$vars['opt_select_dest'] = $this->destination_map_select($teleport->map_id);
		// Génération du code html
		$layout = $this->load->view('vlux/add_dest_form', $vars, true);
		return $layout;
	}

	public function abort_form_teleport(){
		$this->set_action(array('enregistrer_map', 'new_teleport', 'new_dest','supression_teleport', 'update_teleport'));
		$this->send_serv();
	}

	public function next_gate( $gate_dep_map, $gate_arr_map, $gate_id, $gate_x, $gate_y, $gate_type){
		$gate = array(
			'map_id'		=> $gate_dep_map,
			'item_id'		=> $gate_id,
			'type'			=> $gate_type,
			'x'				=> $gate_x,
			'y'				=> $gate_y,
			'destination'	=> $gate_arr_map
			);
		// Si le formulaire n'est pas valide
		if(!$this->teleport_validation($gate)){
			$this->emit('message',json_encode(array(self::Notice, validation_errors())));
			$this->emit('abort_gate', $gate['item_id']);
			$this->set_action(array('new_teleport', 'new_dest'));
			return $this->send_serv();
		}
		// Sinon
		else{

			// On met la première porte en cache
			$this->load->library('lib_cache_cli');
			$register_key = $this->lib_cache_cli->store($gate_type.'_'.$gate_x.$gate_y, $gate, 3600);

			$this->change_map($gate_arr_map);
			//On configure le Creator
			$cursor_id = ($gate_type=='sens_unique'?teleport_factory::Teleport_type_bloque:constant('teleport_factory::Teleport_type_'.$gate_type));
			$this->emit('arrival_gate',json_encode(array($cursor_id, $register_key, $gate_dep_map)));
			$this->set_action(array('create_gate', 'create_dest', 'abort_teleport'));
			// Envoie de la réponse
			return $this->send_serv();
		}
	}

	public function create_gate( $gate_dep_map, $gate_arr_map, $gate_id, $gate_x, $gate_y, $gate_type, $register_key){
		$arr_gate = array(
			'map_id'		=> $gate_dep_map,
			'item_id'		=> $gate_id,
			'type'			=> $gate_type,
			'x'				=> $gate_x,
			'y'				=> $gate_y,
			'destination'	=> $gate_arr_map
			);
		// Si le formulaire n'est pas valide
		if(!$this->teleport_validation($arr_gate)){
			$this->emit('message',json_encode(array(self::Notice, validation_errors())));
			$this->emit('abort_gate', $gate['item_id']);
			$this->set_action(array('new_teleport', 'new_dest'));
			// On renvoie le créateur sur la map de départ.
			$this->change_map($gate_arr_map);
			return $this->send_serv();
		}
		// Sinon
		else{
			//On récupère le cache
			$this->load->library('lib_cache_cli');
			$dep_gate = $this->lib_cache_cli->fetch($register_key);
			array_pop($dep_gate);
			// On supprime le registre
			$this->lib_cache_cli->clear_register($register_key);
			//on enregistre les teleports
			$id_porte_depart = $this->teleport_factory->create_teleport($dep_gate);
			$arr_gate['destination']=$id_porte_depart;
			$id_porte_arrivee = $this->teleport_factory->create_teleport($arr_gate);
			$this->teleport_factory->finalize_teleport($id_porte_depart, $id_porte_arrivee);
			$this->set_action(array('new_teleport', 'new_dest', 'enregistrer_map', 'supression_teleport', 'update_teleport'));
			// On renvoie le créateur sur la map de départ.
			$this->change_map($gate_arr_map);
			return $this->send_serv();
		}
	}

	/**
	 * Stockage du premier téléport et envoie des info de la map de destination
	 **/
	public function next_dest($map_id, $x, $y, $type, $dest){
		if(!is_numeric($dest) AND $dest <= 0){
			$this->emit('message',json_encode(array(self::Notice, "Demande erronée !")));
			return $this->send_serv();
		}

		else{
			$gate = array(
				'map_id' 	=> $map_id,
				'x'			=> $x,
				'y'			=> $y,
			); 
			// On met la première porte en cache
			$this->load->library('lib_cache_cli');
			$register_key = $this->lib_cache_cli->store('new_dest_'.$map_id.$dest, $gate, 3600);


			$this->set_action(array('append_dest', 'add_dest', 'abort_teleport'));
			//On prépare la réponse à envoyer au joueur
			$this->change_map($dest);

			//On configure le Creator
			$cursor_id = ($type=='sens_unique'?teleport_factory::Teleport_type_bloque:constant('teleport_factory::Teleport_type_'.$type));
			$this->emit('arrival_gate',json_encode(array($cursor_id, $register_key, $map_id)));
			
			// Envoie de la réponse
			return $this->send_serv();
		}
	}

	/**
	 * création de destination.
	 * Le premier téléport existe, mais pas celui d'arrivée.
	 * 
	 **/
	public function append_dest($gate_dep_map, $gate_arr_map, $gate_id, $gate_x, $gate_y, $gate_type, $register_key){
		$this->load->library('lib_cache_cli');
		//Création du téléport de destination
		$arr_teleport = array(
			'map_id'		=> $gate_dep_map,
			'item_id'		=> $gate_id,
			'type'			=> $gate_type,
			'x'				=> $gate_x,
			'y'				=> $gate_y
			);
		
		//Lecture du registre
		$dep_teleport = $this->lib_cache_cli->fetch($register_key);
		//Suppression du registre
		$this->lib_cache_cli->clear_register($register_key);
		//Récupération du téléport de départ
		$dep_teleport = $this->teleport_factory->get_teleport_by_coord($dep_teleport['map_id'], $dep_teleport['x'], $dep_teleport['y']);

		//Enregistrement du téléport d'arrivée
		$arr_teleport['destination'] = $dep_teleport->id;
		$id_dest_teleport = $this->teleport_factory->create_teleport($arr_teleport);
		//Maj du téléport de départ
		$dep_teleport->destination[] = $id_dest_teleport;
		$this->teleport_factory->update_teleport($dep_teleport);

		$this->set_action(array('new_teleport', 'new_dest', 'enregistrer_map', 'supression_teleport', 'update_teleport'));
		// On renvoie le créateur sur la map de départ.
		$this->change_map($gate_arr_map);
		$this->send_serv();
	}

	/**
	 * Ajout destination
	 * Le téléport de départ est une création
	 **/
	public function create_dest($map_id, $x, $y, $register_key){
		$this->load->library('lib_cache_cli');

		$dep_teleport = $this->lib_cache_cli->fetch($register_key);
		$this->lib_cache_cli->clear_register($register_key);

		//Récupération du téléport d'arrivée
		$arr_teleport = $this->teleport_factory->get_teleport_by_coord($map_id, $x, $y);
		//Création du téléport de départ
		$dep_teleport['destination'] = $arr_teleport->id;
		$dep_teleport_id = $this->teleport_factory->create_teleport($dep_teleport);

		//Ajout de la destination au téléport d'arriver
		$arr_teleport->destination[] = $dep_teleport_id;
		$this->teleport_factory->update_teleport($arr_teleport);
		$this->set_action(array('new_teleport', 'new_dest', 'enregistrer_map', 'supression_teleport', 'upadte_teleport'));
		// On renvoie le créateur sur la map de départ.
		$this->change_map($dep_teleport['map_id']);
		$this->send_serv();
	}

	/**
	 * Ajout de destination
	 * Les deux téléport existent déjà
	 **/
	public function add_dest($map_id, $x, $y, $register_key){
		//Recupération du premier téléport
		$this->load->library('lib_cache_cli');
		$dep_teleport = $this->lib_cache_cli->fetch($register_key);
		$this->lib_cache_cli->clear_register($register_key);
		$dep_teleport = $this->teleport_factory->get_teleport_by_coord($dep_teleport['map_id'], $dep_teleport['x'], $dep_teleport['y']);

		// Récupération du deuxième téléport
		$arr_teleport = $this->teleport_factory->get_teleport_by_coord($map_id, $x, $y);

		//Mise à jour des téléports
		$dep_teleport->destination[]= $arr_teleport->id;
		$this->teleport_factory->update_teleport($dep_teleport);
		$arr_teleport->destination[]= $dep_teleport->id;
		$this->teleport_factory->update_teleport($arr_teleport);

		$this->set_action(array('new_teleport', 'new_dest', 'enregistrer_map', 'supression_teleport', 'update_teleport'));
		// On renvoie le créateur sur la map de départ.
		$this->change_map($dep_teleport->map_id);
		$this->send_serv();
	}

	public function abort_teleport($register_key){
		//Recupération du premier téléport
		$this->load->library('lib_cache_cli');
		$dep_teleport = $this->lib_cache_cli->fetch($register_key);
		// Suppression du registre
		$this->lib_cache_cli->clear_register($register_key);
		//Maj des droits
		$this->set_action(array('new_teleport', 'new_dest', 'enregistrer_map', 'supression_teleport', 'update_teleport'));
		// On renvoie le créateur sur la map de départ.
		$this->change_map($dep_teleport['map_id']);
		$this->send_serv();
	}

	public function supression_teleport($map_id, $x, $y){
		$teleport = $this->teleport_factory->get_teleport_by_coord($map_id, $x, $y);
		//Supression du téléport en bdd
		$this->teleport_factory->supression_teleport($teleport);
		//On recharge la map
		$this->change_map($map_id);
		return $this->send_serv();
	}

	public function update_teleport($old_x, $old_y, $new_x, $new_y, $map_id, $decor){
		if(!is_numeric($old_x) || !is_numeric($new_x) || !is_numeric($old_y) || !is_numeric($new_y)){
			return false;
		}
		$teleport = $this->teleport_factory->get_teleport_by_coord($map_id, $old_x, $old_y);
		if($this->map_factory->is_map($map_id)){
			$map = $this->map_factory->get_map($map_id);
		}
		else{
			return false;
		}
		$decor = $this->item_factory->check_decor($decor, $map->size, $map->type);
		if(!is_array($decor)){
			return false;
		}
		else{
			$teleport->x = $new_x;
			$teleport->y = $new_y;
			$this->teleport_factory->update_teleport($teleport);
			$map->decor = $decor;
			$this->map_factory->update_map($map);
		}
	}


	public function teleportation_request ($socket_id, $teleport_x, $teleport_y, $user_session){
	// On récupère la session de la socket
		$this->get_user_session($user_session);
		// On récupère le téléport de départ
		$teleport = $this->teleport_factory->get_teleport_by_coord($this->user_session->map_id, $teleport_x, $teleport_y);
		// On vérifie si le joueur peut le franchir
		if(!$this->teleport_factory->is_granted($this->user_session->id,$this->user_session->rang, $teleport)){
			// Le joueur ne peut pas franchir le teleport
			$this->emit('message', json_encode(array(self::Notice, "Vous ne pouvez pas franchir ce passage !")));
			return $this->send_serv();
		}
		$c_dest = count($teleport->destination);
		// Si plusieurs destinations possibles
		if($c_dest>1){
			// On envoie le formulaire de choix de destination
			$message = $this->teleportation_layout($teleport->destination);
			$this->emit('afficher_form_porte', json_encode(array($message)));
			return $this->send_serv();
		}
		// Si une seule destination possible
		elseif($c_dest == 1){
			// id du téléport de destination
			$id_teleport = $teleport->destination[0];
			// téléportation, monsieur Spoke !
			$this->teleportation($socket_id, $id_teleport);
		}
		// Sinon, bug ou tenta de hack
		else{
			error_log("Demande de téléportation erroné venant de".$this->user_session->pseudo);
		}
	}

	public function teleportation_layout($destinations){
		$vars['opt_form'] = array('id'=> 'destination_choix');

		// On prépare le select
		$options_select = array();
		foreach($destinations as $destination){
			$teleport = $this->teleport_factory->get_teleport($destination);
			$map = $this->map_factory->get_map($teleport->map_id);
			$options_select[$teleport->id] = $map->nom;
		}
		$vars['opt_select_dest'] = $options_select;
		// Génération du code html
		$vars['title'] = "Choix de la destination : ";
		$layout = $this->load->view('vlux/dest_form', $vars, true);
		return $layout;
	}

	public function teleportation ($socket_id, $id_teleport, $user_session = null){
		$this->load->library('vlux/tchat_factory');

		if($user_session != null){
			$this->get_user_session($user_session);
		}
		$dep_map_id = $this->user_session->map_id;

		$teleport = $this->teleport_factory->get_teleport($id_teleport);
		$coord = array(
			'map_id'	=> $teleport->map_id,
			'map_x'		=> $teleport->x,
			'map_y'		=> $teleport->y
			);
		$this->tp_to($coord, $dep_map_id, $socket_id);
	}

	public function tp_to($coord, $dep_map_id, $socket_id){
		// On retire le joueur de l'ancienne map
		$this->broadcast('chan_map_room_'.$this->user_session->map_id, 'outcoming_player', "'".$this->user_session->pseudo."'");
		$this->broadcast('chan_modo', 'outcoming_player' ,json_encode(array('chan_id'=>$this->user_session->map_id, 'id'=>$this->user_session->id, 'pseudo'=>$this->tchat_factory->get_profil($this->user_session->id, $this->user_session->pseudo, $this->user_session->rang))));

		// On va chercher la map de destination
		$map = $this->map_factory->get_map($coord['map_id']);
		$data = $this->for_client($map->id);

		$this->vlux_factory->vlux_gps_update($coord, $this->user_session->id);
		$bouzouk = $this->vlux_factory->vlux_gps($this->user_session->id);
		$data['avat'] = $bouzouk;
		// Mise à jour de la session
		$this->user_session->map_id = $coord['map_id'];
		$this->user_session->map_type = $map->type;
		$this->user_session->map_x = $coord['map_x'];
		$this->user_session->map_y = $coord['map_y'];
		$this->set_user_session();
		// Changement de chan
		$this->broadcast('chan_map_room_'.$dep_map_id , 'tchat', json_encode(array('chan'=>'chan_map_room_'.$dep_map_id , 'content'=>$bouzouk['pseudo']." s'en va.", 'id'=>0, 'date'=>tchat_datetime(bdd_datetime()), 'pseudo'=>Tchat_factory::PSEUDO_INFO)));
		$this->leave('chan_map_room_'.$dep_map_id);
		$this->join('chan_map_room_'.$coord['map_id']);
		//On déplace le joueur et on diffuse sur le tchat
		$this->emit('change_map', json_encode($data));
		$this->broadcast('chan_map_room_'.$coord['map_id'] , 'tchat', json_encode(array('chan'=>'chan_map_room_'.$coord['map_id'] , 'content'=>$bouzouk['pseudo']." est là !", 'id'=>0, 'date'=>tchat_datetime(bdd_datetime()), 'pseudo'=>Tchat_factory::PSEUDO_INFO)));
		$this->emit('tchat', json_encode(array('chan' =>'chan_map_room_'.$coord['map_id'] ,'content'=>"Vous parlez sur le chan ".$data['map']['nom'].'.', 'id'=>0, 'date'=>tchat_datetime(bdd_datetime()), 'pseudo'=>Tchat_factory::PSEUDO_INFO)));
		// On affiche son avatar sur la map des joueurs présents
		$player = array(
			'socket_id'=>$socket_id,
			'map_x'=>$coord['map_x'],
			'map_y'=>$coord['map_y'],
			'pseudo'=>$this->user_session->pseudo,
			'decx'=>$bouzouk['dec_x'],
			'decy'=>$bouzouk['dec_y'],
			'img'=>$bouzouk['img']
			);
		$this->broadcast('chan_map_room_'.$map->id,'incoming_player', json_encode($player));
		$this->broadcast('chan_modo', 'incoming_player' ,json_encode(array('chan_id'=>$this->user_session->map_id, 'id'=>$this->user_session->id, 'pseudo'=>$this->tchat_factory->get_profil($this->user_session->id, $this->user_session->pseudo, $this->user_session->rang))));
		
		return $this->send_serv();
	}

	private function destination_map_select($map_id){
		$options = array();
		// Liste des destinations existantes
		$list_destinations = $this->teleport_factory->get_map_destinations($map_id);
		//on ajoute la map pour qu'elle ne soit pas dans les destinations possibles
		$list_destinations[] = $map_id;
		// Liste des destinations possibles
		$list_destinations = $this->map_factory->get_possible_destination($list_destinations);
		//Formatage pour le select
		foreach($list_destinations as $map){
			$options[$map['id']] = $map['nom'];
		}
		return $options;
	}

	public function avatar_info($map_x, $map_y, $actual_x, $actual_y, $socket_to_emit, $user_session){
		// Récupération de la session
		$this->get_user_session($user_session);
		//Check données
		if(!ctype_digit($map_x) || !ctype_digit($map_y) || !ctype_digit($actual_x) || !ctype_digit($actual_y)){
			log_message('error', __FILE__.' '.__LINE__.' '.' : coordonnées non numériques recues de '.$this->user_session->pseudo);
			return;
		}
		// Préparation des infos à envoyer
		$player_info = array(
			'map_x'		=> $map_x,
			'map_y'		=> $map_y,
			'actual_x'	=> $actual_x,
			'actual_y'	=> $actual_y,
			'img'		=> $this->user_session->avatar_img,
			'decx'		=> $this->user_session->avatar_decx,
			'decy'		=> $this->user_session->avatar_decy,
			'pseudo'	=> $this->user_session->pseudo
			);
		$this->broadcast( $socket_to_emit,'player_info', json_encode($player_info));
		$this->send_serv();
	}

	/***************
	*  Vlux Tchat  *
	***************/

	private function tchat_emit($message){
		$message_to_send = array(
			'chan' 			=> $message['chan_id'],
			'message_id'	=> $message['id'],
			'date'			=> $message['date'],
			'pseudo'		=> $message['pseudo'],
			'content'		=> $message['content'],
			'raw_pseudo'	=> $this->user_session->pseudo
			);
		// Retour à la socket émettrice
		$this->emit('tchat', json_encode($message_to_send));
		// Emition sur le channel 
		$this->broadcast($message['chan_id'] , 'tchat', json_encode($message_to_send));
	}

	public function tchat_message ($socket_id, $chan_id, $message_content, $user_session = false){
		if(!$user_session){
			return;
		}
		// On récupère la session de la socket
		$this->get_user_session($user_session);
		$message_content = urldecode($message_content);
		$message_content = str_replace(array("&#40;", "&#41;"), array("(", ")"), $message_content);
		$this->load->library('vlux/tchat_factory');
		// On vérifie que le channel est valide
		$bouzouk = $this->vlux_factory->vlux_gps($this->user_session->id);
		//Seuls les admins peuvent envoyer des messages sans être présents
		if(!$this->bouzouk->is_admin(null, $this->user_session->rang)){
			// On vérifie si le joueur est bien connecté à la map
			if(!$this->vlux_factory->is_connected($this->user_session->id)){
				return false;
			}
			// On vérifie que le joueur a accés au chan
			if($chan_id!='chan_global' && $chan_id!='chan_map_room_'.$this->user_session->map_id)
				return false;
		}
		// Si le joueur est "chuté"
		if($this->user_session->tchat_statut >= time()){
			$action = array(
				'chan'		=> $chan_id,
				'message_id'	=> 0,
				'date'			=>tchat_datetime(bdd_datetime()),
				'pseudo'		=> Tchat_factory::PSEUDO_BOT,
				'content'		=> "Hey ".$this->user_session->pseudo." ! On t'as pas dit de te taire toi !! O(><)o",
				'raw_pseudo'	=> $this->user_session->pseudo
				);
			$this->emit('tchat', json_encode($action));
			return $this->send_serv();
		}
		//Parsing du message
		$action = $this->tchat_factory->parse_message($this->user_session->id, $this->user_session->pseudo,$this->user_session->rang, $message_content, $chan_id);
		if($action['action']=='message'){
			//Limitation du spam ( 1 message par seconde)
			if($this->user_session->tchat_derniere_requete >= (time()-1) && !$this->bouzouk->is_admin(null, $this->user_session->rang)){
				$action = array(
					'chan_id' 		=> $chan_id,
					'id'			=> 0,
					'date'			=> tchat_datetime(bdd_datetime()),
					'pseudo'		=> Tchat_factory::PSEUDO_BOT,
					'content'		=> "Hey ".$this->user_session->pseudo." ! Détends toi un peu (><)"
					);
			}
			else{
				$this->user_session->tchat_derniere_requete = time();
			}
			$this->set_user_session();
			$this->tchat_emit($action);
			return $this->send_serv();
		}
		elseif($action['action']=='commande'){
			// Si le joueur a déjà lancer une commande depuis moins de 1 minutes
			if($this->user_session->tchat_derniere_requete >= (time()-60) && !$this->bouzouk->is_admin(null, $this->user_session->rang)){

				$action = array(
					'chan_id' 		=> $chan_id,
					'id'			=> 0,
					'date'			=> tchat_datetime(bdd_datetime()),
					'pseudo'		=> Tchat_factory::PSEUDO_BOT,
					'content'		=> "Doucement ".$this->user_session->pseudo.", ou je t'occis les métacarpes !"
					);
			}
			else{
				$this->user_session->tchat_derniere_requete = time();
			}
			$this->set_user_session();
			$this->tchat_emit($action);
			return $this->send_serv();
		}
		elseif($action['action'] == 'chut'){
			// On notifie le chan global de l'action de modé
			$this->set_player_session($action['id'], array('tchat_statut'=>time()+$action['duree']*60));
			$message = array(
				'chan_id' 	=> 'chan_global',
				'id'		=> 0,
				'date'		=> tchat_datetime(bdd_datetime()),
				'pseudo'	=> Tchat_factory::PSEUDO_INFO,
				'content'	=> $action['pseudo']." a été privé de parole pour une durée de ".pluriel($action['duree'],"minute").".");
			$this->tchat_emit($message);
			return $this->send_serv();
		}
		elseif($action['action'] == 'dechut'){
			$this->set_player_session($action['id'], array('tchat_statut'=>time()));
			$message = array(
				'chan_id' 	=> 'chan_global',
				'id'		=> 0,
				'date'		=> tchat_datetime(bdd_datetime()),
				'pseudo'	=> Tchat_factory::PSEUDO_BOT,
				'content'	=> "Ok ".$action['pseudo']." çà va pour cette fois, tu peux de nouveau t'exprimer."
				);
			$this->tchat_emit($message);
			return $this->send_serv();
		}
		elseif($action['action'] == 'tp_home'){
			return $this->teleportation($socket_id, $action['dest_id']);
		}
		elseif($action['action'] == 'tp_to'){
			return $this->tp_to($action['coord'], $this->user_session->map_id, $socket_id);
		}
		else{
			return;
		}
		
	}

	public function get_histo($chan, $user_session){
		$this->load->library('vlux/tchat_factory');
		$this->get_user_session($user_session);
		if($this->bouzouk->is_admin(null, $this->user_session->rang)){
			$limit = 200;
		}
		else{
			$limit = 50;
		}
		$histo = $this->tchat_factory->get_histo($chan, $limit);
		foreach ($histo as $key=>$message) {
			$this->emit('tchat', json_encode(array( 'chan'=> $message['chan_id'], 'message_id'=> $message['id'], 'date'=> $message['date'], 'pseudo'=> $message['pseudo'], 'content'=> $message['content'])));
		}
		return $this->send_serv();
	}

	public function suppr_msg($messages_ids, $user_session){
		
		$messages_ids = urldecode($messages_ids);
		$messages_ids = json_decode($messages_ids);
		$this->load->library('vlux/tchat_factory');
		$this->get_user_session($user_session);
		// Si le joueur n'a pas les droits
		if(! $this->bouzouk->is_moderateur(bouzouk::Rang_ModerateurTchats, $this->user_session->rang)){
			return;
		}
		if (empty($messages_ids)) {
			return;
		}
		$chans = $this->tchat_factory->delete_message($messages_ids, $this->user_session->id, $this->user_session->pseudo, $this->user_session->rang);
		//TODO maj des chans concernés
		$this->emit('delete_msg_confirm', json_encode($messages_ids));
		foreach ($chans as $chan) {
			$this->broadcast($chan->chan_id, 'delete_msg_confirm', json_encode($messages_ids));
		}
		return $this->send_serv();
	}

	/*********************
	* 	Multi joueur     *
	*********************/
	public function move_player($to_x , $to_y, $user_session){
		$this->get_user_session($user_session);
		if(!ctype_digit($to_x) || !ctype_digit($to_y)){
			log_message('error', __FILE__.' '.__LINE__.' : coordonnées invalides émises par '.$this->user_session->pseudo);
			return;
		}
		$this->broadcast('chan_map_room_'.$this->user_session->map_id,'movePlayer', json_encode(array('pseudo'=>$this->user_session->pseudo,'x'=> $to_x, 'y' => $to_y)));
		// Mise à jour de la session
		$this->user_session->map_x = $to_x;
		$this->user_session->map_y = $to_y;
		$this->set_user_session();
		return $this->send_serv();
	}
}
// fin webservice vlux3d