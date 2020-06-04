<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : gestion de l'entreprise côté patron
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Entreprises extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		
		// ---------- Hook clans ----------
		// Malediction du Schnibble (SDS)
		if ($this->session->userdata('maudit') && ! $this->bouzouk->is_admin())
			redirect('clans/maudit');
	}

	public function creer()
	{
		// On vérifie que le joueur a assez d'expérience pour créer une entreprise
		if ($this->session->userdata('experience') < $this->bouzouk->config('entreprises_xp_creer'))
		{
			$vars = array(
				'titre_layout' => 'Créer une entreprise',
				'titre'        => 'Créer une entreprise',
				'image_url'    => 'entreprises/entreprise.gif',
				'message'      => "Tu dois avoir au moins <span class='pourpre'>".$this->bouzouk->config('entreprises_xp_creer')." xp</span> pour pouvoir créer ton entreprise."
			);
			return $this->layout->view('blocage', $vars);
		}

		// On va chercher tous les objets possibles pour la création d'entreprises
		$query = $this->db->select('o.id, o.nom, o.type, o.image_url, COUNT(e.id) AS nb_entreprises')
						  ->from('objets o')
						  ->join('entreprises e', 'e.objet_id = o.id', 'left')
						  ->where('o.disponibilite', 'entreprise')
						  ->group_by('o.id')
						  ->get();
		$objets = array();

		// On créé un array
		foreach ($query->result() as $objet)
		{
			if ( ! isset($objets[$objet->type]))
			{
				$objets[$objet->type] = array($objet);
			}

			else
			{
				$objets[$objet->type][] = $objet;
			}
		}

		// On va chercher le prix d'une entreprise
		$query = $this->db->select('aide_entreprise, struls')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();
		$prix_entreprise = $this->bouzouk->config('entreprises_prix_entreprise') - $mairie->aide_entreprise;

		$vars = array(
			'objets'          => $objets,
			'prix_entreprise' => $prix_entreprise,
			'aide_mairie'     => $mairie->aide_entreprise
		);

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('nom', "Le nom de l'entreprise", 'required|min_length[3]|max_length[20]|regex_match[#^[a-z0-9 .\'-]+$#i]|is_unique[entreprises.nom]|is_unique[classement_entreprises.nom_entreprise]');
		$this->form_validation->set_rules('objet_id', "Le type de production", 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
		{
			return $this->layout->view('entreprises/creer', $vars);
		}

		// On vérifie que le joueur a assez de struls pour payer la création
		if ($this->session->userdata('struls') < $prix_entreprise)
		{
			$this->echec("Tu n'as pas assez de struls pour payer la création d'entreprise");
			return $this->layout->view('entreprises/creer', $vars);
		}

		$this->load->library('lib_mairie');

		// On vérifie que la mairie a assez de struls
		if ( ! $this->lib_mairie->fonds_suffisants($mairie->aide_entreprise))
		{
			$this->echec("La mairie n'a pas assez de struls pour aider à la création d'entreprise");
			return $this->layout->view('entreprises/creer', $vars);
		}

		// On vérifie que l'objet existe
		$query = $this->db->select('nom, disponibilite')
						  ->from('objets')
						  ->where('id', $this->input->post('objet_id'))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Cet objet n'existe pas");
			return $this->layout->view('entreprises/creer', $vars);
		}

		// On vérifie que l'objet est disponible pour les entreprises
		$objet = $query->row();

		if ($objet->disponibilite != 'entreprise')
		{
			$this->echec("Cet objet n'est pas disponible pour les entreprises");
			return $this->layout->view('entreprises/creer', $vars);
		}

		// On vérifie que le joueur n'a pas fait faillite il y a quelques temps
		$faillite = $this->db->where('joueur_id', $this->session->userdata('id'))
							 ->count_all_results('faillites');

		if ($faillite > 0)
		{
			$this->echec('Tu as perdu une entreprise il y a moins de '.$this->bouzouk->config('entreprises_duree_faillite').' jours, tu dois attendre ce délai avant de pouvoir recréer une entreprise');
			return $this->layout->view('entreprises/creer', $vars);
		}

		// On libère les annonces du chômeur
		$this->load->library('lib_entreprise');
		$this->lib_entreprise->liberer_annonces($this->session->userdata('id'));

		// On retire le prix d'une entreprise au joueur
		$this->bouzouk->retirer_struls($prix_entreprise);

		// On retire le prix de l'aide entreprise à la mairie
		$this->lib_mairie->retirer_struls($mairie->aide_entreprise);

		// On insère la nouvelle entreprise
		$data_entreprises = array(
			'nom'             => $this->input->post('nom'),
			'date_creation'   => bdd_datetime(),
			'chef_id'         => $this->session->userdata('id'),
			'objet_id'        => $this->input->post('objet_id'),
			'struls'          => $prix_entreprise + $prix_entreprise / 5,
			'salaire_chef'    => 50,
		);
		$this->db->insert('entreprises', $data_entreprises);

		// Ne rien mettre ici, à cause du insert_id()
		
		// On ajoute un message de robot sur le tchat
		$data_tchats_entreprises = array(
			'tchat_id'      => $this->db->insert_id(),
			'joueur_id'     => Bouzouk::Robot_MissPoohLett,
			'message'       => "Bienvenue sur la machine à café de l'entreprise, il ne reste plus qu'à l'inaugurer :)",
			'date_envoi'    => bdd_datetime()
		);
		$this->db->insert('tchats_entreprises', $data_tchats_entreprises);
				
		// La session doit être mise à jour
		$this->bouzouk->augmente_version_session();

		// On ajoute de l'expérience
		$this->bouzouk->ajouter_experience($this->bouzouk->config('entreprises_gain_xp_creer'));

		// On ajoute à l'historique du joueur
		$this->bouzouk->historique(49, 50, array($this->input->post('nom'), $prix_entreprise, $this->bouzouk->config('entreprises_gain_xp_creer')));

		// On ajoute à l'historique de la mairie
		$this->lib_mairie->historique(profil()." a créé l'entreprise <span class='pourpre'>".$this->input->post('nom')."</span> (".$objet->nom."), la mairie perd ".struls($mairie->aide_entreprise));

		// On affiche un message de confirmation
		$this->succes('Ton entreprise à bien été créée sous le nom de <span class="pourpre">'.$this->input->post('nom')."</span>.<br>La somme de <span class='pourpre'>$prix_entreprise
					   struls</span> a été déboursée de ton porte-monnaie (ben oui, ça coûte cher toutes ces machines).<br>Nous te rappelons que tu n'as pas le droit d'envoyer d'offre
					   d'emploi à d'autres bouzouks par missives au risque de voir ton compte bloqué : utilise le recrutement par petites annonces, merci.");
		redirect('entreprises/gerer');
	}

	private function get_clans_actions()
	{
		// ---------- Hook clans ----------
		// Pression syndicale
		// Grêve d'entreprise
		// Soutien salarial
		// Grêve générale
		$actions = array(null, null, null, null);
		$actions_possibles = array(1, 2, 3, 6);

		$query = $this->db->select('cal.id, cal.action_id, cal.parametres, c.nom AS nom_clan, c.mode_recrutement, ca.nom AS nom_action, cal.date_debut, c.entreprise_id')
						  ->from('clans_actions_lancees cal')
						  ->join('clans c', 'c.id = cal.clan_id')
						  ->join('clans_actions ca', 'ca.id = cal.action_id')
						  ->where_in('cal.action_id', $actions_possibles)
						  ->where('cal.statut', Bouzouk::Clans_ActionEnCours)
						  ->get();

		foreach ($query->result() as $action)
		{
			if ($action->id == 6 || isset($action->entreprise_id) && $action->entreprise_id == $this->session->userdata('entreprise_id'))
			{
				foreach ($actions_possibles as $cle => $action_id)
				{
					if ($action->action_id == $action_id)
					{
						$action->parametres = unserialize($action->parametres);
						$action->nom_clan = ($action->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('Un clan') : couleur(form_prep($action->nom_clan));
						$actions[$cle] = $action;
					}
				}
			}
		}

		return $actions;
	}

	public function gerer()
	{
		// On va chercher les infos de l'entreprise
		$query = $this->db->select('e.nom, e.date_creation, e.struls, e.salaire_chef, e.anciennete_chef, e.dernier_bonus, e.message_1, e.message_2, e.production, e.derniere_rentree, e.dernier_salaire,
							e.historique_publique, e.syndicats_autorises, c_e.position, c_e.evolution, o.nom AS nom_objet, o.image_url, o.prix')
				  ->from('entreprises e')
				  ->join('objets o', 'o.id = e.objet_id')
				  ->join('classement_entreprises c_e', 'c_e.entreprise_id = e.id', 'left')
				  ->where('e.id', $this->session->userdata('entreprise_id'))
				  ->get();
		$entreprise = $query->row();

		// On construit la liste des syndicats de l'entreprise
		$query = $this->db->select('id')
						  ->from('clans')
						  ->where('entreprise_id', $this->session->userdata('entreprise_id'))
						  ->where('type', Bouzouk::Clans_TypeSyndicat)
						  ->get();
		$clans = $query->result();
		$syndicats = array(0);

		foreach ($clans as $clan)
			$syndicats[] = $clan->id;

		// On va chercher la liste des employés
		$query = $this->db->select('j.id, j.pseudo, j.experience, j.statut, e.job_id, e.salaire, e.prime_depart, e.anciennete, e.payer, e.dernier_bonus, p.id AS syndique, c.id AS chef_syndicat')
						 ->from('employes e')
						 ->join('joueurs j', 'j.id = e.joueur_id')
 						 ->join('politiciens p', 'p.joueur_id = j.id AND p.clan_id IN ('.implode(',', $syndicats).')', 'left')
 						 ->join('clans c', 'c.chef_id = j.id AND c.type = '.Bouzouk::Clans_TypeSyndicat, 'left')
						 ->where('e.entreprise_id', $this->session->userdata('entreprise_id'))
						 ->order_by('e.anciennete')
						 ->order_by('date_embauche')
						 ->order_by('j.pseudo')
						 ->get();
		$nb_employes = $query->num_rows();
		$employes = $query->result();

		// On calcule la masse salariale
		$total_salaires = $entreprise->salaire_chef;
		foreach ($employes as $employe)
		{
			if ($employe->statut == Bouzouk::Joueur_Actif AND $employe->payer)
				$total_salaires += $employe->salaire;
		}

		// On va chercher la liste des jobs du jeu
		$query = $this->db->select('id, nom, experience')
						  ->from('jobs')
						  ->order_by('experience')
						  ->get();
		$jobs = $query->result();

		// On compte le nombre de bonus hier
		$nb_bonus = 0;

		foreach ($employes as $employe)
			if ($employe->dernier_bonus)
				$nb_bonus++;

		// On compte le nombre de syndicats
		$nb_syndicats = $this->db->where('type', Bouzouk::Clans_TypeSyndicat)
								 ->where('entreprise_id', $this->session->userdata('entreprise_id'))
								 ->count_all_results('clans');
		// On va chercher tous les objets possibles pour la création d'entreprises
		$query = $this->db->select('o.id, o.nom, o.type, o.image_url, COUNT(e.id) AS nb_entreprises')
						  ->from('objets o')
						  ->join('entreprises e', 'e.objet_id = o.id', 'left')
						  ->where('o.disponibilite', 'entreprise')
						  ->group_by('o.id')
						  ->get();
		 $objets = array();
		// On créé un array pour le formatage bouffzouk etc
		foreach ($query->result() as $objet)
		{
			if ( ! isset($objets[$objet->type]))
			{
				$objets[$objet->type] = array($objet);
			}

			else
			{
				$objets[$objet->type][] = $objet;
			}
		}
		// ---------- Hook clans ----------
		$actions            = $this->get_clans_actions();
		$pression_syndicale = $actions[0];
		$greve_entreprise   = $actions[1];
		$soutien_salarial   = $actions[2];
		$greve_generale     = $actions[3];
		
		// On affiche
		$vars = array(
			'entreprise'         => $entreprise,
			'objets'          	 => $objets,
			'employes'           => $employes,
			'nb_employes'        => $nb_employes,
			'nb_bonus'           => $nb_bonus,
			'estimation'         => $entreprise->production,
			'total_salaires'     => $total_salaires,
			'jobs'               => $jobs,
			'nb_syndicats'       => $nb_syndicats,
			'table_smileys'      => creer_table_smileys('message'),
			'pression_syndicale' => $pression_syndicale,
			'soutien_salarial'   => $soutien_salarial,
			'greve_entreprise'   => $greve_entreprise,
			'greve_generale'     => $greve_generale
			);
		return $this->layout->view('entreprises/gerer', $vars);
	}

	public function historique()
	{
		// On récupère l'historique de l'entreprise
		$query = $this->db->select('date, nb_employes, impots, rentree_argent, salaires_employes, salaire_patron, pourcent_achats, struls')
						  ->from('historique_entreprises')
						  ->where('entreprise_id', $this->session->userdata('entreprise_id'))
						  ->order_by('date', 'desc')
						  ->get();
		$historiques = $query->result();

		// On affiche
		$vars = array(
			'historiques' => $historiques
		);
		return $this->layout->view('entreprises/historique', $vars);
	}

	public function modifier_employes()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('ids', 'Les employés', 'required');
		$this->form_validation->set_rules('job_ids', 'Les jobs', 'required');
		$this->form_validation->set_rules('salaires', 'Les salaires', 'required');

		if ( ! $this->form_validation->run())
			return $this->gerer();
	
		// On récupère les infos
		$job_ids             = $this->input->post('job_ids');
		$salaires            = $this->input->post('salaires');
		$payer               = $this->input->post('payer');
		$virer               = $this->input->post('virer');
		$erreurs_pseudos     = '';
		$nb_joueurs_modifies = 0;

		// Si le joueur veut virer des employés
		if ($virer !== false)
		{
			$employes_a_virer = array();

			// On regarde quels joueurs peuvent se faire virer
			foreach (array_keys($this->input->post('virer')) as $joueur_id)
			{
				$query = $this->db->select('e.date_embauche, e.prime_depart, j.pseudo')
								  ->from('employes e')
								  ->join('joueurs j', 'j.id = e.joueur_id')
								  ->where('e.joueur_id', $joueur_id)
								  ->where('e.entreprise_id', $this->session->userdata('entreprise_id'))
								  ->get();

				// Si le joueur n'existe pas, on continue
				if ($query->num_rows() == 0)
					continue;

				$employe = $query->row();

				// Si le joueur a été embauché il y a peu de temps, erreur
				if (strtotime($employe->date_embauche) >= strtotime(bdd_datetime().'-'.$this->bouzouk->config('entreprises_attente_embauche').' HOUR'))
				{
					$erreurs_pseudos .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$employe->pseudo." => Tu dois attendre ".$this->bouzouk->config('entreprises_attente_embauche')."h avant de pouvoir virer ce bouzouk<br>";
					continue;
				}

				// Sinon on peut le virer et lui envoyer une missive
				$employes_a_virer[$joueur_id] = array(
					'pseudo'       => $employe->pseudo,
					'prime_depart' => $employe->prime_depart
				);
			}

			// On débauche tous les employés virés
			if (count($employes_a_virer) > 0)
			{
				$this->load->library('lib_missive');
				
				// On débauche les employés
				$this->db->where('entreprise_id', $this->session->userdata('entreprise_id'))
						 ->where_in('joueur_id', array_keys($employes_a_virer))
						 ->delete('employes');

				// Leur session doit être mise à jour
				$this->db->set('version_session', 'version_session+1', false)
						 ->where_in('id', array_keys($employes_a_virer))
						 ->update('joueurs');

				$nb_joueurs_modifies += $this->db->affected_rows();

				// On va chercher le nom de l'entreprise
				$query = $this->db->select('nom')
								  ->from('entreprises')
								  ->where('id', $this->session->userdata('entreprise_id'))
								  ->get();
				$entreprise = $query->row();

				// On leur envoit une missive
				$data_missives = array();
				$date          = bdd_datetime();
				$timbre        = $this->lib_missive->timbres(0);
				$total_primes  = 0;
				$this->load->library('lib_clans');

				foreach ($employes_a_virer as $joueur_id => $array_joueur)
				{
					// On quitte le syndicat
					$this->lib_clans->quitter_syndicat($joueur_id);

					$pseudo       = $array_joueur['pseudo'];
					$prime_depart = $array_joueur['prime_depart'];

					// On lui ajoute la prime de départ
					$this->bouzouk->ajouter_struls($prime_depart, $joueur_id);
					$total_primes += $prime_depart;

					// On ajoute à l'historique de l'employé
					if ($prime_depart > 0)
						$this->bouzouk->historique(51, 52, array(profil(), struls($prime_depart), $entreprise->nom), $joueur_id);

					else
						$this->bouzouk->historique(53, 52, array(profil(), '', $entreprise->nom), $joueur_id);

					// On envoit une notif
					if ($this->bouzouk->est_connecte($joueur_id))
						$this->bouzouk->notification(53, array(profil()), $joueur_id);

					// Message
					$message  = "	Bonjour $pseudo\n\n";
					$message .= "Nous avons le regret de t'annoncer que tu ne fais désormais plus partie de notre entreprise.\n";
					$message .= "J'espère que tu comprendras cette solution radicale mais nécessaire...\n\n";
					$message .= ($prime_depart > 0) ? "Ta prime d'incompétence de ".struls($prime_depart)." t'as été versée (grrr...).\n\n" :
													  "Vu que ta prime d'incompétence était de 0 strul, tu n'emportes rien avec toi !\n\n";
					$message .= "	Amicalement, ".profil().", le patron de <span class='pourpre'>$entreprise->nom</span>";

					$data_missives[] = array(
						'expediteur_id'   => Bouzouk::Robot_Emploi,
						'destinataire_id' => $joueur_id,
						'date_envoi'      => $date,
						'timbre'          => $timbre,
						'objet'           => 'Tu as été viré !',
						'message'         => $message
					);

					// On ajoute à l'historique du patron
					if ($prime_depart > 0)
						$this->bouzouk->historique(54, null, array(profil($joueur_id, $pseudo), struls($prime_depart)));

					else
						$this->bouzouk->historique(55, null, array(profil($joueur_id, $pseudo)));
				}
				$this->db->insert_batch('missives', $data_missives);

				// On retire les primes de départ à l'entreprise
				$this->db->set('struls', 'struls-'.$total_primes, false)
						 ->where('id', $this->session->userdata('entreprise_id'))
						 ->update('entreprises');
			}
		}

		$query = $this->db->select('id, nom, experience')
						  ->from('jobs')
						  ->get();
		$jobs = $query->result();

		// ---------- Hook clans ----------
		$actions            = $this->get_clans_actions();
		$pression_syndicale = $actions[0];
		$greve_entreprise   = $actions[1];
		$soutien_salarial   = $actions[2];
		$greve_generale     = $actions[3];

		// On parcourt tous les employés envoyés en post
		foreach ($this->input->post('ids') as $id)
		{
			// Si le joueur vient de se faire virer, on passe
			if ($virer !== false AND isset($virer[$id]))
				continue;

			// On va chercher les infos de cet employé
			$query = $this->db->select('e.entreprise_id, e.job_id, e.salaire, e.payer, e.anciennete, e.date_embauche, j.id, j.pseudo, j.experience, j.statut')
							  ->from('employes e')
							  ->join('joueurs j', 'j.id = e.joueur_id')
							  ->where('e.joueur_id', $id)
							  ->where('e.entreprise_id', $this->session->userdata('entreprise_id'))
							  ->get();

			// Si l'employé n'existe pas, on passe
			if ($query->num_rows() == 0)
				continue;

			$data_employes  = array();
			$job_nom        = '';
			$employe = $query->row();

			// Si le job est modifié
			if (isset($job_ids[$id]) AND $job_ids[$id] != $employe->job_id)
			{
				// On vérifie que le job existe et qu'il est autorisé pour ce joueur
				$job_valide = false;
				foreach ($jobs as $job)
				{
					if ($job->id == $job_ids[$id])
					{
						if ($employe->experience + $employe->anciennete >= $job->experience)
						{
							$job_valide = true;
							$job_nom    = $job->nom;
						}
						break;
					}
				}

				// Si invalide
				if ( ! $job_valide)
				{
					$erreurs_pseudos .= '&nbsp;&nbsp;&nbsp;&nbsp;'.profil($employe->id, $employe->pseudo)." => Ce job n'est pas possible pour lui<br>";
					continue;
				}

				// Sinon on ajoute le job à la liste des changements
				$data_employes['job_id'] = $job_ids[$id];
			}

			// Si le salaire est modifié
			if (isset($salaires[$id]) AND $salaires[$id] != $employe->salaire)
			{
				// ---------- Hook clans ----------
				// Si c'est une baisse de salaire, on regarde si il y a une pression syndicale ou un soutien salarial
				if ($salaires[$id] < $employe->salaire)
				{
					if (isset($pression_syndicale))
					{
						$erreurs_pseudos .= '&nbsp;&nbsp;&nbsp;&nbsp;'.profil($employe->id, $employe->pseudo)." => ".$pression_syndicale->nom_clan.' a lancé une '.couleur($pression_syndicale->nom_action).', tu ne peux pas baisser les salaires<br>';
						continue;
					}

					else if (isset($soutien_salarial) && $soutien_salarial->parametres['joueur_id'] == $employe->id)
					{
						$erreurs_pseudos .= '&nbsp;&nbsp;&nbsp;&nbsp;'.profil($employe->id, $employe->pseudo)." => ".$soutien_salarial->nom_clan.' a lancé un '.couleur($soutien_salarial->nom_action).' sur cet employé, tu ne peux pas baisser son salaire<br>';
						continue;
					}
				}

				// On vérifie que le salaire est valide
				if ( ! entier_naturel($salaires[$id]) OR $salaires[$id] > $this->bouzouk->config('entreprises_salaire_max_employe'))
				{
					$erreurs_pseudos .= '&nbsp;&nbsp;&nbsp;&nbsp;'.profil($employe->id, $employe->pseudo)." => Le salaire proposé est invalide (".struls($this->bouzouk->config('entreprises_salaire_max_employe'))." maximum)<br>";
					continue;
				}

				// On ajoute le salaire à la liste des changements
				$data_employes['salaire'] = $salaires[$id];
			}

			// Si "payer" est modifié
			if ((isset($payer[$id]) AND $employe->payer == 0) OR ( ! isset($payer[$id]) AND $employe->payer == 1))
			{
				// ---------- Hook clans ----------
				// Si c'est un décochage de la case "payer", on regarde si il y a une pression syndicale ou un soutien salarial
				if ( ! isset($payer[$id]) AND $employe->payer == 1)
				{
					if (isset($pression_syndicale))
					{
						$erreurs_pseudos .= '&nbsp;&nbsp;&nbsp;&nbsp;'.profil($employe->id, $employe->pseudo)." => ".$pression_syndicale->nom_clan.' a lancé une '.couleur($pression_syndicale->nom_action).', tu ne peux pas enlever les salaire<br>';
						continue;
					}

					else if (isset($soutien_salarial) && $soutien_salarial->parametres['joueur_id'] == $employe->id)
					{
						$erreurs_pseudos .= '&nbsp;&nbsp;&nbsp;&nbsp;'.profil($employe->id, $employe->pseudo)." => ".$pression_syndicale->nom_clan.' a lancé un '.couleur($soutien_salarial->nom_action).' sur cet employé, tu ne peux pas enlever son salaire<br>';
						continue;
					}
				}

				$data_employes['payer'] = isset($payer[$id]) ? 1 : 0;
			}

			// Si des changements ont été effectués, on met à jour la base de données
			if (count($data_employes) > 0)
			{
				// Si l'employé a été embauché il y a moins de 24 heures
				if (strtotime(bdd_datetime()) < strtotime($employe->date_embauche.'+'.$this->bouzouk->config('entreprises_attente_embauche').' HOUR'))
				{
					$erreurs_pseudos .= '&nbsp;&nbsp;&nbsp;&nbsp;'.profil($employe->id, $employe->pseudo)." => tu dois attendre <span class='pourpre'>".$this->bouzouk->config('entreprises_attente_embauche')."h</span> après l'embauche pour modifier ses infos<br>";
					continue;
				}

				$this->db->where('entreprise_id', $this->session->userdata('entreprise_id'))
						 ->where('joueur_id', $id)
						 ->update('employes', $data_employes);

				$nb_joueurs_modifies++;

				// On ajoute à l'historique de l'employé

				// Salaire
				if (isset($data_employes['salaire']))
				{
					$this->bouzouk->historique(56, null, array(profil(), struls($salaires[$id])), $id);

					// On envoit une notif
					if ($this->bouzouk->est_connecte($id))
						$this->bouzouk->notification(56, array(profil(), struls($salaires[$id])), $id);
				}

				// Job
				if (isset($data_employes['job_id']))
				{
					$this->bouzouk->historique(57, 58, array(profil(), $job_nom), $id);

					// On envoit une notif
					if ($this->bouzouk->est_connecte($id))
						$this->bouzouk->notification(57, array(profil(), $job_nom), $id);
				}

				// Payer
				if (isset($data_employes['payer']))
				{
					if ($data_employes['payer'] == 1)
					{
						$this->bouzouk->historique(59, null, array(profil()), $id);

						// On envoit une notif
						if ($this->bouzouk->est_connecte($id))
							$this->bouzouk->notification(59, array(profil()), $id);
					}

					else
					{
						$this->bouzouk->historique(60, null, array(profil()), $id);

						// On envoit une notif
						if ($this->bouzouk->est_connecte($id))
							$this->bouzouk->notification(60, array(profil()), $id);
					}
				}
			}
		}

		// On affiche un message
		if (isset($erreurs_pseudos) AND $erreurs_pseudos != '')
		{
			if ($nb_joueurs_modifies == 0)
				$this->echec("Aucune modification n'a été faite car il y a eu des erreurs pour les employés suivants :<br><br>".$erreurs_pseudos);

			else
				$this->attention('Les modifications ont bien été effectuées mais il y a eu des erreurs pour les employés suivants :<br><br>'.$erreurs_pseudos);
		}

		else if ($nb_joueurs_modifies == 0)
			$this->echec('Ben il faut modifier quelque chose avant de cliquer sur le bouton !');

		else
			$this->succes('Les modifications de tes employés ont bien été effectuées');
			
		return $this->gerer();
	}

	public function modifier_messages()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('message_1', "Le premier communiqué", 'max_length[120]');
		$this->form_validation->set_rules('message_2', "Le deuxième communiqué", 'max_length[120]');

		if ( ! $this->form_validation->run())
		{
			return $this->gerer();
		}

		// On met à jour les messages
		$data_entreprises = array(
			'message_1' => $this->input->post('message_1'),
			'message_2' => $this->input->post('message_2'),
		);
		$this->db->where('id', $this->session->userdata('entreprise_id'))
				 ->update('entreprises', $data_entreprises);

		// On affiche un message de succès
		$this->succes('Tes changements de communiqués ont bien été publiés');
		return $this->gerer();
	}

	public function injecter_struls()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('montant', "Le montant de l'injection", 'required|is_natural_no_zero|less_than_or_equal['.$this->bouzouk->config('entreprises_max_injection').']');

		if ( ! $this->form_validation->run())
		{
			return $this->gerer();
		}

		// On vérifie que le joueur a cette somme
		if ($this->session->userdata('struls') < $this->input->post('montant'))
		{
			$this->echec("Tu n'as pas assez de struls pour injecter ".struls($this->input->post('montant')));
			return $this->gerer();
		}

		// On vérifie que le patron n'a pas atteint la limite de dons
		$this->load->library('lib_entreprise');
		$don_possible = $this->lib_entreprise->don_possible($this->input->post('montant'));

		if ($don_possible['limite_atteinte'])
		{
			$this->echec("Tu as déjà injecté ".struls($don_possible['total_dons'])." dans ton entreprise, cette injection de ".struls($this->input->post('montant'))." te ferait dépasser la limite de ".struls($don_possible['max_injection'])." en <span class='pourpre'>".$don_possible['intervalle']." heures</span>.");
			return $this->gerer();
		}

		// On retire la somme au joueur
		$this->bouzouk->retirer_struls($this->input->post('montant'));

		// On ajoute la somme à l'entreprise
		$this->db->set('struls', 'struls+'.$this->input->post('montant'), false)
				 ->where('id', $this->session->userdata('entreprise_id'))
				 ->update('entreprises');

		// On enregistre la transaction
		// Attention : ici j'utilise le joueur_id comme entreprise_id (ce champ n'est pas réutilisé dans le cas d'une entreprise)
		$data_donations = array(
			'donateur_id' => $this->session->userdata('id'),
			'joueur_id'   => $this->session->userdata('entreprise_id'),
			'montant'     => $this->input->post('montant'),
			'date'        => bdd_datetime(),
			'type'        => Bouzouk::Donation_Entreprise
		);
		$this->db->insert('donations', $data_donations);

		// On ajoute à l'historique
		$this->bouzouk->historique(61, null, array(struls($this->input->post('montant'))));

		// On affiche un message de confirmation
		$this->succes('Tu as injecté '.struls($this->input->post('montant')).' dans ton entreprise');
		return $this->gerer();
	}

	public function modifier_salaire()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('salaire', 'Le salaire', 'required|is_natural|less_than_or_equal['.$this->bouzouk->config('entreprises_salaire_max_patron').']');

		if ( ! $this->form_validation->run())
		{
			return $this->gerer();
		}

		// On modifie le pourcentage de salaire
		$data_entreprises = array(
			'salaire_chef' => $this->input->post('salaire')
		);
		$this->db->where('id', $this->session->userdata('entreprise_id'))
				 ->update('entreprises', $data_entreprises);

		// On ajoute à l'historique
		$this->bouzouk->historique(62, null, array(struls($this->input->post('salaire'))));

		// On affiche un message de succès
		$this->succes('Tu as modifié ton salaire de patron à '.struls($this->input->post('salaire')));
		return $this->gerer();
	}

	public function demissionner()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('demissionner', "L'appui sur le bouton <span class='pourpre'>Démissionner</span>", 'required');

		if ( ! $this->form_validation->run())
		{
			return $this->gerer();
		}

		$this->load->library('lib_entreprise');
		$this->lib_entreprise->demission($this->session->userdata('entreprise_id'));

		// On affiche un message de succès
		$message_repreneur = '';
		$query = $this->db->select('j.id, j.pseudo')
						  ->from('entreprises e')
						  ->join('joueurs j', 'j.id = e.chef_id')
						  ->where('e.id', $this->session->userdata('entreprise_id'))
						  ->get();
		if ($query->num_rows() > 0)
		{
			$repreneur = $query->row();
			$message_repreneur = ', '.profil($repreneur->id, $repreneur->pseudo).' est le nouveau patron';
		}

		$this->succes("Tu as démissionné de ton entreprise, tu perds <span class='pourpre'>-".$this->bouzouk->config('entreprises_perte_xp_demission')." xp</span>$message_repreneur et tu retournes maintenant parmis les valeureux chômeurs :)");
		redirect('joueur');
	}

	public function changer_nom()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('nom', "Le nom de l'entreprise", 'required|min_length[3]|max_length[20]|regex_match[#^[a-z0-9 .\'-]+$#i]|is_unique[entreprises.nom]|is_unique[classement_entreprises.nom_entreprise]');

		if ( ! $this->form_validation->run())
		{
			return $this->gerer();
		}

		// On vérifie que l'entreprise peut dépenser les struls
		$this->load->library('lib_entreprise');
		
		if ( ! $this->lib_entreprise->fonds_suffisants($this->session->userdata('entreprise_id'), $this->bouzouk->config('entreprises_prix_changer_nom')))
		{
			$this->echec("Les fonds de l'entreprise ne sont pas suffisants pour effectuer le changement de nom");
			return $this->gerer();
		}

		// On retire les struls
		$this->db->set('struls', 'struls-'.$this->bouzouk->config('entreprises_prix_changer_nom'), false)
				 ->where('id', $this->session->userdata('entreprise_id'))
				 ->update('entreprises');

		// On change le nom
		$this->db->set('nom', $this->input->post('nom'))
				 ->where('id', $this->session->userdata('entreprise_id'))
				 ->update('entreprises');

		// On affiche une confirmation
		$this->succes("Le nom de l'entreprise a bien été changé");
		return $this->gerer();
	}

	public function modifier_infos()
	{
		$syndicats_autorises = $this->input->post('syndicat') != false ? '1' : '0';
		$historique_publique = $this->input->post('publique') != false ? '1' : '0';

		// On va chercher les infos de l'entreprise
		$query = $this->db->select('syndicats_autorises')
						  ->from('entreprises')
						  ->where('id', $this->session->userdata('entreprise_id'))
						  ->get();
		$entreprise = $query->row();

		// Si le syndicat est changé en non, on vérifie qu'il n'y a pas de syndicat de créé
		if ($entreprise->syndicats_autorises && ! $syndicats_autorises)
		{
			$existe = $this->db->where('entreprise_id', $this->session->userdata('entreprise_id'))
							   ->where('type', Bouzouk::Clans_TypeSyndicat)
							   ->count_all_results('clans');

			if ($existe)
			{
				$this->echec('Il existe <span class="pourpre">'.pluriel($existe, 'syndicat')."</span> dans ton entreprise, tu dois attendre qu'il n'en existe plus pour pouvoir désactiver les syndicats");
				return $this->gerer();
			}
		}

		// On modifie
		$this->db->set('historique_publique', $historique_publique)
				 ->set('syndicats_autorises', $syndicats_autorises)
				 ->where('id', $this->session->userdata('entreprise_id'))
				 ->update('entreprises');

		// Si les syndicat son changé, il faut mettre à jour les sessions de tous les employés
		if ($entreprise->syndicats_autorises != $syndicats_autorises)
		{
			// On récupère la liste des employés
			$query = $this->db->select('joueur_id')
						 ->from('employes')
						 ->where('entreprise_id', $this->session->userdata('entreprise_id'))
						 ->get();
			$employes = $query->result();

			// Pour chaque employé, on met à jour la session
			foreach ($employes as $employe)
				$this->bouzouk->augmente_version_session($employe->joueur_id);
		}

		// On affiche une confirmation
		$this->succes("Les infos de l'entreprise ont bien été changées");
		return $this->gerer();
	}
	    public function changer_produit()
	{		// Règles de validation
		$this->load->library('form_validation');
		// On va chercher tous les objets possibles pour la création d'entreprises
		$query = $this->db->select('o.id, o.nom, o.type, o.image_url, COUNT(e.id) AS nb_entreprises')
						  ->from('objets o')
						  ->join('entreprises e', 'e.objet_id = o.id', 'left')
						  ->where('o.disponibilite', 'entreprise')
						  ->group_by('o.id')
						  ->get();
		$objets = array();

		// On créé un array
		foreach ($query->result() as $objet)
		{
			if ( ! isset($objets[$objet->type]))
			{
				$objets[$objet->type] = array($objet);
			}

			else
			{
				$objets[$objet->type][] = $objet;
			}
		}
		$this->form_validation->set_rules('objet_id', "Le type de production", 'required|is_natural_no_zero');
		if ( ! $this->form_validation->run())
		{
			return $this->gerer();
		}
		// On vérifie que l'objet existe
		$query = $this->db->select('nom, disponibilite')
						  ->from('objets')
						  ->where('id', $this->input->post('objet_id'))
						  ->get();
		if ($query->num_rows() == 0)
		{
			$this->echec("Cet objet n'existe pas");
			return $this->layout->view('entreprises/creer', $vars);
		}
		// On vérifie que l'objet est disponible pour les entreprises
		$objet = $query->row();

		if ($objet->disponibilite != 'entreprise')
		{
			$this->echec("Cet objet n'est pas disponible pour les entreprises");
			return $this->layout->view('entreprises/creer', $vars);
		}
		// On vérifie que l'entreprise peut dépenser les struls
		$this->load->library('lib_entreprise');
		
		if ( ! $this->lib_entreprise->fonds_suffisants($this->session->userdata('entreprise_id'), $this->bouzouk->config('entreprises_prix_changer_nom')))
		{
			$this->echec("Les fonds de l'entreprise ne sont pas suffisants pour effectuer le changement de nom");
			return $this->gerer();
		}
		// On vérifie que le joueur n'a pas fait changement il y a quelques temps
		$changement = $this->db->where('entreprise_id', $this->session->userdata('entreprise_id'))
							 ->count_all_results('changement_produit');
		if ($changement > 0)
		{
			$this->echec('L\'entreprise a changée de produit il y a moins de '.$this->bouzouk->config('entreprises_duree_changement_produit').' jours, tu dois attendre ce délai avant de pouvoir re-changer de produit');
			return $this->gerer();
		}
		// Tout les test on été validé on passe à la modification
		$data_changement = array(
			'entreprise_id'     => $this->session->userdata('entreprise_id'),
			'date_changement' => bdd_datetime()
			);
		// On ajoute l'entreprise aux changement de produit pour l'empêcher de re-changer tout de suite
		$this->db->insert('changement_produit', $data_changement);
		// On retire les struls
		$this->db->set('struls', 'struls-'.$this->bouzouk->config('entreprises_prix_changer_nom'), false)
				 ->where('id', $this->session->userdata('entreprise_id'))
				 ->update('entreprises');
		// On affiche une confirmation
		$this->succes("Le produit de l'entreprise a bien été changé");
        $this->db->set('objet_id', $this->input->post('objet_id'))
				 ->where('id', $this->session->userdata('entreprise_id'))
				 ->update('entreprises');
		return $this->gerer();
        }
}
	