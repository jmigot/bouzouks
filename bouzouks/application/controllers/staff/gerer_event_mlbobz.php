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
 * @see          event MLBooBz
 * 
 */
class Gerer_event_mlbobz extends MY_Controller
{

	public function index(){
		$vars['title'] = "Gestion Event MLBooobz";
		$vars['etat_event'] = $this->bouzouk->etat_event_mlbobz();
		//On paramétre le formulaire
		$vars['select_joueurs'] = $this->bouzouk->boobzable(false);

		//On affiche la page
		$this->layout->view('staff/event_mlbobz/accueil', $vars);
	}
	/**
	 * Lancement de l'envent mlboobz
	 **/
	public function lancer_event(){
		// On vérifie qu'aucun event est en court.
		if($this->bouzouk->check_event()){
			$this->echec('Un event est déjà en cours');
			redirect('staff/accueil');
		}
		// Sinon, on demande les joueurs désignés.
		else{
			$this->choix_candidats();
		}
	}


	public function choix_candidats(){
		$vars['title'] = "Choix des candidats";
		$vars['select_joueurs_candidat'] = $this->bouzouk->select_joueurs(array('name'=>'candidat'));
		$vars['select_joueurs_suppleant'] = $this->bouzouk->select_joueurs(array('name'=>'suppleant'));
		$this->layout->view('staff/event_mlbobz/choix_candidats', $vars);
	}

	public function validation_candidats(){
		// Validation du choix des candidats
		$this->load->library('form_validation');
		$this->form_validation->set_rules(array(
			array(
				'field'=> 'candidat',
				'label'=> 'pseudo du candidat',
				'rules'=> 'required|callback_candidat_check'
				),
			array(
				'field'=> 'suppleant',
				'label'=> 'pseudo du suppléant',
				'rules'=> 'required|callback_suppleant_check'
				)
			));
		if(!$this->form_validation->run()){
			$this->choix_candidats();
		}
		else{
			// On enregistre les candidats sélectionner
			$this->bouzouk->update_candidat_mlbobz($this->input->post('candidat'), 'candidat');
			$this->bouzouk->update_candidat_mlbobz($this->input->post('suppleant'), 'suppleant');
			// Et on les boobze
			$this->bouzouk->maudire_mlbobz($this->input->post('candidat'), false);
			$this->bouzouk->maudire_mlbobz($this->input->post('suppleant'), false);
			// On remet à zéro le token de notification
			$this->bouzouk->set_notif_event_mlbobz('0');
			// Si l'event n'est pas en cours, on le lance
			if(!$this->bouzouk->etat_event_mlbobz()){
				$this->bouzouk->set_etat_mlbobz('1');
				$this->succes('Le RP Zoukette a commencé !');
			}
			else{
				$this->succes('Les candidats ont bien été mis à jour.');
			}
			$this->index();
		}
		
	}

	public function stop_event(){
		//On vide la table event_mlbobz
		$list_maudits = $this->db->select('id_joueur')->get('event_mlbobz');
		$list_maudits = $list_maudits->result();
		var_dump($list_maudits);
		foreach ($list_maudits as $maudit) {
			$this->bouzouk->augmente_version_session($maudit->id_joueur);
		}
		$this->db->truncate('event_mlbobz');
		$this->bouzouk->set_etat_mlbobz('0');
		$this->succes("Fin de l'event MLBoobz");
		redirect('staff/gerer_event_mlbobz');
	}

	public function malediction_mlbobz(){
		$this->load->library('form_validation');
		//Régle de validation du formulaire
		$rules = array(
			array(
				'field' => 'malediction_mlbobz',
				'label'	=> 'Id du joueur à maudir',
				'rules'	=> 'is_natural|required|callback_malediction_bobz_check'
				)
			);
		$this->form_validation->set_rules($rules);
		// Traitement du formulaire
		if($this->form_validation->run()){
			// On transforme le joueur
			$id_joueur = $this->form_validation->set_value('malediction_mlbobz');
			$this->bouzouk->maudire_mlbobz($id_joueur);
			$this->succes("Le joueur a bien été boobzer !");
		}
		redirect('staff/gerer_event_mlbobz');
	}

	public function malediction_bobz_check(){
		if($this->bouzouk->est_maudit_mlbobz($this->input->post('malediction_mlbobz'))){
			$this->form_validation->set_message('malediction_bobz_check', "Le joueurs choisi est invalide.");
			return FALSE;
		}
		else{
			return TRUE;
		}
	}
}