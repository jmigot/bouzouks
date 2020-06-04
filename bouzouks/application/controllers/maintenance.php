<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : maintenance du jeu toutes les nuits
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Maintenance extends CI_Controller
{
	private $date               = '';
	private $datetime           = '';
	private $node_server;
	private $etat_node;

	public function __construct()
	{
		parent::__construct();

		// Ce controller ne peut être appelé qu'en console
		if ( ! $this->input->is_cli_request())
			show_404();

		$this->date     = bdd_date();
		$this->datetime = bdd_datetime();

		$this->output->set_header('Content-Type: text/html; charset=utf-8');

		// On charge quelques librairies
		$this->load->library('email');
		$this->load->library('lib_clans');
		$this->load->library('lib_entreprise');
		$this->load->library('lib_joueur');
		$this->load->library('lib_mairie');
		$this->load->library('lib_missive');
		$this->load->library('lib_maintenance');
		$this->load->library('lib_plouk');
		$this->load->library('vlux/vlux_factory');
		$this->load->library('vlux/map_factory');
		$this->load->library('lib_cache_cli');
	}

	public function newsletter()
	{
		$this->email->set_mailtype('html');

		// On récupère les joueurs
		$query = $this->db->select('email, pseudo')
						  ->from('joueurs')
						  ->where_in('statut', array(Bouzouk::Joueur_Pause, Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_GameOver, Bouzouk::Joueur_Inactif))
						  ->or_where_in('id', array(16, 17, 5271))
						  ->get();

		echo $query->num_rows();

		foreach ($query->result() as $joueur)
		{
			// On prépare l'email
			$vars = array(
				'pseudo' => $joueur->pseudo,
			);
			$email = $this->load->view('email/newsletter', $vars, true);

			$this->email->from($this->bouzouk->config('email_from'), 'Bouzouks')
						->to($joueur->email)
						->subject('[Bouzouks.net] Du nouveau chez les bouzouks !')
						->message($email)
						->send();
		}
	}

	public function activer_maintenance()
	{
		$this->lib_maintenance->activer_maintenance();
	}
		
	public function desactiver_maintenance()
	{
		$this->lib_maintenance->desactiver_maintenance();
	}

	public function is_time()
	{
		$maintenance = $this->db->where('derniere_maintenance < (NOW() - INTERVAL  12 HOUR)')
								->count_all_results('mairie');

		// Maintenance déjà passée pour cette nuit
		if ($maintenance == 0)
			exit('0');

		// On regarde si l'heure est arrivée
		$query = $this->db->select('prochaine_maintenance')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();
		$time = explode(':', $mairie->prochaine_maintenance);
		
		if ((int)date('H') == (int)$time[0] && (int)date('i') >= (int)$time[1])
			exit('1');

		exit('0');
	}

	public function ecrire_heure()
	{
		// On tire aléatoirement la prochaine date de maintenance
		$heure = mt_rand(2, 4);
		$minutes = mt_rand(0, 59);

		// On recule d'une minute pour être sur de se faire capter par la cron (pas confiance)
		if ($heure == 4 && $minutes == 59)
			$minutes = 58;
			
		// On écrit la date actuelle de maintenance et la prochaine date de maintenance
		$this->db->set('derniere_maintenance', $this->datetime)
				 ->set('prochaine_maintenance', $heure.':'.$minutes)
				 ->update('mairie');
	}
	
	/* Fonctions de maintenance */
	public function run()
	{
		// On recharge la config car on est en CLI et le cache est différent de Apache
		$this->bouzouk->recharger_config();
		
		// On affiche la page de maintenance
		$this->lib_maintenance->activer_maintenance();

		// On déconnecte tous les joueurs
		$this->lib_maintenance->deconnecter_joueurs();

		//On arrête le serveur node
		//$this->map_stop();

		// On écrit l'historique des joueurs et de la mairie avant
		$this->historiques_avant();
		
		// On effectue la maintenance des events
		$this->events();
		// Désactivation de la maintenance pour l'event bouf'tête
		//$this->event_bouf_tete();
		
		// La mairie remplit les magasins tous les 3 jours
		$this->mairie();

		// On réajuste les jobs, les joueurs sont payés, puis les patrons, puis les aides au chômage
		$this->jobs();
		$this->payes();
		$this->chomage();
		$this->loterie();

		// On regarde si des entreprises sont en faillite
		$this->faillites();

		// Les entreprises produisent des nouveaux stocks d'objets
		$this->productions();

		// Taxes et impôts
		$this->anniversaires();
		$this->taxes_surprises();
		$this->impots();

		// On baisse les stats des joueurs, on vérifie s'il y a des game over, des aliénés ou si le maire n'est plus actif
		$this->joueurs();
		$this->game_over();
		$this->asile();
		$this->maire();

		// On gère les élections
		$this->elections_triche();
		$this->elections();

		// On gère les mendiants
		$this->mendiants();
		$this->dons();

		// Objets et marché noir
		$this->objets();
		$this->marche_noir();

		// Fonctions indépendantes
		$this->clans();
		$this->codes_aleatoires();
		$this->tchats();
		$this->rumeurs();
		$this->missives();
		$this->factures();
		$this->historiques();
		$this->annonces();
		$this->liens_campagne();
		$this->gazette();
		$this->plouk();

		// Une fois que plus rien ne bouge, on peut refaire les classements
		$this->classements();

		// On écrit l'historique des joueurs et de la mairie après
		$this->historiques_apres();

		// On nettoie les tables
		$this->nettoyage();

		// On effectue des tests sur le site
		$this->verifier_site();

		// On redémarre le serveur node
		//$this->map_start();
		
		// On enlève la page de maintenance
		$this->lib_maintenance->desactiver_maintenance();
	}
	
	public function events()
	{
		// --------- Event RP Zombies ------------
		$this->db->set('nb_morsure', 2)
				 ->update('event_joueurs_zombies');
		// --------- Event RP Zombies ------------

		// Spécial noel
		$this->db->set('noel', 0)
				 ->update('joueurs');

		// Event mlbobz
		if($this->bouzouk->etat_event_mlbobz()){
			$this->lib_maintenance->update_event_mlbobz();
		}
	}

	public function event_bouf_tete(){

		$verif = $this->lib_maintenance->update_bouf_tete();
		$query = $this->db->select('maire_id')
						->from('mairie')
						->get();
		$mairie = $query->row();
		
		// On ajoute à l'historique
		$this->lib_mairie->historique($verif[0], $mairie->maire_id);
		$this->lib_mairie->historique($verif[1], $mairie->maire_id);
	}

	public function anniversaires()
	{
		// On envoie une facture à tous ceux dont c'est l'anniversaire
		$query = $this->db->select('id, pseudo, YEAR(CURRENT_DATE) - YEAR(date_de_naissance) AS annees')
						  ->from('joueurs')
						  ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))
						  ->where('MONTH(date_de_naissance) = MONTH(CURRENT_DATE) AND DAY(date_de_naissance) = DAY(CURRENT_DATE)')
						  ->get();

		if ($query->num_rows() > 0)
		{
			$data_factures = array();
			$data_missives = array();
			$timbre        = $this->lib_missive->timbres(0);

			foreach ($query->result() as $joueur)
			{
				// On ajoute la facture
				$data_factures[] = array(
					'joueur_id'  => $joueur->id,
					'titre'      => 'Anniversaire',
					'montant'    => $this->bouzouk->config('factures_montant_taxe_anniversaire'),
					'majoration' => 0,
					'date'       => $this->datetime
				);

				// Message
				$message  = "	Bonjour $joueur->pseudo\n\n";
				$message .= "Pour commencer, je te demande de <b>ne pas diffuser le contenu de cette missive</b> afin de conserver la suprise qu'elle engendre (sans cette surprise, elle n'aurait aucun interet).\n\n";
				$message .= "Comme tu le sais certainement, rien n'échappe à ma vigilance. Et c'est grâce à cette vigilance que j'ai pu voir dans mes documents qu'aujourd'hui, c'était ton anniversaire et que tu fêtais tes $joueur->annees ans.\n";
				$message .= "Je te souhaite donc un très bon anniversaire et un vieillissement des plus joyeux.\n\n";
				$message .= "De plus, à cette occasion, j'ai le plaisir de t'informer que tu vas faire des heureux.\n";
				$message .= "En effet, tu es prié de verser la somme de ".struls($this->bouzouk->config('factures_montant_taxe_anniversaire'))." à la mairie avant ".$this->bouzouk->config('factures_delai_majoration')." jours. Passé ce délai, tu ne seras plus accepté dans aucun service ni aucune boutique de la ville de Vlurxtrznbnaxl nécessitant de l'argent et tu devras travailler dur afin de rembourser ta dette majorée de ".$this->bouzouk->config('factures_pourcent_majoration')."% (ben oui, quoi, c'est pas parce que c'est ton anniversaire qu'il faut croire que tu as tous les droits !).\n\n";
				$message .= "<a href='".site_url('factures')."'>Payer mes factures</a>\n\n";
				$message .= "Toutes mes félicitations et bonne chance pour la suite :)\n\n";
				$message .= "	Cordialement, le percepteur de Vlurxtrznbnaxl";

				$data_missives[] = array(
					'expediteur_id'   => Bouzouk::Robot_Percepteur,
					'destinataire_id' => $joueur->id,
					'date_envoi'      => $this->datetime,
					'timbre'          => $timbre,
					'objet'           => 'Une petite surprise...',
					'message'         => $message
				);
			}
			$this->db->insert_batch('factures', $data_factures);
			$this->db->insert_batch('missives', $data_missives);
		}
	}

	public function annonces()
	{
		// On remet en jeu les annonces en attente depuis 24h
		$this->db->set('joueur_id', null)
				 ->where('joueur_id IS NOT NULL')
				 ->where('type', Bouzouk::PetitesAnnonces_Patron)
				 ->where('NOW() > (date_acceptee + INTERVAL 24 HOUR)')
				 ->update('petites_annonces');
	}

	public function asile()
	{
		// On met à l'asile les joueurs dont le stress est à 100%
		$query = $this->db->select('id')
						  ->from('joueurs')
						  ->where('statut', Bouzouk::Joueur_Actif)
						  ->where('stress', 100)
						  ->get();
		$joueurs = $query->result();

		foreach ($joueurs as $joueur)
		{
			$this->lib_joueur->mettre_asile($joueur->id);
		}
	}

	public function clans()
	{
		// On augmente l'ancienneté des politiciens
		$this->db->set('anciennete', 'anciennete+1', false)
				 ->update('politiciens');

		// On descend d'un jour les durées restantes sur les actions en cours
		$this->db->set('jours_restants', 'jours_restants-1', false)
				 ->where('jours_restants > 0')
				 ->where('statut', Bouzouk::Clans_ActionEnCours)
				 ->update('clans_actions_lancees');

		// ---------- Hook clans ----------
		// Braquage (Organisation)
		// Fabrique de Gnoulze (Struleone)
		// Recrutement d'aliéné (SDS)
		$query = $this->db->select('id, parametres, action_id')
						  ->from('clans_actions_lancees')
						  ->where('statut', Bouzouk::Clans_ActionEnCours)
						  ->where('jours_restants', 0)
						  ->where_in('action_id', array(17, 26, 41))
						  ->get();
		$actions = $query->result();

		foreach ($actions as $action)
		{
			// Braquage (Organisation)
			if ($action->action_id == 17)
			{
				$action->parametres = unserialize($action->parametres);

				// On va chercher les objets en vente par les robots
				$query = $this->db->select('id, quantite')
								  ->from('marche_noir')
								  ->where('objet_id', $action->parametres['objet_id'])
								  ->where_in('joueur_id', $this->bouzouk->get_robots())
								  ->order_by('quantite', 'desc')
								  ->limit(1)
								  ->get();

				if ($query->num_rows() == 1)
				{
					$vente = $query->row();

					// On supprime la vente du marché noir
					$this->db->where('id', $vente->id)
							 ->delete('marche_noir');

					// On rajoute les objets au magasin
					$this->db->set('quantite', 'quantite+'.$vente->quantite, false)
							 ->where('objet_id', $action->parametres['objet_id'])
							 ->update('magasins');
				}
			}

			// Fabrique de Gnoulze (Struleone)
			else if ($action->action_id == 26)
			{
				// On va chercher les objets en vente par les robots
				$query = $this->db->select('id')
								  ->from('marche_noir')
								  ->where('objet_id', 24)
								  ->where_in('joueur_id', $this->bouzouk->get_robots())
								  ->order_by('quantite', 'desc')
								  ->limit(1)
								  ->get();

				if ($query->num_rows() == 1)
				{
					$vente = $query->row();

					// On supprime la vente du marché noir
					$this->db->where('id', $vente->id)
							 ->delete('marche_noir');
				}
			}

			// Recrutement d'aliéné (SDS))
			else if ($action->action_id == 41)
			{
				$action->parametres = unserialize($action->parametres);
						
				$query = $this->db->select('statut')
								  ->from('joueurs')
								  ->where('id', $action->parametres['joueur_id'])
								  ->get();

				$joueur_aliene = $query->num_rows() == 1 ? $query->row() : null;
						
				if ( ! isset($joueur_aliene) || $joueur_aliene->statut != Bouzouk::Joueur_Asile)
				{
					// On stoppe l'action
					$this->db->set('statut', Bouzouk::Clans_ActionTerminee)
							 ->where('id', $action->id)
							 ->update('clans_actions_lancees');
				}
			}
		}

		// ---------- Hook clans ----------
		// Prise de pouvoir (Parti Politique)
		$query = $this->db->select('cal.id, cal.parametres, c.chef_id')
						  ->from('clans_actions_lancees cal')
						  ->join('clans c', 'c.id = cal.clan_id')
						  ->where('cal.action_id', 8)
						  ->where('cal.statut', Bouzouk::Clans_ActionEnCours)
						  ->get();

		if ($query->num_rows() == 1)
		{
			$action = $query->row();
			$action->parametres = unserialize($action->parametres);

			// On destitue le maire
			$this->lib_clans->reponse_prise_de_pouvoir('ceder', $action);
		}

		// Les action qui ne doivent pas s'arrêter à la maintenance, mais à une heure bien précise
		// -> Corruption à agent (1h)
		// -> Concurrence gênante (24h)
		// -> Pillage compulsif (24h)
		// -> Malédiction du Schnibble (1h)
		$actions_sur_duree = array(25, 38, 39, 40);
		
		// On stoppe toutes les actions en cours qui n'ont plus de jours restants
		$this->db->set('statut', Bouzouk::Clans_ActionTerminee)
				 ->where('statut', Bouzouk::Clans_ActionEnCours)
				 ->where('jours_restants', 0)
				 ->where_not_in('action_id', $actions_sur_duree)
				 ->update('clans_actions_lancees');

		// Pour chaque type de clan
		foreach (array(Bouzouk::Clans_TypeSyndicat, Bouzouk::Clans_TypePartiPolitique, Bouzouk::Clans_TypeOrganisation) as $type)
		{
			// On récupère la meilleure enchère s'il y en a une
			$query = $this->db->select('clan_id, action_id, parametres, montant_enchere, annulee')
							  ->from('clans_encheres')
							  ->where('clan_type', $type)
							  ->order_by('montant_enchere', 'desc')
							  ->limit(1)
							  ->get();

			if ($query->num_rows() == 1)
			{
				$enchere = $query->row();

				// On retire les points au clan
				$this->lib_clans->retirer_points_action($enchere->clan_id, $enchere->montant_enchere);
				
				// On va chercher tous les alliés s'il y en a
				$query = $this->db->select('caa.clan_invite_id, ca.cout_par_allie')
								  ->from('clans_actions_allies caa')
								  ->join('clans_actions ca', 'ca.id = caa.action_id')
								  ->where('caa.clan_createur_id', $enchere->clan_id)
								  ->where('caa.action_id', $enchere->action_id)
								  ->get();
				$allies = $query->result();

				foreach ($allies as $allie)
					$this->lib_clans->retirer_points_action($allie->clan_invite_id, $allie->cout_par_allie);

				// On lance l'action
				if ( ! $enchere->annulee)
					$this->lib_clans->lancer_action($enchere->clan_id, $enchere->action_id, unserialize($enchere->parametres), $enchere->montant_enchere);
			}
		}

		// On nettoie les enchères et les alliances
		$this->db->truncate('clans_encheres');
		$this->db->truncate('clans_actions_allies');
	}

	public function classements()
	{
		// ---------- Hook clans ----------
		// Magouille fiscale (Struleone)
		$joueurs_magouille_fiscale = $this->lib_clans->magouille_fiscale_en_cours();
	
		// On récupère les classements actuels
		$ancien_classement_entreprises = array();

		$query = $this->db->select('entreprise_id, position')
						  ->from('classement_entreprises')
						  ->get();

		foreach ($query->result() as $classement)
			$ancien_classement_entreprises[$classement->entreprise_id] = $classement->position;
			
		$this->db->truncate('classement_entreprises');
			
		// On établit les classements des entreprises
		$query = $this->db->select('e.id, e.nom, j.id AS patron_id, j.pseudo AS patron_pseudo, COUNT(h_e.id) AS nb_lignes, SUM(h_e.nb_employes) AS nb_employes, SUM(h_e.rentree_argent) AS rentree_argent, SUM(h_e.struls) AS struls')
						  ->from('entreprises e')
						  ->join('historique_entreprises h_e', 'h_e.entreprise_id = e.id')
						  ->join('joueurs j', 'j.id = e.chef_id')
						  ->where('e.classement', 1)
						  ->group_by('h_e.entreprise_id')
						  ->get();

		if ($query->num_rows() > 0)
		{
			$entreprises = $query->result();
			$data_classement_entreprises = array();

			// Pour chaque entreprise
			foreach ($entreprises as $entreprise)
			{
				// On calcule son score
				$score = round(((0.27 * ($entreprise->nb_employes / $entreprise->nb_lignes)) +
							   (0.63 * ($entreprise->struls / $entreprise->nb_lignes)) +
							   (0.10 * ($entreprise->rentree_argent / $entreprise->nb_lignes))) / 10.0);

				// On l'insère dans le classement
				$data_classement_entreprises[] = array(
					'entreprise_id'  => $entreprise->id,
					'nom_entreprise' => $entreprise->nom,
					'chef_id'        => $entreprise->patron_id,
					'nom_chef'       => $entreprise->patron_pseudo,
					'position'       => 0,
					'score'          => $score,
				);
			}
			$this->db->insert_batch('classement_entreprises', $data_classement_entreprises);

			// On récupère la liste des entreprises du classement triées par score
			$query = $this->db->select('entreprise_id')
							  ->from('classement_entreprises')
							  ->order_by('score', 'desc')
							  ->get();

			$entreprises = $query->result();
			$i = 1;

			// On définit la position de chaque entreprise dans l'ordre
			foreach ($entreprises as $entreprise)
			{
				// On récupère l'ancienne position
				$ancienne_position = isset($ancien_classement_entreprises[$entreprise->entreprise_id]) ? $ancien_classement_entreprises[$entreprise->entreprise_id] : 0;

				// On met à jour la position et l'évolution
				$this->db->set('position', $i++)
						 ->set('evolution', $this->lib_maintenance->classements_evolution_entreprise($ancienne_position, $i - 1))
						 ->where('entreprise_id', $entreprise->entreprise_id)
						 ->update('classement_entreprises');
			}
		}

		// On établit les classements des joueurs
		$data_classement_joueurs = array();

		// Richesse
		$query = $this->db->select('id, pseudo, struls, sexe')
						  ->from('joueurs')
						  ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))
						  ->order_by('struls', 'desc')
						  ->get();
		$joueurs = $query->result();
		$i = 1;

		foreach ($joueurs as $joueur)
		{
			// ---------- Hook clans ----------
			// Magouille fiscale (Struleone)
			if (in_array($joueur->id, $joueurs_magouille_fiscale))
				continue;

			$data_classement_joueurs[] = array(
				'joueur_id' => $joueur->id,
				'pseudo'    => $joueur->pseudo,
				'type'      => Bouzouk::Classement_Richesse,
				'position'  => $i,
				'valeur'    => $joueur->struls,
				'sexe'      => $joueur->sexe,
				'evolution' => $this->lib_maintenance->classements_evolution_joueur(Bouzouk::Classement_Richesse, $joueur->id, $i)
			);
			$i++;
		}

		foreach ($joueurs as $joueur)
		{
			// ---------- Hook clans ----------
			// Magouille fiscale (Struleone)
			if ( ! in_array($joueur->id, $joueurs_magouille_fiscale))
				continue;

			$data_classement_joueurs[] = array(
				'joueur_id' => $joueur->id,
				'pseudo'    => $joueur->pseudo,
				'type'      => Bouzouk::Classement_Richesse,
				'position'  => $i,
				'valeur'    => 0,
				'sexe'      => $joueur->sexe,
				'evolution' => $this->lib_maintenance->classements_evolution_joueur(Bouzouk::Classement_Richesse, $joueur->id, $i)
			);
			$i++;
		}

		// Expérience
		$query = $this->db->select('id, pseudo, experience, sexe')
						  ->from('joueurs')
						  ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))
						  ->order_by('experience', 'desc')
						  ->get();
		$joueurs = $query->result();
		$i = 1;

		foreach ($joueurs as $joueur)
		{
			$data_classement_joueurs[] = array(
				'joueur_id' => $joueur->id,
				'pseudo'    => $joueur->pseudo,
				'type'      => Bouzouk::Classement_Experience,
				'position'  => $i,
				'valeur'    => $joueur->experience,
				'sexe'      => $joueur->sexe,
				'evolution' => $this->lib_maintenance->classements_evolution_joueur(Bouzouk::Classement_Experience, $joueur->id, $i)
			);
			$i++;
		}

		// Fortune
		$query = $this->db->select('j.id, j.pseudo, j.struls, j.sexe, SUM(m.quantite * o.prix) AS struls_maison')
						  ->from('joueurs j')
						  ->join('maisons m', 'm.joueur_id = j.id', 'left')
						  ->join('objets o', 'o.id = m.objet_id', 'left')
						  ->where_in('j.statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))
						  ->where('j.classement', 1)
						  ->group_by('j.id')
						  ->order_by('j.struls', 'desc')
						  ->get();
		$joueurs = $query->result();
		$tab_joueurs = array();
		$tab_joueurs_objets = array();
		
		foreach ($joueurs as $joueur)
		{
			// ---------- Hook clans ----------
			// Magouille fiscale (Struleone)
			if (in_array($joueur->id, $joueurs_magouille_fiscale))
				$tab_joueurs[$joueur->id] = 0;

			else
			{
				$joueur->struls_marche_noir = 0;
			
				// On va chercher la fortune du marché noir
				$query = $this->db->select('SUM(m_n.quantite * o.prix) AS struls_marche_noir')
								  ->from('marche_noir m_n')
								  ->join('objets o', 'o.id = m_n.objet_id')
								  ->where('m_n.joueur_id', $joueur->id)
								  ->get();

				if ($query->num_rows() == 1)
				{
					$marche_noir = $query->row();
					$joueur->struls_marche_noir = $marche_noir->struls_marche_noir;
				}
				
				// On fait le total
				$tab_joueurs[$joueur->id] = $joueur->struls + $joueur->struls_maison + $joueur->struls_marche_noir;
			}

			$tab_joueurs_objets[$joueur->id] = $joueur;
		}

		// On trie le tableau
		arsort($tab_joueurs);

		// On ajoute au classement
		$i = 1;

		foreach ($tab_joueurs as $joueur_id => $fortune)
		{
			$data_classement_joueurs[] = array(
				'joueur_id' => $joueur_id,
				'pseudo'    => $tab_joueurs_objets[$joueur_id]->pseudo,
				'type'      => Bouzouk::Classement_Fortune,
				'position'  => $i,
				'valeur'    => $fortune,
				'sexe'      => $tab_joueurs_objets[$joueur_id]->sexe,
				'evolution' => $this->lib_maintenance->classements_evolution_joueur(Bouzouk::Classement_Fortune, $joueur_id, $i)
			);

			$i++;
		}

		// Plouk
		$query = $this->db->select('id, pseudo, plouk_stats, sexe')
						  ->from('joueurs')
						  ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))
						  ->get();
		$joueurs = $query->result();
		$tab_joueurs = array();
		$tab_joueurs_objets = array();
		
		foreach ($joueurs as $joueur)
		{
			$joueur->plouk_stats = explode('|', $joueur->plouk_stats);
				
			// On fait le total
			if ($joueur->plouk_stats[0] + $joueur->plouk_stats[1] + $joueur->plouk_stats[2] >= $this->bouzouk->config('plouk_nb_parties_classement'))
			{
				$tab_joueurs[$joueur->id] = round((2.0 * $joueur->plouk_stats[0] - 2.0 * $joueur->plouk_stats[1] + $joueur->plouk_stats[2]) / 5.0, 2);
				$tab_joueurs_objets[$joueur->id] = $joueur;
			}
		}

		// On trie le tableau
		arsort($tab_joueurs);

		// On ajoute au classement
		$i = 1;
		
		foreach ($tab_joueurs as $joueur_id => $ratio)
		{
			$data_classement_joueurs[] = array(
				'joueur_id' => $joueur_id,
				'pseudo'    => $tab_joueurs_objets[$joueur_id]->pseudo,
				'type'      => Bouzouk::Classement_Plouk,
				'position'  => $i,
				'valeur'    => $ratio,
				'sexe'      => $tab_joueurs_objets[$joueur_id]->sexe,
				'evolution' => $this->lib_maintenance->classements_evolution_joueur(Bouzouk::Classement_Plouk, $joueur_id, $i)
			);

			$i++;
		}

		// Collectionneurs
		$query = $this->db->query('SELECT j.id AS joueur_id, j.pseudo AS joueur_pseudo, j.sexe AS joueur_sexe, o.id, m.quantite, m.peremption, o.rarete, o.nom '.
								  'FROM maisons m '.
								  'JOIN objets o ON o.id = m.objet_id '.
								  'JOIN joueurs j ON j.id = m.joueur_id '.
								  'WHERE o.rarete IN ("rare", "tres_rare") AND j.classement = 1 '.
								  'ORDER BY m.joueur_id, m.objet_id, IF(m.peremption = -1, 999999, m.peremption) DESC'); // ici l'illimité est trafiqué
		$objets = $query->result();
		$joueurs          = array();
		$joueur_id_actuel = 0;
		$objet_id_actuel  = 0;
		$joueurs_objets   = array();
		
		foreach ($objets as $objet)
		{
			// Nouveau joueur : score 0
			if ($objet->joueur_id != $joueur_id_actuel)
			{
				$joueurs[$objet->joueur_id] = 0;
				$joueurs_objets[$objet->joueur_id] = $objet;
				$joueur_id_actuel = $objet->joueur_id;
				$objet_id_actuel = 0;
			}

			// Coefficient de score selon la péremption
			$coeff_peremption = 1;

			if ($objet->peremption == -1)
				$coeff_peremption = 1.2;

			else if ($objet->peremption == 0)
				$coeff_peremption = 0.5;

			// On score l'objet
			$score = ($objet->quantite - 1) * $coeff_peremption;

			if ($objet->rarete == 'tres_rare')
				$score *= 3;
				
			// Nouvel objet : on ajoute un gros coefficient pour le dernier objet
			if ($objet->id != $objet_id_actuel)
			{
				if ($objet->rarete == 'rare')
					$score += (15 * $coeff_peremption);

				else
					$score += (30 * $coeff_peremption);
					
				$objet_id_actuel = $objet->id;
			}

			// Sinon objet en double : on ajoute simplement le score
			else
			{
				if ($objet->rarete == 'rare')
					$score += (1 * $coeff_peremption);

				else
					$score += (3 * $coeff_peremption);
			}

			$joueurs[$objet->joueur_id] += $score;
		}

		// On trie le tableau
		arsort($joueurs);
		
		// On ajoute au classement
		$i = 1;

		foreach ($joueurs as $joueur_id => $score)
		{
			$data_classement_joueurs[] = array(
				'joueur_id' => $joueur_id,
				'pseudo'    => $joueurs_objets[$joueur_id]->joueur_pseudo,
				'type'      => Bouzouk::Classement_Collection,
				'position'  => $i,
				'valeur'    => $score,
				'sexe'      => $joueurs_objets[$joueur_id]->joueur_sexe,
				'evolution' => $this->lib_maintenance->classements_evolution_joueur(Bouzouk::Classement_Collection, $joueur_id, $i)
			);

			$i++;
		}

		$this->db->truncate('classement_joueurs');
		
		if (count($data_classement_joueurs) > 0)
		{
			// On enregistre le classement
			$this->db->insert_batch('classement_joueurs', $data_classement_joueurs);

			// Si on est lundi, on publie un article dans la gazette
			if (date('w') == 1 && count($data_classement_joueurs) >= 10)
			{
				// On désactive les anciens articles de la gazette concernant le classement
				$this->db->where('type', Bouzouk::Gazette_Classement)
						 ->delete('gazettes');

				// On va chercher le doyen d'expérience, de struls et de fortune
				$doyens = array();

				foreach (array(Bouzouk::Classement_Experience, Bouzouk::Classement_Fortune, Bouzouk::Classement_Richesse, Bouzouk::Classement_Plouk, Bouzouk::Classement_Collection) as $type)
				{
					$query = $this->db->select('joueur_id, pseudo, valeur')
									  ->from('classement_joueurs')
									  ->where('type', $type)
									  ->order_by('position')
									  ->limit(1)
									  ->get();
					$doyens[$type] = $query->row();
				}

				// Expérience
				$texte =  "Cette semaine c'est ".profil($doyens[Bouzouk::Classement_Experience]->joueur_id, $doyens[Bouzouk::Classement_Experience]->pseudo).' qui est le doyen de la ville avec ';
				$texte .= '<span class="pourpre">'.intval($doyens[Bouzouk::Classement_Experience]->valeur)." d'expérience</span><br><br>";

				// Fortune
				$texte .= profil($doyens[Bouzouk::Classement_Fortune]->joueur_id, $doyens[Bouzouk::Classement_Fortune]->pseudo)." est à l'honneur avec une fortune personnelle de ".struls($doyens[Bouzouk::Classement_Fortune]->valeur).'<br><br>';

				// Plouk
				$texte .= profil($doyens[Bouzouk::Classement_Plouk]->joueur_id, $doyens[Bouzouk::Classement_Plouk]->pseudo).' est le meilleur joueur de Plouk avec un ratio de <span class="pourpre">'.$doyens[Bouzouk::Classement_Plouk]->valeur.'</span><br><br>';

				// Collection
				$texte .= profil($doyens[Bouzouk::Classement_Collection]->joueur_id, $doyens[Bouzouk::Classement_Collection]->pseudo)." a bien chiné puisqu'il se hisse premier du classement collectionneurs avec une valeur d'objets rares de <span class='pourpre'>".$doyens[Bouzouk::Classement_Collection]->valeur.'</span><br><br>';

				// Struls
				$texte .= 'Enfin '.profil($doyens[Bouzouk::Classement_Richesse]->joueur_id, $doyens[Bouzouk::Classement_Richesse]->pseudo)." a le plus gros porte-struls en se baladant dans les rues avec ".struls($doyens[Bouzouk::Classement_Richesse]->valeur);

				// On ajoute un nouvel article dans la gazette
				$data_gazettes = array(
					'auteur_id' => Bouzouk::Robot_MissPoohLett,
					'type'      => Bouzouk::Gazette_Classement,
					'titre'     => 'Classement de la semaine',
					'texte'     => $texte,
					'image_url' => '',
					'date'      => bdd_datetime(),
					'en_ligne'  => Bouzouk::Gazette_Publie
				);
				$this->db->insert('gazettes', $data_gazettes);
			}
		}
	}

	public function codes_aleatoires()
	{
		// On supprime tous les codes aléatoires qui ont plus de 24h
		$this->db->where('date < (NOW() - INTERVAL 24 HOUR)')
				 ->where_in('type', array(Bouzouk::Code_PassPerdu, Bouzouk::Code_ChangerEmail))
				 ->delete('codes_aleatoires');

		// On supprime les comptes inscrits non-activés depuis 30 jours
		$query = $this->db->select('joueur_id')
						  ->from('codes_aleatoires')
						  ->where('date < (NOW() - INTERVAL 30 DAY)')
						  ->where_in('type', array(Bouzouk::Code_Inscription))
						  ->get();

		if ($query->num_rows() > 0)
		{
			$codes = $query->result();

			// On récupère les identifiants des joueurs
			$joueur_ids = array();
			foreach ($codes as $code)
			{
				$joueur_ids[] = $code->joueur_id;

				// On supprime le compte
				$this->lib_joueur->supprimer_joueur($code->joueur_id);
			}

			// On supprime les codes aléatoires
			$this->db->where_in('joueur_id', $joueur_ids)
					 ->delete('codes_aleatoires');
		}
	}

	public function chomage()
	{
		// On va chercher le montant de l'aide au chômage
		$query = $this->db->select('aide_chomage, maire_id')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();

		// Si l'aide existe
		if ($mairie->aide_chomage > 0)
		{
			// On va chercher tous les joueurs chômeurs (qui ne sont pas employés, patrons ou maire de la ville)
			$query = $this->db->select('j.id')
							->from('joueurs j')
							->join('employes em', 'em.joueur_id = j.id', 'left')
							->join('entreprises en', 'en.chef_id = j.id', 'left')
							->where('j.statut', Bouzouk::Joueur_Actif)
							->where('em.joueur_id', null)
							->where('en.chef_id', null)
							->where('j.id !=', $mairie->maire_id)
							->get();

			// S'il existe des chômeurs
			if ($query->num_rows() > 0)
			{
				// On récupère les identifiants des chômeurs
				$joueurs_ids = array();
				foreach ($query->result() as $joueur)
				{
					$joueurs_ids[] = $joueur->id;
					$this->bouzouk->historique(78, null, array(struls($mairie->aide_chomage)), $joueur->id);
				}

				// On leur ajoute le montant de l'aide au chômage
				$this->db->set('struls', 'struls+'.$mairie->aide_chomage, false)
						->where_in('id', $joueurs_ids)
						->update('joueurs');

				// On retire le montant total de la mairie
				$nb_chomeurs = count($joueurs_ids);
				$total_aides = $nb_chomeurs * $mairie->aide_chomage;

				if ($this->lib_mairie->retirer_struls($total_aides))
				{
					// On ajoute à l'historique de la mairie
					$this->lib_mairie->historique("L'aide au chômage de ".struls($mairie->aide_chomage)." a été versée à <span class='pourpre'>".pluriel($nb_chomeurs,'bouzouk')."</span> pour un total de ".struls($total_aides), $mairie->maire_id);
				}

				else
				{
					$this->lib_mairie->historique("L'aide au chômage de ".struls($mairie->aide_chomage)." n'a pas pu être versée en raison de l'état des comptes de la mairie", $mairie->maire_id);
				}
			}
		}
	}

	public function dons()
	{
		// On supprime les enregistrements de donations des mendiants
		$this->db->where('type', Bouzouk::Donation_Mendiant)
				 ->delete('donations');

		// On supprime les enregistrements de donations de la mairie de plus de 2 MOIS
		$this->db->where_in('type', array(Bouzouk::Donation_MairieBouzouk, Bouzouk::Donation_MairieBouzouks, Bouzouk::Donation_MairieMendiants, Bouzouk::Donation_MairieTous))
				 ->where('date < (NOW() - INTERVAL '.$this->bouzouk->config('mairie_intervalle_don_bouzouk').' HOUR)')
				 ->delete('donations');

		// On supprime les enregistrements des injections dans les entreprises
		$this->db->where('type', Bouzouk::Donation_Entreprise)
				 ->where('date < (NOW() - INTERVAL '.$this->bouzouk->config('entreprises_intervalle_max_injection').' HOUR)')
				 ->delete('donations');
	}

	public function elections()
	{
		// On va chercher la date de début des élections actuelles
		$query = $this->db->select('date_debut_election')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();
		$date_actuelle = strtotime($this->date);
		$duree_candidatures = $this->bouzouk->config('elections_duree_candidatures');
		$duree_tour_1 = $this->bouzouk->config('elections_duree_tour_1');
		$duree_tour_2 = $this->bouzouk->config('elections_duree_tour_2');
		$duree_tour_3 = $this->bouzouk->config('elections_duree_tour_3');
		$duree_interdiction_propagande = $duree_candidatures - 1;

		// Un jour avant le 1er tour, on ouvre le forum propagande
		if ($date_actuelle == strtotime($mairie->date_debut_election.'+'.$duree_interdiction_propagande.' DAY'))
		{
			$this->db->where('forum_id', Bouzouk::Tobozon_IdForumPropagande)
					 ->delete('tobozon_forum_perms');
		}

		// Si on doit passer au 1er tour
		else if ($date_actuelle == strtotime($mairie->date_debut_election.'+'.$duree_candidatures.' DAY'))
		{
			// On passe au tour 1 à la mairie
			$this->db->set('tour_election', Bouzouk::Elections_Tour1)
					 ->update('mairie');

			// On passe tous les candidats en tour 1
			$this->db->set('tour', Bouzouk::Elections_Tour1)
					 ->where('tour', Bouzouk::Elections_Candidater)
					 ->update('elections');

			// On réinitialise les votes
			$this->db->truncate('elections_votes');
		}

		// Si on doit passer au tour 2 à la mairie
		else if ($date_actuelle == strtotime($mairie->date_debut_election.'+'.($duree_candidatures + $duree_tour_1).' DAY'))
		{
			// On passe au tour 2 à la mairie
			$this->db->set('tour_election', Bouzouk::Elections_Tour2)
					 ->update('mairie');

			// On recopie les votes du tour 1 dans le champ spécial pour les stats de fin d'élections
			$this->db->set('votes_tour1', 'votes', false)
					 ->where('tour', Bouzouk::Elections_Tour1)
					 ->update('elections');

			// On récupère le nombre total de votes du tour 1
			$query = $this->db->select_sum('votes')
							  ->from('elections')
							  ->where('tour', 1)
							  ->get();
			$somme = $query->row();
			$nb_votes = max(1, $somme->votes);

			// On passe les 6 meilleurs candidats en tour 2
			$this->db->set('tour', Bouzouk::Elections_Tour2)
					 ->where('tour', Bouzouk::Elections_Tour1)
					 ->order_by('votes', 'desc')
					 ->order_by('joueur_id', 'random')
					 ->limit(6)
					 ->update('elections');

			// On récupère la config du jeu
			$gain_xp = $this->bouzouk->config('elections_gain_xp_tour2');
			$perte_xp = $this->bouzouk->config('elections_perte_xp_tour2');

			// On envoit une missive à chaque candidat du tour 1 et du tour 2 pour expliquer où il en est
			$query = $this->db->select('j.id, j.pseudo, e.tour, e.votes')
							  ->from('elections e')
							  ->join('joueurs j', 'j.id = e.joueur_id')
							  ->get();

			// S'il y a au moins 1 candidat (très probable quand même :))
			if ($query->num_rows() > 0)
			{
				$candidats     = $query->result();
				$data_missives = array();
				$timbre        = $this->lib_missive->timbres(0);

				foreach ($candidats as $candidat)
				{
					$pourcentage = round($candidat->votes * 100 / $nb_votes, 1);

					if ($candidat->tour == Bouzouk::Elections_Tour1)
					{
						// On retire de l'expérience au candidat
						$this->bouzouk->retirer_experience($perte_xp, $candidat->id);

						// On prépare une missive selon le résultat du candidat
						$message = "	Bonjour $candidat->pseudo\n\n";

						// Aucun vote
						if ($candidat->votes == 0)
							$message .= "Tu n'es vraiment pas aimé ! Personne n'a voté pour toi...Autant abréger tes souffrances : tu es éliminé de ces élections.\n";

						// Un seul vote
						else if ($candidat->votes == 1)
							$message .= "Tu n'es vraiment pas aimé ! Avec <span class='pourpre'>un seul vote</span> en ta faveur, autant abréger tes souffrances : tu es éliminé de ces élections.\n";

						// Plusieurs votes
						else
							$message .= "Avec seulement <span class='pourpre'>$candidat->votes votes</span> au bout de ".pluriel($duree_tour_1, 'jour')." (soit <span class='pourpre'>$pourcentage% des votes</span>), ce n'est plus la peine de rêver : tu es éliminé.\n";

						$message .= "Tu perds <span class='pourpre'>-$perte_xp xp</span>.\n\n";
						$message .= "	Amicalement, le dépouilleur des élections de Vlurxtrznbnaxl.";

						$data_missives[] = array(
							'expediteur_id'   => Bouzouk::Robot_JF_Sebastien,
							'destinataire_id' => $candidat->id,
							'date_envoi'      => $this->datetime,
							'timbre'          => $timbre,
							'objet'           => 'Résultats élections [Tour 1]',
							'message'         => $message
						);
					}

					else if ($candidat->tour == Bouzouk::Elections_Tour2)
					{
						// On ajoute de l'expérience aux candidats retenus pour le tour2
						$this->bouzouk->ajouter_experience($gain_xp, $candidat->id);

						// On prépare une missive selon le résultat du candidat
						$message = "	Bonjour $candidat->pseudo\n\n";
						$message .= "Avec <span class='pourpre'>".pluriel($candidat->votes, 'vote')."</span> au bout de ".pluriel($duree_tour_1, 'jour')." (soit <span class='pourpre'>$pourcentage% des votes</span>), tu es admis au 2ème tour des élections.\n";
						$message .= "Félicitations ! :)\n";
						$message .= "Tu gagnes <span class='pourpre'>+$gain_xp xp</span>.\n\n";
						$message .= "	Amicalement, le dépouilleur des élections de Vlurxtrznbnaxl.";

						$data_missives[] = array(
							'expediteur_id'   => Bouzouk::Robot_JF_Sebastien,
							'destinataire_id' => $candidat->id,
							'date_envoi'      => $this->datetime,
							'timbre'          => $timbre,
							'objet'           => 'Résultats élections [Tour 1]',
							'message'         => $message
						);
					}
				}
				$this->db->insert_batch('missives', $data_missives);
			}

			// On réinitialise les votes
			$this->db->set('votes', 0)
					 ->update('elections');
		}

		// Si on doit passer au 3ème tour
		else if ($date_actuelle == strtotime($mairie->date_debut_election.'+'.($duree_candidatures + $duree_tour_1 + $duree_tour_2).' DAY'))
		{
			// On passe au tour 3 à la mairie
			$this->db->set('tour_election', Bouzouk::Elections_Tour3)
					 ->update('mairie');

			// On recopie les votes du tour 2 dans le champ spécial pour les stats de fin d'élections
			$this->db->set('votes_tour2', 'votes', false)
					 ->where('tour', Bouzouk::Elections_Tour2)
					 ->update('elections');

			// On récupère le nombre total de votes
			$query = $this->db->select_sum('votes')
							  ->from('elections')
							  ->where('tour', 2)
							  ->get();
			$somme = $query->row();
			$nb_votes = max(1, $somme->votes);

			// On passe les 6 meilleurs candidats au tour 3
			$this->db->set('tour', Bouzouk::Elections_Tour3)
					 ->where('tour', Bouzouk::Elections_Tour2)
					 ->order_by('votes', 'desc')
					 ->order_by('votes_tour1', 'desc')
					 ->order_by('joueur_id', 'random')
					 ->limit(2)
					 ->update('elections');

			// On récupère la config du jeu
			$gain_xp = $this->bouzouk->config('elections_gain_xp_tour3');

			// On envoit une missive à chaque candidat du tour 2 et du tour 3 pour expliquer où il en est
			$query = $this->db->select('j.id, j.pseudo, e.tour, e.votes')
							  ->from('elections e')
							  ->join('joueurs j', 'j.id = e.joueur_id')
							  ->where('e.tour', Bouzouk::Elections_Tour2)
							  ->or_where('e.tour', Bouzouk::Elections_Tour3)
							  ->get();

			// S'il y a au moins 1 candidat (très probable quand même :))
			if ($query->num_rows() > 0)
			{
				$candidats = $query->result();
				$data_missives    = array();
				$timbre           = $this->lib_missive->timbres(0);

				foreach ($candidats as $candidat)
				{
					$pourcentage = round($candidat->votes * 100 / $nb_votes, 1);

					if ($candidat->tour == Bouzouk::Elections_Tour2)
					{
						// On prépare une missive selon le résultat du candidat
						$message = "	Bonjour $candidat->pseudo\n\n";

						// Aucun vote
						if ($candidat->votes == 0)
							$message .= "Tu n'es vraiment pas aimé ! Personne n'a voté pour toi...Autant abréger tes souffrances : tu es éliminé de ces élections.\n\n";

						// Un seul vote
						else if ($candidat->votes == 1)
							$message .= "Tu n'es vraiment pas aimé ! Avec <span class='pourpre'>un seul vote</span> en ta faveur, autant abréger tes souffrances : tu es éliminé de ces élections.\n\n";

						// Plusieurs votes
						else
							$message .= "Avec seulement <span class='pourpre'>$candidat->votes votes</span> au bout de ".pluriel($duree_tour_2, 'jour')." (soit <span class='pourpre'>$pourcentage% des votes</span>), tu ne fais pas le poids ! Des messages anonymes te dissuadent de continuer ta campagne : tu ne fais plus partie des candidats. Dommage...\n\n";

						$message .= "	Amicalement, le dépouilleur des élections de Vlurxtrznbnaxl.";

						$data_missives[] = array(
							'expediteur_id'   => Bouzouk::Robot_JF_Sebastien,
							'destinataire_id' => $candidat->id,
							'date_envoi'      => $this->datetime,
							'timbre'          => $timbre,
							'objet'           => 'Résultats élections [Tour 2]',
							'message'         => $message
						);
					}

					else if ($candidat->tour == Bouzouk::Elections_Tour3)
					{
						// On ajoute de l'expérience aux candidats retenus pour le tour3
						$this->bouzouk->ajouter_experience($gain_xp, $candidat->id);

						// On prépare une missive selon le résultat du candidat
						$message = "	Bonjour $candidat->pseudo\n\n";
						$message .= "Avec <span class='pourpre'>".pluriel($candidat->votes, 'vote')."</span> au bout de ".pluriel($duree_tour_2, 'jour')." (soit <span class='pourpre'>$pourcentage% des votes</span>), tu es admis en duel au 3ème tour des élections.\n";
						$message .= "Félicitations ! :)\n";
						$message .= "Tu gagnes <span class='pourpre'>+$gain_xp xp</span>.\n\n";
						$message .= "Bonne bagarre !\n\n";
						$message .= "	Amicalement, le dépouilleur des élections de Vlurxtrznbnaxl.";

						$data_missives[] = array(
							'expediteur_id'   => Bouzouk::Robot_JF_Sebastien,
							'destinataire_id' => $candidat->id,
							'date_envoi'      => $this->datetime,
							'timbre'          => $timbre,
							'objet'           => 'Résultats élections [Tour 2]',
							'message'         => $message
						);
					}
				}
				$this->db->insert_batch('missives', $data_missives);
			}

			// On réinitialise les votes
			$this->db->set('votes', 0)
					 ->update('elections');
		}

		// Si c'est la fin des élections, on élit le nouveau maire
		else if ($date_actuelle == strtotime($mairie->date_debut_election.'+'.($duree_candidatures + $duree_tour_1 + $duree_tour_2 + $duree_tour_3).' DAY'))
		{
			// On recopie les votes du tour 3 dans le champ spécial pour les stats de fin d'élections
			$this->db->set('votes_tour3', 'votes', false)
					 ->where('tour', Bouzouk::Elections_Tour3)
					 ->update('elections');

			// On va chercher le nouveau maire et son suppléant
			$query = $this->db->select('j.id, j.pseudo, e.votes')
							  ->from('elections e')
							  ->join('joueurs j', 'j.id = e.joueur_id')
							  ->where('tour', 3)
							  ->order_by('votes', 'desc')
							  ->order_by('votes_tour2', 'desc')
							  ->order_by('votes_tour1', 'desc')
							  ->order_by('j.experience', 'desc')
							  ->get();

			$data_mairie = array(
				'tour_election'       => Bouzouk::Elections_Candidater,
				'date_debut_election' => $this->date
			);

			// S'il y a au moins 1 candidat
			if ($query->num_rows() > 0)
			{
				$candidats = $query->result();

				// On récupère le nombre de votes
				$query = $this->db->select_sum('votes')
							  ->from('elections')
							  ->where('tour', 3)
							  ->get();
				$somme = $query->row();

				// On envoit des missives aux deux
				$data_missives           = array();
				$timbre                  = $this->lib_missive->timbres(0);
				$maire                   = $candidats[0];
				$nb_vote                 = $nb_votes = max(1, $somme->votes);
				$pourcentage_maire       = round($maire->votes * 100 / $nb_votes, 1);
				$data_mairie['maire_id'] = $maire->id;

				// On insère le nouveau maire dans l'historique
				$data_historique_maires = array(
					'maire_id'   => $maire->id,
					'date_debut' => $this->datetime
				);
				$this->db->insert('historique_maires', $data_historique_maires);

				// On prépare une missive pour le maire
				$message = "	Bonjour $maire->pseudo\n\n";
				$message .= "Avec <span class='pourpre'>".pluriel($maire->votes, 'vote')."</span> au bout de ".pluriel($duree_tour_3, 'jour')." (soit <span class='pourpre'>$pourcentage_maire% des votes</span>), tu es élu nouveau maire de la ville :).\n";
				$message .= "Tu peux désormais prendre tes fonctions dans <a href='".site_url('mairie/gerer')."' title='Gérer la mairie'>ton nouveau bureau</a>.\n\n";
				$message .= "Bon courage !\n\n";
				$message .= "	Amicalement, le dépouilleur des élections de Vlurxtrznbnaxl.";

				$data_missives[] = array(
					'expediteur_id'   => Bouzouk::Robot_JF_Sebastien,
					'destinataire_id' => $maire->id,
					'date_envoi'      => $this->datetime,
					'timbre'          => $timbre,
					'objet'           => 'Résultats élections',
					'message'         => $message
				);

				// Si un deuxième candidat est toujours là
				if (count($candidats) > 1)
				{
					$maire_suppleant             = $candidats[1];
					$pourcentage_maire_suppleant = round($maire_suppleant->votes * 100 / $nb_votes, 1);

					// On prépare une missive pour le suppléant
					$message = "	Bonjour $maire_suppleant->pseudo\n\n";
					$message .= "Avec <span class='pourpre'>".pluriel($maire_suppleant->votes, 'vote')."</span> au bout de ".pluriel($duree_tour_3, 'jour')." (soit <span class='pourpre'>$pourcentage_maire_suppleant% des votes</span>), tu es arrivé 2ème au duel de fin d'élections :(\n";
					$message .= "Pas de chance pour cette fois, néamoins tu es élu maire suppléant, ce qui veut dire que si quelque chose arrivait au maire et qu'il ne pouvait plus assurer ses fonctions, tu serais automatiquement élu maire jusqu'à la fin du mandat.\n\n";
					$message .= "De toute façon, il est peu probable que cela se produise...Mais un accident est vite arrivé...Moi je dis ça je dis rien...\n\n";
					$message .= "	Amicalement, le dépouilleur des élections de Vlurxtrznbnaxl.";

					$data_missives[] = array(
						'expediteur_id'   => Bouzouk::Robot_JF_Sebastien,
						'destinataire_id' => $maire_suppleant->id,
						'date_envoi'      => $this->datetime,
						'timbre'          => $timbre,
						'objet'           => 'Résultats élections',
						'message'         => $message
					);

					$data_mairie['maire_suppleant_id'] = $maire_suppleant->id;
				}
				$this->db->insert_batch('missives', $data_missives);
				// On remet à zéro les compteur de changement de nom des maps
				$this->map_factory->reset_changement_nom_map();
			}

			// On passe au statut de candidature, on définit la date des prochaines élections et on définir le maire et son suppléant
			$this->db->update('mairie', $data_mairie);

			// On établit le classement des votes avec des pourcentages
			// On va chercher les nombres de votes
			$query = $this->db->select('SUM(votes_tour1) AS tour1, SUM(votes_tour2) AS tour2, SUM(votes_tour3) AS tour3')
							  ->from('elections')
							  ->get();
			$nb_votes = $query->row();

			// On va chercher les votes des candidats
			$query = $this->db->select('joueur_id, tour, votes_tour1, votes_tour2, votes_tour3')
							  ->from('elections')
							  ->order_by('tour', 'desc')
							  ->order_by('votes_tour3', 'desc')
							  ->order_by('votes_tour2', 'desc')
							  ->order_by('votes_tour1', 'desc')
							  ->order_by('joueur_id', 'random')
							  ->get();

			// S'il y a des candidats (toujours très probable :))
			if ($query->num_rows() > 0)
			{
				$candidats                 = $query->result();
				$data_classement_elections = array();
				$i                         = 1;

				// Pour chaque candidat, on enregistre le dernier tour auquel il a accedé (pour trier plus rapidemment), son nombre de votes ainsi que son pourcentage
				foreach ($candidats as $candidat)
				{
					$votes_tour1 = $votes_tour2 = $votes_tour3 = $pourcentage_tour1 = $pourcentage_tour2 = $pourcentage_tour3 = 0;

					// Si le candidat a atteint le tour 1
					if ($candidat->tour >= Bouzouk::Elections_Tour1)
					{
						$votes_tour1       = $candidat->votes_tour1;
						$pourcentage_tour1 = round($candidat->votes_tour1 * 100 / max(1, $nb_votes->tour1), 2);
					}

					// Si le candidat a atteint le tour 2
					if ($candidat->tour >= Bouzouk::Elections_Tour2)
					{
						$votes_tour2       = $candidat->votes_tour2;
						$pourcentage_tour2 = round($candidat->votes_tour2 * 100 / max(1, $nb_votes->tour2), 2);
					}

					// Si le candidat a atteint le tour 3
					if ($candidat->tour >= Bouzouk::Elections_Tour3)
					{
						$votes_tour3       = $candidat->votes_tour3;
						$pourcentage_tour3 = round($candidat->votes_tour3 * 100 / max(1, $nb_votes->tour3), 2);
					}

					$data_classement_elections[] = array(
						'date'              => $this->date,
						'joueur_id'         => $candidat->joueur_id,
						'tour'              => $candidat->tour,
						'votes_tour1'       => $votes_tour1,
						'votes_tour2'       => $votes_tour2,
						'votes_tour3'       => $votes_tour3,
						'pourcentage_tour1' => $pourcentage_tour1,
						'pourcentage_tour2' => $pourcentage_tour2,
						'pourcentage_tour3' => $pourcentage_tour3,
						'position'          => $i
					);
					$i++;
				}
				$this->db->insert_batch('classement_elections', $data_classement_elections);
			}

			// On vide la table des élections et on réinitialise les votes
			$this->db->truncate('elections');
			$this->db->truncate('elections_votes');

			// On supprime tous les topics de propagande du tobozon

			// 1. On récupère les ids des topics du forum de la propagande
			$query = $this->db->select('id')
						  ->from('tobozon_topics')
						  ->where('forum_id', Bouzouk::Tobozon_IdForumPropagande)
						  ->get();
			$topics_ids = array();

			foreach ($query->result() as $topic)
			{
				$topics_ids[] = $topic->id;
			}
			
			// 2. On supprime tous les topics du forum propagande sans oublier les topics déplacés
			$this->db->where('forum_id', Bouzouk::Tobozon_IdForumPropagande)
					 ->or_where_in('moved_to', $topics_ids)
					 ->delete('tobozon_topics');

			// 3. On supprime tous les posts des topics
			$this->db->where_in('topic_id', $topics_ids)
					 ->delete('tobozon_posts');

			// 4. On supprime les souscriptions email à ces topics
			$this->db->where_in('topic_id', $topics_ids)
					 ->delete('tobozon_topic_subscriptions');

			// 5. On met à jour les variables du forum
			$data_tobozon_forums = array(
				'num_topics'   => 0,
				'num_posts'    => 0,
				'last_post'    => NULL,
				'last_post_id' => NULL,
				'last_poster'  => NULL
			);
			$this->db->where('id', Bouzouk::Tobozon_IdForumPropagande)
					 ->update('tobozon_forums', $data_tobozon_forums);

			// 6. On ferme le forum propagande pour les groupes 4 à 14 (tous les types de joueurs)
			$data_tobozon_forum_perms = array();

			for ($i = 4; $i <= 14; $i++)
			{
				$data_tobozon_forum_perms[] = array(
					'group_id'     => $i,
					'forum_id'     => Bouzouk::Tobozon_IdForumPropagande,
					'read_forum'   => 1,
					'post_replies' => 0,
					'post_topics'  => 0
				);
			}
			$this->db->insert_batch('tobozon_forum_perms', $data_tobozon_forum_perms);
		}
	}

	public function elections_triche()
	{
		// On va chercher les infos de la mairie
		$query = $this->db->select('m.tricher_elections, m.maire_id, m.tour_election, e.votes')
						  ->from('mairie m')
						  ->join('elections e', 'e.joueur_id = m.maire_id AND e.tour = m.tour_election')
						  ->where('e.tour > '.Bouzouk::Elections_Candidater)
						  ->get();

		// Si le maire est présent aux élections, on aura un résultat
		if ($query->num_rows() == 1)
		{
			$mairie = $query->row();

			// Si les élections sont en cours et que le maire demande à tricher
			if ($mairie->tricher_elections)
			{
				// Nombre de voix = entre 5 et 10% du nombre de votes
				$nb_voix = max(1, ceil($mairie->votes / (1.0 * mt_rand(10, 20))));

				// On lui rajoute les voix
				$this->db->set('votes', 'votes+'.$nb_voix, false)
						 ->set('faux_votes', 'faux_votes+'.$nb_voix, false)
						 ->where('joueur_id', $mairie->maire_id)
						 ->update('elections');

				// On ajoute à l'historique de la mairie
				$this->lib_mairie->historique("Tu as triché aux élections et tu as gagné <span class='pourpre'>+$nb_voix voix</span>", $mairie->maire_id);
			}
		}
	}

	public function factures()
	{
		$data_missives       = array();
		$timbre              = $this->lib_missive->timbres(0);
		$delai_majoration    = $this->bouzouk->config('factures_delai_majoration');
		$pourcent_majoration = $this->bouzouk->config('factures_pourcent_majoration');

		// On va chercher la liste des joueurs ayant des factures à majorer
		$query = $this->db->select('COUNT(f.id) AS nb_factures_majorees, j.id, j.pseudo')
						  ->from('factures f')
						  ->join('joueurs j', 'j.id = f.joueur_id')
						  ->where('(TO_DAYS(NOW()) - TO_DAYS(f.date)) >= '.$delai_majoration)
						  ->where('(TO_DAYS(NOW()) - TO_DAYS(f.date)) % '.$delai_majoration.' = 0')
						  ->where('majoration < 1000')
						  ->group_by('f.joueur_id')
						  ->get();

		// Si des factures doivent être majorées
		if ($query->num_rows() > 0)
		{
			$joueurs = $query->result();

			// On ajoute la majoration à chaque facture
			$this->db->set('majoration', 'majoration + montant/'.$pourcent_majoration, false)
					 ->where('(TO_DAYS(NOW()) - TO_DAYS(date)) >= '.$delai_majoration)
					 ->where('(TO_DAYS(NOW()) - TO_DAYS(date)) % '.$delai_majoration.' = 0')
					 ->where('majoration < 1000')
					 ->update('factures');

			// Majoration d'au moins 1 strul sur les factures trop faibles
			$this->db->set('majoration', 1)
					 ->where('(TO_DAYS(NOW()) - TO_DAYS(date)) >= '.$delai_majoration)
					 ->where('(TO_DAYS(NOW()) - TO_DAYS(date)) % '.$delai_majoration.' = 0')
					 ->where('majoration', 0)
					 ->update('factures');

			// On limite les majorations à 1000 struls
			$this->db->set('majoration', 1000)	
					 ->where('majoration > 1000')
					 ->update('factures');

			// Pour chaque joueur
			foreach ($joueurs as $joueur)
			{
				// On envoit une misse au joueur
				$message = "	Bonjour $joueur->pseudo\n\n";
				$message .= "Nous venons d'appliquer une majoration de <span class='pourpre'>$pourcent_majoration%</span> sur tes factures impayées depuis $delai_majoration jours (ce qui concerne <span class='pourpre'>".pluriel($joueur->nb_factures_majorees, 'facture')."</span>).\n";
				$message .= "Nous te conseillons de les payer au plus vite afin de retrouver un accès complet à tous les services de la ville.\n\n";
				$message .= "<a href='".site_url('factures')."'>Payer mes factures</a>\n\n";
				$message .= "	Amicalement, la mairie de Vlurxtrznbnaxl.";

				$data_missives[] = array(
					'expediteur_id'   => Bouzouk::Robot_Maire,
					'destinataire_id' => $joueur->id,
					'date_envoi'      => $this->datetime,
					'timbre'          => $timbre,
					'objet'           => 'Factures majorées',
					'message'         => $message
				);
			}
		}

		// On récupère tous les joueurs qui ont trop de factures impayées et qui ont encore de l'expérience à enlever
		$query = $this->db->select('COUNT(f.id) AS nb_factures_impayees, j.id, j.pseudo')
						  ->from('factures f')
						  ->join('joueurs j', 'j.id = f.joueur_id')
						  ->where('f.majoration > 0')
						  ->where('j.experience > 0')
						  ->where('j.statut', Bouzouk::Joueur_Actif)
						  ->group_by('f.joueur_id')
						  ->having('COUNT(f.joueur_id) >= '.$this->bouzouk->config('factures_nb_factures_perte_xp'))
						  ->get();

		// S'il existe effectivement des joueurs dans ce cas-là
		if ($query->num_rows() > 0)
		{
			$joueurs = $query->result();

			// Pour chaque joueur
			foreach ($joueurs as $joueur)
			{
				$perte_xp = $this->bouzouk->config('factures_perte_xp');

				// On lui enlève de l'expérience
				$this->bouzouk->retirer_experience($perte_xp, $joueur->id);

				// On ajoute à l'historique
				$this->bouzouk->historique(79, null, array($this->bouzouk->config('factures_nb_factures_perte_xp'), $perte_xp), $joueur->id);
			}
		}

		if (count($data_missives) > 0)
			$this->db->insert_batch('missives', $data_missives);
	}

	public function faillites()
	{
		// On supprime les faillites trop vieilles
		$this->db->where('date_faillite < (NOW() - INTERVAL '.$this->bouzouk->config('entreprises_duree_faillite').' DAY)')
				 ->delete('faillites');

		// On regarde si des entreprises sont en faillite
		$query = $this->db->select('id')
						  ->from('entreprises')
						  ->where('struls < '.$this->bouzouk->config('entreprises_limite_faillite'))
						  ->get();
		$entreprises = $query->result();

		// Pour chaque entreprise en faillite
		foreach ($entreprises as $entreprise)
		{
			$this->lib_entreprise->faillite($entreprise->id);
		}
	}

	public function game_over()
	{
		$this->load->library('vlux/vlux_factory');
		// On supprime les comptes inactif, étudiant, choix perso ou game over depuis trop longtemps
		$query = $this->db->select('id')
						  ->from('joueurs')
						  ->where_in('statut', array(Bouzouk::Joueur_Inactif, Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_ChoixPerso, Bouzouk::Joueur_GameOver))
						  ->where_not_in('id', $this->bouzouk->get_inactifs())
						  ->where('date_statut < (NOW() - INTERVAL '.$this->bouzouk->config('maintenance_delai_suppression_game_over').' DAY)')
						  ->get();
		$joueurs = $query->result();

		foreach ($joueurs as $joueur)
		{
			$this->lib_joueur->supprimer_joueur($joueur->id);
			//$this->vlux_factory->supprimer_joueur($joueur->id);
		}

		// On va chercher les joueurs qui doivent passer game over (sauf les admins)
		// - les joueurs en pause depuis trop longtemps
		// - les joueurs dont les stats sont à 0 (faim et santé uniquement, le stress amène à l'asile)
		// - les joueurs à l'asile depuis trop longtemps
		$delai_pause = $this->bouzouk->config('maintenance_delai_pause_to_game_over');
		$delai_asile = $this->bouzouk->config('maintenance_delai_asile_to_game_over');
		$statut_pause = Bouzouk::Joueur_Pause;
		$statut_actif = Bouzouk::Joueur_Actif;
		$statut_asile = Bouzouk::Joueur_Asile;
		$rang_admin   = Bouzouk::Rang_Admin;

		$query = $this->db->select('id, pseudo')
						  ->from('joueurs')
						  ->where("rang != $rang_admin AND (".
								  "(statut = $statut_pause AND date_statut < (NOW() - INTERVAL $delai_pause DAY)) OR ".
								  "(statut = $statut_actif AND faim = 0 AND sante = 0) OR ".
								  "(statut = $statut_asile AND date_statut < (NOW() - INTERVAL $delai_asile DAY)))")
						  ->get();
		$joueurs = $query->result();

		// Pour chaque joueur qui devient game over
		foreach ($joueurs as $joueur)
		{
			$this->lib_joueur->mettre_game_over($joueur->id);
			// Debug maintenance
			//echo $joueur->pseudo."\n";
		}
	}

	public function gazette()
	{
		// On désactive les anciens articles de la gazette concernant la fête bouzouk et la météo
		$this->db->where_in('type', array(Bouzouk::Gazette_Fete, Bouzouk::Gazette_Meteo))
				 ->delete('gazettes');

		$data_gazettes = array();

		// On va chercher un joueur au hasard
		$query = $this->db->select('id, pseudo')
						  ->from('joueurs')
						  ->where_in('statut', array(Bouzouk::Joueur_Actif))
						  ->order_by('id', 'random')
						  ->limit(1)
						  ->get();
		$saint = $query->row();

		// On ajoute un nouvel article dans la gazette pour la fête bouzouk
		$data_gazettes[] = array(
			'auteur_id' => Bouzouk::Robot_MissPoohLett,
			'type'      => Bouzouk::Gazette_Fete,
			'titre'     => 'Événement inutile',
			'texte'     => "Pensez à souhaiter une bonne non-fête<br>à ".profil($saint->id, $saint->pseudo).' !',
			'image_url' => '',
			'date'      => $this->datetime,
			'en_ligne'  => 1
		);

		$temps = array(
			'temps ensoleillé',
			'chutes de météorites',
			'neige radioactive',
			'temps couvert avec de fortes pluies radioatives',
			'tempête de neige',
			'très beau temps',
			'tempête de neige, ouragan, pluie radioactive et chute de météorites',
			'nuages nucléaires',
			'temps radioactif',
			'grosse vague de chaleur'
		);

		$anecdotes = array(
			"Ca va sentir le grillé aujourd'hui :D",
			"N'oubliez pas de sortir vos parapluies en bêton armé.",
			"MétéoBouz vous recommande de faire vérifier vos skis par des professionnels.",
			"Ca va déprimer aujourd'hui !",
			"Grande réduction sur les glaces au supermarché !",
			"Donc restez à l'abri chez vous !",
			"Bref, temps tout à fait normal.",
			"N'oubliez pas votre paire d'oculoplastok pour sortir.",
			"Bref, temps tout à fait normal. Bonne journée à tous !",
			"Pensez à vous habiller chaudement."
		);

		$num = mt_rand(0, count($temps)-1);
		$min = mt_rand(-50, 20);
		$max = mt_rand(21, 150);
		$texte = "Aujourd'hui, ".$temps[$num].' avec des températures allant de '.$min.'°C à '.$max.'°C. '.$anecdotes[$num];

		// On ajoute un nouvel article dans la gazette pour la météo
		$data_gazettes[] = array(
			'auteur_id' => Bouzouk::Robot_MissPoohLett,
			'type'      => Bouzouk::Gazette_Meteo,
			'titre'     => 'La météo',
			'texte'     => $texte,
			'image_url' => '',
			'date'      => $this->datetime,
			'en_ligne'  => Bouzouk::Gazette_Publie
		);

		$this->db->insert_batch('gazettes', $data_gazettes);

		// On supprime les articles refusés
		$this->db->where('en_ligne', Bouzouk::Gazette_Refuse)
				 ->delete('gazettes');
	}

	public function historiques()
	{
		// On vidange les historiques entreprise
		$this->db->where('date < (NOW() - INTERVAL '.$this->bouzouk->config('historique_entreprise_duree_retention').' DAY)')
				 ->delete('historique_entreprises');

		// On vidange les historiques joueurs
		$this->db->where('date < (NOW() - INTERVAL '.$this->bouzouk->config('historique_joueur_duree_retention').' DAY)')
				 ->delete('historique');

		// On vidange l'historique de la mairie
		$this->db->where('date < (NOW() - INTERVAL '.$this->bouzouk->config('historique_mairie_duree_retention').' DAY)')
				 ->delete('historique_mairie');

		// On vidange les historiques des clans
		$this->db->where('date < (NOW() - INTERVAL '.$this->bouzouk->config('clans_historique_duree_retention').' DAY)')
				 ->delete('historique_clans');

		// On vidange les historiques de modération
		$this->db->where('date < (NOW() - INTERVAL 60 DAY)')
				 ->delete('historique_moderation');

		$this->db->where('date < (NOW() - INTERVAL 60 DAY)')
				 ->delete('tobozon_log_moderation');
	}

	public function historiques_avant()
	{
		// On récupère les infos des joueurs
		$query = $this->db->select('id, faim, sante, stress, experience, struls')
						  ->from('joueurs')
						  ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile))
						  ->get();
		$joueurs = $query->result();
		$data_historique = array();

		// On écrit une ligne dans l'historique du joueur pour savoir où il en est
		foreach ($joueurs as $joueur)
		{
			$data_historique[] = array(
				'joueur_id'			=> $joueur->id,
				'texte_id_private'	=> 213,
				'donnees'			=> serialize(array($joueur->faim, $joueur->sante, $joueur->stress, $joueur->experience, $joueur->struls)),
				'date'				=> $this->datetime
			);
		}
		$this->db->insert_batch('historique', $data_historique);

		// On écrit une ligne dans l'historique de la mairie pour savoir où elle en est
		$query = $this->db->select('maire_id, struls')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();
		$this->lib_mairie->historique('Début maintenance : '.struls($mairie->struls).' dans les caisses de la mairie', $mairie->maire_id);
	}

	public function historiques_apres()
	{
		// On récupère les infos des joueurs
		$query = $this->db->select('id, faim, sante, stress, experience, struls')
						  ->from('joueurs')
						  ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile))
						  ->get();
		$joueurs = $query->result();
		$data_historique = array();

		// On écrit une ligne dans l'historique du joueur pour savoir où il en est
		foreach ($joueurs as $joueur)
		{
			$data_historique[] = array(
				'joueur_id'			=> $joueur->id,
				'texte_id_private'	=> 214,
				'donnees'			=> serialize(array($joueur->faim, $joueur->sante, $joueur->stress, $joueur->experience, $joueur->struls)),
				'date'				=> $this->datetime
			);
		}
		$this->db->insert_batch('historique', $data_historique);

		// On écrit une ligne dans l'historique de la mairie pour savoir où elle en est
		$query = $this->db->select('maire_id, struls')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();
		$this->lib_mairie->historique('Fin maintenance : '.struls($mairie->struls).' dans les caisses de la mairie', $mairie->maire_id);
	}

	public function impots()
	{
		// ---------- Hook clans ----------
		// Magouille fiscale (Struleone)
		$joueurs_magouille_fiscale = $this->lib_clans->magouille_fiscale_en_cours();

		// On envoie les impôts
		$query = $this->db->select('m.maire_id, j.pseudo, m.impots_employes, m.impots_faim, m.impots_sante, m.impots_stress, m.date_prochain_impot')
						  ->from('mairie m')
						  ->join('joueurs j', 'j.id = m.maire_id')
						  ->get();
		$mairie = $query->row();

		// On regarde si c'est le jour des impôts
		if ($mairie->date_prochain_impot == $this->date)
		{
			// On définit la date du prochain impot
			$this->db->set('date_prochain_impot', '(CURRENT_DATE() + INTERVAL '.$this->bouzouk->config('mairie_intervalle_impots').' DAY)', false)
					 ->update('mairie');

			$data_factures = array();
			$data_missives = array();
			$timbre        = $this->lib_missive->timbres(0);

			// On récupère la liste des employés du jeu
			$query = $this->db->select('j.id, j.pseudo, j.struls, j.statut, j.pause_payer_taxes')
							  ->from('joueurs j')
							  ->join('employes e', 'e.joueur_id = j.id')
							  ->where_in('j.statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))
							  ->get();
			$joueurs = $query->result();

			// Pour chaque employé
			foreach ($joueurs as $joueur)
			{
				// ---------- Hook clans ----------
				// Magouille fiscale (Struleone)
				if (in_array($joueur->id, $joueurs_magouille_fiscale))
					continue;

				// On calcule le montant de son impôt selon le salaire
				$montant = (int) ($joueur->struls * $mairie->impots_employes / 100);

				if ($montant <= 0)
				{
					continue;
				}

				// Si le joueur est en pause et qu'il veut payer ses factures automatiquement, on paye
				if ($joueur->statut == Bouzouk::Joueur_Pause && $joueur->pause_payer_taxes == 1)
				{
					// On enlève au joueur
					$this->db->set('struls', 'struls-'.$montant, false)
								->where('id', $joueur->id)
								->update('joueurs');

					// On ajoute à la mairie
					$this->db->set('struls', 'struls+'.$montant, false)
								->update('mairie');

					$this->bouzouk->historique(80, null, array(struls($montant)), $joueur->id);

					continue;
				}

				// On prépare une facture
				$data_factures[] = array(
					'joueur_id'  => $joueur->id,
					'titre'      => 'Impôts sur le revenu',
					'montant'    => $montant,
					'majoration' => 0,
					'date'       => $this->datetime
				);

				// On prépare une missive
				$message  = "	Bonjour $joueur->pseudo\n\n";
				$message .= "Aujourd'hui, en tant qu'employé exploité, il est temps de payer tes impôts sur le revenu :)\n";
				$message .= "Ces impôts iront tout droit dans les caisses de la mairie et permettent à des affamés dans ton genre de se remplir l'estomac au bouffzouk.\n\n";
				$message .= "Le maire ".profil($mairie->maire_id, $mairie->pseudo)." a fixé les impôts à <span class='pourpre'>$mairie->impots_employes %</span> des struls, tu dois donc verser un total de ".struls($montant)." à la mairie.\n\n";
				$message .= "Tu as ".pluriel($this->bouzouk->config('factures_delai_majoration'), 'jour')." pour les payer, passé ce délai, tu ne seras plus accepté dans aucun service ni aucune boutique de la ville de Vlurxtrznbnaxl nécessitant de l'argent et tu devras travailler dur afin de rembourser ta dette majorée de ".$this->bouzouk->config('factures_pourcent_majoration')."%.\n";
				$message .= "Ce serait dommage... :-)\n\n";
				$message .= "<a href='".site_url('factures')."'>Payer mes factures</a>\n\n";
				$message .= "	Amicalement, la mairie de Vlurxtrznbnaxl.";

				$data_missives[] = array(
					'expediteur_id'   => Bouzouk::Robot_Percepteur,
					'destinataire_id' => $joueur->id,
					'date_envoi'      => $this->datetime,
					'timbre'          => $timbre,
					'objet'           => 'Impôts sur le revenu',
					'message'         => $message
				);
			}

			// On récupère la liste des entreprises du jeu
			$query = $this->db->select('e.id, e.struls, o.type, e.chef_id, j.pseudo AS chef_pseudo')
							  ->from('entreprises e')
							  ->join('objets o', 'o.id = e.objet_id')
							  ->join('joueurs j', 'j.id = e.chef_id')
							  ->get();
			$entreprises = $query->result();

			$impots = array(
				'faim'   => $mairie->impots_faim,
				'sante'  => $mairie->impots_sante,
				'stress' => $mairie->impots_stress
			);

			$montant_total_mairie = 0;

			// Pour chaque entreprise
			foreach ($entreprises as $entreprise)
			{
				// On calcule le montant de son impôt selon les struls qu'elle possède
				// Et selon le type de l'entreprise (impots différents)
				$montant = (int) ($entreprise->struls * $impots[$entreprise->type] / 100);

				if ($montant <= 0)
				{
					continue;
				}

				// On retire l'argent de l'entreprise
				$this->db->set('struls', 'struls-'.$montant, false)
						 ->where('id', $entreprise->id)
						 ->update('entreprises');

				// On rajoute l'argent à la mairie
				$this->db->set('struls', 'struls+'.$montant, false)
						 ->update('mairie');

				$montant_total_mairie += $montant;

				// On ajoute à l'historique
				$this->bouzouk->historique(81, null, array($impots[$entreprise->type], struls($montant)), $entreprise->chef_id);

				// On met à jour l'historique de cette session
				$query = $this->db->set('impots', $montant)
								  ->where('entreprise_id', $entreprise->id)
								  ->where('date', $this->date)
								  ->update('historique_entreprises');

				// On prépare une missive
				$message  = "   Bonjour $entreprise->chef_pseudo\n\n";
                $message .= "Aujourd'hui, en tant que patron, il est temps de payer tes impôts sur le revenu :)\n";
                $message .= "Ces impôts vont tout droit dans les caisses de la mairie et permettent à des affamés dans ton genre de se remplir l'estomac au bouffzouk.\n\n";
                $message .= "Le maire ".profil($mairie->maire_id, $mairie->pseudo)." a fixé les impôts de ton entreprise à <span class='pourpre'>".$impots[$entreprise->type]." %</span> des struls de l'entreprise, tu dois donc verser un total de ".struls($montant)." à la mairie.\n";
                $message .= "Puisque le maire n'a pas confiance en toi, les impôts ont été prélevés automatiquement de ton entreprise après la rentrée d'argent.\n\n";
                $message .= "Bonne journée... :-)\n\n";
                $message .= "   Amicalement, la mairie de Vlurxtrznbnaxl.";

				$data_missives[] = array(
					'expediteur_id'   => Bouzouk::Robot_Percepteur,
					'destinataire_id' => $entreprise->chef_id,
					'date_envoi'      => $this->datetime,
					'timbre'          => $timbre,
					'objet'           => 'Impôts sur le revenu',
					'message'         => $message
				);
			}

			// On ajoute à l'historique
			$this->lib_mairie->historique('Prélèvement des impôts aux entreprises : la mairie gagne <span class="pourpre">+'.struls($montant_total_mairie, false).'</span><br>'.
									  'Bouffzouk : <span class="pourpre">'.$mairie->impots_faim.'%</span> | Indispenzouk : <span class="pourpre">'.$mairie->impots_sante.
									  '%</span> | Luxezouk : <span class="pourpre">'.$mairie->impots_stress.'%</span>', $mairie->maire_id);

			$this->db->insert_batch('factures', $data_factures);
			$this->db->insert_batch('missives', $data_missives);
		}
	}

	public function jobs()
	{
		// On récupère les employés qui ont un job trop élevé par rapport à leur expérience et ancienneté
		$query = $this->db->select('j.id, j.pseudo, j.experience, e.anciennete, en.chef_id')
						  ->from('employes e')
						  ->join('entreprises en', 'en.id = e.entreprise_id')
						  ->join('joueurs j', 'j.id = e.joueur_id')
						  ->join('jobs', 'jobs.id = e.job_id')
						  ->where('j.experience + e.anciennete < jobs.experience')
						  ->get();
		$employes = $query->result();

		// Pour chaque employé trouvé
		foreach ($employes as $employe)
		{
			$experience = $employe->experience + $employe->anciennete;

			// On va chercher le meilleur job possible pour lui
			$query = $this->db->select('id, nom, salaire')
							  ->from('jobs')
							  ->where('experience <= '.$experience)
							  ->order_by('experience', 'desc')
							  ->limit(1)
							  ->get();
			$job = $query->row();

			// On définit le nouveau job
			$this->db->set('job_id', $job->id)
					 ->set('salaire', $job->salaire)
					 ->where('joueur_id', $employe->id)
					 ->update('employes');

			// On ajoute une ligne à l'historique de l'employé
			$this->bouzouk->historique(82, 83, array($job->nom, struls($job->salaire)), $employe->id);

			// On ajoute une ligne à l'historique du patron
			$this->bouzouk->historique(84, null, array(profil($employe->id, $employe->pseudo), $job->nom, struls($job->salaire)), $employe->chef_id);
		}
	}

	public function joueurs()
	{
		// On va chercher la liste des joueurs
		$query = $this->db->select('j.id, j.faim, j.sante, j.stress, j.experience, j.points_action, j.rang, j.pseudo, j.email, en.id AS patron, em.id AS employe, m.id AS maire')
						  ->from('joueurs j')
						  ->join('entreprises en', 'en.chef_id = j.id', 'left')
						  ->join('employes em', 'em.joueur_id = j.id', 'left')
						  ->join('mairie m', 'm.maire_id = j.id', 'left')
						  ->where('statut', Bouzouk::Joueur_Actif)
						  ->get();

		foreach ($query->result() as $joueur)
		{
			$faim          = $joueur->faim;
			$sante         = $joueur->sante;
			$stress        = $joueur->stress;
			$experience    = $joueur->experience;
			$points_action = $joueur->points_action;

			// On baisse les stats des joueurs
			$perte_min = $this->bouzouk->config('maintenance_perte_stats_min');
			$perte_max = $this->bouzouk->config('maintenance_perte_stats_max');

			// Les admins sont immunisés : Tweedy, Robby, versgui , Hikingyo, Doublure Stylo
			if ( ! in_array($joueur->id, array(16, 17, 29, 5271, 5195)))
			{
				$faim   = max(0, $faim - mt_rand($perte_min, $perte_max));
				$sante  = max(0, $sante - mt_rand($perte_min, $perte_max));
				$stress = min(100, $stress + mt_rand($perte_min, $perte_max));

				// On envoie un mail aux joueurs trop faibles
				if ($faim < 21 AND $sante < 21 AND ($faim > 0 OR $sante > 0))
				{
					$vars = array(
						'pseudo' => $joueur->pseudo,
						'faim'   => $faim,
						'sante'  => $sante,
						'stress' => $stress
					);
					$message = $this->load->view('email/mal_en_point', $vars, true);

					$this->email->from($this->bouzouk->config('email_from'), 'Bouzouks')
								->to($joueur->email)
								->subject('[Bouzouks.net] Ton bouzouk est mal en point')
								->message($message)
								->send();
				}
			}

			// On ajoute de l'expérience aux joueurs (cumulables)
			// Joueur normal
			$experience += 1;

			// Employé
			if ($joueur->employe != null)
				$experience += $this->bouzouk->config('joueur_gain_xp_employe');

			// Patron
			if ($joueur->patron != null)
				$experience += $this->bouzouk->config('joueur_gain_xp_patron');

			// Maire de la ville
			if ($joueur->maire != null)
				$experience += $this->bouzouk->config('joueur_gain_xp_maire');

			$gain_xp = $experience - $joueur->experience;
			$points_action += $gain_xp;

			$data_joueurs = array(
				'faim'          => $faim,
				'sante'         => $sante,
				'stress'        => $stress,
				'experience'    => $experience,
				'points_action' => $points_action
			);
			$this->db->where('id', $joueur->id)
					 ->update('joueurs', $data_joueurs);

			$this->bouzouk->historique(85, null, array($gain_xp, pluriel($gain_xp, 'point')), $joueur->id);
		}

		// On augmente l'ancienneté des employés
		$this->db->set('anciennete', 'anciennete+1', false)
				 ->update('employes');

		// On augmente l'ancienneté des patrons
		$this->db->set('anciennete_chef', 'anciennete_chef+1', false)
				 ->update('entreprises');

		// On va chercher les joueurs à l'asile qui vont bientôt passer game over
		$nb_jours = $this->bouzouk->config('maintenance_delai_asile_to_game_over') - 2;

		$query = $this->db->select('pseudo, email')
						  ->from('joueurs')
						  ->where('statut', Bouzouk::Joueur_Asile)
						  ->where('date_statut <= (NOW() - INTERVAL '.$nb_jours.' DAY)')
						  ->get();
		$joueurs = $query->result();

		foreach ($joueurs as $joueur)
		{
			$vars = array(
				'pseudo' => $joueur->pseudo,
			);

			$message = $this->load->view('email/asile', $vars, true);

			$this->email->from($this->bouzouk->config('email_from'), 'Bouzouks')
						->to($joueur->email)
						->subject('[Bouzouks.net] Ton bouzouk est mal en point')
						->message($message)
						->send();
		}
	}

	public function liens_campagne()
	{
		$this->load->library('lib_maintenance');
		$this->lib_maintenance->mettre_a_jour_topics_elections();
	}

	public function loterie()
	{
		// On récupère quelques variables
		$nb_numeros_a_jouer     = $this->bouzouk->config('jeux_nb_numeros_a_jouer');
		$lohtoh_nums            = array('GNEE', 'KAH', 'ZIG', 'STO', 'BLAZ', 'DRU', 'GOZ', 'POO', 'BNZ', 'GLAP');
		$numeros_gagnants       = '';
		$numeros_gagnants_texte = '';
		$nb_gagnants            = 0;

		// On tire une combinaison au hasard
		for ($i = 0; $i < $nb_numeros_a_jouer; $i++)
		{
			$num = mt_rand(0, 9);
			$numeros_gagnants .= $num;
			$numeros_gagnants_texte .= $lohtoh_nums[$num].' - ';
		}
		$numeros_gagnants_texte = mb_substr($numeros_gagnants_texte, 0, strlen($numeros_gagnants_texte) - 3);

		// On va chercher les infos de la mairie
		$query = $this->db->select('cagnotte_lohtoh, impots_lohtoh, maire_id')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();

		// On regarde s'il y a des gagnants
		$query = $this->db->select('j.id, j.pseudo')
						  ->from('loterie l')
						  ->join('joueurs j', 'j.id = l.joueur_id')
						  ->where('j.statut', Bouzouk::Joueur_Actif)
						  ->where("l.numeros LIKE '$numeros_gagnants%'")
						  ->get();

		// S'il y a un ou plusieurs gagnants
		if ($query->num_rows() > 0)
		{
			$nb_gagnants = $query->num_rows();
			$joueurs = $query->result();

			// On calcule quelques valeurs
			$part_mairie = floor($mairie->impots_lohtoh * $mairie->cagnotte_lohtoh / 100);
			$part_joueur = round(($mairie->cagnotte_lohtoh - $part_mairie) / $nb_gagnants, 1);
			$gain_xp     = $this->bouzouk->config('jeux_gain_xp_gagnant_lohtoh');

			if ($nb_gagnants > 1)
			{
				$profils_gagnants = '';
				$cpt = 0;

				// On écrit les profils des gagnants un par un
				foreach ($joueurs as $joueur)
				{
					$profils_gagnants .= profil($joueur->id, $joueur->pseudo);
					$cpt++;

					if ($cpt == $nb_gagnants - 1)
					{
						$profils_gagnants .= ' et ';
					}

					else if ($cpt < $nb_gagnants)
					{
						$profils_gagnants .= ', ';
					}
				}
			}

			// Pour tous les gagnants
			foreach ($joueurs as $joueur)
			{
				// On ajoute le gain au gagnant
				$this->bouzouk->ajouter_struls($part_joueur, $joueur->id);

				// On ajoute de l'expérience au gagnant
				$this->bouzouk->ajouter_experience($gain_xp, $joueur->id);

				// On envoie une missive au gagnant
				$message  = "	Bonjour $joueur->pseudo\n\n";
				$message .= "Les numéros du lohtoh étaient <span class='pourpre'>$numeros_gagnants_texte</span> et tu as gagné !\n\n";
				$message .= "La cagnotte s'élève à ".struls($mairie->cagnotte_lohtoh).".\n";
				$message .= "Le maire ayant fixé les impôts des gains du lohtoh à <span class='pourpre'>".$mairie->impots_lohtoh."%</span>, la mairie empoche donc ".struls($part_mairie).".\n";

				// Plusieurs gagnants
				if ($nb_gagnants > 1)
				{
					$message .= "Il reste donc ".struls($mairie->cagnotte_lohtoh - $part_mairie)." à partager entre <span class='pourpre'>".$nb_gagnants." bouzouks</span> ayant joué ce numéro : $profils_gagnants.\n";
				}

				$message .= "Tu remportes alors un joli total de ".struls($part_joueur).".\n\n";
				$message .= "De plus, tu gagnes <span class='pourpre'>+$gain_xp xp</span> :)\n\n";
				$message .= "	Amicalement, Miss Lotoh de Vlurxtrznbnaxl.";

				$this->lib_missive->envoyer_missive(Bouzouk::Robot_MissPoohLett, $joueur->id, 'Tu as gagné à la loterie !', $message);

				// On ajoute à l'historique
				$this->bouzouk->historique(86, 87, array(struls($part_joueur), struls($part_mairie), $gain_xp), $joueur->id);
			}

			// On ajoute la part de la mairie et on remet la cagnotte à 0
			$this->db->set('struls', 'struls+'.$part_mairie, false)
					 ->set('cagnotte_lohtoh', 0)
					 ->update('mairie');

			// On ajoute à l'historique de la mairie
			$this->lib_mairie->historique("Prélèvement de l'impôt de <span class='pourpre'>".$mairie->impots_lohtoh."%</span> sur les gains du lohtoh : la mairie gagne +".struls($part_mairie), $mairie->maire_id);

			// On désactive les anciens articles de la gazette concernant le lohtoh
			$this->db->where('type', Bouzouk::Gazette_Lohtoh)
					 ->delete('gazettes');

			// On ajoute un nouvel article dans la gazette pour le lohtoh
			if ($nb_gagnants == 1)
			{
				$texte = "Les numéros gagnants du ".bouzouk_date($this->date)." étaient $numeros_gagnants_texte. ".profil($joueurs[0]->id, $joueurs[0]->pseudo)." a donc remporté la cagnotte de ".
						 struls($mairie->cagnotte_lohtoh).", soit ".struls($part_joueur)." pour lui et ".struls($part_mairie)." pour la mairie (<span class='pourpre'>".$mairie->impots_lohtoh."%</span> d'impôts). Félicitations à lui :)";
			}

			else
			{
				$texte = "Les numéros gagnants du ".bouzouk_date($this->date)." étaient $numeros_gagnants_texte. $profils_gagnants ont donc remporté la cagnotte de ".
						 struls($mairie->cagnotte_lohtoh).", soit ".struls($part_joueur)." chacun et ".struls($part_mairie)." pour la mairie (<span class='pourpre'>".$mairie->impots_lohtoh."%</span> d'impôts). Félicitations à eux :)";
			}
			$data_gazettes = array(
				'auteur_id' => Bouzouk::Robot_MissPoohLett,
				'type'      => Bouzouk::Gazette_Lohtoh,
				'titre'     => 'Lohtoh',
				'texte'     => $texte,
				'image_url' => '',
				'date'      => bdd_datetime(),
				'en_ligne'  => Bouzouk::Gazette_Publie
			);
			$this->db->insert('gazettes', $data_gazettes);
		}

		// On enregistre le tirage d'aujourd'hui
		$data_loterie_tirages = array(
			'date'     => bdd_datetime(),
			'numeros'  => $numeros_gagnants_texte,
			'cagnotte' => $mairie->cagnotte_lohtoh,
			'gagnants' => ($nb_gagnants > 0) ? (isset($profils_gagnants) ? $profils_gagnants : profil($joueurs[0]->id, $joueurs[0]->pseudo)) : '<i>aucun</i>'
		);
		$this->db->insert('loterie_tirages', $data_loterie_tirages);

		// On vide la table du loto
		$this->db->truncate('loterie');
	}

	public function maire()
	{
		$this->lib_mairie->verifier_maire_et_suppleant();
	}

	public function mairie()
	{
		// Tous les 3 jours : on achète aux entreprises et on remplit les magasins
		$query = $this->db->select('date_prochain_achat, struls, maire_id, coefficients_achats,bonus_entreprise,malus_entreprise')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();
		$mairie->coefficients_achats = explode('|', $mairie->coefficients_achats);

		$mairie->coefficients_achats = array(
			'faim' => $mairie->coefficients_achats[0],
			'sante' => $mairie->coefficients_achats[1],
			'stress' => $mairie->coefficients_achats[2],
			'total' => $mairie->coefficients_achats[0] + $mairie->coefficients_achats[1] + $mairie->coefficients_achats[2]
		);


		// On vérifie qu'on est bien le jour des achats (tous les 3 jours)
		if (strtotime($this->date) >= strtotime($mairie->date_prochain_achat))
		{
			// On définit la date du prochain achat
			$this->db->set('date_prochain_achat', '(CURRENT_DATE() + INTERVAL 3 DAY)', false)
					 ->update('mairie');

			// Total des struls que la mairie peut dépenser
			$depense_mairie = 0;

			if ($mairie->struls > 0)
				$depense_mairie = $mairie->struls;
			
			// Nombre de joueurs actifs
			$nb_joueurs_actifs = $this->db->where('statut', Bouzouk::Joueur_Actif)
										  ->count_all_results('joueurs');

			// On va chercher tous les objets produits par au moins une entreprise
			$query = $this->db->select('id, prix, type')
							  ->from('objets')
							  ->where('id IN (SELECT DISTINCT(objet_id) FROM entreprises)')
							  ->where('disponibilite', 'entreprise')
							  ->get();
			$nb_objets = $query->num_rows();
			$objets = $query->result();
			$total_achats = (int)(($nb_joueurs_actifs * ($this->bouzouk->config('maintenance_coefficient_achats') + $mairie->coefficients_achats['total'] / 3.0)) * $nb_objets);

			// Pourcentage que la mairie doit acheter pour chaque magasin
			$coeff_achat = floor(min(100, 100 * $depense_mairie / $total_achats));

			// Pour chaque objet
			foreach ($objets as $objet)
			{
				// On calcule le nombre de produits à acheter
				$nb_produits = floor(($nb_joueurs_actifs * ($this->bouzouk->config('maintenance_coefficient_achats') + $mairie->coefficients_achats[$objet->type]) / $objet->prix) * $coeff_achat / 100.0);

				// On remplit le magasin
				$this->db->set('quantite', 'quantite+'.$nb_produits, false)
						 ->where('objet_id', $objet->id)
						 ->update('magasins');
			}

			// On retire les struls de la mairie avec un supplément paramétrable
			$total_achats = floor(min($depense_mairie , $total_achats * $coeff_achat / 100.0 ));
			if($mairie->struls - $total_achats >= $this->bouzouk->config('sup_achat')){
				$total_achats = $total_achats + $this->bouzouk->config('sup_achat');
			}
			$this->lib_mairie->retirer_struls($total_achats);
			$this->lib_mairie->historique('Achats aux entreprises : la mairie perd <span class="pourpre">-'.struls($total_achats, false).'</span> soit <span class="pourpre">'.$coeff_achat.'%</span> des stocks de chaque entreprise', $mairie->maire_id);

			// On va chercher la liste des entreprises
					$query = $this->db->select('id,production , objet_id')
						  ->from('entreprises e')
						  ->get();
			$entreprises = $query->result();

		  /*$query = $this->db->select('id, production')
				  ->from('entreprises')
				  ->get();*/
			$query = $this->db->select('o.id AS id')
				  ->from('entreprises AS e')
				  ->join('objets AS o', 'o.id = e.objet_id')
				  ->get();			
			$objets = array_count_values(array_map(create_function('$o', 'return $o->id;'), $query->result()));
			// Pour chaque entreprise
			foreach ($entreprises as $entreprise)
			{
				$modificateur=1;
				if($objets[$entreprise->objet_id]<=$this->bouzouk->config('entreprises_nombre_bonus_rentre_argent'))
				{
					$modificateur=$modificateur+$mairie->bonus_entreprise/100;
				}
				elseif($objets[$entreprise->objet_id]>=$this->bouzouk->config('entreprises_nombre_malus_rentre_argent'))
				{
					$modificateur=$modificateur-$mairie->malus_entreprise/100;
				}
				//	$result="bonus";
				// On calcule la rentrée d'argent
				$rentree_argent = (int)($entreprise->production * $coeff_achat *$modificateur/ 100);

				// On ajoute les struls à l'entreprise
				$this->db->set('production', 0)
						 ->set('derniere_rentree', $rentree_argent)
						 ->set('struls', 'struls+'.$rentree_argent, false)
						 ->where('id', $entreprise->id)
						 ->update('entreprises');

				// On ajoute un enregistrement à l'historique
				$nb_employes = $this->db->where('entreprise_id', $entreprise->id)
										->count_all_results('employes');

				$data_historique_entreprises = array(
					'entreprise_id'   => $entreprise->id,
					'date'            => $this->date,
					'nb_employes'     => $nb_employes,
					'rentree_argent'  => $rentree_argent,
					'pourcent_achats' => $coeff_achat
				);
				$this->db->insert('historique_entreprises', $data_historique_entreprises);
			}

			// On récupère les objets du boostzouk
			$query = $this->db->select('m.quantite, m.objet_id, o.quantite_max, o.prix * '.$this->bouzouk->config('maintenance_pourcentage_prix_boostzouk') .' / 100.0 AS prix') // le boostzouk est à 50% pour la mairie
							->from('magasins m')
							->join('objets o', 'o.id = m.objet_id')
							->where('o.type', 'boost')
							->order_by('o.peremption', 'desc')
							->get();
			$magasins = $query->result();
			$prix_total_achats_boostzouk = 0;

			foreach ($magasins as $magasin)
			{
				// On calcule la quantité à acheter
				$quantite = $magasin->quantite_max - $magasin->quantite;
				$prix_total = $quantite * $magasin->prix;

				if ($quantite > 0)
				{
					// On regarde si la mairie a assez
					$query = $this->db->select('struls, maire_id')
									->from('mairie')
									->get();
					$mairie = $query->row();

					if ($mairie->struls <= 0)
						break;
						
					if ($mairie->struls < $prix_total)
					{
						// On calcule la quantité maximum que la mairie peut acheter
						$quantite = $mairie->struls / $magasin->prix;
						$prix_total = $quantite * $magasin->prix;

						if ($quantite <= 0)
							continue;
					}

					// On ajoute la quantité au magasin
					$this->db->set('quantite', 'quantite+'.$quantite, false)
							->where('objet_id', $magasin->objet_id)
							->update('magasins');

					// On retire le prix à la mairie
					$this->db->set('struls', 'struls-'.$prix_total, false)
							->update('mairie');

					$prix_total_achats_boostzouk += $prix_total;
				}
			}

			// On ajoute à l'historique
			if ($prix_total_achats_boostzouk > 0)
			{
				$this->lib_mairie->historique('Achats dans le boostzouk : la mairie perd <span class="pourpre">-'.struls($prix_total_achats_boostzouk, false).'</span>', $mairie->maire_id);
			}
		}
	}

	public function marche_noir()
	{
		// On va chercher l'heure de la prochaine maintenance
		$query = $this->db->select('prochaine_maintenance')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();

		// On reconstruit la date et l'heure de la prochaine maintenance (la bdd ne donne que l'heure)
		$time = explode(':', $mairie->prochaine_maintenance);
		$timestamp_prochaine_maintenance = mktime((int)$time[0], (int)$time[1], 0) + 86000; // on part d'aujourd'hui et on rajoute presque +24h (pour pas tomber pile sur la maintenance et éviter les erreurs SQL)
		
		// Ajout d'objets rares au marché noir (XX% de chances)
		if ($this->bouzouk->config('maintenance_pourcent_rares') > 0 && $this->bouzouk->config('maintenance_pourcent_rares') >= mt_rand(1, 100))
		{
			// On va chercher un objet très rare aléatoirement
			$query = $this->db->select('id')
							  ->from('objets')
							  ->where('disponibilite', 'marche_noir')
							  ->where('rarete', 'rare')
							  ->order_by('id', 'random')
							  ->limit(1)
							  ->get();
			
			// S'il en existe un
			if ($query->num_rows() == 1)
			{
				$objet = $query->row();
				
				// On tire une heure aléatoire
				$date_prochaine_vente = mt_rand(time(), $timestamp_prochaine_maintenance);				
				
				// On ajoute des vendeurs de rare
				$data_vendeurs_rare = array(
					'objet_id'   => $objet->id,
					'date_vente' => date('Y-m-d H:i:s', $date_prochaine_vente)
				);
				$this->db->insert('objets_rares_attente', $data_vendeurs_rare);
			}
		}

		// Ajout d'objets très rares au marché noir (YY% de chances)
		if ($this->bouzouk->config('maintenance_pourcent_tres_rares') > 0 && $this->bouzouk->config('maintenance_pourcent_tres_rares') >= mt_rand(1, 100))
		{
			// On va chercher un objet très rare aléatoirement
			$query = $this->db->select('id')
							  ->from('objets')
							  ->where('disponibilite', 'marche_noir')
							  ->where('rarete', 'tres_rare')
							  ->order_by('id', 'random')
							  ->limit(1)
							  ->get();
			
			// S'il en existe un
			if ($query->num_rows() == 1)
			{
				$objet = $query->row();

				// On tire une heure aléatoire
				$date_prochaine_vente = mt_rand(time(), $timestamp_prochaine_maintenance);				
				
				// On ajoute des vendeurs de très rare
				$data_vendeurs_rare = array(
					'objet_id'   => $objet->id,
					'date_vente' => date('Y-m-d H:i:s', $date_prochaine_vente)
				);
				$this->db->insert('objets_rares_attente', $data_vendeurs_rare);
			}
		}

		$robots = $this->bouzouk->get_robots();
		
		// On supprime les objets du marché noir vendus par des robots et qui sont périmés
		$this->db->where_in('joueur_id', $robots)
				 ->where('peremption', 0)
				 ->delete('marche_noir');
	}

	public function mendiants()
	{
		// On récupère la liste des mendiants
		$query = $this->db->select('j.id, j.pseudo')
						  ->from('mendiants m')
						  ->join('joueurs j', 'j.id = m.joueur_id')
						  ->get();

		if ($query->num_rows() > 0)
		{
			$mendiants = $query->result();
			$data_missives = array();

			// Pour chaque mendiant
			foreach ($mendiants as $mendiant)
			{
				// Si il n'a eu aucune donation
				$nb_donateurs = $this->db->where('joueur_id', $mendiant->id)
										 ->where('type', Bouzouk::Donation_Mendiant)
										 ->count_all_results('donations');

				if ($nb_donateurs == 0)
				{
					// On lui retire de l'expérience
					$perte_xp = $this->bouzouk->config('mendiants_perte_xp_aucun_don');
					$this->bouzouk->retirer_experience($perte_xp, $mendiant->id);

					// On lui envoie une missive
					$message  = "	Bonjour $mendiant->pseudo\n\n";
					$message .= "Tu as choisi de mendier toute la journée d'hier, malheureusement aucun bouzouk n'a eu pitié de toi. Tu ne sais peut-être pas convaincre les bouzouks de te faire un don...\n";
					$message .= "Bref, tu perds <span class='pourpre'>-$perte_xp xp</span>.\n\n";
					$message .= "Je suis vraiment navré pour toi, ça ne doit pas être facile d'être aussi nul...\n\n";
					$message .= "	Amicalement, J.F Sébastien.";

					// On ajoute à l'historique
					$this->bouzouk->historique(88, null, array($perte_xp), $mendiant->id);

					$data_missives[] = array(
						'expediteur_id'   => Bouzouk::Robot_JF_Sebastien,
						'destinataire_id' => $mendiant->id,
						'date_envoi'      => $this->datetime,
						'timbre'          => $this->lib_missive->timbres(0),
						'objet'           => 'Aucune donation hier...',
						'message'         => $message
					);
				}
			}

			// S'il y a eu des mendiants sans donation
			if (count($data_missives) > 0)
			{
				$this->db->insert_batch('missives', $data_missives);
			}
		}

		// On vide la table des mendiants
		$this->db->truncate('mendiants');
	}

	public function missives()
	{
		// On supprime toutes les missives supprimées par l'expéditeur et le destinataire
		$this->db->where('expediteur_supprime', '1')
				 ->where('destinataire_supprime', '1')
				 ->delete('missives');

		// On supprime toutes les missives supprimées par le destinataire dont l'expéditeur est un robot
		$this->db->where_in('expediteur_id', $this->bouzouk->get_robots())
				 ->where('destinataire_supprime', '1')
				 ->delete('missives');

		// On envoie un message à tous ceux dont le nombre de missives dépasse la limite
		$query = $this->db->select('j.id, j.pseudo, COUNT(j.id) AS nb_missives')
						  ->from('joueurs j')
						  ->join('missives m', 'm.expediteur_id = j.id OR m.destinataire_id = j.id')
  						  ->where('((m.expediteur_id = j.id AND m.expediteur_supprime = 0) OR (m.destinataire_id = j.id AND m.destinataire_supprime = 0))')
						  ->where('statut !=', Bouzouk::Joueur_Robot)
						  ->group_by('j.id')
						  ->having('COUNT(j.id) >= '.$this->bouzouk->config('missives_limite'))
						  ->get();

		$joueurs       = $query->result();
		$data_missives = array();
		$timbre        = $this->lib_missive->timbres(0);

		foreach ($joueurs as $joueur)
		{
			// On prépare une missive
			$message  = "	Bonjour $joueur->pseudo\n\n";
			$message .= "Tu as <span class='pourpre'>$joueur->nb_missives missives</span> dans ta boîte, tu as atteint la limite, ça déborde !\n";
			$message .= "En effet l'usine de fabrication des timbres est en grève et ne peut supporter qu'un stock de <span class='pourpre'>".$this->bouzouk->config('missives_limite')." missives</span> par bouzouk.\n";
			$message .= "Tu ne peux donc ni écrire ni recevoir de missives jusqu'à ce que tu aies fait un peu de ménage.\n\n";
			$message .= "Sors l'aspirateur ;)\n\n";
			$message .= "	Amicalement, le dealer de Vlurxtrznbnaxl.";

			$data_missives[] = array(
				'expediteur_id'   => Bouzouk::Robot_Dealer,
				'destinataire_id' => $joueur->id,
				'date_envoi'      => $this->datetime,
				'timbre'          => $timbre,
				'objet'           => 'Ta boîte de missives est pleine',
				'message'         => $message
			);
		}

		if (count($data_missives) > 0)
		{
			$this->db->insert_batch('missives', $data_missives);
		}
	}

	public function nettoyage()
	{
		// On supprime les connexions de plus de 15 jours
		$this->db->where('date < (NOW() - INTERVAL 15 DAY)')
				 ->delete('connexions');

		// On supprime les transactions marché noir de plus de 15 jours
		$this->db->where('date < (NOW() - INTERVAL 15 DAY)')
				 ->delete('mc_marche_noir');

		// On supprime les payes employés de plus de 15 jours
		$this->db->where('date < (NOW() - INTERVAL 15 DAY)')
				 ->delete('mc_employes');
			
		// On supprime les parties de plouk de plus de 15 jours
		$this->db->where('date_debut < (NOW() - INTERVAL 15 DAY)')
				 ->delete('mc_plouk');

		// On vide les visiteurs
		$this->db->truncate('visiteurs');

		// On optimise la base de données
		$this->load->dbutil();
		$this->dbutil->optimize_database();

		// On répare les tables
		foreach ($this->db->list_tables() as $table)
		{
			$this->dbutil->repair_table($table);
		}
	}
	
	public function objets()
	{
		// On récupère les ids des joueurs en pause
		$query = $this->db->select('id')
						  ->from('joueurs')
						  ->where('statut', Bouzouk::Joueur_Pause)
						  ->get();
		$joueurs = $query->result();
		$pause_ids = array();

		foreach ($joueurs as $joueur)
			$pause_ids[] = $joueur->id;
						  
		// On enlève 1 de péremption aux objets des maison
		$this->db->set('peremption', 'peremption-1', false)
				 ->where('peremption > 0')
				 ->where_not_in('joueur_id', $pause_ids)
				 ->update('maisons');

		// On enlève 1 de péremption aux objets du marché_noir
		$this->db->set('peremption', 'peremption-1', false)
				 ->where('peremption > 0')
				 ->where_not_in('joueur_id', $pause_ids)
				 ->update('marche_noir');

		// On regroupe les objets périmés des maisons
		$query = $this->db->select('m1.quantite, m1.joueur_id, m1.objet_id')
						  ->from('maisons m1')
						  ->where('m1.peremption = 0 AND EXISTS (select 1 from maisons m2 where m2.peremption = 0 AND m2.objet_id = m1.objet_id AND m2.joueur_id = m1.joueur_id AND m2.id != m1.id)')
						  ->order_by('m1.joueur_id, m1.objet_id')
						  ->get();

		if ($query->num_rows() > 0)
		{
			$objets = $query->result();
			$dernier_joueur_id = 0;
			$dernier_objet_id = 0;
			$quantite = 0;

			foreach ($objets as $objet)
			{
				if ($objet->objet_id != $dernier_objet_id || $objet->joueur_id != $dernier_joueur_id)
				{
					if ($quantite > 0)
					{
						// On supprime toutes les lignes
						$this->db->where('joueur_id', $dernier_joueur_id)
								 ->where('objet_id', $dernier_objet_id)
								 ->where('peremption', 0)
								 ->delete('maisons');

						// On ajoute les objets périmés au joueur
						$this->bouzouk->ajouter_objets($dernier_objet_id, $quantite, 0, $dernier_joueur_id);

						// On réinitialise la quantité
						$quantite = 0;
					}

					// On sauvegarde l'ancienne ligne
					$dernier_joueur_id = $objet->joueur_id;
					$dernier_objet_id = $objet->objet_id;
				}

				$quantite += $objet->quantite;
			}

			if ($quantite > 0)
			{
				// On supprime toutes les lignes
				$this->db->where('joueur_id', $dernier_joueur_id)
						 ->where('objet_id', $dernier_objet_id)
						 ->where('peremption', 0)
						 ->delete('maisons');

				// On ajoute les objets périmés au joueur
				$this->bouzouk->ajouter_objets($dernier_objet_id, $quantite, 0, $dernier_joueur_id);
			}
		}
		
		// ---------- Hook clans ----------
		// Malediction du Schnibble (SDS)
		// -> On supprime les maledictions périmés
		$this->db->where('objet_id', 49)
				 ->where('peremption', 0)
				 ->delete('maisons');
		
		// On enlève un peu d'expérience aux joueurs qui ont trop d'objets périmés dans leur maison
		$perte_xp = $this->bouzouk->config('maison_perte_xp_objets_perimes');
		$nb_objets_perimes_max = $this->bouzouk->config('maison_nb_objets_perimes_perte_xp');

		$query = $this->db->select('joueur_id, SUM(quantite) AS total_perimes')
						  ->from('maisons')
						  ->where('peremption', 0)
						  ->where_not_in('joueur_id', $pause_ids)
						  ->group_by('joueur_id')
						  ->having('total_perimes >= '.$nb_objets_perimes_max)
						  ->get();
		$joueurs = $query->result();

		foreach ($joueurs as $joueur)
		{
			// On enlève l'expérience
			$this->bouzouk->retirer_experience($perte_xp, $joueur->joueur_id);

			// On ajoute à l'historique
			$this->bouzouk->historique(89, null, array($perte_xp), $joueur->joueur_id);
		}

		// On regroupe les objets périmés du marché noir
		$query = $this->db->select('m1.quantite, m1.joueur_id, m1.objet_id, m1.prix')
						  ->from('marche_noir m1')
						  ->where('m1.peremption = 0 AND EXISTS (select 1 from marche_noir m2 where m2.peremption = 0 AND m2.objet_id = m1.objet_id AND m2.joueur_id = m1.joueur_id AND m2.prix = m1.prix AND m2.id != m1.id)')
						  ->order_by('m1.joueur_id, m1.objet_id, m1.prix')
						  ->get();

		if ($query->num_rows() > 0)
		{
			$objets = $query->result();
			$dernier_joueur_id = 0;
			$dernier_objet_id = 0;
			$dernier_prix = 0;
			$quantite = 0;

			foreach ($objets as $objet)
			{
				if ($objet->objet_id != $dernier_objet_id || $objet->joueur_id != $dernier_joueur_id || $objet->prix != $dernier_prix)
				{
					if ($quantite > 0)
					{
						// On supprime toutes les lignes
						$this->db->where('joueur_id', $dernier_joueur_id)
								 ->where('objet_id', $dernier_objet_id)
								 ->where('peremption', 0)
								 ->where('prix', $dernier_prix)
								 ->delete('marche_noir');

						// On ajoute une nouvelle ligne
						$data_marche_noir = array(
							'objet_id'   => $dernier_objet_id,
							'joueur_id'  => $dernier_joueur_id,
							'quantite'   => $quantite,
							'prix'       => $dernier_prix,
							'peremption' => 0
						);
						$this->db->insert('marche_noir', $data_marche_noir);

						// On réinitialise la quantité
						$quantite = 0;
					}

					// On sauvegarde l'ancienne ligne
					$dernier_joueur_id = $objet->joueur_id;
					$dernier_objet_id = $objet->objet_id;
					$dernier_prix = $objet->prix;
				}

				$quantite += $objet->quantite;
			}

			if ($quantite > 0)
			{
				// On supprime toutes les lignes
				$this->db->where('joueur_id', $dernier_joueur_id)
						 ->where('objet_id', $dernier_objet_id)
						 ->where('peremption', 0)
						 ->where('prix', $dernier_prix)
						 ->delete('marche_noir');

				// On ajoute une nouvelle ligne
				$data_marche_noir = array(
					'objet_id'   => $dernier_objet_id,
					'joueur_id'  => $dernier_joueur_id,
					'quantite'   => $quantite,
					'prix'       => $dernier_prix,
					'peremption' => 0
				);
				$this->db->insert('marche_noir', $data_marche_noir);
			}
		}

		// On enlève un peu d'expérience aux joueurs qui ont trop d'objets périmés au marché noir
		$perte_xp = $this->bouzouk->config('marche_noir_perte_xp_objets_perimes');
		$nb_objets_perimes_max = $this->bouzouk->config('marche_noir_nb_objets_perimes_perte_xp');

		$query = $this->db->select('joueur_id, SUM(quantite) AS total_perimes')
						  ->from('marche_noir')
						  ->where('peremption', 0)
						  ->where_not_in('joueur_id', $pause_ids)
						  ->group_by('joueur_id')
						  ->having('total_perimes >= '.$nb_objets_perimes_max)
						  ->get();
		$joueurs = $query->result();

		foreach ($joueurs as $joueur)
		{
			// On enlève -1xp
			$this->bouzouk->retirer_experience($perte_xp, $joueur->joueur_id);

			// On ajoute à l'historique
			$this->bouzouk->historique(90, null, array($perte_xp), $joueur->joueur_id);
		}
	}

	public function payes()
	{
		// On paye le maire selon le salaire fixé
		$query = $this->db->select('maire_id, salaire_maire')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();

		// Si le salaire est > 0 et que le maire n'est pas un robot
		if ($mairie->salaire_maire > 0 && ! in_array($mairie->maire_id, $this->bouzouk->get_robots()))
		{
			// On paye le maire
			$this->db->set('struls', 'struls+'.$mairie->salaire_maire, false)
					 ->where('id', $mairie->maire_id)
					 ->update('joueurs');

			// On retire de la mairie
			$this->lib_mairie->retirer_struls($mairie->salaire_maire);

			// On ajoute à l'historique du maire
			$this->bouzouk->historique(91, null, array(struls($mairie->salaire_maire)), $mairie->maire_id);

			// On ajoute à l'historique de la mairie
			$this->lib_mairie->historique('Tu as reçu ton salaire de maire de '.struls($mairie->salaire_maire), $mairie->maire_id);
		}

		// On remet le dernier salaire à 0
		$this->db->set('dernier_salaire', 0)
				 ->update('employes');

		// On va chercher la liste des entreprises
		$query = $this->db->select('id, struls, salaire_chef, chef_id')
						  ->from('entreprises')
						  ->get();
		$entreprises = $query->result();

		$data_mc_employes = array();
		
		// Pour chaque entreprise
		foreach ($entreprises as $entreprise)
		{
			// On va chercher la liste des employés
			$query = $this->db->select('e.joueur_id, e.salaire, e.payer, e.job_id, jo.salaire AS salaire_recommande')
							  ->from('employes e')
							  ->join('joueurs j', 'j.id = e.joueur_id')
							  ->join('jobs jo', 'jo.id = e.job_id')
							  ->where('j.statut', Bouzouk::Joueur_Actif)
							  ->where('e.entreprise_id', $entreprise->id)
							  ->get();
			$employes = $query->result();

			$total_salaires = 0;

			// Pour chaque employe
			foreach ($employes as $employe)
			{
				// Si le patron veut bien payer l'employé
				if ($employe->payer)
				{
					$dernier_salaire = $employe->salaire;

					// On paye l'employé
					$this->db->set('struls', 'struls+'.$dernier_salaire, false)
							 ->where('id', $employe->joueur_id)
							 ->update('joueurs');

					// On met à jour le dernier salaire
					$this->db->set('dernier_salaire', $dernier_salaire)
							 ->where('joueur_id', $employe->joueur_id)
							 ->update('employes');

					$this->bouzouk->historique(92, null, array(struls($dernier_salaire)), $employe->joueur_id);

					// On cumule la masse salariale
					$total_salaires += $dernier_salaire;
				}

				// On enregistre les salaires en dessous de la FAQ pour les multi-comptes
				if ($employe->salaire < $employe->salaire_recommande)
				{
					$data_mc_employes[] = array(
						'patron_id'          => $entreprise->chef_id,
						'employe_id'         => $employe->joueur_id,
						'date'               => $this->datetime,
						'salaire'            => $employe->salaire,
						'salaire_recommande' => $employe->salaire_recommande,
						'job_id'             => $employe->job_id,
					);
				}
			}

			// On débite l'entreprise du total des salaires
			$struls_entreprise = $entreprise->struls - $total_salaires;
			$dernier_salaire_patron = 0;

			// Si il reste assez, on paye le patron
			if ($entreprise->salaire_chef > 0)
			{
				$this->db->set('struls', 'struls+'.$entreprise->salaire_chef, false)
						 ->where('id', $entreprise->chef_id)
						 ->update('joueurs');

				$dernier_salaire_patron = $entreprise->salaire_chef;
				$struls_entreprise -= $entreprise->salaire_chef;

				$this->bouzouk->historique(93, null, array(struls($dernier_salaire_patron)), $entreprise->chef_id);
			}

			// On met à jour le dernier salaire
			$this->db->set('dernier_salaire', $dernier_salaire_patron)
					 ->where('id', $entreprise->id)
					 ->update('entreprises');

			// On met à jour les struls de l'entreprise après toutes les modifications
			$this->db->set('struls', $struls_entreprise)
					 ->where('id', $entreprise->id)
					 ->update('entreprises');

			// On met à jour l'historique de cette session
			$query = $this->db->select('rentree_argent')
							  ->from('historique_entreprises')
							  ->where('entreprise_id', $entreprise->id)
							  ->where('date', $this->date)
							  ->get();

			if ($query->num_rows() == 1)
			{
				$historique_entreprise = $query->row();

				$data_historique_entreprises = array(
					'salaires_employes' => $total_salaires,
					'salaire_patron'    => $dernier_salaire_patron,
					'struls'            => $struls_entreprise
				);
				$this->db->where('entreprise_id', $entreprise->id)
						 ->where('date', $this->date)
						 ->update('historique_entreprises', $data_historique_entreprises);
			}

			// Si pas d'historique, on ajoute une ligne
			else
			{
				$nb_employes = $this->db->where('entreprise_id', $entreprise->id)
										->count_all_results('employes');

				$data_historique_entreprises = array(
					'entreprise_id'     => $entreprise->id,
					'date'              => $this->date,
					'nb_employes'       => $nb_employes,
					'salaires_employes' => $total_salaires,
					'salaire_patron'    => $dernier_salaire_patron,
					'struls'            => $struls_entreprise
				);
				$this->db->insert('historique_entreprises', $data_historique_entreprises);
			}
		}

		// On enregistre les salaires en dessous de la FAQ pour les multi-comptes
		if (count($data_mc_employes) > 0)
			$this->db->insert_batch('mc_employes', $data_mc_employes);
	}

	public function plouk()
	{
		// On récupère les parties qui doivent être supprimées
		$query = $this->db->select('id')
						  ->from('plouk_parties')
						  ->where('statut', Lib_plouk::Terminee)
						  ->get();

		if ($query->num_rows() > 0)
		{
			$parties = $query->result();
			$parties_ids = array();

			// On récupère les ids
			foreach ($parties as $partie)
				$parties_ids[] = $partie->id;

			// On supprime les parties
			$this->db->where_in('id', $parties_ids)
					 ->delete('plouk_parties');

			// On supprime les messages de tchat
			$this->db->where_in('partie_id', $parties_ids)
					 ->delete('plouk_tchat');

			// On supprime les connectés
			$this->db->where_in('partie_id', $parties_ids)
					 ->delete('plouk_connectes');
		}
	}
	
	public function productions()
	{
		// On regarde si une promotion est en cours
		$query = $this->db->select('promotion_objet_id AS objet_id')
						  ->from('mairie')
						  ->where('promotion_objet_id IS NOT NULL')
						  ->get();
		$promotion = $query->num_rows() == 1 ? $query->row() : null;
		
		// On arrête la promotion du jour
		$this->db->set('promotion_objet_id', null)
				 ->update('mairie');
		
		// On va chercher toutes les entreprises
		$query = $this->db->select('e.id, e.objet_id, o.prix AS prix_produit, o.nom AS nom_produit, e.chef_id, e.anciennete_chef, j.statut AS statut_chef, j.faim, j.sante, j.stress, j.force, j.charisme, j.intelligence, j.experience')
						  ->from('entreprises e')
						  ->join('objets o', 'o.id = e.objet_id')
						  ->join('joueurs j', 'j.id = e.chef_id')
						  ->get();
		$entreprises = $query->result();

		// On va chercher le salaire max des jobs
		$query = $this->db->select('MAX(salaire) AS salaire_max')
						  ->from('jobs')
						  ->get();
		$jobs = $query->row();

		// On remet les bonus à 0
		$this->db->set('dernier_bonus', 0)
				 ->update('employes');

		$this->db->set('dernier_bonus', 0)
				 ->update('entreprises');

		// ---------- Hook clans ----------
		// Grêve générale (Syndicat)
		$query = $this->db->select('cal.id, cal.action_id, cal.parametres, c.nom AS nom_clan, c.mode_recrutement, ca.nom AS nom_action, cal.date_debut')
						  ->from('clans_actions_lancees cal')
						  ->join('clans c', 'c.id = cal.clan_id')
						  ->join('clans_actions ca', 'ca.id = cal.action_id')
						  ->where('cal.action_id', 6)
						  ->where('cal.statut', Bouzouk::Clans_ActionEnCours)
						  ->get();
		$greve_generale = ($query->num_rows() == 1) ? $query->row() : null;

		// Pour chaque entreprise
		foreach ($entreprises as $entreprise)
		{
			// ---------- Hook clans ----------
			// Grêve d'entreprise (Syndicat)
			$query = $this->db->select('cal.id, cal.action_id, cal.parametres, c.nom AS nom_clan, c.mode_recrutement, ca.nom AS nom_action, cal.date_debut')
						  	  ->from('clans_actions_lancees cal')
						  	  ->join('clans c', 'c.id = cal.clan_id')
						  	  ->join('clans_actions ca', 'ca.id = cal.action_id')
						  	  ->where('cal.action_id', 2)
						  	  ->where('c.entreprise_id', $entreprise->id)
						  	  ->where('cal.statut', Bouzouk::Clans_ActionEnCours)
						  	  ->get();
			$greve_entreprise = ($query->num_rows() == 1) ? $query->row() : null;

			$production = 0;

			// ---------- Hook clans ----------
			// Grêve entreprise : personne dans l'entreprise ne produit
			if ( ! isset($greve_entreprise))
			{
				// On va chercher la liste des employés actifs
				$query = $this->db->select('j.salaire, jo.id, j.stat, j.valeur, j.bonus, jo.faim, jo.sante, jo.stress, jo.force, jo.charisme, jo.intelligence')
								  ->from('employes e')
								  ->join('jobs j', 'j.id = e.job_id')
								  ->join('joueurs jo', 'jo.id = e.joueur_id')
								  ->where('jo.statut', Bouzouk::Joueur_Actif)
								  ->where('e.entreprise_id', $entreprise->id)
								  ->get();

				// S'il y a au moins un employe
				if ($query->num_rows() > 0)
				{
					$employes = $query->result();

					// On calcule la production totale du jour
					foreach ($employes as $employe)
					{
						// ---------- Hook clans ----------
						// Grêve générale : si l'employé est syndiqué, il ne produit pas
						if (isset($greve_generale))
						{
							$query = $this->db->select('p.id')
											  ->from('politiciens p')
											  ->join('clans c', 'c.id = p.joueur_id')
											  ->where('p.joueur_id', $employe->id)
											  ->where('c.type', Bouzouk::Clans_TypeSyndicat)
											  ->get();

							if ($query->num_rows() == 0)
							{
								$syndique = $this->db->where('chef_id', $employe->id)
												 	 ->where('type', Bouzouk::Clans_TypeSyndicat)
												 	 ->count_all_results('clans');
							}

							else
								$syndique = true;

							if ($syndique)
								continue;
						}

						$coeff = (mt_rand(6, 9) * min(2, $jobs->salaire_max / $employe->salaire));
						$production += $employe->salaire + $employe->salaire / $coeff;

						// Bonus de l'employé selon sa condition physique et ses stats
						if ($employe->faim >= 50 && $employe->sante >= 50 && $employe->stress <= 50 && $employe->stat != '' && $employe->{$employe->stat} >= $employe->valeur)
						{
							$production += $employe->bonus;
							$this->db->set('dernier_bonus', 1)
									 ->where('joueur_id', $employe->id)
									 ->update('employes');
						}
					}
				}
			}

			// On rajoute un peu de production par le patron
			if ($entreprise->statut_chef == Bouzouk::Joueur_Actif && $entreprise->faim >= 50 && $entreprise->sante >= 50 && $entreprise->stress <= 50 && $entreprise->force >= 200 && $entreprise->charisme >= 200 && $entreprise->intelligence >= 200)
			{
				// On va chercher l'équivalent du métier pour le patron
				$xp = $entreprise->experience + $entreprise->anciennete_chef;

				$query = $this->db->select('salaire')
								  ->from('jobs')
								  ->where('experience <=', $xp)
								  ->order_by('salaire', 'desc')
								  ->limit(1)
								  ->get();
				$job_patron = $query->row();

				// On fait le même calcul que l'employé mais on enlève un peu de struls parce que faut pas déconner quand même
				// Puis ça permettra aux gars comme Bubool de se creuser la tête sur les calculs de salaires :D
				$coeff = (mt_rand(6, 9) * min(2, $jobs->salaire_max / $job_patron->salaire));
				$production += $job_patron->salaire + $job_patron->salaire / $coeff - mt_rand(1, floor($job_patron->salaire / 5.0));

				$this->db->set('dernier_bonus', 1)
						 ->where('id', $entreprise->id)
						 ->update('entreprises');
			}

			// On met à jour la production
			if ($production > 0)
			{
				// Si une promo a eu lieu dans la journée, la production des entreprises fabriquant ces objets diminue un peu. On averti aussi le patron
				if (isset($promotion) && $promotion->objet_id == $entreprise->objet_id)
				{
					$production = $production - ($this->bouzouk->config('reduction_production_promotion') * $production / 100.0);
					
					$this->bouzouk->notification(235, array($entreprise->nom_produit), $entreprise->chef_id);
				}

				$production = (int)$production;

				$this->db->set('production', 'production+'.$production, false)
						 ->where('id', $entreprise->id)
						 ->update('entreprises');
			}
		}
	}

	public function rumeurs()
	{
		// On supprime les rumeurs refusées depuis trop longtemps
		$this->db->where('date < (NOW() - INTERVAL '.$this->bouzouk->config('communaute_delai_rumeurs_refusees').' DAY)')
				 ->where('statut', Bouzouk::Rumeur_Refusee)
				 ->delete('rumeurs');
	}

	public function taxes_surprises()
	{
		// ---------- Hook clans ----------
		// Magouille fiscale (Struleone)
		$joueurs_magouille_fiscale = $this->lib_clans->magouille_fiscale_en_cours();

		// Si des taxes surprise ont été programmées par le maire, on les envoie
		$query = $this->db->select('t_s.id, t_s.taux, t_s.raison, t_s.date_taxe, t_s.maire_id, j.pseudo')
						  ->from('taxes_surprises t_s')
						  ->join('joueurs j', 'j.id = t_s.maire_id')
						  ->where('t_s.distribuee', 0)
						  ->get();

		if ($query->num_rows() > 0)
		{
			$taxes_surprises = $query->result();
			$data_factures   = array();
			$data_missives   = array();
			$timbre          = $this->lib_missive->timbres(0);

			// On récupère la liste des joueurs
			$query = $this->db->select('id, pseudo, struls, statut, pause_payer_taxes')
							  ->from('joueurs')
							  ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))
							  ->get();
			$joueurs = $query->result();

			// Pour chaque taxe
			foreach ($taxes_surprises as $taxe)
			{
				// On ajoute à l'historique de la mairie
				$this->lib_mairie->historique("Tu as envoyé une taxe surprise de <span class='pourpre'>$taxe->taux%</span> à tous les bouzouks", $taxe->maire_id);

				// Pour chaque joueur
				foreach ($joueurs as $joueur)
				{
					// ---------- Hook clans ----------
					// Magouille fiscale (Struleone)
					if (in_array($joueur->id, $joueurs_magouille_fiscale))
						continue;

					$montant = (int) ($joueur->struls * $taxe->taux / 100);

					if ($montant <= 0)
					{
						continue;
					}

					// Si le joueur est en pause et qu'il veut payer ses factures automatiquement, on paye
					if ($joueur->statut == Bouzouk::Joueur_Pause && $joueur->pause_payer_taxes == 1)
					{
						// On enlève au joueur
						$this->db->set('struls', 'struls-'.$montant, false)
								 ->where('id', $joueur->id)
								 ->update('joueurs');

						// On ajoute à la mairie
						$this->db->set('struls', 'struls+'.$montant, false)
								 ->update('mairie');

						$this->bouzouk->historique(94, null, array(struls($montant)), $joueur->id);

						continue;
					}

					// On prépare une facture
					$data_factures[] = array(
						'joueur_id'  => $joueur->id,
						'titre'      => 'Taxe surprise',
						'montant'    => $montant,
						'majoration' => 0,
						'date'       => $taxe->date_taxe
					);

					// On prépare une missive
					$message  = "	Bonjour $joueur->pseudo\n\n";
					$message .= "Le maire ".profil($taxe->maire_id, $taxe->pseudo)." a décidé d'envoyer une taxe surprise de <span class='pourpre'>$taxe->taux%</span> à tous les bouzouks pour la raison suivante :\n\n";
					$message .= "<i>".form_prep($taxe->raison)."</i>\n\n";
					$message .= "Tu dois donc verser à la mairie la modique somme de <span class='pourpre'>".struls($montant)."</span>.\n";
					$message .= "Tu as <span class='pourpre'>".pluriel($this->bouzouk->config('factures_delai_majoration'), 'jour')."</span> pour la payer, passé ce délai, tu ne seras plus accepté dans aucun service ni aucune boutique de la ville de Vlurxtrznbnaxl nécessitant de l'argent et tu devras travailler dur afin de rembourser ta dette majorée de <span class='pourpre'>".$this->bouzouk->config('factures_pourcent_majoration')."%</span>.\n";
					$message .= "Ce serait dommage... :-)\n\n";
					$message .= "<a href='".site_url('factures')."'>Payer mes factures</a>\n\n";
					$message .= "	Amicalement, la mairie de Vlurxtrznbnaxl.";

					$data_missives[] = array(
						'expediteur_id'   => Bouzouk::Robot_Maire,
						'destinataire_id' => $joueur->id,
						'date_envoi'      => $this->datetime,
						'timbre'          => $timbre,
						'objet'           => 'Taxe surprise !',
						'message'         => $message
					);
				}

				// On met à jour la taxe pour indiquer qu'elle a bien été distribuée
				$this->db->set('distribuee', 1)
						 ->where('id', $taxe->id)
						 ->update('taxes_surprises');
			}
			$this->db->insert_batch('factures', $data_factures);
			$this->db->insert_batch('missives', $data_missives);
		}
	}

	public function tchats()
	{
		// On vide la table des connectés
		$this->db->truncate('tchats_connectes');
		$this->db->truncate('tchats_entreprises_connectes');
		$this->db->truncate('tchats_clans_connectes');

		// On supprime les vieux messages des tchats d'entreprise
		// On va chercher toutes les entreprises
		$query = $this->db->select('id')
						  ->from('entreprises')
						  ->get();
		$entreprises = $query->result();

		// Pour chaque entreprise
		foreach ($entreprises as $entreprise)
		{
			// On va cherche l'id du plus vieux message de cet entreprise
			$query = $this->db->select('id')
							  ->from('tchats_entreprises')
							  ->where('tchat_id', $entreprise->id)
							  ->order_by('id', 'desc')
							  ->limit(1, $this->bouzouk->config('maintenance_tchats_messages_entreprise'))
							  ->get();

			if ($query->num_rows() == 1)
			{
				$tchat = $query->row();

				// On supprime tous les messages plus anciens que cet id
				$this->db->where('id <=', $tchat->id)
						 ->where('tchat_id', $entreprise->id)
						 ->delete('tchats_entreprises');
			}
		}

		// On supprime les vieux messages des tchats
		foreach (array(Bouzouk::Tchat_Asile, Bouzouk::Tchat_Journal, Bouzouk::Tchat_Chomeur) as $type)
		{
			// On va cherche l'id du plus vieux message
			$query = $this->db->select('id')
							  ->from('tchats')
							  ->where('tchat_id', $type)
							  ->order_by('id', 'desc')
							  ->limit(1, $this->bouzouk->config('maintenance_tchats_messages'))
							  ->get();

			if ($query->num_rows() == 1)
			{
				$tchat = $query->row();

				// On supprime tous les messages plus anciens que cet id
				$this->db->where('id <=', $tchat->id)
				 		 ->where('tchat_id', $type)
						 ->delete('tchats');
			}
		}

		// On supprime les vieux messages des tchats de clans
		// On va chercher tous les clans
		$query = $this->db->select('id')
						  ->from('clans')
						  ->get();
		$clans = $query->result();

		// Pour chaque clan
		foreach ($clans as $clan)
		{
			// On va cherche l'id du plus vieux message de ce clan
			$query = $this->db->select('id')
							  ->from('tchats_clans')
							  ->where('tchat_id', $clan->id)
							  ->order_by('id', 'desc')
							  ->limit(1, $this->bouzouk->config('maintenance_tchats_messages_clan'))
							  ->get();

			if ($query->num_rows() == 1)
			{
				$tchat = $query->row();

				// On supprime tous les messages plus anciens que cet id
				$this->db->where('id <=', $tchat->id)
						 ->where('tchat_id', $clan->id)
						 ->delete('tchats_clans');
			}
		}
	}

	public function verifier_site()
	{
		$verifications = $this->lib_maintenance->tests_site();
		$tests_reussis = true;
		
		foreach ($verifications as $verification)
		{
			if ( ! $verification['resultat'])
			{
				$tests_reussis = false;
				break;
			}
		}

		// On envoit un mail en cas d'erreur
		if ( ! $tests_reussis)
		{
			$message = "Les tests automatiques effectués après la maintenance ont remonté des erreurs sur le site à corriger d'urgence !\n\n";

			// On ajoute les erreurs au message
			foreach ($verifications as $verification)
			{
				$resultat = $verification['resultat'] ? '[OK]     ' : '[ERREUR] ';
				$message .= $resultat.$verification['texte']."\n";
			}
			
			$this->email->from($this->bouzouk->config('email_from'), 'Bouzouks')
						->to($this->bouzouk->config('email_from'))
						->subject('[Bouzouks.net] Maintenance : erreur lors des tests du site')
						->message($message)
						->send();
		}
	}

	private function map_stop(){
		$this->node_server = $this->vlux_factory->get_server();
		// On stocke l'état initial du serveur
		$this->etat_node = $this->node_server->etat;
		// On arrête le serveur node
		$this->node_server->switch_state(0);
		// On vide le dossier application/cache/cache_cli
		$this->lib_cache_cli->clear_all();
	}

	private function map_start(){
		// On redémarre le serveur node dans son état antèrieur
		$this->node_server->switch_state($this->etat_node);
	}
}
