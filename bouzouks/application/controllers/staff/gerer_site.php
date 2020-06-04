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
 
class Gerer_site extends MY_Controller
{
	public function index()
	{
		return $this->layout->view('staff/gerer_site');
	}
	
	public function mettre_a_jour_elections()
	{
		// On met à jour
		$this->load->library('lib_maintenance');
		$this->lib_maintenance->mettre_a_jour_topics_elections();

		// On affiche un message de confirmation
		$this->succes('Les topics des élections ont bien été mis à jour');
		return $this->index();
	}

	public function trouver_erreurs()
	{
		// On cherche les erreurs
		$this->load->library('lib_maintenance');
		$verifications = $this->lib_maintenance->tests_site();
		
		// On affiche les résultats
		$vars = array(
			'verifications' => $verifications
		);
		return $this->layout->view('staff/gerer_site', $vars);
	}

	public function clean_apc_cache()
	{
		// On nettoie le cache système et opcode
		apc_clear_cache();
		apc_clear_cache('opcode');

		// On affiche un message de confirmation
		$this->succes('Le cache APC PHP a bien été nettoyé');
		return $this->index();
	}

	public function clean_apc_user_cache()
	{
		// On nettoie le cache utilisateurs
		apc_clear_cache('user');
		
		// On affiche un message de confirmation
		$this->succes('Le cache APC utilisateur a bien été nettoyé');
		return $this->index();
	}

	public function deconnecter_joueurs()
	{
		// On ajoute à l'historique modération
		$this->bouzouk->historique_moderation(profil().' a déconnecté tous les joueurs');
		
		// On déconnecte tout le monde
		$this->load->library('lib_maintenance');
		$this->lib_maintenance->deconnecter_joueurs();

		// On envoit une notification aux joueurs
		$this->lib_staff->envoyer_notification_joueurs(profil(-1, '', $this->session->userdata('rang')).' vient de déconnecter tous les joueurs pour des raisons de maintenance');
		
		// On affiche un message de confirmation
		$this->succes('Les joueurs ont bien été déconnectés...ta session est également déconnectée :)');
		return $this->index();
	}
	
	public function mettre_maintenance()
	{
		// On met en maintenance
		$this->load->library('lib_maintenance');
		$this->lib_maintenance->activer_maintenance();

		// On envoit une notification aux joueurs
		$this->lib_staff->envoyer_notification_joueurs(profil(-1, '', $this->session->userdata('rang')).' vient de mettre le site en maintenance');
		return $this->index();
	}

	public function envoyer_notification()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('texte', 'Le texte', 'required|max_length[500]');

		if ( ! $this->form_validation->run())
		{
			return $this->voir($this->input->post('joueur_id'));
		}

		$this->lib_staff->envoyer_notification_joueurs($this->input->post('texte'));
		
		// On affiche un message de confirmation
		$this->succes("La notification a bien été envoyée à tous les joueurs");
		return $this->index();
	}
}
