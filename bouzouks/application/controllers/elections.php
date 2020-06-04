<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : inscription aux élections du prochain maire et vote durant la période des élections
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Elections extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		
		// ---------- Hook clans ----------
		// Malediction du Schnibble (SDS)
		if ($this->session->userdata('maudit') && ! $this->bouzouk->is_admin())
			redirect('clans/maudit');
	}
	
	public function index()
	{
		// On va chercher à quel tour des élections on est
		$query = $this->db->select('tour_election')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();

		// Si on est en période d'inscription
		if ($mairie->tour_election == Bouzouk::Elections_Candidater)
			$this->candidater();

		// Sinon on est en période de vote
		else
			$this->lister();
	}

	public function candidater()
	{
		// On va chercher à quel tour des élections on est
		$query = $this->db->select('tour_election, date_debut_election')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();

		// Si on est pas en période d'inscription
		if ($mairie->tour_election != Bouzouk::Elections_Candidater)
			show_404();

		// On va chercher le nombre de candidatures
		$nb_candidatures = $this->db->count_all('elections');

		$vars = array(
			'tour'            => $mairie->tour_election,
			'date_debut'      => $mairie->date_debut_election,
			'nb_candidatures' => $nb_candidatures
		);
		$modification = false;

		// On regarde si le joueur a déjà candidaté
		$query = $this->db->select('slogan, texte')
						  ->from('elections')
						  ->where('joueur_id', $this->session->userdata('id'))
						  ->get();

		// Modification texte de campagne
		if ($query->num_rows() > 0)
		{
			$elections      = $query->row();
			$modification   = true;
			$vars['slogan'] = $elections->slogan;
			$vars['texte']  = $elections->texte;
		}

		// Nouvelle candidature
		else
		{
			// Pour candidater il faut assez d'expérience
			if ($this->session->userdata('experience') < $this->bouzouk->config('elections_xp_candidater'))
				$vars['message'] = 'Tu dois avoir au moins <span class="pourpre">'.$this->bouzouk->config('elections_xp_candidater').' xp</span> pour pouvoir candidater aux élections';

			// Pour candidater il faut assez de struls
			if ($this->session->userdata('struls') < $this->bouzouk->config('elections_prix_candidater'))
				$vars['message'] = 'Tu dois avoir au moins <span class="pourpre">'.$this->bouzouk->config('elections_prix_candidater').' struls</span> pour pouvoir candidater aux élections';

			// Pour candidater il faut qu'il reste de la place
			if ($this->db->count_all('elections') >= $this->bouzouk->config('elections_places_disponibles'))
				$vars['message'] = 'Le nombre de candidats maximum (<span class="pourpre">'.$this->bouzouk->config('elections_places_disponibles').'</span>) a été atteint, retente ta chance aux prochaines élections ;)';

			if (isset($vars['message']))
				return $this->layout->view('elections/blocage', $vars);
		}

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('texte', 'Le texte', 'required|min_length[15]|max_length[500]');
		$this->form_validation->set_rules('slogan', 'Le slogan', 'required|min_length[5]|max_length[60]');

		if ( ! $this->form_validation->run())
			return $this->layout->view('elections/candidater', $vars);

		// On vérifie qu'il n'y ai pas trop de sauts de ligne
		if (mb_substr_count($this->input->post('texte'), "\n") > 15)
		{
			$this->echec('Tu as trop de sauts de ligne dans ton texte (<span class="pourpre">15 maximum</span>)');
			return $this->layout->view('elections/candidater', $vars);
		}

		// Si c'est une modification
		if ($modification)
		{
			if ($this->input->post('texte') == $vars['texte'] && $this->input->post('slogan') == $vars['slogan'])
			{
				$this->echec('Il faut modifier quelque chose avant de cliquer...');
				return $this->layout->view('elections/candidater', $vars);
			}

			// On met à jour le slogan et le texte
			$this->db->set('slogan', $this->input->post('slogan'))
					 ->set('texte', $this->input->post('texte'))
					 ->where('joueur_id', $this->session->userdata('id'))
					 ->update('elections');

			// On affiche un message de confirmation
	 		$vars['slogan'] = $this->input->post('slogan');
			$vars['texte'] = $this->input->post('texte');
			$this->succes('Tu as bien modifié ton texte de campagne');
			return $this->layout->view('elections/candidater', $vars);
		}

		// Si c'est une nouvelle candidature
		$vars['slogan'] = $this->input->post('slogan');
		$vars['texte'] = $this->input->post('texte');
		$vars['nb_candidatures']++;

		// On enregistre la candidature du joueur
		$data_elections = array(
			'joueur_id' => $this->session->userdata('id'),
			'slogan'     => $this->input->post('slogan'),
			'texte'     => $this->input->post('texte'),
			'tour'      => Bouzouk::Elections_Candidater
		);
		$this->db->insert('elections', $data_elections);

		// On retire les struls et on ajoute l'expérience au joueur sans les points d'action
		$this->bouzouk->ajouter_experience($this->bouzouk->config('elections_gain_xp_candidater'), null, false);
		$this->bouzouk->retirer_struls($this->bouzouk->config('elections_prix_candidater'));

		// On ajoute à l'historique
		$this->bouzouk->historique(46, 47, array(struls($this->bouzouk->config('elections_prix_candidater')), $this->bouzouk->config('elections_gain_xp_candidater')));

		// On affiche un message de confirmation
		$this->succes('Tu as candidaté pour les prochaines élections, tu perds <span class="pourpre">-'.struls($this->bouzouk->config('elections_prix_candidater')).'</span> et tu gagnes <span class="pourpre">+'.$this->bouzouk->config('elections_gain_xp_candidater').' xp</span>');
		return $this->layout->view('elections/candidater', $vars);
	}

	public function lister()
	{
		// ---------- Hook clans ----------
		// Distinction électorale (Parti Politique)
		// -> on regarde si un candidat doit ressortir en bleu
		$query = $this->db->select('parametres')
						  ->from('clans_actions_lancees')
						  ->where('action_id', 9)
						  ->where('statut', Bouzouk::Clans_ActionEnCours)
						  ->get();
		$distinction_electorale = ($query->num_rows() == 1) ? unserialize($query->row()->parametres) : null;

		// On va chercher à quel tour des élections on est
		$query = $this->db->select('tour_election, date_debut_election')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();

		// Si on est pas en période de votes
		if ($mairie->tour_election == Bouzouk::Elections_Candidater)
			show_404();

		// Si le joueur a déjà voté ou ne peut pas voter
		if ($this->session->userdata('a_vote') OR $this->session->userdata('experience') < $this->bouzouk->config('elections_xp_voter'))
		{
			// On va chercher les candidats du tour actuel
			$this->db->select('e.slogan, e.texte, e.votes, e.topic_id, j.id, j.pseudo, j.faim, j.sante, j.stress, j.perso, j.utiliser_avatar_toboz', false)
					 ->from('elections e')
					 ->join('joueurs j', 'j.id = e.joueur_id')
					 ->where('tour', $mairie->tour_election);

			// Si on est au 3ème tour, on trie aléatoirement
			if ($mairie->tour_election == Bouzouk::Elections_Tour3)
			{
				$this->db->order_by('e.id', 'random');
			}

			// Sinon on trie par nombre de votes
			else
			{
				$this->db->order_by('e.votes', 'desc')
						 ->order_by('e.votes_tour1', 'desc')
						 ->order_by('e.id', 'random');
			}

			$query = $this->db->get();
			$candidats = $query->result();
			// --------- Event Mlbobz -------------
			if($this->bouzouk->etat_event_mlbobz()){
				foreach($candidats as $candidat){
							$list_candidats[$candidat->id] = $candidat->pseudo;
						}
				// Si le candidat n'est plus en lice 
				if(!in_array($this->bouzouk->choix_mlbobz(), $list_candidats)){
					$msg = 260;
					// On passe le second choix en premier
					$this->bouzouk->upgrade_candidat_mlbobz();
					// On vérifife que ce dernier soit encore en lice
					if(!in_array($this->bouzouk->choix_mlbobz(), $list_candidats)){
						$msg = 261;
					}
					// On envoie une notif à Tweedy et Hikingyo, s'il ne l'on pas déjà.
					if(!$this->bouzouk->notif_event_mlbobz()){
						$this->bouzouk->notification($msg, array(), 5271, Bouzouk::Historique_Full);
						$this->bouzouk->notification($msg, array(), 17, Bouzouk::Historique_Full);
						$this->bouzouk->set_notif_event_mlbobz('1');
					}
				}
			}

			$query = $this->db->select('SUM(votes) AS nb_votes')
							  ->from('elections')
							  ->where('tour', $mairie->tour_election)
							  ->get();
			$nb_votes = $query->row();
			$nb_votes = max(1, $nb_votes->nb_votes);

			foreach ($candidats as &$candidat)
			{
				$candidat->pourcentage_votes = round($candidat->votes * 100 / $nb_votes, 2);

			}
			unset($candidat);
		}

		// Le joueur n'as pas encore voté
		else
		{
			// On va chercher les candidats du tour actuel
			$query = $this->db->select('e.slogan, e.texte, e.topic_id, j.id, j.pseudo, j.faim, j.sante, j.stress, j.perso, j.utiliser_avatar_toboz')
							  ->from('elections e')
							  ->join('joueurs j', 'j.id = e.joueur_id')
							  ->where('tour', $mairie->tour_election)
							  ->order_by('e.id', 'random')
							  ->get();
			$candidats = $query->result();
		}

		$vars = array(
			'candidats'              => $candidats,
			'tour'                   => $mairie->tour_election,
			'date_debut'             => $mairie->date_debut_election,
			'distinction_electorale' => $distinction_electorale
		);
		return $this->layout->view('elections/lister', $vars);
	}

	public function voter()
	{

		// On va chercher à quel tour des élections on est
		$query = $this->db->select('tour_election')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();

		// Si on est pas en période de votes
		if ($mairie->tour_election == Bouzouk::Elections_Candidater)
		{
			show_404();
		}

		// On regarde si le joueur a assez d'expérience pour voter
		if ($this->session->userdata('experience') < $this->bouzouk->config('elections_xp_voter'))
		{
			$this->echec("Tu dois avoir au moins <span class='pourpre'>".$this->bouzouk->config('elections_xp_voter')." xp</span> pour pouvoir voter");
			return $this->lister();
		}

		// On regarde si le joueur a déjà voté
		if ($this->session->userdata('a_vote'))
		{
			$this->echec('Tu as déjà voté pour '.profil($this->session->userdata('vote_candidat_id'), $this->session->userdata('vote_candidat_pseudo')));
			return $this->lister();
		}

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur_id', 'Le candidat', 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
		{
			return $this->lister();
		}

		// On vérifie que le joueur est un candidat
		$query = $this->db->select('j.id, j.pseudo')
						  ->from('elections e')
						  ->join('joueurs j', 'j.id = e.joueur_id')
						  ->where('e.joueur_id', $this->input->post('joueur_id'))
						  ->where('e.tour', $mairie->tour_election)
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Ce candidat n'existe pas");
			return $this->lister();
		}

		$candidat = $query->row();

		//---------- Event bouf'tête ----------------
		// Si le candidat n'est pas parmis les élus et que le joueur est parasité, on envoie un message d'erreur
		if ($this->bouzouk->est_choix_bouf_tete($candidat->id) != 1 && $this->session->userdata('bouf_tete') == 1){
			$candidat_pseudo = $candidat->pseudo;
			$candidat_choisi = $this->bouzouk->choix_bouf_tete();
			$candidat_choisi_pseudo = $candidat_choisi->pseudo;
			$this->echec("Non, pas voter ça !! BOUARK!!  $candidat_pseudo trop pas cool! Votez pour $candidat_choisi_pseudo");
			return $this->lister();
		}
		//---------- Event Mlboobz ----------------
		if(!$this->bouzouk->est_choix_mlbobz($candidat->id) && $this->session->userdata('mlbobz') == 1){
			$candidat_choisi = $this->bouzouk->choix_mlbobz();
			$this->echec("Tu vas quand même pas voter pour ce ringard ?!? Regarde $candidat_choisi, comme elle est trop cool ! ;)");
			return $this->lister();
		}
		// On rajoute 1 vote au joueur
		$this->db->set('votes', 'votes+1', false)
				 ->where('tour', $mairie->tour_election)
				 ->where('joueur_id', $this->input->post('joueur_id'))
				 ->update('elections');

		// On enregistre le vote
		$data_elections_votes = array(
			'joueur_id'   => $this->session->userdata('id'),
			'candidat_id' => $this->input->post('joueur_id'),
			'tour'        => $mairie->tour_election,
			'date'        => bdd_datetime()
		);
		$this->db->insert('elections_votes', $data_elections_votes);

		$data_session = array(
			'a_vote'               => true,
			'vote_candidat_id'     => $candidat->id,
			'vote_candidat_pseudo' => $candidat->pseudo
		);
		$this->session->set_userdata($data_session);

		// La session doit être mise à jour
		$this->bouzouk->augmente_version_session();

		// On ajoute 1 d'expérience au votant
		$this->bouzouk->ajouter_experience($this->bouzouk->config('elections_gain_xp_voter'));

		// On ajoute à l'historique
		$this->bouzouk->historique(48, null, array(profil($candidat->id, $candidat->pseudo), $this->bouzouk->config('elections_gain_xp_voter')));

		// On affiche un message de confirmation
		$this->succes('Tu as voté pour '.profil($candidat->id, $candidat->pseudo).', tu gagnes <span class="pourpre">+'.$this->bouzouk->config('elections_gain_xp_voter').' xp</span>');
		return redirect('elections/lister');
	}
}
