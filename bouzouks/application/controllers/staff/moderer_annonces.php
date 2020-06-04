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
 
class Moderer_annonces extends MY_Controller
{
	public function index($offset = '0')
	{
		// Pagination
		$nb_annonces = $this->db->count_all('petites_annonces');
		$pagination = creer_pagination('staff/moderer_annonces/index', $nb_annonces, 50, $offset, 4);

		// On récupère les annonces
		$query = $this->db->select('p_e.id, p_e.message, p_e.date_annonce, j.id AS chef_id, j.pseudo AS chef_pseudo, e.nom')
						  ->from('petites_annonces p_e')
						  ->join('entreprises e', 'e.id = p_e.entreprise_id')
						  ->join('joueurs j', 'j.id = e.chef_id')
						  ->order_by('e.nom')
						  ->order_by('p_e.date_annonce', 'desc')
						  ->limit($pagination['par_page'], $pagination['offset'])
						  ->get();
		$annonces = $query->result();

		$vars = array(
			'annonces'   => $annonces,
			'pagination' => $pagination['liens']
		);
		return $this->layout->view('staff/moderer_annonces', $vars);
	}

	public function modifier()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('annonce_id', "L'annonce", 'required|is_natural_no_zero');
		$this->form_validation->set_rules('message', 'Le message', 'required|max_length[250]');

		if ( ! $this->form_validation->run())
		{
			return $this->index();
		}

		// On va chercher les infos de l'annonce
		$query = $this->db->select('pa.message, e.nom, j.id AS chef_id, j.pseudo AS chef_pseudo')
						  ->from('petites_annonces pa')
						  ->join('entreprises e', 'e.id = pa.entreprise_id')
						  ->join('joueurs j', 'j.id = e.chef_id')
						  ->where('pa.id', $this->input->post('annonce_id'))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Cette annonce n'existe pas");
			return $this->index();
		}

		$annonce = $query->row();
		
		if ($this->input->post('modifier') !== false)
		{
			$this->db->set('message', $this->input->post('message'))
					 ->where('id', $this->input->post('annonce_id'))
					 ->update('petites_annonces');

			// On ajoute à l'historique modération
			$this->bouzouk->historique_moderation(profil()." a modifié l'annonce entreprise de ".profil($annonce->chef_id, $annonce->chef_pseudo)." (<span class='pourpre'>".form_prep($annonce->nom)."</span>) : <span class='pourpre'>".form_prep($annonce->message).'</span>');
		
			// On prévient le joueur
			$this->bouzouk->historique(37, null, array(profil(-1, '', $this->session->userdata('rang'))), $annonce->chef_id, Bouzouk::Historique_Full);

			// Message de confirmation
			$this->succes('Le message de cette annonce a bien été modifié');
		}

		else if ($this->input->post('supprimer') !== false)
		{
			$this->db->where('id', $this->input->post('annonce_id'))
					 ->delete('petites_annonces');

			// On ajoute à l'historique modération
			$this->bouzouk->historique_moderation(profil()." a supprimé l'annonce entreprise de ".profil($annonce->chef_id, $annonce->chef_pseudo)." (<span class='pourpre'>".form_prep($annonce->nom)."</span>) : <span class='pourpre'>".form_prep($annonce->message).'</span>');

			// On prévient le joueur
			$this->bouzouk->historique(38, null, array(profil(-1, '', $this->session->userdata('rang'))), $annonce->chef_id, Bouzouk::Historique_Full);

			// Message de confirmation
			$this->succes('Cette annonce a bien été supprimée');
		}

		else
		{
			$this->succes('Cette opération est inconnue');
		}

		return $this->index();
	}
}

