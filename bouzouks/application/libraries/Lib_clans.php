<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions de gestion des clans
 *
 * @author      : Jean-Luc Migot (jluc.migot@gmail.com)
 * @Contributor : Hikingyo
 * @Date        : août 2013
 * @update : Oct 2015
 * Copyright (C) 2012-2015 Team Bouzouks - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Lib_clans
{
	private $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->library('lib_parser');
	}

	/* Fonctions de vérification d'actions en cours */
	public function espionnage_en_cours($clan_id)
	{
		$query = $this->CI->db->select('parametres')
							  ->from('clans_actions_lancees')
							  ->where('clan_id', $clan_id)
							  ->where('action_id', 34)
							  ->where('statut', Bouzouk::Clans_ActionEnCours)
							  ->get();

		$espionnage = ($query->num_rows() == 1) ? $query->row() : null;

		if (isset($espionnage))
		{
			$espionnage->parametres = unserialize($espionnage->parametres);

			// Entreprise
			if ($espionnage->parametres['entreprise_id'] > 0)
			{
				// On vérifie que l'entreprise existe toujours
				$query = $this->CI->db->select('nom')
									  ->from('entreprises')
									  ->where('id', $espionnage->parametres['entreprise_id'])
									  ->get();

				$espionnage->valide = $query->num_rows() == 1;
				
				if ($query->num_rows() == 1)
					$espionnage->entreprise = $query->row();
			}

			// Clan
			else if ($espionnage->parametres['clan_id'] > 0)
			{
				// On vérifie que le clan existe toujours
				$query = $this->CI->db->select('nom')
									  ->from('clans')
									  ->where('id', $espionnage->parametres['clan_id'])
									  ->get();

				$espionnage->valide = $query->num_rows() == 1;
				
				if ($query->num_rows() == 1)
					$espionnage->clan = $query->row();
			}

			// On désactive l'action si elle est invalide
			else
				$espionnage->valide = false;
		}

		return $espionnage;
	}

	public function sabotage_en_cours($clan_id)
	{
		$query = $this->CI->db->select('cal.parametres, c.nom, c.mode_recrutement, cal.date_debut')
							  ->from('clans_actions_lancees cal')
							  ->join('clans c', 'c.id = cal.clan_id')
							  ->where('cal.action_id', 13)
							  ->where('cal.statut', Bouzouk::Clans_ActionEnCours)
							  ->get();

		foreach ($query->result() as $sabotage)
		{
			$sabotage->parametres = unserialize($sabotage->parametres);

			if ($sabotage->parametres['clan_id'] == $clan_id)
				return $sabotage;
		}

		return null;
	}

	public function magouille_fiscale_en_cours()
	{
		$query = $this->CI->db->select('p.joueur_id, c.chef_id')
						 	  ->from('clans_actions_lancees cal')
						 	  ->join('politiciens p', 'p.clan_id = cal.clan_id')
						 	  ->join('clans c', 'c.id = cal.clan_id')
							  ->where('cal.action_id', 27)
							  ->where('cal.statut', Bouzouk::Clans_ActionEnCours)
							  ->get();
		$magouilles_fiscales = ($query->num_rows() > 0) ? $query->result() : null;
		$joueurs_ids = array();

		if (isset($magouilles_fiscales))
		{
			foreach ($magouilles_fiscales as $magouille_fiscale)
			{
				if (isset($magouille_fiscale->joueur_id) && ! in_array($magouille_fiscale->joueur_id, $joueurs_ids))
					$joueurs_ids[] = $magouille_fiscale->joueur_id;

				if (isset($magouille_fiscale->chef_id) && ! in_array($magouille_fiscale->chef_id, $joueurs_ids))
					$joueurs_ids[] = $magouille_fiscale->chef_id;
			}
		}

		return $joueurs_ids;
	}
	
	public function reponse_prise_de_pouvoir($decision, $action)
	{
		// On récupère le maire
		$query = $this->CI->db->select('maire_id')
							  ->from('mairie')
							  ->get();
		$mairie = $query->row();

		// On applique la décision
		if ($decision == 'ceder')
		{
			// On place le chef du parti à la place du maire
			$this->CI->db->set('maire_id', $action->chef_id)
						 ->update('mairie');

			// On insère le nouveau maire dans l'historique
			$data_historique_maires = array(
				'maire_id'   => $action->chef_id,
				'date_debut' => bdd_datetime()
			);
			$this->CI->db->insert('historique_maires', $data_historique_maires);

			// On met à jour la session du maire et celle du chef
			$this->CI->bouzouk->augmente_version_session($mairie->maire_id);
			$this->CI->bouzouk->augmente_version_session($action->chef_id);

			// On envoit une notification au maire et au chef de clan
			$this->CI->bouzouk->notification(200, array(profil($action->chef_id)), $mairie->maire_id);
			$this->CI->bouzouk->notification(201, array(profil($mairie->maire_id)), $action->chef_id);
		}

		else if ($decision == 'sanction')
		{
			// On tire aléatoirement une sanction
			$sanction = mt_rand(0, 4);

			// 5 objets aléatoires supprimés
			if ($sanction == 0)
			{
				for ($i = 0 ; $i < 5 ; $i++)
				{
					$query = $this->CI->db->select('objet_id, peremption')
										  ->from('maisons')
										  ->where('joueur_id', $mairie->maire_id)
										  ->order_by('id', 'random')
										  ->limit(1)
										  ->get();

					if ($query->num_rows() == 1)
					{
						$objet = $query->row();
						$this->CI->bouzouk->retirer_objets($objet->objet_id, 1, $objet->peremption, $mairie->maire_id);
					}
				}

				// On envoit une notification au maire et au chef de clan
				$this->CI->bouzouk->notification(202, array(), $mairie->maire_id);
				$this->CI->bouzouk->notification(203, array(), $action->chef_id);
			}

			// Amende de 10000 struls
			else if ($sanction == 1)
			{
				$montant_facture = 10000;
				
				// On lui ajoute une facture
				$data_factures = array(
					'joueur_id' => $mairie->maire_id,
					'titre'     => '[Prise de pouvoir] Sanction aléatoire',
					'montant'   => $montant_facture,
					'date'      => bdd_datetime()
				);
				$this->CI->db->insert('factures', $data_factures);

				// On envoit une notification au maire et au chef de clan
				$this->CI->bouzouk->notification(204, array(struls($montant_facture)), $mairie->maire_id);
				$this->CI->bouzouk->notification(205, array(struls($montant_facture)), $action->chef_id);
			}

			// Remise à 0 des points d'action
			else if ($sanction == 2)
			{
				$this->CI->db->set('points_action', 0)
							 ->set('force', 0)
							 ->set('charisme', 0)
							 ->set('intelligence', 0)
							 ->where('id', $mairie->maire_id)
							 ->update('joueurs');

				// On envoit une notification au maire et au chef de clan
				$this->CI->bouzouk->notification(206, array(), $mairie->maire_id);
				$this->CI->bouzouk->notification(207, array(), $action->chef_id);

				// On met à jour la session du maire
				$this->CI->bouzouk->augmente_version_session($mairie->maire_id);
			}

			// Envoi à l'asile
			else if ($sanction == 3)
			{
				$this->CI->load->library('lib_joueur');
				$this->CI->lib_joueur->mettre_asile($mairie->maire_id, "Tu as choisi une sanction aléatoire suite à l'action <span class='pourpre'>Prise de pouvoir</span>, tu es donc tombé à l'asile");

				// On envoit une notification au maire et au chef de clan
				$this->CI->bouzouk->notification(208, array(), $mairie->maire_id);
				$this->CI->bouzouk->notification(209, array(), $action->chef_id);
			}

			// Prise de pouvoir réussie quand même
			else
			{
				// On place le chef du parti à la place du maire
				$this->CI->db->set('maire_id', $action->chef_id)
							 ->update('mairie');

				// On insère le nouveau maire dans l'historique
				$data_historique_maires = array(
					'maire_id'   => $action->chef_id,
					'date_debut' => bdd_datetime()
				);
				$this->CI->db->insert('historique_maires', $data_historique_maires);
			
				// On met à jour la session du maire et celle du chef
				$this->CI->bouzouk->augmente_version_session($mairie->maire_id);
				$this->CI->bouzouk->augmente_version_session($action->chef_id);

				// On envoit une notification au maire et au chef de clan
				$this->CI->bouzouk->notification(200, array(profil($action->chef_id)), $mairie->maire_id);
				$this->CI->bouzouk->notification(210, array(), $action->chef_id);
			}
		}

		else if ($decision == 'bouzopolice')
		{
			// On vérifie que le maire a assez de points d'action
			$prix_bouzopolice = ceil($action->parametres['montant_enchere'] * 1.1);

			if ($this->CI->session->userdata('charisme') < $prix_bouzopolice)
			{
				$this->CI->echec("Tu n'as pas assez de charisme pour envoyer la bouzopolice (coût : <span class='pourpre'>$prix_bouzopolice p.a</span>)");
				return false;
			}

			// On retire les points d'action
			$this->CI->db->set('charisme', 'charisme-'.$prix_bouzopolice, false)
						 ->where('id', $mairie->maire_id)
						 ->update('joueurs');

			// On met à jour la session du maire
			$this->CI->bouzouk->augmente_version_session($mairie->maire_id);

			// On envoit une notification au maire et au chef de clan
			$this->CI->bouzouk->notification(211, array(couleur($prix_bouzopolice." points de charisme")), $mairie->maire_id);
			$this->CI->bouzouk->notification(212, array(couleur($prix_bouzopolice." de charisme")), $action->chef_id);
		}

		// L'action est finie, on l'arrête
		$this->CI->db->set('statut', Bouzouk::Clans_ActionTerminee)
					 ->set('jours_restants', 0)
					 ->where('id', $action->id)
					 ->update('clans_actions_lancees');

		return true;
	}

	/* Fonctions de recrutement */
	public function rejoindre_clan($joueur_id, $clan_id, $clan_type, $invisible, $notification_chef = false)
	{
		// On va chercher les infos du joueur
		$query = $this->CI->db->select('id, pseudo, rang')
							  ->from('joueurs')
							  ->where('id', $joueur_id)
							  ->get();
		$joueur = $query->row();

		// On va chercher les infos du clan
		$clan = $this->get_clan($clan_id);

		// On supprime les annonces des autres clans
		$this->CI->db->where('joueur_id', $joueur_id)
					 ->where('refuse', 0)
					 ->where('clan_id IN (SELECT id FROM clans WHERE type = '.$clan_type.')')
					 ->delete('clans_recrutement');

		// On accepte le membre dans le clan
		$data_politiciens = array(
			'joueur_id'   => $joueur_id,
			'clan_id'     => $clan_id,
			'date_entree' => bdd_datetime(),
			'grade'       => Bouzouk::Clans_GradeTest,
			'invisible'   => $invisible,
		);
		$this->CI->db->insert('politiciens', $data_politiciens);

		// La session doit être mise à jour
		$this->CI->bouzouk->augmente_version_session($joueur_id);

		// On ajoute à l'historique du clan
		if ($notification_chef)
			$this->historique(profil($joueur->id, $joueur->pseudo, $joueur->rang).' a rejoint le clan', $clan_id);
		else
			$this->historique(profil(-1).' a accepté '.profil($joueur->id, $joueur->pseudo, $joueur->rang).' dans le clan', $clan_id);

		// On ajoute à l'historique du joueur
		if ($clan->mode_recrutement != Bouzouk::Clans_RecrutementInvisible && $invisible == 0)
			$this->CI->bouzouk->historique(152, 153, array(couleur(form_prep($clan->nom))), $joueur_id);
		else
			$this->CI->bouzouk->historique(152, null, array(couleur(form_prep($clan->nom))), $joueur_id);

		// On envoit une notif au chef ou au joueur
		if ($notification_chef)
			$this->CI->bouzouk->notification(162, array(profil($joueur->id, $joueur->pseudo, $joueur->rang), couleur(form_prep($clan->nom))), $clan->chef_id);

		else if ($clan->mode_recrutement != Bouzouk::Clans_RecrutementInvisible)
			$this->CI->bouzouk->notification(163, array(profil(-1), couleur(form_prep($clan->nom))), $joueur_id);
	}

	public function quitter_syndicat($joueur_id)
	{
		// On regarde s'il était chef d'un syndicat
		$query = $this->CI->db->select('id')
							  ->from('clans')
							  ->where('chef_id', $joueur_id)
							  ->where('type', Bouzouk::Clans_TypeSyndicat)
							  ->get();

		// Si oui on lègue le syndicat
		if ($query->num_rows() == 1)
		{
			$clan = $query->row();
			$this->leguer_clan($clan->id);
		}

		// Sinon on quitte simplement le clan
		else
		{
			$query = $this->CI->db->select('p.clan_id')
								  ->from('politiciens p')
								  ->join('clans c', 'c.id = p.clan_id')
								  ->where('c.type', Bouzouk::Clans_TypeSyndicat)
								  ->where('p.joueur_id', $joueur_id)
								  ->get();

			if ($query->num_rows() == 1)
			{
				$politicien = $query->row();
				$this->quitter_clan($politicien->clan_id, $joueur_id);
			}
		}
	}

	public function quitter_clan($clan_id, $joueur_id)
	{
		$clan = $this->get_clan($clan_id);

		// On ajoute à l'historique du clan
		$this->historique(profil($joueur_id).' a quitté le clan', $clan_id);
		
		// On vérifie si le joueur est en invisible ou pas
		$query = $this->CI->db->select('invisible')
						  ->from('politiciens')
						  ->where('joueur_id', $joueur_id)
						  ->where('clan_id', $clan_id)
						  ->get();
		$politicien = $query->row();
		
		// On quitte le clan
		$this->CI->db->where('clan_id', $clan_id)
				 	 ->where('joueur_id', $joueur_id)
					 ->delete('politiciens');

		// On ajoute à l'historique du joueur
		if ($clan->mode_recrutement != Bouzouk::Clans_RecrutementInvisible && $politicien->invisible == 0)
			$this->CI->bouzouk->historique(154, 155, array(couleur(form_prep($clan->nom))), $joueur_id);
		else
			$this->CI->bouzouk->historique(154, null, array(couleur(form_prep($clan->nom))), $joueur_id);

		$this->CI->load->library('lib_notifications');
		// On envoit une notif au chef
		if ($this->CI->lib_notifications->notifier(Bouzouk::Notification_QuitterMembreClan, $clan->chef_id))
			$this->CI->bouzouk->notification(164, array(profil($joueur_id), couleur(form_prep($clan->nom))), $clan->chef_id);

		// On met à jour la session
		$this->CI->bouzouk->augmente_version_session($joueur_id);
	}

	public function leguer_clan($clan_id, $joueur_id = null)
	{
		// Si joueur_id n'existe pas, on va chercher le membre le plus gradé et le plus ancien (qui n'est pas chef d'un autre clan)
		if ( ! isset($joueur_id))
		{
			$query = $this->CI->db->select('p.joueur_id')
								  ->from('politiciens p')
								  ->join('joueurs j', 'j.id = p.joueur_id')
								  ->join('clans c', 'c.chef_id = j.id', 'left')
							  	  ->where('c.id IS NULL')
								  ->where('j.statut', Bouzouk::Joueur_Actif)
								  ->where('p.clan_id', $clan_id)
								  ->order_by('p.grade', 'desc')
								  ->order_by('p.date_entree')
								  ->get();

			// Si pas de membre pour reprendre le clan, on le supprime
			if ($query->num_rows() == 0)
				return $this->supprimer_clan($clan_id);
			
			else
			{
				$politicien = $query->row();
				$joueur_id = $politicien->joueur_id;
			}
		}

		// On récupère le clan
		$clan = $this->get_clan($clan_id);

		// On récupère les membres du clan
		$query = $this->CI->db->select('joueur_id')
							  ->from('politiciens')
							  ->where('clan_id', $clan_id)
							  ->get();
		$politiciens = $query->result();

		// On envoit une notif
		foreach ($politiciens as $politicien)
			$this->CI->bouzouk->notification(165, array(profil($clan->chef_id), couleur(form_prep($clan->nom)), profil($joueur_id)), $politicien->joueur_id);

		// On supprime le membre des politiciens
		$this->CI->db->where('joueur_id', $joueur_id)
					 ->where('clan_id', $clan_id)
					 ->delete('politiciens');

		// On change le chef
		$this->CI->db->set('chef_id', $joueur_id)
					 ->where('id', $clan_id)
					 ->update('clans');

		// On ajoute à l'historique
		$this->historique(profil($clan->chef_id).' a légué son clan '.couleur(form_prep($clan->nom)).' à '.profil($joueur_id), $clan_id);

		// On regarde si un forum existait
		$query = $this->CI->db->select('id, clan_mode')
							  ->from('tobozon_forums')
							  ->where('clan_id', $clan_id)
							  ->get();

		if ($query->num_rows() > 0)
		{
			$this->CI->load->library('lib_tobozon');
			$forum = $query->row();

			// On récupère les infos du chef
			$query = $this->CI->db->select('id, pseudo')
								  ->from('joueurs')
								  ->where('id', $joueur_id)
								  ->get();
			$chef = $query->row();

			// On met à jour les modérateurs
			$this->CI->lib_tobozon->update_moderateurs_clans($forum->id, $forum->clan_mode, $chef->id, $chef->pseudo);
			
			// Si l'ancien chef n'est pas modérateur, on le change de groupe
			$query = $this->CI->db->select('tu.id')
								  ->from('tobozon_users tu')
								  ->join('tobozon_groups tg', 'tg.g_id = tu.group_id')
								  ->where('tu.id', $clan->chef_id)
								  ->where('(tg.g_moderator = 1 OR tg.g_id = 1)')
								  ->group_by('tu.id')
								  ->get();

			if ($query->num_rows() == 0)
			{
				$this->CI->db->set('group_id', Bouzouk::Tobozon_IdGroupeBouzouks)
							 ->where('id', $clan->chef_id)
							 ->update('tobozon_users');
			}
		}

		// On met à jour la session de l'ancien et du nouveau chef
		$this->CI->bouzouk->augmente_version_session($joueur_id);
		$this->CI->bouzouk->augmente_version_session($clan->chef_id);
	}

	public function supprimer_clan($clan_id)
	{
		// On récupère l'ancien chef
		$query = $this->CI->db->select('c.chef_id, j.pseudo, j.rang, c.nom')
							  ->from('clans c')
							  ->join('joueurs j', 'j.id = c.chef_id')
							  ->where('c.id', $clan_id)
							  ->get();
		$clan = $query->row();

		// On débauche tous les membres du clan
		$query = $this->CI->db->select('joueur_id')
							  ->from('politiciens')
							  ->where('clan_id', $clan_id)
							  ->get();
		$politiciens = $query->result();

		foreach ($politiciens as $politicien)
		{
			// On envoit une notif
			$this->CI->bouzouk->notification(166, array(profil($clan->chef_id, $clan->pseudo, $clan->rang), couleur(form_prep($clan->nom))), $politicien->joueur_id);

			// On supprime le membre
			$this->CI->db->where('joueur_id', $politicien->joueur_id)
						 ->where('clan_id', $clan_id)
						 ->delete('politiciens');
			$this->CI->bouzouk->augmente_version_session($politicien->joueur_id);
		}

		// On supprime les annonces de recrutement
		$this->CI->db->where('clan_id', $clan_id)
					 ->delete('clans_recrutement');

		// On supprime l'historique du clan
		$this->CI->db->where('clan_id', $clan_id)
					 ->delete('historique_clans');

		// On supprime les messages de tchat
		$this->CI->db->where('tchat_id', $clan_id)
					 ->delete('tchats_clans');

		// On supprime les enchères
		$this->CI->db->where('clan_id', $clan_id)
					 ->delete('clans_encheres');

		// On supprime des alliés
		$this->CI->db->where('clan_createur_id', $clan_id)
					 ->or_where('clan_invite_id', $clan_id)
					 ->delete('clans_actions_allies');

		// On supprime les actions lancées
		$this->CI->db->where('clan_id', $clan_id)
					 ->delete('clans_actions_lancees');

		// On supprime le clan
		$this->CI->db->where('id', $clan_id)
					 ->delete('clans');

		// On regarde si un forum existait
		$query = $this->CI->db->select('id')
							  ->from('tobozon_forums')
							  ->where('clan_id', $clan_id)
							  ->get();

		if ($query->num_rows() > 0)
		{
			$this->CI->load->library('lib_tobozon');
			$forum = $query->row();
			$this->CI->lib_tobozon->supprimer_forum($forum->id);
		}

		// On ajoute à l'historique du chef
		$this->CI->bouzouk->historique(156, null, array(couleur(form_prep($clan->nom))), $clan->chef_id);

		// On met à jour la session de l'ancien chef
		$this->CI->bouzouk->augmente_version_session($clan->chef_id);
	}	

	/* Fonctions utiles sur les clans */
	public function historique($texte, $clan_id, $historique_publique = false, $historique_alternatif_public = false)
	{
		// On enregistre dans l'historique du clan
		$data_historique_clans = array(
			'clan_id' => $clan_id,
			'texte'   => $texte,
			'date'    => bdd_datetime()
		);
		$this->CI->db->insert('historique_clans', $data_historique_clans);

		if ($historique_publique)
		{
			// On prévient le chef
			$query = $this->CI->db->select('chef_id')
								  ->from('clans')
								  ->where('id', $clan_id)
								  ->get();
			$clan = $query->row();
			$this->CI->bouzouk->historique(158, null, array($texte), $clan->chef_id, Bouzouk::Historique_Full);

			// On remplace par l'historique public à partir d'ici si besoin
			if ($historique_alternatif_public !== false)
				$texte = $historique_alternatif_public;

			// On prévient tous les membres
			$query = $this->CI->db->select('joueur_id')
								  ->from('politiciens')
								  ->where('clan_id', $clan_id)
								  ->get();
			$politiciens = $query->result();

			foreach ($politiciens as $politicien)
				$this->CI->bouzouk->historique(158, null, array($texte), $politicien->joueur_id, Bouzouk::Historique_Full);
		}
	}

	public function get_clan($clan_id)
	{
		$query = $this->CI->db->select('id, chef_id, nom, type, type_officiel, points_action, entreprise_id, mode_recrutement, grade_lancer_actions')
							  ->from('clans')
							  ->where('id', $clan_id)
							  ->get();

		if ($query->num_rows() == 0)
			return null;

		return $query->row();
	}

	public function get_all_clan(){
		$query = $this->CI->db->select('id, nom, type, type_officiel, points_action, entreprise_id')
							  ->from('clans')
			  				  ->order_by('type')
							  ->get();
		return $query->result();
	}

	public function get_nbr_membres($clan_id){
		$query = $this->CI->db->where('clan_id', $clan_id)
							  ->count_all_results('politiciens');
		return $query;
	}

	public function get_nbr_membres_actifs($clan_id){
		$query = $this->CI->db->where('clan_id', $clan_id)
				 		  ->join('joueurs AS j', 'j.id = politiciens.joueur_id')
				 		  ->where_in('j.statut', array(bouzouk::Joueur_Actif))
				 		  ->count_all_results('politiciens');
		return $query;
	}
	
	public function points_action_disponibles($clan_id, $prendre_en_compte_enchere = false)
	{
		$points_action_disponibles = 0;

		$clan = $this->get_clan($clan_id);
		
		// On récupère les points d'action du clan
		$query = $this->CI->db->select('points_action')
							  ->from('clans')
							  ->where('id', $clan_id)
							  ->get();
		$points_action_disponibles += $query->row()->points_action;

		// Si on prend en compte l'enchère en cours
		if ($prendre_en_compte_enchere)
		{
			$query = $this->CI->db->select('clan_id, montant_enchere')
								  ->from('clans_encheres')
								  ->where('clan_type', $clan->type)
								  ->order_by('montant_enchere', 'desc')
								  ->limit(1)
								  ->get();

			if ($query->num_rows() == 1)
			{
				$enchere = $query->row();

				if ($enchere->clan_id == $clan_id)
					$points_action_disponibles -= $enchere->montant_enchere;
			}
		}

		// Si le clan est allié à une autre action on retranche les points
		$query = $this->CI->db->select('ca.cout_par_allie')
							  ->from('clans_actions_allies caa')
							  ->join('clans_actions ca', 'ca.id = caa.action_id')
							  ->where('caa.clan_invite_id', $clan_id)
							  ->where('caa.statut', Bouzouk::Clans_AllianceAcceptee)
							  ->get();

		if ($query->num_rows() > 0)
			$points_action_disponibles -= $query->row()->cout_par_allie;
		
		return $points_action_disponibles;
	}

	public function retirer_points_action($clan_id, $montant)
	{
		$this->CI->db->set('points_action', 'points_action-'.$montant, false)
					 ->where('id', $clan_id)
					 ->update('clans');
	}

	public function encherir_action($clan_id, $action_id, $montant_enchere, $parametres)
	{
		$clan = $this->get_clan($clan_id);

		// On enchérit
		$data_clans_encheres = array(
			'clan_id'         => $clan_id,
			'action_id'       => $action_id,
			'parametres'      => serialize($parametres),
			'clan_type'       => $clan->type,
			'montant_enchere' => $montant_enchere,
			'date'            => bdd_datetime(),
			'moderee'         => ! isset($parametres['texte']) && ! isset($parametres['titre'])
		);
		$this->CI->db->insert('clans_encheres', $data_clans_encheres);
	}
	
	public function verifier_allie_valide($action, $clan)
	{
		// On vérifie que le clan invité a assez de points à réserver pour l'action
		if ($this->points_action_disponibles($clan->id) < $action->cout_par_allie)
			return false;
		
		// On vérifie que le clan n'est pas en train d'être saboté
		if ($this->sabotage_en_cours($clan->id) != null)
			return false;

		// On vérifie que le clan invité a assez de membres actifs
		$query = $this->CI->db->select('count(c.id) AS nb_membres_actifs')
							  ->from('clans c')
							  ->join('politiciens p', 'p.clan_id = c.id', 'left')
							  ->join('joueurs j', 'j.id = p.joueur_id', 'left')
							  ->where('j.statut', Bouzouk::Joueur_Actif)
							  ->where('c.type', $clan->type)
							  ->where('c.id', $clan->id)
							  ->group_by('c.id')
							  ->get();
		$nb_membres_actifs = $query->row()->nb_membres_actifs;

		if ($nb_membres_actifs < $action->nb_membres_allies_min)
			return false;

		return true;
	}

	public function est_membre_invisible($joueur_id, $clan_id)
	{
		return $this->CI->db->where('joueur_id', $joueur_id)
							->where('clan_id', $clan_id)
							->where('invisible', 1)
							->count_all_results('politiciens') > 0;
	}

	/* Lancement et vérifications d'actions */
	public function fin_des_encheres($clan_type)
	{
		// On va chercher la dernière maintenance
		$derniere_maintenance = $this->CI->db->select('derniere_maintenance')
							  ->from('mairie')
							  ->limit(1)
							  ->get()
							  ->row();
		
		// Si c'est le même jour c'est qu'elle est déjà passée
		$maintenance_passee = date('d') == date('d', strtotime($derniere_maintenance->derniere_maintenance));
		
		// On récupère la meilleure enchère s'il y en a une
		$query = $this->CI->db->select('date')
						  ->from('clans_encheres')
						  ->where('clan_type', $clan_type)
						  ->order_by('montant_enchere', 'desc')
						  ->limit(1)
						  ->get();
		$derniere_action = $query->row();
		
		// Si il est moins de 20h mais que la maintenance est passée ce n'est pas la fin des enchères
		if ((int)date('H') < 20 && $maintenance_passee)
			return false;
		
		// Sinon si il est moins de 22h et qu'il y a eu une enchère et qu'elle a été faite il y a moins de 10 minutes ce n'est pas la fin des enchères
		else if ((int)date('H') < 22 && $query->num_rows() == 1 && strtotime($derniere_action->date) > time() - $this->CI->bouzouk->config('temps_pour_surenchere') * 60)
			return false;
		
		// Sinon c'est la fin des enchères
		return true;
	}
	
	public function heures_annulation($date_enchere)
	{
		$temps_pour_surenchere = $this->CI->bouzouk->config('temps_pour_surenchere');
		
		// On a le droit à 2 heures pour annuler une action
		$heure_debut = strtotime($date_enchere." +".$temps_pour_surenchere." minute");
		$heure_fin = strtotime($date_enchere." +2 hours ".$temps_pour_surenchere." minute");
		
		// Si $heure_debut dépasse 20h alors on renvoit des heures d'annulations en fonction
		if((int)date('H', $heure_debut) >= 20)
		{
			// Si ça dépasse 22h on limite de 22h à 23h59
			if((int)date('H', $heure_debut) >= 22)
				$heures = array("22:00", "23:59");
			else 
				$heures = array(date('H:i', $heure_debut), date('H:i', $heure_fin));
		}
		// Sinon les annnulations sont de 20h à 22h
		else
			$heures = array("20:00", "22:00");
		
		return $heures;
	}
	
	public function action_possible($action, $clan, $nb_membres_clan, $enchere = null, $verifier_allies = true)
	{
		// On regarde si le clan est sous le coup d'un sabotage
		if (($sabotage = $this->sabotage_en_cours($clan->id)) !== null)
		{
			$nom_clan = ($sabotage->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? 'Un clan' : form_prep($sabotage->nom);
			return $nom_clan." a utilisé l'action Sabotage pour bloquer tes actions pendant 4 jours depuis le ".bouzouk_datetime($sabotage->date_debut, 'court');
		}
		
		// Si il n'a pas assez de point pour l'action
		if ($this->points_action_disponibles($clan->id) < $action->cout)
				return 'Il faut '.couleur($action->cout." points d'action").' pour lancer cette action';

		// Membres minimum
		if ($nb_membres_clan < $action->nb_membres_min)
			return 'Il faut '.pluriel($action->nb_membres_min, 'membre').' minimum pour lancer cette action';
		
		// Enchère en cours
		if ($action->effet == Bouzouk::Clans_EffetDiffere)
		{
			// Après 20h et avant la maintenance, on ne peut plus enchérir
			if ($this->fin_des_encheres($clan->type))
				return 'Les enchères ne sont plus disponibles après <span class="pourpre">20h</span>';
			
			// Une autre enchère existe déjà
			if (isset($enchere))
			{
				// Si c'est son enchère
				if ($enchere->clan_id == $clan->id)
					return 'Tu as déjà une enchère en cours';

				$montant_enchere = floor($enchere->montant_enchere * $this->CI->bouzouk->config('clans_coefficient_surenchere'));

				// Si l'enchère est trop haut pour lui
				if ($this->points_action_disponibles($clan->id) < $montant_enchere)
					return 'Il faut '.couleur($montant_enchere." points d'action").' pour enchérir';
			}
		}

		else
		{
			// Pas d'action direct si on est premier aux enchères
			if ($action->effet == Bouzouk::Clans_EffetDirect && isset($enchere) && $enchere->clan_id == $clan->id && ! $enchere->annulee)
				return "Tu ne peux pas lancer d'autre action tant que tu es premier aux enchères";
		}

		// Vérifier si une action est en cours
		$query = $this->CI->db->select('ca.nom, cal.jours_restants, ca.id')
							  ->from('clans_actions_lancees cal')
							  ->join('clans_actions ca', 'ca.id = cal.action_id')
							  ->where('cal.clan_id', $clan->id)
							  ->where('cal.statut', Bouzouk::Clans_ActionEnCours)
							  ->get();

		if ($query->num_rows() > 0)
		{
			$actions_en_cours = $query->result();
			
			foreach ($actions_en_cours as $action_en_cours)
			{
				// Pillage compulsif (Organisation)
				// Concurrence gênante (Organisation)
				// -> pas de limite
				if( ! in_array($action_en_cours->id, array(38, 39)))
				{
					// Action directe : impossible seulement si l'action en cours est la même
					if ($action->effet == Bouzouk::Clans_EffetDirect && $action_en_cours->id == $action->id)
						return "Tu as déjà l'action ".couleur($action_en_cours->nom).' qui est toujours en cours';
				}

				// Action différée : on regarde si c'est le dernier jour, dans ce cas on peut enchérir
				if ($action->effet == Bouzouk::Clans_EffetDiffere && $action_en_cours->jours_restants != 1 && $action_en_cours->id == $action->id)
					return "Tu as déjà l'action ".couleur($action_en_cours->nom)." qui est toujours en cours, tu dois attendre le dernier jour pour pouvoir enchérir";
			}
		}

		// Prise d'otage (Syndicat)
		// -> le patron ne doit pas être candidat aux élections
		if ($action->id == 4)
		{
			// On récupère le patron
			$query = $this->CI->db->select('chef_id')
								  ->from('entreprises')
								  ->where('id', $clan->entreprise_id)
								  ->get();
			$patron_id = $query->row()->chef_id;

			// On regarde si le patron est candidat aux élections
			$existe = $this->CI->db->where('joueur_id', $patron_id)
								   ->count_all_results('elections');

			if ($existe)
				return 'Ton patron est candidat aux élections, tu ne peux pas lancer cette action';

			// On regarde si le patron est maire de la ville
			$maire = $this->CI->db->where('maire_id', $patron_id)
								  ->count_all_results('mairie');

			if ($maire)
				return 'Ton patron est maire de la ville, tu ne peux pas lancer cette action';

			// On regarde si le patron est un joueur actif
			$existe = $this->CI->db->where('id', $patron_id)
								   ->where('statut', Bouzouk::Joueur_Actif)
								   ->count_all_results('joueurs');

			if ( ! $existe)
				return "Ton patron n'est pas un bouzouk actif, il ne peut pas aller à l'asile";
		}

		// Grosse manif syndicale (Syndicat)
		// Grêve générale (Syndicat)
		// Destitution (Parti Politique)
		// Diffamation collective (Parti Politique)
		// -> nombre d'alliés nécessaires
		if ($verifier_allies && in_array($action->id, array(5, 6, 7, 14)))
		{
			$nb_allies = $this->CI->db->where('action_id', $action->id)
									  ->where('clan_createur_id', $clan->id)
									  ->where('statut', Bouzouk::Clans_AllianceAcceptee)
									  ->count_all_results('clans_actions_allies');

			if ($nb_allies < $action->nb_allies_min)
				return 'Tu as '.$nb_allies.'/'.$action->nb_allies_min." alliés ayant accepté l'action";
		}

		// Destitution (Parti Politique)
		if (in_array($action->id, array(7)))
		{
			// On vérifie que le maire est un joueur actif
			$query = $this->CI->db->select('j.id')
								  ->from('mairie m')
								  ->join('joueurs j', 'j.id = m.maire_id')
								  ->where('j.statut', Bouzouk::Joueur_Actif)
								  ->get();

			if ($query->num_rows() == 0)
				return "Le maire n'est pas un bouzouk actif";
		}

		// Distinction électorale (Parti Politique)
		// Elections truquées (Parti politique)
		// Recomptage (Parti politique)
		// Tract électoral (Parti Politique)
		// -> les élections vont commencer
		if (in_array($action->id, array(9, 15, 16, 36)))
		{
			// On va chercher la date de début des élections actuelles
			$mairie = $this->CI->db->select('date_debut_election')
						  ->from('mairie')
						  ->get()
						  ->row();
			$date_actuelle = time();
			$duree_candidatures = $this->CI->bouzouk->config('elections_duree_candidatures');
			
			// Si on est moins qu'un jour avant le tour 1, on ne peut pas lancer les actions
			if ($date_actuelle < strtotime($mairie->date_debut_election.'+'.($duree_candidatures - 1).' DAY'))
				return 'Tu dois attendre la veille des élections pour pouvoir lancer cette action';
		}

		// Distinction électorale (Parti Politique)
		// Elections truquées (Parti politique)
		// Recomptage (Parti politique)
		// Tract électoral (Parti Politique)
		// -> il faut au moins 1 candidat aux élections
		if (in_array($action->id, array(9, 15, 16, 36)))
		{
			$nb_candidats = $this->CI->db->count_all('elections');

			if ($nb_candidats == 0)
				return "Il n'y a encore aucun candidat aux élections";
		}

		// Schnibble traité (SdS)
		// -> il faut au moins 1 membre avec un Schnibble de 3 jours mininmum de péremption
		if ($action->id == 30)
		{
			// On vérifie les membres
			$query = $this->CI->db->select('p.id')
								  ->from('politiciens p')
								  ->join('maisons m', 'm.joueur_id = p.joueur_id')
								  ->where('p.clan_id', $clan->id)
								  ->where('m.objet_id', 18)
								  ->where('(m.peremption >= 3 OR m.peremption = -1)')
								  ->get();

			if ($query->num_rows() == 0)
			{
				// On vérifie le chef
				$existe = $this->CI->db->where('joueur_id', $clan->chef_id)
									   ->where('objet_id', 18)
									   ->where('peremption >= 3')
									   ->count_all_results('maisons');

				if ( ! $existe)
					return "Aucun membre n'a de Schnibble d'au moins 3 jours de péremption dans sa maison";
			}
		}

		return true;
	}

	public function html_action($action_id, $clan_id)
	{
		$retour = '';

		// Soutien salarial (Syndicat)
		// Racket aux pochtrons (Organisation)
		// Fabrique de Gnoulze (Struleone)
		// Misérabilisme (Organisation)
		// Schnibble traité (SdS)
		// Culture de Raki (MLB)
		// -> choisir un membre du clan
		if (in_array($action_id, array(3, 20, 26, 28, 30, 33)))
		{
			// On va chercher le chef du clan
			$query = $this->CI->db->select('j.id, j.pseudo')
								  ->from('clans c')
								  ->join('joueurs j', 'j.id = c.chef_id')
								  ->where('c.id', $clan_id)
								  ->get();
			$chef = $query->row();

			// On va chercher les membres du clan
			$query = $this->CI->db->select('j.id, j.pseudo')
								  ->from('politiciens p')
								  ->join('joueurs j', 'j.id = p.joueur_id')
								  ->where('p.clan_id', $clan_id)
								  ->where('j.statut', Bouzouk::Joueur_Actif)
								  ->order_by('j.pseudo')
								  ->get();
			$membres = $query->result();

			// On construit la liste des membres
			$retour .= 'Choix du membre : <select name="joueur_id"><option value="">-----</option>';

			// Le chef
			if ($action_id == 30)
			{
				$existe = $this->CI->db->where('objet_id', 18)
									   ->where('joueur_id', $chef->id)
									   ->where('(peremption >= 3 OR peremption = -1)')
									   ->count_all_results('maisons');

				if ($existe)
					$retour .= '<option value="'.$chef->id.'">'.$chef->pseudo.'</option>';
			}

			else
				$retour .= '<option value="'.$chef->id.'">'.$chef->pseudo.'</option>';

			foreach ($membres as $membre)
			{
				// Schnibble traité (SdS)
				// -> le membre doit avoir un Schnibble d'au moins 3 jours de péremption dans sa maison
				if ($action_id == 30)
				{
					$existe = $this->CI->db->where('objet_id', 18)
										   ->where('joueur_id', $membre->id)
										   ->where('(peremption >= 3 OR peremption = -1)')
										   ->count_all_results('maisons');

					if ( ! $existe)
						continue;
				}
			
				$retour .= '<option value="'.$membre->id.'">'.$membre->pseudo.'</option>';
			}

			$retour .= '</select><br>';
		}

		// Grosse manif syndicale (Syndicat)
		// Grève générale (Syndicat)
		// Destitution (Parti Politique)
		// Diffamation collective (Parti Politique)
		// -> choix des alliés dans la même catégorie
		if (in_array($action_id, array(5, 6, 7, 14)))
		{
			// On récupère les infos du clan
			$clan_createur = $this->get_clan($clan_id);

			// On récupère le nombre de membres actifs nécessaires pour les alliés de cette action
			$query = $this->CI->db->select('nb_membres_allies_min')
								  ->from('clans_actions')
								  ->where('id', $action_id)
								  ->get();
			$action = $query->row();

			// On va chercher la liste des alliés ayant assez de membres actifs
			$query = $this->CI->db->select('c.id, c.nom')
								  ->from('clans c')
								  ->join('politiciens p', 'p.clan_id = c.id', 'left')
								  ->join('joueurs j', 'j.id = p.joueur_id', 'left')
								  ->where('j.statut', Bouzouk::Joueur_Actif)
								  ->where('c.type', $clan_createur->type)
								  ->where('c.id !=', $clan_id)
								  ->where_in('c.mode_recrutement', array(Bouzouk::Clans_RecrutementOuvert, Bouzouk::Clans_RecrutementFerme))
								  ->group_by('c.id')
								  ->having('count(c.id) >= '.$action->nb_membres_allies_min)
								  ->order_by('c.nom')
								  ->get();
			$clans = $query->result();

			// On construit la liste
			$retour .= 'Ajouter un clan allié : <select name="clan_allie_id" class="action_'.$action_id.'""><option value="">-----</option>';

			foreach ($clans as $clan)
				$retour .= '<option value="'.$clan->id.'">'.form_prep($clan->nom).'</option>';

			$retour .= '</select><br>';
			$retour .= 'Clan caché : <input type="text" name="clan_allie_nom" maxlength="35" size="20" class="action_'.$action_id.'"><br>';
			$retour .= '<input type="button" value="Ajouter" class="action_'.$action_id.'"><br>';

			// Liste des clans ayant été invités
			$query = $this->CI->db->select('c.nom, caa.statut')
								  ->from('clans_actions_allies caa')
								  ->join('clans c', 'c.id = caa.clan_invite_id')
								  ->where('caa.action_id', $action_id)
								  ->where('caa.clan_createur_id', $clan_id)
								  ->where_in('c.mode_recrutement', array(Bouzouk::Clans_RecrutementOuvert, Bouzouk::Clans_RecrutementFerme))
								  ->order_by('c.nom')
								  ->get();
			$allies = $query->result();

			$retour .= '<div class="highlight gauche"><p class="noir">Liste des clans ayant reçu ta proposition :</p>';

			foreach ($allies as $allie)
			{
				$statut = ($allie->statut == Bouzouk::Clans_AllianceAttente) ? ' <span class="gris gras">en attente</span>' : ($allie->statut == Bouzouk::Clans_AllianceAcceptee ? ' <span class="vert gras">a accepté</span>' : ' <span class="rouge gras">a refusé</span>');
				$retour .= '<br> - '.couleur(form_prep($allie->nom)).$statut;
			}

			$retour .= '</div>';
		}

		// Distinction électorale (Parti Politique)
		// Elections truquées (Parti politique)
		// Recomptage (Parti politique)
		// -> choix d'un candidat aux élections
		if (in_array($action_id, array(9, 15, 16)))
		{
			// On récupère le tour actuel
			$query = $this->CI->db->select('tour_election')
								  ->from('mairie')
								  ->get();
			$mairie = $query->row();

			// On va chercher les candidats des élections
			$query = $this->CI->db->select('j.id, j.pseudo')
								  ->from('elections e')
								  ->join('joueurs j', 'j.id = e.joueur_id')
								  ->where('tour', $mairie->tour_election)
								  ->order_by('j.pseudo')
								  ->get();
			$membres = $query->result();

			// On construit la liste
			$retour .= 'Choix du candidat : <select name="joueur_id"><option value="">-----</option>';

			foreach ($membres as $membre)
				$retour .= '<option value="'.$membre->id.'">'.$membre->pseudo.'</option>';

			$retour .= '</select><br>';
		}

		// Propagande (Parti politique)
		// Pillage compulsif (Organisation)
		// -> choix d'un shop
		if (in_array($action_id, array(11, 38)))
		{
			// On construit la liste
			$retour .= 'Choix du shop : <select name="shop"><option value="">-----</option>';
			$retour .= '<option value="faim">Bouffzouk</option>';
			$retour .= '<option value="sante">Indispenzouk</option>';
			$retour .= '<option value="stress">Luxezouk</option>';
			$retour .= '</select><br>';
		}

		// SPAM (Organisation)
		// Tract électoral (Parti politique)
		// Note à la secrétaire (Syndicat)
		// -> choix d'un titre
		if (in_array($action_id, array(35, 36, 37)))
		{
			$retour .= 'Titre<br><input type="text" name="titre_'.$action_id.'" class="compte_caracteres" maxlength="60" size="50">';
			$retour .= '<p id="titre_'.$action_id.'_nb_caracteres_restants" class="transparent">&nbsp;</p><br>';
		}

		// Propagande (Parti Politique)
		// Publication promotionnelle (Organisation)
		// SPAM (Organisation)
		// Internement (CdBM)
		// Censure des mendiants (CdBM)
		// Tag MLBiste (MLB)
		// Tract électoral (Parti politique)
		// Note à la secrétaire (Syndicat)
		// Misérabilisme (Organisation)
		// -> choix d'un texte
		if (in_array($action_id, array(11, 18, 24, 28, 31, 35, 23, 36, 37)))
		{
			$caractere_max = 500;
			
			// Si c'est une actions SPAM (Organisation), Tract électoral (Parti politique) ou Note à la secrétaire (Syndicat) la limite est à 700
			if (in_array($action_id, array(35, 36, 37)))
				$caractere_max = 700;
			
			$retour .= 'Texte<br>'.$this->CI->lib_parser->bbcode('texte_'.$action_id).'<textarea name="texte_'.$action_id.'" id="texte_'.$action_id.'" rows="10" cols="70" class="compte_caracteres" placeholder="Entre ton texte ici" maxlength="'.$caractere_max.'"></textarea>';
			$retour .= '<p id="texte_'.$action_id.'_nb_caracteres_restants" class="transparent">&nbsp;</p><br>';
			$retour .= '<div class="gauche previsualiser_texte_'.$action_id.' margin invisible"><p class="highlight pourpre padding">Prévisualisation du texte</p><p class="texte"></p><p class="hr"></p></div>';
		}

		// Espionnage (Organisation)
		// Sabotage (MLB)
		// -> choix d'une entreprise
		if (in_array($action_id, array(32, 34)))
		{
			// On va chercher les entreprises
			$query = $this->CI->db->select('id, nom')
								  ->from('entreprises')
								  ->order_by('nom')
								  ->get();
			$entreprises = $query->result();

			// On construit la liste
			$retour .= "Choix de l'entreprise : <select name='entreprise_id'><option value=''>-----</option>";

			foreach ($entreprises as $entreprise)
				$retour .= '<option value="'.$entreprise->id.'">'.form_prep($entreprise->nom).'</option>';

			$retour .= '</select><br>';
		}

		// Calomnie (Parti Politique)
		// Sabotage (Parti Politique)
		// Diffamation collective (Parti Politique)
		// Espionnage (Organisation)
		// Tag MLBiste (MLB)
		// -> choix d'un clan adverse de la même catégorie
		// -> choix d'un clan adverse parmi toutes les catégories
		if (in_array($action_id, array(12, 13, 14, 31, 34)))
		{
			// On récupère les infos du clan
			$clan = $this->get_clan($clan_id);

			// On va chercher la liste des clans adverses
			$this->CI->db->select('id, nom')
						 ->from('clans');

			if ( ! in_array($action_id, array(31, 34)))
				$this->CI->db->where('type', $clan->type);

			$query = $this->CI->db->where('id !=', $clan_id)
								  ->where_in('mode_recrutement', array(Bouzouk::Clans_RecrutementOuvert, Bouzouk::Clans_RecrutementFerme))
								  ->order_by('nom')
								  ->get();
			$clans = $query->result();

			// On construit la liste
			$retour .= 'Choix du clan adverse : <select name="clan_id"><option value="">-----</option>';

			foreach ($clans as $clan)
				$retour .= '<option value="'.$clan->id.'">'.form_prep($clan->nom).'</option>';

			$retour .= '</select><br>';
			$retour .= 'Clan caché : <input type="text" name="clan_nom" maxlength="35" size="25"><br><span class="pourpre">L\'action sera validée même si le clan caché n\'existe pas</span><br>';
		}

		// Braquage (Organisation)
		// -> choix d'un objet de shop
		if ($action_id == 17)
		{
			// On va chercher les objets
			$query = $this->CI->db->select('id, nom, type')
								  ->from('objets')
								  ->where('disponibilite', 'entreprise')
								  ->order_by('type')
								  ->order_by('nom')
								  ->get();
			$objets = $query->result();

			// On construit la liste
			$retour .= "Choix de l'objet : <select name='objet_id'><option value=''>-----</option>";

			foreach ($objets as $objet)
				$retour .= '<option value="'.$objet->id.'">'.$objet->nom.' ('.$objet->type.')</option>';

			$retour .= '</select><br>';
		}

		// Censure budgétaire (CdBM)
		// Internement (CdBM)
		// Pillage compulsif (Organisation)
		// Concurrence gênante (Organisation)
		// Malédiction du Schnibble (SDS)
		// -> choix d'un joueur
		if (in_array($action_id, array(22, 23, 38, 39, 40)))
		{
			// On va chercher les joueurs
			$query = $this->CI->db->select('id, pseudo')
								  ->from('joueurs')
								  ->where('statut', Bouzouk::Joueur_Actif)
								  ->order_by('pseudo')
								  ->get();
			$joueurs = $query->result();

			// Internement (CdBM)
			// -> ni le maire, ni le suppléant, ni les candidats du 2e et 3e tour
			if ($action_id == 23)
			{
				$joueurs_interdits = array();

				// On récupère le maire et le suppléant
				$query = $this->CI->db->select('maire_id, maire_suppleant_id, tour_election')
									  ->from('mairie')
									  ->get();
				$mairie = $query->row();
				
				$joueurs_interdits[] = $mairie->maire_id;
				$joueurs_interdits[] = $mairie->maire_suppleant_id;

				// On récupère les candidats 2e et 3e tour
				if ($mairie->tour_election >= Bouzouk::Elections_Tour2)
				{
					$query = $this->CI->db->select('joueur_id')
										  ->from('elections')
										  ->where('tour', $mairie->tour_election)
										  ->get();
					$candidats = $query->result();

					foreach ($candidats as $candidat)
						$joueurs_interdits[] = $candidat->joueur_id;
				}
			}
			
			// Pillage compulsif (Organisation)
			// Concurrence gênante (Organisation)
			// -> ni ceux qui ont moins de 1000 de fortunes, ni ceux qui sont déjà touchés par l'action
			if (in_array($action_id, array(38, 39)))
			{
				$joueurs_interdits = array();
				
				// On recupère la liste des joueurs avec leur struls
				$query = $this->CI->db->select('id, struls')
								  ->from('joueurs')
								  ->get();
				$struls_joueurs = $query->result();
				
				// On recupère la liste des joueurs avec leur valeur d'objets à la maison
				$query = $this->CI->db->select('m.joueur_id, SUM(o.prix * m.quantite) AS prix_total')
								  ->from('maisons m')
								  ->join('objets o', 'o.id = m.objet_id')
								  ->group_by('m.joueur_id')
								  ->get();
				$struls_maison_joueurs = $query->result();
				
				$array_maison = array();
				foreach ($struls_maison_joueurs as $struls_maison_joueur)
					$array_maison[$struls_maison_joueur->joueur_id] = $struls_maison_joueur->prix_total;
				
				// On recupère la liste des joueurs avec leur valeur d'objets au marché noir
				$query = $this->CI->db->select('m_n.joueur_id, SUM(o.prix * m_n.quantite) AS prix_total')
								  ->from('marche_noir m_n')
								  ->join('objets o', 'o.id = m_n.objet_id')
								  ->group_by('m_n.joueur_id')
								  ->get();
				$struls_marche_noir_joueurs = $query->result();
				
				$array_marche_noir = array();
				foreach ($struls_marche_noir_joueurs as $struls_marche_noir_joueur)
					$array_marche_noir[$struls_marche_noir_joueur->joueur_id] = $struls_marche_noir_joueur->prix_total;
				
				// ---------- Hook clans ----------
				// Magouille fiscale (Struleone)
				$query = $this->CI->db->select('clan_id')
									  ->from('clans_actions_lancees')
									  ->where('action_id', 27)
									  ->where('statut', Bouzouk::Clans_ActionEnCours)
									  ->get();
				$magouille_fiscale = ($query->num_rows() > 0);
				
				// Si il y a bien une magouille fiscale en cours, on en récupère la liste
				if ($magouille_fiscale)
				{
					$id_clan_magouille_fiscale = $query->row()->clan_id;
					
					$joueurs_magouille_fiscale = array();
					
					// On récupère le chef
					$query = $this->CI->db->select('chef_id')
										  ->from('clans')
										  ->where('id', $id_clan_magouille_fiscale)
										  ->get();
					$joueurs_magouille_fiscale[] = $query->row()->chef_id;
					
					// On récupère les membres
					$query = $this->CI->db->select('joueur_id')
										  ->from('politiciens')
										  ->where('clan_id', $id_clan_magouille_fiscale)
										  ->get();					
					foreach ($query->result() as $membre_magouille_fiscale)
						$joueurs_magouille_fiscale[] = $membre_magouille_fiscale->joueur_id;
				}
				
				foreach ($struls_joueurs as $struls_joueur)
				{
					$fortune_total = $struls_joueur->struls;
					if (isset($array_maison[$struls_joueur->id]))
						$fortune_total += $array_maison[$struls_joueur->id];
					if (isset($array_marche_noir[$struls_joueur->id]))
						$fortune_total += $array_marche_noir[$struls_joueur->id];
					
					if ($fortune_total < 1000 || ($magouille_fiscale && in_array($struls_joueur->id, $joueurs_magouille_fiscale)))
						$joueurs_interdits[] = $struls_joueur->id;
				}
				
				// On récupère ceux déjà touché par l'action
				$query = $this->CI->db->select('parametres')
								  ->from('clans_actions_lancees')
								  ->where('statut', Bouzouk::Clans_ActionEnCours)
								  ->where('action_id', $action_id)
								  ->get();

				if ($query->num_rows() > 0)
				{
					$actions_en_cours = $query->result();
			
					foreach ($actions_en_cours as $action_en_cours)
					{
						$action_en_cours = unserialize($action_en_cours->parametres);
						$joueurs_interdits[] = $action_en_cours['joueur_id'];
					}
				}
			}

			// On construit la liste
			$retour .= 'Choix du bouzouk : <select name="joueur_id"><option value="">-----</option>';

			foreach ($joueurs as $joueur)
			{
				// Internement, Pillage compulsif & Concurrence gênante : liste de bouzouks interdits
				if (in_array($action_id, array(23, 38, 39)) && in_array($joueur->id, $joueurs_interdits))
					continue;

				$retour .= '<option value="'.$joueur->id.'">'.$joueur->pseudo.'</option>';
			}

			$retour .= '</select><br>';
		}
		
		// Recrutement d'alliéné (SDS)
		// -> choix d'un joueur à l'asile
		if (in_array($action_id, array(41)))
		{
			$joueurs_interdits = array();
			
			// On va chercher les joueurs
			$query = $this->CI->db->select('id, pseudo')
								  ->from('joueurs')
								  ->where('statut', Bouzouk::Joueur_Asile)
								  ->where('statut_staff_id', null)
								  ->order_by('pseudo')
								  ->get();
			$joueurs = $query->result();
			
			// On récupère ceux déjà touché par l'action
			$query = $this->CI->db->select('parametres')
							  ->from('clans_actions_lancees')
							  ->where('statut', Bouzouk::Clans_ActionEnCours)
							  ->where('action_id', $action_id)
							  ->get();

			if ($query->num_rows() > 0)
			{
				$actions_en_cours = $query->result();
			
				foreach ($actions_en_cours as $action_en_cours)
				{
					$action_en_cours = unserialize($action_en_cours->parametres);
					$joueurs_interdits[] = $action_en_cours['joueur_id'];
				}
			}
			
			// On construit la liste
			$retour .= "Choix de l'aliéné ".': <select name="joueur_id"><option value="">-----</option>';

			foreach ($joueurs as $joueur)
				$retour .= '<option value="'.$joueur->id.'">'.$joueur->pseudo.'</option>';

			$retour .= '</select><br>';
		}

		// Censure budgétaire (CdBM)
		// -> choix du pourcentage
		if ($action_id == 22)
		{
			// On construit la liste
			$retour .= 'Choix du pourcentage : <select name="pourcentage"><option value="">-----</option>';

			for ($i = 1 ; $i <= 10 ; $i++)
				$retour .= '<option value="'.$i.'">'.$i.' %</option>';
			
			$retour .= '</select><br>';
		}

		// Sainte Brigade (SdS)
		// -> choix des objets à bloquer
		if ($action_id == 29)
		{
			// On construit la liste
			$retour .= 'Choix des objets à bloquer :<br>';
			$retour .= '<input type="checkbox" name="bibles" id="bibles"><label for="bibles">Bibles du Schnibble</label><br>';
			$retour .= '<input type="checkbox" name="schnibbles" id="schnibbles"><label for="schnibbles">Schnibbles</label><br>';
		}

		// SPAM (Organisation)
		// -> choix des 100 bouzouks à spammer
		if ($action_id == 35)
		{
			// On construit la liste
			$retour .= 'Choix des bouzouks : <select name="bouzouks_type"><option value="">-----</option>';
			$retour .= '<option value="derniers_inscrits">Derniers inscrits</option>';
			$retour .= '<option value="plus_riches">Plus riches</option>';
			$retour .= '<option value="moins_riches">Moins riches</option>';
			$retour .= '<option value="aleatoires">Aléatoires</option>';
			$retour .= '<option value="sans_clan">Sans clan</option>';
			$retour .= '<option value="sans_syndicat">Sans syndicat</option>';
			$retour .= '<option value="sans_parti_politique">Sans parti politique</option>';
			$retour .= '<option value="sans_organisation">Sans organisation</option>';
			$retour .= '<option value="males">Mâles</option>';
			$retour .= '<option value="femelles">Femelles</option>';
			$retour .= '<option value="petites_trompes">Plus petite trompe</option>';
			$retour .= '<option value="grandes_trompes">Plus grande trompe</option>';
			$retour .= '<option value="chomeurs">Chômeurs</option>';
			$retour .= '</select><br>';
		}
		
		// Tract électoral (Parti politique)
		// -> choix des 100 bouzouks à spammer
		if ($action_id == 36)
		{
			// On construit la liste
			$retour .= 'Choix des bouzouks : <select name="bouzouks_type"><option value="">-----</option>';
			$retour .= '<option value="pas_vote">Pas encore voté</option>';
			$retour .= '</select><br>';
		}
		
		// Note à la secrétaire (Syndicat)
		// -> choix des bouzouks à missiver
		if ($action_id == 37)
		{
			// On construit la liste
			$retour .= 'Choix des bouzouks : <select name="bouzouks_type"><option value="">-----</option>';
			$retour .= '<option value="chefs_syndicats">Chefs des syndicats</option>';
			$retour .= '<option value="membres_syndicat">Membres du syndicat</option>';
			$retour .= '<option value="employes_non_syndiques">Employés non syndiqués de la boîte</option>';
			$retour .= '<option value="tous_employes">Tous les employés de la boîte</option>';
			$retour .= '</select><br>';
		}

		return $retour;
	}

	public function get_action_post_parametres($action_id, $clan_id)
	{
		$clan = $this->get_clan($clan_id);
		$parametres = array();

		// Soutien salarial (Syndicat)
		// Racket aux pochtrons (Organisation)
		// Fabrique de Gnoulze (Struleone)
		// Misérabilisme (Organisation)
		// Schnibble traité (SdS)
		// Culture de Raki (MLB)
		// -> choisir un membre du clan
		if (in_array($action_id, array(3, 20, 26, 28, 30, 33)))
		{
			$this->CI->form_validation->set_rules('joueur_id', 'Le bouzouk', 'required|is_natural_no_zero');
			
			if ( ! $this->CI->form_validation->run())
				return false;

			// On vérifie que le membre est un joueur actif
			$actif = $this->CI->db->where('id', $this->CI->input->post('joueur_id'))
								  ->where('statut', Bouzouk::Joueur_Actif)
								  ->count_all_results('joueurs');

			if ( ! $actif)
			{
				$this->CI->echec("Ce bouzouk n'est pas un joueur actif");
				return false;
			}

			// On vérifie que le joueur est chef ou membre du clan
			$existe = $this->CI->db->where('chef_id', $this->CI->input->post('joueur_id'))
								   ->where('id', $clan->id)
								   ->count_all_results('clans');

			if ( ! $existe)
			{
				$existe = $this->CI->db->where('joueur_id', $this->CI->input->post('joueur_id'))
									   ->where('clan_id', $clan->id)
									   ->count_all_results('politiciens');
			}

			if ( ! $existe)
			{
				$this->CI->echec("Ce bouzouk n'est pas membre de ton clan");
				return false;
			}

			// Schnibble traité (SdS)
			// -> le membre doit avoir un Schnibble d'au moins 3 jours de péremption dans sa maison
			if ($action_id == 30)
			{
				$existe = $this->CI->db->where('objet_id', 18)
									   ->where('joueur_id', $this->CI->input->post('joueur_id'))
									   ->where('(peremption >= 3 OR peremption = -1)')
									   ->count_all_results('maisons');

				if ( ! $existe)
				{
					$this->CI->echec("Ce bouzouk n'a pas de Schnibble d'au moins 3 jours de péremption dans sa maison");
					return false;
				}
			}

			// On regarde si le membre est invisible
			$parametres['joueur_id'] = $this->CI->input->post('joueur_id');
			$parametres['joueur_invisible'] = $this->CI->db->where('joueur_id', $parametres['joueur_id'])
														   ->where('clan_id', $clan->id)
														   ->where('invisible', 1)
														   ->count_all_results('politiciens');
		}

		// Distinction électorale (Parti Politique)
		// Elections truquées (Parti politique)
		// Recomptage (Parti politique)
		// -> choix d'un candidat aux élections
		if (in_array($action_id, array(9, 15, 16)))
		{
			$this->CI->form_validation->set_rules('joueur_id', 'Le bouzouk', 'required|is_natural_no_zero');
			
			if ( ! $this->CI->form_validation->run())
				return false;

			// On récupère le tour actuel
			$query = $this->CI->db->select('tour_election')
								  ->from('mairie')
								  ->get();
			$mairie = $query->row();

			// On vérifie que le joueur est candidat aux élections
			$existe = $this->CI->db->where('joueur_id', $this->CI->input->post('joueur_id'))
								   ->where('tour', $mairie->tour_election)
								   ->count_all_results('elections');

			if ( ! $existe)
			{
				$this->CI->echec("Ce bouzouk n'est pas candidat aux élections");
				return false;
			}

			$parametres['joueur_id'] = $this->CI->input->post('joueur_id');
		}

		// Propagande (Parti politique)
		// Pillage compulsif (Organisation)
		// -> choix d'un shop
		if (in_array($action_id, array(11, 38)))
		{
			$this->CI->form_validation->set_rules('shop', 'Le shop', 'required|regex_match[#^faim|sante|stress$#]');
			
			if ( ! $this->CI->form_validation->run())
				return false;

			$parametres['shop'] = $this->CI->input->post('shop');
		}

		// SPAM (Organisation)
		// Tract électoral (Parti politique)
		// Note à la secrétaire (Syndicat)
		// -> choix d'un titre
		if (in_array($action_id, array(35, 36, 37)))
		{
			$this->CI->form_validation->set_rules('titre_'.$action_id, 'Le titre', 'required|min_length[3]|max_length[60]');
			
			if ( ! $this->CI->form_validation->run())
				return false;

			$parametres['titre'] = $this->CI->input->post('titre_'.$action_id);
		}

		// Propagande (Parti Politique)
		// Publication promotionnelle (Organisation)
		// SPAM (Organisation)
		// Internement (CdBM)
		// Censure des mendiants (CdBM)
		// Tag MLBiste (MLB)
		// Tract électoral (Parti politique)
		// Note à la secrétaire (Syndicat)
		// Misérabilisme (Organisation)
		// -> choix d'un texte
		if (in_array($action_id, array(11, 18, 24, 28, 31, 35, 23, 36, 37)))
		{
			$caractere_max = 500;
			
			// Si c'est une actions SPAM (Organisation), Tract électoral (Parti politique) ou Note à la secrétaire (Syndicat) la limite est à 700
			if (in_array($action_id, array(35, 36, 37)))
				$caractere_max = 700;
			
			$this->CI->form_validation->set_rules('texte_'.$action_id, 'Le texte', 'required|min_length[3]|max_length['.$caractere_max.']');
			
			if ( ! $this->CI->form_validation->run())
				return false;

			$parametres['texte'] = $this->CI->input->post('texte_'.$action_id);
		}

		// Espionnage (Organisation)
		// -> on vérifie qu'on a soit une entreprise, soit un clan
		if ($action_id == 34)
		{
			$entreprise_cible = ctype_digit($this->CI->input->post('entreprise_id'));
			$clan_cible = ctype_digit($this->CI->input->post('clan_id')) || $this->CI->input->post('clan_nom') != '';

			if (($entreprise_cible && $clan_cible) || ( ! $entreprise_cible && ! $clan_cible))
			{
				$this->CI->echec('Il faut choisir soit un clan soit une entreprise');
				return false;
			}
		}

		// Calomnie (Parti Politique)
		// Sabotage (Parti Politique)
		// Diffamation collective (Parti Politique)
		// Espionnage (Organisation)
		// Tag MLBiste (MLB)
		// -> choix d'un clan adverse de la même catégorie
		// -> choix d'un clan adverse parmi toutes les catégories
		if (in_array($action_id, array(12, 13, 14, 31, 34)))
		{
			$clans_toutes_categories = array(31, 34);

			$this->CI->form_validation->set_rules('clan_id', 'Le clan', 'is_natural_no_zero');
			$this->CI->form_validation->set_rules('clan_nom', 'Le clan', 'min_length[3]|max_length[35]');
			
			if ( ! $this->CI->form_validation->run())
				return false;

			$input_clan_id = 0;

			// Si un clan est donné par id
			if (ctype_digit($this->CI->input->post('clan_id')) && $this->CI->input->post('clan_nom') == '')
			{
				$this->CI->db->where('id', $this->CI->input->post('clan_id'))
							 ->where('id !=', $clan->id)
							 ->where_in('mode_recrutement', array(Bouzouk::Clans_RecrutementOuvert, Bouzouk::Clans_RecrutementFerme));

				if ( ! in_array($action_id, $clans_toutes_categories))
					$this->CI->db->where('type', $clan->type);

				$existe = $this->CI->db->count_all_results('clans');

				if ( ! $existe)
				{
					$this->CI->echec("Ce clan est invalide");
					return false;
				}

				$input_clan_id = $this->CI->input->post('clan_id');
			}

			// Si un clan est donné par nom
			else if ( ! ctype_digit($this->CI->input->post('clan_id')) && $this->CI->input->post('clan_nom') != '')
			{
				$this->CI->db->select('id')
							 ->from('clans')
							 ->where('nom', $this->CI->input->post('clan_nom'))
							 ->where('id !=', $clan->id)
							 ->where('mode_recrutement', Bouzouk::Clans_RecrutementInvisible);

				if ( ! in_array($action_id, $clans_toutes_categories))
					$this->CI->db->where('type', $clan->type);

				$query = $this->CI->db->get();

				if ($query->num_rows() == 1)
					$input_clan_id = $query->row()->id;

				else
					$input_clan_id = 0;
			}

			// Aucun des deux
			// Espionnage (Organisation)
			// Tag MLBiste (MLB)
			// -> pas obligé de choisir un clan
			else if ( ! in_array($action_id, array(34, 31)))
			{
				$this->CI->echec("Il faut choisir un clan : soit un dans la liste soit écrire le nom d'un clan caché");
				return false;
			}

			$parametres['clan_id'] = $input_clan_id;
			$parametres['clan_nom'] = $this->CI->input->post('clan_nom') != '' ? $this->CI->input->post('clan_nom') : null;
		}

		// Espionnage (Organisation)
		// Sabotage (MLB)
		// -> choix d'une entreprise
		if (in_array($action_id, array(32, 34)))
		{
			// Espionnage : entreprise pas obligatoire
			if ($action_id == 34)
				$this->CI->form_validation->set_rules('entreprise_id', "L'entreprise", 'is_natural_no_zero');
	
			else
				$this->CI->form_validation->set_rules('entreprise_id', "L'entreprise", 'required|is_natural_no_zero');
			
			if ( ! $this->CI->form_validation->run())
				return false;
			
			// On vérifie que l'entreprise existe
			if ($action_id != 34 || ctype_digit($this->CI->input->post('entreprise_id')))
			{
				$existe = $this->CI->db->where('id', $this->CI->input->post('entreprise_id'))
									   ->count_all_results('entreprises');

				if ( ! $existe)
				{
					$this->CI->echec("Cette entreprise n'existe pas");
					return false;
				}
			}

			$parametres['entreprise_id'] = $this->CI->input->post('entreprise_id');
		}

		// Braquage (Organisation)
		// -> choix d'un objet de shop
		if ($action_id == 17)
		{
			$this->CI->form_validation->set_rules('objet_id', "L'objet shop", 'required|is_natural_no_zero');
	
			if ( ! $this->CI->form_validation->run())
				return false;
			
			// On vérifie que l'objet existe
			$existe = $this->CI->db->where('id', $this->CI->input->post('objet_id'))
								   ->where('disponibilite', 'entreprise')
								   ->count_all_results('objets');

			if ( ! $existe)
			{
				$this->CI->echec("Cet objet shop n'existe pas");
				return false;
			}
			
			$parametres['objet_id'] = $this->CI->input->post('objet_id');
		}
		
		// Censure budgétaire (CdBM)
		// Internement (CdBM)
		// Pillage compulsif (Organisation)
		// Concurrence gênante (Organisation)
		// Malediction du Schnibble (SDS)
		// -> choix d'un joueur
		if (in_array($action_id, array(22, 23, 38, 39, 40)))
		{
			$this->CI->form_validation->set_rules('joueur_id', 'Le joueur', 'required|is_natural_no_zero');
			
			if ( ! $this->CI->form_validation->run())
				return false;
			
			// Internement (CdBM)
			// -> ni le maire, ni le suppléant, ni les candidats du 2e et 3e tour
			if ($action_id == 23)
			{
				$joueurs_interdits = array();

				// On récupère le maire et le suppléant
				$query = $this->CI->db->select('maire_id, maire_suppleant_id, tour_election')
									  ->from('mairie')
									  ->get();
				$mairie = $query->row();
				
				$joueurs_interdits[] = $mairie->maire_id;
				$joueurs_interdits[] = $mairie->maire_suppleant_id;

				// On récupère les candidats 2e et 3e tour
				if ($mairie->tour_election >= Bouzouk::Elections_Tour2)
				{
					$query = $this->CI->db->select('joueur_id')
										  ->from('elections')
										  ->where('tour', $mairie->tour_election)
										  ->get();
					$candidats = $query->result();

					foreach ($candidats as $candidat)
						$joueurs_interdits[] = $candidat->joueur_id;
				}
			}

			// On vérifie que le joueur est valide
			if (isset($joueurs_interdits))
				$this->CI->db->where_not_in('id', $joueurs_interdits);

			$valide = $this->CI->db->where('id', $this->CI->input->post('joueur_id'))
								   ->where('statut', Bouzouk::Joueur_Actif)
								   ->count_all_results('joueurs');

			if ( ! $valide)
			{
				$this->CI->echec('Le joueur ciblé est invalide');
				return false;
			}
			
			// Pillage compulsif (Organisation)
			// Concurrence gênante (Organisation)
			// -> choisir un bouzouk actif avec une fortune d'au moins 1000 struls et qu'il ne soit pas déjà victime
			if (in_array($action_id, array(38, 39)))
			{
				$fortune_totale = $this->CI->bouzouk->fortune_totale($this->CI->input->post('joueur_id'));

				if ($fortune_totale['total'] < 1000)
				{
					$this->CI->echec("Ce bouzouk est trop pauvre.");
					return false;
				}
				
				if ($action_id == 38)
				{
					if ($this->CI->bouzouk->clans_pillage_compulsif(null, $this->CI->input->post('joueur_id')))
					{
						$this->CI->echec("Ce bouzouk a déjà un Pillage compulsif actif sur lui.");
						return false;
					}
				}

				else if ($action_id == 39)
				{
					if ($this->CI->bouzouk->clans_concurrence_genante($this->CI->input->post('joueur_id')))
					{
						$this->CI->echec("Ce bouzouk a déjà une Concurrence gênante actif sur lui.");
						return false;
					}
				}
			}

			$parametres['joueur_id'] = $this->CI->input->post('joueur_id');
		}
		
		// Recrutement d'aliéné (SDS)
		// -> choix d'un alliéné
		if ($action_id == 41)
		{
			// On vérifie qu'il est à l'asile
			$query = $this->CI->db->select('id')
								  ->from('joueurs')
								  ->where('statut', Bouzouk::Joueur_Asile)
								  ->where('statut_staff_id', null)
								  ->where('id', $this->CI->input->post('joueur_id'))
								  ->get();
			
			if ($query->num_rows() == 0)
			{
				$this->CI->echec("Ce bouzouk n'est pas à l'asile");
					return false;
			}
			
			// On récupère ceux déjà touché par l'action
			$query = $this->CI->db->select('parametres')
							  ->from('clans_actions_lancees')
							  ->where('statut', Bouzouk::Clans_ActionEnCours)
							  ->where('action_id', $action_id)
							  ->get();

			if ($query->num_rows() > 0)
			{
				$actions_en_cours = $query->result();
			
				foreach ($actions_en_cours as $action_en_cours)
				{
					$action_en_cours = unserialize($action_en_cours->parametres);
					if ($action_en_cours['joueur_id'] == $this->CI->input->post('joueur_id'))
					{
						$this->CI->echec("Ce bouzouk a déjà un recrutement en cours");
							return false;
					}
				}
			}
			
			$parametres['joueur_id'] = $this->CI->input->post('joueur_id');
		}

		// Censure budgétaire (CdBM)
		// -> choix du pourcentage
		if ($action_id == 22)
		{
			$this->CI->form_validation->set_rules('pourcentage', 'Le pourcentage', 'required|is_natural_no_zero|greater_than_or_equal[1]|less_than_or_equal[10]');
			
			if ( ! $this->CI->form_validation->run())
				return false;

			$parametres['pourcentage'] = $this->CI->input->post('pourcentage');
		}

		// Sainte Brigade (SdS)
		// -> choix des objets à bloquer
		if ($action_id == 29)
		{
			$bibles = $this->CI->input->post('bibles') != false;
			$schnibbles = $this->CI->input->post('schnibbles') != false;

			// Au moins un des deux
			if ( ! $bibles && ! $schnibbles)
			{
				$this->CI->echec('Il faut choisir au moins un des objets');
				return false;
			}

			$parametres['bibles'] = $bibles;
			$parametres['schnibbles'] = $schnibbles;
		}

		// SPAM (Organisation)
		// -> choix des 100 bouzouks à spammer
		if ($action_id == 35)
		{
			$this->CI->form_validation->set_rules('bouzouks_type', 'Le type de bouzouks', 'required|regex_match[#^derniers_inscrits|plus_riches|moins_riches|aleatoires|sans_clan|sans_syndicat|sans_parti_politique|sans_organisation|males|femelles|petites_trompes|grandes_trompes|chomeurs$#]');

			if ( ! $this->CI->form_validation->run())
				return false;

			$parametres['bouzouks_type'] = $this->CI->input->post('bouzouks_type');
		}
		
		// Tract électoral (Parti politique)
		// -> choix des 100 bouzouks à spammer
		if ($action_id == 36)
		{
			$this->CI->form_validation->set_rules('bouzouks_type', 'Le type de bouzouks', 'required|regex_match[#^pas_vote$#]');
			
			if ( ! $this->CI->form_validation->run())
				return false;

			$parametres['bouzouks_type'] = $this->CI->input->post('bouzouks_type');
		}
		
		// Note à la secrétaire (Syndicat)
		// -> choix des bouzouks à missiver
		if ($action_id == 37)
		{
			$this->CI->form_validation->set_rules('bouzouks_type', 'Le type de bouzouks', 'required|regex_match[#^chefs_syndicats|membres_syndicat|employes_non_syndiques|tous_employes$#]');

			if ( ! $this->CI->form_validation->run())
				return false;

			$parametres['bouzouks_type'] = $this->CI->input->post('bouzouks_type');
		}
		
		// Recrutement d'alliéné (SDS)
		// -> choisir un bouzouk alliéné
		if (in_array($action_id, array(41)))
		{
			// On vérifie que le membre est un joueur alliéné
			$alienne = $this->CI->db->where('id', $this->CI->input->post('joueur_id'))
								  ->where('statut', Bouzouk::Joueur_Asile)
								  ->where('statut_staff_id', null)
								  ->count_all_results('joueurs');

			if ( ! $alienne)
			{
				$this->CI->echec("Ce bouzouk n'est pas un joueur alliéné");
				return false;
			}
			
			// On récupère ceux déjà touché par l'action
			$query = $this->CI->db->select('parametres')
							  ->from('clans_actions_lancees')
							  ->where('statut', Bouzouk::Clans_ActionEnCours)
							  ->where('action_id', $action_id)
							  ->get();

			if ($query->num_rows() > 0)
			{
				$actions_en_cours = $query->result();
			
				foreach ($actions_en_cours as $action_en_cours)
				{
					if ($action_en_cours['joueur_id'] == $this->CI->input->post('joueur_id'))
					{
						$this->CI->echec("Ce bouzouk est déjà en train de se faire recruter");
						return false;
					}
				}
			}
			
			$parametres['joueur_id'] = $this->CI->input->post('joueur_id');
		}

		return $parametres;
	}

	public function parametres($parametres, $montrer_invisibles = false)
	{
		$retour = array();

		// Choix d'un joueur
		if (isset($parametres['joueur_id']))
		{
			if (isset($parametres['joueur_invisible']) && $parametres['joueur_invisible'] && ! $montrer_invisibles)
				$retour[] = 'membre invisible';

			else
				$retour[] = profil($parametres['joueur_id']);
		}

		// Choix d'un shop
		if (isset($parametres['shop']))
		{
			$shops = array(
				'faim' => 'Bouffzouk',
				'sante' => 'Indispenzouk',
				'stress' => 'Luxezouk'
			);
			$retour[] = $shops[$parametres['shop']];
		}

		// Choix d'une entreprise
		if (isset($parametres['entreprise_id']) && $parametres['entreprise_id'] > 0)
		{
			// On va chercher l'entreprise
			$query = $this->CI->db->select('nom')
								  ->from('entreprises')
								  ->where('id', $parametres['entreprise_id'])
								  ->get();
			$entreprise = $query->num_rows() == 1 ? $query->row() : null;

			if (isset($entreprise))
				$retour[] = form_prep($entreprise->nom);
		}

		// Choix d'un clan adverse
		if (isset($parametres['clan_id']) && $parametres['clan_id'] > 0)
		{
			// On récupère les infos du clan
			$clan = $this->get_clan($parametres['clan_id']);

			if (isset($clan))
			{
				if ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible && ! $montrer_invisibles)
					$retour[] = 'clan caché';

				else
					$retour[] = form_prep($clan->nom);
			}
		}

		// Choix d'un clan adverse par nom : ne surtout jamais afficher le nom pour les joueurs,
		// car si le clan change de nom, il risque d'être visible dans l'historique des actions lancées
		if (isset($parametres['clan_nom']) && mb_strlen($parametres['clan_nom']) > 0)
		{
			if ( ! $montrer_invisibles)
				$retour[] = 'clan caché';

			else
				$retour[] = form_prep($parametres['clan_nom']);
		}

		// Choix d'un objet de shop
		if (isset($parametres['objet_id']))
		{
			// On va chercher l'objet
			$query = $this->CI->db->select('nom')
								  ->from('objets')
								  ->where('id', $parametres['objet_id'])
								  ->get();
			$objet = $query->row();
			$retour[] = $objet->nom;
		}

		// Choix du pourcentage
		if (isset($parametres['pourcentage']))
			$retour[] = $parametres['pourcentage'].'%';

		// Choix des objets à bloquer
		if (isset($parametres['bibles']) && $parametres['bibles'])
			$retour[] = 'bibles';

		if (isset($parametres['schnibbles']) && $parametres['schnibbles'])
			$retour[] = 'schnibbles';

		// Choix des 100 bouzouks à spammer
		if (isset($parametres['bouzouks_type']))
			$retour[] = str_replace('_', ' ', $parametres['bouzouks_type']);

		if (count($retour) == 0)
			return '';

		return '('.implode(', ', $retour).')';
	}

	public function lancer_action($clan_id, $action_id, $parametres, $cout)
	{
		// On récupère le clan
		$clan = $this->get_clan($clan_id);

		// On récupère l'action
		$query = $this->CI->db->select('nom, duree')
							  ->from('clans_actions')
							  ->where('id', $action_id)
							  ->get();
		$action = $query->row();
		$nom_action = couleur(form_prep($action->nom));

		// On enregistre l'action
		$jours_restants = $action->duree / 24;
		$parametres['montant_enchere'] = $cout;

		$data_clans_actions_lancees = array(
			'clan_id'        => $clan_id,
			'action_id'      => $action_id,
			'parametres'     => serialize($parametres),
			'date_debut'     => bdd_datetime(),
			'duree'          => $action->duree,
			'cout'           => $cout,
			'jours_restants' => $jours_restants,
			'statut'         => $jours_restants > 0 ? Bouzouk::Clans_ActionEnCours : Bouzouk::Clans_ActionTerminee,
			'nb_restants'    => ($action_id == 10) ? 3 : 0
		);
		$this->CI->db->insert('clans_actions_lancees', $data_clans_actions_lancees);
		
		$action_lancee_id = $this->CI->db->insert_id();

		// ---------- On effectue l'action ----------
		// Pression syndicale (Syndicat)
		if ($action_id == 1)
		{
			// On récupère l'entreprise
			$query = $this->CI->db->select('nom, chef_id')
								  ->from('entreprises')
								  ->where('id', $clan->entreprise_id)
								  ->get();
			$entreprise = $query->row();

			// On prévient le patron
			$nom_clan = ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('un syndicat') : 'le syndicat '.couleur(form_prep($clan->nom));
			$this->CI->bouzouk->notification(167, array(couleur('['.$nom_action.'] '), $nom_clan), $entreprise->chef_id);

			// Historique
			$historique = 'les baisses de salaire de tous les employés de '.couleur(form_prep($entreprise->nom)).' sont bloquées pour la journée';
		}

		// Grève d'entreprise (Syndicat)
		else if ($action_id == 2)
		{
			// On récupère l'entreprise
			$query = $this->CI->db->select('nom, chef_id')
								  ->from('entreprises')
								  ->where('id', $clan->entreprise_id)
								  ->get();
			$entreprise = $query->row();

			// On prévient le patron
			$nom_clan = ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('un syndicat') : 'le syndicat '.couleur(form_prep($clan->nom));
			$this->CI->bouzouk->notification(168, array(couleur('['.$nom_action.'] '), $nom_clan), $entreprise->chef_id);

			// Historique
			$historique = 'tous les employés de '.couleur(form_prep($entreprise->nom)).' seront improductifs à la prochaine production';
		}

		// Soutien salarial (Syndicat)
		else if ($action_id == 3)
		{
			// On vérifie que le joueur est toujours actif et employé de cette entreprise
			$query = $this->CI->db->select('e.id, e.chef_id')
								  ->from('employes e')
								  ->join('joueurs j', 'j.id = e.joueur_id')
								  ->where('e.joueur_id', $parametres['joueur_id'])
								  ->where('j.statut', Bouzouk::Joueur_Actif)
								  ->where('e.entreprise_id', $clan->entreprise_id)
								  ->get();

			if ($query->num_rows() > 0)
			{
				$entreprise = $query->row();

				// On prévient le patron
				$nom_clan = ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('un syndicat') : 'le syndicat '.couleur(form_prep($clan->nom));
				$this->CI->bouzouk->notification(169, array(couleur('['.$nom_action.'] '), $nom_clan, profil($parametres['joueur_id'])), $entreprise->chef_id);

				$historique = 'la baisse de salaire de '.profil($parametres['joueur_id']).' est bloquée pour 3 jours';
			}

			else
				$historique = 'aucun effet car '.profil($parametres['joueur_id'])." n'est plus employé dans cette entreprise ou n'est plus actif";
		}

		// Prise d'otage (Syndicat)
		else if ($action_id == 4)
		{
			// On vérifie que l'entreprise existe, que le patron est actif et qu'il n'est pas candidat aux élections
			$query = $this->CI->db->select('j.id, j.pseudo, j.rang, e.nom')
								  ->from('clans c')
								  ->join('entreprises e', 'e.id = c.entreprise_id')
								  ->join('joueurs j', 'j.id = e.chef_id')
								  ->join('elections el', 'el.joueur_id = e.chef_id', 'left')
								  ->where('c.type', Bouzouk::Clans_TypeSyndicat)
								  ->where('c.id', $clan->id)
								  ->where('j.statut', Bouzouk::Joueur_Actif)
								  ->where('el.joueur_id IS NULL')
								  ->get();

			if ($query->num_rows() == 1)
			{
				$patron = $query->row();

				// On regarde si le patron est maire de la ville
				$maire = $this->CI->db->where('maire_id', $patron->id)
								 	  ->count_all_results('mairie');

				if ($maire)
					$historique = 'ton patron est maire de la ville, tu ne peux pas lancer cette action';

				else
				{
					// On met le patron à l'asile
					$nom_clan = ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('un syndicat') : 'le syndicat '.couleur(form_prep($clan->nom));
					$this->CI->load->library('lib_joueur');
					$this->CI->lib_joueur->mettre_asile($patron->id, 'Tu as été interné par '.$nom_clan." suite à l'action ".couleur($action->nom), 24);

					// Historique
					$historique = 'le patron de '.couleur(form_prep($patron->nom)).', '.profil($patron->id, $patron->pseudo, $patron->rang).", a été envoyé à l'asile pour 24h";
				}
			}

			else
				$historique = "aucun effet car le patron ne peut pas aller à l'asile (candidat aux élections ou déjà à l'asile)";
		}
		
		// Grosse manif syndicale (Syndicat)
		else if ($action_id == 5)
		{
			// On récupère tous les patrons non candidats aux élections
			$query = $this->CI->db->select('e.chef_id')
								  ->from('entreprises e')
								  ->join('elections el', 'el.joueur_id = e.chef_id', 'left')
								  ->where('el.joueur_id IS NULL')
								  ->or_where('e.chef_id = (SELECT maire_id FROM mairie)')
								  ->get();

			if ($query->num_rows() > 0)
			{
				$patrons = $query->result();
				$this->CI->load->library('lib_joueur');

				// On envoit les patrons à l'asile
				$nom_clan = ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('un syndicat') : 'le syndicat '.couleur(form_prep($clan->nom));

				foreach ($patrons as $patron)
					$this->CI->lib_joueur->mettre_asile($patron->chef_id, 'Tu as été interné par '.$nom_clan." suite à l'action ".couleur($action->nom), 12);

				// Historique
				$historique = "tous les patrons non candidats aux élections ont été envoyés à l'asile";
			}

			else
				$historique = "aucun effet car aucun patron non candidat aux élections n'est actif";
		}

		// Grève générale (Syndicat)
		else if ($action_id == 6)
		{
			$historique = 'tous les employés syndiqués de toutes les entreprises seront improductifs pendant 3 jours';
		}

		// Destitution (Parti Politique)
		else if ($action_id == 7)
		{
			// On vérifie que le maire est un joueur actif
			$query = $this->CI->db->select('j.id, j.pseudo, j.rang')
								  ->from('mairie m')
								  ->join('joueurs j', 'j.id = m.maire_id')
								  ->where('j.statut', Bouzouk::Joueur_Actif)
								  ->get();

			if ($query->num_rows() == 1)
			{
				$maire = $query->row();

				// On met le maire à l'asile
				$nom_clan = ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('un clan') : 'le clan '.couleur(form_prep($clan->nom));
				$this->CI->load->library('lib_joueur');
				$this->CI->lib_joueur->mettre_asile($maire->id, 'Tu as été destitué par '.$nom_clan.' et ses alliés', 12);

				// Historique
				$historique = profil($maire->id, $maire->pseudo, $maire->rang)." a été destitué de son poste, le maire suppléant prend sa place";
			}

			else
				$historique = "aucun effet car le maire n'est plus un bouzouk actif";
		}

		// Prise de pouvoir (Parti Politique)
		else if ($action_id == 8)
		{
			// On récupère les infos du maire
			$query = $this->CI->db->select('j.id, j.pseudo')
								  ->from('mairie m')
								  ->join('joueurs j', 'j.id = m.maire_id')
								  ->where('j.statut', Bouzouk::Joueur_Actif)
								  ->get();

			if ($query->num_rows() == 1)
			{
				$maire = $query->row();

				// On prépare la missive
				$this->CI->load->library('lib_missive');

				$vars = array(
 					'maire'            => $maire,
 					'nom_clan'         => ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('clan secret') : couleur(form_prep($clan->nom)),
 					'prix_bouzopolice' => floor($parametres['montant_enchere'] * $this->CI->bouzouk->config('clans_coefficient_surenchere'))
				);
				$contenu = $this->CI->load->view('clans/missive_prise_de_pouvoir', $vars, true);

				// On envoit une missive au maire pour lui laisser le choix
				$data_missives = array(
					'expediteur_id'   => Bouzouk::Robot_JF_Sebastien,
					'destinataire_id' => $maire->id,
					'date_envoi'      => bdd_datetime(),
					'timbre'          => $this->CI->lib_missive->timbres(6), // petit timbre sympa
					'objet'           => 'Prise de pouvoir',
					'message'         => $contenu
				);
				$this->CI->db->insert('missives', $data_missives);

				// Historique
				$historique = profil($maire->id)." a 24h pour prendre une décision sans quoi il cédera sa place au chef du clan";
			}

			else
			{
				// L'action est finie, on l'arrête
				$this->CI->db->set('statut', Bouzouk::Clans_ActionTerminee)
							 ->set('jours_restants', 0)
							 ->where('id', $action_lancee_id)
							 ->update('clans_actions_lancees');

				// On place le chef du parti à la place de JF
				$this->CI->db->set('maire_id', $clan->chef_id)
							 ->update('mairie');

				// On met à jour la session du chef
				$this->CI->bouzouk->augmente_version_session($clan->chef_id);

				// On envoit une notification au chef de clan
				$this->CI->bouzouk->notification(170, array(couleur('[Prise de pouvoir]'), profil(Bouzouk::Robot_JF_Sebastien)), $clan->chef_id);
				
				$historique = "le chef du clan a reprit la mairie";
			}
		}

		// Distinction électorale (Parti Politique)
		else if ($action_id == 9)
		{
			// On récupère le tour de la mairie
			$query = $this->CI->db->select('tour_election')
								  ->from('mairie')
								  ->get();
			$mairie = $query->row();

			// On vérifie que le candidat est à ce tour là
			$candidat = $this->CI->db->where('tour', $mairie->tour_election)
									 ->where('joueur_id', $parametres['joueur_id'])
									 ->count_all_results('elections');

			if ($candidat)
			{
				// On envoit une notif au candidat
				$nom_clan = ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('un clan') : 'le clan '.couleur(form_prep($clan->nom));
				$this->CI->bouzouk->notification(171, array(couleur('['.$nom_action.'] '), $nom_clan), $parametres['joueur_id']);

				$historique = profil($parametres['joueur_id']).' est distingué des autres candidats aux élections';
			}
			
			else
				$historique = 'aucun effet car '.profil($parametres['joueur_id'])." n'est plus en course aux élections";
		}

		// Magouille (Parti Politique)
		else if ($action_id == 10)
		{
			// On prévient le maire
			$query = $this->CI->db->select('maire_id')
								  ->from('mairie')
								  ->get();
			$mairie = $query->row();

			// On envoit une notif au maire
			$nom_clan = ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('un clan') : 'le clan '.couleur(form_prep($clan->nom));
			$this->CI->bouzouk->notification(172, array(couleur('['.$nom_action.'] '), $nom_clan), $mairie->maire_id);

			$historique = 'les 3 prochaines actions du maire seront cachées aux journalistes';
		}

		// Propagande (Parti Politique)
		else if ($action_id == 11)
		{
			$shops = array(
				'faim' => 'le Bouffzouk',
				'sante' => "l'Indispenzouk",
				'stress' => 'le Luxezouk'
			);
			$historique = $shops[$parametres['shop']].' est bloqué pour la journée avec un texte de propagande';
		}

		// Calomnie (Parti Politique)
		// Diffamation collective (Parti Politique)
		else if (in_array($action_id, array(12, 14)))
		{
			// On vérifie que le clan existe toujours
			$clan_cible = $this->get_clan($parametres['clan_id']);

			if (isset($clan_cible))
			{
				// Calomnie : on supprime 50% des points d'action
				if ($action_id == 12)
				{
					$points_action = ceil($clan_cible->points_action / 2.0);
					$historique = couleur(form_prep($clan_cible->nom))." perd 50% de points d'action (charisme)";

					if ($clan_cible->mode_recrutement == Bouzouk::Clans_RecrutementInvisible)
						$historique_public = couleur('un clan')." perd 50% de points d'action (charisme)";
				}

				// Diffamation collective : on supprime tous les points d'action
				else
				{
					$points_action = 0;
					$historique = couleur(form_prep($clan_cible->nom))." perd tous ses points d'action (charisme)";

					if ($clan_cible->mode_recrutement == Bouzouk::Clans_RecrutementInvisible)
						$historique_public = couleur('un clan')." perd 50% de points d'action (charisme)";
				}

				$this->CI->db->set('points_action', $points_action)
							 ->where('id', $parametres['clan_id'])
							 ->update('clans');

				// On envoit une notif au chef et au clan
				$nom_clan = ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('un clan') : 'le clan '.couleur(form_prep($clan->nom));
				$this->CI->bouzouk->notification(173, array(couleur('['.$nom_action.']'), $nom_clan, $historique), $clan_cible->chef_id);
				$this->historique(couleur('['.$nom_action.'] ').' : '.$nom_clan.' a lancé cette action, '.$historique, $clan_cible->id);
			}

			else
				$historique = "aucun effet car le clan ciblé n'existe plus";
		}
		
		// Sabotage (Parti Politique)
		else if ($action_id == 13)
		{
			// On vérifie que le clan existe toujours
			$clan_cible = $this->get_clan($parametres['clan_id']);

			if (isset($clan_cible))
			{
				$historique = couleur(form_prep($clan_cible->nom)).' a ses actions bloquées pendant 4 jours';

				if ($clan_cible->mode_recrutement == Bouzouk::Clans_RecrutementInvisible)
					$historique_public = couleur('un clan').' a ses actions bloquées pendant 4 jours';

				// On envoit une notif au chef et au clan
				$nom_clan = ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('un clan') : 'le clan '.couleur(form_prep($clan->nom));
				$this->CI->bouzouk->notification(173, array(couleur('['.$nom_action.']'), $nom_clan, $historique), $clan_cible->chef_id);;
				$this->historique(couleur('['.$nom_action.'] ').' : '.$nom_clan.' a lancé cette action, '.$historique, $clan_cible->id);
			}
			
			else
				$historique = "aucun effet car le clan ciblé n'existe plus";
		}

		// Elections truquées (Parti Politique)
		else if ($action_id == 15)
		{
			// On récupère le tour actuel
			$query = $this->CI->db->select('tour_election')
								  ->from('mairie')
								  ->get();
			$mairie = $query->row();

			// On vérifie que le candidat est toujours aux élections
			$existe = $this->CI->db->where('joueur_id', $parametres['joueur_id'])
								   ->where('tour', $mairie->tour_election)
								   ->count_all_results('elections');

			if ($existe)
			{
				// On tire aléatoirement un nombre de votes et on les ajoute
				$nb_faux_votes = mt_rand(10, 15);
				$this->CI->db->set('faux_votes', 'faux_votes+'.$nb_faux_votes, false)
							 ->set('votes', 'votes+'.$nb_faux_votes, false)
							 ->where('joueur_id', $parametres['joueur_id'])
							 ->update('elections');

				// Historique
				$historique = profil($parametres['joueur_id']).' gagne +'.pluriel($nb_faux_votes, 'vote').' aux élections';

				// On envoit une notif au candidat
				$nom_clan = ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('un clan') : 'le clan '.couleur(form_prep($clan->nom));
				$this->CI->bouzouk->notification(175, array(couleur('['.$nom_action.']'), $nom_clan, $nb_faux_votes), $parametres['joueur_id']);
			}

			else
				$historique = "aucun effet car le candidat ciblé n'est plus en course";
		}

		// Recomptage (Parti Politique)
		else if ($action_id == 16)
		{
			// On récupère le tour actuel
			$query = $this->CI->db->select('tour_election')
								  ->from('mairie')
								  ->get();
			$mairie = $query->row();

			// On vérifie que le candidat est toujours aux élections
			$query = $this->CI->db->select('votes, faux_votes')
								  ->from('elections')
								  ->where('joueur_id', $parametres['joueur_id'])
								  ->where('tour', $mairie->tour_election)
								  ->get();

			if ($query->num_rows() == 1)
			{
				$candidat = $query->row();

				// On enlève tous les faux votes
				$nb_votes = max(0, $candidat->votes - $candidat->faux_votes);
				$this->CI->db->set('votes', $nb_votes)
							 ->set('faux_votes', 0)
							 ->where('joueur_id', $parametres['joueur_id'])
							 ->update('elections');

				// Historique
				$historique = profil($parametres['joueur_id']).' perd -'.pluriel($candidat->faux_votes, 'vote').' aux élections';

				// On envoit une notif au candidat
				$nom_clan = ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('un clan') : 'le clan '.couleur(form_prep($clan->nom));
				$this->CI->bouzouk->notification(177, array(couleur('['.$nom_action.']'), $nom_clan, pluriel($candidat->faux_votes, 'vote')), $parametres['joueur_id']);
			}

			else
				$historique = "aucun effet car le candidat ciblé n'est plus en course";
		}

		// Braquage (Organisation)
		else if ($action_id == 17)
		{
			// On va chercher la quantité de cet objet dans le magasin
			$query = $this->CI->db->select('m.quantite, o.nom, o.id, o.peremption, o.prix')
								  ->from('magasins m')
								  ->join('objets o', 'o.id = m.objet_id')
								  ->where('m.objet_id', $parametres['objet_id'])
								  ->get();
			$objet = $query->row();

			// Si la quantité est > 0
			if ($objet->quantite > 0)
			{
				// On réduit à 0 la quantité du magasin
				$this->CI->db->set('quantite', 0)
							 ->where('objet_id', $parametres['objet_id'])
							 ->update('magasins');

				// On choisit un robot vendeur aléatoirement
				$robots = $this->CI->bouzouk->get_robots();
				$vendeur = $robots[mt_rand(0, count($robots) - 1)];
				
				// On introduit les objets au marché noir
				$data_marche_noir = array(
					'objet_id'   => $objet->id,
					'joueur_id'  => $vendeur,
					'quantite'   => $objet->quantite,
					'prix'       => ceil($objet->prix * 1.3),
					'peremption' => $objet->peremption
				);
				$this->CI->db->insert('marche_noir', $data_marche_noir);

				// Historique
				$historique = 'le clan vide '.couleur($objet->quantite.' '.$objet->nom).' du magasin et les revend au marché noir avec 30% de marge';
			}
		
			else
				$historique = "aucun effet car l'objet ".couleur($objet->nom).' est en rupture de stock';
		}

		// Publication promotionnelle (Organisation)
		else if ($action_id == 18)
		{
			// On change le texte de la pub clan dans la gazette
			$this->CI->db->set('texte', $parametres['texte'])
						 ->set('date', bdd_datetime())
						 ->where('type', Bouzouk::Gazette_PubClan)
						 ->update('gazettes');

			$historique = 'la publicité dans la gazette a été modifiée';
		}

		// Informateur (Organisation)
		else if ($action_id == 19)
		{
			// On regarde si une taxe est déjà prévue
			$query = $this->CI->db->select('taux')
								  ->from('taxes_surprises')
								  ->where('distribuee', 0)
								  ->order_by('taux', 'desc')
								  ->limit(1)
								  ->get();

			if ($query->num_rows() == 1)
			{
				$taxe = $query->row();

				// On récupère tous les membres du clan
				$query = $this->CI->db->select('joueur_id')
									  ->from('politiciens')
									  ->where('clan_id', $clan->id)
									  ->get();
				$politiciens = $query->result();

				// On prévient le chef
				$this->CI->bouzouk->notification(95, array(couleur($taxe->taux)), $clan->chef_id);

				// On prévient les membres
				foreach ($politiciens as $politicien)
					$this->CI->bouzouk->notification(95, array(couleur($taxe->taux)), $politicien->joueur_id);
			}

			$historique = 'tous les membres seront prévenus si le maire envoie une taxe surprise';
		}

		// Racket aux pochtrons (Organisation)
		else if ($action_id == 20)
		{
			$historique = 'toutes les donations mendiants vont dans la poche de '.profil($parametres['joueur_id']);

			if ($this->est_membre_invisible($parametres['joueur_id'], $clan->id))
				$historique_public = "toutes les donations mendiants vont dans la poche d'".couleur('un membre du clan');
		}

		// Escroquerie à la mendicité (Organisation)
		else if ($action_id == 21)
		{
			$historique = 'tous les membres du clan peuvent mendier en tant que riche';
		}

		// Censure budgétaire (CdBM)
		else if ($action_id == 22)
		{
			// On vérifie que le joueur est toujours actif
			$actif = $this->CI->db->where('id', $parametres['joueur_id'])
								  ->where('statut', Bouzouk::Joueur_Actif)
								  ->count_all_results('joueurs');

			if ($actif)
			{
				// On récupère la fortune du joueur, limité à 1000 strulls
				$fortune = $this->CI->bouzouk->fortune_totale($parametres['joueur_id']);
				$taxe = min(1000, max(1, floor($fortune['total'] * $parametres['pourcentage'] / 100.0)));

				// On lui ajoute une facture
				$data_factures = array(
					'joueur_id' => $parametres['joueur_id'],
					'titre'     => '[Censure budgétaire] de la part du clan '.couleur(form_prep($clan->nom)).' : '.couleur($parametres['pourcentage'].' % ( max 1000 $)').' de ta fortune totale',
					'montant'   => $taxe,
					'date'      => bdd_datetime()
				);
				$this->CI->db->insert('factures', $data_factures);

				// On lui envoit une notification
				$this->CI->bouzouk->historique(159, null, array(couleur($taxe.' $'), couleur(form_prep($clan->nom))), $parametres['joueur_id'], Bouzouk::Historique_Full);

				// Historique
				$historique = profil($parametres['joueur_id']).' prend une taxe sur la fortune de '.struls($taxe)." $.";
			}

			else
				$historique = "aucun effet car le bouzouk ciblé n'est plus un bouzouk actif";
		}

		// Internement (CdBM)
		else if ($action_id == 23)
		{
			// On vérifie que le joueur est toujours actif
			$actif = $this->CI->db->where('id', $parametres['joueur_id'])
								  ->where('statut', Bouzouk::Joueur_Actif)
								  ->count_all_results('joueurs');

			if ($actif)
			{
				$this->CI->load->library('lib_joueur');
				$this->CI->lib_joueur->mettre_asile($parametres['joueur_id'], 'Tu as été interné par le clan '.couleur(form_prep($clan->nom)).' pour la raison suivante :<br><br>&laquo;'.$this->CI->lib_parser->remplace_bbcode(couleur(nl2br(form_prep($parametres['texte'])))).'&raquo;', 8);

				// Historique
				$historique = profil($parametres['joueur_id'])." a été interné à l'asile";
			}

			else
				$historique = "aucun effet car le bouzouk ciblé n'est plus un bouzouk actif";
		}

		// Censure des mendiants (CdBM)
		else if ($action_id == 24)
		{
			$historique = 'les pages des mendiants sont censurées pour la journée';
		}

		// Corruption à agent (Struleone)
		else if ($action_id == 25)
		{
			$historique = 'les membres du clan ont 50% de chances en moins de se faire attraper par la bouzopolice pendant 1h';
		}

		// Fabrique de Gnoulze (Struleone)
		else if ($action_id == 26)
		{
			// On vérifie que le joueur est toujours actif
			$actif = $this->CI->db->where('id', $parametres['joueur_id'])
								  ->where('statut', Bouzouk::Joueur_Actif)
								  ->count_all_results('joueurs');

			if ($actif)
			{
				// On va chercher les infos de cet objet
				$query = $this->CI->db->select('id, prix, peremption, nom')
									  ->from('objets')
									  ->where('id', 24)
									  ->get();
				$objet = $query->row();

				// On choisit un robot vendeur aléatoirement
				$robots = $this->CI->bouzouk->get_robots();
				$vendeur = $robots[mt_rand(0, count($robots) - 1)];
					
				// On introduit les objets au marché noir
				$data_marche_noir = array(
					'objet_id'   => $objet->id,
					'joueur_id'  => $vendeur,
					'quantite'   => 20,
					'prix'       => $objet->prix,
					'peremption' => $objet->peremption
				);
				$this->CI->db->insert('marche_noir', $data_marche_noir);

				// Historique
				$historique = 'le clan a fabriqué '.couleur('20 '.$objet->nom).' et les vend au marché noir au profit de '.profil($parametres['joueur_id']);

				if ($this->est_membre_invisible($parametres['joueur_id'], $clan->id))
					$historique_public = 'le clan a fabriqué '.couleur('20 '.$objet->nom)." et les vend au marché noir au profit d'un membre du clan";
			}
		
			else
				$historique = "aucun effet car le bouzouk ciblé n'est plus un joueur actif";
		}

		// Magouille fiscale (Struleone)
		else if ($action_id == 27)
		{
			$historique = 'la richesse des membres du clan est considérée à 0 strul pour la journée (taxes, mendiants, classements et dons du maire)';
		}

		// Misérabilisme (Organisation)
		else if ($action_id == 28)
		{
			$historique = 'la page des mendiants est remplacée par une page de donation au clan';
		}

		// Sainte Brigade (Organisation)
		else if ($action_id == 29)
		{
			// On regarde quels objets sont bloqués
			$objets = array();

			if ($parametres['bibles'])
				$objets[] = couleur('Bibles du Schnibble');

			if ($parametres['schnibbles'])
				$objets[] = couleur('Schnibbles');

			$objets = implode(' et ', $objets);

			// Historique
			$historique = "l'achat des objets $objets est bloqué pour la journée";
		}

		// Schnibble traité (SdS)
		else if ($action_id == 30)
		{
			// On récupère un schnibble de plus haute péremption
			$query = $this->CI->db->select('m.peremption, o.id, o.nom')
								  ->from('maisons m')
								  ->join('objets o', 'o.id = m.objet_id')
								  ->join('joueurs j', 'j.id = m.joueur_id')
								  ->where('m.joueur_id', $parametres['joueur_id'])
								  ->where('m.objet_id', 18)
								  ->where('(m.peremption >= 3 OR m.peremption = -1)')
								  ->where('j.statut', Bouzouk::Joueur_Actif)
								  ->order_by('m.peremption', 'desc')
								  ->limit(1)
								  ->get();

			if ($query->num_rows() == 1)
			{
				$objet = $query->row();

				// On récupère les infos du schnibble traité
				$query = $this->CI->db->select('id, nom')
									  ->from('objets')
									  ->where('id', 32)
									  ->get();
				$objet_traite = $query->row();

				// On retire le schnibble
				$this->CI->bouzouk->retirer_objets($objet->id, 1, $objet->peremption, $parametres['joueur_id']);

				// On ajoute le schnibble traité
				$this->CI->bouzouk->ajouter_objets($objet_traite->id, 1, $objet->peremption, $parametres['joueur_id']);

				// On lui envoit une notification
				$this->CI->bouzouk->historique(160, null, array(couleur(form_prep($clan->nom)), couleur($objet->nom), couleur($objet_traite->nom)), $parametres['joueur_id'], Bouzouk::Historique_Full);

				// Historique
				$historique = profil($parametres['joueur_id']).' a reçu un '.couleur($objet_traite->nom)." dans sa maison en échange d'un ".couleur($objet->nom);

				if ($this->est_membre_invisible($parametres['joueur_id'], $clan->id))
					$historique_public = couleur('un membre du clan').' a reçu un '.couleur($objet_traite->nom)." dans sa maison en échange d'un ".couleur($objet->nom);
			}

			else
				$historique = "aucun effet car le bouzouk ciblé n'a plus de Schnibble ou n'est plus actif";
		}

		// Tag MLBiste
		else if ($action_id == 31)
		{
			if ($parametres['clan_id'] > 0)
			{
				$clan_cible = $this->get_clan($parametres['clan_id']);

				if (isset($clan_cible))
				{
					$historique = 'des tags sont affichés chez '.couleur(form_prep($clan_cible->nom));

					if ($clan_cible->mode_recrutement == Bouzouk::Clans_RecrutementInvisible)
						$historique_public = 'des tags sont affichés chez '.couleur('un clan');
				}
			}

			else
				$historique = 'des tags sont affichés un peu partout dans la ville';
		}

		// Sabotage (MLB)
		else if ($action_id == 32)
		{
			// On vérifie que l'entreprise existe
			$query = $this->CI->db->select('nom, production, chef_id')
								  ->from('entreprises')
								  ->where('id', $parametres['entreprise_id'])
								  ->get();
			$entreprise = ($query->num_rows() == 1) ? $query->row() : null;

			if (isset($entreprise))
			{
				// On enlève xx% de la production de l'entreprise
				$pourcentage = mt_rand(2, 50);
				$production_perdue = ceil($entreprise->production * $pourcentage / 100.0);
				
				$this->CI->db->set('production', 'production-'.$production_perdue, false)
							 ->where('id', $parametres['entreprise_id'])
							 ->update('entreprises');

				// On envoit une notif au patron
				$nom_clan = ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('un clan') : 'le clan '.couleur(form_prep($clan->nom));
				$this->CI->bouzouk->notification(178, array(couleur('['.$nom_action.']'), $nom_clan, couleur($pourcentage.'%'), struls($production_perdue)), $entreprise->chef_id);

				// Historique
				$historique = couleur(form_prep($entreprise->nom)).' perd '.couleur($pourcentage.'%').' de sa production soit '.struls($production_perdue);
			}

			else
				$historique = "aucun effet car l'entreprise ciblée n'existe plus";
		}

		// Culture de Raki (MLB)
		else if ($action_id == 33)
		{
			// On vérifie que le joueur est toujours actif
			$actif = $this->CI->db->where('id', $parametres['joueur_id'])
								  ->where('statut', Bouzouk::Joueur_Actif)
								  ->count_all_results('joueurs');

			if ($actif)
			{
				// On récupère l'objet
				$query = $this->CI->db->select('id, nom, peremption')
									  ->from('objets')
									  ->where('id', 27)
									  ->get();
				$objet = $query->row();

				// On ajoute le raki
				$this->CI->bouzouk->ajouter_objets($objet->id, 1, $objet->peremption, $parametres['joueur_id']);

				// On lui envoit une notification
				$this->CI->bouzouk->historique(161, null, array(couleur(form_prep($clan->nom)), couleur('1 '.$objet->nom)), $parametres['joueur_id'], Bouzouk::Historique_Full);

				// Historique
				$historique = profil($parametres['joueur_id']).' a reçu '.couleur('1 '.$objet->nom).' dans sa maison';

				if ($this->est_membre_invisible($parametres['joueur_id'], $clan->id))
					$historique_public = couleur('un bouzouk').' a reçu '.couleur('1 '.$objet->nom).' dans sa maison';
			}

			else
				$historique = "aucun effet car le bouzouk ciblé n'est plus un bouzouk actif";
		}

		// Espionnage (Organisation)
		else if ($action_id == 34)
		{
			// On vérifie que le clan ou l'entreprise existe
			if ($parametres['clan_id'] == 0 && $parametres['entreprise_id'] == 0)
				$historique = "aucun effet car le clan ou l'entreprise cible n'existe pas";

			// On récupère le nom du clan ou de l'entreprise
			$query = $this->CI->db->select('nom')
								  ->from('entreprises')
								  ->where('id', $parametres['entreprise_id'])
								  ->get();
			$entreprise = ($query->num_rows() == 1) ? $query->row() : null;

			if ( ! isset($entreprise))
			{
				$clan_cible = $this->get_clan($parametres['clan_id']);

				if (isset($clan_cible))
				{
					$historique = couleur(form_prep($clan_cible->nom)).' se fait espionner pour la journée';

					if ($clan_cible->mode_recrutement == Bouzouk::Clans_RecrutementInvisible)
						$historique_public = couleur('un clan').' se fait espionner pour la journée';
				}

				else
					$historique = "aucun effet car le clan ou l'entreprise ciblé(e) n'existe plus";
			}

			else
				$historique = couleur(form_prep($entreprise->nom)).' se fait espionner pour la journée';
		}

		// SPAM (Organisation)
		else if ($action_id == 35)
		{
			// On récupère le chef et les membres du clan pour les enlever du spam
			$clan_membres_ids = array($clan->chef_id);

			$query = $this->CI->db->select('joueur_id')
								 ->from('politiciens')
								 ->where('clan_id', $clan->id)
								 ->get();
			$politiciens = $query->result();

			foreach ($politiciens as $politicien)
				$clan_membres_ids[] = $politicien->joueur_id;

			// On va chercher les bouzouks
			if ($parametres['bouzouks_type'] == 'derniers_inscrits')
			{
				$this->CI->db->select('id')
							 ->from('joueurs')
							 ->where('statut', Bouzouk::Joueur_Actif)
							 ->where_not_in('id', $clan_membres_ids)
							 ->order_by('date_inscription', 'desc');

				$historique = 'les 100 derniers bouzouks inscrits';
			}

			else if ($parametres['bouzouks_type'] == 'plus_riches')
			{
				$this->CI->db->select('joueur_id AS id')
						  	 ->from('classement_joueurs')
							 ->where('type', Bouzouk::Classement_Fortune)
							 ->where_not_in('joueur_id', $clan_membres_ids)
							 ->order_by('position')
							 ->order_by('id', 'random');

				$historique = 'les 100 bouzouks les plus riches';
			}

			else if ($parametres['bouzouks_type'] == 'moins_riches')
			{
				$this->CI->db->select('joueur_id AS id')
							 ->from('classement_joueurs')
							 ->where('type', Bouzouk::Classement_Fortune)
							 ->where_not_in('joueur_id', $clan_membres_ids)
							 ->order_by('position', 'desc')
							 ->order_by('id', 'random');

				$historique = 'les 100 bouzouks les moins riches';
			}

			else if ($parametres['bouzouks_type'] == 'aleatoires')
			{
				$this->CI->db->select('id')
						  	 ->from('joueurs')
							 ->where('statut', Bouzouk::Joueur_Actif)
							 ->where_not_in('id', $clan_membres_ids)
							 ->order_by('id', 'random');

				$historique = '100 bouzouks aléatoires';
			}

			else if ($parametres['bouzouks_type'] == 'sans_clan')
			{
				$this->CI->db->select('id')
							 ->from('joueurs')
							 ->where('statut', Bouzouk::Joueur_Actif)
							 ->where('id NOT IN (SELECT DISTINCT joueur_id FROM politiciens)')
							 ->where('id NOT IN (SELECT DISTINCT chef_id FROM clans)')
							 ->order_by('id', 'random');

				$historique = '100 bouzouks sans clan';
			}

			else if ($parametres['bouzouks_type'] == 'sans_syndicat')
			{
				$this->CI->db->select('id')
						  	 ->from('joueurs')
							 ->where('statut', Bouzouk::Joueur_Actif)
							 ->where('id NOT IN (SELECT joueur_id FROM politiciens p join clans c ON c.id = p.clan_id WHERE c.type = '.Bouzouk::Clans_TypeSyndicat.')')
							 ->where('id NOT IN (SELECT chef_id FROM clans WHERE type = '.Bouzouk::Clans_TypeSyndicat.')')
							 ->where_not_in('id', $clan_membres_ids)
							 ->order_by('id', 'random');

				$historique = '100 bouzouks sans syndicat';
			}

			else if ($parametres['bouzouks_type'] == 'sans_parti_politique')
			{
				$this->CI->db->select('id')
						  	 ->from('joueurs')
						  	 ->where('statut', Bouzouk::Joueur_Actif)
							 ->where('id NOT IN (SELECT joueur_id FROM politiciens p join clans c ON c.id = p.clan_id WHERE c.type = '.Bouzouk::Clans_TypePartiPolitique.')')
							 ->where('id NOT IN (SELECT chef_id FROM clans WHERE type = '.Bouzouk::Clans_TypePartiPolitique.')')
							 ->where_not_in('id', $clan_membres_ids)
							 ->order_by('id', 'random');

				$historique = '100 bouzouks sans parti politique';
			}

			else if ($parametres['bouzouks_type'] == 'sans_organisation')
			{
				$this->CI->db->select('id')
						  	 ->from('joueurs')
						  	 ->where('statut', Bouzouk::Joueur_Actif)
							 ->where('id NOT IN (SELECT joueur_id FROM politiciens p join clans c ON c.id = p.clan_id WHERE c.type = '.Bouzouk::Clans_TypeOrganisation.')')
							 ->where('id NOT IN (SELECT chef_id FROM clans WHERE type = '.Bouzouk::Clans_TypeOrganisation.')')
							 ->where_not_in('id', $clan_membres_ids)
							 ->order_by('id', 'random');

				$historique = '100 bouzouks sans organisation';
			}
			
			else if ($parametres['bouzouks_type'] == 'males')
			{
				$this->CI->db->select('id')
						  	 ->from('joueurs')
							 ->where('statut', Bouzouk::Joueur_Actif)
							 ->where('sexe', 'male')
							 ->where_not_in('id', $clan_membres_ids)
							 ->order_by('id', 'random');

				$historique = '100 bouzouks mâles';
			}
			
			else if ($parametres['bouzouks_type'] == 'femelles')
			{
				$this->CI->db->select('id')
						  	 ->from('joueurs')
							 ->where('statut', Bouzouk::Joueur_Actif)
							 ->where('sexe', 'femelle')
							 ->where_not_in('id', $clan_membres_ids)
							 ->order_by('id', 'random');

				$historique = '100 bouzouks femelles';
			}
			
			else if ($parametres['bouzouks_type'] == 'grandes_trompes')
			{
				$this->CI->db->select('j.id')
						  	 ->from('joueurs j')
							 ->join('tobozon_users tu', 'tu.id = j.id')
							 ->where('j.statut', Bouzouk::Joueur_Actif)
							 ->where_not_in('j.id', $clan_membres_ids)
							 ->order_by('tu.num_posts', 'desc');

				$historique = '100 bouzouks avec la plus grande trompe';
			}
			
			else if ($parametres['bouzouks_type'] == 'petites_trompes')
			{
				$this->CI->db->select('j.id')
						  	 ->from('joueurs j')
							 ->join('tobozon_users tu', 'tu.id = j.id')
							 ->where('j.statut', Bouzouk::Joueur_Actif)
							 ->where_not_in('j.id', $clan_membres_ids)
							 ->order_by('tu.num_posts');

				$historique = '100 bouzouks avec la plus petite trompe';
			}
			
			else if ($parametres['bouzouks_type'] == 'chomeurs')
			{
				$this->CI->db->select('j.id')
							 ->from('joueurs j')
							 ->join('employes em', 'em.joueur_id = j.id', 'left')
							 ->join('entreprises en', 'en.chef_id = j.id', 'left')
							 ->where_in('j.statut', array(Bouzouk::Joueur_Actif))
							 ->where('em.joueur_id IS NULL')
							 ->where('en.chef_id IS NULL')
							 ->order_by('id', 'random');
					
				$historique = '100 bouzouks chômeurs';
			}

			$query = $this->CI->db->limit(100)
								  ->get();
			$joueurs = $query->result();

			// On prépare les missives
			$this->CI->load->library('lib_missive');
			$data_missives = array();
			$timbre        = $this->CI->lib_missive->timbres(6); // petit timbre sympa
			$datetime      = bdd_datetime();

			// On envoit le spam
			foreach ($joueurs as $joueur)
			{
				$data_missives[] = array(
					'expediteur_id'   => Bouzouk::Robot_Spam,
					'destinataire_id' => $joueur->id,
					'date_envoi'      => $datetime,
					'timbre'          => $timbre,
					'objet'           => $parametres['titre'],
					'message'         => $parametres['texte']
				);
			}
			$this->CI->db->insert_batch('missives', $data_missives);

			// Historique
			$historique .= ' ont reçu une missive de Spam';
		}
		
		// Tract électoral (Parti politique)
		else if ($action_id == 36)
		{
			$commencees = $this->CI->db->where('tour_election !='.Bouzouk::Elections_Candidater)
									   ->count_all_results('mairie');

			if ($commencees)
			{
				// On récupère le chef et les membres du clan pour les enlever des tracts
				$clan_membres_ids = array($clan->chef_id);

				$query = $this->CI->db->select('joueur_id')
									 ->from('politiciens')
									 ->where('clan_id', $clan->id)
									 ->get();
				$politiciens = $query->result();

				foreach ($politiciens as $politicien)
					$clan_membres_ids[] = $politicien->joueur_id;

				// On va chercher les bouzouks
				if ($parametres['bouzouks_type'] == 'pas_vote')
				{
					$this->CI->db->select('id')
								 ->from('joueurs')
								 ->where('statut', Bouzouk::Joueur_Actif)
								 ->where('id NOT IN (SELECT joueur_id FROM elections_votes)')
								 ->where_not_in('id', $clan_membres_ids)
								 ->where('experience >=', $this->CI->bouzouk->config('elections_xp_voter'))
								 ->order_by('id', 'random');

					$historique = "100 bouzouks aléatoires qui n'ont pas voté";
				}

				$query = $this->CI->db->limit(100)
									  ->get();
				$joueurs = $query->result();

				// On prépare les missives
				$this->CI->load->library('lib_missive');
				$data_missives = array();
				$timbre        = $this->CI->lib_missive->timbres(6); // petit timbre sympa
				$datetime      = bdd_datetime();

				// On envoit les tracts
				foreach ($joueurs as $joueur)
				{
					$data_missives[] = array(
						'expediteur_id'   => Bouzouk::Robot_Spam,
						'destinataire_id' => $joueur->id,
						'date_envoi'      => $datetime,
						'timbre'          => $timbre,
						'objet'           => $parametres['titre'],
						'message'         => $parametres['texte']
					);
				}
				$this->CI->db->insert_batch('missives', $data_missives);

				// Historique
				$historique .= ' ont reçu un Tract électoral';
			}
			else
				$historique = 'aucun effet car les élections sont finies';
		}
		
		// Note à la secrétaire (Syndicat)
		else if ($action_id == 37)
		{
			// On va chercher les bouzouks
			if ($parametres['bouzouks_type'] == 'chefs_syndicats')
			{
				$this->CI->db->select('chef_id AS id')
						  	 ->from('clans')
							 ->where('type', Bouzouk::Clans_TypeSyndicat)
							 ->where('chef_id !=', $clan->chef_id);

				$historique = "Les chefs des syndicats";
			}
			
			elseif ($parametres['bouzouks_type'] == 'membres_syndicat')
			{
				$this->CI->db->select('joueur_id AS id')
						  	 ->from('politiciens')
							 ->where('clan_id', $clan->id);

				$historique = "Les membres du syndicat";
			}
			
			elseif ($parametres['bouzouks_type'] == 'employes_non_syndiques')
			{
				$this->CI->db->select('joueur_id AS id')
						  	 ->from('employes')
							 ->where('entreprise_id', $clan->entreprise_id)
							 ->where('id NOT IN (SELECT joueur_id FROM politiciens p join clans c ON c.id = p.clan_id WHERE c.type = '.Bouzouk::Clans_TypeSyndicat.')')
							 ->where('id NOT IN (SELECT chef_id FROM clans WHERE type = '.Bouzouk::Clans_TypeSyndicat.')');

				$historique = "Les employés non syndiqués de la boîte";
			}
			
			elseif ($parametres['bouzouks_type'] == 'tous_employes')
			{
				$this->CI->db->select('joueur_id AS id')
						  	 ->from('employes')
							 ->where('entreprise_id', $clan->entreprise_id)
							 ->where('joueur_id !=', $clan->chef_id);

				$historique = "Les employés de la boîte";
			}

			$query = $this->CI->db->get();
			$joueurs = $query->result();

			// On prépare les missives
			$this->CI->load->library('lib_missive');
			$data_missives = array();
			$timbre        = $this->CI->lib_missive->timbres(6); // petit timbre sympa
			$datetime      = bdd_datetime();

			// On envoit les tracts
			foreach ($joueurs as $joueur)
			{
				$data_missives[] = array(
					'expediteur_id'   => $clan->chef_id,
					'destinataire_id' => $joueur->id,
					'date_envoi'      => $datetime,
					'timbre'          => $timbre,
					'objet'           => $parametres['titre'],
					'message'         => $parametres['texte']
				);
			}
			$this->CI->db->insert_batch('missives', $data_missives);

			// Historique
			$historique .= ' ont reçu une note de la secrétaire';
		}
				
		// Pillage compulsif (Organisation)
		else if ($action_id == 38)
		{
			$shops = array(
				'faim' => 'au Bouffzouk',
				'sante' => "à l'Indispenzouk",
				'stress' => 'au Luxezouk'
			);
			
			// Historique
			$historique = profil($parametres['joueur_id']).' ne peut pas accéder '.$shops[$parametres['shop']].' pendant 24h';
			
			// On envoit une notif au bouzouk ciblé
			$nom_clan = ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('un clan') : 'le clan '.couleur(form_prep($clan->nom));
			$this->CI->bouzouk->notification(224, array(couleur('['.$nom_action.']'), $nom_clan, $shops[$parametres['shop']]), $parametres['joueur_id']);
		}
		
		// Concurrence gênante (Organisation)
		else if ($action_id == 39)
		{			
			// Historique
			$historique = profil($parametres['joueur_id']).' ne peut plus vendre au marché noir pendant 24h';

			// On envoit une notif au bouzouk ciblé
			$nom_clan = ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('un clan') : 'le clan '.couleur(form_prep($clan->nom));
			$this->CI->bouzouk->notification(225, array(couleur('['.$nom_action.']'), $nom_clan), $parametres['joueur_id']);
		}

		// Malédiction du Schnibble (SDS)
		else if ($action_id == 40)
		{			
			// Historique
			$historique = profil($parametres['joueur_id']).' a reçu une malédiction du Schnibble';
			
			// On récupère la péremption de la malédiction
			$query = $this->CI->db->select('id, peremption')
								  ->from('objets')
								  ->where('id', 49)
								  ->get();
			$malediction = $query->row();
			
			// On ajoute l'objet dans la maison de la cible
			$this->CI->bouzouk->ajouter_objets($malediction->id, 1, $malediction->peremption, $parametres['joueur_id']);
			
			// On augmente la version du joueur
			$this->CI->bouzouk->augmente_version_session($parametres['joueur_id']);

			// On envoit une notif au bouzouk ciblé
			$nom_clan = ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('un clan') : 'le clan '.couleur(form_prep($clan->nom));
			$this->CI->bouzouk->notification(242, array(couleur('['.$nom_action.']'), $nom_clan), $parametres['joueur_id']);
		}

		// Recrutement d'alliéné (SDS)
		else if ($action_id == 41)
		{
			// Historique
			$historique = profil($parametres['joueur_id'])." se fait recruter à l'asile";
			 
			// On envoit une notif au bouzouk ciblé
			$nom_clan = ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('un clan') : 'le clan '.couleur(form_prep($clan->nom));
			$this->CI->bouzouk->notification(241, array(couleur('['.$nom_action.']'), $nom_clan), $parametres['joueur_id']);
		}

		else
			return;

		// Si le clan est caché, il faut ajouter un historique public
		if ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible && ! isset($historique_public))
			$historique_public = $historique;
		
		// On construit l'historique
		$historique = couleur(form_prep($clan->nom))." a lancé l'action ".$nom_action.' pour '.couleur($cout.' p.a').' : '.$historique;
		
		// On construit l'historique public
		if (isset($historique_public))
		{
			$nom_clan = ($clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('Un clan') : couleur(form_prep($clan->nom));
			$historique_public = $nom_clan." a lancé l'action ".$nom_action.' pour '.couleur($cout.' p.a').' : '.$historique_public;
		}

		else
			$historique_public = false;

		// On enregistre les historiques
		$this->historique($historique, $clan->id, true, $historique_public);
	}

	/* Gestion des actions */
	public function get_action($id){
		$query = $this->CI->db->from('clans_actions')->where('id', $id)->get();
		if($query->num_rows > 0){
			$action = $query->row();
			return $action;
		}
		else{
			return false;
		}
	}

	public function update_action($action){
		$this->CI->db->where('id', $action->id)->update('clans_actions', $action);
	}

	public function get_all_action(){
		$query = $this->CI->db->from('clans_actions')->order_by('clan_type')->get();
		$actions = $query->result();
		return $actions;
	}

	public function is_action($id){
		// $id contient un nombre ?
		if(!ctype_digit($id)){
			return false;
		}
		$query = $this->CI->db->select('id')->where('id', $id)->get('clans_actions');
		if($query->num_rows != 1){
			return false;
		}
		return true;
	}
}
	