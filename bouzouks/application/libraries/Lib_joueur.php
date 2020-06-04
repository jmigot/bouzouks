<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions de gestion des joueurs
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : décembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Lib_joueur
{
	private $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function virer_elections($joueur_id)
	{
		// On regarde si le bouzouk était candidat aux élections et pas encore au tour 2
		$candidat = $this->CI->db->where('joueur_id', $joueur_id)
								 ->where_in('tour', array(Bouzouk::Elections_Candidater, Bouzouk::Elections_Tour1))
								 ->count_all_results('elections');
		
		// On va chercher à quel tour des élections on est
		$query = $this->CI->db->select('tour_election')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();

		// Si il est au élection et qu'on est en tour 1 (ou au candidature) il doit perdre l'xp, sinon il l'a déjà perdu
		if ($candidat == 1 && $mairie->tour_election <= Bouzouk::Elections_Tour1)
		{
			$perte_xp = $this->CI->bouzouk->config('elections_perte_xp_tour2');
			$this->CI->bouzouk->retirer_experience($perte_xp, $joueur_id);

			// On ajoute à l'historique
			$this->CI->bouzouk->historique(187, null, array($perte_xp), $joueur_id);
		}

		// On récupère les infos de la mairie
		$query = $this->CI->db->select('tour_election')
							  ->from('mairie')
							  ->get();
		$mairie = $query->row();

		// On supprime le joueur des élections, i.e on le recule d'un tour si il est au tour de la mairie
		$this->CI->db->set('tour', Bouzouk::Elections_Banni)
					 ->where('joueur_id', $joueur_id)
					 ->where('tour', $mairie->tour_election)
					 ->update('elections');
		// Si l'event Bouf'tête est actif
		if($this->CI->bouzouk->etat_bouf_tete() == 'start'){
			//Si le joueur est boubouch, on place Pincemi en alpha
			if($joueur_id == 34){
				$this->CI->db->set('candidat', 2)->where('id_joueur', 34)->update('event_bouf_tete');
				$this->CI->db->set('candidat', 1)->where('id_joueur', 624)->update('event_bouf_tete');
				 
			}
			//Si le joueur est pincemi, on arrête l'event
			if($joueur_id == 624){
				$this->CI->bouzouk->stop_event_bouf_tete();
			}
		}
	}

	public function mettre_pause($joueur_id, $payer_taxes = 1)
	{
		// On met le joueur en pause
		$this->CI->db->set('statut', Bouzouk::Joueur_Pause)
					 ->set('date_statut', bdd_datetime())
					 ->set('raison_statut', '')
					 ->set('statut_staff_id', null)
					 ->set('pause_payer_taxes', $payer_taxes)
					 ->where('id', $joueur_id)
					 ->update('joueurs');

		// Si le joueur était à l'asile sans raison, on lui remet 50 de stress
		//if ($this->session->userdata('statut') == Bouzouk::Joueur_Asile AND $this->session->userdata('raison_statut') == '')
		//	$data_joueur['stress'] = 50;

		// On change son statut sur le tobozon
		$this->CI->db->set('title', 'Pause')
					 ->where('id', $joueur_id)
					 ->update('tobozon_users');
					 
		// On supprime le joueur des élections
		$this->virer_elections($joueur_id);

		// On supprime le joueur des mendiants
		$this->CI->db->where('joueur_id', $joueur_id)
					 ->delete('mendiants');

		// On libère les annonces du chômeur
		$this->CI->load->library('lib_entreprise');
		$this->CI->lib_entreprise->liberer_annonces($joueur_id);

		$this->CI->bouzouk->augmente_version_session($joueur_id);

		// On ajoute à l'historique
		$this->CI->bouzouk->historique(188, 189, array(), $joueur_id);
	}

	public function mettre_asile($joueur_id, $raison = '', $duree = 24, $staff_id = null, $sanction = true)
	{
		if ( ! ctype_digit((string)$duree) || $duree == 0)
			$duree = 24;

		// On met le joueur à l'asile
		$this->CI->db->set('statut', Bouzouk::Joueur_Asile)
					 ->set('date_statut', bdd_datetime())
					 ->set('raison_statut', $raison)
					 ->set('duree_asile', $duree)
					 ->set('statut_staff_id', $staff_id)
					 ->where('id', $joueur_id)
					 ->update('joueurs');

		// On change son statut sur le tobozon
		$statut_existe = $this->CI->db->where('title !=', '')
		 							  ->where('id', $joueur_id)
									  ->count_all_results('tobozon_users');

		if ( ! $statut_existe)
		{
			$this->CI->db->set('title', 'Asile')
						 ->where('id', $joueur_id)
						 ->update('tobozon_users');
		}
					 
		// On supprime le joueur des élections
		if ($sanction)
			$this->virer_elections($joueur_id);

		// On supprime les petites annonces
		$this->CI->db->set('joueur_id', null)
					 ->where('joueur_id', $joueur_id)
					 ->where('type', Bouzouk::PetitesAnnonces_Patron)
					 ->update('petites_annonces');

		$this->CI->db->where('joueur_id', $joueur_id)
					 ->where('type', Bouzouk::PetitesAnnonces_Chomeur)
					 ->delete('petites_annonces');

		$this->CI->db->where('joueur_id', $joueur_id)
					 ->delete('chomeurs');
					 
		// On le retire des mendiants
		$this->CI->db->where('joueur_id', $joueur_id)
					 ->delete('mendiants');

		// Si le joueur était maire ou suppléant
		if ($sanction)
		{
			$query = $this->CI->db->select('maire_id, maire_suppleant_id')
								  ->from('mairie')
								  ->get();
			$mairie = $query->row();

			if (in_array($joueur_id, array($mairie->maire_id, $mairie->maire_suppleant_id)))
			{
				$this->CI->load->library('lib_mairie');
				$this->CI->lib_mairie->verifier_maire_et_suppleant();
			}

			// On enlève de l'expérience
			if ($raison == '' && $staff_id == null)
			{
				$perte_xp = $this->CI->bouzouk->config('joueur_perte_xp_asile');
				$this->CI->bouzouk->retirer_experience($perte_xp, $joueur_id);

				// On ajoute à l'historique
				$this->CI->bouzouk->historique(191, 190, array($perte_xp), $joueur_id);
			}

			// Internement par un clan
			else if ($staff_id == null)
			{
				$perte_xp = 2;
				$this->CI->bouzouk->retirer_experience($perte_xp, $joueur_id);

				// On ajoute à l'historique
				$this->CI->bouzouk->historique(192, 190, array($perte_xp), $joueur_id);
			}

			// Internement par un modo/admin
			else
			{
				$perte_xp = $this->CI->bouzouk->config('joueur_perte_xp_asile_moderation');
				$this->CI->bouzouk->retirer_experience($perte_xp, $joueur_id);

				// On ajoute à l'historique
				$this->CI->bouzouk->historique(193, 190, array($raison, $perte_xp), $joueur_id);
			}
		}

		$this->CI->bouzouk->augmente_version_session($joueur_id);
	}

	public function mettre_game_over($joueur_id)
	{
		// Si le joueur était patron d'entreprise
		$query = $this->CI->db->select('id, nom')
							  ->from('entreprises')
							  ->where('chef_id', $joueur_id)
							  ->get();

		if ($query->num_rows() > 0)
		{
			$entreprise = $query->row();
			$this->CI->load->library('lib_entreprise');
			$this->CI->lib_entreprise->demission($entreprise->id);
		}

		// Si le joueur était employe
		$query = $this->CI->db->select('j.id, j.pseudo')
							  ->from('employes em')
							  ->join('entreprises en', 'en.id = em.entreprise_id')
							  ->join('joueurs j', 'j.id = en.chef_id')
							  ->where('em.joueur_id', $joueur_id)
							  ->get();

		if ($query->num_rows() > 0)
		{
			$chef = $query->row();

			// On débauche l'employé
			$this->CI->db->where('joueur_id', $joueur_id)
						 ->delete('employes');

			// On récupère les infos de l'employe
			$query = $this->CI->db->select('id, pseudo')
								  ->from('joueurs')
								  ->where('id', $joueur_id)
								  ->get();
			$employe = $query->row();

			// On envoie une missive au patron
			$message  = "	Bonjour $chef->pseudo\n\n";
			$message .= "Nous avons le regret de t'annoncer le départ en retraite anticipée de ton employé ".profil($employe->id, $employe->pseudo).".\n\n";
			$message .= "Poussé par la faim, il a préféré partir pour d'autres contrées, en laissant derrière lui job et amis.\n";
			$message .= "Il ne figurera donc plus parmis tes employés.\n\n";
			$message .= "	Amicalement, le Ministère des départs en retraite de Vlurxtrznbnaxl.";

			$this->CI->load->library('lib_missive');
			$this->CI->lib_missive->envoyer_missive(Bouzouk::Robot_Emploi, $chef->id, 'Un employé en moins...', $message);
		}

		// Si le joueur était maire ou suppléant
		$query = $this->CI->db->select('maire_id, maire_suppleant_id')
							  ->from('mairie')
							  ->get();
		$mairie = $query->row();

		if (in_array($joueur_id, array($mairie->maire_id, $mairie->maire_suppleant_id)))
		{
			$this->CI->load->library('lib_mairie');
			$this->CI->lib_mairie->verifier_maire_et_suppleant();
		}

		$this->CI->load->library('lib_clans');

		// Si le joueur était chef de clan
		$query = $this->CI->db->select('id')
						  	  ->from('clans')
						  	  ->where('chef_id', $joueur_id)
						  	  ->get();

		if ($query->num_rows() > 0)
		{
			$clan = $query->row();
			$this->CI->lib_clans->leguer_clan($clan->id);
		}

		// On quitte tous les clans
		$query = $this->CI->db->select('clan_id')
						  ->from('politiciens')
						  ->where('joueur_id', $joueur_id)
						  ->get();
		$clans = $query->result();

		foreach ($clans as $clan)
			$this->CI->lib_clans->quitter_clan($clan->clan_id, $joueur_id);

		// On change son statut sur le tobozon
		$this->CI->db->set('title', 'Quête du Schnibble')
					 ->where('id', $joueur_id)
					 ->update('tobozon_users');
					 
		// On supprime ses codes aléatoires
		$this->CI->db->where('joueur_id', $joueur_id)
					 ->delete('codes_aleatoires');

		// On supprime ses donations
		$this->CI->db->where('donateur_id', $joueur_id)
					 ->or_where('joueur_id', $joueur_id)
					 ->delete('donations');

		// On le supprime des élections
		$this->CI->db->where('joueur_id', $joueur_id)
					 ->delete('elections');

		// On supprime ses factures
		$this->CI->db->where('joueur_id', $joueur_id)
					 ->delete('factures');

		// On supprime ses faillites
		$this->CI->db->where('joueur_id', $joueur_id)
					 ->delete('faillites');

		// On supprime ses numéros joués à la loterie
		$this->CI->db->where('joueur_id', $joueur_id)
					 ->delete('loterie');

		// On supprime ses missives
		$this->CI->db->set('expediteur_supprime', 1)
					 ->where('expediteur_id', $joueur_id)
					 ->update('missives');

		$this->CI->db->set('destinataire_supprime', 1)
					 ->where('destinataire_id', $joueur_id)
					 ->update('missives');

		// On le retire des mendiants
		$this->CI->db->where('joueur_id', $joueur_id)
					 ->delete('mendiants');

		// On vide sa maison
		$this->CI->db->where('joueur_id', $joueur_id)
					 ->delete('maisons');

		// On supprime ses objets au marché noir
		$this->CI->db->where('joueur_id', $joueur_id)
					 ->delete('marche_noir');

		// On supprime tous les amis
		$this->CI->db->where('joueur_id', $joueur_id)
					 ->or_where('ami_id', $joueur_id)
					 ->delete('amis');

		// On libère les annonces du chômeur
		$this->CI->load->library('lib_entreprise');
		$this->CI->lib_entreprise->liberer_annonces($joueur_id);

		// On supprime l'historique
		$this->CI->db->where('joueur_id', $joueur_id)
					 ->delete('historique');

		// On supprime le recrutement des clans
		$this->CI->db->where('joueur_id', $joueur_id)
					 ->delete('clans_recrutement');

		// Le parrainage n'est plus effectif (tant pis si les joueurs n'ont pas reçu de récompense)
		$this->CI->db->where('parrain_id', $joueur_id)
					 ->or_where('filleul_id', $joueur_id)
					 ->delete('parrainages');

		// On récupère les infos du joueur
		$query = $this->CI->db->select('experience')
							  ->from('joueurs')
							  ->where('id', $joueur_id)
							  ->get();
		$joueur = $query->row();

		// On passe le joueur en game over
		$this->CI->db->set('statut', Bouzouk::Joueur_GameOver)
					 ->set('date_statut', bdd_datetime())
					 ->set('raison_statut', '')
					 ->set('statut_staff_id', null)
					 ->set('struls', $this->CI->bouzouk->config('joueur_struls_depart'))
					 ->set('faim', $this->CI->bouzouk->config('joueur_faim_depart'))
					 ->set('sante', $this->CI->bouzouk->config('joueur_sante_depart'))
					 ->set('stress', $this->CI->bouzouk->config('joueur_stress_depart'))
					 ->set('experience', ceil($joueur->experience / 2.0))
					 ->set('points_action', 0)
					 ->set('force', 0)
					 ->set('charisme', 0)
					 ->set('intelligence', 0)
					 ->set('duree_asile', 0)
					 ->set('notes_controuilles', '')
					 ->set('plouk_stats', '0|0|0')
					 ->set('connecte', null)
					 ->where('id', $joueur_id)
					 ->update('joueurs');

		$this->CI->bouzouk->augmente_version_session($joueur_id);
	}

	public function bannir($joueur_id, $raison, $staff_id)
	{
		// On efface toute trace du joueur
		$this->mettre_game_over($joueur_id);

		// On change son statut sur le tobozon
		$this->CI->db->set('title', 'Banni')
					 ->where('id', $joueur_id)
					 ->update('tobozon_users');
					 
		// On le banni du site
		$this->CI->db->set('statut', Bouzouk::Joueur_Banni)
					 ->set('date_statut', bdd_datetime())
					 ->set('raison_statut', $raison)
					 ->set('statut_staff_id', $staff_id)
					 ->set('rang', Bouzouk::Rang_Aucun)
					 ->set('rang_description', '')
					 ->where('id', $joueur_id)
					 ->update('joueurs');

		$this->CI->bouzouk->augmente_version_session($joueur_id);
	}

	public function supprimer_joueur($joueur_id)
	{
		$this->mettre_game_over($joueur_id);

		// On supprimme le joueur du tobozon
		$this->CI->load->library('lib_tobozon');
		$this->CI->lib_tobozon->supprimer_joueur($joueur_id);

		// On supprime le joueur des parrainages
		$this->CI->db->set('parrain_id', null)
					 ->where('parrain_id', $joueur_id)
					 ->update('joueurs');

		// On supprime ses missives
		$this->CI->db->where('expediteur_id', $joueur_id)
					 ->or_where('destinataire_id', $joueur_id)
					 ->delete('missives');

		// On supprime le joueur
		$this->CI->db->where('id', $joueur_id)
					 ->delete('joueurs');
		// On supprime le joueur de la map
		$this->CI->load->library('vlux/vlux_factory');
		$this->CI->vlux_factory->supprimer_joueur($joueur_id);
	}

	public function get_joueur_info($joueur){
		// On va chercher les infos du joueur
		$query = $this->CI->db->select('id, fb_id, pseudo, mot_de_passe, email, date_de_naissance, commentaire, date_inscription, statut, date_statut, raison_statut, rang, sexe, perso, struls,
									faim, sante, stress, notes_controuilles, adresse, interdit_missives, interdit_tchat, tobozon_ids')
						  ->from('joueurs')
						  ->where($joueur)
						  ->get();
		if($query->num_rows == 0){
			return false;
		}
		$info = $query->row();
		return $info;
	}

	public function connecter($session){
		// On remplit une session pour le joueur avec les données récupérées de la requête
		$websocket_auth = md5($session->id.':'.$session->mot_de_passe);
		unset($session->mot_de_passe);
		$session->connecte = true;
		$session->version_session = -1;
		$session->bonneteau_gagnees = 0;
		$session->filtres_historique = array();
		$session->plouk_id = false;
		$this->CI->session->set_userdata($session);

		// On enregistre l'id dans le cookie pour faire des vérifications (cas de changement de fichiers de session : bug Stagaga)
		$this->CI->input->set_cookie('joueur_id', $this->CI->session->userdata('id'), 0);

		// On enregistre de quoi s'authentifier sur la websocket
		$this->CI->input->set_cookie('websocket_auth', $websocket_auth, 0);

		// On enregistre la connexion
		$this->CI->load->library('user_agent');

		// On vérifie qu'une entrée n'existe pas déjà
		$deja_present = $this->CI->db->where('joueur_id', $this->CI->session->userdata('id'))
								 ->where('user_agent', $this->CI->agent->agent_string())
								 ->where('ip', $this->CI->input->ip_address())
								 ->count_all_results('connexions');

		if ($deja_present == 0)
		{
			$data_connexions = array(
				'joueur_id'  => $session->id,
				'date'       => bdd_datetime(),
				'user_agent' => $this->CI->agent->agent_string(),
				'ip'         => $this->CI->input->ip_address()
			);
			$this->CI->db->insert('connexions', $data_connexions);
		}

		// On supprime le joueur des visiteurs du site
		$this->CI->db->where('ip', $this->CI->input->ip_address())
				 ->delete('visiteurs');

		// On connecte au tobozon
		$this->CI->load->library('lib_tobozon');
		$this->CI->lib_tobozon->connecter();

		// On enlève les mutex sur le site
		$this->CI->load->library('lib_gazette');
		$this->CI->lib_gazette->liberer_mutex_joueur($this->CI->session->userdata('id'));
	}

	public function assoc_compte_fb($id, $fb_id, $fb_mail){
		// On enregistre l'id fb et le mail
		$this->CI->db->set(array('fb_id'=>$fb_id, 'fb_mail'=>$fb_mail))->where('id', $id)->update('joueurs');
	}

	public function get_id_by_email($mail){
		$id = $this->CI->db->select('id')->where('email', $mail)->get('joueurs');
		if($id->num_rows>0){
			$id = $id->row();
			$id = $id->id;
			return $id;
		}
		else{
			return false;
		}
	}
 
	public function get_id_by_pseudo($pseudo_parrain){
		$query = $this->CI->db->select('id')
							  ->from('joueurs')
					 		  ->where('pseudo', $pseudo_parrain)
							  ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))
							  ->get();
		// Si le parrain n'existe pas
		if($query->num_rows() == 0){
			return NULL;
		}
		// Sinon, on retourne son id
		else{
			$parrain_id = $query->row()->id;
			return $parrain_id;
		}
	}

	private function is_banned_ip($ip){
		$query = $this->CI->db->select('raison_statut')
							  ->where('ip_inscription', $ip)
							  ->where('statut', Bouzouk::Joueur_Banni)
							  ->limit(1)
							  ->get('joueurs');
		if($query->num_rows() > 0){
			$ip_banned  = $query->row()->raison_statut;
			return $ip_banned;
		}
		else{
			return FALSE;
		}
	}

	private function get_nb_inscription_by_ip($ip){
		$query = $this->CI->db->where('ip_inscription', $ip)
							  ->count_all_results('joueurs');
		return $query;
	}

	public function check_info_inscription($data){
		$check_info = array(
			'erreur'=> array(
				'type' 		=> null,
				'message' 	=> null
				)
			);
		$parrain_id = $this->get_id_by_pseudo($data['parrain']);
		$data['parrain_id'] = $parrain_id;
		// On vérifie le pseudo du parrain
		if($data['parrain']){
			// Si le parrain n'existe pas
			if(!$parrain_id){
				$check_info['erreur'] = array('type'=>'parrain_id', 'message'=>"Le parrain n'existe pas ou n'est plus actif, redemande lui son pseudo exact");
				return $check_info;
			}
		}
		// On vérifie que l'ip n'est pas bannie
		$ip_banned = $this->is_banned_ip($data['ip']);
		if ($ip_banned)
		{
			$check_info['erreur'] = array(
				'type'		=>'ip_bannie',
				'message'	=>'Tu as été banni de Vlurxtrznblax pour la raison suivante :<br><br>&laquo; <span class="pourpre">'.$ip_banned." &raquo; </span><br><br>Si tu penses qu'il y a une erreur, <a href='".
						 	   site_url('site/team')."'>contacte un administrateur en privé</a>"
				);
			return $check_info;
		}
		// On vérifie le nombre d'inscription sur la même IP
		$nb_inscription = $this->get_nb_inscription_by_ip($data['ip']);
		// Plus de trois insciptions sur la même IP
		if($nb_inscription >= 3){
			$check_info['erreur'] = array(
				'type'		=> 'nb_inscription',
				'message'	=> "Il y a déjà plusieurs inscriptions sur cette connexion, nous limitions le nombre afin de détecter plus facilement les multi-comptes"
				);
			return $check_info;
		}
		// Si tout est bon, on renvoie les infos
		return $data;
	}

	public function inscription($data){
		// On enregistre le joueur sur le jeu
		$date = bdd_datetime();
		// Date de naissance
		if(!isset($data['birthday'])){
			// Inscription standart
			$data['birthday'] = $date;
		}
		$data_joueur = array(
			'fb_id'				 => $data['fb_id'],
			'pseudo'             => $data['pseudo'],
			'mot_de_passe'       => sha1($data['mot_de_passe']),
			'email'              => $data['email'],
			'fb_mail'			 => null,
			'commentaire'        => '',
			'raison_statut'      => '',
			'date_de_naissance'  => $data['birthday'],
			'date_inscription'   => $date,
			'ip_inscription'     => $data['ip'],
			'statut'             => Bouzouk::Joueur_Inactif,
			'date_statut'        => $date,
			'rang'               => Bouzouk::Rang_Aucun,
			'adresse'            => mt_rand(1, 9999).', rue des pochtrons',
			'faim'               => $this->CI->bouzouk->config('joueur_faim_depart'),
			'sante'              => $this->CI->bouzouk->config('joueur_sante_depart'),
			'stress'             => $this->CI->bouzouk->config('joueur_stress_depart'),
			'struls'             => $this->CI->bouzouk->config('joueur_struls_depart'),
			'experience'         => $this->CI->bouzouk->config('joueur_xp_depart'),
			'plouk_stats'        => '0|0|0',
			'parrain_id'         => $data['parrain_id'],
		);
		$this->CI->db->insert('joueurs', $data_joueur);
		$data['joueur_id'] = $this->CI->db->insert_id();
		$data['date'] = $date;

		// On enregistre le joueur sur le forum
		$data_tobozon_user = array(
			'username'        => $data['pseudo'],
			'group_id'        => Bouzouk::Tobozon_IdGroupeBouzouks,
			'password'        => sha1($data['mot_de_passe']),
			'email'           => $this->CI->lib_email->nettoyer_email($data['email']),
			'email_setting'   => 2,
			'timezone'        => 1,
			'dst'             => 0,
			'language'        => 'French',
			'style'           => 'Bouzouks',
			'registered'      => time(),
			'registration_ip' => '',
			'last_visit'      => time()
		);
		$this->CI->db->insert('tobozon_users', $data_tobozon_user);

		// On ajoute le parrainage en attente de validation par un admin
		if ($data['parrain_id']!=null)
		{
			$data_parrainage = array(
				'parrain_id' => $data['parrain_id'],
				'filleul_id' => $data['joueur_id'],
				'date'       => $date
			);
			$this->CI->db->insert('parrainages', $data_parrainage);
		}

		// Si l'ip est déjà existante
		$ip_existe = $this->CI->db->where('ip_inscription', $this->CI->input->ip_address())
							  ->count_all_results('joueurs');

		if ($ip_existe == 0)
		{
			$ip_existe = $this->CI->db->where('ip', $this->CI->input->ip_address())
								  ->count_all_results('connexions');
		}

		$data['charte'] = '';

		if ($ip_existe > 0)
			$data['charte'] = "<br><br>On dirait que cette adresse IP a déjà été <u>utilisée pour un autre compte</u>. Les multicomptes étant interdits, nous te conseillons fortement de <span class='gras rouge'>bien lire la charte</span> afin de comprendre les règles concernant ces derniers et pouvoir jouer en toute tranquillité.";
		return $data;
	}

	public function activation_compte($joueur_id, $joueur_pseudo){
		$data_joueur = array(
			'statut'			=> Bouzouk::Joueur_Etudiant,
			'date_statut'	=> bdd_datetime()
			);
		// Mise à jour du statut du joueur
		$this->CI->db->where('id', $joueur_id)->update('joueurs', $data_joueur);
		// On donne accès à la map
		$this->CI->load->library('vlux/vlux_factory');
		$this->CI->vlux_factory->nouveau_joueur($joueur_id, $joueur_pseudo);
		// On met à jour le cache
		$this->CI->load->library('lib_tobozon');
		$this->CI->lib_tobozon->regenerer_cache('cache_users_info.php');
	}

	public function get_id_by_FB($fb_id){
		$query = $this->CI->db->select('id')->where('fb_id', $fb_id)->get('joueurs');
		if($query->num_rows()>0){
			$query = $query->row();
			$joueur_id = $query->id;
			return $joueur_id;
		}
		else{
			return FALSE;
		}
	}
}
