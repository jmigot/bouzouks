<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Projet      : Bouzouks
 * Description : Librairie de gestion et de control des maps de Vlux 3D
 *
 * Auteur      : Hikingyo
 * Date        : Novembre 2014
 * Revision    : Avril 2015
 *
 * Copyright (C) 201-2015 Hikingyo - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */


class Map_factory {

	private $CI;
	private $manager;

	const Mode_Hors_Ligne = 0;
	const Mode_Admin = 1;
	const Mode_Crea = 2;
	const Mode_Ouvert = 3;

	public function __construct(){
		$this->CI =& get_instance();
		$this->manager = $this->get_manager();
	}

	private function get_manager(){
		$this->CI->load->model('map_manager');
		return $this->CI->map_manager;
	}

	public function get_map($id) {
		$data = $this->manager->get_map($id);
		if ($data != null)//@todo correction de la condition
		{
			// on retourne un objet Map correspondant à celle demandée.

			$this->CI->load->library('vlux/map');
			$map = new $this->CI->map($data);
			return $map;
		}
		elseif(ENVIRONMENT == 'development'){
			log_message('error', "Aucune map ne correspond à votre demande : ");
		}
		else{
			return FALSE;
		}
	}

	public function for_client($map_id) {
		$map = $this->get_map($map_id);
		foreach ($map as $attr => $value) {
			$map_client[$attr] = $value;
		}
		return $map_client;
	}

	public function list_maps() {
		$maps = $this->manager->list_maps();
		return $maps;
	}

	public function list_own_maps($bouzouk_id){
		return $this->manager->list_own($bouzouk_id);
	}

	public function get_possible_destination($list_destinations){
		// En phase de test, les joueurs peuvent posser un téléport sur n'importe quelles maps
		// En phase d'exploitation, seules les propriétés du joueur pourrons être reliées
		return $this->manager->get_possible_destination($list_destinations);
	}

	public function count_maps(){ 
		return $this->manager->count_maps();
	}

	public function count_maps_open() {
		return $this->manager->count_maps_open();
	}

	public function new_map($map= NULL){
		if($map == NULL){
			$map = array(
			'id'			=> 'tmp',
			'nom'			=> 'nouveau terrain',
			'type'			=>'interieur',
			'prix'			=> 0,
			'monnaie'		=> 'strul',
			'proprio_id'	=> 2,
			'pseudo_proprio'=>'Mairie',
			'size'			=> 10,
			'statut_vente'	=> 0
			);
		}
		$this->CI->load->library('vlux/map');
		// on retourne un objet Item par défaut.
		$map =new $this->CI->map($map);
		return $map;
	}

	public function create_map($map){

		/*	Définition du remplissage par défaut */

		$size = $map->size;
		$type = $map->type;
		$tuile = ($type =='interieur')?1420144320:1;
		//Génération de la map
		$tuiles='';
		for ($i=0; $i < $size ; $i++) { 
			if($i>0){
				$tuiles .=':';
			}
			for ($j=0; $j < $size ; $j++) { 
				if($j>0){
					$tuiles .=',';
				}
				$tuiles .= $tuile;
			}
		}
		// Définition du terrain par défaut
		$map->tuiles = $tuiles;
		$map->zone = $this->CI->vlux_factory->make_zone($tuiles);
		$map->decor = ($type=='exterieur')?$this->CI->vlux_factory->make_object_map($map->size):array();
		$map->batiments = array();
		// Etat par défaut

		$map->etat = self::Mode_Crea;

		//Création de la map
		$id = $this->manager->create($map);

		// Maj de l'id de la map
		$map->id = $id;

		//Retour de la map créée
		return $map->id;
	}

	public function delete_map($id){
		$this->manager->erase($id);
	}

	public function update_map($map){
		$this->manager->update($map);
	}

