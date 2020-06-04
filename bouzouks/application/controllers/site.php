<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : parties publiques du site accessibles à tout le monde (même aux non-connectés)
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Site extends MY_Controller
{
	public function index()
	{
		return $this->accueil();
	}

	public function accueil()
	{
		return $this->layout->view('site/accueil');
	}

	public function histoire()
	{
		return $this->layout->view('site/histoire');
	}

	public function creation()
	{
		return $this->layout->view('site/creation');
	}

	public function faq($section = '')
	{
		if ($section == '')
		{
			return $this->layout->view('site/faq');
		}

		$sections_autorisees = array(
			'inscription', 'connexion', 'but_du_jeu',
			'argent', 'stats', 'experience',
			'controuilles', 'embauches', 'entreprises', 'jobs',
			'maire', 'elections', 'taxes', 'mendier', 'lohtoh', 'bonneteau', 'tobozon', 'missives', 'pause', 'asile', 'game_over', 'plouk',
			'maison', 'shops', 'marche_noir',
			'questions_courantes', 'maintenance', 'points_actions', 'clans', 'jeu_de_role'
		);

		if (in_array($section, $sections_autorisees, true) && file_exists(APPPATH.'views/site/faq/'.$section.'.php'))
		{
			return $this->layout->view('site/faq/'.$section.'.php');
		}

		show_404();
	}

	public function lexique()
	{
		return $this->layout->view('site/lexique');
	}

	public function charte()
	{
		return $this->layout->view('site/charte');
	}

	public function team()
	{
		// On récupère les admins
		$query = $this->db->select('id, pseudo, rang, rang_description, perso')
						  ->from('joueurs')
						  ->where_in('id', array(17, 8028))
						  ->get();
		$admins = $query->result();
		
		// On récupère les stars
		$query = $this->db->select('id, pseudo, rang, rang_description, perso')
						  ->from('joueurs')
						  ->where_in('id', array(16))
						  ->get();
		$stars = $query->result();
		
		// On récupère les développeurs
		$query = $this->db->select('id, pseudo, rang, rang_description, perso')
						  ->from('joueurs')
						  ->where_in('id', array(0))
						  ->get();
		$developpeurs = $query->result();

		// On récupère les graphistes
		$query = $this->db->select('id, pseudo, rang, rang_description, perso')
						  ->from('joueurs')
						  ->where_in('id', array(0))
						  ->get();
		$graphistes = $query->result();

		// On récupère les maîtres de jeu
		$query = $this->db->select('id, pseudo, rang, rang_description, perso')
						  ->from('joueurs')
						  ->where('(rang & '.Bouzouk::Rang_MaitreJeu.') > 0')
						  ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Pause, Bouzouk::Joueur_Asile))
						  ->get();
		$mdj = $query->result();

		// On récupère les membres d'honneur
		$query = $this->db->select('id, pseudo, rang, rang_description, perso')
						  ->from('joueurs')
						  ->where_in('id', array(29, 5271))
						  ->get();
		$honneurs = $query->result();

		// On récupère les modérateurs
		$query = $this->db->select('id, pseudo, rang, rang_description, perso')
						  ->from('joueurs')
						  ->where('(rang & '.$this->bouzouk->get_masque(Bouzouk::Masque_Moderateur).') > 0')
						  ->where('(rang & '.Bouzouk::Rang_MaitreJeu.') = 0')
						  ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Pause, Bouzouk::Joueur_Asile))
						  ->get();
		$moderateurs = $query->result();
		
		// On affiche tout ça
		$vars = array(
			'admins'       => $admins,
			'stars'       => $stars,
			'developpeurs' => $developpeurs,
			'graphistes'   => $graphistes,
			'honneurs'     => $honneurs,
			'mdj'          => $mdj,
			'moderateurs'  => $moderateurs,
		);
		return $this->layout->view('site/team', $vars);
	}

	public function journal()
	{
		return $this->layout->view('site/journal');
	}

	public function tchat()
	{
		return $this->layout->view('site/tchat');
	}

	public function error($status_code)
	{
		// On définit le code http dans le header
		set_status_header((int)$status_code);

		// Ici certains codes sont personnalisés car ils reviennent souvent, les autres plus rares utilisent une page générique
		if ($status_code == '404')
			return $this->layout->view('site/errors/error_404');
		
		else if ($status_code == '403')
			return $this->layout->view('site/errors/error_403');

		else
		{
			$vars = array(
				'status_code' => $status_code,
				'message'     => $this->session->userdata('erreur_http')
			);
			$this->session->unset_userdata('erreur_http');
			return $this->layout->view('site/errors/error_general', $vars);
		}
	}

	public function pub()
	{
		return $this->layout->view('site/pub');
	}

	public function mentions_legales()
	{
		return $this->layout->view('site/mentions_legales');
	}
}
