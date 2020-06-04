<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions de gestion de la mairie
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : décembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Lib_mairie
{
	private $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->library('lib_missive');
	}

	public function historique($message, $maire_id = null)
	{
		if ( ! isset($maire_id))
		{
			$query = $this->CI->db->select('maire_id')
								  ->from('mairie')
								  ->get();
			$mairie = $query->row();
			$maire_id = $mairie->maire_id;
		}

		$visible_journalistes = 1;

		// ---------- Hook clans ----------
		// Magouille (parti Politique)
		$query = $this->CI->db->select('nb_restants')
							  ->from('clans_actions_lancees')
							  ->where('action_id', 10)
							  ->where('statut', Bouzouk::Clans_ActionEnCours)
							  ->get();

		if ($query->num_rows() > 0)
		{
			$magouille = $query->row();
			$visible_journalistes = 0;

			// On descend de 1 le nombre de coups restants
			$this->CI->db->set('nb_restants', 'nb_restants-1', false)
						 ->where('action_id', 10)
						 ->where('statut', Bouzouk::Clans_ActionEnCours)
						 ->update('clans_actions_lancees');

			// So tout a été utilisé, on clos l'action
			if ($magouille->nb_restants == 1)
			{
				$this->CI->db->set('statut', Bouzouk::Clans_ActionTerminee)
							 ->set('jours_restants', 0)
							 ->where('action_id', 10)
							 ->where('statut', Bouzouk::Clans_ActionEnCours)
							 ->update('clans_actions_lancees');
			}
		}

		$data_historique_mairie = array(
			'date'                 => bdd_datetime(),
			'maire_id'             => $maire_id,
			'texte'                => $message,
			'visible_journalistes' => $visible_journalistes
		);
		$this->CI->db->insert('historique_mairie', $data_historique_mairie);
	}

	public function don_possible($montant)
	{
		// On va chercher le total des dons
		$query = $this->CI->db->select('SUM(montant) AS total_dons')
							  ->from('donations')
							  ->where_in('type', array(Bouzouk::Donation_MairieBouzouk, Bouzouk::Donation_MairieBouzouks, Bouzouk::Donation_MairieMendiants, Bouzouk::Donation_MairieTous))
							  ->where('date >= (NOW() - INTERVAL '.$this->CI->bouzouk->config('mairie_intervalle_max_dons').' HOUR)')
							  ->where('donateur_id', $this->CI->session->userdata('id'))
							  ->get();
		$mairie = $query->row();

		// On regarde si le don ne dépasse pas la limite
		return array(
			'total_dons'      => $mairie->total_dons,
			'max_dons'        => $this->CI->bouzouk->config('mairie_max_dons'),
			'limite_atteinte' => $mairie->total_dons + $montant > $this->CI->bouzouk->config('mairie_max_dons'),
			'intervalle'      => $this->CI->bouzouk->config('mairie_intervalle_max_dons')
		);
	}

	public function fonds_suffisants($montant)
	{
		// On vérifie que la mairie a assez de fonds
		$query = $this->CI->db->select('struls')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();

		return $mairie->struls - $montant >= $this->CI->bouzouk->config('mairie_decouvert_autorise');
	}

	public function retirer_struls($struls)
	{
		// On va chercher le nombre de struls de la mairie
		$query = $this->CI->db->select('struls')
							  ->from('mairie')
							  ->get();
		$mairie = $query->row();

		// Si la mairie a assez pour retirer le montant
		if ($mairie->struls - $struls >= $this->CI->bouzouk->config('mairie_decouvert_autorise'))
		{
			$this->CI->db->set('struls', 'struls-'.$struls, false)
						 ->update('mairie');
			return true;
		}

		return false;
	}

	public function ajouter_struls($montant){
		$this->CI->db->set('struls', 'struls+'.$montant, false)
					->update('mairie');
	}

	public function verifier_maire_et_suppleant()
	{
		// On récupère l'id du maire et du suppléant
		$query = $this->CI->db->select('m.maire_id, j1.pseudo AS maire_pseudo, j1.statut AS maire_statut, m.maire_suppleant_id, j2.pseudo AS maire_suppleant_pseudo, j2.statut AS maire_suppleant_statut')
							  ->from('mairie m')
							  ->join('joueurs j1', 'j1.id = m.maire_id')
							  ->join('joueurs j2', 'j2.id = m.maire_suppleant_id')
							  ->get();
		$mairie = $query->row();

		// Si le maire est un robot, pas besoin de vérifier quoi que ce soit
		if (in_array($mairie->maire_id, $this->CI->bouzouk->get_robots()))
		{
			return;
		}

		// On récupère le statut du maire et du suppléant
		$maire_actif = $this->CI->db->where('id', $mairie->maire_id)
									->where('statut', Bouzouk::Joueur_Actif)
									->count_all_results('joueurs');

		$suppleant_actif = $this->CI->db->where('id', $mairie->maire_suppleant_id)
										->where('statut', Bouzouk::Joueur_Actif)
										->count_all_results('joueurs');

		// Si le maire n'est plus actif mais le suppléant l'est toujours
		if ( ! $maire_actif && $suppleant_actif)
		{
			// Le suppléant devient le maire et J.F Sébastien devient le suppléant
			$data_mairie = array(
				'maire_id'           => $mairie->maire_suppleant_id,
				'maire_suppleant_id' => Bouzouk::Robot_JF_Sebastien
			);
			$this->CI->db->update('mairie', $data_mairie);

			// On insère le nouveau maire dans l'historique
			$data_historique_maires = array(
				'maire_id'   => $mairie->maire_suppleant_id,
				'date_debut' => bdd_datetime()
			);
			$this->CI->db->insert('historique_maires', $data_historique_maires);

			// Si le maire est juste à l'asile ou en pause, on peut envoyer une missive (s'il est game over il ne faut pas)
			if (in_array($mairie->maire_statut, array(Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause)))
			{
				// On envoit une missive au maire
				$message  = "	Bonjour $mairie->maire_pseudo\n\n";
				$message .= "Au vu des récents évènements, il a été décrété que tu n'es plus en mesure d'assurer tes fonctions au pouvoir de la mairie.\n\n";
				$message .= "Tu es donc déchu de ton poste et ton suppléant ".profil($mairie->maire_suppleant_id, $mairie->maire_suppleant_pseudo)." te remplace jusqu'à la fin de ton mandat.\n";
				$message .= "Nous sommes bien navrés de cette situation.\n\n";
				$message .= "Bonne continuation :)\n\n";
				$message .= "	Amicalement, la mairie de Vlurxtrznbnaxl.";

				$this->CI->lib_missive->envoyer_missive(Bouzouk::Robot_Maire, $mairie->maire_id, 'Mairie', $message);
			}

			// On prépare une missive pour le suppléant
			$message  = "	Bonjour $mairie->maire_suppleant_pseudo\n\n";
			$message .= "Le maire ".profil($mairie->maire_id, $mairie->maire_pseudo)." n'est plus en mesure d'assurer ses fonctions au pouvoir.\n\n";
			$message .= "En tant que maire suppléant, tu prends donc sa place de plein droit et tu continues son mandat jusqu'à la fin.\n";
			$message .= "Tu peux désormais prendre tes fonctions dans <a href='".site_url('mairie/gerer')."' title='Gérer la mairie'>ton nouveau bureau</a>.\n\n";
			$message .= "Bon courage !\n\n";
			$message .= "	Amicalement, la mairie de Vlurxtrznbnaxl.";

			$this->CI->lib_missive->envoyer_missive(Bouzouk::Robot_Maire, $mairie->maire_suppleant_id, 'Mairie', $message);

			// On demande la mise à jour des sessions
			$this->CI->bouzouk->augmente_version_session($mairie->maire_id);
			$this->CI->bouzouk->augmente_version_session($mairie->maire_suppleant_id);
		}

		// Si le maire n'est plus actif et le suppléant non plus
		else if ( ! $maire_actif && ! $suppleant_actif)
		{
			// J.F Sébastien devient le maire et il est aussi suppléant
			$data_mairie = array(
				'maire_id'           => Bouzouk::Robot_JF_Sebastien,
				'maire_suppleant_id' => Bouzouk::Robot_JF_Sebastien
			);
			$this->CI->db->update('mairie', $data_mairie);

			// On insère le nouveau maire dans l'historique
			$data_historique_maires = array(
				'maire_id'   => Bouzouk::Robot_JF_Sebastien,
				'date_debut' => bdd_datetime()
			);
			$this->CI->db->insert('historique_maires', $data_historique_maires);

			// Si le maire est juste à l'asile ou en pause, on peut envoyer une missive (s'il est game over il ne faut pas)
			if (in_array($mairie->maire_statut, array(Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause)))
			{
				// On envoit une missive au maire
				$message  = "	Bonjour $mairie->maire_pseudo\n\n";
				$message .= "Au vu des récents évènements, il a été décrété que tu n'es plus en mesure d'assurer tes fonctions au pouvoir de la mairie.\n\n";
				$message .= "Tu es donc déchu de ton poste et puisque ton suppléant n'est pas non plus en mesure d'assurer le pouvoir, c'est ".profil(Bouzouk::Robot_JF_Sebastien, 'J.F Sébastien')." qui te remplace jusqu'à la fin de ton mandat.\n";
				$message .= "Nous sommes bien navrés de cette situation.\n\n";
				$message .= "Bonne continuation :)\n\n";
				$message .= "	Amicalement, la mairie de Vlurxtrznbnaxl.";

				$this->CI->lib_missive->envoyer_missive(Bouzouk::Robot_Maire, $mairie->maire_id, 'Mairie', $message);
			}

			// Si le suppléant du maire est juste à l'asile ou en pause, on peut envoyer une missive (s'il est game over il ne faut pas)
			if (in_array($mairie->maire_suppleant_statut, array(Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause)))
			{
				$message  = "	Bonjour $mairie->maire_suppleant_pseudo\n\n";
				$message .= "Le maire ".profil($mairie->maire_id, $mairie->maire_pseudo)." n'est plus en mesure d'assurer ses fonctions au pouvoir.\n\n";
				$message .= "En tant que maire suppléant, tu es sensé prendre sa place mais tu n'es pas non plus en mesure d'assurer le pouvoir à la mairie.\n";
				$message .= "Tu es donc supprimé du poste de suppléant à la mairie jusqu'à la fin du mandat en cours.\n\n";
				$message .= "Bonne continuation :)\n\n";
				$message .= "	Amicalement, la mairie de Vlurxtrznbnaxl.";

				$this->CI->lib_missive->envoyer_missive(Bouzouk::Robot_Maire, $mairie->maire_suppleant_id, 'Mairie', $message);
			}

			// On demande la mise à jour des sessions
			$this->CI->bouzouk->augmente_version_session($mairie->maire_id);
			$this->CI->bouzouk->augmente_version_session($mairie->maire_suppleant_id);
		}

		// Si le maire est actif mais le suppléant inactif
		else if ($maire_actif && ! $suppleant_actif)
		{
			// J.F Sébastien devient le maire suppléant
			$data_mairie = array(
				'maire_suppleant_id' => Bouzouk::Robot_JF_Sebastien
			);
			$this->CI->db->update('mairie', $data_mairie);

			// Si le suppléant est juste à l'asile ou en pause, on peut envoyer une missive (s'il est game over il ne faut pas)
			if (in_array($mairie->maire_suppleant_statut, array(Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause)))
			{
				// On envoit une missive au suppléant
				$message  = "	Bonjour $mairie->maire_pseudo\n\n";
				$message .= "Au vu des récents évènements, il a été décrété que tu n'es plus en mesure d'assurer tes fonctions en tant que suppléant au maire.\n\n";
				$message .= "Tu es donc déchu de ton poste et c'est ".profil(Bouzouk::Robot_JF_Sebastien, 'J.F Sébastien')." qui te remplace jusqu'à la fin du mandat du maire.\n";
				$message .= "Nous sommes bien navrés de cette situation.\n\n";
				$message .= "Bonne continuation :)\n\n";
				$message .= "	Amicalement, la mairie de Vlurxtrznbnaxl.";

				$this->CI->lib_missive->envoyer_missive(Bouzouk::Robot_Maire, $mairie->maire_suppleant_id, 'Mairie', $message);
			}
		}
	}
}

