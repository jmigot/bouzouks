<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : modération/administration du site
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : février 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */
 
class Connexion_bouzouk extends MY_Controller
{
	public function index()
	{	
		$vars = array(
			'select_joueurs' => $this->bouzouk->select_joueurs(array('status_not_in' => array(Bouzouk::Joueur_Robot, Bouzouk::Joueur_Banni), 'champ_texte' => true))
		);
		
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur_id', 'Le joueur', 'is_natural_no_zero');
		$this->form_validation->set_rules('joueur_id_pseudo', 'Le pseudo', 'min_length[3]|max_length[20]');

		if ( ! $this->form_validation->run())
		{
			return $this->layout->view('staff/connexion_bouzouk', $vars);
		}

		// On va chercher les infos du joueur
		// Les champs sélectionnés ici seront ceux qui iront dans la session automatiquement
		$this->db->select('id, pseudo, mot_de_passe, email, date_de_naissance, commentaire, date_inscription, statut, date_statut, rang, sexe, perso, struls, faim, sante, stress,
						   notes_controuilles, adresse, interdit_missives, interdit_tchat, tobozon_ids')
				 ->from('joueurs');

		if ($this->input->post('joueur_id_pseudo') != false)
			$this->db->where('pseudo', $this->input->post('joueur_id_pseudo'));

		else
			$this->db->where('id', $this->input->post('joueur_id'));

		$query = $this->db->where_not_in('statut', array(Bouzouk::Joueur_Inactif, Bouzouk::Joueur_Banni, Bouzouk::Joueur_Robot))
						  ->get();

		// On vérifie que le compte existe
		if ($query->num_rows() == 0)
		{
			$this->echec("Ce joueur n'existe pas");
			return $this->layout->view('staff/connexion_bouzouk', $vars);
		}

		$session = $query->row();

		// Seuls les admins peuvent se connecter sur le compte des autres admins
		if ( ! $this->session->userdata('admin_connecte'))
		{
			if (($session->rang & Bouzouk::Rang_Admin) > 0 && ! $this->bouzouk->is_admin(Bouzouk::Rang_Admin))
			{
				$this->echec("Tu n'as pas les droits suffisants pour te connecter sur le compte d'un administrateur");
				return $this->layout->view('staff/connexion_bouzouk', $vars);
			}
		}

		else
		{
			// On va chercher le rang de l'ancien id
			$query = $this->db->select('rang')
							  ->from('joueurs')
							  ->where('id', $this->session->userdata('admin_connecte_ancien_id'))
							  ->get();
			$joueur = $query->row();
			
			if (($session->rang & Bouzouk::Rang_Admin) > 0 && ! $this->bouzouk->is_admin(Bouzouk::Rang_Admin, $joueur->rang))
			{
				$this->echec("Tu n'as pas les droits suffisants pour te connecter sur le compte d'un administrateur");
				return $this->layout->view('staff/connexion_bouzouk', $vars);
			}
		}

		// On remplit une session pour le joueur avec les données récupérées de la requête
		$websocket_auth = md5($session->id.':'.$session->mot_de_passe);		
		unset($session->mot_de_passe);
		$session->connecte = true;
		$session->version_session = -1;
		$session->bonneteau_gagnees = 0;
		$session->filtres_historique = array();
		$session->plouk_id = false;
		$session->admin_connecte = true;

		// Pour revenir au compte de départ
		if ( ! $this->session->userdata('admin_connecte'))
		{
			$session->admin_connecte_ancien_id = $this->session->userdata('id');
			$session->admin_connecte_ancien_pseudo = $this->session->userdata('pseudo');
		}

		else if ($this->input->post('joueur_id') == $this->session->userdata('admin_connecte_ancien_id'))
		{
			$session->admin_connecte = false;
			$session->admin_connecte_ancien_id = null;
			$session->admin_connecte_ancien_pseudo = null;
		}

		$this->session->set_userdata($session);

		// On enregistre l'id dans le cookie pour faire des vérifications (cas de changement de fichiers de session : bug Stagaga)
		$this->input->set_cookie('joueur_id', $this->input->post('joueur_id'), 0);
		
		// On enregistre de quoi s'authentifier sur la websocket
		$this->input->set_cookie('websocket_auth', $websocket_auth, 0);

		// On connecte au tobozon
		if ($this->input->post('connexion_tobozon') !== false)
		{
			$this->load->library('lib_tobozon');
			$this->lib_tobozon->connecter();
		}

		// On enlève les mutex sur le site
		$this->load->library('lib_gazette');
		$this->lib_gazette->liberer_mutex_joueur($this->session->userdata('id'));
		
		// On redirige le joueur vers l'accueil des joueurs
		redirect('joueur/accueil');
	}

	public function revenir_compte()
	{
		$_POST['joueur_id'] = $this->session->userdata('admin_connecte_ancien_id');
		$_POST['joueur_id_pseudo'] = $this->session->userdata('admin_connecte_ancien_pseudo');
		$_POST['connexion_tobozon'] = true;
		return $this->index();
	}
}
