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
 
class Multicomptes extends MY_Controller
{
	public function inscriptions()
	{
		// On va chercher tous les joueurs qui se sont inscrits avec la même ip
		$query = $this->db->select('j1.id, j1.pseudo, j1.ip_inscription AS ip, j1.date_inscription, j1.mot_de_passe, j1.email')
						  ->from('joueurs j1')
						  ->where_in('j1.statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))
						  ->where('j1.exception_multi', 0)
						  ->where('j1.ip_inscription IN (SELECT j2.ip_inscription FROM joueurs j2 WHERE j2.id != j1.id AND j2.exception_multi = 0 AND j2.statut IN ('.Bouzouk::Joueur_Actif.','.Bouzouk::Joueur_Asile.','.Bouzouk::Joueur_Pause.'))')
						  ->order_by('j1.ip_inscription')
						  ->order_by('j1.pseudo')
						  ->get();
		$joueurs = $query->result();

		// On affiche les résultats
		$vars = array(
			'joueurs' => $joueurs
		);
		return $this->layout->view('staff/multicomptes/inscriptions', $vars);
	}
	
	public function connexions()
	{
		// On va chercher tous les joueurs qui se sont connectés avec la même ip
		$query = $this->db->select('DISTINCT(j1.id), j1.pseudo, c1.ip, j1.mot_de_passe')
						  ->from('connexions c1')
						  ->join('joueurs j1', 'j1.id = c1.joueur_id')
						  ->where_in('j1.statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))
						  ->where('j1.exception_multi', 0)
						  ->where('c1.ip IN (SELECT c2.ip FROM connexions c2 JOIN joueurs j2 ON j2.id = c2.joueur_id WHERE j1.id != j2.id AND j2.exception_multi = 0 AND j2.statut IN ('.Bouzouk::Joueur_Actif.','.Bouzouk::Joueur_Asile.','.Bouzouk::Joueur_Pause.'))')
						  ->order_by('c1.ip')
						  ->order_by('j1.pseudo')
						  ->get();
		$joueurs = $query->result();

		// On affiche les résultats
		$vars = array(
			'joueurs' => $joueurs
		);
		return $this->layout->view('staff/multicomptes/connexions', $vars);
	}

	public function mots_de_passe()
	{
		// On va chercher tous les joueurs qui ont le même mot de passe
		$query = $this->db->select('j1.id, j1.pseudo, j1.mot_de_passe')
						  ->from('joueurs j1')
						  ->where_in('j1.statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))
						  ->where('j1.exception_multi', 0)
						  ->where('j1.mot_de_passe IN (SELECT j2.mot_de_passe FROM joueurs j2 WHERE j2.id != j1.id AND j2.exception_multi = 0 AND j2.statut IN ('.Bouzouk::Joueur_Actif.','.Bouzouk::Joueur_Asile.','.Bouzouk::Joueur_Pause.'))')
						  ->order_by('j1.mot_de_passe')
						  ->order_by('j1.pseudo')
						  ->get();
		$joueurs = $query->result();

		// On affiche les résultats
		$vars = array(
			'joueurs' => $joueurs
		);
		return $this->layout->view('staff/multicomptes/mots_de_passe', $vars);
	}

	public function pioupiouk_chercheur()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('ip_inscription', 'ip_inscription', '');
		$this->form_validation->set_rules('mot_de_passe', 'mot_de_passe', '');
		$this->form_validation->set_rules('date_de_naissance', 'date_de_naissance', '');
		$this->form_validation->set_rules('exceptions', 'exceptions', '');
		$this->form_validation->run();

		// Si un filtre au moins a été posté
		if ($this->input->post() !== false)
		{
			// On filtre
			$query = 'SELECT j1.id, j1.pseudo, j1.date_de_naissance, j1.mot_de_passe, j1.ip_inscription '.
					 'FROM joueurs j1 '.
					 'WHERE j1.statut IN ('.implode(',', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause)).') ';

			if ($this->input->post('exceptions') === false)
				$query .= ' AND j1.exception_multi = 0 ';

			$where = '';
			$order_by = array();

			// Filtres WHERE et ORDER BY
			if ($this->input->post('exceptions') === false)
				$where .= ' AND j2.exception_multi = 0';

			if ($this->input->post('ip_inscription') !== false)
			{
				$where .=  ' AND j2.ip_inscription = j1.ip_inscription';
				$order_by[] = 'j1.ip_inscription';
			}

			if ($this->input->post('mot_de_passe') !== false)
			{
				$where .=  ' AND j2.mot_de_passe = j1.mot_de_passe';
				$order_by[] = 'j1.mot_de_passe';
			}

			if ($this->input->post('date_de_naissance') !== false)
			{
				$where .=  ' AND j2.date_de_naissance = j1.date_de_naissance';
				$order_by[] = 'j1.date_de_naissance';
			}
			
			$order_by[] = 'j1.pseudo';

			// On construit le reste de la requête avec WERE et ORDER BY			
			$query .= ' AND EXISTS (SELECT j2.id FROM joueurs j2 WHERE j2.id != j1.id AND j2.statut IN ('.implode(',', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause)).')'.$where.')';
			$query .= 'ORDER BY '.implode(',', $order_by);

			$joueurs = $this->db->query($query)->result();	
		}

		// On affiche les résultats
		$vars = array(
			'joueurs' => isset($joueurs) ? $joueurs : array()
		);
		return $this->layout->view('staff/multicomptes/pioupiouk_chercheur', $vars);
	}

