<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : modération/administration des maps
 *
 * Auteur      : Hikingyo
 * Date        : septembre 2014
 *
 * Copyright (C) 2014 Hikingyo - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Gerer_maps extends MY_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('vlux/vlux_factory', 'vlux/map_factory'));

	}
	
	public function index()
	{
		// On récupère toutes les maps du jeu
		$vars['maps'] = $this->map_factory->list_maps();
		$vars['title'] = "Gestion Maps";
		$vars['lien'] = 3;
		return $this->layout->view('staff/vlux/gerer_maps', $vars);
	}
	/**
	*	Map Creator : interface de création de décor
	**/
	public function map_creator($id) {
		$this->load->library('vlux/item_factory');
		if (is_numeric($id)){
			$map = $this->map_factory->get_map($id);
			// Info à demander au webservice
			$vars['map'] = array(
								'id' => $id,
								'type' => $map->type);
			$vars['objets']= $this->item_factory->get_ressources($map->type, $this->session->userdata('rang'));
			$vars['lien'] = 3;
			$vars['io_url'] = get_io_url();
			return $this->layout->view('vlux/map_creator', $vars);
		}
		elseif(ENVIRONMENT =='development') {
			show_error("Problême avec le Map Creator : $id", 500);//todo : mode dev ->error, mode prod -> redirection list map avec message
		}
		else{
			$this->echec('Problême avec le Map Creator');
			redirect('staff/gerer_maps');
		}
	}

	public function map_editor($id){
		// Si la map existe et qu'il n'y a pas de formulaire à valider
		if($id !='new' && empty($_POST)){
			$map = $this->map_factory->get_map($id);
			// On prépare la liste des proprios possibles
			$vars['proprios'] = $this->bouzouk->select_joueurs(array(
				'name'          => 'proprio_id',
				'joueur_id'     => $map->proprio_id,
				'status_not_in' => array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_ChoixPerso, Bouzouk::Joueur_GameOver, Bouzouk::Joueur_Banni))
			);
			$vars['title'] = "Modification de la map $map->nom";
			$vars['map'] = $map;
			$vars['lien'] = 3;
			return $this->layout->view('staff/vlux/map_editor', $vars);
		}
		// Création d'un map
		elseif($id == 'new' && empty($_POST)){
			// On va chercher une map standard
			$vars['map'] = $this->map_factory->new_map();
			// On prépare la liste des proprios possibles
			$vars['proprios'] = $this->bouzouk->select_joueurs(array(
				'name'          => 'proprio_id',
				'joueur_id'     => 2,// Par défaut, la mairie
				'status_not_in' => array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_ChoixPerso, Bouzouk::Joueur_GameOver, Bouzouk::Joueur_Banni))
			);
			// Génération de la vue
			$vars['title'] = "Création d'une map";
			$vars['lien'] = 3;
			return $this->layout->view('staff/vlux/map_editor', $vars);
		}
		// Si on reçoit un formulaire de l'éditeur
		elseif(isset($_POST) && !empty($_POST)){
			$this->load->library('form_validation');
			// Régles de validation de l'éditeur de map
			$rules = array(
						array(
							'field'	=> 'id',
							'label'	=> 'Id de la map',
							'rules'	=> 'required'
							),
						array(
							'field'	=> 'type',
							'label'	=> 'type de la map',
							'rules'	=> 'required|callback_type_check'
							),
						array(
							'field'	=> 'prix',
							'label'	=> 'prix',
							'rules'	=> 'required|is_natural'
							),

						array(
							'field'	=> 'monnaie',
							'label'	=> 'monnaie',
							'rules'	=> 'required|callback_monnaie_check'
							),
						array(
							'field'	=> 'nom',
							'label'	=> 'Nom de la map',
							'rules'	=> 'required|max_length[30]'
							),
						array(
							'field'	=> 'proprio_id',
							'label'	=> 'identifiant du proprio',
							'rules'	=> 'required|is_natural'
							),
						array(
							'field'	=> 'size',
							'label'	=> 'Dimension de la map',
							'rules'	=> 'required|is_natural|max_length[4]'
							)
				);
			//Définition des règles de validation du formulaire
			$this->form_validation->set_rules($rules);
			//Traitement du formulaire
			if(!$this->form_validation->run()){
				// Si la map est nouvelle
				if ($id =='tmp'){
					//création d'une map type
					$map= $this->map_factory->new_map();
					// Data pour la vue
					$map->id =$this->form_validation->set_value('id');
					$map->type =$this->form_validation->set_value('type');
					$map->nom =$this->form_validation->set_value('nom');
					$map->prix =$this->form_validation->set_value('prix');
					$map->monnaie =$this->form_validation->set_value('monnaie');
					$map->proprio_id = $this->form_validation->set_value('proprio_id');
					$map->size =$this->form_validation->set_value('size');
					$vars['map']=$map;
				}
				// Si la map existe déjà
				elseif(is_numeric($id) && $id>0){
					// On la récupère
					$map = $this->map_factory->get_map($id);
					$vars['map'] = $map;
				}
				// On prépare la liste des proprios possibles
				$vars['proprios'] = $this->bouzouk->select_joueurs(array(
					'name'          => 'proprio_id',
					'joueur_id'     => $map->proprio_id,
					'status_not_in' => array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_ChoixPerso, Bouzouk::Joueur_GameOver, Bouzouk::Joueur_Banni))
				);
				// Génération de la vue
				$vars['title'] = "Modification de la map $map->nom";
				$vars['lien'] = 3;
				return $this->layout->view('staff/vlux/map_editor',$vars);
			}
			// Si le formulaire est valide
			else{
				// Création de map
				if ($id =='tmp'){
					$map= $this->map_factory->new_map();
					$this->succes("La map a bien été créée !");
				}
				// Modification
				elseif(is_numeric($id) && $id>0){
					$map = $this->map_factory->get_map($id);
					$this->succes("La map $map->nom a bien été mise à jour !");
				}
				// Hack
				else{
					show_error("Cette map n'existe pas !!", 500);
				}
				//récupération des données
				$map->id =$this->form_validation->set_value('id');
				$map->type =$this->form_validation->set_value('type');
				$map->nom =$this->form_validation->set_value('nom');
				$map->prix =$this->form_validation->set_value('prix');
				$map->monnaie =$this->form_validation->set_value('monnaie');
				$map->proprio_id = $this->form_validation->set_value('proprio_id');
				$map->size = $this->form_validation->set_value('size');
			}
			//Fin formulaire
			// Maj de la map en bdd
			if ($id != 'tmp'){
				// Si la map existe déjà, on fait une maj
				$this->map_factory->update_map($map);
				// Direction la liste des maps
				redirect('staff/gerer_maps');
			}
			elseif($id =='tmp'){
				// Sinon, on la crée
				$this->load->library('vlux/item_factory');
				$map_id = $this->map_factory->create_map($map);
				// Direction Map Creator
				redirect('staff/gerer_maps/map_creator/'.$map_id);
			}
		}
		//Bug
		else{
			show_error("Problême avec l'éditeur de map.", 500);
		}
	}

	public function monnaie_check(){
		if(!in_array($this->input->post('monnaie'), array('strul','fragment'))){
			$this->form_validation->set_message('Veuillez choisir une monnaie !');
			return FALSE;
		}
		elseif($this->input->post('type')== 'exterieur' && $this->input->post('monnaie')!= 'strul'){
			$this->form_validation->set_message('monnaie_check','Les maps de type exterieur doivent avoir un prix en struls !');
			return FALSE;
		}
		else{
			return TRUE;
		}
	}

	public function type_check(){
		if(!in_array($this->input->post('type'), array('exterieur','interieur','batiment','prison', 'special'))){
			$this->form_validation->set_message('type_check','Le type de la map est incorrect !');
			return FALSE;
		}
		else{
			return TRUE;
		}
	}

	public function effacer_map($id){
		if(is_numeric($id) && $this->bouzouk->is_admin()){
			$this->map_factory->delete_map($id);
			redirect('staff/gerer_maps');
		}
		elseif(ENVIRONMENT == 'development'){
			show_error("ID : $id invalide ou rang du joueur insuffisant !", 500);
		}
		else{
			show_404();
		}
	}

}
