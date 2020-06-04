<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : webservices destinés aux clans
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : août 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class WebServices_clans extends MY_Controller
{
	private $types;

	public function __construct()
	{
		parent::__construct();

		// Si ce controller n'est pas appelé en Ajax ou en Post
		if ( ! $this->input->is_ajax_request() || ! $this->input->isPost())
			show_404();

		$this->load->library('lib_clans');

		$this->types = array(
			Bouzouk::Clans_TypeSyndicat,
			Bouzouk::Clans_TypePartiPolitique,
			Bouzouk::Clans_TypeOrganisation,
		);

		// On renvoit du JSON
		$this->output->set_content_type('application/json');
		$this->load->library('lib_plouk');
	}
	
	public function ajouter_allie_action()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('action_id', "L'action", 'required|is_natural_no_zero');
		$this->form_validation->set_rules('clan_type', 'Le type de clan', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('clan_id', 'Le clan', 'is_natural_no_zero');
		$this->form_validation->set_rules('clan_nom', 'Le clan', 'min_length[3]|max_length[35]');

		if ( ! $this->form_validation->run())
			return $this->output->set_output(json_encode(''));

		// Le type doit être valide
		$type = $this->input->post('clan_type');
		if ( ! in_array($type, $this->types) || ! $this->session->userdata('clan_id')[$type])
			show_404();

		$reponse = array();

		// On récupère le clan
		$clan = $this->lib_clans->get_clan($this->session->userdata('clan_id')[$type]);
		
		// Le joueur doit avoir le droit de gérer les actions
		if( ! $this->session->userdata('clan_grade')[$clan->type] >= $clan->grade_lancer_actions)
			show_404();

		// On récupère l'action
		$query = $this->db->select('id, nom, effet, cout, clan_type, nb_membres_min, nb_allies_min, nb_membres_allies_min, cout_par_allie')
						  ->from('clans_actions')
						  ->where('id', $this->input->post('action_id'))
						  ->get();
		
		// Si l'action n'existe pas
		if ($query->num_rows() == 0)
		{
			$reponse['alert'] = "Cette action n'existe pas";
			return $this->output->set_output(json_encode($reponse));
		}

		$action = $query->row();

		// On regarde si une enchère est en cours
		$query = $this->db->select('montant_enchere, date, clan_id, annulee')
						  ->from('clans_encheres')
						  ->where('clan_type', $type)
						  ->order_by('id', 'desc')
						  ->limit(1)
						  ->get();
			
		$enchere = ($query->num_rows() == 1) ? $query->row() : null;

		$nb_membres_clan = $this->db->where('clan_id', $clan->id)
									->count_all_results('politiciens');

		if (($erreur = $this->lib_clans->action_possible($action, $clan, $nb_membres_clan, $enchere, false)) !== true)
		{
			$reponse['alert'] = $erreur;
			return $this->output->set_output(json_encode($reponse));
		}

		// Clan_id donné
		if (ctype_digit($this->input->post('clan_id')) && $this->input->post('clan_nom') == '')
		{
			// On regarde si le clan existe
			$existe = $this->db->where('id', $this->input->post('clan_id'))
							   ->where('id !=', $clan->id)
							   ->where_in('mode_recrutement', array(Bouzouk::Clans_RecrutementOuvert, Bouzouk::Clans_RecrutementFerme))
							   ->where('type', $type)
							   ->count_all_results('clans');

			if ( ! $existe)
			{
				$reponse['alert'] = "Ce clan n'existe pas";
				return $this->output->set_output(json_encode($reponse));
			}

			// On regarde si le joueur n'a pas déjà envoyé une proposition à ce clan
			$existe = $this->db->where('action_id', $action->id)
							   ->where('clan_createur_id', $clan->id)
							   ->where('clan_invite_id', $this->input->post('clan_id'))
							   ->count_all_results('clans_actions_allies');

			if ($existe)
			{
				$reponse['alert'] = 'Tu as déjà invité ce clan';
				return $this->output->set_output(json_encode($reponse));
			}

			if ($this->lib_clans->sabotage_en_cours($this->input->post('clan_id')) !== null)
			{
				$reponse['alert'] = "Ce clan est en train de se faire saboter, il ne peut pas t'aider";
				return $this->output->set_output(json_encode($reponse));
			}

			if ( ! $this->lib_clans->verifier_allie_valide($action, $this->lib_clans->get_clan($this->input->post('clan_id'))))
			{
				$reponse['alert'] = "Ce clan ne remplit pas les conditions nécessaires de points d'action ou de nombre de membres actifs minimum";
				return $this->output->set_output(json_encode($reponse));
			}

			// On invite le clan
			$data_clans_actions_allies = array(
				'action_id'        => $this->input->post('action_id'),
				'clan_createur_id' => $clan->id,
				'clan_invite_id'   => $this->input->post('clan_id'),
				'statut'           => Bouzouk::Clans_AllianceAttente,
				'date'             => bdd_datetime()
			);
			$this->db->insert('clans_actions_allies', $data_clans_actions_allies);

			// On affiche une confirmation
			$reponse['alert'] = 'Le clan a bien été ajouté';
		}

		// Clan_nom donné
		else if ( ! ctype_digit($this->input->post('clan_id')) && $this->input->post('clan_nom') != '')
		{
			// On regarde si le clan existe
			$query = $this->db->select('id')
							  ->from('clans')
							  ->where('nom', $this->input->post('clan_nom'))
							  ->where('id !=', $clan->id)
							  ->where('mode_recrutement', Bouzouk::Clans_RecrutementInvisible)
							  ->where('type', $type)
							  ->get();

			if ($query->num_rows() == 1)
			{
				$clan_invite = $query->row();

				// On regarde si le joueur n'a pas déjà envoyé une proposition à ce clan
				$existe = $this->db->where('action_id', $action->id)
								   ->where('clan_createur_id', $clan->id)
								   ->where('clan_invite_id', $clan_invite->id)
								   ->count_all_results('clans_actions_allies');

				if ( ! $existe && $this->lib_clans->verifier_allie_valide($action, $clan))
				{
					// On invite le clan
					$data_clans_actions_allies = array(
						'action_id'        => $this->input->post('action_id'),
						'clan_createur_id' => $clan->id,
						'clan_invite_id'   => $clan_invite->id,
						'statut'           => Bouzouk::Clans_AllianceAttente,
						'date'             => bdd_datetime()
					);
					$this->db->insert('clans_actions_allies', $data_clans_actions_allies);
				}
			}

			$reponse['alert'] = "Le clan a bien été ajouté. Ce message s'affiche même si le clan n'existe pas, de plus tu ne pourras pas savoir quel clan caché a accepté ou non";
		}

		// Aucun des deux ou les deux à la fois
		else
			$reponse['alert'] = "Il faut choisir un clan : soit un dans la liste soit écrire le nom d'un clan caché";

		// On affiche la réponse
		return $this->output->set_output(json_encode($reponse));
	}
}
