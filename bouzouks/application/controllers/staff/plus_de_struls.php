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
 
class Plus_de_struls extends MY_Controller
{
	public function index($offset = '0')
	{
		// Pagination
		$nb_dons = $this->db->count_all('plus_de_struls');
		$pagination = creer_pagination('staff/plus_de_struls/index', $nb_dons, 25, $offset, 4);
		
		// On va chercher toutes les donations
		$query = $this->db->select('j.id, j.pseudo, p.code, p.montant, p.struls, p.date')
						  ->from('plus_de_struls p')
						  ->join('joueurs j', 'j.id = p.joueur_id')
						  ->order_by('date', 'desc')
						  ->limit($pagination['par_page'], $pagination['offset'])
						  ->get();
		$dons = $query->result();

		// On va chercher les donations regroupées par montant
		$query = $this->db->select('j.id, j.pseudo, COUNT(p.id) AS nb_dons, SUM(p.montant) AS montant_total, SUM(p.struls) AS struls_total')
						  ->from('plus_de_struls p')
						  ->join('joueurs j', 'j.id = p.joueur_id')
						  ->group_by('j.id')
						  ->order_by('montant_total', 'desc')
						  ->limit(30)
						  ->get();
		$dons_groupes = $query->result();

		// On va chercher le total des dons et le total des struls ainsi que le total des joueurs qui ont donné
		$query = $this->db->select('SUM(montant) AS montant_total, SUM(struls) AS struls_total, COUNT(DISTINCT joueur_id) AS joueurs_total')
						  ->from('plus_de_struls')
						  ->get();
		$plus_de_struls = $query->row();

		// Nombre de joueurs au total
		$nb_joueurs = $this->db->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause, Bouzouk::Joueur_GameOver, Bouzouk::Joueur_Banni))
							   ->count_all_results('joueurs');

		$plus_de_struls->nb_joueurs = $nb_joueurs;
		$plus_de_struls->pourcentage_joueurs = round($plus_de_struls->joueurs_total * 100.0 / $plus_de_struls->nb_joueurs, 1);
		
		$vars = array(
			'dons'           => $dons,
			'dons_groupes'   => $dons_groupes,
			'plus_de_struls' => $plus_de_struls,
			'pagination'     => $pagination['liens']
		);
		return $this->layout->view('staff/plus_de_struls', $vars);
	}
}
