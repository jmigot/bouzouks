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

class Gerer_objets extends MY_Controller
{
	public function index()
	{
		// On va chercher les infos de chaque objet
		$query = $this->db->select('id, nom, type, faim, sante, stress, peremption, prix, disponibilite, image_url')
						  ->from('objets')
						  ->where_in('type', array('faim', 'sante', 'stress'))
						  ->order_by('type')
						  ->order_by('nom')
						  ->get();
		$objets_fss = $query->result();

		$query = $this->db->select('id, nom, type, jours_peremption, experience, peremption, prix, disponibilite, quantite_max, image_url')
						  ->from('objets')
						  ->where('type', 'boost')
						  ->order_by('nom')
						  ->get();
		$objets_boost = $query->result();

		// Calcul des rentabilités
		foreach ($objets_fss as $objet)
		{
			$objet->rentabilite = round(($objet->faim + $objet->sante - $objet->stress) / $objet->prix, 2);
		}

		// On compte le nombre d'objets dans le jeu : magasins, maisons, marché noir
		$query = $this->db->select('o.id, o.nom, m.quantite AS quantite_magasins')
						  ->from('objets o')
						  ->join('magasins m', 'm.objet_id = o.id', 'left')
						  ->order_by('o.nom')
						  ->get();
		$quantites_objets = $query->result();

		foreach ($quantites_objets as &$objet)
		{
			// Maisons
			$query = $this->db->select('SUM(quantite) AS quantite_maisons')
							  ->from('maisons')
							  ->where('objet_id', $objet->id)
							  ->get();
			$maisons = $query->row();

			// Marché noir
			$query = $this->db->select('SUM(quantite) AS quantite_marche_noir')
							  ->from('marche_noir')
							  ->where('objet_id', $objet->id)
							  ->get();
			$marche_noir = $query->row();

			// Total
			$objet->quantite_magasins    = (int)$objet->quantite_magasins;
			$objet->quantite_maisons     = (int)$maisons->quantite_maisons;
			$objet->quantite_marche_noir = (int)$marche_noir->quantite_marche_noir;
			$objet->quantite_totale      = $objet->quantite_magasins + $objet->quantite_maisons + $objet->quantite_marche_noir;
		}
		unset($objet);

		// Tri du tableau des quantités
		function cmp_quantites($a, $b)
		{
			if ($a == $b)
				return 0;

			return ($a->quantite_totale > $b->quantite_totale) ? -1 : 1;
		}
		usort($quantites_objets, 'cmp_quantites');

		// On affiche les résultats
		$vars = array(
			'objets_fss'       => $objets_fss,
			'objets_boost'     => $objets_boost,
			'quantites_objets' => $quantites_objets,
		);
		return $this->layout->view('staff/gerer_objets', $vars);
	}

	public function modifier_objet_fss()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('objet_id', "L'objet", 'required|is_natural_no_zero');
		$this->form_validation->set_rules('nom', 'Le nom', 'required|regex_match[#^[^<>]{3,50}$#i]');
		$this->form_validation->set_rules('faim', 'La faim', 'required|regex_match[#^[+-]?[0-9]{1,3}$#]');
		$this->form_validation->set_rules('sante', 'La santé', 'required|regex_match[#^[+-]?[0-9]{1,3}$#]');
		$this->form_validation->set_rules('stress', 'Le stress', 'required|regex_match[#^[+-]?[0-9]{1,3}$#]');
		$this->form_validation->set_rules('prix', 'Le prix', 'required|numeric|greater_than[0]');
		$this->form_validation->set_rules('peremption', 'La péremption', 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
		{
			return $this->index();
		}

		// On vérifie que l'objet existe
		$existe = $this->db->where('id', $this->input->post('objet_id'))
						   ->where_in('type', array('faim', 'sante', 'stress'))
						   ->count_all_results('objets');

		if ( ! $existe)
		{
			$this->echec("Cet objet n'existe pas");
			return $this->index();
		}

		// Mise à jour de l'objet
		$data_objets = array(
			'nom'        => $this->input->post('nom'),
			'faim'       => $this->input->post('faim'),
			'sante'      => $this->input->post('sante'),
			'stress'     => $this->input->post('stress'),
			'prix'       => $this->input->post('prix'),
			'peremption' => $this->input->post('peremption')
		);
		$this->db->where('id', $this->input->post('objet_id'))
				 ->update('objets', $data_objets);

		// On ajoute à l'historique modération
		$this->bouzouk->historique_moderation(profil()." a modifié l'objet <span class='pourpre'>".$this->input->post('nom').'</span>');
		
		// Message de confirmation
		$this->succes("L'objet a bien été modifié");
		return $this->index();
	}

	public function modifier_objet_boost()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('objet_id', "L'objet", 'required|is_natural_no_zero');
		$this->form_validation->set_rules('nom', 'Le nom', 'required|regex_match[#^[^<>]{3,50}$#i]');
		$this->form_validation->set_rules('jours_peremption', 'Les jours de péremption', 'required|regex_match[#^[+-]?[0-9]{1,3}$#]');
		$this->form_validation->set_rules('experience', "L'expérience", 'required|regex_match[#^[+-]?[0-9]{1,3}$#]');
		$this->form_validation->set_rules('quantite_max', 'La quantite max', 'required|is_natural');
		$this->form_validation->set_rules('prix', 'Le prix', 'required|numeric|greater_than[0]');
		$this->form_validation->set_rules('peremption', 'La péremption', 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
		{
			return $this->index();
		}

		// On vérifie que les stats sont valides
		if ($this->input->post('jours_peremption') != 0 AND $this->input->post('experience') != 0)
		{
			$this->echec("Tu ne peux pas mettre de l'expérience et des jours de péremption en même temps, faut choisir dans la vie");
			return $this->index();
		}

		// On vérifie que l'objet existe
		$existe = $this->db->where('id', $this->input->post('objet_id'))
						   ->where('type', 'boost')
						   ->count_all_results('objets');

		if ( ! $existe)
		{
			$this->echec("Cet objet n'existe pas");
			return $this->index();
		}

		// Mise à jour de l'objet
		$data_objets = array(
			'nom'              => $this->input->post('nom'),
			'jours_peremption' => $this->input->post('jours_peremption'),
			'experience'       => $this->input->post('experience'),
			'quantite_max'     => $this->input->post('quantite_max'),
			'prix'             => $this->input->post('prix'),
			'peremption'       => $this->input->post('peremption')
		);
		$this->db->where('id', $this->input->post('objet_id'))
				 ->update('objets', $data_objets);

		// On ajoute à l'historique modération
		$this->bouzouk->historique_moderation(profil()." a modifié l'objet <span class='pourpre'>".$this->input->post('nom').'</span>');
		
		// Message de confirmation
		$this->succes("L'objet a bien été modifié");
		return $this->index();
	}
}