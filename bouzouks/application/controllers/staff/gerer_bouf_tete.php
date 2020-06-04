<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Controleur de gestion des items présent dans Vlurx 3D
 * 
 * 
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 * 
 * @author       Hikingyo <hikingyo@outlook.com>
 * @copyright    Team Bouzouk 2015 (C) 2015 Hikingyo - Tous droits réservés
 * @package      bouzouks\vlux\controllers\staff
 * @see          event Bouf'Tête
 * 
 */
class Gerer_bouf_tete extends MY_Controller
{

	public function index(){
		$vars['title'] = "Gestion Event BF";
		$vars['etat_event'] = $this->bouzouk->etat_bouf_tete();
		//On paramétre le formulaire
		$vars['select_joueurs'] = $this->bouzouk->infectables(false);

		//On affiche la page
		$this->layout->view('staff/event_bouf_tete/accueil', $vars);
	}
	/**
	 * Lancement de l'envent bouf'tête
	 * On infecte les deux premier candidats choisis
	 **/
	public function lancer_event(){
		$this->bouzouk->infecter(34);
		$this->bouzouk->infecter(624);
		$this->bouzouk->choisir_candidat_bouf_tete(34, 1);
		$this->bouzouk->choisir_candidat_bouf_tete(624,2);
		$this->succes("Les bestioles jaunes débarquent !!","Event Bouf'Tête");
		redirect('staff/gerer_bouf_tete');
	}

	public function stop_event(){
		//On vide la table event_bouf_tete
		$this->bouzouk->stop_event_bouf_tete();
		$this->succes("Adieu les bestioles jaunes !!");
		redirect('staff/gerer_bouf_tete');
	}

	public function infecter(){
		$this->load->library('form_validation');
		//Régle de validatio ndu formulaire
		$rules = array(
			array(
				'field' => 'infection',
				'label'	=> 'Id du joueur à infecter',
				'rules'	=> 'is_natural|required'
				)
			);
		$this->form_validation->set_rules($rules);
		// Traitement du formulaire
		if($this->form_validation->run() != FALSE){
			// On implante le joueur
			$id_joueur = $this->form_validation->set_value('infection');
			$this->bouzouk->infecter($id_joueur);
			$this->succes("Le joueur a bien été implanté !");
		}
		redirect('staff/gerer_bouf_tete');
	}
}