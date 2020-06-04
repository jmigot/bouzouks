<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : modération/administration du site
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : juin 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Moderer_irc extends MY_Controller
{
	public function index()
	{
		// On regarde tous les fichiers log proposés
		$fichiers_logs = glob(FCPATH.'../bot_irc/plugins/logs/*');
		$fichiers_logs = array_reverse($fichiers_logs);
		$logs = array();

		foreach ($fichiers_logs as $fichier)
		{
			if (is_dir($fichier))
				continue;

			$tmp = explode('/', $fichier);
			$logs[] = $tmp[count($tmp) - 1];
		}

		// On affiche le résultat
		$vars = array(
			'logs' => $logs
		);
		return $this->layout->view('staff/moderer_irc/index', $vars);
	}

	public function voir_log($log)
	{
		$fichier = FCPATH.'../bot_irc/plugins/logs/html/'.$log;

		// On vérifie que le log existe bien
		if ( ! file_exists($fichier))
		{
			$this->echec("Ce fichier log n'existe pas");
			return $this->index();
		}

		// On récupère le contenu du log
		$contenu = file_get_contents($fichier);

		// On affiche le résultat
		$vars = array(
			'date'    => $log,
			'contenu' => $contenu
		);
		return $this->layout->view('staff/moderer_irc/voir_log', $vars);
	}
}
