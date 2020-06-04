<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : modération/administration du site
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : novembre 2014
 *
 * Copyright (C) 2012-2015 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Gerer_pnj extends MY_Controller
{
	public function index()
	{
		// On va chercher la liste des robots du jeu
		$query = $this->db->select('id, pseudo')
						  ->from('joueurs')
						  ->where('statut', 9)
						  ->get();
		$robots = $query->result();

		// On affiche les résultats
		$vars = array(
			'robots' => $robots
		);
		return $this->layout->view('staff/gerer_pnj', $vars);
	}

	public function modifier_pnj()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('robot_id', "L'id du PNJ", 'required|is_natural_no_zero');
		$this->form_validation->set_rules('nom', 'Le nom', 'required');
		
		if ( ! $this->form_validation->run())
			return $this->index();

		// On modifie le PNJ
		$this->db->set('pseudo', $this->input->post('nom'))
				 ->set('mot_de_passe', $this->input->post('nom'))
				 ->where('id', $this->input->post('robot_id'))
				 ->where('statut', 9)
				 ->update('joueurs');

		if ($this->db->affected_rows() == 1)
		{
			$this->db->set('username', $this->input->post('nom'))
				 	 ->set('password', $this->input->post('nom'))
				 	 ->where('id', $this->input->post('robot_id'))
				 	 ->update('tobozon_users');
		}

		$this->succes('Le PNJ a bien été modifié');
		return $this->index();
	}

	public function modifier_actifs()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('robots_actifs', "Les robots actifs", 'required');
		$this->form_validation->set_rules('robots_inactifs', 'Les robots inactifs', 'required');
		
		if ( ! $this->form_validation->run())
			return $this->index();

		// On récupère les ids correctement
		$robots_actifs = explode(',', $this->input->post('robots_actifs'));
		$robots_inactifs = explode(',', $this->input->post('robots_inactifs'));

		for ($i = 0; $i < count($robots_actifs); $i++)
			$robots_actifs[$i] = (int)trim($robots_actifs[$i]);

		for ($i = 0; $i < count($robots_inactifs); $i++)
			$robots_inactifs[$i] = (int)trim($robots_inactifs[$i]);

		// On vérifie que tous les ids ont été donnés
		$robots_obligatoires = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 19, 20, 21, 24, 25, 27, 28, 31, 37, 38, 39);

		foreach ($robots_obligatoires as $robot_id)
		{
			if ( ! in_array($robot_id, $robots_actifs) && ! in_array($robot_id, $robots_inactifs))
			{
				$this->echec('Tu as supprimé un ou plusieurs robots des listes actifs/inactifs');
				return $this->index();
			}
		}

		// On met à jour la config
		$this->db->set('valeur', implode(', ', $robots_actifs))
				 ->where('cle', 'jeu_robots_actifs')
				 ->update('config');

		$this->db->set('valeur', implode(', ', $robots_inactifs))
				 ->where('cle', 'jeu_robots_inactifs')
				 ->update('config');

		// On recharge le cache
		$this->lib_cache->config(true);
		$this->lib_cache->robots_actifs(true);
		$this->lib_cache->robots_inactifs(true);

		// On affiche un message de confirmation
		$this->succes('Tu as bien modifié la liste des robots actifs/inactifs');
		return $this->index();
	}
}