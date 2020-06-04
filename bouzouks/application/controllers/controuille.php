<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : devoirs à faire lorsque le joueur est étudiant
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Controuille extends MY_Controller
{
	public function index()
	{
		if ((string) $this->session->userdata('notes_controuilles') == '')
			$this->controuille1_debut();

		else
			$this->controuille2_debut();
	}

	public function controuille1_debut()
	{
		// On vérifie que le joueur n'a pas déjà fait ce controuille
		if ((string) $this->session->userdata('notes_controuilles') != '')
			return $this->controuille2_debut();

		// On affiche
		return $this->layout->view('controuille/controuille1_debut');
	}

	public function controuille1()
	{
		// On vérifie que le joueur n'a pas déjà fait ce controuille
		if ((string) $this->session->userdata('notes_controuilles') != '')
			return $this->controuille2_debut();

		if ( ! $this->input->isPost())
			return $this->layout->view('controuille/controuille1');

		// Réponses au contrôuille
		// Format du tableau : 'id question' => array(réponse, nombre de points)
		$reponses = array(
			'question1a' => array('reponse' => 1, 'points' => 2),
			'question1b' => array('reponse' => 2, 'points' => 2),
			'question2'  => array('reponse' => 2, 'points' => 4),
			'question3'  => array('reponse' => 3, 'points' => 4),
			'question4'  => array('reponse' => 3, 'points' => 4),
			'question5'  => array('reponse' => 2, 'points' => 4),
		);

		// Calcul de la note
		$note = 0;
		foreach ($reponses as $question_id => $question_array)
		{
			if ($this->input->post($question_id) !== false AND $this->input->post($question_id) == $question_array['reponse'])
			{
				$note += $question_array['points'];
			}
		}

		// Si le joueur a 0/20, il perd de l'experience
		if ($note == 0)
		{
			$perte_xp = $this->bouzouk->config('controuille_perte_xp');
			$this->bouzouk->retirer_experience($perte_xp);
			$this->echec("Puisque tu as eu 0/20, tu perds <span class='pourpre'>-$perte_xp xp</span> :(");
		}

		// Si le joueur a la moyenne, il gagne des struls
		if ($note >= 10)
		{
			$gain_xp = $this->bouzouk->config('controuille_gain_xp');
			$gain_struls = $this->bouzouk->config('controuille_gain_struls');

			$this->bouzouk->ajouter_struls($gain_struls);

			// Si il a 20/20, il gagne de l'expérience
			if ($note == 20)
			{
				$this->bouzouk->ajouter_experience($gain_xp);
				$this->succes("Puisque tu as eu 20/20, tu gagnes <span class='pourpre'>+".struls($gain_struls)."</span> et <span class='pourpre'>+$gain_xp xp</span> :)");
			}

			else
			{
				$this->succes("Tu as la moyenne, tu gagnes <span class='pourpre'>+".struls($gain_struls)."</span> :)");
			}
		}

		// Le joueur passe à l'état Controuille2
		$data_joueur = array(
			'notes_controuilles' => $note
		);
		$this->db->where('id', $this->session->userdata('id'))
				 ->update('joueurs', $data_joueur);
		$this->session->set_userdata($data_joueur);

		// On affiche le résultat
		$vars = array(
			'note' => $note
		);
		return $this->layout->view('controuille/controuille1_fin', $vars);
	}

	public function controuille2_debut()
	{
		// On vérifie que le joueur n'a pas déjà fait ce controuille
		if (strpos($this->session->userdata('notes_controuilles'), '_') !== false)
			redirect('joueur/choix_perso');

		// On affiche
		$vars = array(
			'note_controuille1' => (string) $this->session->userdata('notes_controuilles')
		);
		$this->layout->view('controuille/controuille2_debut', $vars);
	}

	public function controuille2()
	{
		// On vérifie que le joueur n'a pas déjà fait ce controuille
		if (strpos($this->session->userdata('notes_controuilles'), '_') !== false)
			redirect('joueur/choix_perso');

		if ( ! $this->input->isPost())
			return $this->layout->view('controuille/controuille2');

		// Ici on ne calcule pas la note mais on prend une note aléatoire < 10
		// En effet le joueur ne va pas passer sa vie à l'école, faut pas déconner :)
		$note = mt_rand(0, 4) * 2 + mt_rand(0, 1);
		
		// Le joueur passe à l'état Actif
		$data_joueur = array(
			'statut'             => Bouzouk::Joueur_ChoixPerso,
			'date_statut'        => bdd_date(),
			'notes_controuilles' => $this->session->userdata('notes_controuilles').'_'.$note
		);
		$this->db->where('id', $this->session->userdata('id'))
				 ->update('joueurs', $data_joueur);
		$this->session->set_userdata($data_joueur);

		// On affiche le résultat
		$vars = array(
			'note' => $note
		);
		return $this->layout->view('controuille/controuille2_fin', $vars);
	}
}
