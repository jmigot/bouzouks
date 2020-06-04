<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : modération/administration du site
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : mars 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */
 
class Statistiques extends MY_Controller
{
	public function index()
	{
		return $this->inscriptions();
	}

	public function inscriptions()
	{
		// On va chercher le nombre d'inscriptions par jour
		$query = $this->db->select('date_inscription, count(id) as nb_inscriptions')
						  ->from('joueurs')
						  ->where('date_inscription BETWEEN (NOW() - INTERVAL 30 DAY) AND (NOW() - INTERVAL 1 DAY)')
						  ->group_by('DATE(date_inscription)')
						  ->get();
		$inscriptions = $query->result();
		$donnees_graphique = array();
		
		foreach ($inscriptions as $inscription)
			$donnees_graphique[strtotime($inscription->date_inscription)] = $inscription->nb_inscriptions;

		// On affiche
		$vars = array(
			'donnees_graphique' => json_encode($donnees_graphique)
		);
		return $this->layout->view('staff/statistiques/inscriptions', $vars);
	}

	public function plus_de_struls()
	{
		// On va chercher le nombre d'allopass par jour
		$query = $this->db->select('date, count(id) as nombre')
						  ->from('plus_de_struls')
						  ->where('date BETWEEN (NOW() - INTERVAL 30 DAY) AND (NOW() - INTERVAL 1 DAY)')
						  ->group_by('DATE(date)')
						  ->get();
		$dons = $query->result();
		$donnees_graphique = array();

		foreach ($dons as $don)
			$donnees_graphique[strtotime($don->date)] = $don->nombre;

		// On affiche
		$vars = array(
			'donnees_graphique' => json_encode($donnees_graphique)
		);
		return $this->layout->view('staff/statistiques/plus_de_struls', $vars);
	}
}