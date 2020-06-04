<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : modération/administration du site
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : août 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */
 
class Moderer_clans_tchats extends MY_Controller
{
	public function index()
	{
		// On va chercher les clans
		$query = $this->db->select('c.id, c.nom, c.type, c.chef_id, j.pseudo AS chef_pseudo')
						  ->from('clans c')
						  ->join('joueurs j', 'j.id = c.chef_id')
						  ->order_by('c.type, c.nom')
						  ->get();
		$clans = $query->result();

		// On affiche les résultats
		$vars = array(
			'clans' => $clans,
		);
		return $this->layout->view('staff/moderer_clans_tchats', $vars);
	}
}
