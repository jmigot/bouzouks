<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : jeu de cartes
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : novembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Plouk extends MY_Controller
{
	private $chronos = array(
		15  => '15sec',
		30  => '30sec',
		45  => '45sec',
		60  => '1min',
		75  => '1min 15sec',
		90  => '1min 30sec',
		105 => '1min 45sec',
		120 => '2min',
		180 => '3min',
	);

	public function __construct()
	{
		parent::__construct();
			
		// Si interdit de Plouk
		if ($this->session->userdata('interdit_plouk') && $this->methode != 'interdit_plouk')
			redirect('plouk/interdit_plouk');
			
		$this->load->library('lib_plouk');

		// Toutes les 10 secondes on lance un nettoyage
		if ($this->lib_cache->nettoyage_plouk())
			$this->nettoyer();
	}

	public function interdit_plouk()
	{
		if ( ! $this->session->userdata('interdit_plouk'))
		{
			return $this->index();
		}

		// On affiche l'interdiction
		$vars = array(
			'titre_layout' => 'Plouk',
			'titre'        => 'Interdit de Plouk',
			'image_url'    => 'plouk/banniere.jpg',
			'message'      => 'Tu as été interdit de jouer au Plouk par un modérateur ou un administrateur'
		);
		return $this->layout->view('blocage', $vars);
	}
	
	public function index()
	{
		// On va chercher la liste des parties en attente
		$query = $this->db->select('id, createur_id, createur_pseudo, objet_nom, nb_tours, chrono, charisme, mediatisation, partisans, tchat, mot_de_passe')
						  ->from('plouk_parties')
						  ->where('statut', Lib_plouk::Proposee)
						  ->get();
		$parties_attente = $query->result();

		// On va chercher la liste des parties en cours
		$query = $this->db->select('pp.id, pp.createur_id, pp.createur_pseudo, pp.adversaire_id, pp.adversaire_pseudo, pp.objet_nom, pp.nb_tours, pp.tour_actuel, pp.tchat, pp.mot_de_passe, COUNT(pc.id) AS nb_connectes')
						  ->from('plouk_parties pp')
						  ->join('plouk_connectes pc', 'pc.partie_id = pp.id AND pc.derniere_visite >= (NOW() - INTERVAL 10 SECOND)', 'left')
						  ->where_in('pp.statut', array(Lib_plouk::Attente, Lib_plouk::EnCours))
						  ->group_by('pp.id')
						  ->get();
		$parties_en_cours = $query->result();

		// On affiche les résultats
		$vars = array(
			'chronos'          => $this->chronos,
			'parties_attente'  => $parties_attente,
			'parties_en_cours' => $parties_en_cours
		);
		return $this->layout->view('plouk/index', $vars);
	}

	public function _chrono_check($chrono)
	{
		if ( ! in_array($chrono, array_keys($this->chronos)))
		{
			$this->form_validation->set_message('_chrono_check', '%s est invalide');
			return false;
		}

		return true;
	}

	public function creer()
	{
		// Le joueur ne doit pas être en cours de partie
		if ($this->session->userdata('plouk_id'))
		{
			$this->echec('Tu es déjà en train de jouer une partie');
			return $this->index();
		}

		// On va chercher les objets disponibles à mettre en jeu
		$objets = $this->get_objets($this->session->userdata('id'));

		$vars = array(
			'chronos' => $this->chronos,
			'objets'  => $objets
		);

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('nb_tours', 'Le nombre de tours', 'required|greater_than_or_equal[10]|less_than_or_equal[100]');
		$this->form_validation->set_rules('chrono', 'Le chrono entre chaque tour', 'required|callback__chrono_check');
		$this->form_validation->set_rules('charisme', 'Le charisme', 'required|greater_than_or_equal[0]|less_than_or_equal[30]');
		$this->form_validation->set_rules('mediatisation', 'La médiatisation', 'required|greater_than_or_equal[1]|less_than_or_equal[5]');
		$this->form_validation->set_rules('partisans', 'Le nombre de partisans', 'required|greater_than_or_equal[1]|less_than_or_equal[5]');
		$this->form_validation->set_rules('machine_a_cafe', 'La machine a café', '');
		$this->form_validation->set_rules('maison_id', "L'objet", 'required|is_natural');
		$this->form_validation->set_rules('mot_de_passe', 'Le mot de passe', 'min_length[3]|max_length[10]');

		if ( ! $this->form_validation->run())
		{
			return $this->layout->view('plouk/creer', $vars);
		}

		// Si un objet a été parié
		if ($this->input->post('maison_id') > 0)
		{
			// On vérifie que le joueur a bien cet objet
			$objet_pari = null;
			
			foreach ($objets as $objet)
			{
				if ($objet->maison_id == $this->input->post('maison_id'))
				{
					$objet_pari = $objet;
					break;
				}
			}
			
			if ( ! isset($objet_pari))
			{
				$this->echec('Tu ne possèdes pas cet objet');
				return $this->layout->view('plouk/creer', $vars);
			}

			// On retire l'objet au joueur
			$this->bouzouk->retirer_objets($objet_pari->id, 1, $objet_pari->peremption);
		}

		// On enregistre la partie
		$data_plouk_parties = array(
			'createur_id'               => $this->session->userdata('id'),
			'createur_pseudo'           => $this->session->userdata('pseudo'),
			'createur_perso'            => $this->session->userdata('perso'),
			'objet_id'                  => isset($objet_pari) ? $objet_pari->id : NULL,
			'objet_nom'                 => isset($objet_pari) ? $objet_pari->nom : NULL,
			'createur_objet_peremption' => isset($objet_pari) ? $objet_pari->peremption : NULL,
			'chrono'                    => $this->input->post('chrono'),
			'charisme'                  => $this->input->post('charisme'),
			'mediatisation'             => $this->input->post('mediatisation'),
			'partisans'                 => $this->input->post('partisans'),
			'nb_tours'                  => $this->input->post('nb_tours'),
			'tour_actuel'               => 0,
			'joueur_actuel'             => 0,
			'tchat'                     => $this->input->post('machine_a_cafe') !== false ? 1 : 0,
			'createur_jeu'              => '',
			'adversaire_jeu'            => '',
			'date_statut'               => bdd_datetime(),
			'statut'                    => Lib_plouk::Proposee,
			'mot_de_passe'              => $this->input->post('mot_de_passe'),
			'messages'                  => "Attente d'un<br>adversaire|||"
		);
		$this->db->insert('plouk_parties', $data_plouk_parties);
		
		$partie_plouk_id = $this->db->insert_id();

		// On met l'id de partie en session
		$partie_id = $this->db->insert_id();
		$this->session->set_userdata('plouk_id', $partie_id);
		
		// Si la partie est publique, on notifie qui le souhaite qu'une partie a été créé (On limite a 1 notif toutes les X minutes)
		if ($this->input->post('mot_de_passe') == '' && $this->session->userdata('derniere_notif_plouk')+($this->bouzouk->config('plouk_temps_entre_deux_notifs')*60) < time())
		{
			$this->load->library('lib_notifications');
			$this->lib_notifications->notifier_all(Bouzouk::Notification_PloukNouvellePartie, 236, array(profil(), site_url('plouk/rejoindre/'.$partie_plouk_id)), array($this->session->userdata('id')));
			
			$this->session->set_userdata('derniere_notif_plouk', time());
		}
		
		// On affiche
		redirect('plouk/jouer');
	}

	public function rejoindre($id_partie = null)
	{
		// Le joueur ne doit pas être en cours de partie
		if ($this->session->userdata('plouk_id'))
		{
			$this->echec('Tu es déjà en train de jouer une partie');
			return $this->index();
		}

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('partie_id', 'La partie', 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run() && ! $id_partie)
			return $this->index();
		
		if ( ! $id_partie)
			$id_partie = $this->input->post('partie_id');

		// On vérifie que la partie existe
		$query = $this->db->select('id, createur_id, createur_pseudo, objet_id, objet_nom, createur_objet_peremption, chrono, charisme, mediatisation, partisans, nb_tours, tchat, date_statut, mot_de_passe')
						  ->from('plouk_parties')
						  ->where('createur_id !=', $this->session->userdata('id'))
						  ->where('id', $id_partie)
						  ->where('statut', Lib_plouk::Proposee)
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Cette partie n'est pas disponible");
			return $this->index();
		}

		$partie = $query->row();
		$objets = array();

		// On vérifie que le joueur peut parier l'objet
		if ($partie->objet_id != null)
		{
			$objets = $this->get_objets($this->session->userdata('id'), $partie->objet_id);
			
			if (count($objets) == 0)
			{
				$this->echec("Tu ne possèdes pas d'objet nécessaire pour le pari");
				return $this->index();
			}			
		}

		$vars = array(
			'partie'  => $partie,
			'objets'  => $objets,
			'chronos' => $this->chronos
		);

		// Si le joueur n'a pas encore décidé de rejoindre
		if ($this->input->post('rejoindre') == false)
		{
			return $this->layout->view('plouk/rejoindre', $vars);
		}

		// Si le mot de passe existe, il doit avoir été envoyé et correspondre
		if ($partie->mot_de_passe != $this->input->post('mot_de_passe'))
		{
			$this->echec("Le mot de passe n'est pas le bon");
			return $this->layout->view('plouk/rejoindre', $vars);
		}

		if ($partie->objet_id != null)
		{
			// On vérifie que le joueur a bien cet objet
			$objet_pari = null;

			foreach ($objets as $objet)
			{
				if ($objet->maison_id == $this->input->post('maison_id'))
				{
					$objet_pari = $objet;
					break;
				}
			}

			if ( ! isset($objet_pari))
			{
				$this->echec('Tu ne possèdes pas cet objet');
				return $this->layout->view('plouk/rejoindre', $vars);
			}

			// On retire l'objet au joueur
			$this->bouzouk->retirer_objets($objet_pari->id, 1, $objet_pari->peremption);
		}

		// On enregistre le joueur dans la partie
		$this->db->set('adversaire_id', $this->session->userdata('id'))
				 ->set('adversaire_pseudo', $this->session->userdata('pseudo'))
				 ->set('adversaire_objet_peremption', ($partie->objet_id != null) ? $objet_pari->peremption : null)
				 ->set('adversaire_perso',$this->session->userdata('perso'))
				 ->set('statut', Lib_plouk::Attente)
				 ->set('createur_jeu', $this->lib_plouk->creer_jeu($partie->charisme, $partie->mediatisation, $partie->partisans))
				 ->set('adversaire_jeu', $this->lib_plouk->creer_jeu($partie->charisme, $partie->mediatisation, $partie->partisans))
				 ->set('date_statut', bdd_datetime())
				 ->set('version', 'version+1', false)
				 ->set('messages', "Attente des joueurs|||".$this->session->userdata('pseudo')." a rejoint la partie")
				 ->where('id', $id_partie)
				 ->update('plouk_parties');

		// On envoit une notif au créateur
		$this->bouzouk->notification(135, array(profil()), $partie->createur_id);
		
		// On met à jour la session
		$this->session->set_userdata('plouk_id', $partie->id);
		
		// On affiche une confirmation
		redirect('plouk/jouer');
	}

	public function jouer()
	{
		// On regarde si le joueur est dans une partie
		$query = $this->db->select('id, createur_id, adversaire_id, createur_pseudo, adversaire_pseudo, createur_pret, adversaire_pret, objet_nom, tchat, statut, mediatisation, partisans')
						  ->from('plouk_parties')
						  ->where('id', $this->session->userdata('plouk_id'))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Tu n'as aucune partie en cours");
			return $this->index();
		}

		$partie = $query->row();

		$query = $this->db->select('pc.derniere_visite')
						  ->from('plouk_connectes pc')
						  ->where('pc.partie_id', $partie->id)
						  ->where('pc.joueur_id', $this->session->userdata('id'))
						  ->get();

		// Si le joueur est déjà connecté
		if ($query->num_rows() > 0)
		{
			// On met à jour sa date de visite
			$this->db->set('derniere_visite', bdd_datetime())
					 ->where('joueur_id', $this->session->userdata('id'))
					 ->where('partie_id', $partie->id)
					 ->update('plouk_connectes');
		}

		// Si le joueur vient d'arriver
		else
		{
			// On l'insère dans la liste des connectés
			$data_plouk_connectes = array(
				'partie_id'       => $partie->id,
				'joueur_id'       => $this->session->userdata('id'),
				'derniere_visite' => bdd_datetime()
			);
			$this->db->insert('plouk_connectes', $data_plouk_connectes);
		}
		
		// On affiche
		$vars = array(
			'partie'        => $partie,
			'table_smileys' => creer_table_smileys('message')
		);
		return $this->layout->view('plouk/jouer', $vars);
	}

	public function suivre($id_partie = null)
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('partie_id', 'La partie', 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run() && ! $id_partie)
			return $this->index();
		
		if ( ! $id_partie)
			$id_partie = $this->input->post('partie_id');
		
		// On vérifie que la partie existe
		$query = $this->db->select('id, createur_id, adversaire_id, createur_pseudo, adversaire_pseudo, objet_nom, mot_de_passe, tchat, mediatisation, partisans')
						  ->from('plouk_parties')
						  ->where('id', $id_partie)
						  ->where_in('statut', array(Lib_plouk::Attente, Lib_plouk::EnCours))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Cette partie n'est pas disponible");
			return $this->index();
		}

		$partie = $query->row();

		// On vérifie le mot de passe (sauf pour les modérateurs)
		if ( ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats) && $partie->mot_de_passe != '' && $partie->mot_de_passe != $this->input->post('mot_de_passe'))
		{
			$this->echec('Le mot de passe est invalide');
			return $this->index();
		}

		// On vérifie que le joueur n'a pas trop de parties suivies
		if ( ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats))
		{
			$nb_parties = $this->db->where('joueur_id', $this->session->userdata('id'))
								   ->where('derniere_visite > (NOW() - INTERVAL 10 SECOND)')
								   ->where('partie_id > 0')
								   ->count_all_results('plouk_connectes');

			if ($nb_parties >= $this->bouzouk->config('plouk_nb_suivies_max'))
			{
				$this->echec('Tu es déjà sur plusieurs parties en même temps, tu dois en quitter une pour pouvoir en suivre une autre');
				return $this->index();
			}
		}
		
		// On ajoute le joueur à la liste des connectés (si on est pas connecté depuis l'interface admin)
		if ( ! $this->session->userdata('admin_connecte'))
		{
			$query = $this->db->select('pc.derniere_visite')
							  ->from('plouk_connectes pc')
							  ->join('plouk_parties pp', 'pp.id = pc.partie_id')
							  ->where('pc.partie_id', $partie->id)
							  ->where('pc.joueur_id', $this->session->userdata('id'))
							  ->where_in('pp.statut', array(Lib_plouk::Attente, Lib_plouk::EnCours))
							  ->get();
			$existe = $query->num_rows();

			if ($existe)
				$joueur = $query->row();

			// Si le joueur est déjà connecté
			if ($existe && strtotime($joueur->derniere_visite.'+ 10 SECOND') >= bdd_datetime())
			{
				// On met à jour sa date de visite
				$this->db->set('derniere_visite', bdd_datetime())
						 ->where('joueur_id', $this->session->userdata('id'))
						 ->where('partie_id', $partie->id)
						 ->update('plouk_connectes');
			}

			// Si le joueur est un nouveau spectateur
			else
			{
				// On vérifie qu'il reste de la place (sauf pour les modérateurs)
				if ( ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats))
				{
					$nb_connectes = $this->db->where('partie_id', $partie->id)
											->where('derniere_visite > (NOW() - INTERVAL 10 SECOND)')
											->count_all_results('plouk_connectes');

					if ($nb_connectes >= $this->bouzouk->config('plouk_max_spectateurs'))
					{
						$this->echec("Il n'y a plus de place dans cette partie");
						return $this->index();
					}
				}

				// Si il existait déjà, on met à jour
				if ($existe)
				{
					// On met à jour sa date de visite
					$this->db->set('derniere_visite', bdd_datetime())
							 ->where('joueur_id', $this->session->userdata('id'))
							 ->where('partie_id', $partie->id)
							 ->update('plouk_connectes');
				}

				// Sinon on l'insère dans la liste des connectés
				else
				{
					$data_plouk_connectes = array(
						'partie_id'       => $partie->id,
						'joueur_id'       => $this->session->userdata('id'),
						'derniere_visite' => bdd_datetime()
					);
					$this->db->insert('plouk_connectes', $data_plouk_connectes);
				}
			}
		}

		// On affiche
		$vars = array(
			'partie'        => $partie,
			'table_smileys' => creer_table_smileys('message')
		);
		return $this->layout->view('plouk/jouer', $vars);
	}

	public function supprimer()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('partie_id', 'La partie', 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
		{
			return $this->index();
		}

		// On vérifie que la partie existe
		$query = $this->db->select('objet_id, createur_objet_peremption')
						  ->from('plouk_parties')
						  ->where('id', $this->input->post('partie_id'))
						  ->where('statut', Lib_plouk::Proposee)
						  ->where('createur_id', $this->session->userdata('id'))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec('Tu ne peux pas supprimer cette partie');
			return $this->index();
		}

		$partie = $query->row();
		
		// On supprime la partie
		$this->db->where('id', $this->input->post('partie_id'))
				 ->delete('plouk_parties');

		// On redonne l'objet au créateur
		if (isset($partie->objet_id))
			$this->bouzouk->ajouter_objets($partie->objet_id, 1, $partie->createur_objet_peremption);
		
		// On met à jour la session
		$this->session->set_userdata('plouk_id', false);

		// On affiche un message de confirmation
		$this->succes('Tu as bien supprimé cette partie');
		return $this->index();
	}
	
	private function nettoyer()
	{
		$parties_ids = array();
		$joueurs_ids = array();
	
		// On supprime les parties proposées depuis trop longtemps
		$query = $this->db->select('id, createur_id, objet_id, createur_objet_peremption')
						  ->from('plouk_parties')
						  ->where('statut', Lib_plouk::Proposee)
						  ->where('(date_statut < (NOW() - INTERVAL '.$this->bouzouk->config('plouk_delai_suppression_proposee').' MINUTE))')
						  ->get();

		if ($query->num_rows() > 0)
		{
			// On récupère les ids
			foreach ($query->result() as $partie)
			{
				$parties_ids[] = $partie->id;
				$joueurs_ids[] = $partie->createur_id;

				// On redonne l'objet
				if (isset($partie->objet_id))
					$this->bouzouk->ajouter_objets($partie->objet_id, 1, $partie->createur_objet_peremption, $partie->createur_id);

				// On envoit une notif au créateur
				if ($this->bouzouk->est_connecte($partie->createur_id))
					$this->bouzouk->notification(136, array(), $partie->createur_id);
			}
		}
		
		// On supprime les parties en attente depuis trop longtemps
		$query = $this->db->select('id, createur_id, createur_pseudo, adversaire_id, adversaire_pseudo, objet_id, createur_objet_peremption, adversaire_objet_peremption')
						  ->from('plouk_parties')
						  ->where('statut', Lib_plouk::Attente)
						  ->where('(date_statut < (NOW() - INTERVAL '.$this->bouzouk->config('plouk_delai_suppression_attente').' MINUTE))')
						  ->get();

		if ($query->num_rows() > 0)
		{
			// On récupère les ids
			foreach ($query->result() as $partie)
			{
				$parties_ids[] = $partie->id;
				$joueurs_ids[] = $partie->createur_id;
				$joueurs_ids[] = $partie->adversaire_id;

				// On redonne les objets
				if (isset($partie->objet_id))
				{
					$this->bouzouk->ajouter_objets($partie->objet_id, 1, $partie->createur_objet_peremption, $partie->createur_id);
					$this->bouzouk->ajouter_objets($partie->objet_id, 1, $partie->adversaire_objet_peremption, $partie->adversaire_id);
				}

				// On envoit une notif
				if ($this->bouzouk->est_connecte($partie->createur_id))
					$this->bouzouk->notification(137, array(profil($partie->adversaire_id, $partie->adversaire_pseudo)), $partie->createur_id);

				if ($this->bouzouk->est_connecte($partie->adversaire_id))
					$this->bouzouk->notification(137, array(profil($partie->createur_id, $partie->createur_pseudo)), $partie->adversaire_id);
			}
		}
		
		// On supprime les parties en cours sans connecté depuis trop longtemps
		$query = $this->db->select('pp.id, pp.createur_id, pp.createur_pseudo, pp.adversaire_id, pp.adversaire_pseudo, pp.objet_id, pp.createur_objet_peremption, pp.adversaire_objet_peremption')
						  ->from('plouk_parties pp')
						  ->join('plouk_connectes pc', 'pc.partie_id = pp.id')
						  ->where('pp.statut', Lib_plouk::EnCours)
						  ->group_by('pp.id')
						  ->having('MAX(pc.derniere_visite) + INTERVAL '.$this->bouzouk->config('plouk_delai_suppression_en_cours').' MINUTE < NOW()')
						  ->get();
				 
		if ($query->num_rows() > 0)
		{
			// On récupère les ids
			foreach ($query->result() as $partie)
			{
				$parties_ids[] = $partie->id;
				$joueurs_ids[] = $partie->createur_id;
				$joueurs_ids[] = $partie->adversaire_id;

				// On redonne les objets
				if (isset($partie->objet_id))
				{
					$this->bouzouk->ajouter_objets($partie->objet_id, 1, $partie->createur_objet_peremption, $partie->createur_id);
					$this->bouzouk->ajouter_objets($partie->objet_id, 1, $partie->adversaire_objet_peremption, $partie->adversaire_id);
				}

				// On envoit une notif
				if ($this->bouzouk->est_connecte($partie->createur_id))
					$this->bouzouk->notification(139, array(profil($partie->adversaire_id, $partie->adversaire_pseudo)), $partie->createur_id);

				if ($this->bouzouk->est_connecte($partie->adversaire_id))
					$this->bouzouk->notification(139, array(profil($partie->createur_id, $partie->createur_pseudo)), $partie->adversaire_id);
			}
		}
		
		if (count($parties_ids) > 0)
		{
			// On supprime les parties
			$this->supprimer_parties($parties_ids);

			// On met à jour les sessions
			$this->db->set('version_session', 'version_session+1', false)
					 ->where_in('id', $joueurs_ids)
					 ->update('joueurs');

			if (in_array($this->session->userdata('id'), $joueurs_ids))
				redirect('plouk');
		}
	}

	private function supprimer_parties($parties_ids)
	{
		// On supprime la partie
		$this->db->where_in('id', $parties_ids)
				 ->delete('plouk_parties');

		// On supprime les messages de tchat
		$this->db->where_in('partie_id', $parties_ids)
				 ->delete('plouk_tchat');

		// On supprime les connectés
		$this->db->where_in('partie_id', $parties_ids)
				 ->delete('plouk_connectes');
	}

	private function get_objets($joueur_id, $objet_id = null)
	{
		// On va chercher les objets disponibles à mettre en jeu
		$query = 'SELECT o.id, m.id AS maison_id, o.nom, m.peremption '.
				 'FROM maisons m '.
				 'JOIN objets o ON o.id = m.objet_id '.
				 'WHERE m.joueur_id = '.$joueur_id.' AND (m.peremption >= '.$this->bouzouk->config('plouk_peremption_min').' OR m.peremption = -1) ';

		if (isset($objet_id))
			$query .= 'AND m.objet_id = '.$objet_id.' ';

		// Péremption illimitée tout en bas par ordre croissant
		$query .= 'ORDER BY o.nom, IF(m.peremption = -1, 999999, m.peremption)';

		$query = $this->db->query($query);
		return $query->result();
	}
}
