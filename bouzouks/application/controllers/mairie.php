<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : affichage et gestion de la mairie du jeu
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Mairie extends MY_Controller
{
	public $map;

	public function __construct()
	{
		parent::__construct();
		$this->load->library('lib_mairie');
		$this->load->library('lib_missive');
	}
	
	public function index($offset = '0')
	{
		// On va chercher les infos de la mairie
		$query = $this->db->select('m.maire_id, j.pseudo AS maire_pseudo, j.perso AS maire_perso, j.utiliser_avatar_toboz, m.struls, m.impots_employes, m.impots_faim, m.impots_sante, m.impots_stress, m.impots_lohtoh, m.salaire_maire, m.aide_entreprise, m.aide_chomage,
									m.date_prochain_achat, m.date_prochain_impot, m.date_debut_election, m.cacher_salaire, m.tricher_elections, m.coefficients_achats,bonus_entreprise,malus_entreprise')
						  ->from('mairie m')
						  ->join('joueurs j', 'j.id = m.maire_id')
						  ->get();
		$mairie = $query->row();
		$mairie->coefficients_achats = explode('|', $mairie->coefficients_achats);

		$query = $this->db->select('m.maire_suppleant_id, j.pseudo')
						  ->from('mairie m')
						  ->join('joueurs j', 'j.id = m.maire_suppleant_id')
						  ->get();
		$maire_suppleant = $query->row();
		$mairie->maire_suppleant_id = $maire_suppleant->maire_suppleant_id;
		$mairie->maire_suppleant_pseudo = $maire_suppleant->pseudo;

		// On va chercher les taxes surprises distribuées
		$query = $this->db->select('t_s.maire_id, j.pseudo, t_s.taux, t_s.date_taxe, t_s.raison')
						  ->from('taxes_surprises t_s')
						  ->join('joueurs j', 'j.id = t_s.maire_id')
						  ->where('t_s.distribuee', 1)
						  ->order_by('t_s.date_taxe', 'desc')
						  ->limit(5)
						  ->get();
		$taxes = $query->result();

		// On va chercher la liste des dons
		$historique = array();

		// On va chercher l'historique de la mairie
		if ($this->bouzouk->is_journaliste(Bouzouk::Rang_Journaliste) || $this->bouzouk->is_maire())
		{
			// Pagination
			$nb_historique = $this->db->count_all('historique_mairie');
			$pagination = creer_pagination('mairie/index', $nb_historique, 20, $offset);

			$this->db->select('j.id, j.pseudo, h.date, h.texte, h.visible_journalistes')
					 ->from('historique_mairie h')
					 ->join('joueurs j', 'j.id = h.maire_id');

			// ---------- Hook clans ----------
			// Magouille (parti Politique)
			// Les journalistes ne peuvent pas voir les actions cachées par la magouille
			if ( ! $this->bouzouk->is_admin())
				$this->db->where('(visible_journalistes = 1 OR maire_id = '.$this->session->userdata('id').')');

			$query = $this->db->order_by('h.date', 'desc')
							  ->order_by('h.id', 'desc')
							  ->limit($pagination['par_page'], $pagination['offset'])
							  ->get();
			$historique = $query->result();
		}

		// On calcule le taux de chômage
		$nb_employes = $this->db->from('employes e')
								->join('joueurs j', 'j.id = e.joueur_id')
								->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile))
								->count_all_results('');
		$nb_patrons = $this->db->count_all('entreprises');
		$nb_joueurs = $this->db->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile))
							   ->count_all_results('joueurs');

		$taux_chomage = round(100 - ($nb_employes + $nb_patrons) * 100.0 / $nb_joueurs, 2);

		// On récupère les anciens maires
		$query = $this->db->select('j.id, j.pseudo, j.perso, j.utiliser_avatar_toboz')
						  ->from('historique_maires hm')
						  ->join('joueurs j', 'j.id = hm.maire_id', 'left')
						  ->order_by('hm.date_debut', 'desc')
						  ->limit(4)
						  ->get();
		$maires = $query->result();

		// On affiche les résultats
		$vars = array(
			'mairie'       => $mairie,
			'maires'       => $maires,
			'taxes'        => $taxes,
			'historique'   => $historique,
			'pagination'   => isset($pagination) ? $pagination['liens'] : '',
			'taux_chomage' => $taux_chomage,
			'nb_chomeurs'  => $nb_joueurs - $nb_employes - $nb_patrons,
			'lien'		   => 1
		);
		return $this->layout->view('mairie/index', $vars);
	}

	public function terrain(){
		$this->load->library('vlux/map_factory');
		//On récupère la liste des terrains mis en vente par la mairie
		$maps = $this->map_factory->get_vente_mairie();
		$vars = array(
			'lien'	=> 2,
			'title'	=> "Terrains",
			'maps'	=> $maps
			);
		return $this->layout->view('mairie/terrains', $vars);
	}

	public function acheter_terrain($map_id = null){
		$this->load->library('vlux/map_factory');
		// Vérif id de la map
		if(!$this->map_factory->is_map($map_id)){
			show_404();
		}
		// On vérifie que la map appartient toujours à la mairie et est en vente
		else{
			$map = $this->map_factory->get_map($map_id);
			// La map n'appartient pas à la mairie ou n'est plus en vente
			if($map->proprio_id != 2 || $map->statut_vente != 1){
				$this->echec("Cette map n'est plus disponible");
				return $this->terrain();
			}
			//Sinon, on vérifie que le joueur a les fonds nécessaire
			elseif($this->session->userdata('struls') < $map->prix){
				$this->echec("Tu n'as pas assez d'argent pour acheter ce terrain !");
				return $this->terrain();
			}
			// Si tout est ok, on finalise la vente
			else{
				// On transfert la map à l'acquéreur
				$this->map_factory->cession_terrain($map->id, $this->session->userdata('id'));
				// On retire la somme de son compte
				$this->bouzouk->retirer_struls($map->prix);
				// On transfert les fonds à la mairie
				$this->lib_mairie->ajouter_struls($map->prix);
				// On ajoute à l'historique de la mairie
				$this->lib_mairie->historique($this->session->userdata('pseudo')." a acheté le terrain \" ".$map->nom." \" pour la somme de ".struls($map->prix).".");
				// On affiche un message de confirmation au joueur
				$this->succes('Tu as acheté le terrain " '.$map->nom.' " à la mairie');
				// Ainsi qu'à son historique
				$this->bouzouk->historique(274, 275, array($map->nom, $map->prix), $this->session->userdata('id'), Bouzouk::Historique_Objets);
			}
		}
	}

	public function gerer()
	{
		// On va chercher les infos de la mairie
		$query = $this->db->select('struls, salaire_maire, aide_entreprise, aide_chomage, impots_employes, impots_faim, impots_sante, impots_stress, impots_lohtoh, tricher_elections, cacher_salaire,
									coefficients_achats, promotion_objet_id,bonus_entreprise,malus_entreprise')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();
		$mairie->coefficients_achats = explode('|', $mairie->coefficients_achats);

		// Nombre de joueurs actifs
		$nb_joueurs_actifs = $this->db->where('statut', Bouzouk::Joueur_Actif)
							   ->where('id !=', $this->session->userdata('id'))
							   ->count_all_results('joueurs');
		$select_joueurs = $this->bouzouk->select_joueurs(array('status_not_in' => array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_ChoixPerso, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause, Bouzouk::Joueur_GameOver, Bouzouk::Joueur_Robot, Bouzouk::Joueur_Banni)));

		// On va chercher le nombre de mendiants
		$nb_mendiants = $this->db->where('joueur_id !=', $this->session->userdata('id'))
								 ->count_all_results('mendiants');

		// On va chercher la dernière taxe prévue si elle existe
		$query = $this->db->select('id, taux, raison')
						  ->from('taxes_surprises')
						  ->where('maire_id', $this->session->userdata('id'))
						  ->where('distribuee', 0)
						  ->order_by('date_taxe', 'desc')
						  ->limit(1)
						  ->get();

		if ($query->num_rows() == 1)
			$taxe = $query->row();

		else
		{
			$taxe         = new StdClass;
			$taxe->id     = 0;
			$taxe->taux   = $this->bouzouk->config('mairie_taxe_min');
			$taxe->raison = '';
		}

		$taxe->modification  = $this->input->post('taxe_id') !== false;
		
		// On regarde si le maire peut encore faire des dons
		$don_possible = $this->lib_mairie->don_possible(0);

		// On calcule le taux de chômage
		$nb_employes = $this->db->from('employes e')
								->join('joueurs j', 'j.id = e.joueur_id')
								->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile))
								->count_all_results('');
		$nb_patrons = $this->db->count_all('entreprises');
		$nb_joueurs = $this->db->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile))
							   ->count_all_results('joueurs');

		$taux_chomage = round(100 - ($nb_employes + $nb_patrons) * 100.0 / $nb_joueurs, 2);
		
		// ---------- Hook clans ----------
		// Propagande (Parti Politique)
		$query = $this->db->select('caa.parametres, c.nom AS nom_clan, c.mode_recrutement')
						  ->from('clans_actions_lancees caa')
						  ->join('clans c', 'c.id = caa.clan_id')
						  ->where('caa.action_id', 11)
						  ->where('caa.statut', Bouzouk::Clans_ActionEnCours)
						  ->get();
		$propagande = ($query->num_rows() == 1) ? $query->row() : null;
		
		// ---------- Hook clans ----------
		// Braquage (Organisation)
		$braquage = $this->bouzouk->clans_braquage();
			
		// On va chercher tous les objets possibles pour une promotion (sauf des magazins fermés pour la journée et les objets braqués)
		$this->db->select('o.id, o.nom')
				 ->from('objets o')
				 ->join('magasins m', 'm.objet_id = o.id')
				 ->where('o.disponibilite', 'entreprise')
				 ->where('m.quantite >= ('.$this->bouzouk->config('mairie_coefficient_promotion').' * '.$nb_joueurs.') / o.prix');
		
		if (isset($braquage))
			$this->db->where('o.id !=', $braquage->parametres['objet_id']);
		
		if (isset($propagande))
		{
			$propagande->parametres = unserialize($propagande->parametres);
			$this->db->where('o.type !=', $propagande->parametres['shop']);
		}
						  
		$query = $this->db->order_by('o.nom')
						  ->get();
		
		$objets_promotion = $query->result();

		// On calcule les moyennes et médianes des fortunes du classement de la dernière maintenance
		$query = $this->db->select('valeur')
						  ->from('classement_joueurs')
						  ->where('type', Bouzouk::Classement_Fortune)
						  ->order_by('id')
						  ->get();
		$fortunes = array();
		$nb_fortunes = $query->num_rows();

		$economie = array(
			'mediane' => 0,
			'moyenne' => 0,
			'moyenne_sous_mediane' => 0,
			'moyenne_sur_mediane' => 0
		);

		// Récupération et tri des fortunes
		foreach ($query->result() as $classement)
			$fortunes[] = $classement->valeur;
		sort($fortunes);

		// Calcul de la médiane
		$economie['mediane'] = $fortunes[$nb_fortunes / 2];

		// Calcul de la moyenne
		foreach ($fortunes as $fortune)
			$economie['moyenne'] += $fortune;
		$economie['moyenne'] /= $nb_fortunes;

		// Calcul de la moyenne sous la médiane
		for ($i = 0; $i < $nb_fortunes / 2; $i++)
			$economie['moyenne_sous_mediane'] += $fortunes[$i];
		$economie['moyenne_sous_mediane'] /= $nb_fortunes / 2;

		// Calcul de la moyenne sur la médiane
		for ($i = $nb_fortunes / 2; $i < $nb_fortunes; $i++)
			$economie['moyenne_sur_mediane'] += $fortunes[$i];
		$economie['moyenne_sur_mediane'] /= $nb_fortunes / 2;

		// On affiche
		$vars = array(
			'mairie'           => $mairie,
			'select_joueurs'   => $select_joueurs,
			'nb_joueurs'       => $nb_joueurs_actifs,
			'nb_mendiants'     => $nb_mendiants,
			'taxe'             => $taxe,
			'total_dons'       => $don_possible['total_dons'],
			'taux_chomage'     => $taux_chomage,
			'nb_chomeurs'      => $nb_joueurs - $nb_employes - $nb_patrons,
			'objets_promotion' => $objets_promotion,
			'economie'         => $economie,
			'lien'			   => 1
		);
		return $this->layout->view('mairie/gerer', $vars);
	}

	public function changer_gestion()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('salaire_maire', 'Le salaire du maire', 'required|is_natural|less_than_or_equal['.$this->bouzouk->config('mairie_salaire_max_maire').']');
		$this->form_validation->set_rules('aide_entreprise', "L'aide aux entreprises", 'required|is_natural|greater_than_or_equal['.$this->bouzouk->config('mairie_aide_entreprise_min').']|less_than_or_equal['.$this->bouzouk->config('mairie_aide_entreprise_max').']');
		$this->form_validation->set_rules('aide_chomage', "L'aide au chômage", 'required|is_natural|less_than_or_equal['.$this->bouzouk->config('mairie_aide_chomage_max').']');
		$this->form_validation->set_rules('bonus_entreprise', "Le bonus de rentré d'argent au entrepreprise", 'required|is_natural|greater_than_or_equal['.$this->bouzouk->config('entreprises_pourcent_min_bonus_rentre_argent').']|less_than_or_equal['.$this->bouzouk->config('entreprises_pourcent_max_bonus_rentre_argent').']');
		$this->form_validation->set_rules('malus_entreprise', "Le malus de rentré d'argent au entrepreprise", 'required|is_natural|greater_than_or_equal['.$this->bouzouk->config('entreprises_pourcent_min_malus_rentre_argent').']|less_than_or_equal['.$this->bouzouk->config('entreprises_pourcent_max_malus_rentre_argent').']');

		if ( ! $this->form_validation->run())
		{
			return $this->gerer();
		}

		// On modifie la mairie
		$data_mairie = array(
			'salaire_maire'      => $this->input->post('salaire_maire'),
			'aide_entreprise'    => $this->input->post('aide_entreprise'),
			'aide_chomage'       => $this->input->post('aide_chomage'),
			'tricher_elections'  => $this->input->post('tricher_elections') != false,
			'cacher_salaire'     => $this->input->post('cacher_salaire') != false,
			'bonus_entreprise'	 => $this->input->post('bonus_entreprise'),
			'malus_entreprise'	 => $this->input->post('malus_entreprise')
		);
		$this->db->update('mairie', $data_mairie);

		// On enregistre dans l'historique de la mairie
		$cacher_salaire = $this->input->post('cacher_salaire') != false ? 'oui' : 'non';
		$tricher_elections = $this->input->post('tricher_elections') != false ? 'oui' : 'non';
		$this->lib_mairie->historique('<span class="noir">Tu as changé la gestion de la mairie.</span> '.
									  'Salaire du maire : '.struls($this->input->post('salaire_maire')).' | '.
									  'Aide entreprises : '.struls($this->input->post('aide_entreprise')).' | '.
									  'Aide chômage : '.struls($this->input->post('aide_chomage')).' | '.
									  'Cacher salaire : <span class="pourpre">'.$cacher_salaire.'</span> | '.
									  'Tricher élections : <span class="pourpre">'.$tricher_elections.'</span>', $this->session->userdata('id'));

		// On affiche un message de confirmation
		$this->succes('Tu as bien modifié la gestion de la mairie');
		return $this->gerer();
	}

	public function changer_shops()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('coefficient_faim', 'Les points de Bouffzouk', 'required|is_natural|less_than_or_equal[18]');
		$this->form_validation->set_rules('coefficient_sante', "Les points d'Indispenzouk", 'required|is_natural|less_than_or_equal[18]');
		$this->form_validation->set_rules('coefficient_stress', 'Les points de Luxezouk', 'required|is_natural|less_than_or_equal[18]');

		if ( ! $this->form_validation->run())
			return $this->gerer();

		if ($this->input->post('coefficient_faim') + $this->input->post('coefficient_sante') + $this->input->post('coefficient_stress') != 18)
		{
			$this->echec('Le total des points de shops doit être égal à 18');
			return $this->gerer();
		}

		$coefficients = array(
			$this->input->post('coefficient_faim'),
			$this->input->post('coefficient_sante'),
			$this->input->post('coefficient_stress')
		);

		// On met à jour la mairie
		$this->db->set('coefficients_achats', implode('|', $coefficients))
				 ->update('mairie');

		// On ajoute à l'historique de la mairie
		$this->lib_mairie->historique('<span class="noir">Tu as changé la répartition des achats.</span><br>'.
									  'Bouffzouk : <span class="pourpre">'.pluriel($this->input->post('coefficient_faim'), 'point').'</span> | '.
									  'Indispenzouk : <span class="pourpre">'.pluriel($this->input->post('coefficient_sante'), 'point').'</span> | '.
									  'Luxezouk : <span class="pourpre">'.pluriel($this->input->post('coefficient_stress'), 'point').'</span>', $this->session->userdata('id'));

		// On affiche un message de confirmation
		$this->succes('La répartition des achats dans les shops a bien été modifiée');
		return $this->gerer();
	}

	public function donner_bouzouk()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur_id', 'Le joueur', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('montant', 'Le montant', 'required|is_natural_no_zero|less_than_or_equal['.$this->bouzouk->config('mairie_don_max_bouzouk').']');

		if ( ! $this->form_validation->run())
		{
			return $this->gerer();
		}

		// On vérifie que le maire ne se donne pas à lui-même
		if ($this->input->post('joueur_id') == $this->session->userdata('id'))
		{
			$this->echec('Tu ne peux pas te donner à toi-même');
			return $this->gerer();
		}

		// On vérifie que la mairie a assez de fonds
		if ( ! $this->lib_mairie->fonds_suffisants($this->input->post('montant')))
		{
			$this->echec("La mairie n'a plus assez de fonds pour cette opération");
			return $this->gerer();
		}

		// On vérifie que la limite de dons journalière n'est pas atteinte
		$don_possible = $this->lib_mairie->don_possible($this->input->post('montant'));

		if ($don_possible['limite_atteinte'])
		{
			$this->echec("Tu as déjà fait ".struls($don_possible['total_dons'])." de dons, ce don de ".struls($this->input->post('montant'))." te ferait dépasser la limite de ".struls($don_possible['max_dons'])." en <span class='pourpre'>".$don_possible['intervalle']." heures</span>.");
			return $this->gerer();
		}

		// On vérifie que le joueur existe et est valide
		$query = $this->db->select('id, pseudo')
						  ->from('joueurs')
						  ->where('id', $this->input->post('joueur_id'))
						  ->where('statut', Bouzouk::Joueur_Actif)
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Ce joueur n'existe pas");
			return $this->gerer();
		}

		$joueur = $query->row();

		// On vérifie que le joueur n'a pas déjà reçu un don ces derniers jours
		$deja_donne = $this->db->where('joueur_id', $this->input->post('joueur_id'))
							   ->where('donateur_id', $this->session->userdata('id'))
							   ->where('type', Bouzouk::Donation_MairieBouzouk)
							   ->where('date > (NOW() - INTERVAL '.$this->bouzouk->config('mairie_intervalle_don_bouzouk').' HOUR)') 
							   ->count_all_results('donations');

		if ($deja_donne > 0)
		{
			$this->echec('Tu as déjà donné récemment à ce bouzouk, tu dois attendre quelques jours avant de pouvoir lui refaire un don');
			return $this->gerer();
		}

		// On ajoute la somme au joueur et on retire à la mairie
		$this->bouzouk->ajouter_struls($this->input->post('montant'), $this->input->post('joueur_id'));
		$this->lib_mairie->retirer_struls($this->input->post('montant'));

		// On enregistre la donation
		$data_donations = array(
			'donateur_id' => $this->session->userdata('id'),
			'joueur_id'   => $this->input->post('joueur_id'),
			'montant'     => $this->input->post('montant'),
			'date'        => bdd_datetime(),
			'type'        => Bouzouk::Donation_MairieBouzouk
		);
		$this->db->insert('donations', $data_donations);

		// On envoie une missive au joueur
		$message  = "	Bonjour $joueur->pseudo\n\n";
		$message .= "Nous avons l'immense plaisir de t'informer que le maire ".profil()." vient de te faire un don de ".struls($this->input->post('montant')).".\n";
		$message .= "Ce don est personnel et il ne concerne que toi...tu es sûrement un de ces corrompus qui sont amis avec le maire...\n\n";
		$message .= "	Amicalement, la mairie de Vlurxtrznbnaxl.";

		$this->lib_missive->envoyer_missive(Bouzouk::Robot_Maire, $this->input->post('joueur_id'), "Le maire t'a fait un don", $message);

		// On ajoute à l'historique du receveur
		$this->bouzouk->historique(96, null, array(profil(), struls($this->input->post('montant'))), $this->input->post('joueur_id'));

		// On ajoute à l'historique de la mairie
		$message = 'Tu as fait un don de '.struls($this->input->post('montant')).' à '.profil($joueur->id, $joueur->pseudo);
		$this->lib_mairie->historique($message, $this->session->userdata('id'));

		// On affiche un message de confirmation
		$this->succes($message);
		return $this->gerer();
	}

	public function donner_bouzouks()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('min_struls', 'La fortune minimum', 'required|is_natural');
		$this->form_validation->set_rules('max_struls', "La fortune maximum", 'required|is_natural_no_zero');
		$this->form_validation->set_rules('montant', 'Le montant', 'required|is_natural_no_zero|less_than_or_equal['.$this->bouzouk->config('mairie_don_max_intervalle').']');

		if ( ! $this->form_validation->run())
		{
			return $this->gerer();
		}

		// Le montant minimum doit être inférieur de 5 struls au moins par rapport au montant maximum
		if ($this->input->post('min_struls') > $this->input->post('max_struls') - 5)
		{
			$this->echec('La fortune minimum doit être inférieure de 5 struls au moins par rapport à la fortune maximum');
			return $this->gerer();
		}

		// ---------- Hook clans ----------
		// Magouille fiscale (Struleone)
		$query = $this->db->select('c.chef_id, p.joueur_id')
						  ->from('clans_actions_lancees cal')
						  ->join('politiciens p', 'p.clan_id = cal.clan_id', 'left')
						  ->join('clans c', 'c.id = cal.clan_id', 'left')
						  ->where('cal.action_id', 27)
						  ->where('cal.statut', Bouzouk::Clans_ActionEnCours)
						  ->get();
		$magouille_fiscale = ($query->num_rows() > 0) ? true : null;
		$magouilleurs = array();

		foreach ($query->result() as $joueur)
		{
			$magouilleurs[] = $joueur->chef_id;

			if (isset($joueur->joueur_id))
				$magouilleurs[] = $joueur->joueur_id;
		}

		// On cherche les joueurs concernes
		// Don par fortune
		if ($this->input->post('par_fortune') !== false)
		{
			// Fortune
			$query = $this->db->select('j.id, j.pseudo, j.struls, SUM(m.quantite * o.prix) AS struls_maison')
							->from('joueurs j')
							->join('maisons m', 'm.joueur_id = j.id', 'left')
							->join('objets o', 'o.id = m.objet_id', 'left')
							->where('j.id !=', $this->session->userdata('id'))
							->where('j.statut', Bouzouk::Joueur_Actif)
							->group_by('j.id')
							->order_by('j.struls', 'desc')
							->get();
			$joueurs_tmp = $query->result();
			$joueurs = array();
			$joueurs_ids = array();

			foreach ($joueurs_tmp as $joueur)
			{
				if (isset($magouille_fiscale) && in_array($joueur->id, $magouilleurs))
					$total = 0;

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

					$total = $joueur->struls + $joueur->struls_maison + $marche_noir->struls_marche_noir;
				}
				
				if ($total >= $this->input->post('min_struls') && $total <= $this->input->post('max_struls'))
				{
					$joueurs[] = $joueur;
					$joueurs_ids[] = $joueur->id;
				}
			}

			$nb_joueurs_concernes = count($joueurs);
		}

		// Don par struls
		else
		{
			$this->db->select('id, pseudo')
					 ->from('joueurs')
					 ->where('struls BETWEEN '.$this->input->post('min_struls').' AND '.$this->input->post('max_struls'))
					 ->where('id !=', $this->session->userdata('id'))
					 ->where('statut', Bouzouk::Joueur_Actif);

			// Si le minimum est > 0, on enlève tous les magouilleurs
			if ($this->input->post('min_struls') > 0 && isset($magouille_fiscale))
				$this->db->where_not_in('id', $magouilleurs);

			$query = $this->db->get();
			$nb_joueurs_concernes = $query->num_rows();
			$joueurs = $query->result();
			$joueurs_ids = array();

			foreach ($joueurs as $joueur)
				$joueurs_ids[] = $joueur->id;

			// Magouille fiscale (Struleone) : si le minimum de struls est de 0 on rajoute aussi les magouilleurs
			if ($this->input->post('min_struls') == 0 && isset($magouille_fiscale))
			{
				$query = $this->db->select('id, pseudo')
								  ->from('joueurs')
								  ->where_in('id', $magouilleurs)
								  ->where('id !=', $this->session->userdata('id'))
								  ->where('statut', Bouzouk::Joueur_Actif)
								  ->get();
				$nb_joueurs_concernes += $query->num_rows();
	
				foreach ($query->result() as $joueur)
				{
					$joueurs[] = $joueur;
					$joueurs_ids[] = $joueur->id;
				}
			}
		}

		// On regarde si au moins 1 bouzouk est concerné
		if ($nb_joueurs_concernes == 0)
		{
			$this->echec("Aucun bouzouk n'est concerné par ce don");
			return $this->gerer();
		}

		// Le don doit être fait à au moins 10% de la population
		$pourcent_touches = $this->bouzouk->config('mairie_pourcent_touches_intervalle');
		$nb_joueurs_actifs  = $this->db->where('statut', Bouzouk::Joueur_Actif)->count_all_results('joueurs');
		$nb_joueurs_minimum = (int) ($nb_joueurs_actifs / $pourcent_touches);

		if ($nb_joueurs_concernes < $nb_joueurs_minimum)
		{
			$this->echec("Le don doit concerner au moins <span class='pourpre'>$nb_joueurs_minimum bouzouks</span> (<span cas='pourpre'>$pourcent_touches%</span> de la population)");
			return $this->gerer();
		}

		$montant_total = $nb_joueurs_concernes * $this->input->post('montant');
		if ( ! $this->lib_mairie->fonds_suffisants($montant_total))
		{
			$this->echec("La mairie n'a plus assez de fonds pour cette opération");
			return $this->gerer();
		}

		// On vérifie que la limite de dons journalière n'est pas atteinte
		$don_possible = $this->lib_mairie->don_possible($montant_total);

		if ($don_possible['limite_atteinte'])
		{
			$this->echec("Tu as déjà fait ".struls($don_possible['total_dons'])." de dons, ce don de ".struls($montant_total)." te ferait dépasser la limite de ".struls($don_possible['max_dons'])." en <span class='pourpre'>".$don_possible['intervalle']." heures</span>.");
			return $this->gerer();
		}

		// On ajoute la somme à tous les joueurs concernés
		$this->db->set('struls', 'struls + '.$this->input->post('montant'), false)
				 ->where_in('id', $joueurs_ids)
				 ->update('joueurs');

		// On retire la somme de la mairie
		$this->lib_mairie->retirer_struls($montant_total);

		// On envoie une missive aux bouzouks concernés
		$fortune = ($this->input->post('par_fortune') !== false) ? 'une fortune ' : '';
		$data_missives   = array();
		$data_historique = array();
		$date            = bdd_datetime();
		$timbre          = $this->lib_missive->timbres(0);

		foreach ($joueurs as $joueur)
		{
			$message  = "	Bonjour $joueur->pseudo\n\n";
			$message .= "Nous avons l'immense plaisir de t'informer que le maire ".profil()." vient de te faire un don de ".struls($this->input->post('montant')).".\n";
			$message .= "Ce don a été fait pour chaque bouzouk possédant ".$fortune."entre ".struls($this->input->post('min_struls')).' et '.struls($this->input->post('max_struls')).".\n\n";
			$message .= "	Amicalement, la mairie de Vlurxtrznbnaxl.";

			$data_missives[] = array(
				'expediteur_id'   => Bouzouk::Robot_Maire,
				'destinataire_id' => $joueur->id,
				'date_envoi'      => $date,
				'timbre'          => $timbre,
				'objet'           => "Le maire t'a fait un don",
				'message'         => $message
			);

			// On ajoute à l'historique du receveur
			$data_historique[] = array(
				'joueur_id' => $joueur->id,
				'type'      => Bouzouk::Historique_Dons,
				'texte'     => 'Tu as reçu un don du maire '.profil().' de '.struls($this->input->post('montant')).', ce don a été fait à tous les bouzouks ayant '.$fortune.'entre '.struls($this->input->post('min_struls')).' et '.struls($this->input->post('max_struls')),
				'date'      => $date
			);
		}
		$this->db->insert_batch('missives', $data_missives);
		$this->db->insert_batch('historique', $data_historique);

		// On enregistre la donation
		$data_donations = array(
			'donateur_id' => $this->session->userdata('id'),
			'joueur_id'   => 0,
			'montant'     => $montant_total,
			'date'        => bdd_datetime(),
			'type'        => Bouzouk::Donation_MairieBouzouks
		);
		$this->db->insert('donations', $data_donations);

		// On ajoute à l'historique de la mairie

		$message = 'Tu as donné '.struls($this->input->post('montant')).' à '.$nb_joueurs_concernes.' bouzouks qui avaient '.$fortune.'entre '.
						  struls($this->input->post('min_struls')).' et '.struls($this->input->post('max_struls')).' pour un total de '.struls($montant_total).' distribués';
		$this->lib_mairie->historique($message, $this->session->userdata('id'));

		// On affiche un message de confirmation
		$this->succes($message);
		return $this->gerer();
	}

	public function donner_mendiants()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('montant', 'Le montant', 'required|is_natural_no_zero|less_than_or_equal['.$this->bouzouk->config('mairie_don_max_mendiant').']');

		if ( ! $this->form_validation->run())
		{
			return $this->gerer();
		}

		// On vérifie que la mairie a assez de fonds
		$query = $this->db->select('j.id, j.pseudo')
						  ->from('mendiants m')
						  ->join('joueurs j', 'j.id = m.joueur_id')
						  ->where('joueur_id !=', $this->session->userdata('id'))
						  ->get();

		$nb_joueurs_concernes = $query->num_rows();
		$montant_total = $nb_joueurs_concernes * $this->input->post('montant');
		if ( ! $this->lib_mairie->fonds_suffisants($montant_total))
		{
			$this->echec("La mairie n'a plus assez de fonds pour cette opération");
			return $this->gerer();
		}

		// Si il n'y a pas assez de mendiants
		$nb_mendiants_min = $this->bouzouk->config('mairie_nb_mendiants_min_don');
		if ($nb_joueurs_concernes < $nb_mendiants_min)
		{
			$this->echec('Le don doit concerner au moins '.$nb_mendiants.' mendiants');
			return $this->gerer();
		}

		// On vérifie que la limite de dons journalière n'est pas atteinte
		$don_possible = $this->lib_mairie->don_possible($montant_total);

		if ($don_possible['limite_atteinte'])
		{
			$this->echec("Tu as déjà fait ".struls($don_possible['total_dons'])." de dons, ce don de ".struls($montant_total)." te ferait dépasser la limite de ".struls($don_possible['max_dons'])." en <span class='pourpre'>".$don_possible['intervalle']." heures</span>.");
			return $this->gerer();
		}

		// Liste des joueurs concernés
		$mendiants = $query->result();

		// On ajoute la somme à tous les joueurs concernés
		$this->db->set('struls', 'struls + '.$this->input->post('montant'), false)
				 ->where('id IN (SELECT joueur_id FROM mendiants)')
				 ->where('id !=', $this->session->userdata('id'))
				 ->update('joueurs');

		// On retire la somme de la mairie
		$this->lib_mairie->retirer_struls($montant_total);

		// On envoie une missive aux bouzouks concernés
		$data_missives   = array();
		$data_historique = array();
		$date            = bdd_datetime();
		$timbre          = $this->lib_missive->timbres(0);

		foreach ($mendiants as $joueur)
		{
			$message  = "	Bonjour $joueur->pseudo\n\n";
			$message .= "Nous avons l'immense plaisir de t'informer que le maire ".profil()." vient de te faire un don de ".struls($this->input->post('montant')).".\n";
			$message .= "Ce don a été fait à tous les mendiants\n\n";
			$message .= "	Amicalement, la mairie de Vlurxtrznbnaxl.";

			$data_missives[] = array(
				'expediteur_id'   => Bouzouk::Robot_Maire,
				'destinataire_id' => $joueur->id,
				'date_envoi'      => $date,
				'timbre'          => $timbre,
				'objet'           => "Le maire t'a fait un don",
				'message'         => $message
			);

			// On ajoute à l'historique du receveur
			$data_historique[] = array(
				'joueur_id' => $joueur->id,
				'type'      => Bouzouk::Historique_Dons,
				'texte'     => 'Tu as reçu un don du maire '.profil().' de '.struls($this->input->post('montant')).', ce don a été fait à tous les mendiants',
				'date'      => $date
			);
		}
		$this->db->insert_batch('missives', $data_missives);
		$this->db->insert_batch('historique', $data_historique);

		// On enregistre la donation
		$data_donations = array(
			'donateur_id' => $this->session->userdata('id'),
			'joueur_id'   => 0,
			'montant'     => $montant_total,
			'date'        => bdd_datetime(),
			'type'        => Bouzouk::Donation_MairieMendiants
		);
		$this->db->insert('donations', $data_donations);

		// On ajoute à l'historique de la mairie
		$message =  'Tu as donné '.struls($this->input->post('montant')).' à '.$nb_joueurs_concernes.' mendiant pour un total de '.struls($montant_total).' distribués';
		$this->lib_mairie->historique($message, $this->session->userdata('id'));

		// On affiche un message de confirmation
		$this->succes($message);
		return $this->gerer();
	}

	public function donner_tous()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('montant', 'Le montant', 'required|is_natural_no_zero|less_than_or_equal['.$this->bouzouk->config('mairie_don_max_tous').']');

		if ( ! $this->form_validation->run())
		{
			return $this->gerer();
		}

		// On vérifie que la mairie a assez de fonds
		$query = $this->db->select('id, pseudo')
						  ->from('joueurs')
						  ->where('statut', Bouzouk::Joueur_Actif)
						  ->where('id !=', $this->session->userdata('id'))
						  ->get();

		$nb_joueurs_concernes = $query->num_rows();
		$joueurs = $query->result();
		$montant_total = $nb_joueurs_concernes * $this->input->post('montant');

		if ( ! $this->lib_mairie->fonds_suffisants($montant_total))
		{
			$this->echec("La mairie n'a plus assez de fonds pour cette opération");
			return $this->gerer();
		}

		// On vérifie que la limite de dons journalière n'est pas atteinte
		$don_possible = $this->lib_mairie->don_possible($montant_total);

		if ($don_possible['limite_atteinte'])
		{
			$this->echec("Tu as déjà fait ".struls($don_possible['total_dons'])." de dons, ce don de ".struls($montant_total)." te ferait dépasser la limite de ".struls($don_possible['max_dons'])." en <span class='pourpre'>".$don_possible['intervalle']." heures</span>.");
			return $this->gerer();
		}

		// On ajoute la somme à tous les joueurs
		$this->db->set('struls', 'struls + '.$this->input->post('montant'), false)
				 ->where('statut', Bouzouk::Joueur_Actif)
				 ->where('id !=', $this->session->userdata('id'))
				 ->update('joueurs');

		// On retire la somme de la mairie
		$this->lib_mairie->retirer_struls($montant_total);

		// On envoie une missive aux bouzouks concernés
		$data_missives   = array();
		$data_historique = array();
		$date            = bdd_datetime();
		$timbre          = $this->lib_missive->timbres(0);

		foreach ($joueurs as $joueur)
		{
			$message  = "	Bonjour $joueur->pseudo\n\n";
			$message .= "Nous avons l'immense plaisir de t'informer que le maire ".profil()." vient de te faire un don de ".struls($this->input->post('montant')).".\n";
			$message .= "Ce don a été fait à tous les bouzouks\n\n";
			$message .= "	Amicalement, la mairie de Vlurxtrznbnaxl.";

			$data_missives[] = array(
				'expediteur_id'   => Bouzouk::Robot_Maire,
				'destinataire_id' => $joueur->id,
				'date_envoi'      => $date,
				'timbre'          => $timbre,
				'objet'           => "Le maire t'a fait un don",
				'message'         => $message
			);

			// On ajoute à l'historique du receveur
			$data_historique[] = array(
				'joueur_id' => $joueur->id,
				'type'      => Bouzouk::Historique_Dons,
				'texte'     => 'Tu as reçu un don du maire '.profil().' de '.struls($this->input->post('montant')).', ce don a été fait à tous les bouzouks',
				'date'      => $date
			);
		}
		$this->db->insert_batch('missives', $data_missives);
		$this->db->insert_batch('historique', $data_historique);

		// On enregistre la donation
		$data_donations = array(
			'donateur_id' => $this->session->userdata('id'),
			'joueur_id'   => 0,
			'montant'     => $montant_total,
			'date'        => bdd_datetime(),
			'type'        => Bouzouk::Donation_MairieTous
		);
		$this->db->insert('donations', $data_donations);

		// On ajoute à l'historique de la mairie
		$message = 'Tu as fait un don à tous les bouzouks, tu as donné '.struls($this->input->post('montant')).' à '.$nb_joueurs_concernes.' joueurs pour un total de '.struls($montant_total).' distribués';
		$this->lib_mairie->historique($message, $this->session->userdata('id'));

		// On affiche un message de confirmation
		$this->succes($message);
		return $this->gerer();
	}

	public function changer_impots()
	{
		// Règles de validation
		$impots_min_employes = $this->bouzouk->config('mairie_impots_employes_min');
		$impots_max_employes = $this->bouzouk->config('mairie_impots_employes_max');
		$impots_min_entreprises = $this->bouzouk->config('mairie_impots_entreprises_min');
		$impots_max_entreprises = $this->bouzouk->config('mairie_impots_entreprises_max');
		$impots_min_lohtoh = $this->bouzouk->config('mairie_impots_lohtoh_min');
		$impots_max_lohtoh = $this->bouzouk->config('mairie_impots_lohtoh_max');

		$this->load->library('form_validation');
		$this->form_validation->set_rules('impots_employes', "Le pourcentage d'impôts des employés", 'required|is_natural_no_zero|greater_than_or_equal['.$impots_min_employes.']|less_than_or_equal['.$impots_max_employes.']');
		$this->form_validation->set_rules('impots_faim', "Le pourcentage d'impôts des entreprises Bouffzouk", 'required|is_natural_no_zero|greater_than_or_equal['.$impots_min_entreprises.']|less_than_or_equal['.$impots_max_entreprises.']');
		$this->form_validation->set_rules('impots_sante', "Le pourcentage d'impôts des entreprises Indispenzouk", 'required|is_natural_no_zero|greater_than_or_equal['.$impots_min_entreprises.']|less_than_or_equal['.$impots_max_entreprises.']');
		$this->form_validation->set_rules('impots_stress', "Le pourcentage d'impôts des entreprises Luxezouk", 'required|is_natural_no_zero|greater_than_or_equal['.$impots_min_entreprises.']|less_than_or_equal['.$impots_max_entreprises.']');
		$this->form_validation->set_rules('impots_lohtoh', "Le pourcentage d'impôts du Lohtoh", 'required|is_natural_no_zero|greater_than_or_equal['.$impots_min_lohtoh.']|less_than_or_equal['.$impots_max_lohtoh.']');

		if ( ! $this->form_validation->run())
		{
			return $this->gerer();
		}

		$data_mairie = array(
			'impots_employes' => $this->input->post('impots_employes'),
			'impots_faim'     => $this->input->post('impots_faim'),
			'impots_sante'    => $this->input->post('impots_sante'),
			'impots_stress'   => $this->input->post('impots_stress'),
			'impots_lohtoh'   => $this->input->post('impots_lohtoh')
		);
		$this->db->update('mairie', $data_mairie);

		// On ajoute à l'historique de la mairie
		$this->lib_mairie->historique('<span class="noir">Tu as changé le montant des impôts.</span> '.
									  'Employés : <span class="pourpre">'.$this->input->post('impots_employes').'%</span> | '.
									  'Bouffzouk : <span class="pourpre">'.$this->input->post('impots_faim').'%</span> | '.
									  'Indispenzouk : <span class="pourpre">'.$this->input->post('impots_sante').'%</span> | '.
									  'Luxezouk : <span class="pourpre">'.$this->input->post('impots_stress').'%</span> | '.
									  'Lohtoh : <span class="pourpre">'.$this->input->post('impots_lohtoh').'%</span>', $this->session->userdata('id'));

		// On affiche un message de confirmation
		$this->succes('Les impôts ont bien été changés');
		return $this->gerer();
	}

	public function changer_promotion()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('objet_id', "L'objet", 'required|is_natural_no_zero');

		// On vérifie qu'une promotion n'est pas déjà en cours
		$existe = $this->db->where('promotion_objet_id IS NOT NULL')
						   ->count_all_results('mairie');

		if ($existe)
		{
			$this->echec("Tu as déjà lancé une promotion aujourd'hui, tu dois attendre la maintenance pour qu'elle prenne fin");
			return $this->gerer();
		}

		// On vérifie que l'objet est valide
		$nb_joueurs = $this->db->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile))
							   ->count_all_results('joueurs');
		
		// ---------- Hook clans ----------
		// Propagande (Parti Politique)
		$query = $this->db->select('caa.parametres, c.nom AS nom_clan, c.mode_recrutement')
						  ->from('clans_actions_lancees caa')
						  ->join('clans c', 'c.id = caa.clan_id')
						  ->where('caa.action_id', 11)
						  ->where('caa.statut', Bouzouk::Clans_ActionEnCours)
						  ->get();
		$propagande = ($query->num_rows() == 1) ? $query->row() : null;
		
		// ---------- Hook clans ----------
		// Braquage (Organisation)
		$braquage = $this->bouzouk->clans_braquage();
		
		$this->db->select('o.nom')
			     ->from('objets o')
				 ->join('magasins m', 'm.objet_id = o.id')
				 ->where('o.id', $this->input->post('objet_id'))
				 ->where('o.disponibilite', 'entreprise')
				 ->where('m.quantite >= ('.$this->bouzouk->config('mairie_coefficient_promotion').' * '.$nb_joueurs.') / o.prix');
		
		if (isset($braquage))
			$this->db->where('o.id !=', $braquage->parametres['objet_id']);
		
		if (isset($propagande))
		{
			$propagande->parametres = unserialize($propagande->parametres);
			$this->db->where('o.type !=', $propagande->parametres['shop']);
		}
		
		$query = $this->db->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Cet objet n'est pas ou plus éligible pour une promotion");
			return $this->gerer();
		}

		$objet = $query->row();

		// On définit la promotion
		$this->db->set('promotion_objet_id', $this->input->post('objet_id'))
				 ->update('mairie');

		$this->load->library('lib_notifications');
		
		// On prévient les joueurs
		$this->lib_notifications->notifier_all(Bouzouk::Notification_PromoMairie, 216, array(profil(), $objet->nom));

		// On ajoute à l'historique de la mairie
		$message = "Tu as mis l'objet <span class='pourpre'>".$objet->nom.'</span> en promotion pour la journée';
		$this->lib_mairie->historique($message, $this->session->userdata('id'));
		
		// On affiche une confirmation
		$this->succes($message);
		return $this->gerer();
	}

	public function taxe_surprise()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('taxe_id', 'La taxe', 'required|is_natural');
		$this->form_validation->set_rules('raison', 'La raison', 'required|min_length[15]|max_length[300]');
		$this->form_validation->set_rules('taux', 'Le taux', 'required|greater_than_or_equal['.$this->bouzouk->config('mairie_taxe_min').']|less_than_or_equal['.$this->bouzouk->config('mairie_taxe_max').']');

		if ( ! $this->form_validation->run())
		{
			return $this->gerer();
		}

		// Nouvelle taxe
		if ($this->input->post('taxe_id') == '0')
		{
			// On vérifie qu'une taxe n'a pas déjà été envoyée il y a quelques temps
			$deja_envoye = $this->db->where('maire_id', $this->session->userdata('id'))
									->where('date_taxe >= DATE_SUB("'.bdd_datetime().'", INTERVAL '.$this->bouzouk->config('mairie_intervalle_taxes').' HOUR)')
									->count_all_results('taxes_surprises');

			if ($deja_envoye > 0)
			{
				$this->echec('Tu as déjà envoyé une taxe aux bouzouks il y a moins de '.$this->bouzouk->config('mairie_intervalle_taxes').'h, tu dois attendre ce délai pour en renvoyer une autre');
				return $this->gerer();
			}

			// On insère la taxe surprise
			$data_taxes_surprises = array(
				'maire_id'  => $this->session->userdata('id'),
				'taux'      => $this->input->post('taux'),
				'raison'    => $this->input->post('raison'),
				'date_taxe' => bdd_datetime()
			);
			$this->db->insert('taxes_surprises', $data_taxes_surprises);
			$this->succes('La taxe surprise de <span class="pourpre">'.$this->input->post('taux').'%</span> a bien été prise en compte, elle sera envoyée aux bouzouks sous <span class="pourpre">24h</span> maximum');
		}

		// Modification
		else
		{
			// On vérifie que la taxe existe, n'a pas été envoyée et correspond bien à ce maire
			$existe = $this->db->where('distribuee', 0)
							   ->where('maire_id', $this->session->userdata('id'))
							   ->where('id', $this->input->post('taxe_id'))
							   ->count_all_results('taxes_surprises');

			if ($existe == 0)
			{
				$this->echec();
				return $this->gerer();
			}

			// On met à jour la taxe surprise
			$data_taxes_surprises = array(
				'taux'      => $this->input->post('taux'),
				'raison'    => $this->input->post('raison'),
			);
			$this->db->where('id', $this->input->post('taxe_id'))
					 ->update('taxes_surprises', $data_taxes_surprises);
			$this->succes("La taxe surprise a bien été modifiée");
		}

		// ---------- Hook clans ----------
		// Informateur (Organisation)
		$query = $this->db->select('caa.clan_id, c.chef_id')
						  ->from('clans_actions_lancees caa')
						  ->join('clans c', 'c.id = caa.clan_id')
						  ->where('caa.action_id', 19)
						  ->where('caa.statut', Bouzouk::Clans_ActionEnCours)
						  ->get();
		$clans = $query->result();


		foreach ($clans as $clan)
		{
			// On récupère tous les membres du clan
			$query = $this->db->select('joueur_id')
							  ->from('politiciens')
							  ->where('clan_id', $clan->clan_id)
							  ->get();
			$politiciens = $query->result();

			// On prévient le chef
			$this->bouzouk->notification(95, array(couleur($this->input->post('taux'))), $clan->chef_id);

			// On prévient les membres
			foreach ($politiciens as $politicien)
				$this->bouzouk->notification(95, array(couleur($this->input->post('taux'))), $politicien->joueur_id);
		}

		// On affiche un message de confirmation
		return $this->gerer();
	}

	public function gerer_map(){
		$vars['title'] = "Terrains";
		$vars['lien'] = 2;
		$this->load->library('vlux/map_factory');
		// On récupère le montant de la trésorerie
		// On va chercher les infos de la mairie
		$query = $this->db->select('struls')
						  ->from('mairie')
						  ->get();
		$tresorie = $query->row();
		$vars['struls'] = $tresorie->struls;
		// On récupère la liste des maps de la mairie
		$vars['maps'] = $this->map_factory->get_mairie_maps();
		// Layout
		$this->layout->view('mairie/gerer_map', $vars);
	}

	public function switch_statut_vente($map_id){
		if(!$this->bouzouk->is_beta_testeur()){
			return $this->gerer();
		}
		// On vérifie que la map est bien à la mairie
		$this->load->library('vlux/map_factory');
		if($this->map_factory->is_map($map_id)){
			$map = $this->map_factory->get_map($map_id);
		}
		else{
			return $this->gerer_map();
		}
		//On vérifie que la map est valide
		$this->load->library('vlux/map_factory');
		$map = $this->map_factory->get_map($map_id);
		//Si la map appartient à la mairie
		if($map && $map->proprio_id == 2){
			$map->statut_vente = ($map->statut_vente==1)?0:1;
			$this->map_factory->update_map($map);
		}
		// Si un terrain est mis en vente, on l'ajoute à l'historique
		if($map->statut_vente == 1){
			$this->lib_mairie->historique("La Mairie vient de mettre en vente le terrain \" ".$map->nom.' " pour la somme de '.struls($map->prix).'.');
		}
		return $this->gerer_map();
	}

	public function map_editor($id_map){
		if(!$this->bouzouk->is_beta_testeur()){
			return $this->gerer();
		}
		$vars['lien'] = 2;
		// On récupère les infos de la maps
		$this->load->library('vlux/map_factory');
		$map = $this->map_factory->get_map($id_map);
		//Si la map n'appartient pas à la mairie
		if($map->proprio_id!=2){
			return $this->gerer_map();
		}
		if($this->map_factory->is_map($id_map)){
			$vars['map']	= $this->map_factory->get_map($id_map);
			$vars['title']	= 'Modifier une map';
			$this->layout->view('mairie/map_editor', $vars);
		}
		elseif($id_map == 'tmp'){
			$vars['map'] = $this->map_factory->new_map();
			$vars['title'] = "Achat d'une map";
		}
		else{
			$this->echec('La map demandée n\'existe pas.');
			return $this->gerer_map();
		}
		$this->session->set_userdata('map_id', $vars['map']->id);
		$this->layout->view('mairie/map_editor', $vars);
		
	}

	public function map_editor_validation(){
		$this->load->library('form_validation');
		$map_id = $this->session->userdata('map_id');
		//Si c'est une nouvelle map
		if($map_id == 'tmp'){
			$rules = array(
				array(
						'field'		=> 'size',
						'label'		=> 'taille de la map',
						'rules'		=> 'required|callback_map_size_check'
						),
				array(
					'field'		=> 'nom',
					'label'		=> 'nom de la map',
					'rules'		=> 'required|max_length[30]|is_unique[vlux_maps.nom]|callback_map_nom_check'
					)
				);
		}
		// Une modification
		else{
			$rules = array(
				array(
				'field'		=> 'prix',
				'label'		=> 'prix de vente de la map',
				'rules'		=> 'required|is_numeric'
				),
				array(
					'field'		=> 'nom',
					'label'		=> 'nom de la map',
					'rules'		=> 'required|max_length[30]|callback_map_nom_check'
					)
				);
		}
		$this->form_validation->set_rules($rules);
		if($this->form_validation->run()){
			$this->load->library('vlux/map_factory');
			$this->load->library('vlux/vlux_factory');
			$this->load->library('vlux/item_factory');
			if($map_id == 'tmp'){
				$this->map = $this->map_factory->new_map();
				$this->map->size = $this->input->post('size');
				$this->map->nom = $this->input->post('nom');
			}
			else{
				$this->map = $this->map_factory->get_map($map_id);
			}
			// Mise à jour de la map
			if($map_id != 'tmp'){
				// On vérifie que le maire peut changer le nom de la map
				if($this->map->nom != $this->input->post('nom') && $this->map->changement_nom >= 2){
					$this->echec('Tu ne peux plus modifier le nom de cette map (max 2 fois par mandat).');
					return $this->gerer_map();
				}
				// Si oui
				if($this->input->post('nom')!= $this->map->nom){
					$this->map->changement_nom = $this->map->changement_nom + 1;
					$this->map->nom = $this->input->post('nom');
				}
				//Si le prix de vente est modfié, on vérifie
				if($this->map->prix != $this->input->post('prix') && !$this->map_prix_check()){
					$this->echec('Le prix de vente spécifié est incorrecte.');
					return $this->map_editor($map_id);
				}
				else{
					$this->map->prix = $this->input->post('prix');
				}
				$this->map_factory->update_map($this->map);
				$this->succes('La map a bien été modifiée.');
			}
			// Création de la map
			else{
				// On calcule le prix de la map
				$taux = $this->vlux_factory->vlux_config->map_prix_mairie;
				$prix = $taux*$this->map->size;
				// On vérifie que la mairie a assez de fonds
				if ( ! $this->lib_mairie->fonds_suffisants($prix))
				{
					$this->echec("La mairie n'a plus assez de fonds pour cette opération");
					return $this->gerer_map();
				}
				else{
					// On retire la somme à la mairie
					$this->lib_mairie->retirer_struls($prix);
					$this->map->prix = $prix;
					// On insert la map en bdd.
					$map_id = $this->map_factory->create_map($this->map);
					$this->succes('La mairie a acquis une nouvelle map');
					//On enregistre l'achat dans l'historique
					$this->lib_mairie->historique("La Mairie a fait l'acquisition d'un nouveau terrains");
				}
			}
			return $this->gerer_map();
		}
		else{
			return $this->map_editor($map_id);
		}
	}

	public function map_prix_check(){
		$prix = $this->input->post('prix');
		$coef = $this->map->size * $this->vlux_factory->vlux_config->map_prix_mairie * 10;
		$prix_min = $prix-(round($coef*25/100));
		$prix_max = 2*$coef;
		if($prix<$prix_min || $prix>$prix_max){
			return FALSE;
		}
		else{
			return TRUE;
		}
	}

	public function map_size_check(){
		$choix = array('20', '40');
		if(!in_array($this->input->post('size'), $choix)){
			$this->form_validation->set_message('map_size_check', "La taille choisie est incorrecte.");
			return FALSE;
		}
		else{
			return TRUE;
		}
	}

	public function map_nom_check(){
		if(strtolower($this->input->post('nom')) == "nouveau terrain"){
			$this->form_validation->set_message('map_nom_check', "Il faut donner un nom à la nouvelle map.");
			return FALSE;
		}
		else{
			return TRUE;
		}
	}

}