<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet	  : Bouzouks
 * Description : fonctions de gestion des amis
 *
 * Auteur	  : Fabien Foixet (fabien@foixet.com)
 * Date		: décembre 2013
 *
 * Copyright (C) 2012-2013 Fabien Foixet - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Amis extends MY_Controller
{
	public function index()
	{ 
		// On va chercher les amis du joueur
		$query = $this->db->select('j.id, j.pseudo, (j.connecte < NOW() - INTERVAL 2 MINUTE) AS connect, j.rang, j.faim, j.stress, j.sante, j.perso')
						  ->from('amis a')
						  ->join('joueurs j', 'a.ami_id = j.id')
						  ->where('a.joueur_id', $this->session->userdata('id'))
						  ->where('a.etat', Bouzouk::Amis_Accepte)
						  ->order_by('connect, j.pseudo')
						  ->get();
		$amis = $query->result();
		
		// On va chercher les demandes du joueur en attente
		$query = $this->db->select('j.id, j.pseudo, j.rang, a.etat, a.date')
						  ->from('amis a')
						  ->join('joueurs j', 'a.ami_id = j.id')
						  ->where('a.joueur_id', $this->session->userdata('id'))
						  ->where_in('a.etat', array(Bouzouk::Amis_Attente, Bouzouk::Amis_Refuse))
						  ->order_by('a.date', 'desc')
						  ->get();
		$demandes_faites = $query->result();

		// On va chercher les demandes des autres joueurs en attente
		$query = $this->db->select('j.id, j.pseudo, j.rang, a.date')
						  ->from('amis a')
						  ->join('joueurs j', 'a.joueur_id = j.id')
						  ->where('a.ami_id', $this->session->userdata('id'))
						  ->where('a.etat', Bouzouk::Amis_Attente)
						  ->order_by('a.date', 'desc')
						  ->get();
		$demandes_attente = $query->result();

		// On va chercher les demandes des autres joueurs refusées
		$query = $this->db->select('j.id, j.pseudo, j.rang, a.date')
						  ->from('amis a')
						  ->join('joueurs j', 'a.joueur_id = j.id')
						  ->where('a.ami_id', $this->session->userdata('id'))
						  ->where('a.etat', Bouzouk::Amis_Refuse)
						  ->order_by('a.date', 'desc')
						  ->get();
		$demandes_liste_noire = $query->result();

		// On prépare la liste des amis possibles
		$amis_actuels = array($this->session->userdata('id'));

		foreach ($amis as $ami)
			$amis_actuels[] = $ami->id;

		foreach ($demandes_faites as $ami)
			$amis_actuels[] = $ami->id;

		foreach ($demandes_attente as $ami)
			$amis_actuels[] = $ami->id;

		foreach ($demandes_liste_noire as $ami)
			$amis_actuels[] = $ami->id;

		$select_amis = $this->bouzouk->select_joueurs(array(
			'name'		    => 'ami_id',
			'status_not_in' => array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_ChoixPerso, Bouzouk::Joueur_GameOver, Bouzouk::Joueur_Robot, Bouzouk::Joueur_Banni),
			'non_inclus'    => $amis_actuels
		));

		// On affiche
		$vars = array(
			'amis'                 => $amis,
			'demandes_faites'      => $demandes_faites,
			'demandes_attente'     => $demandes_attente,
			'demandes_liste_noire' => $demandes_liste_noire,
			'select_amis'          => $select_amis,
		);

		return $this->layout->view('amis/index', $vars);
	}

	public function ajouter()
	{
		// Règles de validation
 		$this->load->library('form_validation');
		$this->form_validation->set_rules('ami_id', 'Un bouzouk', 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
			return $this->index();

		// On vérifie que le joueur ne veut s'ajouter lui-même en ami
		if ($this->input->post('ami_id') == $this->session->userdata('id'))
		{
			$this->echec("Tu veux être ami avec toi même ? T'es si seul que ça ?");
			return $this->index();
		}

		// On vérifie que le joueur est valide
		$ami_valide = $this->db->where('id', $this->input->post('ami_id'))
							   ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))
							   ->count_all_results('joueurs');  
		if ( ! $ami_valide)
		{
			$this->echec("Ce bouzouk n'existe pas");
			return $this->index();
		}

		// On regarde si la demande d'ami existe déjà
		$deja_demande = $this->db->where('(ami_id = '.$this->input->post('ami_id').' AND joueur_id = '.$this->session->userdata('id').')')
								 ->or_where('(joueur_id = '.$this->input->post('ami_id').' AND ami_id = '.$this->session->userdata('id').')')
								 ->count_all_results('amis');
	
		if ($deja_demande)
		{
			$this->echec("Une demande est déjà en cours pour ce bouzouk");
			return $this->index();
		}
		
		// On ajoute la demande
		$data_amis = array(
			'joueur_id' => $this->session->userdata('id'),
			'ami_id'	=> $this->input->post('ami_id'),
			'date'      => bdd_datetime(),
			'etat'	    => Bouzouk::Amis_Attente
		);
		$this->db->insert('amis', $data_amis);
			
		// On envoit une notification
		$this->bouzouk->notification(9, array(profil(-1, '', $this->session->userdata('rang')), site_url('amis')), $this->input->post('ami_id'));

		// On affiche une confirmation
		$this->succes("La demande d'amitié a bien été envoyée");
		return $this->index();
	}

	public function accepter()
	{
		// Règles de validation
 		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur_id', 'Un bouzouk', 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
			return $this->index();

		// On vérifie que la demande existe
		$existe = $this->db->where('joueur_id', $this->input->post('joueur_id'))
						   ->where('ami_id', $this->session->userdata('id'))
						   ->where('etat', Bouzouk::Amis_Attente)
						   ->count_all_results('amis');

		if ( ! $existe)
		{
			$this->echec("Aucune demande d'amitié n'existe pour ce bouzouk");
			return $this->index();
		}

		// On valide l'amitié entre les deux joueurs (ici on ajoute deux lignes par amitié)
		$this->db->set('etat', Bouzouk::Amis_Accepte)
				 ->set('date', bdd_datetime())
				 ->where('joueur_id', $this->input->post('joueur_id'))
				 ->where('ami_id', $this->session->userdata('id'))
				 ->update('amis');

		$data_amis = array(
			'joueur_id' => $this->session->userdata('id'),
			'ami_id'    => $this->input->post('joueur_id'),
			'date'      => bdd_datetime(),
			'etat'      => Bouzouk::Amis_Accepte
		);
		$this->db->insert('amis', $data_amis);

		// On récupère les infos de l'ami
		$query = $this->db->select('id, pseudo, sexe, rang')
						  ->from('joueurs')
						  ->where('id', $this->input->post('joueur_id'))
						  ->get();
		$ami = $query->row();

		// On ajoute à l'historique
		$this->bouzouk->historique(1, 2, array(profil($ami->id, $ami->pseudo, $ami->rang)));
		$this->bouzouk->historique(3, 2, array(profil(-1, '', $this->session->userdata('rang'))), $this->input->post('joueur_id'), Bouzouk::Historique_Full);

		// On affiche une confirmation
		$this->succes('La demande a bien été acceptée');
		return $this->index();
	}
	
	public function refuser()
	{
		// Règles de validation
 		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur_id', 'Un bouzouk', 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
			return $this->index();

		// On vérifie que la demande existe
		$existe = $this->db->where('joueur_id', $this->input->post('joueur_id'))
						   ->where('ami_id', $this->session->userdata('id'))
						   ->where('etat', Bouzouk::Amis_Attente)
						   ->count_all_results('amis');

		if ( ! $existe)
		{
			$this->echec("Aucune demande d'amitié n'existe pour ce bouzouk");
			return $this->index();
		}
		
		// On refuse la demande
		$this->db->set('etat', Bouzouk::Amis_Refuse)
				 ->set('date', bdd_datetime())
				 ->where('joueur_id', $this->input->post('joueur_id'))
				 ->where('ami_id', $this->session->userdata('id'))
				 ->update('amis');

		// On récupère les infos de l'ami
		$query = $this->db->select('id, pseudo, sexe, rang')
						  ->from('joueurs')
						  ->where('id', $this->input->post('joueur_id'))
						  ->get();
		$ami = $query->row();

		// On ajoute à l'historique
		$this->bouzouk->historique(4, null, array(profil($ami->id, $ami->pseudo, $ami->rang)));
		$this->bouzouk->historique(5, null, array(profil(-1, '', $this->session->userdata('rang'))), $this->input->post('joueur_id'), Bouzouk::Historique_Full);

		// On affiche une confirmation
		$this->succes('La demande a bien été refusée, le bouzouk a été prévenu et ne pourra plus refaire de demande tant que tu le laisseras en liste noire');
		return $this->index();
	}

	public function supprimer()
	{
		// Règles de validation
 		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur_id', 'Un bouzouk', 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
			return $this->index();

		// On vérifie que c'est bien son ami
		$existe = $this->db->where('joueur_id', $this->input->post('joueur_id'))
						   ->where('ami_id', $this->session->userdata('id'))
						   ->where('etat', Bouzouk::Amis_Accepte)
						   ->count_all_results('amis');

		if ( ! $existe)
		{
			$this->echec("Tu n'es pas ami avec ce bouzouk");
			return $this->index();
		}

		// On supprime l'amitié
		$this->db->where('ami_id', $this->input->post('joueur_id'))
				 ->where('joueur_id', $this->session->userdata('id'))
				 ->delete('amis');

		$this->db->where('ami_id', $this->session->userdata('id'))
				 ->where('joueur_id', $this->input->post('joueur_id'))
				 ->delete('amis');

		// On recupère les infos de l'ami
		$query = $this->db->select('id, pseudo, sexe, rang')
						  ->from('joueurs')
						  ->where('id', $this->input->post('joueur_id'))
						  ->get();
		$ami = $query->row();

		// On ajoute à l'historique
		$this->bouzouk->historique(6, 7, array(profil($ami->id, $ami->pseudo, $ami->rang)));
		$this->bouzouk->historique(8, 7, array(profil(-1, '', $this->session->userdata('rang'))), $this->input->post('joueur_id'), Bouzouk::Historique_Full);

		// On affiche une confirmation
		$this->succes(profil($ami->id, $ami->pseudo, $ami->rang)." n'est plus ton ami");
		return $this->index();
	}

	public function supprimer_liste_noire()
	{
		// Règles de validation
 		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur_id', 'Un bouzouk', 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
			return $this->index();

		// On vérifie que le joueur est en liste noire
		$existe = $this->db->where('joueur_id', $this->input->post('joueur_id'))
						   ->where('ami_id', $this->session->userdata('id'))
						   ->where('etat', Bouzouk::Amis_Refuse)
						   ->count_all_results('amis');

		if ( ! $existe)
		{
			$this->echec("Ce bouzouk n'est pas dans ta liste noire");
			return $this->index();
		}

		// On supprime de la liste noire
		$this->db->where('joueur_id', $this->input->post('joueur_id'))
				 ->where('ami_id', $this->session->userdata('id'))
				 ->delete('amis');

		// On affiche une confirmation
		$this->succes("Tu as bien supprimé ce bouzouk de ta liste noire");
		return $this->index();
	}
}