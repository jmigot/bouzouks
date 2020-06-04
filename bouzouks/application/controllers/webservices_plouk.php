<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : webservices destinés au jeu de plouk
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : février 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class WebServices_plouk extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();

		// Si ce controller n'est pas appelé en Ajax ou en Post
		if ( ! $this->input->is_ajax_request() || ! $this->input->isPost())
			show_404();
		
		// On renvoit du JSON
		$this->output->set_content_type('application/json');
		$this->load->library('lib_plouk');
	}
	
	private function get_partie($statuts = null)
	{
		if ( ! isset($statuts))
			$statuts = array(Lib_plouk::Proposee, Lib_plouk::Attente, Lib_plouk::EnCours, Lib_plouk::Terminee);
			
		$partie_id = $this->input->post('partie_id');
		
		// On va chercher les infos de la partie
		$query = $this->db->select('*, createur_chrono - UNIX_TIMESTAMP() AS createur_chrono_restant, adversaire_chrono - UNIX_TIMESTAMP() AS adversaire_chrono_restant')
						  ->from('plouk_parties')
						  ->where('id', $partie_id)
						  ->where_in('statut', $statuts)
						  ->get();

		if ($query->num_rows() == 0)
			return false;

		$partie = $query->row();
		$partie->spectateur = ! ($partie->createur_id == $this->session->userdata('id') || $partie->adversaire_id == $this->session->userdata('id'));
		$partie->alert = '';
		
		// Si c'est un spectateur, on vérifie qu'il est connecté
		if ($partie->spectateur && ! $this->session->userdata('admin_connecte'))
		{
			$existe = $this->db->where('partie_id', $partie_id)
							   ->where('joueur_id', $this->session->userdata('id'))
							   ->where('derniere_visite > (NOW() - INTERVAL 10 SECOND)')
							   ->count_all_results('plouk_connectes');

			// Reconnexion s'il était là il y a 30 secondes
			if ( ! $existe)
			{
				$existe = $this->db->where('partie_id', $partie_id)
								   ->where('joueur_id', $this->session->userdata('id'))
								   ->where('derniere_visite > (NOW() - INTERVAL 30 SECOND)')
								   ->count_all_results('plouk_connectes');

				if ( ! $existe)
					return false;
			}
		}
		
		return $partie;
	}

	public function save_partie($partie)
	{
		// On recompose le jeu pour la base
		$this->lib_plouk->implode_jeux($partie);
		
		// On enregistre
		$data_plouk_parties = array(
			'createur_jeu'      => $partie->createur_jeu,
			'createur_pret'     => $partie->createur_pret,
			'adversaire_pret'   => $partie->adversaire_pret,
			'adversaire_jeu'    => $partie->adversaire_jeu,
			'derniere_carte'    => $partie->derniere_carte,
			'derniere_action'   => $partie->derniere_action,
			'createur_chrono'   => $partie->createur_chrono,
			'adversaire_chrono' => $partie->adversaire_chrono,
			'joueur_actuel'     => $partie->joueur_actuel,
			'tour_actuel'       => $partie->tour_actuel,
			'statut'            => $partie->statut,
			'gagnant_id'        => $partie->gagnant_id,
			'messages'          => $partie->messages,
			'version'           => $partie->version,
		);
		$this->db->where('id', $partie->id)
				 ->update('plouk_parties', $data_plouk_parties);
	}
	
	public function poster_tchat()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('message', "Le message", 'required|max_length[150]');

		if ( ! $this->form_validation->run())
			return $this->output->set_output(json_encode(''));

		// Si le joueur est interdit de tchat
		if ($this->session->userdata('interdit_tchat') == 1)
			return $this->output->set_output(json_encode(''));

		// Si c'est un spectateur qui poste
		if ( ! $this->session->userdata('admin_connecte') && ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats) && $this->session->userdata('plouk_id') != $this->input->post('partie_id'))
		{
			// On vérifie qu'il est connecté
			$connecte = $this->db->where('partie_id', $this->input->post('partie_id'))
								 ->where('joueur_id', $this->session->userdata('id'))
								 ->where('derniere_visite > (NOW() - INTERVAL 10 SECOND)')
								 ->count_all_results('plouk_connectes');

			if ( ! $connecte)
				return $this->output->set_output(json_encode(''));

			// On vérifie que la partie autorise le tchat spectateurs
			$autorise = $this->db->where('id', $this->input->post('partie_id'))
								 ->where('tchat', 0)
								 ->count_all_results('plouk_parties');

			if ( ! $autorise)
				return $this->output->set_output(json_encode(''));
		}

		// On poste le message
		$data_plouk_tchat = array(
			'partie_id'  => $this->input->post('partie_id'),
			'joueur_id'  => $this->session->userdata('id'),
			'message'    => $this->input->post('message'),
			'date_envoi' => bdd_datetime()
		);
		$this->db->insert('plouk_tchat', $data_plouk_tchat);

		return $this->output->set_output(json_encode(''));
	}
	
	private function rafraichir_tchat($partie_id, $dernier_id)
	{
		$reponse = array('messages' => array(), 'connectes' => array());

		// On met à jour le champ connecte pour indiquer que le joueur est toujours connecte (si on est pas connecté depuis l'interface admin)
		if ( ! $this->session->userdata('admin_connecte'))
		{
			$this->db->set('derniere_visite', bdd_datetime())
					 ->where('joueur_id', $this->session->userdata('id'))
					 ->where('partie_id', $partie_id)
					 ->update('plouk_connectes');
		}

		// On va chercher la liste des connectés
		$query = $this->db->select('j.id, j.pseudo, j.rang')
						  ->from('plouk_connectes pc')
						  ->join('joueurs j', 'j.id = pc.joueur_id')
						  ->where('pc.partie_id', $partie_id)
						  ->where('pc.derniere_visite > (NOW() - INTERVAL 10 SECOND)')
						  ->order_by('j.pseudo')
						  ->get();

		foreach ($query->result() as $joueur)
		{
			$reponse['connectes'][] = profil($joueur->id, $joueur->pseudo, $joueur->rang);
		}

		// On va chercher les derniers messages
		$query = $this->db->select('pt.id, j.id AS joueur_id, j.pseudo, pt.message, pt.date_envoi')
						  ->from('plouk_tchat pt')
						  ->join('joueurs j', 'j.id = pt.joueur_id')
						  ->where('pt.id >', $dernier_id)
						  ->where('pt.partie_id', $partie_id)
						  ->order_by('pt.id', 'desc')
						  ->limit($this->bouzouk->config('plouk_tchat_max_messages'))
						  ->get();

		$messages = array();

		foreach ($query->result() as $message)
		{
			$pseudo = profil($message->joueur_id, $message->pseudo);

			// Le /me apparait en pourpre
			if (mb_substr($message->message, 0, 4) == '/me ')
				$pseudo = '<span class="noir">'.$message->pseudo.'</span>';

			if ( ! in_array($message->joueur_id, $this->bouzouk->get_robots()))
				$message->message = form_prep($message->message);
				
			$messages[] = array(
				'id'        => $message->id,
				'pseudo'    => $pseudo,
				'message'   => remplace_smileys($message->message),
				'date'      => tchat_datetime($message->date_envoi)
			);
		}

		// On renverse l'ordre pour afficher les vieux messages en premier
		$reponse['messages'] = array_reverse($messages);

		// On affiche les données
		return $reponse;
	}

	public function actualiser()
	{
		// On récupère la partie
		$partie = $this->get_partie();
		
		if ($partie === false)
		{
			$reponse = array('waiter' => 1);
			return $this->output->set_output(json_encode($reponse));
		}
		
		// On récupère les messages et les connectés du tchat
		$reponse = $this->rafraichir_tchat($this->input->post('partie_id'), $this->input->post('dernier_id'));

		// On vérifie que la partie est en cours
		if ($partie->statut == Lib_plouk::EnCours)
		{
			// Si il faut changer de joueur
			if (($partie->joueur_actuel == $partie->createur_id && ($partie->createur_chrono_restant <= 0 || $partie->createur_chrono_restant > $partie->chrono)) ||
				($partie->joueur_actuel == $partie->adversaire_id && ($partie->adversaire_chrono_restant <= 0 || $partie->adversaire_chrono_restant > $partie->chrono)))
			{
				// On décompose et on change de joueur
				$partie->rejouer = false;
				$this->lib_plouk->explode_jeux($partie, true);
				$this->lib_plouk->flash($partie, '');
				$this->lib_plouk->joueur_suivant($partie);
				$this->lib_plouk->verifier_partie_gagnee($partie);
				$partie->derniere_action = 'defausser';
				$partie->version++;

				// On recompose pour la base de données
				$this->save_partie($partie);
			}
		}

		// On décompose pour cacher les cartes adverses des résultats
		$this->lib_plouk->explode_jeux($partie);

		if ($partie->spectateur || $partie->statut == Lib_plouk::Proposee)
		{
			for ($i = 1 ; $i <= 6 ; $i++)
				$partie->{'carte_'.$i} = '';
		}
		
		// On envoit les résultats
		$reponse['partie'] = $partie;
		$reponse['waiter'] = 0;
		return $this->output->set_output(json_encode($reponse));
	}

	public function jouer()
	{
		// On récupère la partie
		$partie = $this->get_partie(array(Lib_plouk::EnCours));

		if ($partie === false || $partie->spectateur)
			return $this->output->set_output(json_encode(''));
		
		// On vérifie que c'est bien au tour de ce joueur de jouer
		if ($partie->joueur_actuel != $this->session->userdata('id'))
		{
			$this->lib_plouk->explode_jeux($partie);
			$partie->alert = "Ce n'est pas à ton tour de jouer";
			return $this->output->set_output(json_encode($partie));
		}

		// On décompose le jeu
		$this->lib_plouk->explode_jeux($partie, true);
		$this->lib_plouk->flash($partie, '');

		// On joue la carte
		if ($this->lib_plouk->jouer_carte($partie, $this->input->post('carte')))
		{
			// On change de joueur
			$this->lib_plouk->joueur_suivant($partie, $this->input->post('carte'));
			$this->lib_plouk->verifier_partie_gagnee($partie);
			$partie->derniere_action = 'jouer';
			$partie->version++;

			$this->save_partie($partie);
		}

		else
			$this->lib_plouk->implode_jeux($partie);

		// On décompose pour cacher les cartes adverses des résultats
		$this->lib_plouk->explode_jeux($partie);

		// On envoit les résultats
		return $this->output->set_output(json_encode($partie));
	}

	public function defausser()
	{
		// On récupère la partie
		$partie = $this->get_partie(array(Lib_plouk::EnCours));

		if ($partie === false || $partie->spectateur)
			return $this->output->set_output(json_encode(''));

		// On vérifie que c'est bien au tour de ce joueur de jouer
		if ($partie->joueur_actuel != $this->session->userdata('id'))
		{
			$this->lib_plouk->explode_jeux($partie);
			$partie->alert = "Ce n'est pas à ton tour de jouer";
			return $this->output->set_output(json_encode($partie));
		}

		// On décompose le jeu
		$this->lib_plouk->explode_jeux($partie, true);

		// On change de joueur
		$partie->rejouer = false;
		$this->lib_plouk->flash($partie, '');
		$this->lib_plouk->historique($partie, $this->session->userdata('pseudo').' se défausse');
		$this->lib_plouk->joueur_suivant($partie, $this->input->post('carte'));
		$this->lib_plouk->verifier_partie_gagnee($partie);
		$partie->derniere_action = 'defausser';
		$partie->version++;

		$this->save_partie($partie);

		// On décompose pour cacher les cartes adverses des résultats
		$this->lib_plouk->explode_jeux($partie);

		// On envoit les résultats
		return $this->output->set_output(json_encode($partie));
	}

	public function commencer()
	{
		// On récupère la partie
		$partie = $this->get_partie(array(Lib_plouk::Attente));

		if ($partie === false || $partie->spectateur)
			return $this->output->set_output(json_encode(''));

		$this->lib_plouk->explode_jeux($partie, true);
		$commencer = false;

		// Si le joueur est le créateur
		if ($this->session->userdata('id') == $partie->createur_id)
		{
			// Il est pret
			$partie->createur_pret = 1;
			$this->lib_plouk->historique($partie, $partie->createur_pseudo.' est prêt');
			$this->lib_plouk->flash($partie, $partie->createur_pseudo.' est prêt', true);

			// Si l'adversaire est pret aussi, on commence
			if ($partie->adversaire_pret)
				$commencer = true;
		}

		// Le joueur est l'adversaire
		else
		{
			// Il est pret
			$partie->adversaire_pret = 1;
			$this->lib_plouk->historique($partie, $partie->adversaire_pseudo.' est prêt');
			$this->lib_plouk->flash($partie, $partie->adversaire_pseudo.' est prêt', true);
			
			// Si le créateur est pret aussi, on commence
			if ($partie->createur_pret)
				$commencer = true;
		}

		if ($commencer)
		{
			$this->lib_plouk->flash($partie, 'La partie<br>commence !');
			$partie->statut = Lib_plouk::EnCours;
			$partie->tour_actuel = 1;
			$partie->joueur_actuel = $partie->createur_id;
			$partie->createur_chrono = time() + $partie->chrono;
			
			// Affichage d'une pige si la partie fait plus de 20 tours et qu'elle ne soit pas privée
			if ($partie->nb_tours > 20 && $partie->mot_de_passe == '')
			{
				$data_piges = array(
					'auteur_id' => Bouzouk::Robot_MissPoohLett,
					'texte'     => 'Une grosse partie de plouk est en train de se jouer entre [b]'.$partie->createur_pseudo.'[/b] et [b]'.$partie->adversaire_pseudo.'[/b].',
					'lien'      => site_url('plouk/suivre/'.$partie->id),
					'date'      => bdd_datetime(),
					'en_ligne'  => Bouzouk::Piges_Active
				);

				$this->db->insert('piges', $data_piges);
			}
		}

		$partie->version++;
		$this->save_partie($partie);

		return $this->output->set_output(json_encode(''));
	}

	public function abandonner()
	{
		// On récupère la partie
		$partie = $this->get_partie(array(Lib_plouk::Attente, Lib_plouk::EnCours));

		if ($partie === false || $partie->spectateur)
			return $this->output->set_output(json_encode(''));

		// On passe la partie à en cours et on met les sondages du joueur à 0%
		$gagnant = $this->session->userdata('id') == $partie->createur_id ? 'adversaire' : 'createur';
		$perdant = $this->session->userdata('id') == $partie->createur_id ? 'createur' : 'adversaire';
		
		$this->lib_plouk->explode_jeux($partie, true);
		$this->lib_plouk->set_sondages($partie, $gagnant, 100);
		$this->lib_plouk->set_sondages($partie, $perdant, 0);
		$this->lib_plouk->historique($partie, $this->session->userdata('pseudo').' a abandonné la partie');
		$partie->abandon = true;
		$this->lib_plouk->verifier_partie_gagnee($partie);
		$partie->version++;

		$this->save_partie($partie);
		
		// On décompose pour cacher les cartes adverses des résultats
		$this->lib_plouk->explode_jeux($partie);

		// On envoit les résultats
		$reponse = array(
			'partie' => $partie,
			'code'   => 1
		);
		return $this->output->set_output(json_encode($reponse));
	}

	public function quitter()
	{
		// On récupère la partie
		$partie = $this->get_partie(array(Lib_plouk::Attente, Lib_plouk::EnCours));

		if ($partie === false || $partie->spectateur)
			return $this->output->set_output(json_encode(''));
			
		$reponse = array();
		
		// Si on est en attente depuis au moins 15 secondes
		if ($partie->statut == Lib_plouk::Attente && strtotime($partie->date_statut.'+ 15 SECOND') < strtotime(bdd_datetime()))
		{
			// On rend son objet à l'adversaire
			if ($partie->objet_id != null)
				$this->bouzouk->ajouter_objets($partie->objet_id, 1, $partie->adversaire_objet_peremption, $partie->adversaire_id);

			$this->db->set('adversaire_id', null)
					 ->set('adversaire_pseudo', '')
					 ->set('adversaire_objet_peremption', null)
					 ->set('adversaire_perso', '')
					 ->set('adversaire_pret', 0)
					 ->set('statut', Lib_plouk::Proposee)
					 ->set('createur_jeu', '')
					 ->set('adversaire_jeu', '')
					 ->set('date_statut', bdd_datetime())
					 ->set('version', 'version+1', false)
					 ->set('messages', "Attente d'un<br>adversaire|||")
					 ->where('id', $this->input->post('partie_id'))
					 ->update('plouk_parties');

			$this->bouzouk->augmente_version_session($partie->adversaire_id);

			// On décompose pour cacher les cartes adverses des résultats
			$this->lib_plouk->explode_jeux($partie);
			
			// Si le joueur est le créateur
			if ($this->session->userdata('id') == $partie->createur_id)
				$reponse['location'] = 0;
				
			// Si le joueur est l'adversaire
			else
			{			
				$this->succes('Tu as bien quitté cette partie');
				$reponse['location'] = 1;
			}

			// On envoit une notif aux joueurs
			if ($this->session->userdata('id') == $partie->createur_id && $this->bouzouk->est_connecte($partie->adversaire_id))
				$this->bouzouk->notification(149, array(profil()), $partie->adversaire_id);

			else if ($this->session->userdata('id') == $partie->adversaire_id && $this->bouzouk->est_connecte($partie->createur_id))
				$this->bouzouk->notification(150, array(profil()), $partie->createur_id);
		}

		// Si on est en jeu, le joueur a gagné
		else if ($partie->statut == Lib_plouk::EnCours)
		{
			// On va chercher la derniere visite de l'adversaire
			$adversaire_id = $this->session->userdata('id') == $partie->createur_id ? $partie->adversaire_id : $partie->createur_id;

			$query = $this->db->select('derniere_visite')
							  ->from('plouk_connectes')
							  ->where('partie_id', $this->input->post('partie_id'))
							  ->where('joueur_id', $adversaire_id)
							  ->get();

			if ($query->num_rows() > 0)
				$adversaire = $query->row();

			// On regarde si l'adversaire est absent depuis au moins 1 min
			if ( ! ( ! isset($adversaire) || strtotime($adversaire->derniere_visite.'+1 MINUTE') < strtotime(bdd_datetime())))
			{
				$reponse['location'] = 0;
				$partie->alert = "Ton adversaire est toujours là";

				// On décompose pour cacher les cartes adverses des résultats
				$this->lib_plouk->explode_jeux($partie);
				$reponse['partie'] = $partie;
				return $this->output->set_output(json_encode($reponse));
			}
			
			// On passe la partie à en cours et on met les sondages du joueur à 0%
			$gagnant = $this->session->userdata('id') == $partie->createur_id ? 'createur' : 'adversaire';
			$perdant = $this->session->userdata('id') == $partie->createur_id ? 'adversaire' : 'createur';

			$this->lib_plouk->explode_jeux($partie, true);
			$this->lib_plouk->set_sondages($partie, $gagnant, 100);
			$this->lib_plouk->set_sondages($partie, $perdant, 0);
			$this->lib_plouk->historique($partie, $this->session->userdata('pseudo')." a gagné par absence de l'adversaire");
			$this->lib_plouk->verifier_partie_gagnee($partie);
			$partie->version++;

			// On recompose pour la base de données
			$this->save_partie($partie);

			// On décompose pour cacher les cartes adverses des résultats
			$this->lib_plouk->explode_jeux($partie);
			
			$reponse['location'] = 0;

			// On envoit une notif aux joueurs
			if ($this->session->userdata('id') == $partie->createur_id && $this->bouzouk->est_connecte($partie->adversaire_id))
				$this->bouzouk->notification(151, array(profil()), $partie->adversaire_id);

			else if ($this->session->userdata('id') == $partie->adversaire_id && $this->bouzouk->est_connecte($partie->createur_id))
				$this->bouzouk->notification(151, array(profil()), $partie->createur_id);
		}

		else
		{
			$reponse['location'] = 0;
			$partie->alert = "Tu dois attendre encore un peu pour pouvoir quitter cette partie";

			// On décompose pour cacher les cartes adverses des résultats
			$this->lib_plouk->explode_jeux($partie);
		}
			
		// On envoit les résultats
		$reponse['partie'] = $partie;
		return $this->output->set_output(json_encode($reponse));
	}

	public function connectes_plouk()
	{
		if ( ! in_array($this->session->userdata('statut'), array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile)))
		{
			return $this->output->set_output(json_encode(''));
		}

		// On regarde si le joueur est déjà présent dans la table des connectés
		$present = $this->db->where('joueur_id', $this->session->userdata('id'))
							->where('partie_id', 0)
							->count_all_results('plouk_connectes');

		// On met à jour sa dernière connexion
		if ($present)
		{
			$this->db->set('derniere_visite', bdd_datetime())
					 ->where('joueur_id', $this->session->userdata('id'))
					 ->where('partie_id', 0)
					 ->update('plouk_connectes');
		}

		// On l'ajoute à la table des connectés
		else
		{
			$data_plouk_connectes = array(
				'partie_id'        => 0,
				'joueur_id'       => $this->session->userdata('id'),
				'derniere_visite' => bdd_datetime()
			);
			$this->db->insert('plouk_connectes', $data_plouk_connectes);
		}

		// On récupère la liste des connectes
		$reponse = array('connectes' => array());
		$joueurs = $this->lib_cache->liste_connectes_plouk();

		foreach ($joueurs as $joueur)
		{
			$reponse['connectes'][] = profil($joueur->id, $joueur->pseudo, $joueur->rang);
		}
		
		return $this->output->set_output(json_encode($reponse));
	}
}
