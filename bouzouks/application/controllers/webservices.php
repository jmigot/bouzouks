<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : webservices destinés principalement aux requêtes Ajax du jeu (rumeurs, salons de discussion instantannée...)
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class WebServices extends MY_Controller
{
	private $tchat_options;
	
	public function __construct()
	{
		parent::__construct();

		// Si ce controller n'est pas appelé en Ajax ou en Post
		if ( ! $this->input->is_ajax_request() || ! $this->input->isPost())
			show_404();

		$this->tchat_options = array(
			Bouzouk::Tchat_Entreprise  => array('table_messages' => 'tchats_entreprises', 'table_connectes' => 'tchats_entreprises_connectes', 'tchat_id' => '', 'limite' => $this->bouzouk->config('maintenance_tchats_messages_entreprise')),
			Bouzouk::Tchat_Asile       => array('table_messages' => 'tchats', 'table_connectes' => 'tchats_connectes', 'tchat_id' => Bouzouk::Tchat_Asile, 'limite' => $this->bouzouk->config('maintenance_tchats_messages')),
			Bouzouk::Tchat_Journal     => array('table_messages' => 'tchats', 'table_connectes' => 'tchats_connectes', 'tchat_id' => Bouzouk::Tchat_Journal, 'limite' => $this->bouzouk->config('maintenance_tchats_messages')),
			Bouzouk::Tchat_Chomeur     => array('table_messages' => 'tchats', 'table_connectes' => 'tchats_connectes', 'tchat_id' => Bouzouk::Tchat_Chomeur, 'limite' => $this->bouzouk->config('maintenance_tchats_messages')),
			Bouzouk::Tchat_Mendiant    => array('table_messages' => 'tchats', 'table_connectes' => 'tchats_connectes', 'tchat_id' => Bouzouk::Tchat_Mendiant, 'limite' => $this->bouzouk->config('maintenance_tchats_messages')),
			Bouzouk::Tchat_Clan        => array('table_messages' => 'tchats_clans', 'table_connectes' => 'tchats_clans_connectes', 'tchat_id' => '', 'limite' => $this->bouzouk->config('maintenance_tchats_messages_clan')),
			Bouzouk::Tchat_Convocation => array('table_messages' => 'tchats_convocations', 'table_connectes' => 'tchats_convocations_connectes', 'tchat_id' => '', 'limite' => $this->bouzouk->config('maintenance_tchats_messages')),
		);
		
		// On renvoit du JSON
		$this->output->set_content_type('application/json');
	}

	public function recharger_rumeurs()
	{
		// On récupère des rumeurs aléatoires qui sont validées
		$query = $this->db->select('texte')
						  ->from('rumeurs')
						  ->where('statut', Bouzouk::Rumeur_Validee)
						  ->order_by('id', 'random')
						  ->limit(30)
						  ->get();

		// On créé un tableau de rumeurs
		$rumeurs = array();
		foreach ($query->result() as $rumeur)
			$rumeurs[] = $rumeur->texte;

		// On affiche les données
		return $this->output->set_output(json_encode($rumeurs));
	}

	private function rafraichir_tchat($type, $invisible = false)
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('dernier_id', "Le dernier identifiant", 'required|is_natural');

		if ( ! $this->form_validation->run())
			return $this->output->set_output(json_encode(''));

		$reponse = array('messages' => array(), 'connectes' => array());

		// On met à jour le champ connecte (sauf si connecté depuis l'interface admin ou membre invisible du clan)
		$indice = array_search($this->tchat_options[$type]['tchat_id'], $this->session->userdata('clan_id'));
		if ( ! $invisible && ! $this->session->userdata('admin_connecte') && ! ($indice !== false && $type == Bouzouk::Tchat_Clan && $this->session->userdata('clan_invisible')[$indice]))
		{
			$present = $this->db->where('joueur_id', $this->session->userdata('id'))
								->where('tchat_id', $this->tchat_options[$type]['tchat_id'])
								->count_all_results($this->tchat_options[$type]['table_connectes']);

			if ($present)
			{
				$this->db->set('derniere_visite', bdd_datetime())
						 ->where('joueur_id', $this->session->userdata('id'))
						 ->where('tchat_id', $this->tchat_options[$type]['tchat_id'])
						 ->update($this->tchat_options[$type]['table_connectes']);
			}

			else
			{
				$data_connectes = array(
					'joueur_id'       => $this->session->userdata('id'),
					'tchat_id'        => $this->tchat_options[$type]['tchat_id'],
					'derniere_visite' => bdd_datetime()
				);
				$this->db->insert($this->tchat_options[$type]['table_connectes'], $data_connectes);
			}
		}

		// On va chercher la liste des connectés
		$query = $this->db->select('j.id, j.pseudo, j.rang')
						  ->from($this->tchat_options[$type]['table_connectes'].' tc')
						  ->join('joueurs j', 'j.id = tc.joueur_id')
						  ->where('tc.tchat_id', $this->tchat_options[$type]['tchat_id'])
						  ->where('tc.derniere_visite > (NOW() - INTERVAL 10 SECOND)')
						  ->order_by('j.pseudo')
						  ->get();

		foreach ($query->result() as $joueur)
			$reponse['connectes'][] = profil($joueur->id, $joueur->pseudo, $joueur->rang);
				
		// On va chercher les derniers messages
		$query = $this->db->select('tm.id, j.id AS joueur_id, j.pseudo, tm.message, tm.date_envoi')
						  ->from($this->tchat_options[$type]['table_messages'].' tm')
						  ->join('joueurs j', 'j.id = tm.joueur_id')
						  ->where('tm.id >', $this->input->post('dernier_id'))
						  ->where('tm.tchat_id', $this->tchat_options[$type]['tchat_id'])
						  ->order_by('tm.id', 'desc')
						  ->limit($this->tchat_options[$type]['limite'])
						  ->get();
		$messages = array();

		$pattern_lien_interne = preg_quote(site_url('tobozon'));

		foreach ($query->result() as $message)
		{
			$pseudo = profil($message->joueur_id, $message->pseudo);

			// Le /me apparait en couleur
			if (mb_substr($message->message, 0, 4) == '/me ')
				$pseudo = '<span class="noir">'.$message->pseudo.'</span>';

			$message->message = form_prep($message->message);

			// On remplace les liens internes
			$message->message = preg_replace('#('.$pattern_lien_interne.'/[^\s]*)#i', '<a href="$1">$1</a>', $message->message);

			$messages[] = array(
				'id'        => $message->id,
				'pseudo'    => $pseudo,
				'message'   => remplace_smileys($message->message),
				'date'      => tchat_datetime($message->date_envoi)
			);
		}

		// On renverse l'ordre pour afficher les vieux messages en premier
		$reponse['messages'] = array_reverse($messages);
		return $this->output->set_output(json_encode($reponse));
	}

	public function rafraichir_tchat_entreprise($entreprise_id = null, $clan_id_espionneur = null)
	{
		// Si l'entreprise est renseignée, le joueur doit être soit modérateur, soit en mode espionnage
		if (isset($entreprise_id))
		{
			// Si pas modérateur
 			if ( ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats))
 			{
 				// Si le joueur n'est pas dans ce clan
				if ( ! in_array((int)$clan_id_espionneur, $this->session->userdata('clan_id')))
					return $this->output->set_output(json_encode(''));

 				// Si pas d'espionnage valide en cours dans cette entreprise
 				$this->load->library('lib_clans');
 				$espionnage = $this->lib_clans->espionnage_en_cours($clan_id_espionneur);

 				if ( ! isset($espionnage) || ! $espionnage->valide)
 					return $this->output->set_output(json_encode(''));
 			}
		}

		// Sinon le joueur doit être actif et dans son entreprise
		else if ($this->session->userdata('statut') != Bouzouk::Joueur_Actif || ! $this->session->userdata('entreprise_id'))
			return $this->output->set_output(json_encode(''));
		
		$this->tchat_options[Bouzouk::Tchat_Entreprise]['tchat_id'] = isset($entreprise_id) ? $entreprise_id : $this->session->userdata('entreprise_id');
		return $this->rafraichir_tchat(Bouzouk::Tchat_Entreprise, isset($espionnage));
	}
	
	public function rafraichir_tchat_asile()
	{
		// Joueur modérateur ou à l'asile
		if ( ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats | Bouzouk::Rang_ModerateurProfils) && $this->session->userdata('statut') != Bouzouk::Joueur_Asile)
			return $this->output->set_output(json_encode(''));

		return $this->rafraichir_tchat(Bouzouk::Tchat_Asile);
	}

	public function rafraichir_tchat_journalistes()
	{
		// Joueur ou journaliste
		if ( ! $this->bouzouk->is_journaliste())
			return $this->output->set_output(json_encode(''));

		return $this->rafraichir_tchat(Bouzouk::Tchat_Journal);
	}

	public function rafraichir_tchat_chomeur()
	{
		// Joueur chômeur ou patron
		if ( ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats) && ($this->session->userdata('employe')))
			return $this->output->set_output(json_encode(''));

		return $this->rafraichir_tchat(Bouzouk::Tchat_Chomeur);
	}
	
	public function rafraichir_tchat_mendiant()
	{
		// Joueur mendiant
		if ( ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats) && ( ! $this->session->userdata('mendiant')))
			return $this->output->set_output(json_encode(''));

		return $this->rafraichir_tchat(Bouzouk::Tchat_Mendiant);
	}

	public function rafraichir_tchat_clan($clan_id = null, $clan_id_espionneur = null)
	{
		// Si le clan espionneur est renseignée, le joueur doit être en mode espionnage
		if (isset($clan_id_espionneur))
		{
			// Si le joueur n'est pas dans ce clan
			if ( ! in_array((int)$clan_id_espionneur, $this->session->userdata('clan_id')))
				return $this->output->set_output(json_encode(''));

			// Si pas d'espionnage valide en cours dans ce clan
 			$this->load->library('lib_clans');
 			$espionnage = $this->lib_clans->espionnage_en_cours($clan_id_espionneur);

 			if ( ! isset($espionnage) || ! $espionnage->valide)
 				return $this->output->set_output(json_encode(''));
		}

		// Joueur actif et dans le clan
		else if ($this->session->userdata('statut') != Bouzouk::Joueur_Actif || ! in_array((int)$clan_id, $this->session->userdata('clan_id')))
		{
			// Si pas modérateur
 			if ( ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats) && ! $this->bouzouk->is_mdj())
 				return $this->output->set_output(json_encode(''));
 		}

		$this->tchat_options[Bouzouk::Tchat_Clan]['tchat_id'] = $clan_id;
		return $this->rafraichir_tchat(Bouzouk::Tchat_Clan, isset($espionnage));
	}
	
	public function rafraichir_tchat_convocation($convocation_id = null)
	{
		if ( ! (int)$convocation_id == $this->session->userdata('convocation_id'))
		{
			// Si pas modérateur
 			if ( ! $this->bouzouk->is_moderateur())
 				return $this->output->set_output(json_encode(''));
 		}

		$this->tchat_options[Bouzouk::Tchat_Convocation]['tchat_id'] = $convocation_id;
		return $this->rafraichir_tchat(Bouzouk::Tchat_Convocation);
	}

	private function poster_tchat($type)
	{
		// Interdit de tchat
		if ($this->session->userdata('interdit_tchat') == 1)
			return $this->output->set_output(json_encode(''));

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('message', "Le message", 'required|max_length[150]');

		if ( ! $this->form_validation->run())
			return $this->output->set_output(json_encode(''));

		// Anti message double
		if ($this->input->post('message') == $this->session->userdata('dernier_message_tchat'))
			return $this->output->set_output(json_encode(''));

		$this->session->set_userdata('dernier_message_tchat', $this->input->post('message'));

		// On enregistre le message
		$data_tchat = array(
			'joueur_id'     => $this->session->userdata('id'),
			'message'       => $this->input->post('message'),
			'tchat_id'      => $this->tchat_options[$type]['tchat_id'],
			'date_envoi'    => bdd_datetime()
		);

		$this->db->insert($this->tchat_options[$type]['table_messages'], $data_tchat);
		return $this->output->set_output(json_encode(''));
	}

	public function poster_tchat_entreprise($entreprise_id = null)
	{
		// Si entreprise renseignée, le joueur doit être modérateur
		if (isset($entreprise_id))
		{
			if ( ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats))
				return $this->output->set_output(json_encode(''));
		}

		// Joueur doit être actif dans l'entreprise
		else if ($this->session->userdata('statut') != Bouzouk::Joueur_Actif || ! $this->session->userdata('entreprise_id'))
			return $this->output->set_output(json_encode(''));

		$this->tchat_options[Bouzouk::Tchat_Entreprise]['tchat_id'] = isset($entreprise_id) ? $entreprise_id : $this->session->userdata('entreprise_id');
		return $this->poster_tchat(Bouzouk::Tchat_Entreprise);
	}

	public function poster_tchat_asile()
	{
		// Joueur modérateur ou à l'asile
		if ( ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats | Bouzouk::Rang_ModerateurProfils) && $this->session->userdata('statut') != Bouzouk::Joueur_Asile)
			return $this->output->set_output(json_encode(''));

		return $this->poster_tchat(Bouzouk::Tchat_Asile);
	}

	public function poster_tchat_journalistes()
	{
		// Joueur ou journaliste
		if ( ! $this->bouzouk->is_journaliste())
			return $this->output->set_output(json_encode(''));

		return $this->poster_tchat(Bouzouk::Tchat_Journal);
	}
	
	public function poster_tchat_chomeur()
	{
		// Joueur chômeur ou patron
		if ( ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats) && ($this->session->userdata('employe')))
			return $this->output->set_output(json_encode(''));

		return $this->poster_tchat(Bouzouk::Tchat_Chomeur);
	}
	
	public function poster_tchat_mendiant()
	{
		// Joueur mendiant
		if ( ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats) && ( ! $this->session->userdata('mendiant')))
			return $this->output->set_output(json_encode(''));

		return $this->poster_tchat(Bouzouk::Tchat_Mendiant);
	}
	
	public function poster_tchat_clan($clan_id = null)
	{
		// Le joueur doit être actif et dans ce clan ou modérateur
		if ( ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats) && ! $this->bouzouk->is_mdj())
		{
			if ($this->session->userdata('statut') != Bouzouk::Joueur_Actif || ! in_array($clan_id, $this->session->userdata('clan_id')))
				return $this->output->set_output(json_encode(''));
		}

		$this->tchat_options[Bouzouk::Tchat_Clan]['tchat_id'] = $clan_id;
		return $this->poster_tchat(Bouzouk::Tchat_Clan);
	}
	
	public function poster_tchat_convocation($convocation_id = null)
	{
		// Le joueur doit être actif et dans ce clan ou modérateur
		if ( ! $this->bouzouk->is_moderateur())
		{
			if ( ! (int)$convocation_id == $this->session->userdata('convocation_id'))
				return $this->output->set_output(json_encode(''));
		}

		$this->tchat_options[Bouzouk::Tchat_Convocation]['tchat_id'] = $convocation_id;
		return $this->poster_tchat(Bouzouk::Tchat_Convocation);
	}

	private function supprimer_tchat($table)
	{
		// Si le joueur n'est pas modérateur tchats
		if ( ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats) && ! ($table == 'tchats_clans' && $this->bouzouk->is_mdj()))
			return $this->output->set_output(json_encode(''));

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('messages_ids', "Les identifiants de message", 'required');

		if ( ! $this->form_validation->run())
		{
			return $this->output->set_output(json_encode(''));
		}

		// On récupère les infos du message
		$query = $this->db->select('joueur_id, COUNT(joueur_id) AS nb_messages')
						  ->from($table)
						  ->where_in('id', $this->input->post('messages_ids'))
						  ->group_by('joueur_id')
						  ->get();
		$messages = $query->result();

		// On envoit une notification aux joueurs censurés
		foreach ($messages as $message)
			$this->bouzouk->historique(147, null, array(profil(-1, '', $this->session->userdata('rang')), pluriel($message->nb_messages, 'message')), $message->joueur_id, Bouzouk::Historique_Full);
		
		// On supprime le message
		$this->db->where_in('id', $this->input->post('messages_ids'))
				 ->delete($table);

		// On ajoute à l'historique modération
		$this->bouzouk->historique_moderation(profil()." a supprimé des messages $table");
			
		return $this->output->set_output(json_encode(''));
	}

	public function supprimer_tchat_entreprise()
	{
		return $this->supprimer_tchat('tchats_entreprises');
	}

	public function supprimer_tchat_clan()
	{
		return $this->supprimer_tchat('tchats_clans');
	}

	public function supprimer_tchat_asile()
	{
		return $this->supprimer_tchat('tchats');
	}

	public function supprimer_tchat_chomeurs()
	{
		return $this->supprimer_tchat('tchats');
	}

	public function supprimer_tchat_mendiants()
	{
		return $this->supprimer_tchat('tchats');
	}

	public function nb_bouzouks_tranche_don()
	{
		// On vérifie que le joueur est le maire ou un admin
		if ( ! $this->bouzouk->is_maire() AND ! $this->bouzouk->is_admin())
		{
			return $this->output->set_output(json_encode(''));
		}

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('min_struls', 'La fortune minimum', 'required|is_natural');
		$this->form_validation->set_rules('max_struls', "La fortune maximum", 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
		{
			return $this->output->set_output(json_encode(''));
		}

		// Le montant minimum doit être inférieur de 5 struls au moins par rapport au montant maximum
		if ($this->input->post('min_struls') > $this->input->post('max_struls') - 5)
		{
			return $this->output->set_output(json_encode(''));
		}

		$reponse = array(
			'nb_bouzouks' => 0
		);

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

		// Si on est par fortune
		if ($this->input->post('par_fortune') != 'false')
		{
			// Struls + maison
			$query = $this->db->select('j.id, j.pseudo, j.struls, SUM(m.quantite * o.prix) AS struls_maison')
							  ->from('joueurs j')
							  ->join('maisons m', 'm.joueur_id = j.id', 'left')
							  ->join('objets o', 'o.id = m.objet_id', 'left')
							  ->where('j.id !=', $this->session->userdata('id'))
							  ->where('j.statut', Bouzouk::Joueur_Actif)
							  ->group_by('j.id')
							  ->order_by('j.struls', 'desc')
							  ->get();
			$joueurs = $query->result();
			$nb_joueurs_concernes = 0;

			foreach ($joueurs as $joueur)
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
					$nb_joueurs_concernes++;
			}

			$reponse['nb_bouzouks'] = $nb_joueurs_concernes;
		}

		else
		{
			$reponse['nb_bouzouks'] = $this->db->where('struls BETWEEN '.$this->input->post('min_struls').' AND '.$this->input->post('max_struls'))
											   ->where('id !=', $this->session->userdata('id'))
											   ->where('statut', Bouzouk::Joueur_Actif)
											   ->count_all_results('joueurs');

			// Magouille fiscale (Struleone) : si le minimum de struls est de 0 on rajoute aussi les magouilleurs
			if ($this->input->post('min_struls') == 0 && isset($magouille_fiscale))
			{
				$reponse['nb_bouzouks'] += $this->db->where_in('id', $magouilleurs)
													->where('id !=', $this->session->userdata('id'))
													->where('statut', Bouzouk::Joueur_Actif)
													->count_all_results('joueurs');
			}

			else if ($this->input->post('min_struls') > 0 && isset($magouille_fiscale))
			{
				$reponse['nb_bouzouks'] -= $this->db->where_in('id', $magouilleurs)
													->where('id !=', $this->session->userdata('id'))
													->where('statut', Bouzouk::Joueur_Actif)
													->count_all_results('joueurs');
			}
		}

		// On affiche les données
		return $this->output->set_output(json_encode($reponse));
	}

	public function previsualisation_missive()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('objet', "L'objet", 'required');
		$this->form_validation->set_rules('message', 'Le message', 'required');
		$this->form_validation->set_rules('timbre', 'Le timbre', 'required');
		$this->form_validation->set_rules('expediteur_robot', 'Expediteur robot', 'required');
		$this->form_validation->set_rules('destinataire_id', 'Le destinataire', 'required|is_natural');

		if ( ! $this->form_validation->run())
			return $this->output->set_output(json_encode(''));

		// Seuls les admins peuvent avoir un expediteur_robot à true
		if ($this->input->post('expediteur_robot') == 1 && ! $this->bouzouk->is_admin())
			return $this->output->set_output(json_encode(''));

		// On va chercher le nom et l'adresse du destinataire
		$query = $this->db->select('id, pseudo, adresse')
						  ->from('joueurs')
						  ->where('id', $this->input->post('destinataire_id'))
						  ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))
						  ->get();

		if ($query->num_rows() == 1)
			$destinataire = $query->row();
			
		else
		{
			$destinataire = new StdClass;
			$destinataire->id = 1;
			$destinataire->pseudo = 'Destinataire';
			$destinataire->adresse = 'Adresse du destinataire';
		}
			
		// On génère le html
		$vars = array(
			'date_envoi'       => bdd_datetime(),
			'objet'            => $this->input->post('objet'),
			'message'          => $this->input->post('message'),
			'timbre'           => $this->input->post('timbre'),
			'expediteur_robot' => $this->input->post('expediteur_robot') == 1,
			'destinataire'     => $destinataire
		);
		$this->load->library('lib_parser');
		$html = $this->load->view('missives/previsualisation', $vars, true);

		$reponse = array(
			'html' => $html
		);

		// On affiche les données
		return $this->output->set_output(json_encode($reponse));
	}

	public function previsualisation_texte()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('texte', 'Le texte', 'required');

		if ( ! $this->form_validation->run())
			return $this->output->set_output(json_encode(''));
		
		if ($this->session->userdata('statut') != Bouzouk::Joueur_Actif)
			return $this->output->set_output(json_encode(''));

		// On remplace le bbcode
		$this->load->library('lib_parser');
		$texte = $this->lib_parser->remplace_bbcode(nl2br($this->input->post('texte')));

		// On affiche les données
		return $this->output->set_output(json_encode(array('html' => $texte)));
	}
	
	public function rafraichir_notifications()
	{
		// Le joueur doit être actif ou à l'asile
		if ( ! in_array($this->session->userdata('statut'), array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile)))
			return $this->output->set_output(json_encode(''));

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('dernier_id', "Le dernier identifiant", 'required|is_natural');

		if ( ! $this->form_validation->run())
			return $this->output->set_output(json_encode(''));

		// On va chercher les dernières notifications
		$query = $this->db->select('h.id, h.donnees, ht.texte, h.date, h.lue')
						  ->from('historique h')
						  ->join('historique_textes ht', 'h.texte_id_private = ht.id')
						  ->where('h.id >', $this->input->post('dernier_id'))
						  ->where('h.joueur_id', $this->session->userdata('id'))
						  ->where_in('h.notification', array(Bouzouk::Historique_Notification, Bouzouk::Historique_Full))
						  ->order_by('h.id', 'desc')
						  ->limit(7)
						  ->get();
		$notifications = array();

		foreach ($query->result() as $notif)
		{	
			$notifications[] = array(
				'id'    => $notif->id,
				'lue'   => $notif->lue,
				'texte' => $this->bouzouk->construire_historique($notif),
				'date'  => tchat_datetime($notif->date)
			);
		}

		// On renverse l'ordre pour afficher les vieux messages en premier
		$reponse['notifications'] = array_reverse($notifications);

		// On regarde le nombre de notifs non lues
		$reponse['nb_notifs'] = $this->db->where('joueur_id', $this->session->userdata('id'))
										 ->where_in('notification', array(Bouzouk::Historique_Notification, Bouzouk::Historique_Full))
										 ->where('lue', 0)
										 ->count_all_results('historique');
		return $this->output->set_output(json_encode($reponse));
	}

	public function marquer_lues_notifications()
	{
		// Le joueur doit être actif ou à l'asile
		if ( ! in_array($this->session->userdata('statut'), array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile)))
			return $this->output->set_output(json_encode(''));

		// On va chercher l'id de la 7ème notif du joueur
		$query = $this->db->select('id')
						  ->from('historique')
						  ->where('joueur_id', $this->session->userdata('id'))
						  ->where_in('notification', array(Bouzouk::Historique_Notification, Bouzouk::Historique_Full))
						  ->order_by('id', 'desc')
						  ->limit(1, 7)
						  ->get();

		if ($query->num_rows() == 0)
			$limite = 0;

		else
		{
			$historique = $query->row();
			$limite = $historique->id;
		}

		// On marque toutes les notifications comme lues
		$this->db->set('lue', 1)
				 ->where('joueur_id', $this->session->userdata('id'))
				 ->where_in('notification', array(Bouzouk::Historique_Notification, Bouzouk::Historique_Full))
				 ->where('id >', $limite)
				 ->update('historique');

		// On regarde le nombre de notifs non lues
		$reponse = array('code' => 'ok');
		$reponse['nb_notifs'] = $this->db->where('joueur_id', $this->session->userdata('id'))
										 ->where_in('notification', array(Bouzouk::Historique_Notification, Bouzouk::Historique_Full))
										 ->where('lue', 0)
										 ->count_all_results('historique');
		return $this->output->set_output(json_encode($reponse));
	}

	public function tobozon_like()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('post_id', "Le post", 'required|is_natural_no_zero');
		
		if ( ! $this->form_validation->run())
		{
			return $this->output->set_output(json_encode(''));
		}

		// On vérifie que le post existe
		$query = $this->db->select('id, poster_id, topic_id')
						  ->from('tobozon_posts')
						  ->where('id', $this->input->post('post_id'))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$reponse = array(
				'result'  => 0,
				'message' => "Ce post n'existe pas"
			);
			return $this->output->set_output(json_encode($reponse));
		}

		$post = $query->row();

		// On vérifie que le joueur a le droit de lire ce topic et qu'il est RP
		$query = $this->db->query('SELECT tc.cat_name '.
								  'FROM tobozon_topics t '.
								  'JOIN tobozon_forums f ON f.id = t.forum_id '.
								  'JOIN tobozon_categories tc ON tc.id = f.cat_id '.
								  'LEFT JOIN tobozon_forum_perms fp ON (fp.forum_id = f.id AND fp.group_id = '.$this->session->userdata('tobozon_group_id').') '.
								  'WHERE (fp.read_forum IS NULL OR fp.read_forum = 1) AND t.id='.$post->topic_id.' AND t.moved_to IS NULL');

		if ($query->num_rows() == 0)
		{
			$reponse = array(
				'result'  => 0,
				'message' => "Tu n'as pas le droit de lire ce topic"
			);
			return $this->output->set_output(json_encode($reponse));
		}

		$topic = $query->row();

		if (preg_match('#\[Hors RP\]#', $topic->cat_name))
		{
			$reponse = array(
				'result'  => 0,
				'message' => "On ne peut pas utiliser cette fonction sur un forum hors rôle-play ! Mouaha !"
			);
			return $this->output->set_output(json_encode($reponse));
		}

		// On vérifie que le joueur n'a pas déjà liké
		$existe = $this->db->where('post_id', $this->input->post('post_id'))
						   ->where('joueur_id', $this->session->userdata('id'))
						   ->count_all_results('tobozon_likes');

		if ($existe)
		{
			$reponse = array(
				'result'  => 0,
				'message' => "Tu aime déjà ce post...sale pochtron..."
			);
			return $this->output->set_output(json_encode($reponse));
		}

		// On ajoute 1 like au post
		$this->db->set('likes', 'likes+1', false)
				 ->where('id', $this->input->post('post_id'))
				 ->update('tobozon_posts');

		// On enregistre le like
		$data_tobozon_likes = array(
			'post_id'   => $this->input->post('post_id'),
			'joueur_id' => $this->session->userdata('id')
		);
		$this->db->insert('tobozon_likes', $data_tobozon_likes);

		$this->load->library('lib_notifications');

		// On envoit une notification au poster
		if ($this->lib_notifications->notifier(Bouzouk::Notification_ZlikeTobozon, $post->poster_id))
			$this->bouzouk->notification(148, array(profil($this->session->userdata('id')), site_url('tobozon/viewtopic.php?pid='.$post->id.'#p'.$post->id)), $post->poster_id);

		// Si le joueur a fait trop de zlikes d'un coup, on sanctionne
		/*if ($this->session->userdata('zlikes') === false)
			$this->session->set_userdata('zlikes', array());

		if (isset($this->session->userdata('zlikes')[$this->input->post('post_id')]))
			$this->session->userdata('zlikes')[$this->input->post('post_id')]++;
		else
			$this->session->userdata('zlikes')[$this->input->post('post_id')] = 1;

		if ($this->session->userdata('zlikes')[$this->input->post('post_id')] == 20)
		{
			unset($this->session->userdata('zlikes')[$this->input->post('post_id')]);

			// On envoit une facture
			$data_factures = array(
				'joueur_id'  => $this->session->userdata('id'),
				'titre'      => 'Groupietude',
				'montant'    => 50,
				'majoration' => 0,
				'date'       => bdd_datetime()
			);
			$this->db->insert('factures', $data_factures);

			// Message
			$message  = "	Bonjour ".$this->session->userdata('pseudo')."\n\n";
			$message .= "Le Percepteur s'est aperçu que tu avais des activités de groupie trop importantes et qui ne garantissent pas la sécurité de la villen.\n\n";
			$message .= "Tu as donc été condamné par un tribunal exceptionnel à verser la somme de ".struls($this->bouzouk->config('factures_montant_taxe_anniversaire'))." à la mairie avant ".$this->bouzouk->config('factures_delai_majoration')." jours. Passé ce délai, tu ne seras plus accepté dans aucun service ni aucune boutique de la ville de Vlurxtrznbnaxl nécessitant de l'argent et tu devras travailler dur afin de rembourser ta dette majorée de ".$this->bouzouk->config('factures_pourcent_majoration')."%.\n\n";
			$message .= "<a href='".site_url('factures')."'>Payer mes factures</a>\n\n";
			$message .= "Merci de freiner tes ardeurs :)\n\n";
			$message .= "	Cordialement, le percepteur de Vlurxtrznbnaxl";

			$this->load->library('lib_missive');

			$data_missives = array(
				'expediteur_id'   => Bouzouk::Robot_Percepteur,
				'destinataire_id' => $this->session->userdata('id'),
				'date_envoi'      => bdd_datetime(),
				'timbre'          => $this->lib_missive->timbres(0),
				'objet'           => 'Over zlike...',
				'message'         => $message
			);
			$this->db->insert('missives', $data_missives);
		}*/

		// On affiche une confirmation
		$reponse = array(
			'result'  => 1,
			'message' => ''
		);

		if ($post->poster_id == $this->session->userdata('id'))
			$reponse['message'] = "Tu aimes ton propre post ? Alors toi dans le genre nombril de Vlurxtrznbnaxl t'es un champion...";

		return $this->output->set_output(json_encode($reponse));
	}

	public function tobozon_like_plus()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('post_id', "Le post", 'required|is_natural_no_zero');
		
		if ( ! $this->form_validation->run())
		{
			return $this->output->set_output(json_encode(''));
		}

		// On enlève le like
		$this->db->where('post_id', $this->input->post('post_id'))
			     ->where('joueur_id', $this->session->userdata('id'))
			     ->delete('tobozon_likes');

		if ($this->db->affected_rows() == 0)
		{
			$reponse = array(
				'result'  => 0,
				'message' => "Tu n'as pas encore aimé ce post"
			);
			return $this->output->set_output(json_encode($reponse));
		}

		// On enlève 1 like au post
		$this->db->set('likes', 'likes-1', false)
				 ->where('id', $this->input->post('post_id'))
				 ->update('tobozon_posts');

		// On affiche une confirmation
		$reponse = array(
			'result'  => 1,
		);
		return $this->output->set_output(json_encode($reponse));
	}

	public function tobozon_like_bouzouks()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('post_id', "Le post", 'required|is_natural_no_zero');
		
		if ( ! $this->form_validation->run())
		{
			return $this->output->set_output(json_encode(''));
		}

		// On vérifie que le post existe
		$query = $this->db->select('topic_id')
						  ->from('tobozon_posts')
						  ->where('id', $this->input->post('post_id'))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$reponse = array(
				'result'  => 0,
				'message' => "Ce post n'existe pas"
			);
			return $this->output->set_output(json_encode($reponse));
		}

		$post = $query->row();

		// On vérifie que le joueur a le droit de lire ce topic et qu'il est RP
		$query = $this->db->query('SELECT tc.cat_name '.
								  'FROM tobozon_topics t '.
								  'JOIN tobozon_forums f ON f.id = t.forum_id '.
								  'JOIN tobozon_categories tc ON tc.id = f.cat_id '.
								  'LEFT JOIN tobozon_forum_perms fp ON (fp.forum_id = f.id AND fp.group_id = '.$this->session->userdata('tobozon_group_id').') '.
								  'WHERE (fp.read_forum IS NULL OR fp.read_forum = 1) AND t.id='.$post->topic_id.' AND t.moved_to IS NULL');

		if ($query->num_rows() == 0)
		{
			$reponse = array(
				'result'  => 0,
				'message' => "Tu n'as pas le droit de lire ce topic"
			);
			return $this->output->set_output(json_encode($reponse));
		}

		// On va chercher les bouzouks qui ont liké ce post
		$query = $this->db->select('j.id, j.pseudo, j.rang')
						  ->from('tobozon_likes tl')
						  ->join('joueurs j', 'j.id = tl.joueur_id')
						  ->where('tl.post_id', $this->input->post('post_id'))
						  ->order_by('j.pseudo')
						  ->get();

		$joueurs = $query->result();

		// On affiche
		$reponse = array(
			'html' => $this->load->view('liste_likers', array('joueurs' => $joueurs), true)
		);
		return $this->output->set_output(json_encode($reponse));
	}
}

