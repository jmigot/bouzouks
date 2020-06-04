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
 
class Moderer_rumeurs extends MY_Controller
{
	public function index($offset = '0')
	{
		// Pagination
		$nb_rumeurs = $this->db->where_not_in('auteur_id', $this->bouzouk->get_robots())
							   ->count_all_results('rumeurs');
		$pagination = creer_pagination('staff/moderer_rumeurs/index', $nb_rumeurs, 10, $offset, 4);

		// On va chercher toutes les rumeurs
		$query = $this->db->select('r.id, r.auteur_id, j.pseudo AS auteur_pseudo, r.texte, r.date, r.statut')
						  ->from('rumeurs r')
						  ->join('joueurs j', 'j.id = r.auteur_id')
						  ->where_not_in('r.auteur_id', $this->bouzouk->get_robots())
						  ->order_by('r.date', 'desc')
						  ->limit($pagination['par_page'], $pagination['offset'])
						  ->get();
		$rumeurs = $query->result();

		$vars = array(
			'rumeurs'    => $rumeurs,
			'offsetpost'	 => $offset,
			'pagination' => $pagination['liens']
		);
		return $this->layout->view('staff/moderer_rumeurs', $vars);
	}

	public function modifier($offset = '0')
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('rumeur_id', 'La rumeur', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('texte', 'Le texte', 'required|min_length[15]|max_length[100]');
		$this->form_validation->set_rules('statut', 'Le statut', 'required|is_natural');

		if ( ! $this->form_validation->run())
		{
			return $this->index();
		}

		// On vérifie que le statut est correct
		if ( ! in_array($this->input->post('statut'), array(Bouzouk::Rumeur_EnAttente, Bouzouk::Rumeur_Refusee, Bouzouk::Rumeur_Validee, Bouzouk::Rumeur_Desactivee)))
		{
			$this->echec("Ce statut n'existe pas");
			return $this->index();
		}

		// On vérifie que la rumeur existe est n'est pas déjà sur ce statut
		$query = $this->db->select('auteur_id')
						  ->from('rumeurs')
						  ->where('id', $this->input->post('rumeur_id'))
						  ->where_not_in('auteur_id', $this->bouzouk->get_robots())
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Cette rumeur n'existe pas");
			return $this->index();
		}

		$rumeur = $query->row();

		// On applique le changement
		$this->db->set('statut', $this->input->post('statut'))
				 ->set('texte', $this->input->post('texte'))
				 ->where('id', $this->input->post('rumeur_id'))
				 ->update('rumeurs');

		// On prévient le joueur
		$this->bouzouk->historique(42, null, array(profil(-1, '', $this->session->userdata('rang'))), $rumeur->auteur_id, Bouzouk::Historique_Full);

		// On ajoute à l'historique modération
		$this->bouzouk->historique_moderation(profil().' a modifié la rumeur <span class="pourpre">'.form_prep($this->input->post('texte')).'</span>');
		
		// On affiche un message de confirmation
		$this->succes('La rumeur a bien été modifiée');
		return $this->index($offset);
	}
}
