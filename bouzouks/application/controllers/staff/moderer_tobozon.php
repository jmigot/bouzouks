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
 
class Moderer_tobozon extends MY_Controller
{
	private $select_joueurs;

	public function __construct()
	{
		parent::__construct();
		$this->select_joueurs = $this->bouzouk->select_joueurs(array(
			'status_not_in' => array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_ChoixPerso, Bouzouk::Joueur_Pause, Bouzouk::Joueur_GameOver, Bouzouk::Joueur_Robot, Bouzouk::Joueur_Banni),
			'champ_texte'   => true,
			'rangs_in'      => Bouzouk::Rang_Aucun
		));
	}
	
	public function index($offset = '0')
	{
		$vars = array(
			'select_joueurs' => $this->select_joueurs
		);
		return $this->layout->view('staff/moderer_tobozon', $vars);
	}

	public function voir($joueur_id = null)
	{
		if ( ! isset($joueur_id))
		{
			// Règles de validation
			$this->load->library('form_validation');
			$this->form_validation->set_rules('joueur_id', 'Le joueur', 'is_natural_no_zero');
			$this->form_validation->set_rules('joueur_id_pseudo', 'Le pseudo', 'min_length[3]|max_length[20]');

			if ( ! $this->form_validation->run())
			{
				return $this->index();
			}
		}

		// On va chercher les infos du joueur
		$this->db->select('j.id, j.pseudo, tu.group_id')
				 ->from('tobozon_users tu')
				 ->join('joueurs j', 'j.id = tu.id');

		if ($this->input->post('joueur_id_pseudo') != false)
			$this->db->where('j.pseudo', $this->input->post('joueur_id_pseudo'));

		else
			$this->db->where('j.id', $this->input->post('joueur_id'));
			
		$query = $this->db->get();

		// On vérifie que le compte existe
		if ($query->num_rows() == 0)
		{
			$this->echec("Ce bouzouk n'existe pas");
			return $this->index();
		}

		$joueur = $query->row();

		// On vérifie qu'il a le droit de le modifier
		if ( ! in_array($joueur->group_id, array(Bouzouk::Tobozon_IdGroupeBouzouks, Bouzouk::Tobozon_IdGroupeCensures)))
		{
			$this->echec("Tu n'as pas le droit de modifier ce bouzouk");
			return $this->index();
		}
		
		$vars = array(
			'select_joueurs' => $this->select_joueurs,
			'joueur'         => $joueur
		);
		return $this->layout->view('staff/moderer_tobozon', $vars);
	}

	public function modifier()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur_id', 'Le joueur', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('group_id', 'Le groupe', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('raison', 'La raison', 'max_length[250]');

		if ( ! $this->form_validation->run())
		{
			return $this->voir();
		}

		// Le joueur ne peut mettre que Bouzouk ou Censuré comme groupe
		if ( ! in_array($this->input->post('group_id'), array(Bouzouk::Tobozon_IdGroupeBouzouks, Bouzouk::Tobozon_IdGroupeCensures)))
		{
			$this->echec("Tu n'as pas les droits pour mettre ce bouzouk dans ce groupe.");
			return $this->voir();
		}
		
		// On va chercher les infos du joueur
		$query = $this->db->select('id, username, group_id')
						  ->from('tobozon_users')
						  ->where('id', $this->input->post('joueur_id'))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Ce bouzouk n'existe pas");
			return $this->voir();
		}

		$joueur = $query->row();
			
		// On vérifie qu'il a le droit de le modifier
		if ( ! in_array($joueur->group_id, array(Bouzouk::Tobozon_IdGroupeBouzouks, Bouzouk::Tobozon_IdGroupeCensures)))
		{
			$this->echec("Tu n'as pas le droit de modifier ce bouzouk");
			return $this->voir();
		}

		// On effectue les modifications
		$this->db->set('group_id', $this->input->post('group_id'))
				 ->where('id', $this->input->post('joueur_id'))
				 ->update('tobozon_users');

		// On ajoute à l'historique du joueur
		if ($this->input->post('group_id') == Bouzouk::Tobozon_IdGroupeCensures)
		{
			$this->bouzouk->historique(43, null, array(profil(-1, '', $this->session->userdata('rang')), form_prep($this->input->post('raison'))), $joueur->id, Bouzouk::Historique_Full);

			// On ajoute à l'historique modération
			$this->bouzouk->historique_moderation(profil().' a interdit '.profil($joueur->id, $joueur->username).' de Tobozon pour la raison : <span class="pourpre">'.form_prep($this->input->post('raison')).'</span>');
		}

		else
		{
			$this->bouzouk->historique(44, null, array(profil(-1, '', $this->session->userdata('rang'))), $joueur->id, Bouzouk::Historique_Full);

			// On ajoute à l'historique modération
			$this->bouzouk->historique_moderation(profil()." a levé l'interdiction de Tobozon de ".profil($joueur->id, $joueur->username));
		}

		// On affiche un message de confirmation
		$this->succes(profil($joueur->id, $joueur->username).' a bien été modifié');
		return $this->voir();
	}
}

