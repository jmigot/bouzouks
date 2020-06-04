<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : modération/administration du site
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : février 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Gerer_joueurs extends MY_Controller
{
	public function index()
	{
		// On va chercher la liste des bouzouks
		$select_joueurs = $this->bouzouk->select_joueurs();

		// On affiche les résultats
		$vars = array(
			'select_joueurs' => $this->bouzouk->select_joueurs(array('status_not_in' => array(Bouzouk::Joueur_Robot), 'inactifs' => true, 'champ_texte' => true))
		);
		return $this->layout->view('staff/gerer_joueurs', $vars);
	}

	public function voir($joueur_id = null)
	{
		if ( ! isset($joueur_id))
		{
			// Règles de validation
			$this->load->library('form_validation');
			$this->form_validation->set_rules('joueur_id', 'Le joueur', 'is_natural_no_zero');
			$this->form_validation->set_rules('joueur_id_pseudo', 'Le pseudo', 'min_length[3]|max_length[20]');

			if ( ! $this->form_validation->run())
			{
				return $this->index();
			}
		}

		// On va chercher les infos du joueur
		$this->db->select('j1.id, j1.pseudo, j1.mot_de_passe, j1.email, j1.date_de_naissance, j1.adresse, j1.commentaire, j1.statut, j1.date_statut, j1.raison_statut,
						   j1.statut_staff_id, j2.pseudo AS statut_staff_pseudo, j1.nb_asile, j1.interdit_missives, j1.interdit_tchat, j1.interdit_plouk, j1.interdit_avatar, j1.rang, j1.rang_description,
						   j1.struls, j1.faim, j1.sante, j1.stress, j1.experience, tu.group_id, j1.duree_asile, j1.force, j1.charisme, j1.intelligence, j1.points_action')
				 ->from('joueurs j1')
				 ->join('joueurs j2', 'j2.id = j1.statut_staff_id', 'left')
				 ->join('tobozon_users tu', 'tu.id = j1.id');

		if ($this->input->post('joueur_id_pseudo') != false)
			$this->db->where('j1.pseudo', $this->input->post('joueur_id_pseudo'));

		else
			$this->db->where('j1.id', $this->input->post('joueur_id'));

		$query = $this->db->where_not_in('j1.statut', array(Bouzouk::Joueur_Robot))
						  ->get();

		// On vérifie que le compte existe
		if ($query->num_rows() == 0)
		{
			$this->echec("Ce joueur n'existe pas");
			return $this->index();
		}

		$joueur = $query->row();

		// On récupère la liste des objets possibles
		$query = $this->db->select('id, nom')
						  ->from('objets')
						  ->order_by('nom')
						  ->get();
		$objets = $query->result();

		// On récupère la liste des groupes possibles sur le tobozon
		$query = $this->db->select('g_id, g_title')
						  ->from('tobozon_groups')
						  ->order_by('g_title')
						  ->get();
		$groupes_tobozon = $query->result();
		
		// On affiche
		$vars = array(
			'joueur'          => $joueur,
			'objets'          => $objets,
			'groupes_tobozon' => $groupes_tobozon,
			'select_joueurs'  => $this->bouzouk->select_joueurs(array('status_not_in' => array(Bouzouk::Joueur_Robot), 'inactifs' => true, 'champ_texte' => true))
		);
		return $this->layout->view('staff/gerer_joueurs', $vars);
	}

	public function fiente_pioupiouk()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur_id', 'Le joueur', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('perte_xp', "La perte d'expérience", 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
		{
			return $this->voir();
		}

		// On vérifie que le joueur existe
		$query = $this->db->select('id, pseudo, rang')
						  ->from('joueurs')
						  ->where('id', $this->input->post('joueur_id'))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Ce joueur n'existe pas");
			return $this->voir();
		}

		$joueur = $query->row();

		// On ajoute à l'historique et on retire l'xp
		$this->bouzouk->historique(33, null, array($this->input->post('perte_xp')), $this->input->post('joueur_id'), Bouzouk::Historique_Full);
		$this->bouzouk->retirer_experience($this->input->post('perte_xp'), $this->input->post('joueur_id'));
		
		// On affiche un message de confirmation
		$this->succes('Tu as bien envoyé une belle fiente de pioupiouk à '.profil($joueur->id, $joueur->pseudo, $joueur->rang).' qui perd <span class="pourpre">-'.$this->input->post('perte_xp').' xp</span>');
		return $this->voir($this->input->post('joueur_id'));
	}

	public function modifier()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur_id', 'Le joueur', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('pseudo', 'Le pseudo', 'required|min_length[3]|max_length[15]|regex_match[#^[^<>]+$#]');
		$this->form_validation->set_rules('date_de_naissance_jour', 'Le jour de naissance', 'required|is_natural_no_zero|less_than_or_equal[31]');
		$this->form_validation->set_rules('date_de_naissance_mois', 'Le mois de naissance', 'required|is_natural_no_zero|less_than_or_equal[12]');
		$this->form_validation->set_rules('date_de_naissance_annee', "L'année de naissance", 'required|is_natural');
		$this->form_validation->set_rules('adresse', "L'adresse", 'required|min_length[15]|max_length[50]|regex_match[#^[a-zA-Z0-9éèàâêôîù ,\'-]+$#]');
		$this->form_validation->set_rules('commentaire', 'Le commentaire', 'max_length[5000]');
		$this->form_validation->set_rules('statut', 'Le statut', 'required|is_natural');
		$this->form_validation->set_rules('date_statut_jour', 'Le jour du statut', 'required|is_natural_no_zero|less_than_or_equal[31]');
		$this->form_validation->set_rules('date_statut_mois', 'Le mois du statut', 'required|is_natural_no_zero|less_than_or_equal[12]');
		$this->form_validation->set_rules('date_statut_annee', "L'année du statut", 'required|is_natural');
		$this->form_validation->set_rules('date_statut_heures', "L'heure du statut", 'required|is_natural|less_than_or_equal[23]');
		$this->form_validation->set_rules('date_statut_minutes', "Les minutes du statut", 'required|is_natural|less_than_or_equal[59]');
		$this->form_validation->set_rules('date_statut_secondes', "Les secondes du statut", 'required|is_natural|less_than_or_equal[59]');
		$this->form_validation->set_rules('raison_statut', 'La raison du statut', 'max_length[250]');
		$this->form_validation->set_rules('duree_asile', "La durée à l'asile", 'required|is_natural|less_than_or_equal[192]');
		$this->form_validation->set_rules('groupe_tobozon', 'Le groupe tobozon', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('rang_description', 'La description du rang', 'max_length[50]');
		$this->form_validation->set_rules('struls', 'Les struls', 'required|numeric|less_than_or_equal[1000000000]');
		$this->form_validation->set_rules('faim', 'La faim', 'required|is_natural|less_than_or_equal[100]');
		$this->form_validation->set_rules('sante', 'La santé', 'required|is_natural|less_than_or_equal[100]');
		$this->form_validation->set_rules('stress', 'Le stress', 'required|is_natural|less_than_or_equal[100]');
		$this->form_validation->set_rules('experience', "L'expérience", 'required|is_natural');
		$this->form_validation->set_rules('force', 'La force', 'required|is_natural');
		$this->form_validation->set_rules('charisme', 'Le charisme', 'required|is_natural');
		$this->form_validation->set_rules('intelligence', "L'intelligence", 'required|is_natural');
		$this->form_validation->set_rules('points_action', "Les points d'action", 'required|is_natural');

		if ( ! $this->form_validation->run())
		{
			return $this->voir($this->input->post('joueur_id'));
		}

		// Les dates doivent être valides
		if ( ! checkdate($this->input->post('date_de_naissance_mois'), $this->input->post('date_de_naissance_jour'), $this->input->post('date_de_naissance_annee')))
		{
			$this->echec("La date de naissance est invalide");
			return $this->voir();
		}

		if ( ! checkdate($this->input->post('date_statut_mois'), $this->input->post('date_statut_jour'), $this->input->post('date_statut_annee')))
		{
			$this->echec("La date du statut est invalide");
			return $this->voir();
		}

		// La mise à l'asile ou le ban doit être accompagnée d'une raison
		if (in_array($this->input->post('statut'), array(Bouzouk::Joueur_Asile, Bouzouk::Joueur_Banni)) && $this->input->post('raison_statut') == '')
		{
			$this->echec("Il faut donner une raison pour un statut asile/banni");
			return $this->voir();
		}

		// On traite les rangs
		$rang = 0;
		$droits = array_keys($this->bouzouk->get_droits());

		if ($this->input->post('rang') !== false)
		{
			foreach ($this->input->post('rang') as $masque)
			{
				if ( ! in_array($masque, $droits))
				{
					$this->echec("Ce droit n'existe pas");
					return $this->voir();
				}

				$rang |= (int)$masque;
			}
		}

		// Si un admin stagiaire essaye de se passer admin, on arrête
		// Si un admin stagiaire essaye de passer quelqu'un d'autre admin ou admin stagiaire, on arrête
		if (($this->input->post('joueur_id') == $this->session->userdata('id') && ! $this->bouzouk->is_admin(Bouzouk::Rang_Admin) && ($rang & Bouzouk::Rang_Admin) > 0) ||
			($this->input->post('joueur_id') != $this->session->userdata('id') && ! $this->bouzouk->is_admin(Bouzouk::Rang_Admin) && ((($rang & $this->bouzouk->get_masque(Bouzouk::Masque_Admin)) > 0) || $this->input->post('groupe_tobozon') == Bouzouk::Tobozon_IdGroupeAdmins)))
		{
			$this->echec('Heuuu...et puis quoi encore ?');
			return $this->voir();
		}

		// On va chercher les infos du joueur
		$query = $this->db->select('id, pseudo, mot_de_passe, email, statut, rang, struls, faim, sante, stress, experience, duree_asile, interdit_missives, interdit_plouk, interdit_tchat, interdit_avatar, date_de_naissance, adresse, commentaire, rang_description, force, charisme, intelligence, points_action')
						  ->from('joueurs')
						  ->where('id', $this->input->post('joueur_id'))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Ce joueur n'existe pas");
			return $this->voir();
		}

		$joueur = $query->row();

		// La durée de l'asile > 0 doit être accompagnée d'une mise à l'asile
		if ($joueur->statut != Bouzouk::Joueur_Asile && $this->input->post('duree_asile') > 0 && $this->input->post('statut') != Bouzouk::Joueur_Asile)
		{
			$this->echec("Tu dois mettre le joueur à l'asile pour pouvoir indiquer une durée");
			return $this->voir();
		}
		
		// Si le pseudo a changé
		if ($this->input->post('pseudo') != $joueur->pseudo)
		{
			// On regarde s'il n'existe pas déjà
			$existe_deja = $this->db->where('pseudo', $this->input->post('pseudo'))
									->where('id !=', $this->input->post('joueur_id'))
									->count_all_results('joueurs');

			if ($existe_deja > 0)
			{
				$this->echec('Ce pseudo est déjà pris par un autre joueur');
				return $this->voir();
			}

			// On change le pseudo dans tout le tobozon
			$this->db->set('username', $this->input->post('pseudo'))
					 ->where('username', $joueur->pseudo)
					 ->update('tobozon_bans');

			$this->db->set('ident', $this->input->post('pseudo'))
					 ->where('ident', $joueur->pseudo)
					 ->update('tobozon_online');

			$this->db->set('poster', $this->input->post('pseudo'))
					 ->where('poster', $joueur->pseudo)
					 ->update('tobozon_posts');

			$this->db->set('edited_by', $this->input->post('pseudo'))
					 ->where('edited_by', $joueur->pseudo)
					 ->update('tobozon_posts');

			$this->db->set('ident', $this->input->post('pseudo'))
					 ->where('ident', $joueur->pseudo)
					 ->update('tobozon_search_cache');

			$this->db->set('poster', $this->input->post('pseudo'))
					 ->where('poster', $joueur->pseudo)
					 ->update('tobozon_topics');

			$this->db->set('last_poster', $this->input->post('pseudo'))
					 ->where('last_poster', $joueur->pseudo)
					 ->update('tobozon_topics');

			$this->db->set('username', $this->input->post('pseudo'))
					 ->where('username', $joueur->pseudo)
					 ->update('tobozon_users');

			$query = $this->db->select('id, moderators')
							  ->from('tobozon_forums')
							  ->get();
			$forums = $query->result();

			foreach ($forums as $forum)
			{
				// On remplace les modérateurs
				$moderators = str_replace('s:'.mb_strlen($joueur->pseudo).':"'.$joueur->pseudo.'"', 's:'.mb_strlen($this->input->post('pseudo')).':"'.$this->input->post('pseudo').'"', $forum->moderators);
				$this->db->set('moderators', $moderators)
						 ->where('id', $forum->id)
						 ->update('tobozon_forums');
			}
		}

		$statut = $this->input->post('statut');
				
		// Le statut n'a pas changé
		if ($statut == Bouzouk::Joueur_Asile)
			$duree_asile = $this->input->post('duree_asile');

		else
			$duree_asile = 0;
		
		// On construit l'historique des modifications
		$historique = '';

		foreach (array('pseudo' => '', 'faim' => '%', 'sante' => '%', 'stress' => '%', 'struls' => ' s', 'experience' => 'xp', 'force' => ' pts', 'charisme' => ' pts', 'intelligence' => ' pts', 'points_action' => ' pts', 'duree_asile' => 'h') as $cle => $unite)
		{
			if ($joueur->{$cle} != $this->input->post($cle))
				$historique .= '- '.str_replace('_', ' ', $cle).' : '.$joueur->{$cle}.$unite.' -&gt; '.$this->input->post($cle).$unite.'<br>';
		}

		foreach (array('interdit_missives', 'interdit_plouk', 'interdit_tchat', 'interdit_avatar') as $cle)
		{
			$avant = $joueur->{$cle} ? 'oui' : 'non';
			$apres = $this->input->post($cle) ? 'oui' : 'non';

			if ($joueur->{$cle} != $this->input->post($cle))
				$historique .= '- '.str_replace('_', ' ', $cle)." : $avant -&gt; $apres<br>";
		}

		if ($joueur->rang_description != $this->input->post('rang_description'))
			$historique .= '- titre : '.$this->input->post('rang_description').'<br>';

		foreach (array('adresse', 'commentaire') as $cle)
		{
			if ($joueur->{$cle} != $this->input->post($cle))
				$historique .= '- '.$cle.' : modifié<br>';
		}

		// On effectue les modifications
		$data_joueurs = array(
			'pseudo'            => $this->input->post('pseudo'),
			'date_de_naissance' => $this->input->post('date_de_naissance_annee').'-'.$this->input->post('date_de_naissance_mois').'-'.$this->input->post('date_de_naissance_jour'),
			'adresse'           => $this->input->post('adresse'),
			'commentaire'       => $this->input->post('commentaire'),
			'statut'            => $this->input->post('statut'),
			'date_statut'       => $this->input->post('date_statut_annee').'-'.$this->input->post('date_statut_mois').'-'.$this->input->post('date_statut_jour').' '.
								   $this->input->post('date_statut_heures').':'.$this->input->post('date_statut_minutes').':'.$this->input->post('date_statut_secondes'),
			'raison_statut'     => $this->input->post('raison_statut'),
			'rang'              => $rang,
			'rang_description'  => $this->input->post('rang_description'),
			'struls'            => $this->input->post('struls'),
			'faim'              => $this->input->post('faim'),
			'sante'             => $this->input->post('sante'),
			'stress'            => $this->input->post('stress'),
			'experience'        => $this->input->post('experience'),
			'force'             => $this->input->post('force'),
			'charisme'          => $this->input->post('charisme'),
			'intelligence'      => $this->input->post('intelligence'),
			'points_action'     => $this->input->post('points_action'),
			'duree_asile'       => $duree_asile,
			'interdit_missives' => $this->input->post('interdit_missives') != false ? 1 : 0,
			'interdit_tchat'    => $this->input->post('interdit_tchat') != false ? 1 : 0,
			'interdit_plouk'    => $this->input->post('interdit_plouk') != false ? 1 : 0,
			'interdit_avatar'   => $this->input->post('interdit_avatar') != false ? 1 : 0,
		);
		$this->db->where('id', $this->input->post('joueur_id'))
				 ->update('joueurs', $data_joueurs);

		// Si interdit d'avatar, on décoche la case du joueur, il ne pourra pas la remettre
		if ($this->input->post('interdit_avatar') != false)
		{
			$this->db->set('utiliser_avatar_toboz', 0)
					 ->where('id', $this->input->post('joueur_id'))
					 ->update('joueurs');
		}

		// On met le groupe à jour sur le tobozon
		$this->db->set('group_id', $this->input->post('groupe_tobozon'))
				 ->where('id', $this->input->post('joueur_id'))
				 ->update('tobozon_users');
				 
		// On applique des règles selon le statut
		$this->load->library('lib_joueur');

		if ($statut == Bouzouk::Joueur_Etudiant && $joueur->statut != Bouzouk::Joueur_Etudiant)
		{
			$this->db->set('notes_controuilles', '')
					 ->set('raison_statut', '')
					 ->where('id', $this->input->post('joueur_id'))
					 ->update('joueurs');
		}

		else if ($statut == Bouzouk::Joueur_Actif && $joueur->statut != Bouzouk::Joueur_Actif)
		{
			$this->db->set('raison_statut', '')
					 ->set('duree_asile', 0)
					 ->where('id', $this->input->post('joueur_id'))
					 ->update('joueurs');

			// On change son statut sur le tobozon
			$this->db->set('title', '')
					 ->where('id', $this->input->post('joueur_id'))
					 ->update('tobozon_users');
		}

		else if ($statut == Bouzouk::Joueur_Asile && $joueur->statut != Bouzouk::Joueur_Asile)
		{
			$this->lib_joueur->mettre_asile($this->input->post('joueur_id'), $this->input->post('raison_statut'), $this->input->post('duree_asile'), $this->session->userdata('id'));

			// On augmente le nombre de fois où le joueur a été envoyé à l'asile
			$this->db->set('nb_asile', 'nb_asile+1', false)
					 ->where('id', $this->input->post('joueur_id'))
					 ->update('joueurs');
		}

		else if ($statut == Bouzouk::Joueur_Pause && $joueur->statut != Bouzouk::Joueur_Pause)
		{
			$this->lib_joueur->mettre_pause($this->input->post('joueur_id'));
		}

		else if ($statut == Bouzouk::Joueur_GameOver && $joueur->statut != Bouzouk::Joueur_GameOver)
		{
			$this->lib_joueur->mettre_game_over($this->input->post('joueur_id'));
		}

		else if ($statut == Bouzouk::Joueur_Banni && $joueur->statut != Bouzouk::Joueur_Banni)
		{
			$this->lib_joueur->bannir($this->input->post('joueur_id'), $this->input->post('raison_statut'), $this->session->userdata('id'));

			$this->load->library('lib_tobozon');
			$this->lib_tobozon->bannir($this->input->post('joueur_id'));
		}

		// On ajoute à l'historique modération
		$this->bouzouk->historique_moderation(profil().' a modifié le profil de '.profil($joueur->id, $joueur->pseudo).' :<br>'.$historique);

		// On ajoute à l'historique du joueur
		if ($historique != '')
			$this->bouzouk->historique(34, null, array(profil(-1, '', $this->session->userdata('rang')), $historique), $joueur->id, Bouzouk::Historique_Full);
		
		$this->bouzouk->augmente_version_session($joueur->id);

		// On affiche un message de confirmation
		$this->succes(profil($joueur->id, $joueur->pseudo, $rang).' a bien été modifié');
		return $this->voir();
	}

	public function donner_objet()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur_id', 'Le joueur', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('objet_id', "L'objet", 'required|is_natural_no_zero');
		$this->form_validation->set_rules('peremption', "La préremption", 'required|is_numeric|less_than_or_equal[1000]');
		$this->form_validation->set_rules('quantite', 'La quantité', 'required|is_natural_no_zero|less_than_or_equal[1000]');

		if ( ! $this->form_validation->run())
		{
			return $this->voir($this->input->post('joueur_id'));
		}

		// On vérifie que le joueur existe
		$existe = $this->db->where('id', $this->input->post('joueur_id'))
						   ->count_all_results('joueurs');

		if ( ! $existe)
		{
			$this->echec("Ce joueur n'existe pas");
			return $this->voir($this->input->post('joueur_id'));
		}

		// On vérifie que l'objet existe
		$query = $this->db->select('id, nom')
						  ->from('objets')
						  ->where('id', $this->input->post('objet_id'))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Cet objet n'existe pas");
			return $this->voir($this->input->post('joueur_id'));
		}

		$objet = $query->row();

		// On ajoute la quantité au joueur
		$this->bouzouk->ajouter_objets($this->input->post('objet_id'), $this->input->post('quantite'), $this->input->post('peremption'), $this->input->post('joueur_id'));

		// La malédiction du Schnibble entraîne une mise à jour de la session du joueur
		if ($objet->id == 49)
			$this->bouzouk->augmente_version_session($this->input->post('joueur_id'));

		// On ajoute à l'historique
		$this->bouzouk->historique(35, null, array(profil(-1, '', $this->session->userdata('rang')), $this->input->post('quantite'), $objet->nom), $this->input->post('joueur_id'), Bouzouk::Historique_Full);

		// On affiche un message de confirmation
		$this->succes("La quantité demandée a bien été ajoutée à ce joueur");
		return $this->voir($this->input->post('joueur_id'));
	}

	public function envoyer_notification()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur_id', 'Le joueur', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('texte', 'Le texte', 'required|max_length[500]');

		if ( ! $this->form_validation->run())
		{
			return $this->voir($this->input->post('joueur_id'));
		}

		// On vérifie que le joueur existe
		$existe = $this->db->where('id', $this->input->post('joueur_id'))
						   ->count_all_results('joueurs');

		if ( ! $existe)
		{
			$this->echec("Ce joueur n'existe pas");
			return $this->voir($this->input->post('joueur_id'));
		}

		// On poste la notif
		$this->bouzouk->notification(36, array($this->input->post('texte')), $this->input->post('joueur_id'));
		
		// On affiche un message de confirmation
		$this->succes("La notification a bien été envoyée à ce joueur");
		return $this->voir($this->input->post('joueur_id'));
	}

	public function supprimer_compte()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur_id', 'Le joueur', 'required|is_natural_no_zero');
		
		if ( ! $this->form_validation->run())
			return $this->voir($this->input->post('joueur_id'));
		
		// On vérifie que le joueur existe
		$existe = $this->db->where('id', $this->input->post('joueur_id'))
						   ->count_all_results('joueurs');

		if ( ! $existe)
		{
			$this->echec("Ce joueur n'existe pas");
			return $this->voir($this->input->post('joueur_id'));
		}
		
		// On supprime le compte
		$this->load->library('lib_joueur');
		$this->lib_joueur->supprimer_joueur($this->input->post('joueur_id'));

		// On affiche un message de confirmation
		$this->succes("Ce compte a bien été supprimé");
		return $this->index();
	}
}
