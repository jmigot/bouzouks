<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions de gestion des notifications
 *
 * Auteur      : Fabien Foixet (fabien@foixet.com)
 * Date        : février 2014
 *
 * Copyright (C) 2012-2014 Fabien Foixet - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Lib_notifications
{
	private $CI;
	private $notifications_defaut = array(
		Bouzouk::Notification_PloukNouvellePartie	=> Bouzouk::Notifications_QuandConnecte,
		Bouzouk::Notification_AnnonceANPC			=> Bouzouk::Notifications_QuandConnecte,
		Bouzouk::Notification_DonMendiant			=> Bouzouk::Notifications_ToutLeTemps,
		Bouzouk::Notification_PseudoPrononceTobozon => Bouzouk::Notifications_ToutLeTemps,
		Bouzouk::Notification_PromoMairie			=> Bouzouk::Notifications_ToutLeTemps,
		Bouzouk::Notification_MissiveJoueur			=> Bouzouk::Notifications_QuandConnecte,
		Bouzouk::Notification_ZlikeTobozon			=> Bouzouk::Notifications_ToutLeTemps,
		Bouzouk::Notification_DonMembreClan			=> Bouzouk::Notifications_QuandConnecte,
		Bouzouk::Notification_NouvelEmploye			=> Bouzouk::Notifications_ToutLeTemps,
		Bouzouk::Notification_QuitterMembreClan		=> Bouzouk::Notifications_ToutLeTemps,
	);
	
	public function __construct()
	{
		$this->CI =& get_instance();
	}
	
	public function notifier($notification, $joueur_id = false)
	{
		if ( ! $joueur_id)
			$joueur_id = $this->CI->session->userdata('id');
		
		// On va chercher les préférences du joueur
		$query = $this->CI->db->select('etat')
						  ->from('notifications')
						  ->where('joueur_id', $joueur_id)
						  ->where('notification_id', $notification)
						  ->get();
		
		if ($query->num_rows() == 0)
			$etat = $this->notifications_defaut[$notification];
		else
			$etat = $query->row()->etat;
		
		
		if ($etat == Bouzouk::Notifications_ToutLeTemps)
			return true;
		elseif ($etat == Bouzouk::Notifications_QuandConnecte && $this->CI->bouzouk->est_connecte($joueur_id))
			return true;
		elseif ($etat == Bouzouk::Notifications_QuandConnecteEtAmi && $this->CI->bouzouk->est_connecte($joueur_id) && $this->CI->bouzouk->sont_amis($joueur_id, $this->CI->session->userdata('id')))
			return true;
		else
			return false;
	}
	
	public function notifier_all($notification, $texte_id_private, $donnees, $joueurs_exclus = false)
	{		
		// On va chercher tous les joueurs ainsi que leur préférence. La requête est fait à la main car AR aime pas IFNULL
		$requete = 'SELECT j.id, IFNULL(n.etat, '.$this->notifications_defaut[$notification].') AS etat '.
				   'FROM joueurs j '.
				   'LEFT JOIN notifications n ON n.joueur_id = j.id AND n.notification_id = '.$notification.' '.
				   'WHERE j.statut IN ('.Bouzouk::Joueur_Actif.', '.Bouzouk::Joueur_Asile.') ';
		
		// Si il y a des joueurs à exclure, on les exclu
		if (is_array($joueurs_exclus))
			$requete .= 'AND j.id NOT IN ('.implode(', ', $joueurs_exclus).')';
		
		$joueurs = $this->CI->db->query($requete)->result();
		
		$data_notifications = array();
		$nombre_notifs = 0;
		
		foreach ($joueurs as $joueur)
		{
			if ($joueur->etat == Bouzouk::Notifications_ToutLeTemps
				|| ($joueur->etat == Bouzouk::Notifications_QuandConnecte && $this->CI->bouzouk->est_connecte($joueur->id))
				|| ($joueur->etat == Bouzouk::Notifications_QuandConnecteEtAmi && $this->CI->bouzouk->est_connecte($joueur->id) && $this->CI->bouzouk->sont_amis($this->CI->session->userdata('id'), $joueur->id)))
			{
				$data_notifications[] = array(
					'joueur_id'        => $joueur->id,
					'texte_id_private' => $texte_id_private,
					'donnees'          => serialize($donnees),
					'notification'     => Bouzouk::Historique_Notification,
					'date'             => bdd_datetime()
				);
				
				$nombre_notifs++;
			}
		}
		
		if ($nombre_notifs > 0)
			return $this->CI->db->insert_batch('historique', $data_notifications);
	}
	
	public function liste_notification($joueur_id = false)
	{		
		if ( ! $joueur_id)
			$joueur_id = $this->CI->session->userdata('id');
		
		$query = $this->CI->db->select('notification_id, etat')
						  ->from('notifications')
						  ->where('joueur_id', $joueur_id)
						  ->get();
		$notifications = $query->result();
		
		$notifs = array();
		foreach ($notifications as $notification)
			$notifs[$notification->notification_id] = $notification->etat;
		
		foreach ($this->notifications_defaut as $notif => $defaut)
		{
			if ( ! isset($notifs[$notif]))
				$notifs[$notif] = $defaut;
		}
		
		return $notifs;
	}
	
	public function modifier_notification($notification_id, $etat, $joueur_id = false)
	{		
		if ( ! $joueur_id)
			$joueur_id = $this->CI->session->userdata('id');
		
		$query = $this->CI->db->select('etat')
						  ->from('notifications')
						  ->where('joueur_id', $joueur_id)
						  ->where('notification_id', $notification_id)
						  ->get();
		
		if ($query->num_rows() == 0)
		{
			$data = array(
					'joueur_id' => $joueur_id,
					'notification_id' => $notification_id,
					'etat' => $etat
				);
			$this->CI->db->insert('notifications', $data); 
		}
		else
		{
			$this->CI->db->where('joueur_id', $joueur_id)
					 ->where('notification_id', $notification_id)
					 ->update('notifications', array('etat' => $etat));
		}
		
		return true;
	}
}