<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions de gestion de la gazette
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : février 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Lib_gazette
{
	private $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function verrouiller_mutex($article_id, $joueur_id)
	{
		$this->CI->db->set('mutex_auteur_id', $joueur_id)
					 ->where('id', $article_id)
					 ->update('gazettes');
	}

	public function deverrouiller_mutex($article_id, $joueur_id)
	{
		$this->CI->db->set('mutex_auteur_id', null)
					 ->where('id', $article_id)
					 ->update('gazettes');
	}

	public function get_mutex_article($article_id)
	{
		// On va chercher le seul mutex de l'article
		$query = $this->CI->db->select('j.id, j.pseudo, j.rang, g.titre, g.texte')
							  ->from('gazettes g')
							  ->join('joueurs j', 'j.id = g.mutex_auteur_id', 'left')
							  ->where('g.id', $article_id)
							  ->get();

		return $query->row();
	}

	public function get_mutex_auteur($joueur_id)
	{
		// On va chercher tous les mutex du joueur
		$query = $this->CI->db->select('id, titre')
							  ->from('gazettes')
							  ->where('mutex_auteur_id', $joueur_id)
							  ->get();

		return $query->result();
	}

	public function liberer_mutex_joueur($joueur_id)
	{
		$this->CI->db->set('mutex_auteur_id', null)
					 ->where('mutex_auteur_id', $joueur_id)
					 ->update('gazettes');
	}
}