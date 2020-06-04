<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : controleur de la page "vlux 3D"
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

class Vlux extends MY_Controller
{

	const Lien_Accueil = 1;
	const Lien_Aventure = 2;
	const Lien_Crea= 3;

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('vlux/vlux_factory', 'vlux/map_factory','vlux/item_factory'));
	}


// @Todo gestion d'accès en fonction de la config du jeu
	public function index(){
		// On autorise l'accès à la map en fonction de l'état du serveur et de rang du joueur
		/*if(!$this->vlux_factory->is_auth()){
			return $this->server_closed();
		}*/
		/**
		*		Spécial lancement test
		* Génération des map à la première visite
		**/
		$this->on_c_deja_vue($this->session->userdata('id'));
		$this->aventure();
	}

	public function server_closed(){
		$vars['title'] = 'La Map est indisponible.';
		$vars['etat_server'] = $this->vlux_factory->vlux_config->server_etat;
		return $this->layout->view('vlux/server_closed', $vars);
	}

	public function creation($id) {
		if (is_numeric($id)){
			$map = $this->map_factory->get_map($id);
			// droit de l'user
			if ($map->proprio_id==$this->session->userdata('id') || $this->bouzouk->is_admin()){
				//configuration du map_crea
				$vars['map'] = array(
									'id' => $id,
									'type' => $map->type);// Id de la map à demander au webservice
				// Chargement de la carte
				$this->load->library('vlux/item_factory');
				$vars['objets']= $this->item_factory->get_ressources($map->type, $this->session->userdata('rang'));
				array_shift($vars['objets']['utilitaires']['portes']);
				$vars['title'] = "Creator";
				$vars['lien'] = self::Lien_Crea;
				$vars['io_url'] = get_io_url();
				return $this->layout->view('vlux/map_creator', $vars);
			}
			else {
				$this->attention('Vous ne pouvez pas modifier cette carte');
				redirect('vlux');
			}
		}
		elseif(ENVIRONMENT == 'development') {
			show_error("Problême id map : $id", 500);
		}
		else{
			show_404();
		}
	}

	public function gestion(){

		//On récupère la liste des maps 
		$maps = $this->map_factory->list_own_maps($this->session->userdata('id'));
		$vars['maps'] = $maps;
		$vars['title'] = "Mes maps";
		$vars['lien'] = self::Lien_Crea;
		$this->layout->view('vlux/accueil_crea', $vars);
	}

	public function aventure(){
		// on localise le bouzouk
		$map_id = $this->vlux_factory->vlux_gps($this->session->userdata('id'));
		$map = $this->map_factory->get_map($map_id['map_id']);

		//Info à transmettre au web service
		$vars['map'] = array(
			'id' => $map->id,
			'type' => $map->type
			);
		// Données pour la vue
		$vars['lien'] = self::Lien_Aventure;
		$vars['title'] = "Version test";
		$vars['io_url'] = get_io_url();
		return $this->layout->view('vlux/map_walker', $vars);	
	}

	private function on_c_deja_vue($id){
		$query = $this->db->where('proprio_id', $id)->get('vlux_maps');
		if($query->num_rows() > 0){
			return true;
		}
		else{
			// Création des deux maps pour le test
			$map_ext = array(
				'id'			=> 'tmp',
				'nom'			=> 'Bac à sable de '.$this->session->userdata('pseudo'),
				'type'			=>'exterieur',
				'auth_level'	=> 4,
				'proprio_id'	=> $id,
				'prix'			=> 0,
				'monnaie'		=> 'strul',
				'pseudo_proprio'=>$this->session->userdata('pseudo'),
				'size'			=> 20
				);
			$map_int = array(
				'id'			=> 'tmp',
				'nom'			=> 'Taudis de '.$this->session->userdata('pseudo'),
				'type'			=>'interieur',
				'auth_level'	=> 4,
				'prix'			=> 0,
				'monnaie'		=> 'strul',
				'proprio_id'	=> $id,
				'pseudo_proprio'=>$this->session->userdata('pseudo'),
				'size'			=> 10
				);
			$map_ext = $this->map_factory->new_map($map_ext);
			$map_int = $this->map_factory->new_map($map_int);
			$map_ext =$this->map_factory->create_map($map_ext);
			$res_principale = $this->map_factory->create_map($map_int);

			//Positionnement du joueur sur la place de la mairie
			$coord = array(
				'map_id'	=> 2,
				'map_x'		=> 1,
				'map_y'		=> 1
				);

			$this->vlux_factory->vlux_gps_update($coord, $id);
			//Définition de la résidence principale part défaut.
			$this->load->library('vlux/vlux_param_joueur');
			$this->vlux_param_joueur->set_res_principale($res_principale);
			return true;
		}
	}

}