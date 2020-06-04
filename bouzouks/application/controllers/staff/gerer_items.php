<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Controleur de gestion des items présent dans Vlurx 3D
 * 
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 * 
 * @author       Hikingyo <hikingyo@outlook.com>
 * @copyright    Team Bouzouk 2015 (C) 2015 Hikingyo - Tous droits réservés
 * @package      bouzouks\vlux\controllers\staff
 * 
 */
class Gerer_items extends MY_Controller
{
	

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('vlux/vlux_factory', 'vlux/item_factory'));
		$this->vars['title'] = "Gestion Items";
		$this->vars['lien'] = 4;

	}
	
	public function index()
	{
		// On récupère toutes les items du jeu
		$items = $this->item_factory->get_list();
		// On affiche la liste des items disponibles
		$this->vars['items'] = $items;
		return $this->layout->view('staff/vlux/gerer_items', $this->vars);
	}
	
	public function item_editor($id=0){

		$this->vars['opt_monnaie'] = $this->vlux_factory->monnaie_select();
		// Affichage de l'éditeur avec l'item à modifier
		if($id !='new_item' && $id !='new_tuile' && empty($_POST)){
			$item = $this->item_factory->get_item($id);
			$this->vars['item'] = $item;
			if($item->type == 'sols'){
				return $this->layout->view('staff/vlux/tuile_editor', $this->vars);
			}
			else{
				return $this->layout->view('staff/vlux/item_editor', $this->vars);
			}
			
		}// Création d'un item
		elseif($id == 'new_item'){
			$this->vars['item'] = $this->item_factory->new_item();
			return $this->layout->view('staff/vlux/item_editor', $this->vars);
		}
		elseif($id == 'new_tuile'){

			$this->vars['item'] = $this->item_factory->new_item();
			$this->vars['item']->type = 'sols';
			$this->vars['item']->nature = 'normale';
			return $this->layout->view('staff/vlux/tuile_editor', $this->vars);
		}
		// Si on reçoit un formulaire de l'éditeur
		elseif(isset($_POST) && !empty($_POST)){
			$this->load->library('form_validation');
			// Régles de validation de l'éditeur d'item
			$rules = array(
						array(
							'field'	=> 'id',
							'label'	=> 'Id de l\'item',
							'rules'	=> 'required'
							),
						array(
							'field'	=> 'type',
							'label'	=> 'type de l\'item',
							'rules'	=> 'required|callback_type_check'
							),
						array(
							'field'	=> 'cat',
							'label'	=> 'catégorie de l\'item',
							'rules'	=> 'required|callback_cat_check'
							),
						array(
							'field'	=> 'nom',
							'label'	=> 'Nom de l\'item',
							'rules'	=> 'required|max_length[30]'
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
							'field'	=> 'decx',
							'label'	=> 'decx de l\'item',
							'rules'	=> 'required|integer|max_length[5]'
							),
						array(
							'field'	=> 'decy',
							'label'	=> 'decy de l\'item',
							'rules'	=> 'required|integer|max_length[5]'
							),
						array(
							'field'	=> 'titre',
							'label'	=> 'Titre de l\'infobulle',
							'rules'	=> 'max_length[30]'
							),
						array(
							'field'	=> 'bulle',
							'label'	=> "Contenu de l'infobulle",
							'rules'	=> 'max_length[255]'
							),
						array(
							'field'	=> 'zone',
							'label' => 'zone',
							'rules'	=> 'required'
							),
						array(
							'field'	=> 'infranchissable',
							'label'	=> 'infranchissable',
							'rules'	=> ''
							),
						array(
							'field'	=> 'nature',
							'label'	=> 'nature',
							'rules'	=> 'callback_nature_check'
							),
						array(
							'field'	=> 'support',
							'label' => 'support',
							'rules' => 'integer|max_length[1]'
							),
						array(
							'field'	=> 'dropable',
							'label' => 'peut être poser',
							'rules' => 'integer|max_length[1]'
							),
						array(
							'field'	=> 'water_dropable',
							'label'	=> 'est posable sur l\'eau',
							'rules'	=> 'integer'
							),
						array(
							'field' => 'hauteur',
							'label'	=> 'hauteur de l\'objet',
							'rules'	=> 'is_natural'),
						array(
							'field'	=> 'auth_level',
							'label'	=> "niveau d'accès",
							'rules' => 'required|callback_auth_level_check')
				);
			$this->form_validation->set_rules($rules);
			//Traitement du formulaire
			if(!$this->form_validation->run()){
				if ($id =='tmp'){
				$item= $this->item_factory->new_item();
				$item->id =$this->form_validation->set_value('id');
				$item->type =$this->form_validation->set_value('type');
				$item->cat =$this->form_validation->set_value('cat');
				$this->img = $this->form_validation->set_value('img');
				$item->nom =$this->form_validation->set_value('nom');
				$item->prix =$this->form_validation->set_value('prix');
				$item->monnaie =$this->form_validation->set_value('monnaie');
				$item->decx =$this->form_validation->set_value('decx');
				$item->decy =$this->form_validation->set_value('decy');
				$item->titre =$this->form_validation->set_value('titre');
				$item->bulle =$this->form_validation->set_value('bulle');
				$item->zone = $this->form_validation->set_value('zone');
				$item->infranchissable = $this->form_validation->set_value('infranchissable');
				$item->nature = $this->form_validation->set_value('nature');
				$item->support = $this->form_validation->set_value('support');
				$item->dropable = $this->form_validation->set_value('dropable');
				$item->water_dropable = $this->form_validation->set_value('water_dropable');
				$item->hauteur = $this->form_validation->set_value('hauteur');
				$item->auth_level = $this->form_validation->set_value('auth_level');
				$this->vars['item']=$item;
				}
				elseif(is_numeric($id) && $id>0){
					$this->vars['item'] = $this->item_factory->get_item($id);
				}
				
				if($this->vars['item']->type == 'sols'){
					return $this->layout->view('staff/vlux/tuile_editor',$this->vars);
				}
				else{
					return $this->layout->view('staff/vlux/item_editor', $this->vars);
				}
			}
			else{
				if ($id =='tmp'){
					$item= $this->item_factory->new_item();
				}
				elseif(is_numeric($id) && $id>0){
					$item = $this->item_factory->get_item($id);
				}
				else{
					show_error("Problême avec l'id de l'item' : $id", 500);
				}
				//récupération des données
				$item->id =$this->form_validation->set_value('id');
				$item->type =$this->form_validation->set_value('type');
				$item->cat =$this->form_validation->set_value('cat');
				$this->img = $this->form_validation->set_value('img');
				$item->nom =$this->form_validation->set_value('nom');
				$item->prix =$this->form_validation->set_value('prix');
				$item->monnaie =$this->form_validation->set_value('monnaie');
				$item->decx =$this->form_validation->set_value('decx');
				$item->decy =$this->form_validation->set_value('decy');
				$item->titre =$this->form_validation->set_value('titre');
				$item->bulle =$this->form_validation->set_value('bulle');
				$item->zone = $this->form_validation->set_value('zone');
				$item->infranchissable = $this->form_validation->set_value('infranchissable');
				$item->nature = $this->form_validation->set_value('nature');
				$item->support = $this->form_validation->set_value('support');
				$item->dropable = $this->form_validation->set_value('dropable');
				$item->water_dropable = $this->form_validation->set_value('water_dropable');
				$item->hauteur = $this->form_validation->set_value('hauteur');
				$item->auth_level = $this->form_validation->set_value('auth_level');
				// Traitement du fichier
				if (is_uploaded_file($_FILES['itemfile']['tmp_name'])){
					$this->load->library('upload');
					// Paramétrage de l'upload
					//Si l'item existe déjà, on récupère le nom du fichier
					if($id!='tmp'){
						$filename = $item->img;
					}
					else{
						$file_name = time();
					}
					$config = array(
						'upload_path'	=> './webroot/images/map/objets/',
						'allowed_types'	=> 'png',
						'file_name'		=> $file_name,
						'overwrite' 	=> TRUE,
						'max_size'		=> '4096', //Kb => 500Mo
						'max_width'		=> '800',
						'max_length'	=> '800',
						'max_filename'	=> '30',
						'remove space'	=> TRUE
						);
					$this->upload->initialize($config);
					//Traitement de l'upload
					if(!$this->upload->do_upload('itemfile')){
						$this->vars['errors'] = $this->upload->display_errors();
						$this->echec($this->vars['errors']);
					}// L'upload est valide, on traite le fichier.
					else{
						// On met à jour l'item
						$file = $this->upload->data();
						$item->img = $file['raw_name'];
						$item->tx = $file['image_width'];
						$item->ty = $file['image_height'];
					}
				}
			}//Fin formulaire
			//Si aucune erreur n'est levée
			if(!isset($this->vars['errors'])){
				// Maj de l'item en bdd
				if ($id != 'tmp'){// Si l'item existe déjà, on fait une maj
					$this->item_factory->update_item($item);
				}
				elseif($id =='tmp'){
					unset($item->id);
					$id = $this->item_factory->create_item($item);
					$item->id = $id;

				}
				// Envoie du message et des données pour la vue
				$this->succes("L'item a bien été enregistré.");
			}
			$this->vars['item']=$item;
			// Retour à l'éditeur
			//TOdo redirection en fonction du type d'item
			if($item->type == 'sols'){
				$this->layout->view('staff/vlux/tuile_editor', $this->vars);
			}
			else{
				$this->layout->view('staff/vlux/item_editor', $this->vars);
			}
			
		}//Bug
		else{
			show_error("Problême avec l'éditeur d'item : $this->vars", 500);
		}
	}

	public function effacer_item($id){
		if(is_numeric($id) && $id>0){
			$this->item_factory->delete_item($id);
			$this->succes("L'item à bien été supprimé !");
			redirect('staff/gerer_items');
		}
	}
	
	public function monnaie_check(){
		$monnaies = $this->vlux_factory->monnaie_select();
		foreach($monnaies as $monnaie => $monnaie_name){
			$check[] = $monnaie;
		}
		if(!in_array($this->input->post('monnaie'), $check)){
			$this->form_validation->set_message('monnaie_check' ,'Veuillez choisir une monnaie existante !');
			return FALSE;
		}
		else{
			return TRUE;
		}
	}

	public function cat_check(){
		$cat =	array('bâtiments', 'brico', 'vegetation', 'mobilier', 'deco', 'cuisine', 'chambre', 'sdb', 'garage', 'living', 'jardin', 'plage', 'urbain', 'foret','outils', 'portes');
		if($this->input->post('cat')=='id'){
			$this->form_validation->set_message('cat_check', 'Veuillez choisir une catégorie !');
			return FALSE;
		}
		elseif(!in_array($this->input->post('cat'), $cat)){
			$this->form_validation->set_message('cat_check', 'Cette catégorie n\'existe pas !');
			return FALSE;
		}
		else{
			return TRUE;
		}
	}

	public function type_check(){
		$type = array( 'exterieur', 'interieur', 'sols', 'utilitaires', 'special');
		if($this->input->post('type')=='id'){
			$this->form_validation->set_message('type_check' ,'Veuillez choisir un type !');
			return FALSE;
		}
		if(!in_array($this->input->post('type'), $type)){
			$this->form_validation->set_message('type_check' ,'Ce type n\'existe pas !');
			return FALSE;
		}
		else{
			return TRUE;
		}
	}

	public function nature_check(){
		$nature =	array('normale', 'eau', 'lave', 'acide');
		if(!in_array($this->input->post('nature'), $nature)){
			$this->form_validation->set_message('nature_check', 'Cette nature d\'objet n\'existe pas !');
			return FALSE;
		}
		else{
			return TRUE;
		}
	}

	public function auth_level_check(){
		$auth_levels = array(Bouzouk::Rang_Aucun, Bouzouk::Rang_BetaTesteur, Bouzouk::Rang_MaitreJeu, Bouzouk::Rang_Admin);
		if(!in_array($this->input->post('auth_level'), $auth_levels)){
			$this->fomr_validation->set_message('auth_level_check', " Le rang choisi est incorrect.");
			return FALSE;
		}
		else{
			return TRUE;
		}
	}

	public function backup_sql(){
		if(ENVIRONMENT == 'development'){
			$this->load->dbutil();
			$this->load->helper('date');
			$now = now();
			$config= array(
				'tables'	=> array('vlux_items'),
				'format'	=> 'zip',
				'filename'	=> "maj_vlux_items_$now.sql",
				'add_drop'	=> TRUE,
				'add_insert'=> TRUE);
			$file = $this->dbutil->backup($config);
			$this->load->helper('download');
			force_download($config['filename'].'.zip', $file);

		}
		else{
			return show_404();
		}
	}
}
