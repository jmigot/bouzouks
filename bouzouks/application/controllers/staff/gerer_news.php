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
 
class Gerer_news extends MY_Controller
{
	public function index($offset = '0')
	{
		// Pagination
		$nb_news = $this->db->count_all('news');
		$pagination = creer_pagination('staff/gerer_news/index', $nb_news, 10, $offset, 4);

		// On récupère les news
		$query = $this->db->select('n.id, n.date, n.titre, n.texte, n.auteur_id, j.pseudo, j.rang, n.en_ligne')
						  ->from('news n')
						  ->join('joueurs j', 'j.id = n.auteur_id')
						  ->order_by('n.date', 'desc')
						  ->limit($pagination['par_page'], $pagination['offset'])
						  ->get();
		$news = $query->result();
		
		$vars = array(
			'news'           => $news,
			'pagination'     => $pagination['liens']
		);
		return $this->layout->view('staff/gerer_news/index', $vars);
	}

	public function rediger($news_id = '0')
	{
		if ( ! entier_naturel($news_id))
		{
			show_404();
		}

		// Si l'édition d'un article est demandée
		if ($news_id != '0')
		{
			// On va chercher les infos de la news
			$query = $this->db->select('n.id, n.titre, n.texte, n.en_ligne, n.date, n.auteur_id')
							  ->from('news n')
							  ->join('joueurs j', 'j.id = n.auteur_id')
							  ->where('n.id', $news_id)
							  ->get();

			// Si l'article n'existe pas
			if ($query->num_rows() == 0)
			{
				$this->echec("Cette news n'existe pas");
				return $this->gerer();
			}

			$news = $query->row();
		}

		// Création d'un nouvel article
		else
		{
			// On construit un article selon qu'il a été posté avec erreurs ou tout nouveau
			$news               = new StdClass;
			$news->titre        = '';
			$news->texte        = '';
			$news->auteur_id    = $this->session->userdata('id');
			$news->date         = bdd_datetime();
			$news->en_ligne     = Bouzouk::Gazette_Brouillon;
		}

		// Si c'est une modification qu'on a posté, on écrase les champs
		$news->modification  = ($this->input->isPost() && $this->input->post('titre') !== false);

		if ($news->modification)
		{
			$news->auteur_id = $this->input->post('auteur_id');
			$news->date      = $this->input->post('date_annee').'-'.$this->input->post('date_mois').'-'.$this->input->post('date_jour').' '.
							   $this->input->post('date_heures').':'.$this->input->post('date_minutes').':00';
			$news->en_ligne  = $this->input->post('statut');
		}
			
		// On affiche
		$vars = array(
			'news_id'        => $news_id,
			'news'           => $news,
			'select_auteurs' => $this->bouzouk->select_joueurs(array('name' => 'auteur_id', 'joueur_id' => $news->auteur_id)),
			'table_smileys'  => creer_table_smileys('texte'),
		);
		return $this->layout->view('staff/gerer_news/rediger', $vars);
	}

	public function modifier()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('titre', 'Le titre', 'required|min_length[3]|max_length[100]');
		$this->form_validation->set_rules('texte', 'Le texte', 'required|min_length[15]|max_length[5000]|callback__texte_check');
		$this->form_validation->set_rules('news_id', "L'article", 'required|is_natural');
		$this->form_validation->set_rules('auteur_id', "L'auteur", 'required|is_natural_no_zero');
		$this->form_validation->set_rules('date_jour', 'Le jour du statut', 'required|is_natural_no_zero|less_than_or_equal[31]');
		$this->form_validation->set_rules('date_mois', 'Le mois du statut', 'required|is_natural_no_zero|less_than_or_equal[12]');
		$this->form_validation->set_rules('date_annee', "L'année du statut", 'required|is_natural');
		$this->form_validation->set_rules('date_heures', "L'heure du statut", 'required|is_natural|less_than_or_equal[23]');
		$this->form_validation->set_rules('date_minutes', "Les minutes du statut", 'required|is_natural|less_than_or_equal[59]');
		$this->form_validation->set_rules('statut', "Le statut", 'required|is_natural');
		
		if ( ! $this->form_validation->run())
		{
			return $this->rediger($this->input->post('news_id'));
		}

		// La date doit être valide
		if ( ! checkdate($this->input->post('date_mois'), $this->input->post('date_jour'), $this->input->post('date_annee')))
		{
			$this->echec("La date est invalide");
			return $this->rediger($this->input->post('news_id'));
		}

		// L'auteur doit exister
		$existe = $this->db->where('id', $this->input->post('auteur_id'))
						   ->where_not_in('statut', array(Bouzouk::Joueur_Inactif))
						   ->count_all_results('joueurs');

		if ( ! $existe)
		{
			$this->echec("L'auteur n'existe pas");
			return $this->rediger($this->input->post('news_id'));
		}

		// Le statut doit être valide
		if ( ! in_array($this->input->post('statut'), array(0, 1)))
		{
			$this->echec("Le statut n'est pas valide");
			return $this->rediger($this->input->post('news_id'));
		}

		// Nouvel article
		if ($this->input->post('news_id') == '0')
		{
			$data_news = array(
				'auteur_id' => $this->input->post('auteur_id'),
				'titre'     => $this->input->post('titre'),
				'texte'     => $this->input->post('texte'),
				'date'      => $this->input->post('date_annee').'-'.$this->input->post('date_mois').'-'.$this->input->post('date_jour').' '.
					 		   $this->input->post('date_heures').':'.$this->input->post('date_minutes').':00',
				'en_ligne'  => $this->input->post('statut')
			);

			$this->db->insert('news', $data_news);

			// Si la news est en ligne, on envoit une notif à tous les joueurs
			if ($this->input->post('statut') == Bouzouk::News_Publie)
				$this->lib_staff->envoyer_notification_joueurs("La <span class='pourpre'>TeamBouzouk</span> vient de publier une nouvelle news en page d'accueil");
		}

		// Modification
		else
		{
			// On regarde si la news existe
			$existe = $this->db->where('id', $this->input->post('news_id'))
							   ->count_all_results('news');
			
			if ($existe == 0)
			{
				$this->echec("Cette news n'existe pas");
				return $this->index();
			}

			// On enregistre les modifications
			$data_news = array(
				'titre'     => $this->input->post('titre'),
				'texte'     => $this->input->post('texte'),
				'auteur_id' => $this->input->post('auteur_id'),
				'date'      => $this->input->post('date_annee').'-'.$this->input->post('date_mois').'-'.$this->input->post('date_jour').' '.
							   $this->input->post('date_heures').':'.$this->input->post('date_minutes').':00',
				'en_ligne'  => $this->input->post('statut'),
			);

			$publiee = $this->db->where('id', $this->input->post('news_id'))
								->where('en_ligne', Bouzouk::News_Publie)
					 			->count_all_results('news');

			// Si la news est en ligne et qu'elle ne l'était pas avant, on envoit une notif à tous les joueurs
			if ( ! $publiee && $this->input->post('statut') == Bouzouk::News_Publie)
				$this->lib_staff->envoyer_notification_joueurs("La <span class='pourpre'>TeamBouzouk</span> vient de publier une nouvelle news en page d'accueil");

			// On enregistre les modifications
			$this->db->where('id', $this->input->post('news_id'))
					 ->update('news', $data_news);
		}


		// On affiche un message de confirmation
		$this->succes("La news a bien été enregistrée");
		return $this->index();
	}
}