	public function set_mode_map($id, $etat){
		//On récupère la map
		$map = $this->get_map($id);
		//Ainsi que les membres connectés dessus
		$bouzouks = $map->get_connecte();
		// Action à mener en fonction de l'état demandé
		switch ($etat) {
			case self::Etat_Hors_Ligne :
				// Déconnexion de tout les joueurs de la map. Cette n'est plus accéssible que sur le map Editor
				$this->game->deco_joueur('all', $map->id);
				// Changement d'état de la carte
				break;
			case self::Etat_Admin :
				// Déconnexion de tout le monde sauf admins. La map n'est accéssible qu'en section admin
				if(!$this->CI->bouzouk->is_admin()){
					$this->game->deco_joueur($id);
				}
				break;
			case self::Etat_Crea :
				// Déconnexion de tout les joueurs. Blocage d'accès en mode aventure
				$this->game->deco_joueur('all', $map->id);
				break;
			case self::Etat_Test :
				// Déconnexion de tout les joueurs sauf béta testeur et au dessus. Blocage du mode créa
				if(!$this->CI->bouzouk->is_admin() OR !$this->CI->bouzouk->is_beta_testeur()){
					$this->game->deco_joueur();
				}
				break;
			default:
				# code...
				break;
		}
		// Changement d'état de la map
		$map->etat = $etat;
		// Mise à jour de la bdd
		$this->update_map($map);
	}

	public function suprimer_decor($map_id, $x, $y, $vid){
		//Extraction du tableau des éléments du décor de la map
		$map = $this->get_map($map_id);
		foreach ($map->decor as $key => $item) {
			//Lorsqu'on trouve l'item
			if($item['x']== $x AND $item['y']== $y AND $item['vid']== $vid){
				// On le suprime
				array_splice($map->decor, $key, 1);
			}
		}
		// Mise à jour de la map
		$this->update_map($map);
	}


	public function is_map($map_id){
		if(ctype_digit($map_id)){
			return $this->manager->is_map($map_id);
		}
		else{
			return FALSE;
		}
	}

	public function get_team_maps(){
		return $this->manager->team_maps();
	}

	public function get_mairie_maps(){
		return $this->manager->mairie_maps();
	}

	public function check_zone($zone, $map_size){
		$zone = str_replace(array("-","_"), array(":",","), $zone );
		$zone_array = explode(':', $zone);
		if(count($zone_array) != (intval($map_size))){
			return FALSE;
		}
		foreach ($zone_array as $zone_row) {
			$zone_row = explode(',', $zone_row);
			if(count($zone_row)!= (intval($map_size))){
				return FALSE;
			}
		}
		return $zone;
	}

	public function ppmap2array($str){
		$tab = explode(':', $str);
		foreach($tab as $row_index => $row){
			$tab[$row_index] = explode(',', $row);
		}
		return $tab;
	}

	public function array2ppmap($array){
		$str = '';
		for ($i=0; $i < count($array) ; $i++) { 
			if($i>0){
				$str .=':';
			}
			for ($j=0; $j < count($array[$i]) ; $j++) { 
				if($j>0){
					$str .=',';
				}
				$str .= $array[$i][$j];
			}
		}
		return $str;
	}

	public function nouveau_joueur($id, $pseudo){
		$map = array(
		'id'			=> 'tmp',
		'nom'			=> 'Taudis de '.$pseudo,
		'type'			=>'interieur',
		'prix'			=> 0,
		'monnaie'		=> 'strul',
		'proprio_id'	=> $id,
		'pseudo_proprio'=> $pseudo,
		'size'			=> 10
		);
		$map = $this->new_map($map);
		$map_id = $this->create_map($map);
		return $map_id;
	}

	// Supression d'un joueur
	public function supprimer_joueur($id){
		// Liste des maps du joueur
		$list_maps = $this->list_own_maps($id);
		foreach($list_maps as $map){
			// Si c'est la map offerte à tous, on la supprime
			// Sinon, on vide le décor et on l'attribue à la mairie
			$plop = null;

		}
	}

	// Remise à zéro des compteur de changement de nom de map
	public function reset_changement_nom_map(){
		return $this->manager->reset_changement_nom();
	}

	//Liste des maps vendues par la mairie
	public function get_vente_mairie(){
		return $this->manager->get_vente(2);
	}

	public function cession_terrain($map_id, $joueur_id){
		return $this->manager->changement_proprio($map_id, $joueur_id);
	}

}