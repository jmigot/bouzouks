<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : modération/administration du site
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : janvier 2014
 *
 * Copyright (C) 2012-2014 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */
 
class Moderer_parrainages extends MY_Controller
{
	private $statuts;

	public function __construct()
	{
		parent::__construct();

		$this->statuts = array(
			Bouzouk::Joueur_Inactif    => 'Inactif',
			Bouzouk::Joueur_Etudiant   => 'Etudiant',
			Bouzouk::Joueur_ChoixPerso => 'Choix perso',
			Bouzouk::Joueur_Actif      => 'Actif',
			Bouzouk::Joueur_Asile      => 'Asile',
			Bouzouk::Joueur_Pause      => 'Pause',
			Bouzouk::Joueur_GameOver   => 'Game over',
			Bouzouk::Joueur_Banni      => 'Banni',
			Bouzouk::Joueur_Robot      => 'Robot',
		);
	}

	public function index()
	{
		// On va chercher toutes les demandes de parrainage en attente
		$query = $this->db->select('p.id, p.date, j1.id AS parrain_id, j1.pseudo AS parrain_pseudo, j1.rang AS parrain_rang, j1.statut AS parrain_statut, j2.id AS filleul_id, j2.pseudo AS filleul_pseudo, j2.statut AS filleul_statut')
						  ->from('parrainages p')
						  ->join('joueurs j1', 'j1.id = p.parrain_id')
						  ->join('joueurs j2', 'j2.id = p.filleul_id')
						  ->order_by('p.date')
						  ->get();
		$parrainages = $query->result();

		// On va chercher les dernières demandes envoyées par email
		$query = $this->db->select('j1.id AS joueur_id, j1.pseudo AS joueur_pseudo, j1.rang AS joueur_rang, j2.id AS filleul_id, j2.pseudo AS filleul_pseudo, pd.email, pd.date')
						  ->from('parrainages_demandes pd')
						  ->join('joueurs j1', 'j1.id = pd.joueur_id')
						  ->join('joueurs j2', 'j2.email = pd.email', 'left')
						  ->order_by('pd.date', 'desc')
						  ->limit(150)
						  ->get();
		$demandes_email = $query->result();

		// On affiche
		$vars = array(
			'parrainages'    => $parrainages,
			'demandes_email' => $demandes_email,
			'statuts'        => $this->statuts
		);
		return $this->layout->view('staff/moderer_parrainages', $vars);
	}

	public function modifier()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('parrainage_id', 'Le parrainage', 'required|is_natural_no_zero');
		
		if ( ! $this->form_validation->run())
			return $this->index();
		
		// On va chercher les infos du parrainage
		$query = $this->db->select('id, parrain_id, filleul_id')
						  ->from('parrainages')
						  ->where('id', $this->input->post('parrainage_id'))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Ce parrainage n'existe pas");
			return $this->index();
		}

		$parrainage = $query->row();

		// Validation
		if ($this->input->post('valider') !== false)
		{
			// On va chercher l'objet
			$query = $this->db->select('id, nom')
							  ->from('objets')
							  ->where('id', 44)
							  ->get();
			$objet = $query->row();

			// On donne un objet rare au parrain
			$this->bouzouk->ajouter_objets($objet->id, 1, 25, $parrainage->parrain_id);
			$this->bouzouk->historique(228, 229, array(profil($parrainage->filleul_id), '1 '.form_prep($objet->nom), profil($parrainage->parrain_id)), $parrainage->parrain_id, Bouzouk::Historique_Full);

			// On donne un avantage au filleul
			$struls = $this->bouzouk->config('joueur_recompense_filleul');
			$this->bouzouk->ajouter_struls($struls, $parrainage->filleul_id);
			$this->bouzouk->historique(230, 231, array(profil($parrainage->parrain_id), "+$struls struls", profil($parrainage->filleul_id)), $parrainage->filleul_id, Bouzouk::Historique_Full);

			// On valide le parrainage pour les stats
			$this->db->set('filleul_valide', 1)
					 ->where('id', $parrainage->filleul_id)
					 ->update('joueurs');

			// On ajoute à l'historique modération
			$this->bouzouk->historique_moderation(profil().' a validé le parrainage de '.profil($parrainage->filleul_id).' par '.profil($parrainage->parrain_id));
			$this->succes("Tu as bien validé ce parrainage, les récompenses ont été distribuées");
		}

		// Suppression
		else if ($this->input->post('refuser') !== false)
		{
			// On ajoute à l'historique modération
			$this->bouzouk->historique_moderation(profil().' a refusé le parrainage de '.profil($parrainage->filleul_id).' par '.profil($parrainage->parrain_id));
			$this->succes("Tu as bien refusé ce parrainage");
		}

		// On supprime des parrainages
		$this->db->where('id', $this->input->post('parrainage_id'))
				 ->delete('parrainages');

		// On affiche
		return $this->index();
	}
}

