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
 
class Gerer_bot_irc extends MY_Controller
{
	public function index()
	{
		// On regarde l'état du bot
		if (file_exists(FCPATH.'../bot_irc/mypid'))
			$etat = '<span class="vert">Démarré</span>';

		else
			$etat = '<span class="rouge">Arrêté</span>';

		// On va chercher la config
		$query = $this->db->select('cle, valeur, description')
						  ->from('bot_irc')
						  ->get();
		$configs = $query->result();

		// On affiche
		$vars = array(
			'etat' => $etat,
			'configs' => $configs
		);
		return $this->layout->view('staff/gerer_bot_irc', $vars);
	}

	public function demarrer()
	{
		// Si le bot est encore en fonction, on l'arrête et on le relance
		$retour = '';
		if (file_exists(FCPATH.'../bot_irc/mypid'))
		{
			exec('kill '.file_get_contents(FCPATH.'../bot_irc/mypid').' ; rm '.FCPATH.'../bot_irc/mypid ; cd '.FCPATH.'../bot_irc/ ; php run.php > /dev/null &');
			$retour = " Au fait, comme il était déjà lancé, ben je l'ai arrêté pour toi hein...";
		}

		// On lance le bot
		else
		{
			exec('cd '.FCPATH.'../bot_irc/ ; php run.php > /dev/null &');
		}

		// On affiche un message de confirmation
		$this->succes('Le bot IRC a bien été démarré.'.$retour);
		redirect('staff/gerer_bot_irc');
	}

	public function arreter()
	{
		// On arrête le bot
		if (file_exists(FCPATH.'../bot_irc/mypid'))
		{
			$retour = array();
			exec('kill '.file_get_contents(FCPATH.'../bot_irc/mypid').' ; rm '.FCPATH.'../bot_irc/mypid', $retour);

			$retour = implode("\n", $retour);

			if (trim($retour) != '')
			{
				$retour = '<br><span class="pourpre">Réponse serveur</span> : '.$retour;
			}

			// On affiche un message de confirmation
			$this->succes('Le bot IRC a bien été été arrêté.'.$retour);
		}

		else
			$this->echec("Le bot n'est même pas lancé ! Ou alors il est en processus zombie sur le serveur et il faut faire quelque chose...");
			
		redirect('staff/gerer_bot_irc');
	}
	
	public function modifier_config()
	{
		// On va chercher toutes les clés/valeurs de configuration
		$query = $this->db->select('cle, valeur')
						  ->from('bot_irc')
						  ->get();
		$configs = $query->result();

		// On enregistre la nouvelle config
		foreach ($configs as $config)
		{
			if ($this->input->post($config->cle) !== false && $config->valeur != $this->input->post($config->cle))
			{
				$this->db->set('valeur', $this->input->post($config->cle))
						 ->where('cle', $config->cle)
						 ->update('bot_irc');
			}
		}

		// On prévient le bot
		file_put_contents(FCPATH.'../bot_irc/update_config', '1');
		
		// On affiche un message de confirmation
		$this->succes('La configuration du bot IRC a bien été modifiée.');
		return $this->index();
	}
}
