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
 
class Moderer_missives extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('lib_parser');
	}
	
	public function index($offset = '0')
	{
		// Pagination
		if ($this->input->post())
		{
			$this->session->set_userdata('admin-missives-filtre_joueur_1', (int) $this->input->post('filtre_joueur_1'));
			$this->session->set_userdata('admin-missives-filtre_joueur_2', (int) $this->input->post('filtre_joueur_2'));
		}

		$filtre_joueur_1 = $this->session->userdata('admin-missives-filtre_joueur_1');
		$filtre_joueur_2 = $this->session->userdata('admin-missives-filtre_joueur_2');

		if ($filtre_joueur_1 > 0 && $filtre_joueur_2 > 0)
		{
			$this->db->where("((expediteur_id = $filtre_joueur_1 AND destinataire_id = $filtre_joueur_2) OR (expediteur_id = $filtre_joueur_2 AND destinataire_id = $filtre_joueur_1))");
		}

		else if ($filtre_joueur_1 > 0)
		{
			$this->db->where("(expediteur_id = $filtre_joueur_1 OR destinataire_id = $filtre_joueur_1)");
		}

		else if ($filtre_joueur_2 > 0)
		{
			$this->db->where("(expediteur_id = $filtre_joueur_2 OR destinataire_id = $filtre_joueur_2)");
		}

		$nb_missives = $this->db->where_not_in('expediteur_id', $this->bouzouk->get_robots())
								->count_all_results('missives');
		$pagination = creer_pagination('staff/moderer_missives/index', $nb_missives, 100, $offset, 4);

		// On va chercher toutes les missives en excluant celles des robots
		$this->db->select('m.id, m.date_envoi, m.objet, m.message, j1.id AS expediteur_id, j1.pseudo AS expediteur_pseudo, j2.id AS destinataire_id, j2.pseudo AS destinataire_pseudo')
				 ->from('missives m')
				 ->join('joueurs j1', 'j1.id = m.expediteur_id')
				 ->join('joueurs j2', 'j2.id = m.destinataire_id');

		if ($filtre_joueur_1 > 0 && $filtre_joueur_2 > 0)
		{
			$this->db->where("((m.expediteur_id = $filtre_joueur_1 AND m.destinataire_id = $filtre_joueur_2) OR (m.expediteur_id = $filtre_joueur_2 AND m.destinataire_id = $filtre_joueur_1))");
		}

		else if ($filtre_joueur_1 > 0)
		{
			$this->db->where("(m.expediteur_id = $filtre_joueur_1 OR m.destinataire_id = $filtre_joueur_1)");
		}

		else if ($filtre_joueur_2 > 0)
		{
			$this->db->where("(m.expediteur_id = $filtre_joueur_2 OR m.destinataire_id = $filtre_joueur_2)");
		}

		$query = $this->db->where_not_in('m.expediteur_id', array_merge($this->bouzouk->get_robots(), $this->bouzouk->get_inactifs()))
						  ->order_by('m.date_envoi', 'desc')
						  ->limit($pagination['par_page'], $pagination['offset'])
						  ->get();
		$missives = $query->result();

		// On va chercher la liste des expéditeurs/destinataires pour les filtres (excluant les robots)
		$select_joueurs1 = $this->bouzouk->select_joueurs(array('name' => 'filtre_joueur_1','joueur_id' => $this->session->userdata('admin-missives-filtre_joueur_1'), 'status_not_in' => array(Bouzouk::Joueur_GameOver, Bouzouk::Joueur_Banni, Bouzouk::Joueur_Robot)));
		$select_joueurs2 = $this->bouzouk->select_joueurs(array('name' => 'filtre_joueur_2','joueur_id' => $this->session->userdata('admin-missives-filtre_joueur_2'), 'status_not_in' => array(Bouzouk::Joueur_GameOver, Bouzouk::Joueur_Banni, Bouzouk::Joueur_Robot)));

		// On affiche
		$this->load->library('lib_missive');
		$vars = array(
			'missives'        => $missives,
			'select_joueurs1' => $select_joueurs1,
			'select_joueurs2' => $select_joueurs2,
			'pagination'      => $pagination['liens']
		);
		return $this->layout->view('staff/moderer_missives', $vars);
	}

	public function supprimer()
	{
		// On essaye de supprimer toutes les lettres correspondant aux identifiants donnés
		$this->db->where_in('id', $this->input->post('ids'))
				 ->delete('missives');

		// Si aucune lettre n'a été effacée
		if ($this->db->affected_rows() == 0)
		{
			$this->echec("Aucune missive n'a été sélectionnée");
		}

		else if ($this->db->affected_rows() == 1)
		{
			$this->succes('La missive a bien été supprimée');
		}

		else
		{
			$this->succes('Les missives ont bien été supprimées');
		}

		return $this->index();
	}
}