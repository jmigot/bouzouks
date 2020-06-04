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
 
class Moderer_mendiants extends MY_Controller
{
	public function index($offset = '0')
	{
		// On récupère la liste des mendiants
		$query = $this->db->select('j.id, j.pseudo, j.faim, j.sante, j.stress, j.perso, m.argument')
						  ->from('mendiants m')
						  ->join('joueurs j', 'j.id = m.joueur_id')
						  ->order_by('j.pseudo')
						  ->get();
		$mendiants = $query->result();

		// On affiche le résultat
		$vars = array(
			'mendiants' => $mendiants
		);
		return $this->layout->view('staff/moderer_mendiants', $vars);
	}

	public function modifier()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur_id', 'Le mendiant', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('argument', "L'argument", 'required|max_length[250]');

		if ( ! $this->form_validation->run())
		{
			return $this->index();
		}

		if ($this->input->post('modifier') !== false)
		{
			$this->db->set('argument', $this->input->post('argument'))
					 ->where('joueur_id', $this->input->post('joueur_id'))
					 ->update('mendiants');

			// On ajoute à l'historique modération
			$this->bouzouk->historique_moderation(profil()." a modifié le texte mendiant de ".get_profil($this->input->post('joueur_id')));
		
			// On envoit une notification au joueur
			$this->bouzouk->historique(40, null, array(profil(-1, '', $this->session->userdata('rang'))), $this->input->post('joueur_id'), Bouzouk::Historique_Full);

			// Message de confirmation
			$this->succes('Le texte du mendiant a bien été mis à jour');
		}

		else if ($this->input->post('supprimer') !== false)
		{
			$this->db->where('joueur_id', $this->input->post('joueur_id'))
					 ->delete('mendiants');

			// On ajoute à l'historique modération
			$this->bouzouk->historique_moderation(profil().' a supprimé '.get_profil($this->input->post('joueur_id')).' des mendiants');
			
			// On envoit une notification au joueur
			$this->bouzouk->historique(41, null, array(profil(-1, '', $this->session->userdata('rang'))), $this->input->post('joueur_id'), Bouzouk::Historique_Full);

			// Message de confirmation
			$this->succes('Le mendiant a bien été supprimé des mendiants');
		}

		else
		{
			$this->echec('Cette opération est inconnue');
		}

		return $this->index();
	}
}

