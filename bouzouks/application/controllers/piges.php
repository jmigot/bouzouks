<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : affichage de courtes informations RP par les joueurs
 *
 * Auteur      : Fabien Foixet (fabien@foixet.com)
 * Date        : février 2014
 *
 * Copyright (C) 2012-2014 Fabien Foixet - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Piges extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('lib_parser');
	}
	
	public function index($offset = '0')
	{
		// Pagination
		$nb_piges = $this->db->count_all_results('piges');
		$pagination = creer_pagination('piges/index', $nb_piges, 10, $offset);
		
		// On va chercher les dernières piges
		$query = $this->db->select('p.id, p.auteur_id, j.pseudo, j.rang, p.texte, p.date, p.lien, en_ligne')
						  ->from('piges p')
						  ->join('joueurs j', 'j.id = p.auteur_id')
						  ->order_by('p.date', 'desc')
						  ->limit(10, $pagination['offset'])
						  ->get();
		
		$vars = array(
			'piges'          => $query->result(),
			'pagination'     => $pagination['liens'],
			'table_smileys'  => creer_table_smileys('message'),
		);
		return $this->layout->view('piges/index', $vars);
	}
	
	public function rediger($pige_id = '0')
	{
		if ( ! entier_naturel($pige_id))
			show_404();
		
		// Si l'édition d'une pige est demandée
		if ($pige_id != '0')
		{
			// On va chercher les infos de la pige
			$query = $this->db->select('p.id, p.auteur_id, j.pseudo, p.texte, p.date, p.lien, p.en_ligne')
							  ->from('piges p')
							  ->join('joueurs j', 'j.id = p.auteur_id')
							  ->where('p.id', $pige_id)
							  ->get();

			// Si la pige n'existe pas
			if ($query->num_rows() == 0)
			{
				$this->echec("Cette pige n'existe pas");
				return $this->index();
			}

			$pige = $query->row();

			// Il faut que le journaliste ne soit pas stagiaire
			if ($pige->auteur_id != $this->session->userdata('id') && ! $this->bouzouk->is_journaliste(Bouzouk::Rang_Journaliste))
			{
				$this->echec("Tu n'es qu'un petit stagiaire...tu ne peux pas modifier les piges des autres journalistes, ils ne te font pas confiance...d'ailleurs, ils te regardent de travers en ce moment !");
				return redirect('piges');
			}
		}

		// Création d'une nouvelle pige
		else
		{
			// On construit une pige selon qu'il a été posté avec erreurs ou tout nouveau
			$pige                  = new StdClass;
			$pige->texte           = '';
			$pige->lien           = '';
			$pige->auteur_id       = $this->session->userdata('id');
			$pige->date            = bdd_datetime();
			$pige->en_ligne        = Bouzouk::Piges_Active;
		}

		// On affiche
		$vars = array(
			'pige_id'     => $pige_id,
			'pige'        => $pige,
		);
		return $this->layout->view('piges/rediger', $vars);
	}

	public function modifier()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('lien', 'Le lien', 'max_length[255]');
		$this->form_validation->set_rules('texte', 'Le texte', 'required|min_length[20]|max_length[200]');
		$this->form_validation->set_rules('pige_id', "La pige", 'required|is_natural');

		// Vérifications en plus pour les chefs
		if ($this->bouzouk->is_journaliste(Bouzouk::Rang_JournalisteChef))
			$this->form_validation->set_rules('statut', "Le statut", 'required|is_natural');

		if ( ! $this->form_validation->run())
			return $this->rediger($this->input->post('pige_id'));
		
		// On vérifie que le lien pointe bien vers Bouzouks.net
		if ( ! $this->input->post('lien') == '' && ! preg_match('#^'.preg_quote(site_url()).'#i', $this->input->post('lien')))
		{
			$this->echec("Le lien n'est pas valide");
			return $this->rediger($this->input->post('pige_id'));
		}

		// Vérifications en plus pour les chefs
		if ($this->bouzouk->is_journaliste(Bouzouk::Rang_JournalisteChef))
		{
			// Le statut doit être valide
			if ( ! in_array($this->input->post('statut'), array(Bouzouk::Piges_Active, Bouzouk::Piges_Desactive)))
			{
				$this->echec("Le statut n'est pas valide");
				return $this->rediger($this->input->post('pige_id'));
			}
		}

		// Nouvelle pige
		if ($this->input->post('pige_id') == '0')
		{
			$data_piges = array(
				'auteur_id' => $this->session->userdata('id'),
				'texte'     => $this->input->post('texte'),
				'lien'     => $this->input->post('lien'),
				'date'      => bdd_datetime(),
				'en_ligne'  => Bouzouk::Piges_Active
			);

			// Les chefs peuvent ajouter plus de champs
			if ($this->bouzouk->is_journaliste(Bouzouk::Rang_JournalisteChef))
				$data_piges['en_ligne']  = $this->input->post('statut');

			$this->db->insert('piges', $data_piges);
			
			$this->succes('Cette pige a bien été publié');
		}

		// Modification
		else
		{
			// On va chercher la piges
			$query = $this->db->select('en_ligne, texte, auteur_id')
							  ->from('piges')
							  ->where('id', $this->input->post('pige_id'))
							  ->get();

			// On regarde si la pige existe
			if ($query->num_rows() == 0)
			{
				$this->echec("Cette pige n'existe pas");
				return $this->index();
			}

			$pige = $query->row();

			// Il faut que le journaliste ne soit pas stagiaire, si il est stagiaire et qu'il est l'auteur de la pige il peut le modifier
			if ($pige->auteur_id != $this->session->userdata('id') && ! $this->bouzouk->is_journaliste(Bouzouk::Rang_Journaliste))
			{
				$this->echec("Tu n'es qu'un petit stagiaire...tu ne peux pas modifier les piges des autres journalistes, ils ne te font pas confiance...d'ailleurs, ils te regardent de travers en ce moment !");
				return $this->index();
			}

			// On enregistre les modifications
			$data_piges = array(
				'lien'     => $this->input->post('lien'),
				'texte'     => $this->input->post('texte'),
			);

			// Les chefs peuvent modifier plus de champs
			if ($this->bouzouk->is_journaliste(Bouzouk::Rang_JournalisteChef))
				$data_piges['en_ligne']  = $this->input->post('statut');

			$this->db->where('id', $this->input->post('pige_id'))
					 ->update('piges', $data_piges);
			
			$this->succes('Cette pige a bien été modifiée');
		}
		
		return $this->index();
	}
}