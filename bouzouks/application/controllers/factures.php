<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : suivi et paiement des factures du joueur
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs ainsi.
 */

class Factures extends MY_Controller
{
	public function index()
	{
		return $this->lister();
	}

	public function lister()
	{
		$query = $this->db->select('id, titre, montant, date, majoration')
						  ->from('factures')
						  ->where('joueur_id', $this->session->userdata('id'))
						  ->order_by('date')
						  ->get();
		$factures = $query->result();

		// Calcul du total
		$total = 0;

		foreach ($factures as $facture)
			$total += $facture->montant + $facture->majoration;

		// On affiche
		$vars = array(
			'factures' => $factures,
			'total'    => $total
		);
		return $this->layout->view('factures/lister', $vars);
	}

	public function payer()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('facture_id', 'La facture', 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
			return $this->lister();

		// On va chercher les infos de la facture
		$query = $this->db->select('id, titre, montant, majoration')
						  ->from('factures')
						  ->where('joueur_id', $this->session->userdata('id'))
						  ->where('id', $this->input->post('facture_id'))
						  ->get();

		// Si la facture n'existe pas
		if ($query->num_rows() == 0)
		{
			$this->echec("Cette facture n'existe pas");
			return $this->lister();
		}

		$facture = $query->row();
		$facture->montant_total = $facture->montant + $facture->majoration;

		// On regarde si le joueur a assez de struls pour la payer
		if ($this->session->userdata('struls') < $facture->montant_total)
		{
			$this->echec("Tu n'as pas assez de struls pour payer cette facture de ".struls($facture->montant_total));
			return $this->lister();
		}

		// On paye la facture
		$this->bouzouk->retirer_struls($facture->montant_total);

		// On ajoute le montant de la facture à la mairie
		$this->db->set('struls', 'struls+'.$facture->montant_total, false)
				 ->update('mairie');

		// On supprime la facture
		$this->db->where('joueur_id', $this->session->userdata('id'))
				 ->where('id', $this->input->post('facture_id'))
				 ->delete('factures');

		// On ajoute à l'historique
		$this->bouzouk->historique(63, null, array($facture->titre, struls($facture->montant_total)));

		// On affiche un message de confirmation
		$this->succes('Tu as payé une facture <span class="pourpre">'.$facture->titre.'</span> de '.struls($facture->montant_total));
		return $this->lister();
	}

	public function message()
	{
		$nb_factures = $this->db->where('joueur_id', $this->session->userdata('id'))
								->where('majoration > 0')
								->count_all_results('factures');

		// Si le joueur n'a aucune facture impayée
		if ($nb_factures == 0)
			show_404();

		$vars = array(
			'titre_layout' => 'Factures',
			'titre'        => 'Factures impayées !',
			'image_url'    => 'magasins/impaye.png',
			'message'      => 'Tu as des factures non payées depuis plus de '.$this->bouzouk->config('factures_delai_majoration').' jours, tu dois payer toutes tes factures en retard pour pouvoir continuer.<br><a href="'.site_url('factures').'">Payer mes factures</a>'
		);
		return $this->layout->view('blocage', $vars);
	}
}
