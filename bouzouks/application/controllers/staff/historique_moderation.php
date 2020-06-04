<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : modération/administration du site
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : mai 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */
 
class Historique_moderation extends MY_Controller
{
	public function index($offset = '0')
	{
		$nb_lignes_historique = $this->db->count_all_results('historique_moderation');
		$pagination = creer_pagination('staff/historique_moderation/index', $nb_lignes_historique, 50, $offset, 4);

		// On va chercher l'historique
		$query = $this->db->select('texte, date')
						  ->from('historique_moderation')
						  ->order_by('id', 'desc')
						  ->limit($pagination['par_page'], $pagination['offset'])
						  ->get();
		$historique = $query->result();

		// Affichage
		$vars = array(
			'historique' => $historique,
			'pagination' => $pagination['liens']
		);
		return $this->layout->view('staff/historique_moderation/index', $vars);
	}

	public function tobozon($offset = '0')
	{
		$nb_lignes_historique = $this->db->count_all_results('tobozon_log_moderation');
		$pagination = creer_pagination('staff/historique_moderation/tobozon', $nb_lignes_historique, 50, $offset, 4);

		// On va chercher l'historique
		$query = $this->db->select('tlm.id, tlm.poster_id, j1.pseudo AS poster_pseudo, j1.rang AS poster_rang, tlm.moderateur_id, j2.pseudo AS moderateur_pseudo, j2.rang AS moderateur_rang, tlm.moderateur_texte, tlm.date, tlm.lien_id')
						  ->from('tobozon_log_moderation tlm')
						  ->join('joueurs j1', 'j1.id = tlm.poster_id')
						  ->join('joueurs j2', 'j2.id = tlm.moderateur_id')
						  ->order_by('tlm.id', 'desc')
						  ->limit($pagination['par_page'], $pagination['offset'])
						  ->get();
		$historique = $query->result();

		// Affichage
		$vars = array(
			'historique' => $historique,
			'pagination' => $pagination['liens']
		);
		return $this->layout->view('staff/historique_moderation/tobozon', $vars);
	}

	public function tobozon_details($log_id = null)
	{
		if ( ! isset($log_id))
			show_404();

		$this->load->library('lib_parser');
		
		// On va chercher l'historique
		$query = $this->db->select('tlm.poster_id, j1.pseudo AS poster_pseudo, j1.rang AS poster_rang, tlm.poster_texte, tlm.moderateur_id, j2.pseudo AS moderateur_pseudo, j2.rang AS moderateur_rang, tlm.moderateur_texte, tlm.date, tlm.lien_id')
						  ->from('tobozon_log_moderation tlm')
						  ->join('joueurs j1', 'j1.id = tlm.poster_id')
						  ->join('joueurs j2', 'j2.id = tlm.moderateur_id')
						  ->where('tlm.id', $log_id)
						  ->get();

		if ($query->num_rows() == 0)
			show_404();

		$details = $query->row();

		// Affichage
		$vars = array(
			'details' => $details,
		);
		return $this->layout->view('staff/historique_moderation/tobozon_details', $vars);
	}
}

