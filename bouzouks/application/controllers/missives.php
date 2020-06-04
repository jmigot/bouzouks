<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : gestion de la messagerie interne du jeu
 *
 * Auteur      : Jean-Luc Migot (jl.migot@yahoo.fr)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord préalable de tous les auteurs ainsi
 * que l'application des conditions données lors de cet accord.
 */

class Missives extends MY_Controller
{
	private $messages_etat = array(
		'faim'   => "Tu as tellement faim que tu as mangé tout le stock de feuilles...Va chercher à manger dans ta maison !",
		'sante'  => "Ca va pas fort : tu n'as même pas la force de tenir un stylo...C'est pas la peine de songer à écrire une lettre !",
		'stress' => "Tu es tellement stressé que tu n'arrives pas à écrire une ligne sans déchirer la feuille...Calme-toi donc !"
	);

	public function __construct()
	{
		parent::__construct();
		$this->load->library('lib_missive');
		$this->load->library('lib_parser');
	}

	public function index()
	{
		$this->recues();
	}

	public function recues($offset = '0')
	{
		// Pagination
		$nb_missives_recues = $this->bouzouk->get_nb_missives($this->session->userdata('id'), true, false);
		$pagination = creer_pagination('missives/recues', $nb_missives_recues, 20, $offset);

		// On va chercher toutes les lettres
		$query = $this->db->select('m.id AS id, m.expediteur_id, j.pseudo AS expediteur, j.rang AS expediteur_rang, m.objet, m.date_envoi, m.lue')
						  ->from('missives m')
						  ->join('joueurs j', 'j.id = m.expediteur_id')
						  ->where('m.destinataire_id', $this->session->userdata('id'))
						  ->where('m.destinataire_supprime', 0)
						  ->order_by('m.date_envoi', 'desc')
						  ->limit($pagination['par_page'], $pagination['offset'])
						  ->get();

		// Affichage
		$vars = array
		(
			'missives'    => $query->result(),
			'nb_missives' => $this->bouzouk->get_nb_missives($this->session->userdata('id')),
			'pagination'  => $pagination['liens']
		);
		return $this->layout->view('missives/recues', $vars);
	}

	public function envoyees($offset = '0')
	{
		// Pagination
		$nb_missives_envoyees = $this->bouzouk->get_nb_missives($this->session->userdata('id'), false, true);
		$pagination = creer_pagination('missives/envoyees', $nb_missives_envoyees, 20, $offset);

		$query = $this->db->select('m.id AS id, m.destinataire_id, j.pseudo AS destinataire, j.rang AS destinataire_rang, m.objet, m.date_envoi, m.destinataire_supprime, m.lue')
						  ->from('missives m')
						  ->join('joueurs j', 'j.id = m.destinataire_id')
						  ->where('m.expediteur_id', $this->session->userdata('id'))
						  ->where('m.expediteur_supprime', 0)
						  ->order_by('m.date_envoi', 'desc')
						  ->limit($pagination['par_page'], $pagination['offset'])
						  ->get();

		// Affichage
		$vars = array(
			'missives'   => $query->result(),
			'nb_missives' => $this->bouzouk->get_nb_missives($this->session->userdata('id')),
			'pagination' => $pagination['liens']
		);
		return $this->layout->view('missives/envoyees', $vars);
	}

	public function lire_recue($id)
	{
		if ( ! entier_naturel_positif($id))
		{
			show_404();
		}

		// On va chercher la lettre en base
		$query = $this->db->select('m.expediteur_id, j.pseudo AS expediteur_nom, j.rang AS expediteur_rang, j.adresse AS expediteur_adresse, m.id, m.date_envoi, m.timbre, m.objet, m.message, m.destinataire_id, m.lue')
						  ->from('missives m')
						  ->join('joueurs j', 'j.id = m.expediteur_id')
						  ->where('m.id', $id)
						  ->where('destinataire_supprime', 0)
						  ->get();

		// Si la lettre n'existe pas
		if ($query->num_rows() == 0)
		{
			$this->echec("Cette missive n'existe pas");
			return $this->index();
		}

		$missive = $query->row();

		// On vérifie que le joueur a le droit de lire cette lettre
		if ($missive->destinataire_id != $this->session->userdata('id'))
		{
			$this->echec("Tu n'as pas le droit de lire cette missive");
			return $this->index();
		}

		// On marque la lettre comme lue
		if ($missive->lue == 0)
		{
			$data_missives = array(
				'lue' => 1
			);
			$this->db->where('id', $id)
					->update('missives', $data_missives);
		}

		// On fait des remplacements dans le message
		$expediteur_robot = true;
		if ( ! in_array($missive->expediteur_id, array_merge($this->bouzouk->get_robots(), $this->bouzouk->get_inactifs())))
		{
			$expediteur_robot = false;
		}

		// On affiche la lettre
		$vars = array(
			'missive'          => $missive,
			'expediteur_robot' => $expediteur_robot
		);
		return $this->layout->view('missives/lire_recue', $vars);
	}