	public function dates_de_naissance()
	{
		// On va chercher tous les joueurs qui ont la même date de naissance
		$query = $this->db->select('j1.id, j1.pseudo, j1.date_de_naissance')
						  ->from('joueurs j1')
						  ->where_in('j1.statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))
						  ->where('j1.exception_multi', 0)
						  ->where_not_in('statut', array(Bouzouk::Joueur_Robot, Bouzouk::Joueur_Banni))
						  ->where('j1.date_de_naissance IN (SELECT j2.date_de_naissance FROM joueurs j2 WHERE j2.id != j1.id AND j2.exception_multi = 0 AND j2.statut IN ('.Bouzouk::Joueur_Actif.','.Bouzouk::Joueur_Asile.','.Bouzouk::Joueur_Pause.'))')
						  ->order_by('j1.date_de_naissance')
						  ->get();
		$joueurs = $query->result();

		// On affiche les résultats
		$vars = array(
			'joueurs' => $joueurs
		);
		return $this->layout->view('staff/multicomptes/dates_de_naissance', $vars);
	}

	public function marche_noir()
	{
		// On va chercher les transactions basses du marché noir
		$query = $this->db->select('mn.vendeur_id, j1.pseudo AS vendeur_pseudo, mn.acheteur_id, j2.pseudo AS acheteur_pseudo, o.nom, mn.quantite, mn.prix, mn.peremption, mn.date')
						  ->from('mc_marche_noir mn')
						  ->join('joueurs j1', 'j1.id = mn.vendeur_id')
						  ->join('joueurs j2', 'j2.id = mn.acheteur_id')
						  ->join('objets o', 'o.id = mn.objet_id')
						  ->order_by('date DESC')
						  ->get();
		$ventes = $query->result();
		
		// On affiche les résultats
		$vars = array(
			'ventes' => $ventes
		);
		return $this->layout->view('staff/multicomptes/marche_noir', $vars);
	}

	public function votes()
	{
		// On récupère le tour actuel
		$query = $this->db->select('tour_election')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();
		
		// On va chercher les votes par date décroissante
		$query = $this->db->select('j1.id AS joueur_id, j1.pseudo AS joueur_pseudo, j2.id AS candidat_id, j2.pseudo AS candidat_pseudo, ev.date')
						  ->from('elections_votes ev')
						  ->join('joueurs j1', 'j1.id = ev.joueur_id')
						  ->join('joueurs j2', 'j2.id = ev.candidat_id')
						  ->where('ev.tour', $mairie->tour_election)
						  ->order_by('ev.date', 'desc')
						  ->get();
		$votes = $query->result();
		
		// On affiche les résultats
		$vars = array(
			'votes' => $votes
		);
		return $this->layout->view('staff/multicomptes/votes_elections', $vars);
	}

	public function payes_employes()
	{
		// On va chercher les joueurs sous-payés
		$query = $this->db->select('mce.date, j1.id AS patron_id, j1.pseudo AS patron_pseudo, j2.id AS employe_id, j2.pseudo AS employe_pseudo, mce.salaire, mce.salaire_recommande, jo.nom AS job')
						  ->from('mc_employes mce')
						  ->join('joueurs j1', 'j1.id = mce.patron_id')
						  ->join('joueurs j2', 'j2.id = mce.employe_id')
						  ->join('jobs jo', 'jo.id = mce.job_id')
						  ->order_by('mce.date', 'desc')
						  ->order_by('j1.pseudo')
						  ->get();
		$joueurs = $query->result();

		// On affiche
		$vars = array(
			'joueurs' => $joueurs
		);
		return $this->layout->view('staff/multicomptes/payes_employes', $vars);
	}

	public function plouk_parties()
	{
		// On va chercher les parties
		$query = $this->db->select('mp.*, j1.pseudo AS createur_pseudo, j2.pseudo AS adversaire_pseudo, o.nom AS objet')
						  ->from('mc_plouk mp')
						  ->join('joueurs j1', 'j1.id = mp.createur_id')
						  ->join('joueurs j2', 'j2.id = mp.adversaire_id')
						  ->join('objets o', 'o.id = mp.objet_id', 'left')
						  ->order_by('mp.date_debut desc')
						  ->get();
		$parties = $query->result();

		$vars = array(
			'parties' => $parties
		);
		return $this->layout->view('staff/multicomptes/plouk_parties', $vars);
	}

	public function exceptions_bans()
	{
		if ($this->input->post('joueurs_ids') === false || count($this->input->post('joueurs_ids')) == 0)
		{
			$this->echec('Il faut sélectionner au moins un joueur');
			redirect($this->input->post('redirect'));
		}

		if ($this->input->post('exception') != false)
		{
			$this->db->set('exception_multi', 1)
					 ->where_in('id', $this->input->post('joueurs_ids'))
					 ->update('joueurs');

			$this->succes('Les joueurs sélectionés ont bien été placés dans la liste des exceptions');
		}

		else if ($this->input->post('bannir') != false)
		{
			$this->load->library('lib_joueur');

			foreach ($this->input->post('joueurs_ids') as $joueur_id)
				$this->lib_joueur->bannir($joueur_id, 'Multicompte', $this->session->userdata('id'));

			$this->succes('Les joueurs sélectionés ont bien été bannis pour multicompte');
		}

		redirect($this->input->post('redirect'));
	}
}