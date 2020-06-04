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
 
class Moderer_clans extends MY_Controller
{
	public function index()
	{
		$this->load->library('lib_clans');

		// On récupère les enchères en cours
		$query = $this->db->select('ce.clan_type, ce.parametres, c.nom AS nom_clan, ca.nom AS nom_action, ce.montant_enchere, ce.id, ce.date, ce.annulee, ce.moderee')
						  ->from('clans_encheres ce')
						  ->join('clans_actions ca', 'ca.id = ce.action_id')
						  ->join('clans c', 'c.id = ce.clan_id')
						  ->order_by('montant_enchere', 'desc')
						  ->get();
		$encheres  = $query->result();

		// On trie les enchères selon le type de clan
		$encheres_triees = array(Bouzouk::Clans_TypeSyndicat => array(), Bouzouk::Clans_TypePartiPolitique => array(), Bouzouk::Clans_TypeOrganisation => array());

		foreach ($encheres as $enchere)
		{
			$enchere->parametres = unserialize($enchere->parametres);
			$encheres_triees[$enchere->clan_type][] = $enchere;
		}

		// On récupère les actions lancées
		$query = $this->db->select('ca.nom, ca.effet, caa.date_debut, ca.image, caa.statut, caa.cout, caa.parametres, c.nom AS nom_clan')
						  ->from('clans_actions_lancees caa')
						  ->join('clans_actions ca', 'ca.id = caa.action_id')
						  ->join('clans c', 'c.id = caa.clan_id')
						  ->order_by('caa.date_debut', 'desc')
						  ->limit(50)
						  ->get();
		$actions_lancees = $query->result();

		// On affiche
		$vars = array(
			'encheres'        => $encheres_triees,
			'actions_lancees' => $actions_lancees
		);
		return $this->layout->view('staff/moderer_clans', $vars);
	}

	public function modifier()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('enchere_id', "L'enchère", 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
			return $this->index();

		// On récupère l'enchère
		$query = $this->db->select('parametres')
						  ->from('clans_encheres')
						  ->where('id', $this->input->post('enchere_id'))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Cette enchère n'existe pas");
			return $this->index();
		}

		$enchere = $query->row();
		$enchere->parametres = unserialize($enchere->parametres);

		// Règles de validation suite
		if (isset($enchere->parametres['titre']))
			$this->form_validation->set_rules('titre', 'Le titre', 'required|min_length[3]|max_length[60]');

		if (isset($enchere->parametres['texte']))
			$this->form_validation->set_rules('texte', 'Le texte', 'required|min_length[3]|max_length[10000]');
			
		if ( ! $this->form_validation->run())
			return $this->index();

		// On modifie les paramètres
		if (isset($enchere->parametres['titre']))
			$enchere->parametres['titre'] = $this->input->post('titre');

		if (isset($enchere->parametres['texte']))
			$enchere->parametres['texte'] = $this->input->post('texte');

		// On met le texte à jour ainsi que la modération
		$this->db->set('parametres', serialize($enchere->parametres))
				 ->set('moderee', 1)
				 ->where('id', $this->input->post('enchere_id'))
				 ->update('clans_encheres');

		// On affiche un message de confirmation
		$this->succes('Tu as bien modéré cette action aux enchères');
		return $this->index();
	}
}
