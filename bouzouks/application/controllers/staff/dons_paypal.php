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
 
class Dons_paypal extends MY_Controller
{
	public function index($offset = '0')
	{
		// Pagination
		$nb_dons = $this->db->count_all('dons_paypal');
		$pagination = creer_pagination('staff/dons_paypal/index', $nb_dons, 15, $offset, 4);
		
		// On va chercher toutes les donations
		$query = $this->db->select('j.id, j.pseudo, dp.montant, dp.date, o.nom, dp.struls')
						  ->from('dons_paypal dp')
						  ->join('joueurs j', 'j.id = dp.joueur_id')
						  ->join('objets o', 'o.id = dp.objet_id', 'left')
						  ->order_by('dp.date', 'desc')
						  ->limit($pagination['par_page'], $pagination['offset'])
						  ->get();
		$dons = $query->result();

		// On va chercher le total des dons et le total des struls ainsi que le total des joueurs qui ont donné
		$query = $this->db->select('SUM(montant) AS montant_total, COUNT(DISTINCT joueur_id) AS joueurs_total')
						  ->from('dons_paypal')
						  ->get();
		$dons_paypal = $query->row();

		// Nombre de joueurs au total
		$nb_joueurs = $this->db->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause, Bouzouk::Joueur_GameOver, Bouzouk::Joueur_Banni))
							   ->count_all_results('joueurs');

		$dons_paypal->nb_joueurs = $nb_joueurs;
		$dons_paypal->pourcentage_joueurs = round($dons_paypal->joueurs_total * 100.0 / $dons_paypal->nb_joueurs, 1);
		
		$vars = array(
			'dons'        => $dons,
			'dons_paypal' => $dons_paypal,
			'pagination'  => $pagination['liens']
		);
		return $this->layout->view('staff/dons_paypal', $vars);
	}

	public function ajouter()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur', 'Le joueur', 'required|regex_match[#^.{3,20} \(\d+\) *$#i]');
		$this->form_validation->set_rules('montant', 'Le montant', 'required|is_natural_no_zero');
		
		if ( ! $this->form_validation->run())
		{
			return $this->index();
		}

		// On parse l'id et le pseudo
		preg_match('#^(.{3,20}) \((\d+)\) *$#i', $this->input->post('joueur'), $matches);
		$joueur_id = (int)$matches[2];
		$joueur_pseudo = trim($matches[1]);
		
		// On vérifie que ce joueur existe
		$query = $this->db->select('id')
						  ->from('joueurs')
						  ->where('id', $joueur_id)
						  ->where('pseudo', $joueur_pseudo)
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Ce joueur n'existe pas");
			return $this->index();
		}

		$joueur = $query->row();
		
		$montant = (int) $this->input->post('montant');
		$struls = null;
		$objet_id = null;

		// Fragments de Schnibble Bleuté
		if ($montant == 5)
		{
			// On va chercher les infos du fragment de schnibble			
			$query = $this->db->select('id, nom')
							  ->from('objets')
							  ->where('id', 55)
							  ->get();
			$objet = $query->row();
			$objet_id = $objet->id;

			// On ajoute l'objet au joueur
			$this->bouzouk->ajouter_objets($objet_id, 60, -1, $joueur->id);

			// On ajoute à l'historique du joueur
			$this->bouzouk->historique(32, null, array($this->input->post('montant'), ', tu gagnes 60 <span class="pourpre">'.$objet->nom.'</span>'), $joueur->id, Bouzouk::Historique_Full);
		}

		// Objet rare
		else if ($montant >= 10 && $montant < 20)
		{
			// On trouve un objet aléatoire
			$query = $this->db->select('id, nom')
							  ->from('objets')
							  ->where_in('rarete', 'rare')
							  ->where('disponibilite !=', 'desactive')
							  ->order_by('id', 'random')
							  ->limit(1)
							  ->get();
			$objet = $query->row();
			$objet_id = $objet->id;

			// On ajoute l'objet au joueur
			$this->bouzouk->ajouter_objets($objet->id, 1, 50, $joueur->id);

			// On ajoute à l'historique du joueur
			$this->bouzouk->historique(32, null, array($this->input->post('montant'), ', tu gagnes 1 <span class="pourpre">'.$objet->nom.'</span>'), $joueur->id, Bouzouk::Historique_Full);
		}

		// Objet très rare
		else if ($montant >= 20)
		{
			// On trouve un objet aléatoire
			$query = $this->db->select('id, nom')
							  ->from('objets')
							  ->where_in('rarete', 'tres_rare')
							  ->where('disponibilite !=', 'desactive')
							  ->order_by('id', 'random')
							  ->limit(1)
							  ->get();
			$objet = $query->row();
			$objet_id = $objet->id;

			// On ajoute l'objet au joueur
			$this->bouzouk->ajouter_objets($objet->id, 1, 50, $joueur->id);

			// On ajoute à l'historique du joueur
			$this->bouzouk->historique(32, null, array($this->input->post('montant'), ', tu gagnes 1 <span class="pourpre">'.$objet->nom.'</span>'), $joueur->id, Bouzouk::Historique_Full);
		}

		// Struls
		else
		{
			// On ajoute les struls
			$struls = $this->input->post('montant') * 85;
			$this->bouzouk->ajouter_struls($struls, $joueur->id);

			// On ajoute à l'historique du joueur
			$this->bouzouk->historique(32, null, array($this->input->post('montant'), ', tu gagnes '.struls($struls)), $joueur->id, Bouzouk::Historique_Full);
		}

		// On ajoute le don
		$data_dons_paypal = array(
			'joueur_id' => $joueur->id,
			'montant'   => $this->input->post('montant'),
			'date'      => bdd_datetime(),
			'objet_id'  => $objet_id,
			'struls'    => $struls
		);
		$this->db->insert('dons_paypal', $data_dons_paypal);
		
		// On affiche un message de confirmation
		$this->succes('Le don a bien été enregistré');
		return $this->index();
	}
}
