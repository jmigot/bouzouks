<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : jeux divers (loto, bonneteau...)
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Jeux extends MY_Controller
{
	private $lohtoh_nums = array('GNEE', 'KAH', 'ZIG', 'STO', 'BLAZ', 'DRU', 'GOZ', 'POO', 'BNZ', 'GLAP');

	public function __construct()
	{
		parent::__construct();
		
		// ---------- Hook clans ----------
		// Malediction du Schnibble (SDS)
		if ($this->session->userdata('maudit') && ! $this->bouzouk->is_admin())
			redirect('clans/maudit');
		
		if ( ! in_array($this->methode, array('scraplouk')))
			$this->bouzouk->verifier_factures();
	}

	public function lohtoh()
	{
		// Si le joueur est trop faible, il ne peut pas jouer
		$message = '';

		if ($this->session->userdata('faim') < $this->bouzouk->config('jeux_faim_lohtoh'))
			$message = "Le lohtoh t'es fermé : ton ventre gargouille trop pour que les joueurs puissent se concentrer.";

		else if ($this->session->userdata('sante') < $this->bouzouk->config('jeux_sante_lohtoh'))
			$message = "Le lohtoh t'es fermé : on ne veut pas de tes microbes.";

		else if ($this->session->userdata('stress') > $this->bouzouk->config('jeux_stress_lohtoh'))
			$message = "Le lohtoh t'es fermé : ton stress te fait hurler des numéros au pif et cela perturbe les autres joueurs.";

		if ($message != '')
		{
			$vars = array(
				'titre_layout' => 'Lohtoh',
				'titre'        => 'Tu es trop faible',
				'image_url'    => 'trop_faible.png',
				'message'      => $message
			);
			return $this->layout->view('blocage', $vars);
		}/*
		// Blocage Roi Boubouch
		else{
			$vars=  array(
				'titre_layout'	=>'Lohtoh',
				'titre'			=> 'Fermé par ordre du Roi !',
				'image_url'		=> './uploads/bestiole_jaune/roi_boubouch.png',
				'message'		=> "Suite à la nouvelle constitution de Vlurxtrznbnaxl instaurée par notre roi auto-proclamé, Boubouch, l'accès au lohtoh n'est plus autorisé !<br><br><a href='http://www.bouzouks.net/tobozon/viewtopic.php?id=2844'>Détails de la nouvelle constitution disponible sur votre Toboz.</a>"
				);
			return $this->layout->view('blocage', $vars);
		}*/

		// On va chercher le total de la cagnotte
		$query = $this->db->select('cagnotte_lohtoh, impots_lohtoh')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();

		// On va chercher les numéros déjà joués
		$query = $this->db->select('numeros, montant, date')
						  ->from('loterie')
						  ->where('joueur_id', $this->session->userdata('id'))
						  ->get();
		$nb_numeros_joues = $query->num_rows();
		$numeros_joues = $query->result();

		// On calcule le montant total
		$montant_total = 0;

		foreach ($numeros_joues as $numero)
		{
			$montant_total += $numero->montant;
		}

		// On calcule les parts mairie/gagnant selon l'impot fixé par le maire
		$part_mairie = floor($mairie->impots_lohtoh * $mairie->cagnotte_lohtoh / 100);
		$part_gagnant = $mairie->cagnotte_lohtoh - $part_mairie;

		$vars = array(
			'cagnotte'         => $mairie->cagnotte_lohtoh,
			'impots_mairie'    => $mairie->impots_lohtoh,
			'part_mairie'      => $part_mairie,
			'part_gagnant'     => $part_gagnant,
			'numeros_joues'    => $numeros_joues,
			'nb_numeros_joues' => $nb_numeros_joues,
			'montant_total'    => $montant_total,
			'nombres'          => $this->lohtoh_nums
		);

		$nb_numeros_a_jouer = $this->bouzouk->config('jeux_nb_numeros_a_jouer');
		$random = $this->input->post('random') == 'true' ? '1' : '0';

		// Règles de validation
		$this->load->library('form_validation');

		for ($i = 1; $i <= $nb_numeros_a_jouer; $i++)
			$this->form_validation->set_rules('choix'.$i, 'Le n°'.$i, 'is_natural|less_than[10]');

		if ( ! $this->form_validation->run())
			return $this->layout->view('jeux/lohtoh', $vars);

		$prix_ticket = $this->bouzouk->config('jeux_prix_ticket_lohtoh');

		// On vérifie que le joueur a assez de struls
		if ($this->session->userdata('struls') < $prix_ticket)
		{
			$this->echec('Il te faut au moins '.struls($prix_ticket).' pour jouer');
			return $this->layout->view('jeux/lohtoh', $vars);
		}

		// On vérifie que le joueur n'a pas atteint la limite de numéros joués
		$nb_numeros_joues = $this->db->where('joueur_id', $this->session->userdata('id'))
									 ->count_all_results('loterie');

		if ($nb_numeros_joues >= $this->bouzouk->config('jeux_max_tickets_lohtoh'))
		{
			$this->echec('Tu as atteint la limite de numéros à jouer pour ce tirage');
			return $this->layout->view('jeux/lohtoh', $vars);
		}

		// On récupère les numéros
		$numeros_joueur = '';
		$numeros_joueur_texte = '';

		if ( ! $random)
		{
			for ($i = 1; $i <= $nb_numeros_a_jouer; $i++)
			{
				$numeros_joueur .= $this->input->post('choix'.$i);
				$numeros_joueur_texte .= $this->lohtoh_nums[$this->input->post('choix'.$i)];

				if ($i < $nb_numeros_a_jouer)
					$numeros_joueur_texte .= '-';
			}
		}
		else
		{
			$numeros_joueur_1 = mt_rand(0, 9);
			$numeros_joueur_2 = mt_rand(0, 9);
			$numeros_joueur_3 = mt_rand(0, 9);
			
			$numeros_joueur = $numeros_joueur_1.$numeros_joueur_2.$numeros_joueur_3;
			$numeros_joueur_texte = $this->lohtoh_nums[$numeros_joueur_1].'-'.$this->lohtoh_nums[$numeros_joueur_2].'-'.$this->lohtoh_nums[$numeros_joueur_3];
		}

		// On vérifie que le joueur n'a pas déjà joué ces numéros
		$deja_joue = $this->db->where('joueur_id', $this->session->userdata('id'))
							  ->where('numeros', $numeros_joueur)
							  ->count_all_results('loterie');

		if ($deja_joue > 0)
		{
			$this->echec('Tu as déjà joué ces numéros');
			return $this->layout->view('jeux/lohtoh', $vars);
		}

		// On insère la combinaison
		$data_loterie = array(
			'joueur_id' => $this->session->userdata('id'),
			'numeros'   => $numeros_joueur,
			'montant'   => $prix_ticket,
			'date'      => bdd_datetime()
		);
		$this->db->insert('loterie', $data_loterie);

		// On retire le prix du ticket au joueur
		$this->bouzouk->retirer_struls($prix_ticket);

		// On ajoute le prix à la cagnotte
		$this->db->set('cagnotte_lohtoh', 'cagnotte_lohtoh+'.$prix_ticket, false)
				 ->update('mairie');

		// On ajoute 2 d'expérience au joueur si c'est son premier jeu du jour
		if ($nb_numeros_joues == 0)
		{
			$gain_xp = $this->bouzouk->config('jeux_gain_xp_lohtoh');
			$this->bouzouk->ajouter_experience($gain_xp);

			// On ajoute à l'historique
			$message = 'Tu as joué les numéros <span class="pourpre">'.$numeros_joueur_texte.'</span> au lohtoh, tu perds <span class="pourpre">-'.struls($prix_ticket).'</span> et tu gagnes <span class="pourpre">+'.$gain_xp.' xp</span>';
			$this->bouzouk->historique(66, null, array($numeros_joueur_texte, struls($prix_ticket), $gain_xp));

			// On affiche une confirmation
			$this->succes('Tu as joué les numéros <span class="pourpre">'.$numeros_joueur_texte.'</span> au lohtoh, tu perds <span class="pourpre">-'.struls($prix_ticket).'</span> et tu gagnes <span class="pourpre">+'.$gain_xp.' xp</span>, bonne chance :)');
		}

		else
		{
			// On ajoute à l'historique
			$this->bouzouk->historique(67, null, array($numeros_joueur_texte, struls($prix_ticket)));

			// On affiche un message de confirmation
			$this->succes('Tu as joué les numéros <span class="pourpre">'.$numeros_joueur_texte.'</span> au lohtoh, tu perds <span class="pourpre">-'.struls($prix_ticket).'</span>, bonne chance :)');
		}

		// On redirige pour mettre à jour l'affichage
		redirect('jeux/lohtoh');
	}

	public function lohtoh_tirages($offset = '0')
	{
		// Pagination
		$nb_tirages = $this->db->count_all('loterie_tirages');
		$pagination = creer_pagination('jeux/lohtoh_tirages', $nb_tirages, 30, $offset);

		// On récupère les derniers tirages du Lohtoh
		$query = $this->db->select('date, numeros, cagnotte, gagnants')
						  ->from('loterie_tirages')
						  ->order_by('date', 'desc')
						  ->limit($pagination['par_page'], $pagination['offset'])
						  ->get();
		$tirages = $query->result();

		// On affiche
		$vars = array(
			'tirages'    => $tirages,
			'pagination' => $pagination['liens']
		);
		return $this->layout->view('jeux/lohtoh_tirages', $vars);
	}

	public function bonneteau()
	{
		$vars = array(
			'images_bols' => array(
				'1' => 'bonneteau_1.png',
				'2' => 'bonneteau_2.png',
				'3' => 'bonneteau_3.png'
			)
		);

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('mise', 'Le montant de la mise', 'required|is_natural_no_zero|greater_than_or_equal['.$this->bouzouk->config('jeux_min_prix_bonneteau').']');
		$this->form_validation->set_rules('bol', 'Le bol', 'required|is_natural_no_zero|greater_than[0]|less_than_or_equal[3]');

		if ( ! $this->form_validation->run())
			return $this->layout->view('jeux/bonneteau', $vars);

			// On vérifie que le joueur a assez de struls pour miser
			if ($this->session->userdata('struls') < $this->input->post('mise'))
			{
				$this->echec("Wha, l'autre, eh ! Il esssaye de miser plus que ce qu'il ne possède !");
				return $this->layout->view('jeux/bonneteau', $vars);
			}

			// On tire un bol aléatoirement
			$message = '';

		// Gagné
		if ($this->input->post('bol') == mt_rand(1, 4))
		{
			// On ajoute 1 au nombre de parties consécutives gagnées
			$this->session->set_userdata('bonneteau_gagnees', $this->session->userdata('bonneteau_gagnees') + 1);
			$message_gain = '';

			// Si le joueur a gagné plusieurs parties d'affilée il gagne de l'expérience
			$nb_parties_gain_xp = $this->bouzouk->config('jeux_nb_parties_gain_xp');
			if ($this->session->userdata('bonneteau_gagnees') >= $nb_parties_gain_xp)
			{
				$gain_xp = $this->bouzouk->config('jeux_gain_xp_bonneteau');
				$this->bouzouk->ajouter_experience($gain_xp);
				$this->session->set_userdata('bonneteau_gagnees', 0);
				$message_gain = "<br>Puisque tu as gagné <span class='pourpre'>$nb_parties_gain_xp fois</span> d'affilée, tu gagnes <span class='pourpre'>+$gain_xp xp</span> !";
			}

			// On ajoute le gain au joueur
			$gain = 2 * $this->input->post('mise');
			$this->bouzouk->ajouter_struls($gain / 2); // Le joueur perd la mise mais gagne le double, donc on rajoute 1 seule fois la mise

			// On ajoute à l'historique
			$this->bouzouk->historique(68, 69, array(struls($gain), $message_gain));

			// On change l'image gagnante et on affiche un message
			$vars['images_bols'][$this->input->post('bol')] = 'bonneteau_gagne.png';
			$this->succes('Bravo, tu as trouvé un oeil en morphoplastoc sous le <span class="pourpre">bol '.$this->input->post('bol').'</span>. Tu remportes <span class="pourpre">+'.struls($gain).'</span> :)'.$message_gain, 'Gagné !');
		}
		// Perdu
		else
		{
			// On remet le nombre de parties consécutives gagnées à 0
			$this->session->set_userdata('bonneteau_gagnees', 0);

			// On retire la mise au joueur
			$this->bouzouk->retirer_struls($this->input->post('mise'));

			// On ajoute à l'historique
			$this->bouzouk->historique(70, null, array(struls($this->input->post('mise'))));

			// On change l'image gagnante et on affiche un message
			$vars['images_bols'][$this->input->post('bol')] = 'bonneteau_perdu.png';
			$this->echec("Il n'y a rien sous le <span class='pourpre'>bol ".$this->input->post('bol').'</span>, tu as perdu <span class="pourpre">-'.struls($this->input->post('mise')).'</span>...Réessaye encore ;)', 'Perdu...');
		}

		return $this->layout->view('jeux/bonneteau', $vars);
	}

	public function scraplouk()
	{
		return $this->layout->view('jeux/scraplouk');
	}
}