	public function lire_envoyee($id)
	{
		if ( ! entier_naturel_positif($id))
		{
			show_404();
		}

		// On va chercher la lettre en base
		$query = $this->db->select('m.destinataire_id, j.pseudo AS destinataire_nom, j.rang AS destinataire_rang, j.adresse AS destinataire_adresse, m.id, m.date_envoi, m.timbre, m.objet, m.message, m.expediteur_id')
						  ->from('missives m')
						  ->join('joueurs j', 'j.id = m.destinataire_id')
						  ->where('m.id', $id)
						  ->where('m.expediteur_supprime', 0)
						  ->get();

		// Si la lettre n'existe pas
		if ($query->num_rows() == 0)
		{
			$this->echec("Cette missive n'existe pas");
			return $this->index();
		}

		$missive = $query->row();

		// On vérifie que le joueur a le droit de lire cette lettre
		if ($missive->expediteur_id != $this->session->userdata('id'))
		{
			$this->echec("Tu n'as pas le droit de lire cette missive");
			return $this->index();
		}

		// On affiche la lettre
		$vars = array(
			'missive' => $missive
		);
		return $this->layout->view('missives/lire_envoyee', $vars);
	}

	public function _intro_check($intro)
	{
		if ( ! array_key_exists($intro, $this->lib_missive->intros()))
		{
			$this->form_validation->set_message('_intro_check', "%s n'existe pas");
			return false;
		}

		return true;
	}

	public function _politesse_check($politesse)
	{
		if ( ! array_key_exists($politesse, $this->lib_missive->politesses()))
		{
			$this->form_validation->set_message('_politesse_check', "%s n'existe pas");
			return false;
		}

		return true;
	}

	public function repondre($destinataire_id = '0')
	{
		// On récupère l'objet et on supprime POST pour éviter une validation des données
		$objet = $this->input->post('objet');
		$message_original = $this->input->post('message_original');
		$this->session->set_userdata('missive_objet', $objet);
		$this->session->set_userdata('missive_message_original', $message_original);

		redirect('missives/ecrire/'.$destinataire_id);
	}

	public function ecrire($destinataire_id = '0')
	{
		// On vérifie que l'id de destinataire est valide
		if ( ! entier_naturel($destinataire_id))
		{
			$destinataire_id = '0';
			$this->session->set_userdata('flash_echec', "Ce destinataire n'existe pas");
		}

		// Si on veut écrire à un destinataire particulier
		if ($destinataire_id != '0')
		{
			$destinataire_existe = $this->db->where('id', $destinataire_id)
											->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Pause, Bouzouk::Joueur_Asile))
											->count_all_results('joueurs');

