<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : historique des actions du joueurs des x derniers jours
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : novembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Historique extends MY_Controller
{
	private $filtres_valides;

	public function __construct()
	{
		parent::__construct();

		$this->filtres_valides = array(
			Bouzouk::Historique_Bonneteau   => array('bonneteau', 'Bonneteau'),
			Bouzouk::Historique_Lohtoh      => array('lohtoh', 'Lohtoh'),
			Bouzouk::Historique_Plouk       => array('plouk', 'Plouk'),
			Bouzouk::Historique_Elections   => array('elections', 'Elections'),
			Bouzouk::Historique_Boulot      => array('boulot', 'Boulot'),
			Bouzouk::Historique_Compte      => array('compte', 'Compte'),
			Bouzouk::Historique_Objets      => array('objets', 'Objets'),
			Bouzouk::Historique_Dons        => array('dons', 'Dons'),
			Bouzouk::Historique_Factures    => array('factures', 'Factures'),
			Bouzouk::Historique_Annonces    => array('annonces', 'Petites annonces'),
			Bouzouk::Historique_Maintenance => array('maintenance', 'Maintenance'),
			Bouzouk::Historique_Divers      => array('divers', 'Divers'),
			Bouzouk::Historique_Clans       => array('clans', 'Clans')
		);
	}

	public function index($offset = '0')
	{
		// Pagination
		$this->db->where('h.joueur_id', $this->session->userdata('id'));
		
		if (count($this->session->userdata('filtres_historique')) > 0)
		{
			$this->db->join('historique_textes ht', 'h.texte_id_private = ht.id')
					 ->where_not_in('ht.type', $this->session->userdata('filtres_historique'));
		}
		
		$nb_lignes_historique = $this->db->count_all_results('historique h');
		$pagination = creer_pagination('historique/index', $nb_lignes_historique, 50, $offset);

		// On va chercher l'historique du joueur
		$this->db->select('h.donnees, h.date, ht.texte')
				 ->from('historique h')
				 ->join('historique_textes ht', 'h.texte_id_private = ht.id')
				 ->where('h.joueur_id', $this->session->userdata('id'))
				 ->where_in('h.notification', array(Bouzouk::Historique_Historique, Bouzouk::Historique_Full));
				 
		// Si il y a des filtres à appliquer
		if (count($this->session->userdata('filtres_historique')) > 0)
			$this->db->where_not_in('ht.type', $this->session->userdata('filtres_historique'));

		$query = $this->db->order_by('h.id', 'desc')
						  ->limit($pagination['par_page'], $pagination['offset'])
						  ->get();

		$historique = $query->result();

		// Affichage
		$vars = array(
			'historique' => $historique,
			'filtres'    => $this->filtres_valides,
			'pagination' => $pagination['liens']
		);
		return $this->layout->view('historique/index', $vars);
	}

	public function modifier_filtres()
	{
		$filtres_historique = array();

		// On récupère tous les filtres
		foreach ($this->filtres_valides as $cle => $filtre)
		{
			if ($this->input->post($filtre[0]) === false)
				$filtres_historique[] = $cle;
		}

		// On enregistre les nouveaux filtres
		$this->session->set_userdata('filtres_historique', $filtres_historique);
		return $this->index();
	}

	public function notifications()
	{
		// On va chercher toutes les notifications
		$query = $this->db->select('h.donnees, h.date, ht.texte, h.lue')
						  ->from('historique h')
						  ->join('historique_textes ht', 'h.texte_id_private = ht.id')
						  ->where('h.joueur_id', $this->session->userdata('id'))
						  ->where_in('h.notification', array(Bouzouk::Historique_Notification, Bouzouk::Historique_Full))
						  ->order_by('h.id', 'desc')
						  ->get();
		$notifications = $query->result();

		// On passe en lues toutes les notifications
		$this->db->set('lue', 1)
				 ->where('joueur_id', $this->session->userdata('id'))
				 ->where_in('notification', array(Bouzouk::Historique_Notification, Bouzouk::Historique_Full))
				 ->update('historique');
				 
		// Affichage
		$vars = array(
			'notifications' => $notifications,
		);
		return $this->layout->view('historique/notifications', $vars);
	}
	
	
	public function amis($offset = '0')
	{
		// On va chercher les amis du joueur
		$query = $this->db->select('j.id, j.pseudo, j.connecte, j.rang, j.faim, j.stress, j.sante, j.perso')
						  ->from('amis a')
						  ->join('joueurs j', 'a.ami_id = j.id')
						  ->where('a.joueur_id', $this->session->userdata('id'))
						  ->where('a.etat', Bouzouk::Amis_Accepte)
						  ->order_by('j.pseudo')
						  ->get();
		$amis = $query->result();
		$amis_id = array(0);

		foreach ($amis as $ami)
			$amis_id[] = $ami->id;
				
		// Pagination
		$this->db->where_in('joueur_id', $amis_id)
				 ->where('texte_id_public IS NOT NULL');
		
		$nb_lignes_historique = $this->db->count_all_results('historique');
		$pagination = creer_pagination('historique/amis', $nb_lignes_historique, 50, $offset);

		// On va chercher l'historique du joueur
		$query = $this->db->select('h.donnees, h.date, ht.texte, h.joueur_id, j.pseudo, j.rang')
						  ->from('historique h')
						  ->join('historique_textes ht', 'h.texte_id_public = ht.id')
						  ->join('joueurs j', 'h.joueur_id = j.id')
						  ->where_in('h.joueur_id', $amis_id)
						  ->where('texte_id_public IS NOT NULL')
						  ->where_in('h.notification', array(Bouzouk::Historique_Historique, Bouzouk::Historique_Full))
						  ->order_by('h.id', 'desc')
						  ->limit($pagination['par_page'], $pagination['offset'])
						  ->get();
		$historique = $query->result();

		// Affichage
		$vars = array(
			'historique' => $historique,
			'filtres'    => $this->filtres_valides,
			'pagination' => $pagination['liens']
		);
		return $this->layout->view('historique/amis', $vars);
	}
}
