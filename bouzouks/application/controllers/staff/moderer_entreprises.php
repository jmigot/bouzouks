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
 
class Moderer_entreprises extends MY_Controller
{
	public function index()
	{
		// On va chercher les entreprises
		$query = $this->db->select('e.id, e.nom, e.struls, e.chef_id, j.pseudo AS chef_pseudo')
						  ->from('entreprises e')
						  ->join('joueurs j', 'j.id = e.chef_id')
						  ->order_by('e.nom')
						  ->get();
		$entreprises = $query->result();

		// On affiche les résultats
		$vars = array(
			'entreprises'     => $entreprises,
		);
		return $this->layout->view('staff/moderer_entreprises', $vars);
	}
}
