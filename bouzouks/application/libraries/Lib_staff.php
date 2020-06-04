<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions de gestion staff
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : juillet 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Lib_staff
{
	private $CI;
	
	public function __construct()
	{
		$this->CI =& get_instance();
	}
	
	public function nb_alertes()
	{
		$alertes = array();

		// Rumeurs
		if ($this->CI->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurRumeurs))
		{
			$alertes['moderer_rumeurs'] = $this->CI->db->where('statut', Bouzouk::Rumeur_EnAttente)
											  		   ->count_all_results('rumeurs');
		}

		// Clans
		if ($this->CI->bouzouk->is_admin() || $this->CI->bouzouk->is_mdj())
		{
			$alertes['moderer_clans'] = 0;
			
			// Pour chaque type de clan
			foreach (array(Bouzouk::Clans_TypeSyndicat, Bouzouk::Clans_TypePartiPolitique, Bouzouk::Clans_TypeOrganisation) as $type)
			{				
				// On récupère la meilleure enchère s'il y en a une
				$query = $this->CI->db->select('moderee')
								  ->from('clans_encheres')
								  ->where('clan_type', $type)
								  ->order_by('montant_enchere', 'desc')
								  ->limit(1)
								  ->get();

				if ($query->num_rows() == 1)
				{
					$enchere = $query->row();
					if ($enchere->moderee == 0)
						$alertes['moderer_clans']++;
				}
			}
		}

		// Parrainages
		if ($this->CI->bouzouk->is_admin())
		{
			$alertes['moderer_parrainages'] = $this->CI->db->where_in('j.statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile))
														   ->join('joueurs j', 'j.id = p.filleul_id')
														   ->count_all_results('parrainages p');
		}

		// Signalements Tchat Map
		if($this->CI->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats))
		{
			$alertes['moderer_map_tchats'] = $this->CI->db->where('statut', Bouzouk::SignalementsTchatMapAttente)
														  ->count_all_results('vlux_signalements');
		}

		return $alertes;
	}

	public function nb_alertes_total()
	{
		$nb_alertes_total = 0;

		foreach ($this->nb_alertes() as $nb)
			$nb_alertes_total += $nb;

		return $nb_alertes_total;
	}

	public function envoyer_notification_joueurs($texte)
	{
		// On va chercher la liste des joueurs
		$query = $this->CI->db->select('id')
						 	  ->from('joueurs')
						 	  ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile))
						 	  ->get();
		$joueurs = $query->result();
		$data_notifications = array();
		$time = bdd_datetime();
		
		// On envoit la notif à chaque joueur
		foreach ($joueurs as $joueur)
		{
			$data_notifications[] = array(
				'joueur_id'        => $joueur->id,
				'texte_id_private' => 36,
				'donnees'          => serialize(array($texte)),
				'notification'     => Bouzouk::Historique_Notification,
				'date'             => $time
			);
		}
		$this->CI->db->insert_batch('historique', $data_notifications);
	}

	public function lien_admin($titre, $controleur, $methode = null, $alertes = array())
	{
		$url = $controleur;

		if (isset($methode))
			$url .= '/'.$methode;
		
		if ($this->CI->bouzouk->is_staff_autorise($controleur, $methode))
		{
			$lien =  '<li><a href="'.site_url('staff/'.$url).'">'.$titre.'</a>';

			if (isset($alertes[$controleur]) && $alertes[$controleur] > 0)
				$lien .= ' ('.$alertes[$controleur].')';

			$lien .= '</li>';
			return $lien;
		}
		
		return '';
	}
}