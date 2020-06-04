<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions de gestion de la maintenance
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : février 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Lib_maintenance
{
	private $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function classements_evolution_joueur($type_classement, $joueur_id, $nouvelle_position)
	{
		$query = $this->CI->db->select('position')
							  ->from('classement_joueurs')
							  ->where('joueur_id', $joueur_id)
							  ->where('type', $type_classement)
							  ->limit(1)
							  ->get();

		if ($query->num_rows() == 1)
		{
			$classement = $query->row();

			if ($classement->position > $nouvelle_position)
				return 'hausse';

			else if ($classement->position < $nouvelle_position)
				return 'baisse';

			return 'egal';
		}

		return 'hausse';
	}

	public function classements_evolution_entreprise($ancienne_position, $nouvelle_position)
	{
		if ($ancienne_position == 0 || $nouvelle_position < $ancienne_position)
			return 'hausse';

		if ($nouvelle_position > $ancienne_position)
			return 'baisse';

		return 'egal';
	}

	public function mettre_a_jour_topics_elections()
	{
		// On va chercher le tour actuel à la mairie
		$query = $this->CI->db->select('tour_election')
							  ->from('mairie')
							  ->get();
		$mairie = $query->row();

		// On va chercher la liste des candidats du tour actuel
		$query = $this->CI->db->select('e.id AS elections_id, j.pseudo')
							  ->from('elections e')
							  ->join('joueurs j', 'j.id = e.joueur_id')
							  ->where('tour', $mairie->tour_election)
							  ->get();
		$candidats = $query->result();

		// Pour chaque candidat
		foreach ($candidats as $candidat)
		{
			// On regarde si un topic de propagande existe pour lui
			$query = $this->CI->db->select('id')
								  ->from('tobozon_topics')
								  ->where('poster', $candidat->pseudo)
								  ->where('forum_id', 5)
								  ->order_by('posted')
								  ->limit(1)
								  ->get();

			if ($query->num_rows() == 1)
			{
				// On définit le lien du topic pour ce candidat
				$topic = $query->row();
				$this->CI->db->set('topic_id', $topic->id)
							 ->where('id', $candidat->elections_id)
							 ->update('elections');
			}
		}
	}

	public function activer_maintenance()
	{
		// On recopie le contenu du htaccess de la maintenance dans le htaccess actif
		$htaccess = file_get_contents(BASEPATH.'../.htaccess_maintenance');
		file_put_contents(BASEPATH.'../.htaccess', $htaccess);
	}

	public function desactiver_maintenance()
	{
		// On recopie le contenu du htaccess du site dans le htaccess actif
		$htaccess = file_get_contents(BASEPATH.'../.htaccess_site');
		file_put_contents(BASEPATH.'../.htaccess', $htaccess);
	}

	public function deconnecter_joueurs()
	{
		// On remet à 0 les version_session
		$this->CI->db->set('version_session', 0)
					 ->update('joueurs');

		// On supprime toutes les sessions
		array_map('unlink', glob(BASEPATH.'../sessions/sess_*'));
	}

	public function tests_site()
	{
		$verifications = '';

		// --------------------------------------------------
		// On cherche les quantités égales à 0 dans les maisons
		$nb_objets = $this->CI->db->where('quantite', 0)
								  ->count_all_results('maisons');
		$verifications[] = array(
			'texte'    => "Aucune quantité d'objets à 0 dans les maisons",
			'resultat' => ($nb_objets == 0)
		);

		// --------------------------------------------------
		// On cherche les quantités égales à 0 au marché noir
		$nb_objets = $this->CI->db->where('quantite', 0)
								  ->count_all_results('marche_noir');
		$verifications[] = array(
			'texte'    => "Aucune quantité d'objets à 0 au marché noir",
			'resultat' => ($nb_objets == 0)
		);

		// --------------------------------------------------
		// On vérifie que le dernier id de joueurs est le meme que celui de tobozon_users
		$query = $this->CI->db->select('id')
							  ->from('joueurs')
							  ->order_by('id', 'desc')
							  ->limit(1)
							  ->get();
		$table_joueurs = $query->row();

		$query = $this->CI->db->select('id')
							  ->from('tobozon_users')
							  ->order_by('id', 'desc')
							  ->limit(1)
							  ->get();
		$table_users = $query->row();

		$verifications[] = array(
			'texte'    => "Identifiants des joueurs du site et du tobozon synchronisés",
			'resultat' => ($table_joueurs->id == $table_users->id)
		);

		// --------------------------------------------------
		// On vérifie que les employés apparaissent une seule fois dans la table employés
		$nb_employes = $this->CI->db->count_all('employes');
		
		$query = $this->CI->db->select('DISTINCT(joueur_id)')
							  ->from('employes')
							  ->get();
		$nb_employes_distincts = $query->num_rows();

		$verifications[] = array(
			'texte'    => "Aucun employé n'est en double",
			'resultat' => ($nb_employes == $nb_employes_distincts)
		);

		// --------------------------------------------------
		// On vérifie que les patrons n'apparaissent jamais en tant qu'employés
		$query = $this->CI->db->select('e.chef_id')
							  ->from('entreprises e')
							  ->join('employes em', 'em.joueur_id = e.chef_id')
							  ->get();
		$nb = $query->num_rows();

		$verifications[] = array(
			'texte'    => "Aucun patron n'est employé en même temps",
			'resultat' => ($nb == 0)
		);

		// --------------------------------------------------
		// On vérifie qu'aucun objet_id des maisons est inexistant
		$query = $this->CI->db->select('o.id')
							  ->from('maisons m')
							  ->join('objets o', 'o.id = m.objet_id', 'left')
							  ->where('o.id IS NULL')
							  ->get();
		$nb = $query->num_rows();

		$verifications[] = array(
			'texte'    => "Aucun objet des maisons est inexistant",
			'resultat' => ($nb == 0)
		);

		// --------------------------------------------------
		// On vérifie qu'aucune missive n'existe pour des comptes robots
		$query = $this->CI->db->select('m.id')
							  ->from('missives m')
							  ->join('joueurs j', 'j.id = m.destinataire_id')
							  ->where_in('j.statut', array(Bouzouk::Joueur_Robot))
							  ->get();
		$nb = $query->num_rows();

		$verifications[] = array(
			'texte'    => "Aucune missive sur des comptes robots",
			'resultat' => ($nb == 0)
		);

		// --------------------------------------------------
		// On vérifie qu'aucun mot de passe n'est différent entre le site et le tobozon
		$query = $this->CI->db->select('id')
							  ->from('joueurs')
							  ->where('mot_de_passe != (SELECT password FROM tobozon_users WHERE tobozon_users.id = joueurs.id)')
							  ->get();
		$nb = $query->num_rows();

		$verifications[] = array(
			'texte'    => "Aucun mot de passe différent entre le site et le tobozon",
			'resultat' => ($nb == 0)
		);

		// --------------------------------------------------
		// On vérifie qu'il y a le même nombre de joueurs sur le site et sur le tobozon
		$verifications[] = array(
			'texte'    => "Même nombre de membres sur le site et sur le Tobozon",
			'resultat' => ($this->CI->db->count_all('joueurs') == $this->CI->db->count_all('tobozon_users'))
		);
		
		// --------------------------------------------------
		// Aucun objet du même joueur avec la même péremption en plusieurs exemplaires dans les maisons
		$query = $this->CI->db->select('id')
							  ->from('maisons')
							  ->group_by('joueur_id, objet_id, peremption')
							  ->having('COUNT(id) > 1')
							  ->get();
		
		$verifications[] = array(
			'texte'    => "Aucun objet du même joueur et de la même péremption en double dans les maisons",
			'resultat' => ($query->num_rows() == 0)
		);

		// --------------------------------------------------
		// Aucun objet du même joueur avec la même péremption en plusieurs exemplaires au marché noir
		$query = $this->CI->db->select('id')
							  ->from('marche_noir')
							  ->group_by('joueur_id, objet_id, peremption, prix')
							  ->having('COUNT(id) > 1')
							  ->get();

		$verifications[] = array(
			'texte'    => "Aucun objet du même joueur et de la même péremption en double au marché noir",
			'resultat' => ($query->num_rows() == 0)
		);
		
		// --------------------------------------------------
		// Tester présence du compte Guest sur le Tobozon (obligatoire pour rendre le Tobozon accessible aux visiteurs)
		$verifications[] = array(
			'texte'    => 'Compte "Guest" présent sur le Tobozon',
			'resultat' => ($this->CI->db->where('username', 'Guest')->count_all_results('tobozon_users'))
		);

		// --------------------------------------------------
		// Aucun joueur chef de plus d'un clan à la fois
		$query = $this->CI->db->select('id')
							  ->from('clans')
							  ->group_by('chef_id')
							  ->having('COUNT(id) > 1')
							  ->get();
		
		$verifications[] = array(
			'texte'    => "Aucun joueur chef de plus d'un clan à la fois",
			'resultat' => ($query->num_rows() == 0)
		);

		// --------------------------------------------------
		// Aucun joueur membre de plus de 3 clans à la fois
		$query = $this->CI->db->select('id')
							  ->from('politiciens')
							  ->group_by('joueur_id')
							  ->having('COUNT(id) > '.Bouzouk::Clans_NbClansMax)
							  ->get();
		
		$verifications[] = array(
			'texte'    => "Aucun joueur membre de plus de ".Bouzouk::Clans_NbClansMax." clans à la fois",
			'resultat' => ($query->num_rows() == 0)
		);

		// --------------------------------------------------
		// Aucun patron ou employé n'a de petite annonce en cours
		$query = $this->CI->db->select('pa.id')
							  ->from('petites_annonces pa')
							  ->join('employes em', 'em.joueur_id = pa.joueur_id')
							  ->join('entreprises en', 'en.chef_id = pa.joueur_id')
							  ->get();
		
		$verifications[] = array(
			'texte'    => "Aucun patron ou employé n'a de petite annonce en cours",
			'resultat' => ($query->num_rows() == 0)
		);

		// --------------------------------------------------
		// Aucune annonce de recrutement (entreprise, joueur) en double (en prenant en compte les deux façons de postuler)
		$query = $this->CI->db->select('id')
							  ->from('petites_annonces')
							  ->where('joueur_id IS NOT NULL')
							  ->where('entreprise_id IS NOT NULL')
							  ->group_by('joueur_id, entreprise_id')
							  ->having('COUNT(id) > 2')
							  ->get();
		
		$verifications[] = array(
			'texte'    => "Aucune annonce de recrutement (entreprise, joueur) en double",
			'resultat' => ($query->num_rows() == 0)
		);

		// --------------------------------------------------
		// Aucune annonce de recrutement (clan_id, joueur_id) en double
		$query = $this->CI->db->select('id')
							  ->from('clans_recrutement')
							  ->group_by('joueur_id, clan_id')
							  ->having('COUNT(id) > 1')
							  ->get();
		
		$verifications[] = array(
			'texte'    => "Aucune annonce de recrutement (clan, joueur) en double",
			'resultat' => ($query->num_rows() == 0)
		);

		// --------------------------------------------------
		// Aucun joueur membre de plus d'un clan du même type
		$query = $this->CI->db->select('p.id')
							  ->from('politiciens p')
							  ->join('clans c', 'c.id = p.clan_id')
							  ->group_by('p.joueur_id, c.type')
							  ->having('COUNT(p.id) > 1')
							  ->get();
		
		$verifications[] = array(
			'texte'    => "Aucun joueur membre de plus d'un clan du même type",
			'resultat' => ($query->num_rows() == 0)
		);

		return $verifications;
	}

	// Event bouf'tête

	/** fonction de maintenance de l'event
	* Les bouf'tête déparasités sont supprimés de la table sql
	* et les joueurs perdent leur immunité
	**/
	public function update_bouf_tete(){
		// On retire les bouf'tête morts
		$query = $this->CI->db->where('immun', 1)
							  ->delete('event_bouf_tete');
		$query = $this->CI->db->affected_rows();
		$verifications[] = "Le nombre de bouf'tête mort est de $query";
		// On remet les enfants à 2
		$query = $this->CI->db->set('nb_petit', 2)->update('event_bouf_tete');
		$query = $this->CI->db->affected_rows() * 2;
		$verifications[] = "Les bouf'tête se sont multipliés. Il sont désormais $query de plus";
		return $verifications;
	}

	public function update_event_mlbobz(){
		// On retire les joueurs guéris
		$this->CI->db->where('immun', 1)->delete('event_mlbobz');
		// On remet le nombre de malédiction possible à 2
		$this->CI->db->set('nb_malediction', 2)->update('event_mlbobz');
	}
}

