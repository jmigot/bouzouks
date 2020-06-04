<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet	  : Bouzouks
 * Description : convocation des joueurs sur un tchat de modération
 *
 * Auteur	  : Fabien Foixet (fabien@foixet.com)
 * Date		: mars 2014
 *
 * Copyright (C) 2012-2014 Fabien Foixet - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Convocation extends MY_Controller
{
	public function index($id_convoc = null)
	{
		//On va chercher les informations de la convoc
		$query = $this->db->select('c.id, c.date, c.etat, jc.id AS convoque_id, jc.pseudo AS convoque_pseudo, jc.rang AS convoque_rang, jm.id AS moderateur_id, jm.pseudo AS moderateur_pseudo, jm.rang AS moderateur_rang')
						  ->from('convocations_moderation c')
						  ->join('joueurs jc', 'jc.id = c.convoque_id')
						  ->join('joueurs jm', 'jm.id = c.moderateur_id')
						  ->where('c.id', (int)$id_convoc)
						  ->get();
		$convocation = $query->row();
		
		if($query->num_rows() == 0 || ($convocation->etat == 0 && ! $this->bouzouk->is_admin()))
			redirect('joueur/index');
		
		// On affiche
		$vars = array(
			'convocation'    => $convocation
		);
		return $this->layout->view('convocation/index', $vars);
	}
}