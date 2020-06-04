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
 
class Moderer_elections extends MY_Controller
{
	public function index()
	{
		// On va chercher tous les candidats
		$query = $this->db->select('j.id, j.pseudo, j.faim, j.sante, j.stress, j.perso, e.texte, e.slogan')
						  ->from('elections e')
						  ->join('joueurs j', 'j.id = e.joueur_id')
						  ->order_by('j.pseudo')
						  ->get();
		$candidats = $query->result();

		// On affiche le résultat
		$vars = array(
			'candidats' => $candidats
		);
		return $this->layout->view('staff/moderer_elections', $vars);
	}

	public function modifier()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('candidat_id', 'Le candidat', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('texte', 'Le texte', 'required|max_length[500]');
		$this->form_validation->set_rules('slogan', 'Le slogan', 'required|min_length[5]|max_length[60]');

		if ( ! $this->form_validation->run())
			return $this->index();

		$this->db->set('texte', $this->input->post('texte'))
				 ->set('slogan', $this->input->post('slogan'))
				 ->where('joueur_id', $this->input->post('candidat_id'))
				 ->update('elections');

		// Message de confirmation
		$this->succes('Le texte du candidat a bien été mis à jour');

		// On prévient le joueur
		$this->bouzouk->historique(39, null, array(profil(-1, '', $this->session->userdata('rang'))), $this->input->post('candidat_id'), Bouzouk::Historique_Full);

		// On ajoute à l'historique modération
		$this->bouzouk->historique_moderation(profil()." a modifié le texte électoral de ".get_profil($this->input->post('candidat_id')));

		return $this->index();
	}
}

