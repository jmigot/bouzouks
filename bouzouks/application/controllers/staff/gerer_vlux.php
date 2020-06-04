<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : Administration de vlurx 3D et gestion des paramètres
 *
 * Auteur      : Hikingyo
 * Date        : Décembre 2014
 * Màj		   : Juin 2015
 *
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */
 
class Gerer_vlux extends MY_Controller
{
	private $game;

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('vlux/vlux_factory', 'vlux/map_factory'));
		$this->game = $this->vlux_factory->vlux_config;
	}

	public function index()
	{
		$vars = $this->vlux_factory->get_info();
		$vars['title'] = "Gestion de Vlux";
		$vars['lien'] = 1;
		$vars['nb_maps_open'] = $this->map_factory->count_maps_open();
		$vars['nb_maps'] = $this->map_factory->count_maps();
		return $this->layout->view('staff/vlux/gerer_vlux', $vars);
	}

	public function gestion()
	{
		// Param layout
		$vars['title'] = "Paramètres de Vlux";
		$vars['lien'] = 5;
		$vars['config'] =$this->game;
		//Seuls les admins peuvent y accéder
		if ($this->bouzouk->is_admin()){
			//Si on reçoit un formulaire
			if(isset($_POST) && !empty($_POST)){
				//chargement de la librairie
				$this->load->library('form_validation');
				// Régles de validation du paramétrage de Vlux
				$rules = array(
							// Param généraux
							array(
								'field'	=> 'img_prefix',
								'label'	=> 'prefixe image',
								'rules'	=> 'alpha'
								),
							array(
								'field'	=> 'img_path',
								'label'	=> 'dossier images',
								'rules'	=> 'required'
								),
							array(
								'field'	=> 'map_slide',
								'label'	=> 'glissement de la carte',
								'rules'	=> 'required|is_natural|max_length[1]'
								),
							array(
								'field'	=> 'fluid',
								'label'	=> 'Fluidité',
								'rules'	=> 'required|is_natural|max_length[1]'
								),
							array(
								'field'	=> 'speed_avatar',
								'label'	=> 'vitesse avatar',
								'rules'	=> 'required|is_natural|max_length[6]'
								),
							array(
								'field'	=> 'move_avatar_speed',
								'label'	=> "Vitesse de l'animation de l'avatar",
								'rules'	=> 'required|is_natural|max_length[6]'
								),
							array(
								'field'	=> 'speed_map',
								'label'	=> 'vitesse map',
								'rules'	=> 'required|is_natural|max_length[6]'
								),
							array(
								'field'	=> 'speed_map_while',
								'label'	=> 'vitesse map_while',
								'rules'	=> 'required|is_natural|max_length[6]'
								),
							// Zoom
							array(
								'field'	=> 'zoom_default',
								'label'	=> 'zoom par défaut',
								'rules'	=> 'required|numeric|max_length[5]'
								),
							array(
								'field'	=> 'zoom_min',
								'label'	=> 'zoom mini',
								'rules'	=> 'required|numeric|max_length[5]|less_than[zoom_max]'
								),
							array(
								'field'	=> 'zoom_max',
								'label'	=> 'zoom maxi',
								'rules'	=> 'required|numeric|max_length[5]'
								),
							array(
								'field'	=> 'zoom_pas',
								'label'	=> 'pas du zoom',
								'rules'	=> 'required|numeric|max_length[3]'
								),
							// Curseur
							array(
								'field'	=> 'cursor_z_index',
								'label'	=> 'decalage z du curseur',
								'rules'	=> 'required|numeric|max_length[10]'
								),
							// Pathfinder
							array(
								'field'	=> 'pathfinding',
								'label'	=> 'etat pathfinder',
								'rules'	=> 'required|natural|max_length[1]'
								),
							array(
								'field'	=> 'cursor_z_index',
								'label'	=> 'decalage z du curseur',
								'rules'	=> 'required|numeric|max_length[10]'
								),
							array(
								'field'	=> 'PF_decx',
								'label'	=> 'decalage x ',
								'rules'	=> 'required|numeric|max_length[4]'
								),
							array(
								'field'	=> 'PF_decy',
								'label'	=> 'decalage y ',
								'rules'	=> 'required|numeric|max_length[4]'
								),
							array(
								'field'	=> 'PF_corners',
								'label'	=> 'gestion diagonal',
								'rules'	=> 'required|is_natural|max_length[1]'
								),
							array(
								'field'	=> 'PF_max',
								'label'	=> 'nbr cases max',
								'rules'	=> 'required|is_natural|less_than[6]'// Limite du pf pour éviter surcharge client
								),
							// Bulles
							array(
								'field'	=> 'bulle_auto_x',
								'label'	=> 'Etat bulle',
								'rules'	=> 'required|is_natural|max_length[1]'
								),
							array(
								'field'	=> 'bulle_deca_y',
								'label'	=> 'décalage y',
								'rules'	=> 'required|is_natural|max_length[4]'
								),
							array(
								'field'	=> 'bulle_auto_y',
								'label'	=> 'position vertical',
								'rules'	=> 'alpha'
								),
							array(
								'field'	=> 'cursor_delay',
								'label'	=> 'delai curseur',
								'rules'	=> 'required|is_natural|max_length[4]'
								),
							array(
								'field'	=> 'map_prix_mairie',
								'label'	=> "Taux d'achat par la mairie",
								'rules'	=> 'required|is_natural_no_zero'
								)
					);
				//Définition des règles de validation du formulaire
				$this->form_validation->set_rules($rules);
				// Si le formulaire est valide
				if($this->form_validation->run()){
					// Récupération des donnée
					$this->get_form();
					//Maj de la congif
					$this->vlux_factory->update_config($this->game);
					// Bravo, clapclap
					$this->succes("La configuration a été mise à jour !");
				}
			}
			// Affichage de la page
			return $this->layout->view('staff/vlux/param_jeu', $vars);
			
		}
		else{
			show_404();
		}
	}

	/*
	*	Fonction de récupération des données du formulaire
	*/

	private function get_form(){

		$this->game->img_prefix =$this->form_validation->set_value('img_prefix');
		if($this->game->img_prefix == null){$this->game->img_prefix ='';}
		$this->game->img_path =$this->form_validation->set_value('img_path');
		$this->game->map_slide =$this->form_validation->set_value('map_slide');
		$this->game->fluid =$this->form_validation->set_value('fluid');
		$this->game->speed_avatar =$this->form_validation->set_value('speed_avatar');
		$this->game->move_avatar_speed =$this->form_validation->set_value('move_avatar_speed');
		$this->game->speed_map =$this->form_validation->set_value('speed_map');
		$this->game->speed_map_while =$this->form_validation->set_value('speed_map_while');
		$this->game->zoom_default =$this->form_validation->set_value('zoom_default');
		$this->game->zoom_min =$this->form_validation->set_value('zoom_min');
		$this->game->zoom_max =$this->form_validation->set_value('zoom_max');
		$this->game->zoom_pas =$this->form_validation->set_value('zoom_pas');
		$this->game->cursor_z_index =$this->form_validation->set_value('cursor_z_index');
		$this->game->pathfinding =$this->form_validation->set_value('pathfinding');
		$this->game->cursor_PF =$this->form_validation->set_value('cursor_PF');
		$this->game->PF_decx =$this->form_validation->set_value('PF_decx');
		$this->game->PF_decy =$this->form_validation->set_value('PF_decy');
		$this->game->PF_corners =$this->form_validation->set_value('PF_corners');
		$this->game->PF_max =$this->form_validation->set_value('PF_max');
		$this->game->bulle_auto_x =$this->form_validation->set_value('bulle_auto_x');
		$this->game->bulle_auto_y =$this->form_validation->set_value('bulle_auto_y');
		$this->game->bulle_deca_y =$this->form_validation->set_value('bulle_deca_y');
		$this->game->cursor_delay =$this->form_validation->set_value('cursor_delay');
		$this->game->map_prix_mairie = $this->form_validation->set_value('map_prix_mairie')/10;
	}


}
