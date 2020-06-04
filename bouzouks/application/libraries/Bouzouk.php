<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions de gestion du jeu
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Bouzouk
{
	/*--------------------------------------------------*/
	/*          Constantes du jeu                       */
	/*--------------------------------------------------*/
	// joueurs.statut
	const Joueur_Inactif    = 1;
	const Joueur_Etudiant   = 2;
	const Joueur_ChoixPerso = 3;
	const Joueur_Actif      = 4;
	const Joueur_Asile      = 5;
	const Joueur_Pause      = 6;
	const Joueur_GameOver   = 7;
	const Joueur_Banni      = 8;
	const Joueur_Robot      = 9;

	// joueur.rang
	const Masque_Journaliste = 1;
	const Masque_Moderateur  = 2;
	const Masque_Admin       = 4;

	const Rang_Aucun                  = 0;
	const Rang_BetaTesteur            = 1;
	const Rang_JournalisteStagiaire   = 2;
	const Rang_Journaliste            = 4;
	const Rang_JournalisteChef        = 8;
	const Rang_ModerateurTobozon      = 16;
	const Rang_ModerateurIRC          = 32;
	const Rang_ModerateurRumeurs      = 64;
	const Rang_ModerateurElections    = 128;
	const Rang_ModerateurMendiants    = 256;
	const Rang_ModerateurProfils      = 512;
	const Rang_ModerateurAnnonces     = 1024;
	const Rang_ModerateurTchats       = 2048;
	const Rang_ModerateurMissives     = 4096;
	const Rang_ModerateurMulticomptes = 8192;
	const Rang_AdminStagiaire         = 16384;
	const Rang_MaitreJeu              = 32768;
	const Rang_Admin                  = 65536;


	// codes_aleatoires.type
	const Code_Inscription  = 1;
	const Code_PassPerdu    = 2;
	const Code_ChangerEmail = 3;

	// rumeurs.statut
	const Rumeur_EnAttente  = 1;
	const Rumeur_Refusee    = 2;
	const Rumeur_Validee    = 3;
	const Rumeur_Desactivee = 4;

	// Signalement Tchat Map
	const SignalementsTchatMapAttente = 1;
	const SignalementsTchatMapTraite = 2;

	// joueurs.id
	const Robot_Maire        =  2;
	const Robot_Dealer       =  4;
	const Robot_Percepteur   =  5;
	const Robot_JF_Sebastien =  6;
	const Robot_MissPoohLett =  9;
	const Robot_Emploi       = 12;
	const Robot_Spam         = 15;

	// classement_joueurs.type
	const Classement_Richesse     = 0;
	const Classement_Experience   = 1;
	const Classement_Fortune      = 2;
	const Classement_Plouk        = 3;
	const Classement_Collection   = 4;
	const Classement_PloukMauvais = 5;

	// donations.type
	const Donation_Mendiant        = 0;
	const Donation_Entreprise      = 1;
	const Donation_MairieBouzouk   = 2;
	const Donation_MairieBouzouks  = 3;
	const Donation_MairieMendiants = 4;
	const Donation_MairieTous      = 5;

	// mairie.tour_election
	const Elections_Banni      = -1;
	const Elections_Candidater = 0;
	const Elections_Tour1      = 1;
	const Elections_Tour2      = 2;
	const Elections_Tour3      = 3;

	// gazettes.type
	const Gazette_Meteo      = 1;
	const Gazette_Lohtoh     = 2;
	const Gazette_Fete       = 3;
	const Gazette_Classement = 4;
	const Gazette_Article    = 5;
	const Gazette_PubClan    = 6;

	// gazettes.en_ligne
	const Gazette_Brouillon  = 0;
	const Gazette_Publie     = 1;
	const Gazette_Refuse     = 2;

	// news.en_ligne
	const News_Brouillon  = 0;
	const News_Publie     = 1;

	// historique.type
	const Historique_Aucun        =  0;
	const Historique_Bonneteau    =  1;
	const Historique_Lohtoh       =  2;
	const Historique_Elections    =  3;
	const Historique_Boulot       =  4;
	const Historique_Compte       =  5;
	const Historique_Objets       =  6;
	const Historique_Dons         =  7;
	const Historique_Factures     =  8;
	const Historique_Annonces     =  9;
	const Historique_Maintenance  = 10;
	const Historique_Divers       = 11;
	const Historique_Plouk        = 12;
	const Historique_Clans        = 13;

	// historique.notification
	const Historique_Historique   = 0;
	const Historique_Notification = 1;
	const Historique_Full         = 2;

	// petites_annonces.type
	const PetitesAnnonces_Chomeur    = 0;
	const PetitesAnnonces_Patron     = 1;

	// tchat.type
	const Tchat_Entreprise           = 0;
	const Tchat_Asile                = 1;
	const Tchat_Journal              = 2;
	const Tchat_Chomeur              = 3;
	const Tchat_Clan                 = 4;
	const Tchat_Mendiant             = 5;
	const Tchat_Convocation          = 6;
	
	// tobozon
	const Tobozon_IdForumPropagande    = 5;
	const Tobozon_IdForumJournal       = 13;

	const Tobozon_IdGroupeAdmins       = 1;
	const Tobozon_IdGroupeModerateurs  = 2;
	const Tobozon_IdGroupeBouzouks     = 4;
	const Tobozon_IdGroupeJournalistes = 5;
	const Tobozon_IdGroupeCensures     = 12;
	const Tobozon_IdGroupeChefsClans   = 14;

	const Tobozon_IdCategorieClansNonOfficiels = 6;

	// clans.type
	const Clans_TypeSyndicat       = 1;
	const Clans_TypePartiPolitique = 2;
	const Clans_TypeOrganisation   = 3;
	const Clans_TypeCDBM           = 4;
	const Clans_TypeStruleone      = 5;
	const Clans_TypeSDS            = 6;
	const Clans_TypeMLB            = 7;

	// clans.mode_recrutement
	const Clans_RecrutementOuvert    = 1;
	const Clans_RecrutementFerme     = 2;
	const Clans_RecrutementInvisible = 3;

	// clans_actions.effet
	const Clans_EffetDirect  = 1;
	const Clans_EffetDiffere = 2;

	// clans_actions_lancees.statut
	const Clans_ActionEnCours  = 1;
	const Clans_ActionTerminee = 2;

	// clans_actions_allies.statut
	const Clans_AllianceAttente  = 1;
	const Clans_AllianceAcceptee = 2;
	const Clans_AllianceRefusee  = 3;

	// politiciens.rang
	const Clans_GradeTest     = 1;
	const Clans_GradeMembre   = 2;
	const Clans_GradeSousChef = 3;
	const Clans_GradeChef     = 4;

	// clans
	const Clans_NbClansMax = 3;

	// amis.etat
	const Amis_Attente = 0;
	const Amis_Accepte = 1;
	const Amis_Refuse  = 2;
	
	// piges.en_ligne
	const Piges_Desactive  = 0;
	const Piges_Active     = 1;
	
	// notifications
	const Notification_PloukNouvellePartie   = 1;
	const Notification_AnnonceANPC		     = 2;
	const Notification_DonMendiant		     = 3;
	const Notification_PseudoPrononceTobozon = 4;
	const Notification_PromoMairie			 = 5;
	const Notification_MissiveJoueur		 = 6;
	const Notification_ZlikeTobozon			 = 7;
	const Notification_DonMembreClan		 = 8;
	const Notification_NouvelEmploye		 = 9;
	const Notification_QuitterMembreClan	 = 10;
	
	// notifications.etat
	const Notifications_Desactive		   = 0;
	const Notifications_QuandConnecte	   = 1;
	const Notifications_ToutLeTemps		   = 2;
	const Notifications_QuandConnecteEtAmi = 3;

	/*--------------------------------------------------*/
	/*          Librairie Bouzouk                       */
	/*--------------------------------------------------*/
	private $CI;

	private $persos = array(
		'male' => array(
			'male_a1' => 'Standard style 1',
			'male_a2' => 'Standard style 2',
			'male_a3' => 'Standard style 3',
			'male_b1' => 'Techno standard',
			'male_b2' => 'Techno naturel',
			'male_b3' => 'Techno MLB fan',
			'male_c1' => 'Classe standard',
			'male_c2' => 'Classe flashy',
			'male_c3' => 'Classe loveur',
			'male_d1' => 'Rebelle 1',
			'male_d2' => 'Rebelle 2',
			'male_d3' => 'Rebelle 3',
			'male_e1' => 'Mafieu',
			'male_e2' => 'Mafieu tricheur',
			'male_e3' => 'Mafieu dragueur',
			'male_f1' => 'Sectaire',
			'male_f2' => 'Sectaire mysterieux',
			'male_f3' => 'Sectaire fou',
			'male_g1' => 'Elegant Revolutionnaire',
			'male_g2' => 'Elegant Costard',
			'male_g3' => 'Elegant Décontracté'
		),
		'femelle' => array(
			'femelle_c1' => 'Standard style 1',
			'femelle_c2' => 'Standard style 2',
			'femelle_c3' => 'Standard style 3',
			'femelle_a1' => 'Ultra-mode standard',
			'femelle_a2' => 'Ultra-mode sobre',
			'femelle_a3' => 'Ultra-mode flashy',
			'femelle_b1' => 'Bronzée standard',
			'femelle_b2' => 'Bronzée à point',
			'femelle_b3' => 'Bronzée saignante',
			'femelle_d1' => 'Bimbo standard',
			'femelle_d2' => 'Bimbo décontractée',
			'femelle_d3' => 'Bimbo Mia',
			'femelle_e1' => 'Voluptueuse Rose',
			'femelle_e2' => 'Voluptueuse Fashion',
			'femelle_e3' => 'Voluptueuse Classe'
		)
	);

	private $config;
	private $staff_droits;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->config = $this->CI->lib_cache->config();

		// --------------------------------------------------
        //		Droits d'accès à la section staff
        // --------------------------------------------------
        $this->staff_droits = array(
			array('connexion_bouzouk',     '*', $this->get_masque(self::Masque_Admin)),
			array('envoyer_missives',      '*', self::Rang_Admin),
			array('envoyer_email',         '*', self::Rang_Admin),
			array('gerer_joueurs',         '*', $this->get_masque(self::Masque_Admin)),
			array('gerer_objets',          '*', self::Rang_Admin),
			array('gerer_config',          '*', self::Rang_Admin),
			array('gerer_bot_irc',         '*', $this->get_masque(self::Masque_Admin)),
			array('gerer_pnj',             '*', $this->get_masque(self::Masque_Admin)),
			array('gerer_vlux',            '*', $this->get_masque(self::Masque_Admin)),
			array('gerer_maps',            '*', $this->get_masque(self::Masque_Admin)),
			array('gerer_batiments',       '*', $this->get_masque(self::Masque_Admin)),
			array('gerer_serveurs',        '*', $this->get_masque(self::Masque_Admin)),
			array('gerer_items',           '*', $this->get_masque(self::Masque_Admin)),
			array('gerer_site',            '*', self::Rang_Admin),
			array('gerer_campagne_fb',	   '*', self::Rang_Admin),
			array('gerer_news',            '*', self::Rang_Admin),
			array('gerer_bouf_tete',       '*', $this->get_masque(self::Masque_Admin)),
			array('gerer_action_clan',	   '*', self::Rang_Admin),
			array('gerer_event_mlbobz',    '*', $this->get_masque(self::Masque_Admin)),
			array('historique_moderation', '*', $this->get_masque(self::Masque_Admin)),
			array('moderer_parrainages',   '*', $this->get_masque(self::Masque_Admin)),
			array('moderer_rumeurs',       '*', $this->get_masque(self::Masque_Admin) | self::Rang_ModerateurRumeurs),
			array('moderer_elections',     '*', $this->get_masque(self::Masque_Admin) | self::Rang_ModerateurElections),
			array('moderer_annonces',      '*', $this->get_masque(self::Masque_Admin) | self::Rang_ModerateurAnnonces),
			array('moderer_mendiants',     '*', $this->get_masque(self::Masque_Admin) | self::Rang_ModerateurMendiants),
			array('moderer_profils',       '*', $this->get_masque(self::Masque_Admin) | self::Rang_ModerateurProfils),
			array('moderer_tobozon',       '*', $this->get_masque(self::Masque_Admin) | self::Rang_ModerateurTobozon),
			array('convoquer_joueur',      '*', $this->get_masque(self::Masque_Admin) | $this->get_masque(self::Masque_Moderateur)),
			array('moderer_missives',      '*', $this->get_masque(self::Masque_Admin) | self::Rang_ModerateurMissives),
			array('moderer_entreprises',   '*', $this->get_masque(self::Masque_Admin) | self::Rang_ModerateurTchats),
			array('moderer_clans',         '*', $this->get_masque(self::Masque_Admin) | self::Rang_MaitreJeu),
			array('moderer_clans_tchats',  '*', $this->get_masque(self::Masque_Admin) | self::Rang_ModerateurTchats),
			array('moderer_map_tchats',	   '*', $this->get_masque(self::Masque_Admin)),
			array('moderer_irc',           '*', $this->get_masque(self::Masque_Admin) | self::Rang_ModerateurIRC),
			array('statistiques',          '*', self::Rang_Admin),
			array('plus_de_struls',        '*', self::Rang_Admin),
			array('dons_paypal',           '*', self::Rang_Admin),
			array('multicomptes',          '*', $this->get_masque(self::Masque_Admin) | Bouzouk::Rang_ModerateurMulticomptes),
		);
	}
	
	public function get_droits()
	{
		$droits = array(
			self::Rang_BetaTesteur            => 'Bêta-testeur',
			self::Rang_JournalisteStagiaire   => 'Chroniqueur',
			self::Rang_Journaliste            => 'Journaliste',
			self::Rang_JournalisteChef        => 'Journaliste rédacteur en chef',
			self::Rang_ModerateurTobozon      => 'Modérateur tobozon',
			self::Rang_ModerateurIRC          => 'Modérateur IRC',
			self::Rang_ModerateurRumeurs      => 'Modérateur rumeurs',
			self::Rang_ModerateurElections    => 'Modérateur éléctions',
			self::Rang_ModerateurMendiants    => 'Modérateur mendiants',
			self::Rang_ModerateurProfils      => 'Modérateur profils',
			self::Rang_ModerateurAnnonces     => 'Modérateur annonces',
			self::Rang_ModerateurTchats       => 'Modérateur tchats',
			self::Rang_ModerateurMissives     => 'Modérateur missives',
			self::Rang_ModerateurMulticomptes => 'Modérateur multicomptes',
			self::Rang_MaitreJeu              => 'Maître de Jeu',
			self::Rang_AdminStagiaire         => 'Administrateur stagiaire',
			self::Rang_Admin                  => 'Administrateur'
		);

		return $droits;
	}

	// Vérifie si un event est déjà en cours.
	public function check_event(){
		if($this->etat_event_mlbobz()){
			return TRUE;
		}
		elseif($this->etat_bouf_tete() == 'start'){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
	
	// --------- Event RP Zombies ------------
	public function est_zombie($joueur_id)
	{
		$query = $this->CI->db->select('nb_morsure')
								  ->from('event_joueurs_zombies')
								  ->where('joueur_id', $joueur_id)
								  ->get();

		if ($query->num_rows() > 0)
			return true;
		else
			return false;
	}
	// --------- Fin Event RP Zombies ------------

	// ---------- Début Even Boobzofolie ---------
	public function set_etat_mlbobz($etat){
		$this->CI->db->where('cle', 'etat')->set('valeur', $etat)->update('event_mlbobz_config');
		// Start event
		if($etat == 1){

			// On met le Dézombateur dispo au MN
			$this->CI->db->set('disponibilite', 'marche_noir')->where('id', 54)->update('objets');
		}
		// Stop event
		if($etat == 0){
			// On envoie une notif à Tweedy et Hikingyo
			$this->historique(256,257, array(), 5271, self::Historique_Full);
			$this->historique(256, 257, array(), 17, self::Historique_Full);
			// On remet le Dézombateur en indisponibilité
			$this->CI->db->set('disponibilite', 'desactive')->where('id', 54)->update('objets');
			// On met à jour la session
			$this->augmente_version_session();
		}
	}

	public function etat_event_mlbobz(){
		$etat = $this->CI->db->select('valeur')->where('cle', 'etat')->get('event_mlbobz_config');
		$etat = $etat->row();
		if($etat->valeur == '1'){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}

	public function est_maudit_mlbobz($id){
		$maudit = $this->CI->db->where('id_joueur', $id)->where('immun', 0)->get('event_mlbobz');
		if($maudit->num_rows()>0){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}

	public function list_maudit_mlbobz(){
		$maudits = $this->CI->db->select('id_joueur')->get('event_mlbobz');
		if($maudits->num_rows()>0){
			$maudits = $maudits->result();
			foreach($maudits as $maudit){
				$list[] = $maudit->id_joueur;
			}
			return $list;
		}
	}

	public function boobzable($connecte){
		$maudits = $this->list_maudit_mlbobz();
		$select_boobzable = $this->select_joueurs(array(
			'name'		=>'malediction_mlbobz',
			'status_not_in' => array(self::Joueur_Etudiant, self::Joueur_ChoixPerso, self::Joueur_GameOver, self::Joueur_Robot, self::Joueur_Banni),
 					'non_inclus'    => $maudits,
 					'connectes'		=> $connecte,
 					'empty_return'  => false
 					));
		return $select_boobzable;
	}

	public function update_candidat_mlbobz($id, $rang){
		if($rang !='candidat' && $rang!='suppleant'){
			return FALSE;
		}
		$this->CI->db->set('valeur', $id)->where('cle', $rang)->update('event_mlbobz_config');
	}

	public function nb_malediction_mlbobz($id_joueur){
		$nb= $this->CI->db->where('id_joueur', $id_joueur)
							->select('nb_malediction')
							->get('event_mlbobz');
		if($nb->num_rows()>0){
			$nb= $nb->row();
			$nb_m = $nb->nb_malediction;
		}
		else{
			$nb_m = 0;
		}
		
		return $nb_m;
	}

	public function maudire_mlbobz($id, $joueur = false){
		//Si le joueur est déjà comtaminé ou immunisé, on retourne false
		if($this->est_maudit_mlbobz($id)){
			return false;
		}
		else{
			// Si la malédiction vient d'un joueur
			if($joueur){
				$nb_m_mlbobz = $this->nb_malediction_mlbobz($this->CI->session->userdata('id'));
				// Si il ne reste plus de petit
				if($nb_m_mlbobz <= 0){
					return "insuffisant";
				}	
				// On met à jour l'émetteur
				$nb_m_mlbobz= $nb_m_mlbobz -1;
				$this->CI->db->set('nb_malediction', $nb_m_mlbobz)->where('id_joueur', $this->CI->session->userdata('id'))->update('event_mlbobz');
				// On met à jour la session du transplanteur
				$this->augmente_version_session();
			}
			else{
				$nb_m_mlbobz = 0;
			}
			$data = array(
				'id_joueur'			=> $id,
				'nb_malediction'	=> 2,
				'immun'				=> 0,
				);
			// On ajoute le nouveau boobzé en bdd
			$this->CI->db->insert('event_mlbobz', $data);

			// On met à jour la session du boobzé
			$this->augmente_version_session($id);
			// On envoie une notif au joueur maudit
			$this->historique(258,259, array(profil(-1, '', $this->CI->session->userdata('rang'))), $id, self::Historique_Full);
			return $nb_m_mlbobz;
		}
	}

	public function malediction_mlbobz_layout($id){
		// Initialisation du layout
		$data_bobz['form'] = false;
		//Si le joueur peut encore envoyer une malédiction
		if($this->nb_malediction_mlbobz($id)> 0){
			// On récupère la liste des victimes potentielles
			$boobzable = $this->boobzable(true);
			//Si il y en a
			if($boobzable){
				// On paramétre le layout
				$data_bobz['text'] = $boobzable;
				$data_bobz['form'] = true;
			}
			else{

			// Sinon, on affiche un message
			$data_bobz['text']= "Il n'y a personne en vue.<br>Sois patiente !<br>Les Boobz vaincront !";
			}
		}
		// Plus de petit
		else{
			$data_bobz['text'] = "Tu ne peux plus contaminer personne.<br/> Attends demain !";
		}
		return $data_bobz;
	}

	public function choix_mlbobz(){
		$candidat = $this->CI->db->where('cle', 'candidat')->select('valeur')->get('event_mlbobz_config');
		$candidat = $candidat->row();
		$candidat = $this->CI->db->where('id',$candidat->valeur)->select('pseudo')->get('joueurs');
		$candidat = $candidat->row();
		return $candidat->pseudo;
	}

	public function est_choix_mlbobz($id){
		// On récupère le candidat
		$boobz = $this->CI->db->where('cle', 'candidat')->select('valeur')->get('event_mlbobz_config');
		// Si le candidat est un élu
		if($boobz->num_rows() >0){
			$boobz = $boobz->row();
			if($boobz->valeur == $id){
				return TRUE;
			}
		}
		// Sinon, on bloque le vote
		else{
			return FALSE;
		}
	}

	public function upgrade_candidat_mlbobz(){
		// On récupère l'id du suppleant
		$suppleant = $this->CI->db->where('cle', 'suppleant')->select('valeur')->get('event_mlbobz_config');
		$suppleant = $suppleant->row();
		$this->CI->db->set('valeur', $suppleant->valeur)->where('cle', 'candidat')->update('event_mlbobz_config');
	}

	public function notif_event_mlbobz(){
		$etat_notif = $this->CI->db->where('cle', 'notif')->select('valeur')->get('event_mlbobz_config');
		$etat_notif = $etat_notif->row();
		if($etat_notif->valeur == 1){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}

	public function set_notif_event_mlbobz($statut){
		$this->CI->db->set('valeur', $statut)->where('cle', 'notif')->update('event_mlbobz_config');
	}
	// --------- Event RP Bouf'tête ------------

	/**
	* Vérification de l'état de l'event
	* Si la table est vide, l'event est en stand by, sinon, c'est qu'il est en cours.
	* @return str 'start'|'stop'
	**/
	public function etat_bouf_tete(){
		$etat = $this->CI->db->where('immun', 0)
							->count_all('event_bouf_tete');
		if($etat > 0){
			return 'start';
		}
		else{
			return 'stop';
		}
	}

	/**
	 * le joueur est-il infecté
	 * @param [$id] id du joueurs à vérifier
	 * @return bool
	 **/
	public function est_infecte($id){
		$infection = $this->CI->db->where('id_joueur', $id)->where('immun', 0)->get('event_bouf_tete');
		if($infection->num_rows() >0){
			return 1;
		}
		else{
			return 0;
		}
	}

	/**
	 * Liste des joueurs infectés
	 * @return array contenant les ids des joueurs infectés
	 **/
	public function list_infecte(){
		$infectes = $this->CI->db->select('id_joueur')->get('event_bouf_tete');
		if($infectes->num_rows()>0){
			$infectes = $infectes->result();
			foreach ($infectes as $infecte) {
				$results[] = $infecte->id_joueur; 
			}
			return $results;
		}
		
	}


	/**
	 * joueur choisi pour le RP
	 * @param [$id] id du joueur à vérifier
	 * @return bool
	 **/
	public function est_choix_bouf_tete($id){
		// On récupère le bouf'tête
		$bouf_tete = $this->CI->db->where('id_joueur', $id)->select('candidat')->get('event_bouf_tete');
		// Si le candidat est un élu
		if($bouf_tete->num_rows() >0){
			$bouf_tete = $bouf_tete->row();
			// Si c'est le candidat alpha
			if($bouf_tete->candidat == 1){
				return $bouf_tete->candidat;
			}
		}
		// Sinon, on bloque le vote
		else{
			return 0;
		}
	}

	/** 
	*la liste des candidats choisi pour le RP
	* @return array : liste des candidats
	**/
	public function choix_bouf_tete(){
		$candidat = $this->CI->db->where('candidat', 1)->select('id_joueur')->get('event_bouf_tete');
		$candidat = $candidat->row();
		$candidat = $this->CI->db->where('id',$candidat->id_joueur)->select('pseudo')->get('joueurs');
		$candidat = $candidat->row();
		return $candidat;
	}

	/**
	 * Liste des victime potentiel pour le select
	 * @return str  select pour layout
	 **/
	public function infectables($connecte){
		$infectes = $this->list_infecte();
		$select_infection = $this->select_joueurs(array(
			'name'		=>'infection',
			'status_not_in' => array(self::Joueur_Etudiant, self::Joueur_ChoixPerso, self::Joueur_GameOver, self::Joueur_Robot, self::Joueur_Banni),
 					'non_inclus'    => $infectes,
 					'connectes'		=> $connecte,
 					'empty_return'  => false
 					));
		return $select_infection;
	}

	/**
	 * Liste des joueurs potentiellement élu
	 * @return str contenu du select pour le layout
	 **/
	public function elus_potentiels(){
		$select_elus = $this->select_joueurs(array(
								'name'			=> 'candidat_elu',
								'status_not_in'	=> array(self::Joueur_Etudiant, self::Joueur_GameOver, self::Joueur_Robot, self::Joueur_Banni)
								));
		return $select_elus;
	}

	/**
	 * Combien reste-il de bébé bouf'tête
	 * @param [$id_joueur]
	 * @return int nombre de bébé restant
	 **/
	public function nb_bebe_bouf_tete($id_joueur){
		$nb= $this->CI->db->where('id_joueur', $id_joueur)
							->select('nb_petit')
							->get('event_bouf_tete');
		$nb= $nb->row();
		return $nb->nb_petit;
	}

	/**
	 * données à destination du layout du site
	 * @param [$id] identifiant du joueur
	 * @return array contenu à afficher
	 **/
	public function infection_layout($id){
		// Initialisation du layout
		$data_bouf_tete['form'] = false;
		//Si le bouf'tête a encore des enfants
		if($this->nb_bebe_bouf_tete($id)> 0){
			// On récupère la liste des victimes potentielles
			$infectables = $this->infectables(true);
			//Si il y en a
			if($infectables){
				// On paramétre le layout
				$data_bouf_tete['text'] = $infectables;
				$data_bouf_tete['form'] = true;
			}
			else{

			// Sinon, on affiche un message
			$data_bouf_tete['text']= "Il n'y a personne en vue.<br>Sois patient !<br>Nous dominerons la ville...";
			}
		}
		// Plus de petit
		else{
			$data_bouf_tete['text'] = "Ta bestiole jaune n'a plus de petit. Attends demain !";
		}
		return $data_bouf_tete;
	}

	/**
	 * implanter un bouf'tête sur un bouzouk
	 * @param [$id] identifiant du bouzouk à implanter
	 * @return int : le nombre de petit en cas de succès
	 **/
	public function infecter($id, $joueur = false){
		//Si le joueur est déjà comtaminé ou immunisé, on retourne false
		if($this->est_infecte($id)){
			return false;
		}
		else{
			// Si l'implentation vient d'un joueur
			if($joueur){
				$nb_bb = $this->nb_bebe_bouf_tete($this->CI->session->userdata('id'));
				// Si il ne reste plus de petit
				if($nb_bb <= 0){
					return "insuffisant";
				}	
				// On met à jour le bouf'tête parent
				$nb_bb= $nb_bb -1;
				$this->CI->db->set('nb_petit', $nb_bb)->where('id_joueur', $this->CI->session->userdata('id'))->update('event_bouf_tete');
				// On met à jour la session du transplanteur
				$this->augmente_version_session();
			}
			$data = array(
				'id_joueur'		=> $id,
				'nb_petit'		=> 2,
				'candidat'		=> 0,
				'immun'			=> 0
				);
			// On ajoute le nouveau bouf'tête en bdd
			$this->CI->db->insert('event_bouf_tete', $data);

			// On met à jour la session du transplanté
			$this->augmente_version_session($id);
			// On envoie une notif au joueur infecté
			$this->historique(251,252, array(profil(-1, '', $this->CI->session->userdata('rang'))), $id, self::Historique_Full);
			return $nb_bb;
		}
	}



	/**
	 * désigner un candidat comme élu des bouf'tête
	 * @param [$id] identifiant du joueur choisi
	 * @return bool
	 **/
	public function choisir_candidat_bouf_tete($id, $rang){
		$choix = $this->CI->db->set('candidat', $rang)->where('id_joueur', $id)->update('event_bouf_tete');
	}

	/**
	 * Arrêter m'event
	 * Vider la table event_bouf_tete
	 **/
	public function stop_event_bouf_tete(){
		$query = $this->CI->db->truncate('event_bouf_tete');

		// On envoie une notif à Tweedy et Hikingyo
		$this->historique(253,254, array(), 5271, self::Historique_Full);
		$this->historique(253, 254, array(), 17, self::Historique_Full);
		// On remet le Dézombateur en indisponibilité
		$this->CI->db->set('disponibilite', 'desactive')->where('id', 54)->update('objets');
		// On met à jour la session
		$this->augmente_version_session();
	}

	// --------- Fin Event RP Bouf'tête ------------


	public function is_staff_autorise($controleur, $methode = null)
	{
		if ( ! isset($methode))
			$methode = '*';

		// Gestion des droits de chaque page du staff
		foreach ($this->staff_droits as $droits)
		{
			if ($droits[0] == $controleur && $droits[1] == $methode && ($droits[2] & $this->CI->session->userdata('rang')) == 0)
				return false;

			if ($droits[0] == $controleur && $droits[1] == '*' && ($droits[2] & $this->CI->session->userdata('rang')) > 0)
				return true;
		}

		return false;
	}

	public function recharger_config()
	{
		$this->config = $this->CI->lib_cache->config(true);
	}
	
	public function config($cle)
	{
		// Pour le débugage
		if ( ! isset($this->config[$cle]) && ! $this->is_admin())
		{
			exit('Erreur configuration : '.$cle.", vite vite va avertir Robby !!\n");
		}

		return $this->config[$cle];
	}

	public function is_beta_testeur($rang = null)
	{
		$masque = self::Rang_BetaTesteur;

		if ( ! isset($rang))
			$rang = $this->CI->session->userdata('rang');

		return ((int)$rang & $masque) > 0 || $this->is_moderateur(null, $rang);
	}
	
	public function is_journaliste($masque = null, $rang = null)
	{
		if ( ! isset($masque))
			$masque = $this->get_masque(self::Masque_Journaliste);

		if ( ! isset($rang))
			$rang = $this->CI->session->userdata('rang');

		// Hiérarchie
		if (($masque & self::Rang_JournalisteStagiaire) > 0)
			$masque |= self::Rang_Journaliste;

		// Hiérarchie
		if (($masque & self::Rang_Journaliste) > 0)
			$masque |= self::Rang_JournalisteChef;

		return ((int)$rang & $masque) > 0 || $this->is_admin(null, $rang);
	}
	
	public function is_moderateur($masque = null, $rang = null)
	{
		if ( ! isset($masque))
			$masque = $this->get_masque(self::Masque_Moderateur);

		if ( ! isset($rang))
			$rang = $this->CI->session->userdata('rang');

		return ((int)$rang & $masque) > 0 || $this->is_admin(null, $rang);
	}

	public function is_mdj($rang = null)
	{
		$masque = self::Rang_MaitreJeu;

		if ( ! isset($rang))
			$rang = $this->CI->session->userdata('rang');

		return ((int)$rang & $masque) > 0;
	}

	public function is_admin($masque = null, $rang = null)
	{
		if ( ! isset($masque))
			$masque = $this->get_masque(self::Masque_Admin);

		if ( ! isset($rang))
			$rang = $this->CI->session->userdata('rang');

		// Hiérarchie
		if (($masque & self::Rang_AdminStagiaire) > 0)
			$masque |= self::Rang_Admin;

		return ((int)$rang & $masque) > 0;
	}

	public function get_masque($masque)
	{
		$masque_retour = 0;

		if (($masque & self::Masque_Journaliste) > 0)
			$masque_retour |= self::Rang_Journaliste | self::Rang_JournalisteStagiaire | self::Rang_JournalisteChef;

		if (($masque & self::Masque_Moderateur) > 0)
		{
			$masque_retour |= self::Rang_ModerateurTobozon | self::Rang_ModerateurIRC | self::Rang_ModerateurRumeurs | self::Rang_ModerateurElections | self::Rang_ModerateurMendiants |
							  self::Rang_ModerateurProfils | self::Rang_ModerateurAnnonces | self::Rang_ModerateurTchats | self::Rang_ModerateurMissives | self::Rang_ModerateurMulticomptes;
		}

		if (($masque & self::Masque_Admin) > 0)
			$masque_retour |= self::Rang_AdminStagiaire | self::Rang_Admin;

		return $masque_retour;
	}

	public function is_maire()
	{
		return $this->CI->session->userdata('maire');
	}

	public function select_joueurs($options = array())
	{
		$name           = isset($options['name'])          ? $options['name']          : 'joueur_id';
		$status_not_in  = isset($options['status_not_in']) ? $options['status_not_in'] : array();
		$rangs_in       = isset($options['rangs_in'])      ? $options['rangs_in']      : null;
		$clan       	= isset($options['clan'])      	   ? $options['clan']      	   : null;
		$joueur_id      = isset($options['joueur_id'])     ? $options['joueur_id']     : null;
		$rangs          = isset($options['rangs'])         ? $options['rangs']         : true;
		$inactifs       = isset($options['inactifs'])      ? $options['inactifs']      : false;
		$champ_texte    = isset($options['champ_texte'])   ? $options['champ_texte']   : false;
		$joueurs        = isset($options['joueurs'])       ? $options['joueurs']       : null;
		$non_inclus     = isset($options['non_inclus'])    ? $options['non_inclus']    : null;
		$connectes		= isset($options['connectes'])     ? $options['connectes']     : false;
		$empty_return   = isset($options['empty_return'])  ? $options['empty_return']  : null;

		if ( ! isset($joueurs))
		{
			if ( ! $inactifs)
				$status_not_in[] = Bouzouk::Joueur_Inactif;

			// On va chercher tous les joueurs
			$requete = 'SELECT j.id, j.pseudo, j.rang, m.maire_id AS maire '.
					   'FROM joueurs j '.
					   'LEFT JOIN mairie m ON j.id = m.maire_id '.
					   'WHERE j.statut NOT IN ('.implode(',', $status_not_in).') ';

			if (isset($rangs_in))
			{
				if ($rangs_in == Bouzouk::Rang_Aucun)
					$requete .= 'AND rang = '.Bouzouk::Rang_Aucun.' ';

				else
					$requete .= 'AND rang & '.$rangs_in.' > 0 ';
			}
			
			if ($connectes)
				$requete .= 'AND j.connecte > (NOW() - INTERVAL 2 MINUTE) ';

			if (isset($non_inclus))
				$requete .= 'AND j.id NOT IN ('.implode(', ', $non_inclus).') ';

			$requete .= 'ORDER BY IF(j.rang = '.Bouzouk::Rang_BetaTesteur.', '.Bouzouk::Rang_Aucun.', j.rang) desc, maire desc, j.pseudo';
			$joueurs = $this->CI->db->query($requete)->result();
		}
		//Si un clan est spécifié
		if(isset($clan)){
			//On récupère l'identifiant des joueurs membres du clan spécifié
			$membres_clan = $this->CI->db->where('clan_id', $clan)
										->select('joueur_id')
										->get('politiciens');
			$membres_clan = $membres_clan->result_array();
			// On filtre le tableau
			foreach ($membres_clan as $membre_clan) {
				$dummy[]= $membre_clan['joueur_id'];
			}
			$membres_clan = $dummy;
			unset($dummy);
			// On filtre la liste des joueurs
			foreach($joueurs as $joueur){
				if(in_array($joueur->id, $membres_clan)){
					$dummy[]= $joueur;
				}
			}
			$joueurs = $dummy;
			unset($dummy);
		}
		$selected = isset($joueur_id) ? '' : ' selected';
		$select = '<select name="'.$name.'" class="select-joueur">'.
				  '    <option value=""'.$selected.'>------------------------------</option>';

		foreach ($joueurs as $joueur)
		{
			$rang = '';
			$rang_css = '';

			if ($this->is_admin(Bouzouk::Rang_Admin, $joueur->rang))
			{
				$rang_css = 'admin';
				$rang = ' - [Admin]';
			}

			else if ($this->is_mdj($joueur->rang))
			{
				$rang_css = 'mdj';
				$rang = ' - [MdJ]';
			}

			else if ($this->is_moderateur(null, $joueur->rang))
			{
				$rang_css = 'modo';
				$rang = ' - [Modo]';
			}

			if ($this->is_journaliste(null, $joueur->rang) && ! $this->is_admin(null, $joueur->rang))
			{
				if ($rang != '')
					$rang .= '[Journal]';
				else
				{
					$rang_css = 'journaliste';
					$rang = ' - [Journal]';
				}
			}

			if ($joueur->maire != null && ! $this->is_admin(null, $joueur->rang))
			{
				if ($rang != '')
					$rang .= '[Maire]';
				else
				{
					$rang_css = 'maire';
					$rang = ' - [Maire]';
				}
			}

			if ( ! $rangs)
				$rang = '';

			$selected = (isset($joueur_id) && $joueur_id == $joueur->id) ? ' selected' : '';
			$select .= '    <option class="'.$rang_css.'" value="'.$joueur->id.'"'.$selected.'>'.$joueur->pseudo.$rang.'</option>';
		}

		$select .= '</select>';

		if ($champ_texte)
			$select .= '<br>Pseudo : <input type="text" name="'.$name.'_pseudo" maxlength="20"><br>';
		
		if (isset($empty_return) && count($joueurs) <= 0)
			return $empty_return;

		return $select;
	}

	public function get_persos()
	{
		return $this->persos;
	}

	// Retourne les ids des robots actifs du jeu
	public function get_robots()
	{
		return explode(', ', $this->CI->lib_cache->robots_actifs());
	}

	// Retourne les ids des comptes inactifs mais utilisés dans le jeu (robots, comptes de clans, ...)
	public function get_inactifs()
	{
		return explode(', ', $this->CI->lib_cache->robots_inactifs());
	}
	
	// Retourne les joueurs fictifs qui servent aux clans
	// Les ids qui sont dans ce tableau doivent aussi être dans get_inactifs() pour ne pas être supprimés à la maintenance
	public function get_clans()
	{
		return array(14);
	}

	public function est_connecte($joueur_id)
	{
		return $this->CI->db->where('id', $joueur_id)->where('connecte >= (NOW() - INTERVAL 2 MINUTE)')->count_all_results('joueurs');
	}
	
	public function ajouter_struls($struls, $joueur_id = null)
	{
		$struls = abs($struls);

		if ( ! isset($joueur_id))
		{
			$joueur_id = $this->CI->session->userdata('id');
			$this->CI->session->set_userdata('struls', $this->CI->session->userdata('struls') + $struls);
		}

		$this->CI->db->set('struls', 'struls + '.$struls, false)
					 ->where('id', $joueur_id)
					 ->update('joueurs');
	}

	public function retirer_struls($struls)
	{
		$struls = abs($struls);

		$struls = max(0, $this->CI->session->userdata('struls') - $struls);
		$this->CI->session->set_userdata('struls', $struls);

		$this->CI->db->set('struls', $struls)
					 ->where('id', $this->CI->session->userdata('id'))
					 ->update('joueurs');
	}

	public function get_nb_missives($id, $recues = true, $envoyees = true)
	{
		if ($recues AND $envoyees)
		{
			$this->CI->db->where('(m.destinataire_id = '.$id.' AND m.destinataire_supprime = 0)')
						 ->or_where('(m.expediteur_id = '.$id.' AND m.expediteur_supprime = 0)');
		}

		else if ($recues AND ! $envoyees)
		{
			$this->CI->db->where('m.destinataire_id', $id)
						 ->where('m.destinataire_supprime', 0);
		}

		else
		{
			$this->CI->db->where('m.expediteur_id', $id)
						 ->where('m.expediteur_supprime', 0);
		}

		return $this->CI->db->join('joueurs j', 'j.id = m.expediteur_id')
							->count_all_results('missives m');
	}

	public function get_nb_missives_non_lues($id)
	{
		return $this->CI->db->where('destinataire_id', $id)
							->where('destinataire_supprime', 0)
							->where('lue', 0)
							->count_all_results('missives');
	}

	public function ajouter_objets($objet_id, $quantite, $peremption, $joueur_id = null)
	{
		if ($quantite <= 0)
		{
			show_404();
		}

		if ( ! isset($joueur_id))
			$joueur_id = $this->CI->session->userdata('id');

		// On met à jour le nombre de fragments
		if ($objet_id == 55)
		{
			$this->CI->db->set('fragments', 'fragments + '.$quantite, false)
						 ->where('id', $joueur_id)
						 ->update('joueurs');
			$this->CI->session->set_userdata('fragments', $this->CI->session->userdata('fragments') + $quantite);
		}

		// On regarde le nombre d'objets du type objet_id de ce joueur
		$nb_objets = $this->CI->db->where('objet_id', $objet_id)
								  ->where('joueur_id', $joueur_id)
								  ->where('peremption', $peremption)
								  ->count_all_results('maisons');

		// Insert
		if ($nb_objets == 0)
		{
			$data_maisons = array(
				'objet_id'   => $objet_id,
				'joueur_id'  => $joueur_id,
				'peremption' => $peremption,
				'quantite'   => $quantite
			);
			$this->CI->db->insert('maisons', $data_maisons);
		}

		// Update
		else
		{
			$this->CI->db->set('quantite', 'quantite + '.$quantite, false)
						 ->where('objet_id', $objet_id)
						 ->where('joueur_id', $joueur_id)
						 ->where('peremption', $peremption)
						 ->update('maisons');
		}
	}

	public function retirer_objets($objet_id, $quantite, $peremption, $joueur_id = null)
	{
		if ($quantite <= 0)
			show_404();

		if ( ! isset($joueur_id))
			$joueur_id = $this->CI->session->userdata('id');

		// On vérifie que le joueur possède bien cet objet
		$query = $this->CI->db->select('m.quantite, m.peremption, o.id, o.nom, o.type, o.faim, o.sante, o.stress, o.jours_peremption, o.experience, o.force, o.charisme, o.intelligence, o.points_action, o.prix, o.rarete')
							  ->from('maisons m')
							  ->join('objets o', 'o.id = m.objet_id')
							  ->where('m.joueur_id', $joueur_id)
							  ->where('m.objet_id', $objet_id)
							  ->where('m.peremption', $peremption)
							  ->get();

		// Si l'objet n'existe pas
		if ($query->num_rows() == 0)
		{
			$this->CI->echec('Tu ne possèdes pas cet objet');
			return false;
		}

		$objet = $query->row();

		// On vérifie que la quantité demandée est disponible
		if ($objet->quantite < $quantite)
		{
			$this->CI->echec("Tu n'as que <span class='pourpre'>".$objet->quantite." ".$objet->nom.'</span>');
			return false;
		}

		// On retire l'objet de sa liste
		if ($quantite == $objet->quantite)
		{
			$this->CI->db->where('objet_id', $objet_id)
						 ->where('joueur_id', $joueur_id)
						 ->where('peremption', $peremption)
						 ->delete('maisons');
		}

		else
		{
			$this->CI->db->set('quantite', 'quantite - '.$quantite, false)
						 ->where('objet_id', $objet_id)
						 ->where('joueur_id', $joueur_id)
						 ->where('peremption', $peremption)
						 ->update('maisons');
		}

		// On met à jour le nombre de fragments
		if ($objet_id == 55)
		{
			$this->CI->db->set('fragments', 'fragments - '.$quantite, false)
						 ->where('id', $joueur_id)
						 ->update('joueurs');
			$this->CI->session->set_userdata('fragments', $this->CI->session->userdata('fragments') - $quantite);
		}

		return $objet;
	}

	public function set_stats($faim, $sante, $stress)
	{
		// On calcule les nouvelles stats avec des limites entre 0 et 100
		$faim = max(0, min(100, $this->CI->session->userdata('faim') + $faim));
		$sante = max(0, min(100, $this->CI->session->userdata('sante') + $sante));
		$stress = min(100, max(0, $this->CI->session->userdata('stress') + $stress));

		// On met à jour la base et la session
		$data_joueur = array(
			'faim' => $faim,
			'sante' => $sante,
			'stress' => $stress
		);
		$this->CI->db->where('id', $this->CI->session->userdata('id'))
					 ->update('joueurs', $data_joueur);
		$this->CI->session->set_userdata($data_joueur);
	}

	public function set_stats_clans($force, $charisme, $intelligence)
	{
		// On calcule les nouvelles stats
		$force = max(0, $this->CI->session->userdata('force') + $force);
		$charisme = max(0, $this->CI->session->userdata('charisme') + $charisme);
		$intelligence = max(0, $this->CI->session->userdata('intelligence') + $intelligence);

		// On met à jour la base et la session
		$data_joueur = array(
			'force' => $force,
			'charisme' => $charisme,
			'intelligence' => $intelligence
		);
		$this->CI->db->where('id', $this->CI->session->userdata('id'))
					 ->update('joueurs', $data_joueur);
		$this->CI->session->set_userdata($data_joueur);
	}

	public function clans_corruption_a_agent($joueur_id = null)
	{
		if ( ! isset($joueur_id))
			$joueur_id = $this->CI->session->userdata('id');

		// ---------- Hook clans ----------
		// Corruption à agent (Struleone)
		$query = $this->CI->db->select('c.id')
						 	  ->from('clans_actions_lancees cal')
						 	  ->join('politiciens p', 'p.clan_id = cal.clan_id', 'left')
						 	  ->join('clans c', 'c.id = cal.clan_id', 'left')
							  ->where('cal.action_id', 25)
							  ->where('cal.statut', Bouzouk::Clans_ActionEnCours)
							  ->where('(c.chef_id = '.$joueur_id.' OR p.joueur_id = '.$joueur_id.')')
							  ->where('cal.date_debut >= (NOW() - INTERVAL duree HOUR)')
							  ->get();
		return ($query->num_rows() > 0);
	}
	
	public function clans_pillage_compulsif($shop = null, $joueur_id = null)
	{
		if ( ! isset($joueur_id))
			$joueur_id = $this->CI->session->userdata('id');

		// ---------- Hook clans ----------
		// Pillage compulsif (Organisation)
		$query = $this->CI->db->select('parametres')
						 	  ->from('clans_actions_lancees')
							  ->where('action_id', 38)
							  ->where('statut', Bouzouk::Clans_ActionEnCours)
							  ->where('date_debut >= (NOW() - INTERVAL duree HOUR)')
							  ->get();
		
		if ($query->num_rows() > 0)
		{
			$actions = $query->result();
			
			foreach ($actions as $action)
			{
				$action->parametres = unserialize($action->parametres);
				
				if ($action->parametres['joueur_id'] == $joueur_id && ($action->parametres['shop'] == $shop ||  ! isset($shop)))
					return true;
			}
		}
		
		return false;
	}
	
	public function clans_concurrence_genante($joueur_id = null)
	{
		if ( ! isset($joueur_id))
			$joueur_id = $this->CI->session->userdata('id');

		// ---------- Hook clans ----------
		// Concurrence gênante (Organisation)
		$query = $this->CI->db->select('parametres')
						 	  ->from('clans_actions_lancees')
							  ->where('action_id', 39)
							  ->where('statut', Bouzouk::Clans_ActionEnCours)
							  ->where('date_debut >= (NOW() - INTERVAL duree HOUR)')
							  ->get();
		if ($joueur_id == -1)
		{
			$joueurs = array(0);			
			$actions = $query->result();
			
			foreach ($actions as $action)
			{
				$action->parametres = unserialize($action->parametres);				
				$joueurs[] = $action->parametres['joueur_id'];
			}
			
			return $joueurs;
		}
		
		if ($query->num_rows() > 0)
		{
			$actions = $query->result();
			
			foreach ($actions as $action)
			{
				$action->parametres = unserialize($action->parametres);
				
				if ($action->parametres['joueur_id'] == $joueur_id)
					return true;
			}
		}
		
		return false;
	}

	public function clans_tag_mlbiste($clan_id = null)
	{
		// ---------- Hook clans ----------
		// Tag MLBiste (MLB)
		$query = $this->CI->db->select('parametres')
						 	  ->from('clans_actions_lancees')
							  ->where('action_id', 31)
							  ->where('statut', Bouzouk::Clans_ActionEnCours)
							  ->get();
		$tag_mlbiste = ($query->num_rows() == 1) ? $query->row() : null;

		if (isset($tag_mlbiste))
		{
			$this->CI->load->library('lib_parser');

			$tag_mlbiste->parametres = unserialize($tag_mlbiste->parametres);

			if ((isset($clan_id) && $tag_mlbiste->parametres['clan_id'] != $clan_id) || ( ! isset($clan_id) && $tag_mlbiste->parametres['clan_id'] > 0))
				$tag_mlbiste = null;
		}

		return $tag_mlbiste;
	}

	public function clans_sainte_brigade()
	{
		// ---------- Hook clans ----------
		// Saint Brigade (SdS)
		$query = $this->CI->db->select('cal.parametres, c.nom AS nom_clan, c.mode_recrutement')
						 	  ->from('clans_actions_lancees cal')
						 	  ->join('clans c', 'c.id = cal.clan_id')
							  ->where('cal.action_id', 29)
							  ->where('cal.statut', Bouzouk::Clans_ActionEnCours)
							  ->get();
		$sainte_brigade = ($query->num_rows() == 1) ? $query->row() : null;

		if (isset($sainte_brigade))
		{
			$sainte_brigade->parametres = unserialize($sainte_brigade->parametres);
			$sainte_brigade->nom_clan = ($sainte_brigade->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('Un clan') : couleur(form_prep($sainte_brigade->nom_clan));
		}

		return $sainte_brigade;
	}

	public function clans_miserabilisme()
	{
		// ---------- Hook clans ----------
		// Tou du Culte (SdS)
		$query = $this->CI->db->select('cal.parametres, c.nom AS nom_clan, c.mode_recrutement')
						 	  ->from('clans_actions_lancees cal')
						 	  ->join('clans c', 'c.id = cal.clan_id')
							  ->where('cal.action_id', 28)
							  ->where('cal.statut', Bouzouk::Clans_ActionEnCours)
							  ->get();
		$trou_du_culte = ($query->num_rows() == 1) ? $query->row() : null;

		if (isset($trou_du_culte))
		{
			$trou_du_culte->parametres = unserialize($trou_du_culte->parametres);
			$trou_du_culte->nom_clan = ($trou_du_culte->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('Un clan') : couleur(form_prep($trou_du_culte->nom_clan));
		}

		return $trou_du_culte;
	}

	public function clans_braquage()
	{
		// ---------- Hook clans ----------
		// Braquage (Organisation)
		$query = $this->CI->db->select('c.chef_id, ca.nom AS nom_action, parametres')
							  ->from('clans_actions_lancees cal')
							  ->join('clans c', 'c.id = cal.clan_id')
							  ->join('clans_actions ca', 'ca.id = cal.action_id')
							  ->where('cal.action_id', 17)
							  ->where('cal.statut', Bouzouk::Clans_ActionEnCours)
							  ->get();
		$braquage = ($query->num_rows() == 1) ? $query->row() : null;

		if (isset($braquage))
			$braquage->parametres = unserialize($braquage->parametres);

		return $braquage;
	}

	public function clans_fabrique_de_gnoulze()
	{
		// ---------- Hook clans ----------
		// Fabrique de gnoulze (Struleone)
		$query = $this->CI->db->select('cal.parametres, ca.nom AS nom_action')
							  ->from('clans_actions_lancees cal')
							  ->join('clans c', 'c.id = cal.clan_id')
							  ->join('clans_actions ca', 'ca.id = cal.action_id')
							  ->where('cal.action_id', 26)
							  ->where('cal.statut', Bouzouk::Clans_ActionEnCours)
							  ->get();
		$fabrique = ($query->num_rows() == 1) ? $query->row() : null;

		if (isset($fabrique))
			$fabrique->parametres = unserialize($fabrique->parametres);

		return $fabrique;
	}

	public function clans_grosse_manif_syndicale()
	{
		// ---------- Hook clans ----------
		// Grosse maninf syndicale (Syndicats)
		$query = $this->CI->db->select('cal.action_id')
							  ->from('clans_actions_lancees cal')
							  ->where('cal.action_id', 5)
							  ->where('cal.statut', Bouzouk::Clans_ActionEnCours)
							  ->get();
		$grosse_manif_syndicale = ($query->num_rows() == 1) ? $query->row() : null;
		return $grosse_manif_syndicale;
	}

	public function bouzopolice($struls)
	{
		// Une chance de se faire attraper par la bouzopolice au dessus d'un certain montant
		if ($struls >= $this->config('jeu_min_struls_bouzopolice'))
		{
			$chances = $this->config('jeu_frequence_bouzopolice');

			// 50% de chances en moins
			if ($this->clans_corruption_a_agent())
				$chances *= 2;

			return mt_rand(1, $chances) == 1;
		}

		return false;
	}

	public function verifier_factures()
	{
		$nb_factures = $this->CI->db->where('joueur_id', $this->CI->session->userdata('id'))
									->where('majoration > 0')
									->count_all_results('factures');

		// Si le joueur a une ou plusieurs factures impayées depuis plus de 15 jours
		if ($nb_factures > 0)
		{
			redirect('factures/message');
		}
	}

	public function ajouter_experience($experience, $joueur_id = null, $ajout_pa = true)
	{
		$experience = abs($experience);

		if ( ! isset($joueur_id))
		{
			$joueur_id = $this->CI->session->userdata('id');
			$this->CI->session->set_userdata('experience', $this->CI->session->userdata('experience') + $experience);
		}

		// On ajoute l'expérience
		$this->CI->db->set('experience', 'experience+'.$experience, false)
					 ->where('id', $joueur_id)
					 ->update('joueurs');
		
		// Si on doit aussi ajouter les points d'actions
		if ($ajout_pa)
		{
			// On regarde le nombre de points d'action du joueur
			if ($joueur_id != $this->CI->session->userdata('id'))
			{
				$query = $this->CI->db->select('points_action')
									  ->from('joueurs')
									  ->where('id', $joueur_id)
									  ->get();
				$points_action = $query->row()->points_action;
			}

			else
				$points_action = $this->CI->session->userdata('points_action');

			// On ajoute des points d'action si le joueur n'est pas encore à la limite
			if ($points_action < $this->config('joueur_points_action_max'))
			{
				$this->CI->db->set('points_action', 'points_action+'.$experience, false)
							 ->where('id', $joueur_id)
							 ->update('joueurs');
			}
		}
	}

	public function retirer_experience($experience, $joueur_id = null)
	{
		// On ne retire que du positif
		$experience = abs($experience);

		// Joueur en session
		if ( ! isset($joueur_id))
		{
			$joueur_id = $this->CI->session->userdata('id');
			$experience = max(0, $this->CI->session->userdata('experience') - $experience);
			$this->CI->session->set_userdata('experience', $experience);
		}

		// Joueur externe
		else
		{
			$query = $this->CI->db->select('experience')
								  ->from('joueurs')
								  ->where('id', $joueur_id)
								  ->get();
			$joueur = $query->row();
			$experience = max(0, $joueur->experience - $experience);
		}

		$this->CI->db->set('experience', $experience)
					 ->where('id', $joueur_id)
					 ->update('joueurs');
	}

	public function augmente_version_session($joueur_id = null)
	{
		if ( ! isset($joueur_id))
		{
			$joueur_id = $this->CI->session->userdata('id');
		}

		$this->CI->db->set('version_session', 'version_session+1', false)
					 ->where('id', $joueur_id)
					 ->update('joueurs');
	}
	
	public function historique($texte_id_private, $texte_id_public = null, $donnees_array = array(), $joueur_id = null, $type_notif = null)
	{
		if ( ! isset($joueur_id))
			$joueur_id = $this->CI->session->userdata('id');

		if ( ! isset($type_notif))
			$type_notif = Bouzouk::Historique_Historique;
		
		$data_historique = array(
			'joueur_id'        => $joueur_id,
			'texte_id_private' => $texte_id_private,
			'texte_id_public'  => $texte_id_public,
			'donnees'          => serialize($donnees_array),
			'notification'     => $type_notif,
			'date'             => bdd_datetime()
		);
		$this->CI->db->insert('historique', $data_historique);
	}

	public function notification($texte_id_private, $donnees_array = array(), $joueur_id = null, $type_notif = null)
	{
		if ( ! isset($joueur_id))
			$joueur_id = $this->CI->session->userdata('id');

		if ( ! isset($type_notif))
			$type_notif = Bouzouk::Historique_Notification;
		
		$data_historique = array(
			'joueur_id'        => $joueur_id,
			'texte_id_private' => $texte_id_private,
			'donnees'          => serialize($donnees_array),
			'notification'     => $type_notif,
			'date'             => bdd_datetime()
		);
		$this->CI->db->insert('historique', $data_historique);
	}
	
	public function historique_moderation($texte)
	{
		$data_historique_moderation = array(
			'texte' => $texte,
			'date'  => bdd_datetime()
		);
		$this->CI->db->insert('historique_moderation', $data_historique_moderation);
	}
	
	public function fortune_totale($joueur_id = null)
	{
		$fortune = array(
			'struls'      => 0,
			'maison'      => 0,
			'marche_noir' => 0,
			'total'       => 0
		);

		if ( ! isset($joueur_id))
		{
			$joueur_id = $this->CI->session->userdata('id');

			// On récupère le nombre de struls
			$fortune['struls'] = $this->CI->session->userdata('struls');
		}

		else
		{
			// On va chercher le nombre de struls du joueur
			$query = $this->CI->db->select('struls')
								  ->from('joueurs')
								  ->where('id', $joueur_id)
								  ->get();
			$joueur = $query->row();
			$fortune['struls'] = $joueur->struls;
		}

		// ---------- Hook clans ----------
		// Magouille fiscale (Struleone)
		$query = $this->CI->db->select('c.id')
						 	  ->from('clans_actions_lancees cal')
						 	  ->join('politiciens p', 'p.clan_id = cal.clan_id', 'left')
						 	  ->join('clans c', 'c.id = cal.clan_id', 'left')
							  ->where('cal.action_id', 27)
							  ->where('cal.statut', Bouzouk::Clans_ActionEnCours)
							  ->where('(c.chef_id = '.$joueur_id.' OR p.joueur_id = '.$joueur_id.')')
							  ->get();
		$magouille_fiscale = ($query->num_rows() > 0);

		if ($magouille_fiscale)
		{
			return array(
				'struls'      => 0,
				'maison'      => 0,
				'marche_noir' => 0,
				'total'       => 0
			);
		}

		// Valeur des objets de la maison
		$query = $this->CI->db->select('SUM(o.prix * m.quantite) AS prix_total')
							  ->from('maisons m')
							  ->join('objets o', 'o.id = m.objet_id')
							  ->where('m.joueur_id', $joueur_id)
							  ->get();

		if ($query->num_rows() == 1)
		{
			$maison = $query->row();
			$fortune['maison'] = (int) $maison->prix_total;
		}

		// Valeur des objets en vente au marché noir
		$query = $this->CI->db->select('SUM(o.prix * m_n.quantite) AS prix_total')
							  ->from('marche_noir m_n')
							  ->join('objets o', 'o.id = m_n.objet_id')
							  ->where('m_n.joueur_id', $joueur_id)
							  ->get();

		if ($query->num_rows() == 1)
		{
			$marche_noir = $query->row();
			$fortune['marche_noir'] = (int) $marche_noir->prix_total;
		}

		// On calcule la fortune totale
		$fortune['total'] = $fortune['struls'] + $fortune['maison'] + $fortune['marche_noir'];

		return $fortune;
	}

	public function sont_amis($joueur_id, $ami_id)
	{
		return $this->CI->db->where('joueur_id', $joueur_id)
							->where('ami_id', $ami_id)
							->count_all_results('amis') > 0;
	}

	public function construire_historique($ligne)
	{
		$donnees = unserialize($ligne->donnees);
		$texte = $ligne->texte;
			
		for ($i = 1 ; $i <= count($donnees); $i++)
			$texte = str_replace('$'.$i, $donnees[($i-1)], $texte);

		return $texte;			
	}

}