			if ($destinataire_existe == 0)
			{
				$destinataire_id = '0';
				$this->echec("Ce destinataire n'existe pas");
			}
		}

		else
		{
			$destinataire = set_value('destinataire_id');
		}

		// On regarde si l'expediteur n'a pas dépassé sa limite de missives (modos/admins illimités)
		if ( ! $this->bouzouk->is_moderateur() && $this->bouzouk->get_nb_missives($this->session->userdata('id')) >= $this->bouzouk->config('missives_limite'))
		{
			$this->echec('Tu as trop de missives envoyées/reçues ('.$this->bouzouk->config('missives_limite').' maximum), fais le ménage pour pouvoir en écrire une nouvelle');
			return $this->index();
		}

		// Si le joueur est trop faible, il ne peut plus écrire (sauf si il est modérateur ou sauf si le destinataire est un modérateur)
		$message = '';
		if ($this->session->userdata('faim') < $this->bouzouk->config('missives_faim_ecrire'))
		{
			$message = $this->messages_etat['faim'];
		}

		else if ($this->session->userdata('sante') < $this->bouzouk->config('missives_sante_ecrire'))
		{
			$message = $this->messages_etat['sante'];
		}

		else if ($this->session->userdata('stress') > $this->bouzouk->config('missives_stress_ecrire'))
		{
			$message = $this->messages_etat['stress'];
		}

		// On regarde si le destinataire est >= modérateur
		if ( ! entier_naturel_positif($destinataire_id) && $this->input->isPost())
		{
			$destinataire_id = (int) $this->input->post('destinataire');
		}

		$destinataire_modo_admin = $this->db->where('id', $destinataire_id)
											->where('rang & '.$this->bouzouk->get_masque(Bouzouk::Masque_Moderateur | Bouzouk::Masque_Admin).' > 0')
											->count_all_results('joueurs');
											
		if ($message != '' && ! $destinataire_modo_admin && ! $this->bouzouk->is_moderateur())
		{
			$vars = array(
				'titre_layout' => 'Missives - Ecrire',
				'titre'        => 'Tu es trop faible',
				'image_url'    => 'trop_faible.png',
				'message'      => $message."<br><br><a href='".site_url('site/team')."'>Mais je dois écrire à un administrateur, c'est urgent !</a>"
			);
			return $this->layout->view('blocage', $vars);
		}

		// Si le joueur était trop faible ou interdit de missive, on ne met que les destinataires >= modérateur
		$rangs_in = ! $this->bouzouk->is_moderateur() && ($message != '' || $this->session->userdata('interdit_missives') == 1) ? $this->bouzouk->get_masque(Bouzouk::Masque_Moderateur | Bouzouk::Masque_Admin) : null;

		// On prépare la liste des destinataires possibles
		$select_destinataires = $this->bouzouk->select_joueurs(array(
			'rangs_in'      => $rangs_in,
			'name'          => 'destinataire',
			'joueur_id'     => $this->input->post('destinataire') !== false ? $this->input->post('destinataire') : ($destinataire_id == '0' ? null : $destinataire_id),
			'status_not_in' => array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_ChoixPerso, Bouzouk::Joueur_GameOver, Bouzouk::Joueur_Robot, Bouzouk::Joueur_Banni))
		);

		// On parse le sujet et le message original
		$objet = '';
		$message_original = '';

		if ($this->session->userdata('missive_objet') != false)
		{
			$objet = $this->session->userdata('missive_objet');
			$this->session->unset_userdata('missive_objet');
		}

		if ($this->session->userdata('missive_message_original') != false)
		{
			$message_original = $this->session->userdata('missive_message_original');
			$this->session->unset_userdata('missive_message_original');
		}

		else if ($this->input->post('missive_originale') !== false)
		{
			$message_original = $this->input->post('missive_originale');
		}

		if ($objet != '' AND mb_substr($objet, 0, 5) != 'Re : ')
		{
			$objet = 'Re : '.$objet;
		}

		// On a besoin de préparer les variables de vue à l'avance
		$vars = array(
			'select_destinataires' => $select_destinataires,
			'destinataire_id'      => $destinataire_id,
			'objet'                => $objet,
			'timbres'              => $this->lib_missive->timbres(),
			'intros'               => $this->lib_missive->intros(),
			'politesses'           => $this->lib_missive->politesses(),
			'adresse'              => $this->session->userdata('adresse'),
			'table_smileys'        => creer_table_smileys('message'),
			'message_original'     => $message_original
		);

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('destinataire', 'Le destinataire', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('timbre', 'Le timbre', 'required');
		$this->form_validation->set_rules('intro', "L'introduction", 'required|callback__intro_check');
		$this->form_validation->set_rules('objet', "L'objet", 'required|min_length[3]|max_length[60]');
		$this->form_validation->set_rules('message', 'Le message', 'required|min_length[1]|max_length[5000]');
		$this->form_validation->set_rules('politesse', 'La formule de politesse', 'required|callback__politesse_check');

		if ( ! $this->form_validation->run())
		{
			return $this->layout->view('missives/ecrire', $vars);
		}

		// Le joueur ne peux pas s'écrire à lui-même
		if ($this->input->post('destinataire') == $this->session->userdata('id'))
		{
			$this->echec("Tu veux t'écrire à toi-même ?! Je crois que tu as bu trop de bierrouïoli...");
			return $this->layout->view('missives/ecrire', $vars);
		}

		// Le timbre doit exister
		if ( ! array_key_exists($this->input->post('timbre'), $this->lib_missive->timbres()))
		{
			$this->echec("Ce timbre n'existe pas");
			return $this->layout->view('missives/ecrire', $vars);
		}

		// Le joueur doit avoir asser de struls pour payer ce timbre, sauf si le destinataire est >= modérateur
		$timbre = $this->lib_missive->timbres($this->input->post('timbre'));
		$prix_timbre = $timbre['prix'];
		
		if ( ! $destinataire_modo_admin AND $this->session->userdata('struls') < $prix_timbre)
		{
			$this->echec("Le timbre est trop cher pour toi, tu n'as pas assez de struls pour payer l'envoi");
			return $this->layout->view('missives/ecrire', $vars);
		}

		// On va chercher le destinataire
		$query = $this->db->select('id, rang')
						  ->from('joueurs')
						  ->where('id', $this->input->post('destinataire'))
						  ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Pause, Bouzouk::Joueur_Asile))
						  ->get();

		// Le destinataire doit exister
		if ($query->num_rows() == 0)
		{
			$this->echec("Ce bouzouk n'existe pas, regarde dans la liste déroulante");
			return $this->layout->view('missives/ecrire', $vars);
		}

		$joueur = $query->row();
		
		// On regarde si le destinataire n'a pas dépassé sa limite de bouzolettres (modos/admin illimités)
		if ( ! $this->bouzouk->is_moderateur() && ! $this->bouzouk->is_moderateur(null, $joueur->rang) && $this->bouzouk->get_nb_missives($joueur->id) >= $this->bouzouk->config('missives_limite'))
		{
			$this->echec("Ce bouzouk a trop de bouzolettres envoyées/reçues, tu dois attendre qu'il fasse le ménage pour pouvoir lui écrire");
			return $this->layout->view('missives/ecrire', $vars);
		}

		// Anti message double
		if ($joueur->id == $this->session->userdata('dernier_destinataire_missive') && $this->input->post('message') == $this->session->userdata('derniere_missive'))
		{
			$this->attention('La missive a déjà été envoyée une fois ;)');
			return $this->index();
		}
			
		$this->session->set_userdata('dernier_destinataire_missive', $joueur->id);
		$this->session->set_userdata('derniere_missive', $this->input->post('message'));
		
		// On enregistre la lettre
		$data_missives = array(
			'expediteur_id'         => $this->session->userdata('id'),
			'destinataire_id'       => $joueur->id,
			'date_envoi'            => bdd_datetime(),
			'timbre'                => $this->input->post('timbre'),
			'objet'                 => $this->input->post('objet'),
			'message'               => "\t".$this->lib_missive->intros($this->input->post('intro')).",\n\n".$this->input->post('message')."\n\n\t".$this->lib_missive->politesses($this->input->post('politesse')),
			'lue'                   => 0,
			'expediteur_supprime'   => 0,
			'destinataire_supprime' => 0
		);
		$this->db->insert('missives', $data_missives);
		$missive_id = $this->db->insert_id();

		if ($missive_id == 0)
		{
			$this->echec("Erreur lors de l'envoi de la missive, merci de prévenir un administrateur avec le contenu de la missive si le problème persiste :)");
			return $this->layout->view('missives/ecrire', $vars);
		}

		$this->load->library('lib_notifications');
		// On envoit une notif au destinataire
		if ($this->lib_notifications->notifier(Bouzouk::Notification_MissiveJoueur, $joueur->id))
			$this->bouzouk->notification(125, array(profil(-1, '', $this->session->userdata('rang')), site_url('missives/lire_recue/'.$missive_id)), $joueur->id);
		
		// On fait payer le timbre au joueur
		if ( ! $destinataire_modo_admin)
			$this->bouzouk->retirer_struls($prix_timbre);

		// On affiche un message de confirmation
		$this->succes('Missive envoyée ;)');
		return $this->index();
	}

	public function supprimer_recues()
	{
		// On essaye de supprimer toutes les lettres correspondant aux identifiants donnés
		$this->db->set('destinataire_supprime', 1)
				 ->where_in('id', $this->input->post('ids'))
				 ->where('destinataire_id', $this->session->userdata('id'))
				 ->where('destinataire_supprime', 0)
				 ->update('missives');

		// Si aucune lettre n'a été effacée
		if ($this->db->affected_rows() == 0)
			$this->echec("Aucune missive n'a été sélectionnée");

		else if ($this->db->affected_rows() == 1)
			$this->succes('La missive a bien été supprimée');

		else
			$this->succes('Les missives ont bien été supprimées');

		return $this->recues();
	}

	public function supprimer_envoyees()
	{
		// On essaye de supprimer toutes les lettres correspondant aux identifiants donnés
		$this->db->set('expediteur_supprime', 1)
				 ->where_in('id', $this->input->post('ids'))
				 ->where('expediteur_id', $this->session->userdata('id'))
				 ->where('expediteur_supprime', 0)
				 ->update('missives');

		// Si aucune lettre n'a été effacée
		if ($this->db->affected_rows() == 0)
			$this->echec("Aucune missive n'a été sélectionnée");

		else if ($this->db->affected_rows() == 1)
			$this->succes('La missive a bien été supprimée');

		else
			$this->succes('Les missives ont bien été supprimées');

		return $this->envoyees();
	}
}
