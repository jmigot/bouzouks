<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : ce controleur est à hériter pour tous les controleurs du jeu, il vérifie si le joueur est connecté et si son statut lui permet d'accéder à la page demandée.
 *				 Il peut être à l'asile, en pause, en game over, etc., on doit donc vérifier qu'il a bien le droit de visiter la page demandée en fonction de son statut.
 *				 Eventuellement pour ajouter des tests on peut utiliser le constructeur de chaque classe, ou même les méthodes.
 *
 * Auteur      : Jean-Luc Migot (jl.migot@yahoo.fr)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord préalable de tous les auteurs ainsi
 * que l'application des conditions données lors de cet accord.
 */

class MY_Controller extends CI_Controller
{
	protected $controleur;
	protected $methode;

	private $ip_bannies = array(
		'xxx',
		'xxx',
	);

    public function __construct()
	{
        parent::__construct();

        // IP bannie
		if (in_array($this->input->ip_address(), $this->ip_bannies))
			exit('Cette connexion est bannie du site');

		// Debug
		//if (ENVIRONMENT != 'production')
			//$this->output->enable_profiler(true);

        // On récupère le nom du controleur et la méthode appelée
        $this->controleur = $this->uri->segment(1);
        $this->methode    = $this->uri->segment(2);

		// --------------------------------------------------
        //		Mises à jour de la session
        // --------------------------------------------------
        // Le statut peut être changé par un admin (banni), les struls peuvent changer en fonction des ventes du marché noir,
        // le joueur peut hériter d'une entreprise, avoir une promotion ou se faire virer, etc.
        if ($this->session->userdata('connecte') !== false)
        {
			// Protection contre le swap de sessions par le serveur
			if ( ! $this->session->userdata('admin_connecte') === true && $this->controleur != 'joueur' && $this->methode != 'deconnexion' && $this->input->cookie('joueur_id') != $this->session->userdata('id'))
			{
				redirect('joueur/deconnexion');
			}

			// On met à jour le champ connecte pour indiquer que le joueur est toujours connecte
			// Si on est connecté sur le compte depuis l'interface admin, on zappe
			if ( ! $this->session->userdata('admin_connecte'))
			{
				$this->db->set('connecte', bdd_datetime())
						 ->where('id', $this->session->userdata('id'))
						 ->update('joueurs');
			}

			// Infos classiques
			$query = $this->db->select('id, fb_id, statut, date_statut, raison_statut, struls, fragments, faim, sante, stress, experience, points_action, force, charisme, intelligence, rang, notes_controuilles, version_session')
						  ->from('joueurs')
						  ->where('id', $this->session->userdata('id'))
						  ->get();
			$joueur = $query->row();

			// On regarde si une mise à jour est nécessaire puis on met à jour la session
			$mise_a_jour = ($this->session->userdata('version_session') != $joueur->version_session);
			$this->session->set_userdata($joueur);

			// Si une mise à jour de la session est nécessaire
			if ($mise_a_jour)
			{
				// On va chercher pas mal d'infos
				$query = $this->db->select('id, fb_id, pseudo, email, date_de_naissance, commentaire, date_inscription, statut, date_statut, raison_statut, sexe, perso, adresse, interdit_missives,
											interdit_tchat, interdit_plouk, interdit_avatar, duree_asile')
								  ->from('joueurs')
								  ->where('id', $this->session->userdata('id'))
								  ->get();
				$session = $query->row();

				// On récupère le groupe
				$query = $this->db->select('group_id')
								  ->from('tobozon_users')
								  ->where('id', $this->session->userdata('id'))
								  ->get();
				$session->tobozon_group_id = $query->row()->group_id;

				// On va chercher les infos des jobs/entreprises
				$session->chef_entreprise     = false;
				$session->employe             = false;
				$session->entreprise_id       = false;
				$session->syndicats_autorises = false;

				// Infos entreprises
				$query = $this->db->select('id')
								  ->from('entreprises')
								  ->where('chef_id', $this->session->userdata('id'))
								  ->get();

				// Si le joueur est patron d'entreprise
				if ($query->num_rows() > 0)
				{
					$entreprise               = $query->row();
					$session->chef_entreprise = true;
					$session->entreprise_id   = $entreprise->id;

					// On va chercher la liste des employés
					$query = $this->db->select('id')
									  ->from('employes')
									  ->where('entreprise_id', $entreprise->id)
									  ->get();
					$nb_employes = $query->num_rows();

					$session->nb_employe      = $nb_employes;
				}

				else
				{
					// Infos employés
					$query = $this->db->select('em.entreprise_id, en.syndicats_autorises')
									  ->from('employes em')
									  ->join('entreprises en', 'en.id = em.entreprise_id')
									  ->where('em.joueur_id', $this->session->userdata('id'))
									  ->get();

					// Si le joueur est employé
					if ($query->num_rows() > 0)
					{
						$employe                      = $query->row();
						$session->employe             = true;
						$session->entreprise_id       = $employe->entreprise_id;
						$session->syndicats_autorises = $employe->syndicats_autorises;
					}
				}

				// On regarde si le joueur est maudit
				$session->maudit = $this->db->where('joueur_id', $this->session->userdata('id'))
											->where('objet_id', 49)
											->count_all_results('maisons');

				// On regarde si le joueur est maire de la ville
				$query = $this->db->select('tour_election, maire_id')
								  ->from('mairie')
								  ->get();
				$mairie = $query->row();
				$session->maire = ($mairie->maire_id == $this->session->userdata('id'));

				// On regarde si le joueur a déjà voté
				$query = $this->db->select('j.id, j.pseudo')
								  ->from('elections_votes e_v')
								  ->join('joueurs j', 'j.id = e_v.candidat_id')
								  ->where('e_v.joueur_id', $this->session->userdata('id'))
								  ->where('e_v.tour', $mairie->tour_election)
								  ->get();

				if ($query->num_rows() > 0)
				{
					$candidat                      = $query->row();
					$session->a_vote               = true;
					$session->vote_candidat_id     = $candidat->id;
					$session->vote_candidat_pseudo = $candidat->pseudo;
				}

				else
				{
					$session->a_vote               = false;
					$session->vote_candidat_id     = '';
					$session->vote_candidat_pseudo = '';
				}

				// On regarde si le joueur a des droits d'édition de messages sur le tobozon
				$query = $this->db->select('g_mod_edit_users')
								  ->from('tobozon_users t_u')
								  ->join('tobozon_groups t_g', 't_g.g_id = t_u.group_id')
								  ->where('t_u.id', $this->session->userdata('id'))
								  ->get();

				if ($query->num_rows() == 0)
					$session->moderateur_tobozon = false;

				else
				{
					$tobozon = $query->row();
					$session->moderateur_tobozon = ($tobozon->g_mod_edit_users == 1);
				}

				// On regarde si le joueur est en train de mendier
				$session->mendiant = $this->db->where('joueur_id', $this->session->userdata('id'))
											  ->count_all_results('mendiants') > 0;

				// On regarde si le joueur est en train de jouer une partie de plouk
				$this->load->library('lib_plouk');

				$query = $this->db->select('id')
								  ->from('plouk_parties')
								  ->where('(createur_id = '.$this->session->userdata('id').' OR adversaire_id = '.$this->session->userdata('id').')')
								  ->where_in('statut', array(Lib_plouk::Proposee, Lib_plouk::Attente, Lib_Plouk::EnCours))
								  ->get();
				$session->plouk_id = false;

				if ($query->num_rows() == 1)
				{
					$partie = $query->row();
					$session->plouk_id = $partie->id;
				}

				// On va chercher les classements du joueur
				$query = $this->db->select('type, position, valeur, evolution')
								  ->from('classement_joueurs')
								  ->where('joueur_id', $this->session->userdata('id'))
								  ->get();
				$classements = $query->result();
				$session->classements = array();

				foreach ($classements as $classement)
				{
					$session->classements[$classement->type] = array($classement->position, $classement->valeur, $classement->evolution);
				}

				// On va chercher les infos clans du joueur
				$session->clan_id = $session->clan_grade = $session->clan_invisible = array(Bouzouk::Clans_TypeSyndicat => false, Bouzouk::Clans_TypeOrganisation => false, Bouzouk::Clans_TypePartiPolitique => false);
				$session->nb_clans = 0;
				$session->chef_clan = false;

				$query = $this->db->select('id, type')
								  ->from('clans')
								  ->where('chef_id', $this->session->userdata('id'))
								  ->get();

				// Si il est chef d'un clan
				if ($query->num_rows() > 0)
				{
					$clan = $query->row();
					$session->clan_id[$clan->type] = $clan->id;
					$session->clan_grade[$clan->type] = Bouzouk::Clans_GradeChef;
					$session->nb_clans++;
					$session->chef_clan = true;
				}

				$query = $this->db->select('c.id, c.type, p.grade, p.invisible')
								  ->from('politiciens p')
								  ->join('clans c', 'c.id = p.clan_id')
								  ->where('p.joueur_id', $this->session->userdata('id'))
								  ->get();

				// Si il est membre d'un clan
				if ($query->num_rows() > 0)
				{
					foreach ($query->result() as $clan)
					{
						$session->clan_id[$clan->type] = $clan->id;
						$session->clan_grade[$clan->type] = $clan->grade;
						$session->clan_invisible[$clan->type] = $clan->invisible == '1';
						$session->nb_clans++;
					}
				}

				$query = $this->db->select('id')
								  ->from('convocations_moderation')
								  ->where('convoque_id', $this->session->userdata('id'))
								  ->where('etat', 1)
								  ->get();

				$session->convocation_id = false;

				// Si il est dans une convocation
				if ($query->num_rows() > 0)
				{
					$convocation = $query->row();
					$session->convocation_id = $convocation->id;
				}

				// --------- Event RP Zombies ------------

				$query = $this->db->select('nb_morsure')
								  ->from('event_joueurs_zombies')
								  ->where('joueur_id', $this->session->userdata('id'))
								  ->get();

				// Si il est dans une morsure
				if ($query->num_rows() > 0)
				{
					$event_zomies = $query->row();
					$session->event_zomies_mordu = true;
					$session->event_zomies_morsures = $event_zomies->nb_morsure;

					$session->perso = 'zombi/'.$session->perso;
				}
				else
				{
					$session->event_zomies_mordu = false;
				}

				// --------- Event RP Zombies ------------

				// --------- Event bouf'tête -------------

				$session->bouf_tete = $this->bouzouk->est_infecte($this->session->userdata('id'));
				// Si le joueur est infecté
				if($session->bouf_tete == 1){
					// On modifie le chemin des images des persos du joueur
					$session->perso = 'zombi/'.$session->perso;
				}
				// ------- Event Mlboobz --------
				$session->mlbobz = $this->bouzouk->est_maudit_mlbobz($this->session->userdata('id'));
				if($session->mlbobz == TRUE){
					$session->perso = 'rp_zoukette/'.$session->perso;
				}
				// On enregistre tout
				$this->session->set_userdata($session);
			}
        }

        // Visiteur
        else
        {
			// On met à jour la date de visite pour l'affichage du nombre de visiteurs
			$existant = $this->db->where('ip', $this->input->ip_address())
								 ->count_all_results('visiteurs');

			// S'il n'existait pas d'enregistrement, on le rajoute
 			if ($existant == 0)
 			{
 				$data_visiteurs = array(
 					'ip'   => $this->input->ip_address(),
 					'date' => bdd_datetime()
 				);
 				$this->db->insert('visiteurs', $data_visiteurs);
 			}

 			// Sinon on met à jour la date
 			else
 			{
				$this->db->set('date', bdd_datetime())
						 ->where('ip', $this->input->ip_address())
						 ->update('visiteurs');
 			}
        }

        // Aucun controleur
        if ($this->controleur == false)
        {
			$this->controleur = 'site';
			$this->methode    = 'accueil';
		}

		else if ($this->methode == false)
        {
			$this->methode    = 'index';
		}

		if ($this->bouzouk->is_moderateur())
        	$this->load->library('lib_staff');

		// --------------------------------------------------
        //		Pages publiques
        // --------------------------------------------------
		if ($this->controleur == 'site' OR ($this->controleur == 'gazette' AND in_array($this->methode, array('index', 'article_id', 'secret'))) OR ($this->controleur == 'webservices' AND $this->methode == 'recharger_rumeurs'))
 		{
			// Si le joueur est connecté et demande l'accueil, on redirige vers l'accueil des joueurs
			if ($this->session->userdata('connecte') !== false AND $this->controleur == 'site' AND $this->methode == 'accueil')
				redirect('joueur');

			// On évite tous les tests suivants
			return;
 		}

 		else if ($this->controleur == 'visiteur')
 		{
			// Si le joueur est connecté, on redirige
			if ($this->session->userdata('connecte'))
			{
				$this->session->set_userdata('flash_echec', 'Cette page est réservée aux visiteurs');
				redirect('joueur');
			}

			// Sinon on évite tous les tests suivants
			return;
		}

		// A partir d'ici, le joueur doit être connecté
		if ($this->session->userdata('connecte') === false)
		{
			$this->session->set_userdata('flash_echec', 'Cette page est réservée aux joueurs connectés');
			redirect();
		}

		// Page du staff demandée
		if ($this->uri->segment(1) == 'staff')
		{
			// Spécial pour revenir à son compte depuis une connexion sur un autre compte
			if ($this->session->userdata('admin_connecte') && $this->uri->segment(2) == 'connexion_bouzouk' && $this->uri->segment(3) == 'revenir_compte')
				return;

			// Accueil : autorisé pour tout le monde au minimum modérateur
			if ($this->uri->segment(2) == 'accueil' && $this->bouzouk->is_moderateur())
				return;

			// Gestion des droits de chaque page du staff
			if ($this->bouzouk->is_staff_autorise($this->uri->segment(2), $this->uri->segment(3)))
				return;

			show_404();
		}

		// Webservice demandé ou admin connecté sur un compte = peut voir n'importe quelle page
        if ($this->controleur == 'webservices' || $this->session->userdata('admin_connecte') === true)
			return;

		// --------------------------------------------------
        //		Convoqué
        // --------------------------------------------------
		if ($this->session->userdata('convocation_id'))
		{
			$autorisations = array(
				'joueur'	  => array('deconnexion'),
				'convocation' => array('index'),
			);

			if ( ! $this->page_autorisee($autorisations))
				redirect('convocation/index/'.$this->session->userdata('convocation_id'));
		}

		// --------------------------------------------------
        //		Joueur normal
        // --------------------------------------------------
		else if ($this->session->userdata('statut') == Bouzouk::Joueur_Actif)
      	{
			// Certaines pages sont interdites à ce statut
			$interdictions = array(
				'joueur'      => array('choix_perso', 'asile', 'en_pause', 'game_over', 'reprendre_asile', 'reprendre_pause', 'recommencer_partie'),
				'controuille' => array('index', 'controuille1_debut', 'controuille1', 'controuille2_debut', 'controuille1'),
				'mendiants'   => array('machine_a_cafe'),
			);

			// Si le joueur n'est pas maire de la ville (sauf pour les admins)
			if ( ! $this->bouzouk->is_maire() AND ! $this->bouzouk->is_admin() AND $this->controleur == 'mairie' AND $this->methode != 'index')
				redirect('joueur/accueil');

			// Si la gestion de la gazette est demandée, il faut etre journaliste
			if (($this->controleur == 'gazette' AND $this->methode != 'index') || $this->controleur == 'webservices_gazette')
			{
				if ( ! $this->bouzouk->is_journaliste())
					redirect('joueur/accueil');
			}

			// Si la gestion des piges est demandée, il faut etre journaliste
			if ($this->controleur == 'piges' AND $this->methode != 'index')
			{
				if ( ! $this->bouzouk->is_journaliste())
					redirect('joueur/accueil');
			}

			// Si un modo/admin demande l'asile, le tchat chômeurs ou le tchat mendiant, on autorise
			if ((($this->controleur == 'joueur' && $this->methode == 'asile') || ($this->controleur == 'anpe' AND $this->methode == 'machine_a_cafe') || ($this->controleur == 'mendiants' AND $this->methode == 'machine_a_cafe')) AND
				$this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurProfils))
				return;

			if ($this->page_interdite($interdictions))
				redirect('joueur/accueil');

			// --------------------------------------------------
			//		Chômeur
			// --------------------------------------------------
			if ( ! $this->session->userdata('employe') AND ! $this->session->userdata('chef_entreprise'))
			{
				$interdictions = array(
					'entreprises' => '*',
					'recrutement' => '*',
					'boulot'      => '*'
				);

				$autorisations = array(
					'entreprises' => array('creer')
				);

				if ( ! $this->page_autorisee($autorisations) && $this->page_interdite($interdictions))
					redirect('joueur/accueil');
			}

			// --------------------------------------------------
			//		Employé
			// --------------------------------------------------
			else if ($this->session->userdata('employe'))
			{
				$interdictions = array(
					'recrutement'      => '*',
					'anpe'             => '*',
					'entreprises'      => '*',
				);

				if ($this->page_interdite($interdictions))
					redirect('joueur/accueil');
			}

			// --------------------------------------------------
			//		Chef d'entreprise
			// --------------------------------------------------
			else if ($this->session->userdata('chef_entreprise'))
			{
				$interdictions = array(
					'entreprises' => array('creer'),
					'boulot'      => '*',
					'anpe'        => '*'
				);

				if ($this->page_interdite($interdictions))
					redirect('joueur/accueil');
			}
      	}

		// --------------------------------------------------
        //		Etudiant
        // --------------------------------------------------
      	else if ($this->session->userdata('statut') == Bouzouk::Joueur_Etudiant)
      	{
			$autorisations = array();

			// Controuille 1
			if ((string)$this->session->userdata('notes_controuilles') == '')
			{
				$autorisations = array(
					'joueur'      => array('deconnexion'),
					'mon_compte'  => array('index', 'changer_mot_de_passe', 'changer_email', 'changer_email_confirmation'),
					'controuille' => array('index', 'controuille1_debut', 'controuille1')
				);
			}

			// Controuille 2
			else
			{
				$autorisations = array(
					'joueur'      => array('deconnexion'),
					'mon_compte'  => array('index', 'changer_mot_de_passe', 'changer_email', 'changer_email_confirmation'),
					'controuille' => array('index', 'controuille2_debut', 'controuille2')
				);
			}

			if ( ! $this->page_autorisee($autorisations))
				redirect('controuille');
      	}

		// --------------------------------------------------
        //		Choix personnage
        // --------------------------------------------------
      	else if ($this->session->userdata('statut') == Bouzouk::Joueur_ChoixPerso)
      	{
			$autorisations = array(
				'joueur'     => array('choix_perso', 'deconnexion'),
				'mon_compte' => array('index', 'changer_mot_de_passe', 'changer_email', 'changer_email_confirmation'),
			);

			if ( ! $this->page_autorisee($autorisations))
				redirect('joueur/choix_perso');
      	}

		// --------------------------------------------------
        //		Asile
        // --------------------------------------------------
		else if ($this->session->userdata('statut') == Bouzouk::Joueur_Asile)
		{
			$autorisations = array(
				'joueur'            => array('asile', 'reprendre_asile', 'deconnexion', 'recrute'),
				'mon_compte'        => array('index', 'changer_mot_de_passe', 'changer_email', 'changer_email_confirmation', 'mettre_en_pause'),
				'missives'          => '*',
				'historique'        => '*',
				'factures'          => '*',
				'communaute'        => '*',
				'plouk'             => '*',
				'webservices_plouk' => '*'
			);

			if ( ! $this->page_autorisee($autorisations))
				redirect('joueur/asile');
		}

		// --------------------------------------------------
        //		En pause
        // --------------------------------------------------
		else if ($this->session->userdata('statut') == Bouzouk::Joueur_Pause)
		{
			$autorisations = array(
				'joueur'     => array('en_pause', 'reprendre_pause', 'deconnexion'),
				'mon_compte' => array('index', 'changer_mot_de_passe', 'changer_email', 'changer_email_confirmation'),
				'communaute' => '*'
			);

			if ( ! $this->page_autorisee($autorisations))
				redirect('joueur/en_pause');
		}

		// --------------------------------------------------
        //		Game over
        // --------------------------------------------------
		else if ($this->session->userdata('statut') == Bouzouk::Joueur_GameOver)
		{
			$autorisations = array(
				'joueur'     => array('game_over', 'recommencer_partie', 'deconnexion'),
				'mon_compte' => array('index', 'changer_mot_de_passe', 'changer_email', 'changer_email_confirmation')
			);

			if ( ! $this->page_autorisee($autorisations))
				redirect('joueur/game_over');
		}

		// --------------------------------------------------
        //		Banni
        // --------------------------------------------------
 		else if ($this->session->userdata('statut') == Bouzouk::Joueur_Banni)
		{
			$autorisations = array(
				'joueur' => array('deconnexion'),
			);

			if ( ! $this->page_autorisee($autorisations))
				redirect('joueur/deconnexion');
		}
    }

    public function page_autorisee($autorisations)
    {
		return in_array($this->controleur, array_keys($autorisations)) AND
			   (
   				   $autorisations[$this->controleur] == '*' OR
				   in_array($this->methode, $autorisations[$this->controleur])
			   );
    }

    public function page_interdite($interdictions)
    {
		return in_array($this->controleur, array_keys($interdictions)) AND
			   (
				   $interdictions[$this->controleur] == '*' OR
			       in_array($this->methode, $interdictions[$this->controleur])
			   );
	}

	public function succes($message, $title = null)
	{
		$this->session->set_userdata('flash_succes', $message);

		if (isset($title))
			$this->session->set_userdata('flash_succes_title', $title);
	}

	public function attention($message)
	{
		$this->session->set_userdata('flash_attention', $message);
	}

	public function echec($message, $title = null)
	{
		$this->session->set_userdata('flash_echec', $message);

		if (isset($title))
			$this->session->set_userdata('flash_echec_title', $title);
	}
}
