<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : webservices destinés principalement aux requêtes Ajax du jeu sur la gazette
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : juillet 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class WebServices_gazette extends MY_Controller
{
	private $tchat_options;
	
	public function __construct()
	{
		parent::__construct();

		// Si ce controller n'est pas appelé en Ajax ou en Post
		if ( ! $this->input->is_ajax_request() || ! $this->input->isPost())
			show_404();

		// On renvoit du JSON
		$this->output->set_content_type('application/json');
	}

	public function changer_mutex_article()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('action', "L'action sur le mutex", 'required');
		$this->form_validation->set_rules('article_id', "L'article", 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
		{
			return $this->output->set_output(json_encode(''));
		}

		$reponse = array(
			'message' => '',
			'action'  => 'verrouiller'
		);

		$this->load->library('lib_gazette');

		if ($this->input->post('action') == 'verrouiller')
		{
			// Si l'auteur n'a pas trop de mutex en cours
			$mutex = $this->lib_gazette->get_mutex_auteur($this->session->userdata('id'));

			if (count($mutex) >= 1)
				$reponse['message'] = "Tu es déjà en train de modifier l'article <span class='pourpre'>".$mutex[0]->titre."</span>";

			else
			{
				// On regarde si l'article n'a pas déjà un mutex
				$mutex = $this->lib_gazette->get_mutex_article($this->input->post('article_id'));

				if (isset($mutex->id) && $mutex->id != $this->session->userdata('id'))
					$reponse['message'] = "<span class='rouge gras'>Cet article est déjà en cours d'édition par ".profil($mutex->id, $mutex->pseudo, $mutex->rang).'</span>';

				else
				{
					$this->lib_gazette->verrouiller_mutex($this->input->post('article_id'), $this->session->userdata('id'));
					$reponse['message'] = "Cet article est maintenant en cours d'édition par ".profil($this->session->userdata('id'), $this->session->userdata('pseudo'), $this->session->userdata('rang'));
					$reponse['action']  = 'deverrouiller';
					$reponse['titre'] = $mutex->titre;
					$reponse['texte'] = $mutex->texte;
				}
			}
		}

		else if ($this->input->post('action') == 'deverrouiller')
		{
			// On regarde si l'article n'a pas déjà un mutex
			$mutex = $this->lib_gazette->get_mutex_article($this->input->post('article_id'));

			if (isset($mutex->id) && $mutex->id != $this->session->userdata('id'))
				$reponse['message'] = "<span class='rouge gras'>Cet article est déjà en cours d'édition par ".profil($mutex->id, $mutex->pseudo, $mutex->rang).'</span>';

			else
			{
				$this->lib_gazette->deverrouiller_mutex($this->input->post('article_id'), $this->session->userdata('id'));
				$reponse['message'] = "Plus personne ne rédige cet article en ce moment";
			}
		}

		else
			$reponse['message'] = "Erreur lors de la requête";

		// On affiche les données
		return $this->output->set_output(json_encode($reponse));
	}

	public function previsualisation_gazette()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('titre', "Le titre", 'required');
		$this->form_validation->set_rules('texte', 'Le texte', 'required');
		$this->form_validation->set_rules('article_id', "L'article", 'required|is_natural');
		
		if ( ! $this->form_validation->run())
		{
			return $this->output->set_output(json_encode(''));
		}

		// On génère le html
		$gazette = array(
			Bouzouk::Gazette_Meteo      => null,
			Bouzouk::Gazette_Lohtoh     => null,
			Bouzouk::Gazette_Fete       => null,
			Bouzouk::Gazette_Classement => null,
			Bouzouk::Gazette_Article    => null
		);

		// On va chercher les infos de l'article
		if ($this->input->post('article_id') > 0)
		{
			// On va chercher les infos de l'article
			$query = $this->db->select('g.date, g.auteur_id, j.pseudo, g.image_url')
							  ->from('gazettes g')
							  ->join('joueurs j', 'j.id = g.auteur_id')
							  ->where('g.id', $this->input->post('article_id'))
							  ->get();

			// Si l'article n'existe pas
			if ($query->num_rows() == 0)
				return $this->output->set_output(json_encode(''));

			$article = $query->row();
		}

		else
		{
			$article            = new StdClass;
			$article->date      = bdd_datetime();
			$article->auteur_id = $this->session->userdata('id');
			$article->pseudo    = $this->session->userdata('pseudo');
			$article->image_url = '';
		}

		$article->titre = $this->input->post('titre');
		$article->texte = $this->input->post('texte');
		$gazette[Bouzouk::Gazette_Article] = $article;
			
		// On va chercher les mini-articles
  		$query = $this->db->select('g.id, g.auteur_id, j.pseudo, g.titre, g.texte, g.type, g.image_url, g.date')
						  ->from('gazettes g')
						  ->join('joueurs j', 'j.id = g.auteur_id')
						  ->where('g.en_ligne', Bouzouk::Gazette_Publie)
						  ->where_in('type', array(Bouzouk::Gazette_Meteo, Bouzouk::Gazette_Lohtoh, Bouzouk::Gazette_Fete, Bouzouk::Gazette_Classement))
						  ->order_by('g.date', 'desc')
						  ->get();
		$mini_gazettes = $query->result();

		// Pour chaque mini-article récupéré
		foreach ($mini_gazettes as $article)
		{
			// On regarde si on peut le caser dans la gazette
			foreach ($gazette as $type => $contenu)
			{
				if ($type == $article->type && $contenu == null)
				{
					$gazette[$type] = $article;
					break;
				}
			}
		}

		// On affiche
		$vars = array(
			'gazette'          => $gazette,
			'anciens_articles' => array(),
			'previsualisation' => true
		);
		
		$this->load->library('lib_parser');
		$html = $this->load->view('gazette/article', $vars, true);

		$reponse = array(
			'html' => $html
		);

		// On affiche les données
		return $this->output->set_output(json_encode($reponse));
	}

	public function images_disponibles()
	{
		// On récupère les images disponibles
		$images = array();
		$fichiers = glob(FCPATH."webroot/images/uploads/gazette/*");

		foreach ($fichiers as $fichier)
		{
			if ( ! is_dir($fichier) && mb_substr($fichier, -10) != 'index.html')
				$images[] = basename($fichier);
		}

		// On génère le html
		$vars = array(
			'images' => $images
		);
		$html = $this->load->view('gazette/images_disponibles', $vars, true);

		// On affiche
		$reponse = array('html' => $html);
		return $this->output->set_output(json_encode($reponse));
	}
}

