<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Projet      : Bouzouks
 * Description : Gestion de configuration des campagnes en lien avec FaceBook
 *
 * Auteur      : Hikingyo
 * Date        : Octobre 2015
 *
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Gerer_campagne_fb extends MY_Controller
{
	public function __construct(){
		parent::__construct();
		$this->load->model('fb_pixel');
	}

	public function index(){
		$vars['title'] = "Gestion Campagne FB";
		// On récupère l'état des pixels du site
		$pixels = $this->fb_pixel->get_all();
		$vars['pixels'] = $pixels;
		return $this->layout->view('staff/facebook/gerer_campagne', $vars);
	}

	public function modif_pixel($pixel_id){

		$vars['title'] = "Modifer un pixel FB";
		// On récupère le pixel
		$pixel = $this->fb_pixel->get($pixel_id);
		// Si aucun pixel ne correspond à la demande
		if(!$pixel){
			$this->echec("Le pixel demandé n'existe pas.");
			return $this->index();
		}
		$vars['pixel'] = $pixel;
		$this->load->library('form_validation');
		// Définition des régles de validation
		$rules = array(
			array(
				'field'	=> 'nom',
				'label'	=> 'nom du pixel',
				'rules'	=> 'required|max_length[32]'
				),
			array(
				'field'	=> 'id_fb',
				'label'	=> 'ID FaceBook du pixel',
				'rules'	=> 'required|is_numeric'
				),
			array(
				'field'	=> 'etat',
				'label'	=> 'etat de la campagne',
				'rules'	=> 'required|callback_etat_pixel_check'
				)
			);
		$this->form_validation->set_rules($rules);

		if(!$this->form_validation->run()){
			return $this->layout->view('staff/facebook/modif_pixel', $vars);
		}
		$pixel->nom = $this->input->post('nom');
		$pixel->id_fb = $this->input->post('id_fb');
		$pixel->etat = (int)$this->input->post('etat');
		$this->fb_pixel->update($pixel);
		$this->succes("La campagne a bien été modifiée !");
		if($pixel->etat == 1){
			return $this->test_pixel($pixel_id);
		}
		else{
			return $this->index();
		}
		
	}

	public function etat_pixel_check(){
		if($this->input->post('etat')!= 0 && $this->input->post('etat')!= 1){
			$this->form_validation->set_message('etat_pixel_check', "L'état choisi pour la campagne est invalide");
			return FALSE;
		}
		else{
			return TRUE;
		}
	}

	public function test_pixel($pixel_id){
		$this->load->model('fb_pixel');
		$pixel = $this->fb_pixel->get($pixel_id);
		return $this->layout->view('staff/facebook/test_pixel', array('title'=>'Test pixel FB', 'pixel'=>$pixel));
	}
}