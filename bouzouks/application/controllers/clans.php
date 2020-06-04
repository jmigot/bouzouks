<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : clans (organisations, partis politiques et syndicats)
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : août 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Clans extends MY_Controller
{
	private $modes_recrutement;
	private $statistiques;
	private $types;

	public function __construct()
	{
		parent::__construct();
		
		// ---------- Hook clans ----------
		// Malediction du Schnibble (SDS)
		if ($this->methode != 'maudit')
			if ($this->session->userdata('maudit') && ! $this->bouzouk->is_admin())
				redirect('clans/maudit');
			
		else if ($this->methode != 'blocage')
		{
			// Si le joueur est trop faible
			if ($this->session->userdata('faim') < $this->bouzouk->config('clans_faim_minimum') ||
				$this->session->userdata('sante') < $this->bouzouk->config('clans_sante_minimum') || 
				$this->session->userdata('stress') > $this->bouzouk->config('clans_stress_maximum'))
			{
				redirect('clans/blocage');
			}
		}

		$this->load->library('lib_clans');

		$this->modes_recrutement = array(
			Bouzouk::Clans_RecrutementOuvert    => 'Clan ouvert',
			Bouzouk::Clans_RecrutementFerme     => 'Clan fermé',
			Bouzouk::Clans_RecrutementInvisible => 'Clan caché'
		);

		$this->statistiques = array(
			Bouzouk::Clans_TypeSyndicat       => array('force', 'de force', 'ta force'),
			Bouzouk::Clans_TypePartiPolitique => array('charisme', 'de charisme', 'ton charisme'),
			Bouzouk::Clans_TypeOrganisation   => array('intelligence', "d'intelligence", 'ton intelligence')
		);

		$this->types = array(
			Bouzouk::Clans_TypeSyndicat       => 'syndicat',
			Bouzouk::Clans_TypePartiPolitique => 'parti_politique',
			Bouzouk::Clans_TypeOrganisation   => 'organisation'
		);
	}

	/* Fonctions accessibles à tout le monde */
	public function blocage()
	{
		$message = '';
		
		if ($this->session->userdata('faim') < $this->bouzouk->config('clans_faim_minimum'))
			$message = "Tu es tout maigrichon, tu n'as que la trompe sur les os ! Mange un truc avant, faut que tu prennes du muscle !";

		else if ($this->session->userdata('sante') < $this->bouzouk->config('clans_sante_minimum'))
			$message = "Il nous faut des membres actifs et au top de leur forme ici ! Reviens quand tu seras en meilleur santé.";

		else if ($this->session->userdata('stress') > $this->bouzouk->config('clans_stress_maximum'))
			$message = "Un grand pouvoir implique de grandes responsabilités... Tu ne vas quand même pas faire d'actions dans ton clan avec l'état de nervosité dans lequel tu es actuellement ?!!";

		if ($message != '')
		{
			$vars = array(
				'titre_layout' => 'Clans',
				'titre'        => 'Clans - Tu es trop faible',
				'image_url'    => 'trop_faible.png',
				'message'      => '<p>'.$message.'</p>'
			);
			return $this->layout->view('blocage', $vars);
		}

		else
			show_404();
	}

	public function maudit()
	{
		if ( ! $this->session->userdata('maudit'))
			show_404();

		return $this->layout->view('clans/maudit');
	}

	public function historique_actions($offset = '0')
	{
		// Pagination
		$nb_lignes_historique = $this->db->where('date_debut < (NOW() - INTERVAL '.$this->bouzouk->config('clans_nb_jours_historique_cache').' DAY)')
										 ->count_all_results('clans_actions_lancees');
		$pagination = creer_pagination('clans/historique_actions', $nb_lignes_historique, 50, $offset);

		// On récupère les actions lancées
		$query = $this->db->select('ca.nom, ca.effet, caa.date_debut, ca.image, caa.statut, caa.cout, caa.parametres, c.nom AS nom_clan, c.mode_recrutement')
						  ->from('clans_actions_lancees caa')
						  ->join('clans_actions ca', 'ca.id = caa.action_id')
						  ->join('clans c', 'c.id = caa.clan_id')
  						  ->where('date_debut < (NOW() - INTERVAL '.$this->bouzouk->config('clans_nb_jours_historique_cache').' DAY)')
						  ->order_by('caa.date_debut', 'desc')
						  ->limit($pagination['par_page'], $pagination['offset'])
						  ->get();
		$actions_lancees = $query->result();

		// On affiche
		$vars = array(
			'actions_lancees' => $actions_lancees,
			'pagination'      => $pagination['liens']
		);
		return $this->layout->view('clans/historique_actions', $vars);
	}

	public function reponse_prise_de_pouvoir()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('decision', 'La décision', 'required|regex_match[#^ceder|sanction|bouzopolice$#]');

		if ( ! $this->form_validation->run())
			show_404();

		// On vérifie que le joueur est le maire
		$maire = $this->db->where('maire_id', $this->session->userdata('id'))
						  ->count_all_results('mairie');

		if ( ! $maire)
			show_404();

		// On vérifie qu'une action de Prise de pouvoir est en cours
		$query = $this->db->select('cal.id, cal.parametres, c.chef_id')
						  ->from('clans_actions_lancees cal')
						  ->join('clans c', 'c.id = cal.clan_id')
						  ->where('cal.action_id', 8)
						  ->where('cal.statut', Bouzouk::Clans_ActionEnCours)
						  ->get();

		if ($query->num_rows() == 0)
			show_404();

		$action = $query->row();
		$action->parametres = unserialize($action->parametres);

		// On applique la décision
		if ($this->lib_clans->reponse_prise_de_pouvoir($this->input->post('decision'), $action))
			$this->succes('Ta décision a bien été appliquée :)');
		
		redirect('joueur/accueil');
	}

	/* Fonction de vérification de formulaires */
	public function _mode_recrutement_check($mode_recrutement)
	{
		if ( ! in_array($mode_recrutement, array_keys($this->modes_recrutement)))
		{
			$this->form_validation->set_message('_mode_recrutement_check', '%s est invalide');
			return false;
		}

		return true;
	}

	public function _type_check($type)
	{
		if ( ! in_array($type, array_keys($this->types)))
		{
			$this->form_validation->set_message('_type_check', '%s est invalide');
			return false;
		}

		return true;
	}

	public function _description_check($description)
	{
		if (mb_substr_count($description, "\n") > 5)
		{
			$this->form_validation->set_message('_description_check', '%s contient trop de sauts de ligne (<span class="pourpre">4 maximum</span>)');
			return false;
		}

		return true;
	}

	/* Recrutement des clans */
	public function creer()
	{
		// Il faut pouvoir rejoindre au moins 1 type de clan et n'être chef d'aucun autre clan		
		if ($this->session->userdata('nb_clans') >= Bouzouk::Clans_NbClansMax || ($this->session->userdata('nb_clans') == Bouzouk::Clans_NbClansMax - 1 && ! $this->session->userdata('syndicats_autorises')) || $this->session->userdata('chef_clan'))
			show_404();

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('nom', 'Le nom du clan', 'required|min_length[3]|max_length[35]|is_unique[clans.nom]');
		$this->form_validation->set_rules('type', 'Le type de clan', 'required|is_natural_no_zero|callback__type_check');
		$this->form_validation->set_rules('mode_recrutement', 'Le mode de recrutement', 'required|is_natural_no_zero|callback__mode_recrutement_check');
		$this->form_validation->set_rules('description', 'La description', 'required|max_length[250]|callback__description_check');

		if ( ! $this->form_validation->run())
			return $this->layout->view('clans/creer');

		// On vérifie que le joueur a assez d'expérience pour créer ce type de clan
		if ($this->session->userdata('experience') < $this->bouzouk->config('clans_xp_min_creer_'.$this->types[$this->input->post('type')]))
		{
			$this->echec("Tu n'as pas assez d'expérience pour créer ce type de clan");
			return $this->layout->view('clans/creer');
		}

		// Si c'est un syndicat, on vérifie que les syndicats sont autorisés pour ce joueur (vrai uniquement s'il est employé)
		if ($this->input->post('type') == Bouzouk::Clans_TypeSyndicat &&  ! $this->session->userdata('syndicats_autorises'))
		{
			$this->echec("Tu dois être employé dans une entreprise qui autorise les syndicats pour en créer un");
			return $this->layout->view('clans/creer');
		}

		// On vérifie que le joueur peut créer ce type de clan
		if ($this->session->userdata('clan_id')[$this->input->post('type')])
		{
			$this->echec("Tu as déjà un clan de ce type");
			return $this->layout->view('clans/creer');
		}

		// On vérifie que le joueur a assez de struls pour payer la création
		if ($this->session->userdata('struls') < $this->bouzouk->config('clans_struls_min_creer_'.$this->types[$this->input->post('type')]))
		{
			$this->echec("Tu n'as pas assez de struls pour payer la création de ce type de clan");
			return $this->layout->view('clans/creer');
		}

		// On supprime les annonces des autres clans
		$this->db->where('joueur_id', $this->session->userdata('id'))
				 ->where('clan_id IN (SELECT id FROM clans WHERE type = '.$this->input->post('type').')')
				 ->where('refuse', 0)
				 ->delete('clans_recrutement');

		// On retire les struls au joueur
 		$struls_perdus = $this->bouzouk->config('clans_struls_min_creer_'.$this->types[$this->input->post('type')]);
		$this->bouzouk->retirer_struls($struls_perdus);

		if ($this->input->post('mode_recrutement') != Bouzouk::Clans_RecrutementInvisible)
			$this->bouzouk->historique(23, 24, array(form_prep($this->input->post('nom')), struls($struls_perdus)));
		else
			$this->bouzouk->historique(23, null, array(form_prep($this->input->post('nom')), struls($struls_perdus)));

		// On enregistre le clan
		$data_clans = array(
			'chef_id'          => $this->session->userdata('id'),
			'type'             => $this->input->post('type'),
			'nom'              => $this->input->post('nom'),
			'mode_recrutement' => $this->input->post('mode_recrutement'),
			'description'      => $this->input->post('description'),
			'entreprise_id'    => ($this->input->post('type') == Bouzouk::Clans_TypeSyndicat) ? $this->session->userdata('entreprise_id') : null,
			'date_creation'    => bdd_datetime()
		);
		$this->db->insert('clans', $data_clans);

		// La session doit être mise à jour
		$this->bouzouk->augmente_version_session();

		// On affiche un message de confirmation
		$this->succes('Ton clan a bien été créé');
		redirect('clans/gerer/'.$this->types[$this->input->post('type')]);
	}

	public function lister()
	{
		// On va chercher la liste des clans ouverts/fermés
		$query = $this->db->select('c.id, c.nom, c.description, c.entreprise_id, c.type, c.type_officiel, c.nom_chef, e.nom AS nom_entreprise, j.id AS chef_id, j.pseudo AS chef_pseudo, j.rang AS chef_rang, COUNT(p.id) AS nb_membres')
						  ->from('clans c')
						  ->join('joueurs j', 'j.id = c.chef_id')
						  ->join('politiciens p', 'p.clan_id = c.id', 'left')
						  ->join('entreprises e', 'e.id = c.entreprise_id', 'left')
						  ->where_in('c.mode_recrutement', array(Bouzouk::Clans_RecrutementOuvert, Bouzouk::Clans_RecrutementFerme))
						  ->group_by('c.id')
  						  ->order_by('c.type, c.entreprise_id, c.nom')
						  ->get();
		$clans = $query->result();
		
		// On trie les clans
		$clans_tries = array(
			Bouzouk::Clans_TypeSyndicat       => array(),
			Bouzouk::Clans_TypePartiPolitique => array(),
			Bouzouk::Clans_TypeOrganisation   => array()
		);
		$nb_syndicats_entreprise = 0;

		foreach ($clans as $clan)
		{
			$clans_tries[$clan->type][] = $clan;

			if ($clan->type == Bouzouk::Clans_TypeSyndicat && $clan->entreprise_id == $this->session->userdata('entreprise_id'))
				$nb_syndicats_entreprise++;
		}

		// Nombre de clans cachés
		$nb_clans_caches = $this->db->where('mode_recrutement', Bouzouk::Clans_RecrutementInvisible)
									->count_all_results('clans');

		// On affiche
		$vars = array(
			'clans'                   => $clans_tries,
			'types'                   => $this->types,
			'nb_clans_caches'         => $nb_clans_caches,
			'nb_syndicats_entreprise' => $nb_syndicats_entreprise
		);
		return $this->layout->view('clans/lister', $vars);
	}

	public function postuler()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('clan_id', 'Le clan', 'required|is_natural');

		if ( ! $this->form_validation->run())
			return $this->lister();

		// On récupère les infos du clan
		$clan = $this->lib_clans->get_clan($this->input->post('clan_id'));

		if ( ! isset($clan) || ! in_array($clan->mode_recrutement, array(Bouzouk::Clans_RecrutementOuvert, Bouzouk::Clans_RecrutementFerme)))
		{
			$this->echec("Ce clan n'existe pas");
			return $this->lister();
		}

		// On vérifie que le joueur peut rejoindre ce type de clan
		if ($this->session->userdata('clan_id')[$clan->type])
		{
			$this->echec('Tu as déjà un clan de ce type');
			return $this->lister();
		}

		// On vérifie que le joueur a assez d'expérience pour le clan
		if ($this->session->userdata('experience') < $this->bouzouk->config('clans_xp_min_rejoindre_'.$this->types[$clan->type]))
		{
			$this->echec("Tu n'as pas assez d'expérience pour rejoindre un clan");
			return $this->lister();
		}

		// Si c'est un syndicat on vérifie que le joueur est bien dans l'entreprise
		if ($clan->type == Bouzouk::Clans_TypeSyndicat)
		{
			if ( ! $this->session->userdata('syndicats_autorises'))
			{
				$this->echec("Tu dois être employé dans une entreprise qui autorise les syndicats pour en rejoindre un");
				return $this->lister();
			}

			if ($this->session->userdata('entreprise_id') != $clan->entreprise_id)
			{
				$this->echec("Ce syndicat n'est pas lié à ton entreprise");
				return $this->lister();
			}
		}

		// On regarde si le joueur a déjà postulé
		$deja_fait = $this->db->where('joueur_id', $this->session->userdata('id'))
							  ->where('clan_id', $clan->id)
							  ->where('refuse', 0)
							  ->count_all_results('clans_recrutement');

		if ($deja_fait)
		{
			$this->echec('Tu as déjà postulé dans ce clan');
			return $this->lister();
		}

		// On regarde si le joueur est dans la liste noire
		$deja_fait = $this->db->where('joueur_id', $this->session->userdata('id'))
							  ->where('clan_id', $clan->id)
							  ->where('refuse', 1)
							  ->count_all_results('clans_recrutement');

		if ($deja_fait)
		{
			$this->echec('Tu es dans la liste noire de ce clan, tu ne peux pas postuler');
			return $this->lister();
		}

		// Si le clan est ouvert, on le rejoint direct
		if ($clan->mode_recrutement == Bouzouk::Clans_RecrutementOuvert)
		{
			$this->lib_clans->rejoindre_clan($this->session->userdata('id'), $clan->id, $clan->type, $this->input->post('invisible') !== false, true);

			// On affiche une confirmation
			$this->succes('Tu as bien rejoint ce clan');
			redirect('clans/gerer/'.$this->types[$clan->type]);
		}

		// Sinon si il est fermé on postule
		else
		{
			$data_clans_recrutement = array(
				'joueur_id' => $this->session->userdata('id'),
				'clan_id'   => $clan->id,
				'date'      => bdd_datetime(),
				'invisible' => $this->input->post('invisible') !== false,
			);
			$this->db->insert('clans_recrutement', $data_clans_recrutement);

			// On envoit une notif au chef
			$this->bouzouk->notification(28, array(profil(-1), couleur(form_prep($clan->nom))), $clan->chef_id);

			// On affiche une confirmation
			$this->succes("Tu as bien postulé pour entrer dans ce clan, tu dois attendre d'être accepté");
			return $this->lister();
		}
	}

	public function postuler_invisible()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('nom', 'Le nom du clan', 'required|min_length[3]|max_length[35]');
		$this->form_validation->set_rules('type', 'Le type de clan', 'required|is_natural_no_zero|callback__type_check');

		if ( ! $this->form_validation->run())
			return $this->lister();

		// On regarde si un clan existe
		$query = $this->db->select('id, mode_recrutement, type, entreprise_id, chef_id, nom')
						  ->from('clans')
						  ->where('nom', $this->input->post('nom'))
						  ->where('type', $this->input->post('type'))
						  ->where('mode_recrutement', Bouzouk::Clans_RecrutementInvisible)
						  ->get();
		
		// Si il existe on postule
		if ($query->num_rows() == 1)
		{
			$clan = $query->row();
			$ok = true;

			// On vérifie que le joueur peut rejoindre ce type de clan
			if ($this->session->userdata('clan_id')[$clan->type])
				$ok = false;

			// On vérifie que le joueur a assez d'expérience
			if ($this->session->userdata('experience') < $this->bouzouk->config('clans_xp_min_rejoindre_'.$this->types[$clan->type]))
				$ok = false;

			// Si c'est un syndicat on vérifie que le joueur est bien dans l'entreprise
			if ($clan->type == Bouzouk::Clans_TypeSyndicat && ( ! $this->session->userdata('syndicats_autorises') || $this->session->userdata('entreprise_id') != $clan->entreprise_id))
				$ok = false;

			// On regarde si le joueur a déjà postulé ou si il est sur liste noire
			$deja_fait = $this->db->where('joueur_id', $this->session->userdata('id'))
								  ->where('clan_id', $clan->id)
								  ->count_all_results('clans_recrutement');

			if ( ! $deja_fait && $ok)
			{
				// On postule
				$data_clans_recrutement = array(
					'joueur_id' => $this->session->userdata('id'),
					'clan_id'   => $clan->id,
					'date'      => bdd_datetime(),
					'invisible' => $this->input->post('invisible') !== false,
				);
				$this->db->insert('clans_recrutement', $data_clans_recrutement);

				// On envoit une notif au chef
				$this->bouzouk->notification(28, array(profil(-1), couleur(form_prep($clan->nom))), $clan->chef_id);
			}
		}

		// Dans tous les cas on affiche le même message
		$this->succes("Une demande a été envoyée pour rejoindre ce clan, ce message s'affiche même si le clan n'existe pas, tu dois donc vérifier avec le chef du clan que ta candidature est bien enregistrée");
		return $this->lister();
	}

	public function recrutement($type = '')
	{
		// Le type doit être valide et le joueur sous-chef dans un clan de ce type
		if (($type = array_search($type, $this->types)) === false || ! $this->session->userdata('clan_id')[$type] || $this->session->userdata('clan_grade')[$type] < Bouzouk::Clans_GradeSousChef)
			show_404();

		// On va chercher les infos du clan
		$query = $this->db->select('c.id, j.id AS chef_id, j.pseudo AS chef_pseudo, j.rang AS chef_rang, c.nom, c.type, c.type_officiel, c.description, c.mode_recrutement, c.points_action, c.nom_chef, c.nom_sous_chefs, c.nom_membres, c.nom_tests, c.date_creation')
						  ->from('clans c')
						  ->join('joueurs j', 'j.id = c.chef_id')
						  ->where('c.id', $this->session->userdata('clan_id')[$type])
						  ->get();
		$clan = $query->row();

		// On va chercher toutes les demandes de recrutement du clan en attente
		$query = $this->db->select('j.id, j.pseudo, j.rang, cr.date, cr.invisible')
						  ->from('clans_recrutement cr')
						  ->join('joueurs j', 'j.id = cr.joueur_id')
						  ->where('cr.clan_id', $this->session->userdata('clan_id')[$type])
						  ->where('refuse', 0)
						  ->order_by('cr.date', 'desc')
						  ->get();
		$joueurs_attente = $query->result();

		// On va chercher les joueurs refusés
		$query = $this->db->select('j.id, j.pseudo, j.rang, cr.date, cr.invisible')
						  ->from('clans_recrutement cr')
						  ->join('joueurs j', 'j.id = cr.joueur_id')
						  ->where('cr.clan_id', $this->session->userdata('clan_id')[$type])
						  ->where('refuse', 1)
						  ->order_by('cr.date', 'desc')
						  ->get();
		$joueurs_refuses = $query->result();

		// On affiche
		$vars = array(
			'joueurs_attente' => $joueurs_attente,
			'joueurs_refuses' => $joueurs_refuses,
			'clan'            => $clan,
			'types'           => $this->types,
			'espionnage'      => $this->lib_clans->espionnage_en_cours($clan->id)
		);
		return $this->layout->view('clans/recrutement', $vars);
	}

	public function accepter()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('type', 'Le type de clan', 'required|is_natural_no_zero|callback__type_check');
		$this->form_validation->set_rules('joueur_id', 'Le joueur', 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
			return $this->recrutement($this->types[$this->input->post('type')]);

		// Le joueur doit être sous-chef dans un clan de ce type
		$type = $this->input->post('type');

		if ( ! $this->session->userdata('clan_id')[$type] || $this->session->userdata('clan_grade')[$type] < Bouzouk::Clans_GradeSousChef)
			show_404();

		// On vérifie que le joueur a postulé dans ce clan
		$query = $this->db->select('id, joueur_id, clan_id, invisible')
						  ->from('clans_recrutement')
						  ->where('clan_id', $this->session->userdata('clan_id')[$type])
				 		  ->where('joueur_id', $this->input->post('joueur_id'))
				 		  ->where('refuse', 0)
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Ce membre n'a pas postulé dans le clan");
			return $this->recrutement($this->types[$type]);
		}

		$annonce = $query->row();

		// On supprime la demande de recrutement
		$this->db->where('id', $annonce->id)
				 ->delete('clans_recrutement');

		// On rejoint le clan
		$this->lib_clans->rejoindre_clan($annonce->joueur_id, $annonce->clan_id, $type, $annonce->invisible);

		// On affiche une confirmation
		$this->succes('Tu as bien accepté ce membre dans le clan');
		return $this->recrutement($this->types[$type]);
	}

	public function refuser()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('type', 'Le type de clan', 'required|is_natural_no_zero|callback__type_check');
		$this->form_validation->set_rules('joueur_id', 'Le joueur', 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
			return $this->recrutement($this->types[$this->input->post('type')]);

		// Le joueur doit être sous-chef dans un clan de ce type
		$type = $this->input->post('type');

		if ( ! $this->session->userdata('clan_id')[$type] || $this->session->userdata('clan_grade')[$type] < Bouzouk::Clans_GradeSousChef)
			show_404();

		// On refuse le membre dans le clan
		$this->db->set('refuse', 1)
				 ->where('clan_id', $this->session->userdata('clan_id')[$type])
				 ->where('joueur_id', $this->input->post('joueur_id'))
				 ->where('refuse', 0)
				 ->update('clans_recrutement');

		if ($this->db->affected_rows() == 0)
		{
			$this->echec("Ce membre n'a pas postulé dans le clan");
			return $this->recrutement($this->types[$type]);
		}

		// On récupère le clan
		$clan = $this->lib_clans->get_clan($this->session->userdata('clan_id')[$type]);

		// On ajoute à l'historique
		$this->lib_clans->historique(profil(-1, '', $this->session->userdata('rang')).' a refusé '.profil($this->input->post('joueur_id')).' dans le clan', $this->session->userdata('clan_id')[$type]);

		// On envoit une notif au joueur
		if ($clan->mode_recrutement != Bouzouk::Clans_RecrutementInvisible)
			$this->bouzouk->notification(29, array(profil(-1, '', $this->session->userdata('rang')), couleur(form_prep($clan->nom))), $this->input->post('joueur_id'));

		// On affiche une confirmation
		$this->succes('Tu as bien refusé ce membre dans le clan');
		return $this->recrutement($this->types[$type]);
	}

	public function ajouter_liste_noire()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('type', 'Le type de clan', 'required|is_natural_no_zero|callback__type_check');
		$this->form_validation->set_rules('pseudo', 'Le pseudo', 'required|max_length[20]');

		if ( ! $this->form_validation->run())
			return $this->recrutement($this->types[$this->input->post('type')]);

		// Le joueur doit être sous-chef dans un clan de ce type
		$type = $this->input->post('type');

		if ( ! $this->session->userdata('clan_id')[$type] || $this->session->userdata('clan_grade')[$type] < Bouzouk::Clans_GradeSousChef)
			show_404();

		// On regarde si le joueur existe
		$query = $this->db->select('id, pseudo, rang')
						  ->from('joueurs')
						  ->where('pseudo', $this->input->post('pseudo'))
						  ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Ce joueur n'existe pas");
			return $this->recrutement($this->types[$this->input->post('type')]);
		}

		$joueur = $query->row();

		// Si le joueur est celui qui demande
		if ($joueur->id == $this->session->userdata('id'))
		{
			$this->echec("Tu veux te mettre toi-même dans la liste noire ? Tu m'as l'air un peu mazouk sur les bords toi...");
			return $this->recrutement($this->types[$this->input->post('type')]);
		}

		// On regarde si le joueur est déjà en liste noire ou s'il a postulé
		$existe = $this->db->where('clan_id', $this->session->userdata('clan_id')[$type])
						   ->where('joueur_id', $joueur->id)
						   ->count_all_results('clans_recrutement');

		if ($existe)
		{
			$this->echec('Ce joueur est déjà dans la liste noire ou a postulé pour entrer dans le clan, ouvre tes yeux !');
			return $this->recrutement($this->types[$this->input->post('type')]);
		}

		// Si non, on l'ajoute
		$data_clans_recrutement = array(
			'joueur_id' => $joueur->id,
			'clan_id'   => $this->session->userdata('clan_id')[$type],
			'date'      => bdd_datetime(),
			'invisible' => 0,
			'refuse'    => 1
		);
		$this->db->insert('clans_recrutement', $data_clans_recrutement);
		
		// On ajoute à l'historique
		$this->lib_clans->historique(profil(-1, '', $this->session->userdata('rang')).' a ajouté '.profil($joueur->id, $joueur->pseudo, $joueur->rang).' dans la liste noire du clan', $this->session->userdata('clan_id')[$type]);

		// On affiche une confirmation
		$this->succes(profil($joueur->id, $joueur->pseudo, $joueur->rang).' a bien été ajouté à la liste noire');
		return $this->recrutement($this->types[$this->input->post('type')]);
	}

	public function supprimer_liste_noire()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('type', 'Le type de clan', 'required|is_natural_no_zero|callback__type_check');
		$this->form_validation->set_rules('joueur_id', 'Le joueur', 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
			return $this->recrutement($this->types[$this->input->post('type')]);

		// Le joueur doit être sous-chef dans un clan de ce type
		$type = $this->input->post('type');

		if ( ! $this->session->userdata('clan_id')[$type] || $this->session->userdata('clan_grade')[$type] < Bouzouk::Clans_GradeSousChef)
			show_404();

		// On supprime l'annonce de recrutement
		$this->db->where('clan_id', $this->session->userdata('clan_id')[$type])
				 ->where('joueur_id', $this->input->post('joueur_id'))
				 ->where('refuse', 1)
				 ->delete('clans_recrutement');

		if ($this->db->affected_rows() == 0)
		{
			$this->echec("Ce membre n'est pas dans la liste noire du clan");
			return $this->recrutement($this->types[$this->input->post('type')]);
		}

		// On ajoute à l'historique
		$this->lib_clans->historique(profil(-1, '', $this->session->userdata('rang')).' a enlevé '.profil($this->input->post('joueur_id')).' de la liste noire du clan', $this->session->userdata('clan_id')[$type]);

		// On affiche une confirmation
		$this->succes('Tu as bien supprimé ce bouzouk de la liste noire du clan');
		return $this->recrutement($this->types[$type]);
	}

	public function quitter($type = '')
	{
		// Le type doit être valide et le joueur dans un clan de ce type
		if (($type = array_search($type, $this->types)) === false || ! $this->session->userdata('clan_id')[$type] || $this->session->userdata('clan_grade')[$type] == Bouzouk::Clans_GradeChef)
			show_404();

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('quitter', "L'appui sur le bouton \"Quitter\"", 'required');

		if ( ! $this->form_validation->run())
			return $this->gerer($this->types[$type]);

		// On vérifie que le joueur y est depuis au moins un certain temps
		$possible = $this->db->where('joueur_id', $this->session->userdata('id'))
							 ->where('clan_id', $this->session->userdata('clan_id')[$type])
							 ->where('date_entree < (NOW() - INTERVAL '.$this->bouzouk->config('clans_temps_avant_quitter').' HOUR)')
							 ->count_all_results('politiciens');

		if ( ! $possible)
		{
			$this->echec('Tu dois attendre au moins <span class="pourpre">'.$this->bouzouk->config('clans_temps_avant_quitter').' heures</span> avant de pouvoir quitter le clan');
			return $this->gerer($this->types[$type]);
		}

		// On quitte le clan
		$this->lib_clans->quitter_clan($this->session->userdata('clan_id')[$type], $this->session->userdata('id'));
		
		// On affiche une confirmation
		$this->succes('Tu as bien quitté ce clan');
		redirect('joueur/accueil');
	}

	public function leguer($type = '')
	{
		// Le type doit être valide et le joueur chef du clan
		if (($type = array_search($type, $this->types)) === false || ! $this->session->userdata('clan_id')[$type] || $this->session->userdata('clan_grade')[$type] != Bouzouk::Clans_GradeChef)
			show_404();

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('leguer', "L'appui sur le bouton \"Léguer\"", 'required');

		if ( ! $this->form_validation->run())
			return $this->gerer($this->types[$type]);

		$joueur_id = $this->input->post('joueur_id') == '' ? null : $this->input->post('joueur_id');

		// On vérifie que le membre est bien dans le clan et qu'il n'est pas chef d'un autre clan
		if (isset($joueur_id))
		{
			$query = $this->db->select('j.id')
							  ->from('politiciens p')
							  ->join('joueurs j', 'j.id = p.joueur_id')
							  ->join('clans c', 'c.chef_id = j.id', 'left')
							  ->where('c.id IS NULL')
							  ->where('j.statut', Bouzouk::Joueur_Actif)
							  ->where('p.clan_id', $this->session->userdata('clan_id')[$type])
							  ->where('p.joueur_id', $joueur_id)
							  ->get();

			if ($query->num_rows() == 0)
			{
				$this->echec("Ce bouzouk n'est pas dans ton clan");
				return $this->gerer($this->types[$type]);
			}
		}

		// On lègue le clan
		$this->lib_clans->leguer_clan($this->session->userdata('clan_id')[$type], $joueur_id);

		// On affiche un message de confirmation
		$this->succes('Tu as bien légué ton clan');
		redirect('joueur/accueil');
	}

	public function supprimer($type = '')
	{
		// Le type doit être valide et le joueur chef du clan
		if (($type = array_search($type, $this->types)) === false || ! $this->session->userdata('clan_id')[$type] || $this->session->userdata('clan_grade')[$type] != Bouzouk::Clans_GradeChef)
			show_404();

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('supprimer', "L'appui sur le bouton \"Supprimer\"", 'required');

		if ( ! $this->form_validation->run())
			return $this->gerer($this->types[$type]);

		// On vérifie que le joueur y est depuis au moins un certain temps
		$possible = $this->db->where('chef_id', $this->session->userdata('id'))
							 ->where('type', $type)
							 ->where('date_creation < (NOW() - INTERVAL '.$this->bouzouk->config('clans_temps_avant_quitter').' HOUR)')
							 ->count_all_results('clans');

		if ( ! $possible)
		{
			$this->echec('Tu dois attendre au moins <span class="pourpre">'.$this->bouzouk->config('clans_temps_avant_quitter').' heures</span> avant de pouvoir supprimer le clan');
			return $this->gerer($this->types[$type]);
		}

		// On supprime le clan
		$this->lib_clans->supprimer_clan($this->session->userdata('clan_id')[$type]);

		// On affiche un message de confirmation
		$this->succes('Tu as bien supprimé ton clan');
		redirect('joueur/accueil');
	}

	/* Accessible à tous les membres */
	public function gerer($type = '')
	{
		// Le type doit être valide et le joueur dans un clan de ce type
		if (($type = array_search($type, $this->types)) === false || ! $this->session->userdata('clan_id')[$type])
			show_404();

		// On va chercher les infos du clan
		$query = $this->db->select('c.id, j.id AS chef_id, j.pseudo AS chef_pseudo, j.rang AS chef_rang, c.nom, c.type, c.type_officiel, c.description, c.donation_chef, c.entreprise_id, c.mode_recrutement, c.points_action, c.nom_chef, c.nom_sous_chefs, c.nom_membres, c.nom_tests, c.date_creation, c.grade_lancer_actions')
						  ->from('clans c')
						  ->join('joueurs j', 'j.id = c.chef_id')
						  ->where('c.id', $this->session->userdata('clan_id')[$type])
						  ->get();

		if ($query->num_rows() == 0)
			show_404();

		$clan = $query->row();

		// On récupère les membres
		$query = $this->db->select('j.id, j.pseudo, j.rang, p.grade, p.anciennete, p.invisible, p.donation')
						  ->from('politiciens p')
						  ->join('joueurs j', 'j.id = p.joueur_id')
						  ->where('p.clan_id', $clan->id)
						  ->order_by('p.grade', 'desc')
						  ->order_by('p.anciennete', 'desc')
						  ->order_by('j.pseudo')
						  ->get();
		$membres = $query->result();

		$nb_membres_clan_actif = $this->db->where('clan_id', $clan->id)
										  ->join('joueurs AS j', 'j.id = politiciens.joueur_id')
										  ->where_in('j.statut', array(bouzouk::Joueur_Actif))
										  ->count_all_results('politiciens');

		// On récupère les actions possibles pour le clan
		$this->db->select('id, nom, description, effet, cout, image, nb_membres_min, nb_allies_min, nb_membres_allies_min, cout_par_allie')
				 ->from('clans_actions')
				 ->like('clan_type', $clan->type);

		if (isset($clan->type_officiel))
			$this->db->or_like('clan_type', $clan->type_officiel);

		$query = $this->db->order_by('clan_type')
						  ->order_by('cout')
						  ->get();
		$actions = $query->result();

		// On prépare les grades
		$grades = array(
			Bouzouk::Clans_GradeChef	 => form_prep($clan->nom_chef),
			Bouzouk::Clans_GradeSousChef => form_prep($clan->nom_sous_chefs),
			Bouzouk::Clans_GradeMembre   => form_prep($clan->nom_membres),
			Bouzouk::Clans_GradeTest     => form_prep($clan->nom_tests)
		);

		// On récupère les membres qui peuvent hériter du clan (ceux qui sont chefs d'un autre clan ne peuvent pas)
		$query = $this->db->select('j.id, j.pseudo')
						  ->from('politiciens p')
						  ->join('joueurs j', 'j.id = p.joueur_id')
						  ->join('clans c', 'c.chef_id = j.id', 'left')
						  ->where('c.id IS NULL')
						  ->where('p.clan_id', $clan->id)
						  ->where('j.statut', Bouzouk::Joueur_Actif)
						  ->order_by('j.pseudo')
						  ->get();
		$membres_heritage = $query->result();

		// On regarde si une enchère est en cours pour ce type de clan
		$query = $this->db->select('ce.montant_enchere, ce.date, ce.clan_id, ca.nom, ce.annulee, ce.parametres')
						  ->from('clans_encheres ce')
						  ->join('clans_actions ca', 'ca.id = ce.action_id')
						  ->where('ce.clan_type', $type)
						  ->order_by('ce.montant_enchere', 'desc')
						  ->limit(1)
						  ->get();
		$enchere = ($query->num_rows() == 1) ? $query->row() : null;
		$cout_surenchere = isset($enchere) ? floor($enchere->montant_enchere * $this->bouzouk->config('clans_coefficient_surenchere')) : 0;

		// On regarde si une enchère a déjà été envoyée pour ce clan là
		$query = $this->db->select('ca.nom, ca.id, ce.parametres')
						  ->from('clans_encheres ce')
						  ->join('clans_actions ca', 'ca.id = ce.action_id')
						  ->where('ce.clan_type', $type)
						  ->where('ce.clan_id', $clan->id)
						  ->order_by('ce.montant_enchere', 'desc')
						  ->limit(1)
						  ->get();
		$enchere_clan = ($query->num_rows() == 1) ? $query->row() : null;

		// On regarde si une demande d'alliance a été envoyée au clan
		$query = $this->db->select('c.nom AS nom_clan, ca.nom AS nom_action, caa.id, ca.image')
						  ->from('clans_actions_allies caa')
						  ->join('clans c', 'c.id = caa.clan_createur_id')
						  ->join('clans_actions ca', 'ca.id = caa.action_id')
						  ->where('caa.clan_invite_id', $clan->id)
						  ->where('caa.statut', Bouzouk::Clans_AllianceAttente)
						  ->get();
		$alliances = $query->result();

		// On cherche l'action de l'enchère du clan pour griser le bouton 'enchérir' ou pas
		$action_surenchere = null;

		if (isset($enchere_clan))
		{
			foreach ($actions as $action)
			{
				if ($action->id == $enchere_clan->id)
					$action_surenchere = $action;
			}
		}

		// ---------- Hook clans ----------
		// Corruption à agent (Struleone)
		// Pillage compulsif (Organisation)
		// Concurrence gênante (Organisation)
		// Malediction du Schnibble (SDS)
		// -> on arrête l'action si elle a dépassé sa durée
		$query = $this->db->set('statut', Bouzouk::Clans_ActionTerminee)
						  ->where_in('action_id', array(25, 38, 39, 40))
						  ->where('statut', Bouzouk::Clans_ActionEnCours)
						  ->where('date_debut < (NOW() - INTERVAL duree HOUR)')
						  ->update('clans_actions_lancees');
		
		// On récupère les actions lancées du clan
		$query = $this->db->select('ca.nom, ca.effet, caa.date_debut, ca.image, caa.statut, caa.cout, caa.parametres')
						  ->from('clans_actions_lancees caa')
						  ->join('clans_actions ca', 'ca.id = caa.action_id')
						  ->where('clan_id', $clan->id)
						  ->order_by('caa.date_debut', 'desc')
						  ->limit(10)
						  ->get();
		$actions_lancees = $query->result();

		// On regarde si le clan a un forum
		$query = $this->db->select('forum_desc, clan_mode, id')
						  ->from('tobozon_forums')
						  ->where('clan_id', $clan->id)
						  ->get();
		$forum = $query->num_rows() > 0 ? $query->row() : null;

		// On récupère les derniers sujets du tobozon
		if (isset($forum))
		{
			$query = $this->db->select('p.posted AS date, p.poster AS pseudo, p.poster_id AS joueur_id, p.id, t.subject AS sujet')
							  ->from('tobozon_topics t')
							  ->join('tobozon_posts p', 'p.id = t.last_post_id')
							  ->where('t.forum_id', $forum->id)
							  ->order_by('t.last_post', 'desc')
							  ->limit(7)
							  ->get();
			$tobozon = $query->result();
		}

		// On affiche la page du clan
		$vars = array(
			'clan'              => $clan,
			'membres'           => $membres,
			'nb_membres'        => count($membres),
			'nb_membres_actifs'	=> $nb_membres_clan_actif,
			'enchere'           => $enchere,
			'enchere_clan'      => $enchere_clan,
			'action_surenchere' => $action_surenchere,
			'alliances'         => $alliances,
			'actions'           => $actions,
			'modes_recrutement' => $this->modes_recrutement,
			'statistiques'      => $this->statistiques,
			'types'             => $this->types,
			'grades'            => $grades,
			'cout_surenchere'   => $cout_surenchere,
			'membres_heritage'  => $membres_heritage,
			'espionnage'        => $this->lib_clans->espionnage_en_cours($clan->id),
			'actions_lancees'   => $actions_lancees,
			'forum'             => $forum,
			'tobozon'           => isset($tobozon) ? $tobozon : array()
		);
		return $this->layout->view('clans/gerer', $vars);
	}

	public function espionnage($type = '')
	{
		// Le type doit être valide, le joueur dans un clan de ce type
		if (($type = array_search($type, $this->types)) === false || ! $this->session->userdata('clan_id')[$type])
			show_404();

		$clan = $this->lib_clans->get_clan($this->session->userdata('clan_id')[$type]);
		$espionnage = $this->lib_clans->espionnage_en_cours($clan->id);

		if ( ! isset($espionnage))
			show_404();

		// On affiche la page
		$vars = array(
			'clan'       => $clan,
			'types'      => $this->types,
			'espionnage' => $espionnage
		);
		return $this->layout->view('clans/espionnage', $vars);
	}

	public function donation($type = '')
	{
		// Le type doit être valide et le joueur dans un clan de ce type
		if (($type = array_search($type, $this->types)) === false || ! $this->session->userdata('clan_id')[$type])
			show_404();

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('points', 'Le nombre de points', 'required|is_natural_no_zero');
		
		if ( ! $this->form_validation->run())
			return $this->gerer($this->types[$type]);

		// On vérifie que le joueur a assez de points
		if ($this->session->userdata($this->statistiques[$type][0]) < $this->input->post('points'))
		{
			$this->echec("Tu n'as pas assez de points de ".$this->statistiques[$type][0].' pour en donner autant');
			return $this->gerer($this->types[$type]);
		}

		// On enlève les points au joueur
		$this->db->set('`'.$this->statistiques[$type][0].'`', '`'.$this->statistiques[$type][0].'`-'.$this->input->post('points'), false)
				 ->where('id', $this->session->userdata('id'))
				 ->update('joueurs');

		// On ajoute les points au clan
		$this->db->set('points_action', 'points_action+'.$this->input->post('points'), false)
				 ->where('id', $this->session->userdata('clan_id')[$type])
				 ->update('clans');

		// On ajoute les points au total de donation du chef
		if ($this->session->userdata('clan_grade')[$type] == Bouzouk::Clans_GradeChef)
		{
			$this->db->set('donation_chef', 'donation_chef+'.$this->input->post('points'), false)
					 ->where('chef_id', $this->session->userdata('id'))
					 ->where('id', $this->session->userdata('clan_id')[$type])
					 ->update('clans');
		}

		// Si c'est un membre
		else
		{
			$this->db->set('donation', 'donation+'.$this->input->post('points'), false)
					 ->where('joueur_id', $this->session->userdata('id'))
					 ->where('clan_id', $this->session->userdata('clan_id')[$type])
					 ->update('politiciens');

			// Notif au chef si il est connecté
			$clan = $this->lib_clans->get_clan($this->session->userdata('clan_id')[$type]);

			$this->load->library('lib_notifications');
			if ($this->lib_notifications->notifier(Bouzouk::Notification_DonMembreClan, $clan->chef_id))
				$this->bouzouk->notification(30, array(profil(-1, '', $this->session->userdata('rang')), couleur(pluriel($this->input->post('points'), 'point')), $this->statistiques[$type][1], couleur(form_prep($clan->nom))), $clan->chef_id);

		}

		// On ajoute à l'historique
		$this->lib_clans->historique(profil().' a fait une donation de '.couleur(pluriel($this->input->post('points'), 'point').' '.$this->statistiques[$type][1]).' au clan', $this->session->userdata('clan_id')[$type]);

		// On affiche une confirmation
		$this->succes('Tu as bien donné <span class="pourpre">'.pluriel($this->input->post('points'), 'point').' '.$this->statistiques[$type][1].'</span> à ton clan');
		redirect('clans/gerer/'.$this->types[$type]);
	}

	/* Accesible uniquement aux sous-chefs */
	public function modifier_membres($type = '')
	{
		// Le type doit être valide, le joueur sous-chef dans le clan
		if (($type = array_search($type, $this->types)) === false || ! $this->session->userdata('clan_id')[$type] || $this->session->userdata('clan_grade')[$type] < Bouzouk::Clans_GradeSousChef)
			show_404();

		$this->load->library('form_validation');
		$this->form_validation->set_rules('ids', 'Les membres', 'required');
		$this->form_validation->set_rules('grades', 'Les grades', 'required');
		
		if ( ! $this->form_validation->run())
			return $this->gerer($this->types[$type]);

		// On récupère les membres du clan
		$query = $this->db->select('joueur_id, invisible, grade')
						  ->from('politiciens')
						  ->where_in('joueur_id', $this->input->post('ids'))
						  ->where('clan_id', $this->session->userdata('clan_id')[$type])
						  ->get();

		// On vérifie que les membres en POST sont les bien tous dans le clan
		if ($query->num_rows() != count($this->input->post('ids')))
		{
			$this->echec('Les membres à modifier sont invalides');
			return $this->gerer($this->types[$type]);
		}

		$membres          = $query->result();
		$grades_autorises = array(Bouzouk::Clans_GradeSousChef, Bouzouk::Clans_GradeMembre, Bouzouk::Clans_GradeTest);
		$membres_vires    = array();
		$membres_modifies = array();

		// On parcourt tous les membres envoyés en post
		foreach ($membres as $membre)
		{
			// Si le grade du modifieur et du modifié sont égaux, on passe
			if ($membre->grade == $this->session->userdata('clan_grade')[$type])
				continue;

			$grade     = $this->input->post('grades')[$membre->joueur_id];
			$invisible = isset($this->input->post('invisibles')[$membre->joueur_id]) ? '1' : '0';
			$virer     = isset($this->input->post('virer')[$membre->joueur_id]);

			// Si le membre doit être viré
			if ($virer)
			{
				// Si le sous-chef se vire lui-même, on passe
				if ($membre->joueur_id == $this->session->userdata('id'))
					continue;

				$this->db->where('joueur_id', $membre->joueur_id)
						 ->where('clan_id', $this->session->userdata('clan_id')[$type])
						 ->delete('politiciens');

				$membres_vires[] = profil($membre->joueur_id);

				// On met à jour la session
				$this->bouzouk->augmente_version_session($membre->joueur_id);
			}

			// Si le grade ou l'invisibilité a changé, on met à jour
			else if ($membre->grade != $grade || $membre->invisible != $invisible)
			{
				// On vérifie que le grade est valide
				if ( ! in_array($grade, $grades_autorises))
				{
					$this->echec("Ce grade n'existe pas");
					return $this->gerer($this->types[$type]);
				}

				$this->db->set('grade', $grade)
						 ->set('invisible', $invisible)
						 ->where('joueur_id', $membre->joueur_id)
						 ->where('clan_id', $this->session->userdata('clan_id')[$type])
						 ->update('politiciens');

				$membres_modifies[] = profil($membre->joueur_id);

				// On met à jour la session
				$this->bouzouk->augmente_version_session($membre->joueur_id);
			} 
		}

		// On ajoute à l'historique du clan
		if (count($membres_vires) > 0)
			$this->lib_clans->historique(profil(-1, '', $this->session->userdata('rang')).' a viré : '.implode(', ', $membres_vires), $this->session->userdata('clan_id')[$type]);

		if (count($membres_modifies) > 0)
			$this->lib_clans->historique(profil(-1, '', $this->session->userdata('rang')).' a modifié : '.implode(', ', $membres_modifies), $this->session->userdata('clan_id')[$type]);

		// On affiche un message de confirmation
		$this->succes('Les membres ont bien été modifiés');
		return $this->gerer($this->types[$type]);
	}

	public function historique($type = '', $offset = '0')
	{
		// Le type doit être valide et le joueur sous-chef dans ce clan
		if (($type = array_search($type, $this->types)) === false || ! $this->session->userdata('clan_id')[$type] || $this->session->userdata('clan_grade')[$type] < Bouzouk::Clans_GradeSousChef)
			show_404();

		// Pagination
		$nb_lignes_historique = $this->db->where('clan_id', $this->session->userdata('clan_id')[$type])
										 ->count_all_results('historique_clans');
		$pagination = creer_pagination('clans/historique/'.$this->types[$type], $nb_lignes_historique, 50, $offset, 4);

		// On va chercher l'historique du clan
		$query = $this->db->select('texte, date')
						  ->from('historique_clans')
						  ->where('clan_id', $this->session->userdata('clan_id')[$type])
						  ->order_by('id', 'desc')
  						  ->limit($pagination['par_page'], $pagination['offset'])
						  ->get();
		$historique = $query->result();

		// On va chercher les infos du clan
		$clan = $this->lib_clans->get_clan($this->session->userdata('clan_id')[$type]);

		// On affiche
		$vars = array(
			'historique' => $historique,
			'clan'       => $clan,
			'types'      => $this->types,
			'pagination' => $pagination['liens'],
			'espionnage' => $this->lib_clans->espionnage_en_cours($clan->id)
		);
		return $this->layout->view('clans/historique', $vars);
	}

	/* Accessible uniquement au chef (voire aux sous-chefs selon l'option choisie) */
	public function valider_alliance($type = '')
	{
		// Le type doit être valide
		if (($type = array_search($type, $this->types)) === false || ! $this->session->userdata('clan_id')[$type])
			show_404();

		// On récupère le clan
		$clan = $this->lib_clans->get_clan($this->session->userdata('clan_id')[$type]);
		
		// Le joueur doit avoir le droit de gérer les actions
		if( ! $this->session->userdata('clan_grade')[$type] >= $clan->grade_lancer_actions)
			show_404();

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('alliance_id', "L'alliance", 'required|is_natural_no_zero');
		$this->form_validation->set_rules('decision', 'La décision', 'required|regex_match[#^accepter|refuser$#]');
		
		if ( ! $this->form_validation->run())
			return $this->gerer($this->types[$type]);
		
		// On modifie la demande d'alliance
		$statut = $this->input->post('decision') == 'accepter' ? Bouzouk::Clans_AllianceAcceptee : Bouzouk::Clans_AllianceRefusee;

		$this->db->set('statut', $statut)
				 ->where('clan_invite_id', $this->session->userdata('clan_id')[$type])
				 ->where('id', $this->input->post('alliance_id'))
				 ->where('statut', Bouzouk::Clans_AllianceAttente)
				 ->update('clans_actions_allies');

		if ($this->db->affected_rows() == 0)
		{
			$this->echec("Aucune demande d'alliance ne t'a été envoyée");
			return $this->gerer($this->types[$type]);
		}

		// On affiche un message de confirmation
		if ($statut == Bouzouk::Clans_AllianceAcceptee)
			$this->succes('Tu as bien accepté cette alliance');

		else
			$this->succes('Tu as bien refusé cette alliance');

		return $this->gerer($this->types[$type]);
	}

	public function lancer_action($type = '')
	{
		// Le type doit être valide
		if (($type = array_search($type, $this->types)) === false || ! $this->session->userdata('clan_id')[$type])
			show_404();

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('action_id', "L'action", 'required|is_natural_no_zero');
		$this->form_validation->set_rules('cout', "Le coût de l'action", 'is_natural_no_zero');

		if ( ! $this->form_validation->run())
			return $this->gerer($this->types[$type]);

		// On récupère le clan
		$clan = $this->lib_clans->get_clan($this->session->userdata('clan_id')[$type]);
		
		// Le joueur doit avoir le droit de gérer les actions
		if( ! $this->session->userdata('clan_grade')[$type] >= $clan->grade_lancer_actions)
			show_404();

		// On récupère l'action
		$query = $this->db->select('id, nom, effet, cout, clan_type, nb_membres_min, nb_allies_min, nb_membres_allies_min')
				 ->from('clans_actions')
				 ->where('id', $this->input->post('action_id'))
				 ->get();
		
		// Si l'action n'existe pas
		if ($query->num_rows() == 0)
		{
			$this->echec("Cette action n'existe pas");
			return $this->gerer($this->types[$type]);
		}

		$action = $query->row();

		// On regarde si une enchère est en cours
		$query = $this->db->select('montant_enchere, date, clan_id, annulee')
						  ->from('clans_encheres')
						  ->where('clan_type', $type)
						  ->order_by('montant_enchere', 'desc')
						  ->limit(1)
						  ->get();
			
		$enchere = ($query->num_rows() == 1) ? $query->row() : null;

		// On vérifie que le clan a accès à cette action
		if (strpos($clan->type, $action->clan_type) === false && strpos($clan->type_officiel, $action->clan_type) === false)
		{
			$this->echec("Le clan n'a pas accès à cette action");
			return $this->gerer($this->types[$type]);
		}

		$nb_membres_clan_actif = $this->db->where('clan_id', $clan->id)
										  ->join('joueurs AS j', 'j.id = politiciens.joueur_id')
										  ->where_in('j.statut', array(bouzouk::Joueur_Actif))
										  ->count_all_results('politiciens');

		// On vérifie que l'action est possible selon plusieurs paramètres
		if (($erreur = $this->lib_clans->action_possible($action, $clan, $nb_membres_clan_actif, $enchere)) !== true)
		{
			$this->echec($erreur);
			return $this->gerer($this->types[$type]);
		}

		// On récupère les paramètres POST
		if (($parametres = $this->lib_clans->get_action_post_parametres($action->id, $clan->id)) === false)
			return $this->gerer($this->types[$type]);

		// Action directe
		if ($action->effet == Bouzouk::Clans_EffetDirect)
		{
			$nb_points_clan = $this->lib_clans->points_action_disponibles($clan->id, true);

			// On vérifie que le clan a assez de points
			if ($nb_points_clan < $action->cout)
			{
				$this->echec('Le clan a '.couleur($nb_points_clan." points d'action").' disponibles, il faut au moins '.couleur($action->cout." points d'action")." pour lancer cette action");
				return $this->gerer($this->types[$type]);
			}

			// On retire les points d'action du clan
			$this->lib_clans->retirer_points_action($clan->id, $action->cout);

			// On lance l'action
			$this->lib_clans->lancer_action($clan->id, $action->id, $parametres, $action->cout);

			// On affiche une confirmation
			$this->succes('Tu as bien lancé cette action');
			return $this->gerer($this->types[$type]);
		}

		// Action différée
		else
		{
			$cout_action = $action->cout;
			$nb_points_clan = $this->lib_clans->points_action_disponibles($clan->id);

			// On vérifie que la dernière enchère n'est pas de ce clan
			if (isset($enchere) && $enchere->clan_id == $clan->id)
			{
				$this->echec('Tu as déjà la meileure enchère en cours, tu dois attendre une surenchère pour lancer une autre action');
				return $this->gerer($this->types[$type]);
			}

			// On vérifie que le clan assez de points pour proposer un tel cout
			if ($nb_points_clan < $this->input->post('cout'))
			{
				$this->echec("Le clan n'a que ".couleur($nb_points_clan." points d'action").' disponibles');
				return $this->gerer($this->types[$type]);
			}

			// Si une enchère existe, le cout change
			if (isset($enchere))
				$cout_action = floor($enchere->montant_enchere * $this->bouzouk->config('clans_coefficient_surenchere'));

			// On vérifie que le clan a assez de points
			if ($nb_points_clan < $cout_action)
			{
				$this->echec('Le clan a '.couleur($nb_points_clan." points d'action").' disponibles, il faut au moins '.couleur($cout_action." points d'action")." pour lancer cette action");
				return $this->gerer($this->types[$type]);
			}

			// On vérifie que le montant proposé est au moins celui du coût minimum
			if ($this->input->post('cout') < $cout_action)
			{
				$this->echec("Tu dois proposer un coût minimum de ".couleur($cout_action." points d'action").' pour lancer cette action');
				return $this->gerer($this->types[$type]);
			}

			// On prévient le chef de la dernière enchère qu'une surenchère a été faite
			if (isset($enchere))
				$this->prevenir_chefs($this->input->post('cout'), $enchere->clan_id);

			// On enregistre l'enchère
			$this->lib_clans->encherir_action($clan->id, $action->id, $this->input->post('cout'), $parametres);
			
			// On ajoute à l'historique du clan
			$this->lib_clans->historique(profil()." a lancé l'enchère de l'action <span class='pourpre'>".$action->nom.'</span> pour '.$this->input->post('cout').' p.a.', $clan->id);
			
			// On affiche une confirmation
			$this->succes('Tu as bien enchéri pour cette action');
			return $this->gerer($this->types[$type]);
		}
	}

	public function surencherir_action($type = '')
	{
		// Le type doit être valide
		if (($type = array_search($type, $this->types)) === false || ! $this->session->userdata('clan_id')[$type])
			show_404();

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('cout', "Le coût de l'action", 'is_natural_no_zero');

		if ( ! $this->form_validation->run())
			return $this->gerer($this->types[$type]);

		// On récupère le clan
		$clan = $this->lib_clans->get_clan($this->session->userdata('clan_id')[$type]);
		
		// Le joueur doit avoir le droit de gérer les actions
		if( ! $this->session->userdata('clan_grade')[$type] >= $clan->grade_lancer_actions)
			show_404();
		
		// On récupère la dernière enchère
		$query = $this->db->select('montant_enchere, clan_id, action_id, parametres, annulee')
						  ->from('clans_encheres')
						  ->where('clan_type', $type)
						  ->order_by('montant_enchere', 'desc')
						  ->limit(1)
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Il n'y a pas d'enchère en cours");
			return $this->gerer($this->types[$type]);
		}

		$enchere = $query->row();

		// On recupère la dernière enchère du clan
		$query = $this->db->select('montant_enchere, clan_id, action_id, parametres')
						  ->from('clans_encheres')
						  ->where('clan_type', $type)
						  ->where('clan_id', $this->session->userdata('clan_id')[$type])
						  ->order_by('montant_enchere', 'desc')
						  ->limit(1)
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Tu n'as encore fait aucune enchère, tu ne peux pas surenchérir");
			return $this->gerer($this->types[$type]);
		}

		$enchere_clan = $query->row();
		$nb_points_clan = $this->lib_clans->points_action_disponibles($clan->id);
		$cout_surenchere = floor($enchere->montant_enchere * $this->bouzouk->config('clans_coefficient_surenchere'));

		// On récupère l'action
		$query = $this->db->select('id, nom, description, effet, cout, image, nb_membres_min, nb_allies_min, nb_membres_allies_min')
						  ->from('clans_actions')
						  ->where('id', $enchere_clan->action_id)
						  ->get();
		$action = $query->row();

		$nb_membres_clan_actif = $this->db->where('clan_id', $clan->id)
										  ->join('joueurs AS j', 'j.id = politiciens.joueur_id')
										  ->where_in('j.statut', array(bouzouk::Joueur_Actif))
										  ->count_all_results('politiciens');

		// On vérifie que l'action est possible selon plusieurs paramètres
		if (($erreur = $this->lib_clans->action_possible($action, $clan, $nb_membres_clan_actif, $enchere)) !== true)
		{
			$this->echec($erreur);
			return $this->gerer($this->types[$type]);
		}

		// On vérifie que le clan assez de points pour proposer un tel cout
		if ($nb_points_clan < $this->input->post('cout'))
		{
			$this->echec("Le clan n'a que ".couleur($nb_points_clan." points d'action").' disponibles');
			return $this->gerer($this->types[$type]);
		}

		// On vérifie que le montant proposé est au moins celui du coût minimum
		if ($this->input->post('cout') < $cout_surenchere)
		{
			$this->echec("Tu dois proposer un coût minimum de ".couleur($cout_surenchere." points d'action").' pour lancer cette action');
			return $this->gerer($this->types[$type]);
		}

		// On prévient le chef de la dernière enchère qu'une surenchère a été faite
		$this->prevenir_chefs($this->input->post('cout'), $enchere->clan_id);

		// On enregistre l'enchère
		$this->lib_clans->encherir_action($this->session->userdata('clan_id')[$type], $enchere_clan->action_id, $this->input->post('cout'), unserialize($enchere_clan->parametres));
		
		// On ajoute à l'historique du clan
		$this->lib_clans->historique(profil()." a surenchérit avec l'action <span class='pourpre'>".$action->nom.'</span> pour '.$this->input->post('cout').' p.a.', $clan->id);

		// On affiche une confirmation
		$this->succes('Tu as bien surenchérit pour ton action pour un montant de '.couleur($this->input->post('cout')." points d'action"));
		return $this->gerer($this->types[$type]);
	}

	/* Factorisation de code pour le lancement/la surenchère d'action */
	private function prevenir_chefs($cout, $clan_adverse_id)
	{
		// On prévient le chef de la dernière enchère qu'une surenchère a été faite
		$clan_adverse = $this->lib_clans->get_clan($clan_adverse_id);
		$this->bouzouk->notification(31, array(couleur($cout.' p.a')), $clan_adverse->chef_id);

		// Si des sous-gradés peuvent gérer les actions, on les prévient aussi
		if ($clan_adverse->grade_lancer_actions < 4)
		{
			// On récupère les sous-gradés
			$query = $this->db->select('j.id')
							  ->from('politiciens p')
							  ->join('joueurs j', 'j.id = p.joueur_id')
							  ->where('p.clan_id', $clan_adverse_id)
							  ->where('grade >=', $clan_adverse->grade_lancer_actions)
							  ->get();
			
			foreach ($query->result() as $sous_chef)
				$this->bouzouk->notification(226, array(site_url('clans/gerer/'.$this->types[$clan_adverse->type]), couleur($cout.' p.a')), $sous_chef->id);
		}
	}

	public function annuler_action($type = '')
	{
		// Le type doit être valide
		if (($type = array_search($type, $this->types)) === false || ! $this->session->userdata('clan_id')[$type])
			show_404();

		// On récupère le clan
		$clan = $this->lib_clans->get_clan($this->session->userdata('clan_id')[$type]);
		
		// Le joueur doit avoir le droit de gérer les actions
		if( ! $this->session->userdata('clan_grade')[$type] >= $clan->grade_lancer_actions)
			show_404();

		// On va chercher la dernière enchère de ce type de clan
		$query = $this->db->select('ce.id, ce.clan_id, ce.annulee, ce.date, ca.nom')
						  ->from('clans_encheres ce')
						  ->join('clans_actions ca', 'ca.id = ce.action_id')
						  ->where('ce.clan_type', $type)
						  ->order_by('ce.montant_enchere', 'desc')
						  ->limit(1)
						  ->get();
			
		// Si pas d'enchère
		if ($query->num_rows() == 0)
		{
			$this->echec("Il n'y a pas d'enchère en cours");
			return $this->gerer($this->types[$type]);
		}

		$enchere = $query->row();

		// On vérifie que l'enchère vient bien de ce clan
		if ($enchere->clan_id != $this->session->userdata('clan_id')[$type])
		{
			$this->echec("La dernière enchère n'est pas de toi");
			return $this->gerer($this->types[$type]);
		}
		
		// 2h après la dernière enchère uniquement
		$heures_annulation = $this->lib_clans->heures_annulation($enchere->date);
		if (date('H:i') < $heures_annulation[0] || date('H:i') > $heures_annulation[1])
		{
			$this->echec('Tu dois attendre entre <span class="pourpre">'.$heures_annulation[0].'</span> et <span class="pourpre">'.$heures_annulation[1].'</span> pour annuler ton action si elle a gagné les enchères');
			return $this->gerer($this->types[$type]);
		}

		// Si l'enchère est déjà annulée
		if ($enchere->annulee)
		{
			$this->echec('Ton enchère a déjà été annulée');
			return $this->gerer($this->types[$type]);
		}

		// On annule l'enchère
		$this->db->set('annulee', 1)
				 ->where('id', $enchere->id)
				 ->update('clans_encheres');
		
		// On ajoute à l'historique du clan
		$this->lib_clans->historique(profil()." a annulé l'action <span class='pourpre'>".$enchere->nom.'</span>.', $clan->id);

		// On affiche un message de confirmation
		$this->succes("Tu as bien annulé ton enchère, les points d'action seront quand même retirés à la maintenance mais l'action ne sera pas lancée");
		return $this->gerer($this->types[$type]);
	}
	
	/* Accessible uniquement au chef */
	public function modifier($type = '')
	{
		// Le type doit être valide et chef dans ce clan
		if (($type = array_search($type, $this->types)) === false || ! $this->session->userdata('clan_id')[$type] || $this->session->userdata('clan_grade')[$type] != Bouzouk::Clans_GradeChef)
			show_404();

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('nom', 'Le nom du clan', 'required|min_length[3]|max_length[35]');
		$this->form_validation->set_rules('mode_recrutement', 'Le mode de recrutement', 'required|is_natural_no_zero|callback__mode_recrutement_check');
		$this->form_validation->set_rules('nom_chef', 'Le nom du chef', 'required|min_length[3]|max_length[20]');
		$this->form_validation->set_rules('nom_sous_chefs', 'Le nom des sous chefs', 'required|min_length[3]|max_length[20]');
		$this->form_validation->set_rules('nom_membres', 'Le nom des membres', 'required|min_length[3]|max_length[20]');
		$this->form_validation->set_rules('nom_tests', 'Le nom des membres test', 'required|min_length[3]|max_length[20]');
		$this->form_validation->set_rules('description', 'La description', 'required|max_length[250]|callback__description_check');
		$this->form_validation->set_rules('forum_mode', 'Le mode du forum', 'required|is_natural|less_than_or_equal[3]');
		$this->form_validation->set_rules('forum_description', 'La description du forum', 'max_length[50]');
		$this->form_validation->set_rules('grade_lancer_actions', 'Le grade minimum pour lancer des actions', 'required|is_natural_no_zero|less_than_or_equal[4]');

		if ( ! $this->form_validation->run())
			return $this->gerer($this->types[$type]);

		// On récupère le clan
		$clan = $this->lib_clans->get_clan($this->session->userdata('clan_id')[$type]);

		// Si le nom a changé
		if ($clan->nom != $this->input->post('nom'))
		{
			// On vérifie que le chef a assez de struls
			if ($this->session->userdata('struls') < $this->bouzouk->config('clans_struls_renommer'))
			{
				$this->echec("Tu n'as pas assez de struls pour renommer ton clan");
				return $this->gerer($this->types[$type]);
			}

			// On vérifie que le nom n'existe pas déjà
			$existe = $this->db->where('nom', $this->input->post('nom'))
							   ->count_all_results('clans');

			if ($existe)
			{
				$this->echec('Un clan existe déjà avec ce nom là, tu dois en choisir un autre');
				return $this->gerer($this->types[$type]);
			}

			// On retire les struls
			$this->bouzouk->retirer_struls($this->bouzouk->config('clans_struls_renommer'));

			// Si un forum existe, on change son nom
			$this->db->set('forum_name', $this->input->post('nom'))
					 ->where('clan_id', $clan->id)
					 ->update('tobozon_forums');

			// On change le nom du clan
			$this->db->set('nom', $this->input->post('nom'))
					 ->where('id', $clan->id)
					 ->update('clans');

			// On ajoute à l'historique du chef
			$this->bouzouk->historique(25, null, array(couleur(form_prep($clan->nom)), couleur(form_prep($this->input->post('nom'))), struls($this->bouzouk->config('clans_struls_renommer'))));
		}

		$this->load->library('lib_tobozon');

		// On regarde si un forum de clan existe déjà
		$query = $this->db->select('id, clan_mode, forum_desc')
						  ->from('tobozon_forums')
						  ->where('clan_id', $clan->id)
						  ->get();

		// Le forum existe
		if ($query->num_rows() > 0)
		{
			$forum = $query->row();

			// La suppression est demandée
			if ($this->input->post('forum_mode') == 0)
			{
				$this->lib_tobozon->supprimer_forum($forum->id);

				// On remet le chef dans le groupe des bouzouks, sauf s'il est déjà modérateur ou admin
				$query = $this->db->select('tu.id')
								  ->from('tobozon_users tu')
								  ->join('tobozon_groups tg', 'tg.g_id = tu.group_id')
								  ->where('tu.id', $this->session->userdata('id'))
								  ->where('tg.g_id != 14')
								  ->where('(tg.g_moderator = 1 OR tg.g_id = 1)')
								  ->group_by('tu.id')
								  ->get();

				if ($query->num_rows() == 0)
				{
					$this->db->set('group_id', Bouzouk::Tobozon_IdGroupeBouzouks)
							 ->where('id', $this->session->userdata('id'))
							 ->update('tobozon_users');
				}
			}

			// Le chef veut juste changer le mode/et ou la description
			else if ($this->input->post('forum_mode') != $forum->clan_mode || $this->input->post('forum_description') != $forum->forum_desc)
			{
				// On vérifie que le chef a assez de struls
				if ($this->session->userdata('struls') < $this->bouzouk->config('clans_struls_modifier_forum'))
				{
					$this->echec("Tu n'as pas assez de struls pour modifier ton forum");
					return $this->gerer($this->types[$type]);
				}

				// On retire les struls
				$this->bouzouk->retirer_struls($this->bouzouk->config('clans_struls_modifier_forum'));

				// On ajoute à l'historique du chef
				$this->bouzouk->historique(26, null, array(couleur(form_prep($clan->nom)), struls($this->bouzouk->config('clans_struls_modifier_forum'))));

				// On met à jour le mode et la description
				$this->db->set('clan_mode', $this->input->post('forum_mode'))
						 ->set('forum_desc', $this->input->post('forum_description'))
						 ->where('id', $forum->id)
						 ->update('tobozon_forums');

				$this->lib_tobozon->update_moderateurs_clans($forum->id, $this->input->post('forum_mode'), $this->session->userdata('id'), $this->session->userdata('pseudo'));
			}
		}

		// Pas encore de forum
		else
		{
			// La création est demandée
			if ($this->input->post('forum_mode') > 0)
			{
				// On vérifie que le chef a assez de struls
				if ($this->session->userdata('struls') < $this->bouzouk->config('clans_struls_creer_forum'))
				{
					$this->echec("Tu n'as pas assez de struls pour créer un forum pour ton clan");
					return $this->gerer($this->types[$type]);
				}

				// On retire les struls
				$this->bouzouk->retirer_struls($this->bouzouk->config('clans_struls_creer_forum'));

				// On ajoute à l'historique du chef
				$this->bouzouk->historique(27, null, array(couleur(form_prep($clan->nom)), struls($this->bouzouk->config('clans_struls_creer_forum'))));

				// On créé le forum
				$data_tobozon_forums = array(
					'forum_name' => $clan->nom,
					'forum_desc' => $this->input->post('forum_description'),
					'cat_id'     => Bouzouk::Tobozon_IdCategorieClansNonOfficiels,
					'clan_id'    => $clan->id,
					'clan_mode'  => $this->input->post('forum_mode')
				);
				$this->db->insert('tobozon_forums', $data_tobozon_forums);
				$forum_id = $this->db->insert_id();

				$this->lib_tobozon->update_moderateurs_clans($forum_id, $this->input->post('forum_mode'), $this->session->userdata('id'), $this->session->userdata('pseudo'));
			}
		}

		// On enregistre les infos du clan
		$this->db->set('nom', $this->input->post('nom'))
				 ->set('mode_recrutement', $this->input->post('mode_recrutement'))
				 ->set('nom_chef', $this->input->post('nom_chef'))
				 ->set('nom_sous_chefs', $this->input->post('nom_sous_chefs'))
				 ->set('nom_membres', $this->input->post('nom_membres'))
				 ->set('nom_tests', $this->input->post('nom_tests'))
				 ->set('description', $this->input->post('description'))
				 ->set('grade_lancer_actions', $this->input->post('grade_lancer_actions'))
				 ->where('id', $this->session->userdata('clan_id')[$type])
				 ->update('clans');

		// On affiche dans l'historique du clan
		$this->lib_clans->historique(profil($clan->chef_id).' a modifié le clan', $clan->id);

		// On affiche un message de confirmation
		$this->succes('Le clan a bien été modifié');
		return $this->gerer($this->types[$type]);
	}
}
