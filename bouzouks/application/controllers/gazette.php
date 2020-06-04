<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions de gestion de la gazette de la ville pour les journalistes
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Gazette extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('lib_parser');
	}
	
	public function index($offset = '0')
	{
		$gazette = array(
			Bouzouk::Gazette_Meteo      => null,
			Bouzouk::Gazette_Lohtoh     => null,
			Bouzouk::Gazette_Fete       => null,
			Bouzouk::Gazette_Classement => null,
			Bouzouk::Gazette_Article    => null,
			Bouzouk::Gazette_PubClan    => null
		);
		$anciens_articles = array();

		// Pagination
		$nb_articles = $this->db->where('en_ligne', Bouzouk::Gazette_Publie)
								->where('type', Bouzouk::Gazette_Article)
								->count_all_results('gazettes');
		$pagination = creer_pagination('gazette/index', $nb_articles, 1, $offset);

		// On va chercher les derniers articles
		$query = $this->db->select('g.id, g.auteur_id, j.pseudo, g.titre, g.texte, g.type, g.resume, g.image_url, g.date, g.topic_id, tt.num_replies AS nb_commentaires')
						  ->from('gazettes g')
						  ->join('joueurs j', 'j.id = g.auteur_id', 'left')
						  ->join('tobozon_topics tt', 'tt.id = g.topic_id', 'left')
						  ->where('g.en_ligne', Bouzouk::Gazette_Publie)
						  ->where('g.type', Bouzouk::Gazette_Article)
						  ->order_by('g.date', 'desc')
						  ->limit(5, $pagination['offset'])
						  ->get();
		$gazettes = $query->result();

		$gazette[Bouzouk::Gazette_Article] = $gazettes[0];

		for ($i = 1; $i < $query->num_rows(); $i++)
		{
			$anciens_articles[] = $gazettes[$i];
		}

		// On va chercher les mini-articles
  		$query = $this->db->select('g.id, g.auteur_id, j.pseudo, g.titre, g.texte, g.type, g.image_url, g.date')
						  ->from('gazettes g')
						  ->join('joueurs j', 'j.id = g.auteur_id', 'left')
						  ->where('g.en_ligne', Bouzouk::Gazette_Publie)
						  ->where_in('type', array(Bouzouk::Gazette_Meteo, Bouzouk::Gazette_Lohtoh, Bouzouk::Gazette_Fete, Bouzouk::Gazette_Classement, Bouzouk::Gazette_PubClan))
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

		$vars = array(
			'gazette'          => $gazette,
			'anciens_articles' => $anciens_articles,
			'pagination'       => $pagination['liens']
		);
		return $this->layout->view('gazette/index', $vars);
	}

	public function article_id($article_id)
	{
		if ( ! entier_naturel($article_id))
		{
			show_404();
		}

		// On récupère la date de l'article
		$query = $this->db->select('date')
						  ->from('gazettes')
						  ->where('id', $article_id)
						  ->get();

		if ($query->num_rows() == 0)
		{
			show_404();
		}

		$article = $query->row();
		
		// On récupère l'offset qui correspond à cet article_id et on redirige vers l'index
		$offset = $this->db->where('date > "'.$article->date.'"')
						   ->where('en_ligne', Bouzouk::Gazette_Publie)
						   ->where('type', Bouzouk::Gazette_Article)
						   ->count_all_results('gazettes');
		redirect('gazette/index/'.$offset);
	}

	public function gerer($offset = '0')
	{
		// Pagination
		$nb_articles = $this->db->where('type', Bouzouk::Gazette_Article)
								->count_all_results('gazettes');
		$pagination = creer_pagination('gazette/gerer', $nb_articles, 10, $offset);

		// On va chercher la liste des derniers articles du journal
		$query = $this->db->select('g.id, g.auteur_id, j.pseudo, j.rang, g.titre, g.date, g.en_ligne')
						  ->from('gazettes g')
						  ->join('joueurs j', 'j.id = g.auteur_id')
						  ->where('g.type', Bouzouk::Gazette_Article)
						  ->order_by('g.date', 'desc')
						  ->limit($pagination['par_page'], $pagination['offset'])
						  ->get();
		$articles = $query->result();

		$nb_articles = array();

		if ($this->bouzouk->is_journaliste(Bouzouk::Rang_JournalisteChef))
		{
			// On va chercher le nombre d'articles par journaliste
			$query = $this->db->select('j.id, j.pseudo, j.rang, COUNT(g.id) AS nb_articles')
							  ->from('joueurs j')
							  ->join('gazettes g', 'g.auteur_id = j.id')
							  ->where('g.type', Bouzouk::Gazette_Article)
							  ->group_by('j.id')
							  ->order_by('nb_articles', 'desc')
							  ->order_by('j.pseudo')
							  ->get();

			$nb_articles = $query->result();
		}

		// On affiche les articles
		$vars = array(
			'articles'        => $articles,
			'nb_articles'     => $nb_articles,
			'table_smileys'   => creer_table_smileys('message'),
			'pagination'      => $pagination['liens']
		);
		return $this->layout->view('gazette/gerer', $vars);
	}

	public function _texte_check($texte)
	{
		// On cherche tous les liens profil de la news
		if (preg_match_all('#{(.+)\|(\d+)}#Ui', $texte, $joueurs))
		{
			// Pour chaque lien trouvé
			for ($i = 0; $i < count($joueurs[0]); $i++)
			{
				// On récupère id, pseudo
				$id     = $joueurs[2][$i];
				$pseudo = $joueurs[1][$i];

				// On vérifie que le bouzouk existe et est valide
				$joueur_existe = $this->db->where('id', $id)
										  ->where('pseudo', $pseudo)
										  ->count_all_results('joueurs');

				// Si aucune correspondance, le rédacteur a trafiqué la chaine
				if ($joueur_existe == 0)
				{
					$this->form_validation->set_message('_texte_check', "Le bouzouk {".form_prep($pseudo).'|'.form_prep($id)."} n'existe pas");
					return false;
				}
			}
		}

		return true;
	}

	public function historique_article($article_id = '0')
	{
		if ( ! entier_naturel($article_id))
		{
			show_404();
		}

		if ($article_id != '0')
		{
			// On vérifie que l'article existe
			$existe = $this->db->where('id', $article_id)
							->count_all_results('gazettes');

			if ($existe == 0)
			{
				$this->echec("Cet article n'existe pas");
				return $this->gerer();
			}
		}

		$query = $this->db->select('j.id AS auteur_id, j.pseudo AS auteur_pseudo, j.rang AS auteur_rang, h_g.date, h_g.commentaire, h_g.modification')
						  ->from('historique_gazettes h_g')
						  ->join('joueurs j', 'j.id = h_g.auteur_id')
						  ->where('article_id', $article_id)
						  ->order_by('h_g.date', 'desc')
						  ->get();

		$historique = $query->result();

		$vars = array(
			'article_id' => $article_id,
			'historique' => $historique
		);
		return $this->layout->view('gazette/historique_article', $vars);
	}

	public function rediger($article_id = '0')
	{
		if ( ! entier_naturel($article_id))
			show_404();

		// Si l'édition d'un article est demandée
		if ($article_id != '0')
		{
			// On va chercher les infos de l'article
			$query = $this->db->select('g.id, g.titre, g.texte, g.resume, g.en_ligne, g.date, g.auteur_id, g.xp_distribuee, g.mutex_auteur_id, j.pseudo AS mutex_pseudo, j.rang AS mutex_rang')
							  ->from('gazettes g')
							  ->join('joueurs j', 'j.id = g.mutex_auteur_id', 'left')
							  ->where('g.id', $article_id)
							  ->get();

			// Si l'article n'existe pas
			if ($query->num_rows() == 0)
			{
				$this->echec("Cet article n'existe pas");
				return $this->gerer();
			}

			$article = $query->row();

			// Il faut que le journaliste ne soit pas stagiaire
			if ($article->auteur_id != $this->session->userdata('id') && ! $this->bouzouk->is_journaliste(Bouzouk::Rang_Journaliste))
			{
				$this->echec("Tu n'es qu'un petit stagiaire...tu ne peux pas modifier les articles des autres journalistes, ils ne te font pas confiance...d'ailleurs, ils te regardent de travers en ce moment !");
				return $this->gerer();
			}

			// Si l'article n'est pas un brouillon et que le joueur n'est pas chef
			else if ($article->en_ligne != Bouzouk::Gazette_Brouillon AND ! $this->bouzouk->is_journaliste(Bouzouk::Rang_JournalisteChef))
			{
				$this->echec("Tu ne peux plus modifier cet article car ce n'est pas un brouillon, si tu veux le modifier contacte un modérateur ou un administrateur pour qu'il repasse l'article en brouillon");
				return $this->gerer();
			}

			$modification = true;
		}

		// Création d'un nouvel article
		else
		{
			// On construit un article selon qu'il a été posté avec erreurs ou tout nouveau
			$article                  = new StdClass;
			$article->titre           = '';
			$article->texte           = '';
			$article->resume          = '';
			$article->auteur_id       = $this->session->userdata('id');
			$article->date            = bdd_datetime();
			$article->en_ligne        = Bouzouk::Gazette_Brouillon;
			$article->xp_distribuee   = 0;
			$article->modification    = false;
			$article->mutex_auteur_id = null;

			$modification = false;
		}

		// Si c'est une modification qu'on a posté, on écrase les champs
		$article->modification  = ($this->input->isPost() && $this->input->post('titre') !== false);

		if ($article->modification)
		{
			$article->auteur_id = $this->input->post('auteur_id');
			$article->date      = $this->input->post('date_annee').'-'.$this->input->post('date_mois').'-'.$this->input->post('date_jour').' '.
								  $this->input->post('date_heures').':'.$this->input->post('date_minutes').':00';
			$article->en_ligne  = $this->input->post('statut');
		}

		// On affiche
		$vars = array(
			'select_ajout'   => $this->bouzouk->select_joueurs(array('name' => 'select_ajout', 'rangs' => false)),
			'article_id'     => $article_id,
			'article'        => $article,
			'select_auteurs' => $this->bouzouk->select_joueurs(array('name' => 'auteur_id', 'joueur_id' => $article->auteur_id)),
			'modification'   => $modification
		);
		return $this->layout->view('gazette/rediger', $vars);
	}

	public function lire($article_id = '0')
	{
		if ( ! entier_naturel($article_id))
		{
			show_404();
		}

		$gazette = array(
			Bouzouk::Gazette_Meteo      => null,
			Bouzouk::Gazette_Lohtoh     => null,
			Bouzouk::Gazette_Fete       => null,
			Bouzouk::Gazette_Classement => null,
			Bouzouk::Gazette_Article    => null,
			Bouzouk::Gazette_PubClan    => null
		);
		
		// On va chercher les infos de l'article
		$query = $this->db->select('g.id, g.titre, g.texte, g.date, g.auteur_id, g.image_url, j.pseudo')
							->from('gazettes g')
							->join('joueurs j', 'j.id = g.auteur_id')
							->where('g.id', $article_id)
							->get();

		// Si l'article n'existe pas
		if ($query->num_rows() == 0)
		{
			$this->echec("Cet article n'existe pas");
			return $this->gerer();
		}

		$gazette[Bouzouk::Gazette_Article] = $query->row();

		// On va chercher les mini-articles
  		$query = $this->db->select('g.id, g.auteur_id, j.pseudo, g.titre, g.texte, g.type, g.image_url, g.date')
						  ->from('gazettes g')
						  ->join('joueurs j', 'j.id = g.auteur_id')
						  ->where('g.en_ligne', Bouzouk::Gazette_Publie)
						  ->where_in('type', array(Bouzouk::Gazette_Meteo, Bouzouk::Gazette_Lohtoh, Bouzouk::Gazette_Fete, Bouzouk::Gazette_Classement, Bouzouk::Gazette_PubClan))
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
			'anciens_articles' => array()
		);
		return $this->layout->view('gazette/lire', $vars);
	}

	public function modifier()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('resume', 'Le résumé', 'required|min_length[5]|max_length[250]');
		$this->form_validation->set_rules('titre', 'Le titre', 'required|min_length[5]|max_length[80]');
		$this->form_validation->set_rules('texte', 'Le texte', 'required|min_length[200]|max_length[10000]|callback__texte_check');
		$this->form_validation->set_rules('article_id', "L'article", 'required|is_natural');

		// Vérifications en plus pour les chefs
		if ($this->bouzouk->is_journaliste(Bouzouk::Rang_JournalisteChef))
		{
			$this->form_validation->set_rules('auteur_id', "L'auteur", 'required|is_natural_no_zero');
			$this->form_validation->set_rules('date_jour', 'Le jour du statut', 'required|is_natural_no_zero|less_than_or_equal[31]');
			$this->form_validation->set_rules('date_mois', 'Le mois du statut', 'required|is_natural_no_zero|less_than_or_equal[12]');
			$this->form_validation->set_rules('date_annee', "L'année du statut", 'required|is_natural');
			$this->form_validation->set_rules('date_heures', "L'heure du statut", 'required|is_natural|less_than_or_equal[23]');
			$this->form_validation->set_rules('date_minutes', "Les minutes du statut", 'required|is_natural|less_than_or_equal[59]');
			$this->form_validation->set_rules('statut', "Le statut", 'required|is_natural');
			$this->form_validation->set_rules('experience_publication', "L'expérience publication", '');
			$this->form_validation->set_rules('prevenir_joueurs', "La prévention des joueurs", '');
		}

		// Si c'est une modification, il faut un commentaire
		if ($this->input->post('article_id') != '0')
			$this->form_validation->set_rules('commentaire', 'Le commentaire de modification', 'required|min_length[5]|max_length[200]');

		if ( ! $this->form_validation->run())
			return $this->rediger($this->input->post('article_id'));

		// Vérifications en plus pour les chefs
		if ($this->bouzouk->is_journaliste(Bouzouk::Rang_JournalisteChef))
		{
			// La date doit être valide
			if ( ! checkdate($this->input->post('date_mois'), $this->input->post('date_jour'), $this->input->post('date_annee')))
			{
				$this->echec("La date est invalide");
				return $this->rediger($this->input->post('article_id'));
			}

			// L'auteur doit exister
			$existe = $this->db->where('id', $this->input->post('auteur_id'))
							   ->where_not_in('statut', array(Bouzouk::Joueur_Inactif))
							   ->count_all_results('joueurs');

			if ( ! $existe)
			{
				$this->echec("L'auteur n'existe pas");
				return $this->rediger($this->input->post('article_id'));
			}

			// Le statut doit être valide
			if ( ! in_array($this->input->post('statut'), array(Bouzouk::Gazette_Brouillon, Bouzouk::Gazette_Publie, Bouzouk::Gazette_Refuse)))
			{
				$this->echec("Le statut n'est pas valide");
				return $this->rediger($this->input->post('article_id'));
			}

			// Si on est pas admin et qu'on veut donner de l'xp, l'xp ne doit pas encore avoir été donné une seule fois
			if ($this->input->post('experience_publication') !== false && ! $this->bouzouk->is_admin())
			{
				$distribuee = $this->db->where('xp_distribuee > 0')
									   ->where('id', $this->input->post('article_id'))
									   ->count_all_results('gazettes');

				if ($distribuee > 0)
				{
					$this->echec("Tu ne peux pas donner plusieurs fois de l'xp à l'auteur");
					return $this->rediger($this->input->post('article_id'));
				}
			}
		}

		// On vérifie que les images existent bien
		$images = array();
		$fichiers = glob(FCPATH."webroot/images/uploads/gazette/*");

		foreach ($fichiers as $fichier)
		{
			if ( ! is_dir($fichier) && mb_substr($fichier, -10) != 'index.html')
				$images[] = basename($fichier);
		}

		preg_match_all('#\[img=(.+)\|taille=(\d+)\|class=(.*)\]#Usi', $this->input->post('texte'), $matches);

		// Images
		foreach ($matches[1] as $match)
		{
			if ( ! in_array($match, $images))
			{
				$this->echec("Tu as inséré une image qui n'existe pas, pour des raisons de sécurité tu dois attendre que l'image soit uploadée par un rédacteur en chef.");
				return $this->rediger($this->input->post('article_id'));
			}
		}

		// Class
		foreach ($matches[3] as $match)
		{
			if ( ! in_array($match, array('', 'fl-gauche', 'fl-droite')))
			{
				$this->echec("Seules les classes fl-gauche ou fl-droite sont autorisées.");
				return $this->rediger($this->input->post('article_id'));
			}
		}

		// Nouvel article
		if ($this->input->post('article_id') == '0')
		{
			$data_gazettes = array(
				'auteur_id' => $this->session->userdata('id'),
				'type'      => Bouzouk::Gazette_Article,
				'resume'    => $this->input->post('resume'),
				'titre'     => $this->input->post('titre'),
				'texte'     => $this->input->post('texte'),
				'image_url' => '',
				'date'      => bdd_datetime(),
				'en_ligne'  => Bouzouk::Gazette_Brouillon
			);

			// Les chefs peuvent ajouter plus de champs
			if ($this->bouzouk->is_journaliste(Bouzouk::Rang_JournalisteChef))
			{
				$data_gazettes['auteur_id'] = $this->input->post('auteur_id');
				$data_gazettes['date']      = $this->input->post('date_annee').'-'.$this->input->post('date_mois').'-'.$this->input->post('date_jour').' '.
											  $this->input->post('date_heures').':'.$this->input->post('date_minutes').':00';
				$data_gazettes['en_ligne']  = $this->input->post('statut');
			}

			$this->db->insert('gazettes', $data_gazettes);
			$article_id = $this->db->insert_id();
		}

		// Modification
		else
		{
			// On va chercher l'article
			$query = $this->db->select('en_ligne, texte, auteur_id')
							  ->from('gazettes')
							  ->where('id', $this->input->post('article_id'))
							  ->get();

			// On regarde si l'article existe
			if ($query->num_rows() == 0)
			{
				$this->echec("Cet article n'existe pas");
				return $this->gerer();
			}

			$article = $query->row();

			$this->load->library('lib_gazette');

			// On vérifie que le mutex est libre ou est vérouillé par le joueur
			$mutex = $this->lib_gazette->get_mutex_article($this->input->post('article_id'));

			if (isset($mutex->id) && $mutex->id != $this->session->userdata('id'))
			{
				$this->echec("Cet article est déjà en cours d'édition par ".profil($mutex->id, $mutex->pseudo, $mutex->rang));
				return $this->rediger($this->input->post('article_id'));
			}

			// Il faut que le journaliste ne soit pas stagiaire, si il est stagiaire et qu'il est l'auteur de l'article il peut le modifier
			if ($article->auteur_id != $this->session->userdata('id') && ! $this->bouzouk->is_journaliste(Bouzouk::Rang_Journaliste))
			{
				$this->echec("Tu n'es qu'un petit stagiaire...tu ne peux pas modifier les articles des autres journalistes, ils ne te font pas confiance...d'ailleurs, ils te regardent de travers en ce moment !");
				return $this->gerer();
			}

			// Si l'article n'est pas un brouillon et que le joueur n'est pas chef
			else if ($article->en_ligne != Bouzouk::Gazette_Brouillon && ! $this->bouzouk->is_journaliste(Bouzouk::Rang_JournalisteChef))
			{
				$this->echec("Tu ne peux plus modifier cet article car ce n'est pas un brouillon, si tu veux le modifier contacte un modérateur ou un administrateur pour qu'il repasse l'article en brouillon");
				return $this->gerer();
			}

			// On enregistre les modifications
			$data_gazettes = array(
				'resume'    => $this->input->post('resume'),
				'titre'     => $this->input->post('titre'),
				'texte'     => $this->input->post('texte'),
			);

			// Les chefs peuvent modifier plus de champs
			if ($this->bouzouk->is_journaliste(Bouzouk::Rang_JournalisteChef))
			{
				$data_gazettes['auteur_id'] = $this->input->post('auteur_id');
				$data_gazettes['date']      = $this->input->post('date_annee').'-'.$this->input->post('date_mois').'-'.$this->input->post('date_jour').' '.
											  $this->input->post('date_heures').':'.$this->input->post('date_minutes').':00';
				$data_gazettes['en_ligne']  = $this->input->post('statut');
			}

			$this->db->where('id', $this->input->post('article_id'))
					 ->update('gazettes', $data_gazettes);

			// On ajoute à l'historique
			$nb_chars = mb_strlen($this->input->post('texte')) - mb_strlen($article->texte);
			$data_historique_gazettes = array(
				'auteur_id'    => $this->session->userdata('id'),
				'article_id'   => $this->input->post('article_id'),
				'date'         => bdd_datetime(),
				'commentaire'  => $this->input->post('commentaire'),
				'modification' => $nb_chars
			);
			$this->db->insert('historique_gazettes', $data_historique_gazettes);

			// On libère le mutex
			$this->lib_gazette->deverrouiller_mutex($this->input->post('article_id'), $this->session->userdata('id'));

			$article_id = $this->input->post('article_id');
		}

		// On regarde si des points d'expérience doivent être distribués
		if ($this->bouzouk->is_journaliste(Bouzouk::Rang_JournalisteChef))
		{
			if ($this->input->post('experience_publication') !== false)
			{
				$gain_xp = $this->bouzouk->config('gain_xp_publication_gazette');
				$this->bouzouk->ajouter_experience($gain_xp, $this->input->post('auteur_id'));

				// On ajoute à l'historique de l'auteur
				$this->bouzouk->historique(64, 65, array(form_prep($this->input->post('titre')), $gain_xp), $this->input->post('auteur_id'));

				// On indique que l'xp a été distribuée pour cet article
				$this->db->set('xp_distribuee', 'xp_distribuee+1', false)
						->where('id', $this->input->post('article_id'))
						->update('gazettes');
			}

			// On regarde si il faut prévenir des joueurs
			if ($this->input->post('prevenir_joueurs') !== false)
			{
				$this->prevenir_joueurs($this->input->post('texte'));
			}
		}

		// Si l'article est publié, on va créer un sujet sur le tobozon et une pige
		if ($this->input->post('statut') == Bouzouk::Gazette_Publie)
		{
			$sujet = '[Article] '.$this->input->post('titre');

			$message  = '[center]'.$this->input->post('resume')."\n\n\n";
			$message .= '[h][url='.site_url('gazette/article_id/'.$article_id)."]Lire l'article[/url][/h]\n";
			$message .= '[color=purple]Postez vos commentaires sur cet article de journal[/color][/center]';

			// On regarde si un topic existe déjà
			$query = $this->db->select('topic_id')
							  ->from('gazettes')
							  ->where('topic_id IS NOT NULL')
							  ->where('id', $article_id)
							  ->get();

			if ($query->num_rows() == 0)
			{
				// On créé le topic		
				$this->load->library('lib_tobozon');
				$topic_id = $this->lib_tobozon->poster_topic(Bouzouk::Robot_MissPoohLett, 'Ella Poolett', $sujet, $message);

				// On sauvegarde le topic_id
				$this->db->set('topic_id', $topic_id)
						 ->where('id', $article_id)
						 ->update('gazettes');
			}

			else
			{
				$topic_id = $query->row()->topic_id;
				
				// On met le le titre à jour
				$this->db->set('subject', $sujet)
						 ->where('id', $topic_id)
						 ->update('tobozon_topics');

				// On récupère l'id du premier post
				$query = $this->db->select('first_post_id')
								  ->from('tobozon_topics')
								  ->where('id', $topic_id)
								  ->get();
				$topic = $query->row();

				// On met le résumé à jour
				$this->db->set('message', $message)
						 ->where('id', $topic->first_post_id)
						 ->update('tobozon_posts');
			}
			
			$data_piges = array(
					'auteur_id' => Bouzouk::Robot_MissPoohLett,
					'texte'     => "Un nouvel article vient d'être publié dans la gazette : « [i]".$this->input->post('titre').'[/i] ».',
					'lien'      => site_url('gazette'),
					'date'      => bdd_datetime(),
					'en_ligne'  => Bouzouk::Piges_Active
				);

			$this->db->insert('piges', $data_piges);
		}
			
		// On affiche un message de confirmation
		if ($this->bouzouk->is_journaliste(Bouzouk::Rang_JournalisteChef))
			$this->succes('Cet article a bien été enregistré');

		else
			$this->succes('Cet article a bien été enregistré comme brouillon, si tu veux le publier contacte un journaliste rédacteur en chef (ou un administrateur)');

		return $this->gerer();
	}

	private function prevenir_joueurs($texte)
	{
		// On cherche tous les liens profil de la news
		if (preg_match_all('#{(.+)\|(\d+)}#Ui', $texte, $joueurs))
		{
			$this->load->library('lib_missive');

			$data_missives  = array();
			$datetime       = bdd_datetime();
			$timbre         = $this->lib_missive->timbres(0);
			$ids_deja_faits = array();
			
			// Pour chaque lien trouvé
			for ($i = 0; $i < count($joueurs[0]); $i++)
			{
				// On récupère id, pseudo
				$id     = $joueurs[2][$i];
				$pseudo = $joueurs[1][$i];

				// On vérifie que le bouzouk existe et est valide
				$joueur_existe = $this->db->where('id', $id)
										  ->where('pseudo', $pseudo)
										  ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))
										  ->count_all_results('joueurs');

				// Si on a une correspondance, on ajoute la missive
				if ($joueur_existe == 1 && ! in_array($id, $ids_deja_faits))
				{
					// On prépare une missive
					$message  = "	Bonjour $pseudo\n\n";
					$message .= "Un informateur anonyme nous a indiqué qu'un journaliste t'a cité dans le journal d'aujourd'hui\n";
					$message .= "<a href='".site_url('gazette')."'>Lire la gazette</a>\n\n";
					$message .= "	Amicalement, Ella Poolett";

					$data_missives[] = array(
						'expediteur_id'   => Bouzouk::Robot_MissPoohLett,
						'destinataire_id' => $id,
						'date_envoi'      => $datetime,
						'timbre'          => $timbre,
						'objet'           => 'Citation gazette',
						'message'         => $message
					);

					$ids_deja_faits[] = $id;
				}
			}

			if (count($data_missives) > 0)
				$this->db->insert_batch('missives', $data_missives);
		}
	}
	
	public function secret()
	{
		return $this->layout->view('gazette/secret');
	}
}