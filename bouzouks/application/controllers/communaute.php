<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions communautaires du jeu : profil, contacts, rumeurs...
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Communaute extends MY_Controller
{
	public function rumeurs()
	{
		// Le joueur doit avoir assez d'expérience
		$xp_rumeur = $this->bouzouk->config('communaute_xp_proposer_rumeur');

		if ($this->session->userdata('experience') < $xp_rumeur)
		{
			$this->echec("Tu dois avoir au moins <span class='pourpre'>$xp_rumeur xp</span> pour pouvoir poster une rumeur");
			return $this->layout->view('message', array('titre' => 'Poster une rumeur'));
		}

		// On va chercher les rumeurs du joueur
		$query = $this->db->select('date, texte, statut')
						  ->from('rumeurs')
						  ->where('auteur_id', $this->session->userdata('id'))
						  ->order_by('date', 'desc')
						  ->get();
		$rumeurs = $query->result();

		$statuts = array(
			Bouzouk::Rumeur_EnAttente  => couleur('En attente', 'pourpre'),
			Bouzouk::Rumeur_Refusee    => couleur('Refusée', 'rouge'),
			Bouzouk::Rumeur_Validee    => couleur('Acceptée', 'vert'),
			Bouzouk::Rumeur_Desactivee => 'Acceptée'
		);

		$vars = array(
			'rumeurs' => $rumeurs,
			'statuts' => $statuts
		);

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('rumeur', 'La rumeur', 'required|min_length[15]|max_length[100]');

		if ( ! $this->form_validation->run())
		{
			return $this->layout->view('communaute/rumeurs', $vars);
		}

		// Le joueur doit avoir assez d'argent
		$prix_rumeur = $this->bouzouk->config('communaute_prix_rumeur');

		if ($this->session->userdata('struls') < $prix_rumeur)
		{
			$this->echec("Tu n'as pas assez de struls pour payer l'envoi d'une rumeur (prix : <span class='pourpre'>".struls($prix_rumeur).'</span>)');
			return $this->layout->view('communaute/rumeurs', $vars);
		}

		// On récupère le nombre de rumeurs en attente du joueur
		$nb_en_attente = $this->db->where('auteur_id', $this->session->userdata('id'))
								  ->where('statut', Bouzouk::Rumeur_EnAttente)
								  ->count_all_results('rumeurs');

		// Le joueur ne doit pas avoir trop de rumeurs en attente
		if ($nb_en_attente >= $this->bouzouk->config('communaute_max_rumeurs_attente'))
		{
			$this->echec("Tu as déjà <span class='pourpre'>$nb_en_attente</span> rumeurs en attente de validation, tu ne peux plus poster de rumeurs pour le moment");
			return $this->layout->view('communaute/rumeurs', $vars);
		}

		// On récupère le nombre de rumeurs refusees du joueur
		$delai_rumeurs_refusees = $this->bouzouk->config('communaute_delai_rumeurs_refusees');

		$nb_refusees = $this->db->where('auteur_id', $this->session->userdata('id'))
								->where('statut', Bouzouk::Rumeur_Refusee)
								->where('date >= (NOW() - INTERVAL '.$delai_rumeurs_refusees.' DAY)')
								->count_all_results('rumeurs');

		// Le joueur ne doit pas avoir trop de rumeurs refusées dans un court laps de temps
		if ($nb_refusees >= $this->bouzouk->config('communaute_max_rumeurs_refusees'))
		{
			$this->echec("Tu as déjà <span class='pourpre'>$nb_refusees rumeurs</span> refusées ces derniers temps, tu ne peux plus en proposer pour le moment");
			return $this->layout->view('communaute/rumeurs', $vars);
		}

		// On insère la rumeur en base
		$data_rumeur = array(
			'auteur_id' => $this->session->userdata('id'),
			'texte'     => $this->input->post('rumeur'),
			'date'      => bdd_datetime(),
			'statut'    => Bouzouk::Rumeur_EnAttente
		);
		$this->db->insert('rumeurs', $data_rumeur);

		// On débite le prix d'une rumeur au joueur
		$this->bouzouk->retirer_struls($prix_rumeur);

		// On ajoute à l'historique
		$this->bouzouk->historique(45, null, array(form_prep($this->input->post('rumeur')), struls($prix_rumeur)));

		// On affiche un message de confirmation
		$this->succes("Ta rumeur a bien été postée, tu perds <span class='pourpre'>-".struls($prix_rumeur)."</span> et tu dois attendre qu'un modérateur la valide pour qu'elle apparaisse sur le téléscripteur");
		redirect('communaute/rumeurs'); // on redirige pour recharger les rumeurs du joueur dans le tableau
	}

	public function profil($joueur_id)
	{
		// joueur_id doit être valide
		if ( ! entier_naturel_positif($joueur_id))
			show_404();
		
		$this->load->library('lib_parser');

		// On va chercher les infos du joueur
		$query = $this->db->select('j.id, j.pseudo, j.rang, j.date_inscription, j.commentaire, j.adresse, j.statut, j.date_statut, j.raison_statut, j.rang_description, j.perso, j.experience, j.connecte, j.parrain_id,
									j.plouk_stats, j.sexe, j.utiliser_avatar_toboz, j.date_de_naissance, j.mot_de_passe, j.email, j.ip_inscription, m.maire_id AS maire, e.chef_id AS patron, em.joueur_id AS employe, me.id AS mendiant')
						  ->from('joueurs j')
						  ->join('mairie m', 'm.maire_id = j.id', 'left')
						  ->join('entreprises e', 'e.chef_id = j.id', 'left')
						  ->join('employes em', 'em.joueur_id = j.id', 'left')
						  ->join('mendiants me', 'me.joueur_id = j.id', 'left')
						  ->where('j.id', $joueur_id)
						  ->where_not_in('j.statut', array(Bouzouk::Joueur_Robot, Bouzouk::Joueur_Inactif))
						  ->get();

		// Si le joueur n'existe pas
		if ($query->num_rows() == 0)
		{
			$this->echec("Ce bouzouk n'existe pas");
			return $this->layout->view('message', array('titre' => 'Profil bouzouk'));
		}

		$this->load->helper('date');

		$joueur                    = $query->row();
		$joueur->plouk_stats       = explode('|', $joueur->plouk_stats);
		$joueur->job               = '';
		$joueur->entreprise        = '';
		$joueur->maire             = isset($joueur->maire) ? couleur('<br>[Maire de la ville]', 'noir') : '';
		$joueur->mendiant          = isset($joueur->mendiant) ? 'oui' : 'non';
		$joueur->jours_inscription = timespan(strtotime($joueur->date_inscription));

		// Si le joueur est patron
		if (isset($joueur->patron))
		{
			$query = $this->db->select('nom')
							  ->from('entreprises')
							  ->where('chef_id', $joueur_id)
							  ->get();
			$entreprise = $query->row();

			$joueur->job        = "<span class='pourpre'>Patron d'entreprise</span>";
			$joueur->entreprise = ' - <i><span class="pourpre">'.$entreprise->nom.'</span></i>';
		}

		else
		{
			// Si le joueur est employé
			if (isset($joueur->employe))
			{
				$query = $this->db->select('en.nom AS entreprise, j.nom AS job')
								  ->from('employes em')
								  ->join('entreprises en', 'en.id = em.entreprise_id')
								  ->join('jobs j', 'j.id = em.job_id')
								  ->where('em.joueur_id', $joueur_id)
								  ->get();
				$entreprise = $query->row();

				$joueur->job = '<i><span class="pourpre">'.$entreprise->job.'</span></i>';
				$joueur->entreprise = ' chez <i><span class="pourpre">'.$entreprise->entreprise.'</span></i>';
			}

			// Le joueur est chômeur
			else
			{
				if (in_array($joueur->statut, array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_ChoixPerso)))
					$joueur->job = 'Etudiant';

				else if ($joueur->statut == Bouzouk::Joueur_GameOver)
					$joueur->job = 'Marche vers des contrées inexplorées';
					
				else if ($joueur->statut == Bouzouk::Joueur_Banni)
					$joueur->job = 'Se repent de ses péchés';

				else
					$joueur->job = 'Chômeur';
			}
		}

		// On regarde si le joueur est chef de clan
		$joueur->clans = array(
			Bouzouk::Clans_TypeSyndicat       => array('<span class="gris">aucun</span>', false),
			Bouzouk::Clans_TypePartiPolitique => array('<span class="gris">aucun</span>', false),
			Bouzouk::Clans_TypeOrganisation   => array('<span class="gris">aucune</span>', false)
		);

		$query = $this->db->select('type, nom, mode_recrutement')
						  ->from('clans')
						  ->where('chef_id', $joueur->id)
						  ->get();

		foreach ($query->result() as $clan)
		{
			if ($clan->mode_recrutement != Bouzouk::Clans_RecrutementInvisible)
				$joueur->clans[$clan->type] = array(form_prep($clan->nom), true);
		}

		// On regarde si le joueur est membre de clans
		$query = $this->db->select('c.type, c.nom, p.invisible, c.mode_recrutement')
						  ->from('politiciens p')
						  ->join('clans c', 'c.id = p.clan_id')
						  ->where('p.joueur_id', $joueur->id)
						  ->get();

		foreach ($query->result() as $clan)
		{
			if ( ! $clan->invisible && $clan->mode_recrutement != Bouzouk::Clans_RecrutementInvisible)
				$joueur->clans[$clan->type][0] = form_prep($clan->nom);
		}

		// Transformations des variables
		// Rang
		if ($this->bouzouk->is_admin(Bouzouk::Rang_Admin, $joueur->rang))
			$joueur->rang = couleur('[Administrateur]', 'rouge');

		else if ($this->bouzouk->is_mdj($joueur->rang))
			$joueur->rang = couleur('[Maître de Jeu]', 'vert_fonce');

		else if ($this->bouzouk->is_moderateur(null, $joueur->rang))
		{
			$ancien_rang = $joueur->rang;

			$joueur->rang = ($joueur->sexe == 'male') ? couleur('[Modérateur]', 'bleu') : couleur('[Modératrice]', 'bleu');

			// Spécial Pincemi
			if ($this->bouzouk->is_journaliste(null, $ancien_rang) && ! $this->bouzouk->is_admin(null, $ancien_rang))
				$joueur->rang .= couleur('[Journaliste]', 'pourpre');
		}
		
		else if ($this->bouzouk->is_journaliste(null, $joueur->rang))
			$joueur->rang = couleur('[Journaliste]', 'pourpre');

		else if ($this->bouzouk->is_beta_testeur($joueur->rang))
			$joueur->rang = ($joueur->sexe == 'male') ? couleur('[Bêta-Testeur]', 'vert') : couleur('[Bêta-Testeuse]', 'vert');

		else
			$joueur->rang = '';

		// Statut
		$array_statuts = array(
			Bouzouk::Joueur_Etudiant   => couleur('Ce bouzouk est encore un petit étudiant', 'orange'),
			Bouzouk::Joueur_ChoixPerso => couleur('Ce bouzouk est encore un petit étudiant', 'orange'),
			Bouzouk::Joueur_Actif      => couleur('Bouzouk actif et en pleine forme', 'vert'),
			Bouzouk::Joueur_Asile      => couleur("Ce bouzouk est à l'asile depuis ".heures_ecoulees($joueur->date_statut), 'rouge'),
			Bouzouk::Joueur_Pause      => couleur('Ce bouzouk est en pause depuis '.jours_ecoules($joueur->date_statut), 'rouge'),
			Bouzouk::Joueur_GameOver   => couleur('Ce bouzouk est parti en quête du Schnibble depuis '.jours_ecoules($joueur->date_statut), 'gris'),
			Bouzouk::Joueur_Banni      => couleur('<span class="gras">Ce joueur est banni depuis '.jours_ecoules($joueur->date_statut).'</span>', 'rouge')
		);
		$joueur->statut_phrase = $array_statuts[$joueur->statut];

		// On recupère le nombre d'amis
		$nb_amis = $this->db->where('joueur_id', $joueur_id)
							->where('etat', Bouzouk::Amis_Accepte)
							->count_all_results('amis');

		$joueur->nb_amis = $nb_amis;

		// On récupère le nombre de filleuls
		$joueur->nb_filleuls = $this->db->where('parrain_id', $joueur_id)
										->where('filleul_valide', 1)
										->count_all_results('joueurs');

		// Pour les admins multicomptes, on affiche beaucoup plus d'infos
		if ($this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurMulticomptes))
		{
			if ($this->bouzouk->is_admin(Bouzouk::Rang_Admin))
			{
				// On va chercher le total des dons Allopass du joueur
				$query = $this->db->select('SUM(montant) AS total_euros')
								  ->from('plus_de_struls')
								  ->where('joueur_id', $joueur_id)
								  ->get();
				$joueur->dons = $query->row();

				// On va chercher le total des dons Paypal du joueur
				$query = $this->db->select('SUM(montant) AS total_euros')
								  ->from('dons_paypal')
								  ->where('joueur_id', $joueur_id)
								  ->get();
				$joueur->dons_paypal = $query->row();
			}

			// On récupère la liste des connexions
			$query = $this->db->select('date, user_agent, ip')
							  ->from('connexions')
							  ->where('joueur_id', $joueur_id)
							  ->group_by('ip')
							  ->get();
			$joueur->ips_connexions = $query->result();

			$joueur_ips = array();

			foreach ($joueur->ips_connexions as $connexion)
				$joueur_ips[] = $connexion->ip;

			$joueur->joueurs_ip_connexion = array();

			// On récupère tous les joueurs ayant eu la même ip de connexion
			if (count($joueur->ips_connexions) > 0)
			{
				$query = $this->db->select('j.id, j.pseudo, j.statut')
								  ->from('connexions c')
								  ->join('joueurs j', 'j.id = c.joueur_id')
								  ->where('c.joueur_id !=', $joueur_id)
								  ->where_in('c.ip', $joueur_ips)
								  ->group_by('j.id')
								  ->get();
				$joueur->joueurs_ip_connexion = $query->result();
			}
			
			// On récupère tous les joueurs ayant le même mot de passe
			$query = $this->db->select('id, pseudo, statut, mot_de_passe, ip_inscription')
							  ->from('joueurs')
							  ->where('id !=', $joueur_id)
							  ->where('(mot_de_passe = "'.$joueur->mot_de_passe.'" OR ip_inscription = "'.$joueur->ip_inscription.'")')
							  ->get();
			$joueur->joueurs_mot_de_passe = array();
			$joueur->joueurs_ip_inscription = array();

			foreach ($query->result() as $j)
			{
				if ($j->mot_de_passe == $joueur->mot_de_passe)
					$joueur->joueurs_mot_de_passe[] = $j;

				if ($j->ip_inscription == $joueur->ip_inscription)
					$joueur->joueurs_ip_inscription[] = $j;
			}

			// On récupère tous les joueurs ayant la même date de naissance
			$query = $this->db->select('id, pseudo, statut')
							  ->from('joueurs')
							  ->where('id !=', $joueur_id)
							  ->where('date_de_naissance', $joueur->date_de_naissance)
							  ->get();
			$joueur->joueurs_date_de_naissance = $query->result();
	
			// On va chercher les transactions basses du marché noir
			$query = $this->db->select('mn.vendeur_id, j1.pseudo AS vendeur_pseudo, mn.acheteur_id, j2.pseudo AS acheteur_pseudo, j1.statut AS vendeur_statut, j2.statut AS acheteur_statut, o.nom, mn.peremption, mn.quantite, mn.prix, mn.date')
							  ->from('mc_marche_noir mn')
							  ->join('joueurs j1', 'j1.id = mn.vendeur_id')
							  ->join('joueurs j2', 'j2.id = mn.acheteur_id')
							  ->join('objets o', 'o.id = mn.objet_id')
							  ->where('vendeur_id', $joueur_id)
							  ->or_where('acheteur_id', $joueur_id)
							  ->order_by('date DESC')
							  ->get();
			$joueur->ventes = $query->result();

			// On va chercher les parties de Plouk
			$query = $this->db->select('mp.*, j1.pseudo AS createur_pseudo, j2.pseudo AS adversaire_pseudo, o.nom AS objet')
							  ->from('mc_plouk mp')
							  ->join('joueurs j1', 'j1.id = mp.createur_id')
							  ->join('joueurs j2', 'j2.id = mp.adversaire_id')
							  ->join('objets o', 'o.id = mp.objet_id', 'left')
							  ->where('mp.createur_id', $joueur_id)
							  ->or_where('mp.adversaire_id', $joueur_id)
							  ->order_by('mp.date_debut desc')
							  ->get();
			$joueur->parties = $query->result();

			// On récupère la liste des filleuls
			$query = $this->db->select('id, pseudo, statut, filleul_valide')
							  ->from('joueurs')
							  ->where('parrain_id', $joueur_id)
							  ->order_by('pseudo')
							  ->get();
			$joueur->filleuls = $query->result();

			// On récupère le parrain
			if (isset($joueur->parrain_id))
			{
				$query = $this->db->select('id, pseudo, statut')
							 	  ->from('joueurs')
							  	  ->where('id', $joueur->parrain_id)
							  	  ->get();
				$joueur->parrain = $query->row();
			}
		}
		
		
		// On affiche le profil
		$vars = array(
			'joueur' => $joueur
		);
		return $this->layout->view('communaute/profil', $vars);
	}

	public function classements()
	{
		// On va chercher le classement des entreprises
		$query = $this->db->select('nom_entreprise, chef_id, nom_chef, score, position, evolution')
						  ->from('classement_entreprises')
						  ->order_by('position')
						  ->limit(5)
						  ->get();
		$entreprises = $query->result();

		$classements = $this->lib_cache->fetch('classements');

		if ( ! $classements)
		{
			$classements = array();
			
			$types = array(
				Bouzouk::Classement_Richesse   => 'richesse',
				Bouzouk::Classement_Experience => 'experience',
				Bouzouk::Classement_Fortune    => 'fortune',
				Bouzouk::Classement_Collection => 'collection',
				Bouzouk::Classement_Plouk      => 'plouk'
			);

			foreach ($types as $type => $nom)
			{
				// On récupère le classement du type
				$query = $this->db->select('joueur_id, pseudo, type, position, valeur, sexe, evolution')
								->from('classement_joueurs')
								->where('type', $type)
								->order_by('position')
								->limit(5)
								->get();
				$classements[$type] = $query->result();
			}

			// Les plus mauvais au Plouk
			$query = $this->db->select('joueur_id, pseudo, type, position, valeur, sexe, evolution')
							->from('classement_joueurs')
							->where('type', Bouzouk::Classement_Plouk)
							->order_by('position', 'desc')
							->limit(5)
							->get();
			$classement_plouk_mauvais = $query->result();
			$nb = $classement_plouk_mauvais[0]->position + 1;

			foreach ($classement_plouk_mauvais as &$classement)
			{
				$classement->position = $nb - $classement->position;
				$classement->type = Bouzouk::Classement_PloukMauvais;
			}
			unset($classement);

			$classements[Bouzouk::Classement_PloukMauvais] = $classement_plouk_mauvais;
			$this->lib_cache->store('classements', $classements, 60 * 60);
		}
				
		// On récupère le profil du maire
		$query = $this->db->select('j.id, j.pseudo')
						  ->from('mairie m')
						  ->join('joueurs j', 'j.id = m.maire_id')
						  ->get();
		$mairie = $query->row();
		
		// On affiche
		$vars = array(
			'entreprises'        => $entreprises,
			'classements'        => $classements,
			'classements_joueur' => $this->session->userdata('classements'),
			'mairie'             => $mairie
		);
		return $this->layout->view('communaute/classements', $vars);
	}

	public function classements_elections($offset = '0')
	{
		// Pagination
		$query = $this->db->select('DISTINCT(date)')
						  ->from('classement_elections')
						  ->order_by('date', 'desc')
						  ->get();
		$nb_elections = $query->num_rows();
		$elections = $query->result();
		$pagination = creer_pagination('communaute/classements_elections', $nb_elections, 1, $offset);

		if ($nb_elections > 0)
			$date_offset = $elections[$offset]->date;

		else
			$date_offset = bdd_datetime();

		// On va chercher le classement des éléctions demandé
		$query = $this->db->select('c_e.position, c_e.tour, c_e.votes_tour1, c_e.votes_tour2, c_e.votes_tour3, c_e.pourcentage_tour1, c_e.pourcentage_tour2, c_e.pourcentage_tour3, j.id, j.pseudo')
						  ->from('classement_elections c_e')
						  ->join('joueurs j', 'j.id = c_e.joueur_id')
						  ->where('date', $date_offset)
						  ->order_by('position')
						  ->get();
		$candidats = $query->result();

		$vars = array(
			'date'       => $date_offset,
			'candidats'  => $candidats,
			'pagination' => $pagination['liens']
		);
		$this->layout->view('communaute/classements_elections', $vars);
	}

	public function connectes()
	{
		$joueurs = $this->lib_cache->liste_connectes();

		// On affiche le résultat
		$vars = array(
			'joueurs' => $joueurs
		);
		return $this->layout->view('communaute/connectes', $vars);
	}

	public function lister_bouzouks($lettre = '%', $recherche = null)
	{
		// Les admins ont plein de vues possibles
		if ($this->bouzouk->is_admin() && ! preg_match('#^([a-zA-Z%]|tous|robots|inactifs|etudiants|asile|pause|game_over|bannis|beta-testeurs)$#', $lettre))
			show_404();
		
		// Les autres n'ont pas grand chose (ahah)
		else if ( ! $this->bouzouk->is_admin() && ! preg_match('#^([a-zA-Z%]|tous)$#', $lettre))
			show_404();
		
		// Recherche par première lettre
		if ( ! isset($recherche))
		{
			$this->db->select('id, pseudo, rang')
					->from('joueurs');

			// Si on demande une lettre
			if (mb_strlen($lettre) == 1)
			{
				$this->db->where_in('statut', array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))
						->like('pseudo', $lettre, 'after');
			}

			else
			{
				if ( ! $this->bouzouk->is_admin())
					$this->db->where_in('statut', array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause));

				else
				{
					if ($lettre == 'tous')
						$this->db->where_in('statut', array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause));

					else if ($lettre == 'beta-testeurs')
					{
						$this->db->where('(rang & '.($this->bouzouk->get_masque(Bouzouk::Masque_Admin | Bouzouk::Masque_Moderateur | Bouzouk::Masque_Journaliste) | Bouzouk::Rang_BetaTesteur).') > 0')
								 ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause));
					}

					else
					{
						$statuts = array(
							'robots'    => Bouzouk::Joueur_Robot,
							'inactifs'  => Bouzouk::Joueur_Inactif,
							'etudiants' => Bouzouk::Joueur_Etudiant,
							'asile'     => Bouzouk::Joueur_Asile,
							'pause'     => Bouzouk::Joueur_Pause,
							'game_over' => Bouzouk::Joueur_GameOver,
							'bannis'    => Bouzouk::Joueur_Banni
						);
						$this->db->where('statut', $statuts[$lettre]);
					}
				}
			}
		}

		// Recherche par nom
		else
		{
			$this->db->select('id, pseudo, rang')
					 ->from('joueurs')
					 ->like('pseudo', $recherche, 'both');

			if ( ! $this->bouzouk->is_admin())
				$this->db->where_in('statut', array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause));
		}

		$query = $this->db->order_by('pseudo')
						  ->get();
		$joueurs = $query->result();
		
		// On affiche les résultats
		$vars = array(
			'joueurs'   => $joueurs,
			'filtre'    => $lettre,
			'recherche' => $recherche
		);
		return $this->layout->view('communaute/lister_bouzouks', $vars);
	}

	public function lister_entreprises($offset = '0')
	{
		// Pagination
		$nb_entreprises = $this->db->count_all('classement_entreprises');
		$pagination = creer_pagination('communaute/lister_entreprises', $nb_entreprises, 10, $offset);

		$query = $this->db->select('j.id AS chef_id, j.pseudo, j.rang, j.statut, j.faim, j.sante, j.stress, j.perso, o.nom AS objet_nom, o.image_url, e.nom, e.date_creation, c_e.score, c_e.position')
						  ->from('classement_entreprises c_e')
						  ->join('entreprises e', 'e.id = c_e.entreprise_id')
						  ->join('objets o', 'o.id = e.objet_id')
						  ->join('joueurs j', 'j.id = e.chef_id')
						  ->order_by('c_e.position')
						  ->limit($pagination['par_page'], $pagination['offset'])
						  ->get();
		$entreprises = $query->result();

		// On affiche les résultats
		$vars = array(
			'entreprises' => $entreprises,
			'pagination'  => $pagination['liens']
		);
		$this->layout->view('communaute/lister_entreprises', $vars);
	}

	public function recherche_bouzouks()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('nom', 'Le pseudo', 'required|min_length[2]|max_length[12]|alpha_dash');

		if ( ! $this->form_validation->run())
		{
			return $this->lister_bouzouks();
		}

		return $this->lister_bouzouks('%', $this->input->post('nom'));
	}
}
