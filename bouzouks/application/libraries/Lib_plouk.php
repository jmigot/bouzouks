<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions du jeu de plouk
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : février 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Lib_plouk
{
	// plouk.statut
	const Proposee = 0;
	const Attente  = 1;
	const EnCours  = 2;
	const Terminee = 3;
	
	private $CI;
	
	private $cartes = array(
		// id carte => (médiatisation min [bleu], partisans min [vert], rejouer)
		 1 => array( 0,  0, 0),  11 => array( 0, 15, 0),  21 => array(20,  0, 1),  31 => array(13,  0, 0),  41 => array( 0, 10, 0),
		 2 => array( 0, 25, 0),  12 => array( 0,  4, 0),  22 => array( 0,  0, 0),  32 => array( 0, 28, 0),  42 => array( 0,  3, 0),
		 3 => array(20,  0, 0),  13 => array( 0,  6, 0),  23 => array( 0,  0, 0),  33 => array( 5,  0, 0),  43 => array( 0,  4, 0),
		 4 => array( 0,  8, 0),  14 => array( 0,  4, 0),  24 => array(10,  0, 0),  34 => array( 0, 15, 0),  44 => array( 4,  0, 0),
		 5 => array( 0,  0, 0),  15 => array( 0, 30, 0),  25 => array( 0,  0, 0),  35 => array( 3,  0, 0),  45 => array( 0,  2, 1),
		 6 => array(10,  0, 0),  16 => array( 0, 12, 0),  26 => array(12,  0, 0),  36 => array(25,  0, 0),  46 => array( 0,  9, 0),
		 7 => array( 0,  2, 0),  17 => array( 5,  0, 0),  27 => array( 0, 20, 0),  37 => array(16,  0, 0),  47 => array( 9,  0, 1),
		 8 => array(15,  0, 1),  18 => array( 0,  2, 0),  28 => array( 0, 10, 1),  38 => array(40,  0, 0),  48 => array( 9,  0, 0),
		 9 => array( 0,  0, 0),  19 => array( 0,  0, 0),  29 => array(15,  0, 0),  39 => array( 0,  3, 0),  49 => array(16,  0, 0),
		10 => array(10,  0, 0),  20 => array( 0, 15, 1),  30 => array( 2,  0, 0),  40 => array( 0, 10, 0),  50 => array( 0,  7, 0),
	);
	private $cartes_historique = array(
		1  => '{joueur} gagne +10 partisans. Encore un sale coup du MLB ! :grrr:',
		2  => "{adversaire} vient de perdre -17 de charisme. Un peu dur d'avoir l'air crédible quand on fait sa campagne électorale en prison...",
		3  => "{joueur} gagne +20 de charisme avec son livre autobiographique ?! Mais c'est même pas lui qui l'a écrit ! En plus le dernier chapitre explique comment réaliser une tarte à la crapouille...Bref...",
		4  => "{joueur} gagne +{charisme} de charisme. :haha:",
		5  => "{joueur} gagne +5 médias et je n'en fais pas partie, je suis intègre moi !",
		6  => "En plein dans la trompe ! {adversaire} perd -15 de charisme.",
		7  => "Remise à zéro du charisme des deux candidats !",
		8  => "{adversaire} perd -7 de charisme. Bon en même temps faut être idiot pour tomber dans un piège aussi grotesque, tu n'as pas honte {adversaire} ?",
		9  => "Les deux candidats perdent -6 médias et -5 partisans. Vous pensez que je vais partir aussi ? Et bien noonn ! :svp:",
		10 => "{adversaire} perd {charisme} de charisme.",
		11 => "Outch! La tarte en pleine trompe ! :haha: {adversaire} perd -20 de charisme.",
		12 => "{adversaire} perd -4 médias et -4 partisans.",
		13 => " :hurle_blue: {adversaire} perd -8 de charisme !",
		14 => "{joueur} gagne +4% dans les sondages mais je trouve ça louche. :hu:",
		15 => "Les sondages ont été inversés ! :argh:",
		16 => "{joueur} gagne +20 de charisme.",
		17 => "{joueur} gagne +10 de charisme. Je ne sais pas pourquoi mais bien qu'{sexe:il ait l'air idiot|elle ait l'air idiote}, j'ai envie de voter pour {sexe:lui|elle}...",
		18 => "Grâce au soutien de l'intellectuel de la ville, {joueur} gagne +2% dans les sondages.",
		19 => "{adversaire} perd -5 de charisme.",
		20 => "{adversaire} gagne +15 de charisme grâce à son lifting de la trompe mais je trouve que ça fait très naturel...",
		21 => "{sexe:Le|La} charismatique {joueur} gagne +10 partisans et +20 de charisme !",
		22 => "{joueur} gagne +{partisans} partisans.",
		23 => "{joueur} gagne +{mediatisation} médias et {sexe:il|elle} les mérite ! :ouin:",
		24 => "+6% dans les sondages pour {joueur}, bien joué.",
		25 => "Après vérification, {joueur} gagne +2% dans les sondages.",
		26 => "Les charismes ont été inversés.",
		27 => "{adversaire} perd -10 de charisme. Tu n'as pas honte de tricher {joueur} ?!",
		28 => "{adversaire} perd -10 de charisme et rejoue.",
		29 => "{joueur} gagne +10 de charisme. :smile:",
		30 => "Remise à zéro de la médiatisation et des partisans des deux candidats.",
		31 => "{joueur} gagne +10% dans les sondages mais son charisme est remis à 0. Est-ce que {adversaire} va en profiter ?",
		32 => array("Pas mal ! {joueur} gagne +10% dans les sondages et +25 de charisme !", "{joueur} gagne +15 de médiatisation, {sexe:il|elle} prépare un sale coup, c'est sur ! :search:"),
		33 => "Et +8 partisans pour {joueur}.",
		34 => "Petite transaction de {joueur} qui gagne +15 de médias.",
		35 => "Les deux joueurs gagnent +10 médias et +10 partisans.",
		36 => "{adversaire} perd -16 de charisme.",
		37 => "{joueur} gagne +16 de charisme.",
		38 => "Oulà ça fait mal !! {adversaire} perd -40 de charisme ! Bien joué {joueur}. :yeah:",
		39 => "{joueur} gagne +10 de charisme.",
		40 => "{adversaire} gagne +10 partisans et son charisme est remis à 0. Bonne ou mauvaise stratégie ?",
		41 => "{adversaire} perd -10 de charisme et -5 partisans.",
		42 => "{joueur} gagne +{mediatisation} médias.",
		43 => "{joueur} gagne +5 médias et {adversaire} perd -10 partisans.",
		44 => "Mise à 20 des partisans et médias des deux candidats.",
		45 => "{joueur} gagne +6 médias et rejoue.",
		46 => "Les médiatisations ont été inversées.",
		47 => "{adversaire} est visiblement mal entouré, hop -10 de charisme.",
		48 => "Scandale ! On vous ment, On vous spolie ! {adversaire} perd {charisme} de charisme, bien fait ! :hey:",
		49 => "Les partisans ont été inversés. :hurle_red:",
		50 => "{adversaire} perd -15 de charisme à cause de ces saletés de paparazzis."
	);
	private $max_mediatisation = 60;
	private $max_partisans = 60;
	private $max_charisme = 100;
	private $max_sondages = 100;
	
	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function creer_jeu($charisme, $mediatisation, $partisans)
	{
		// On assemble les infos (sondages = 50 au départ)
		$infos = "$charisme|$mediatisation|$partisans|50";
		
		// On tire aléatoirement 6 cartes et on les ajoute (pas de doublons)
		$cartes = array_keys($this->cartes);
		
		for ($i = 0 ; $i < 6 ; $i++)
		{
			$carte = mt_rand(0, count($cartes) - 1);
			$infos .= '|'.$cartes[$carte];

			// Si c'est une carte 'rejouer', on enlève toutes les cartes 'rejouer' de la liste
			if ($this->cartes[$cartes[$carte]][2])
				$this->supprimer_cartes_rejouer($cartes);

			else
				unset($cartes[$carte]);

			$cartes = array_values($cartes);
		}
			
		// On renvoit les infos
		return $infos;
	}

	public function plouk_stats($joueur_id, $gain, $perte, $egalite)
	{
		$query = $this->CI->db->select('plouk_stats')
							  ->from('joueurs')
							  ->where('id', $joueur_id)
							  ->get();

		if ($query->num_rows() > 0)
		{
			$joueur = $query->row();
			$stats = explode('|', $joueur->plouk_stats);
			$stats[0] += $gain;
			$stats[1] += $perte;
			$stats[2] += $egalite;

			$this->CI->db->set('plouk_stats', implode('|', $stats))
						 ->where('id', $joueur_id)
						 ->update('joueurs');
		}
	}

	public function historique($partie, $message, $joueur_pseudo = '', $adversaire_pseudo = '', $charisme = 0, $mediatisation = 0, $partisans = 0)
	{
		$message = str_replace('{joueur}', $joueur_pseudo, $message);
		$message = str_replace('{adversaire}', $adversaire_pseudo, $message);
		$message = str_replace('{charisme}', $charisme, $message);
		$message = str_replace('{mediatisation}', $mediatisation, $message);
		$message = str_replace('{partisans}', $partisans, $message);
		$replace = $this->CI->session->userdata('sexe') == 'male' ? '$1' : '$2';
		$message = preg_replace('#{sexe:(.+)\|(.+)}#U', $replace, $message);

		// Couleur du message
		$couleur = 'pourpre';
		
		if ($partie->joueur_actuel == $partie->createur_id)
			$couleur = 'rouge';

		else if ($partie->joueur_actuel == $partie->adversaire_id)
			$couleur = 'bleu';

		$message = '<span class="'.$couleur.'">'.$message.'</span>';
		
		// Message commentateur machine à café
		$data_plouk_tchat = array(
			'partie_id'  => $partie->id,
			'joueur_id'  => Bouzouk::Robot_MissPoohLett,
			'message'    => $message,
			'date_envoi' => bdd_datetime()
		);
		$this->CI->db->insert('plouk_tchat', $data_plouk_tchat);
	}

	public function flash($partie, $message, $permanent = false)
	{
		$partie->flash_permanent = '';
		$partie->flash_temporaire = '';
		
		if ($permanent)
			$partie->flash_permanent = $message;

		else
			$partie->flash_temporaire = $message;
	}

	public function explode_jeux($partie, $jouer = false)
	{
		$messages = explode('|', $partie->messages);
		$partie->flash_permanent  = $messages[0];
		$partie->flash_temporaire = $messages[1];
		unset($partie->messages);

		// La partie attend un adversaire
		if ($partie->statut == Lib_plouk::Proposee)
		{
			$partie->createur_charisme      = $partie->charisme;
			$partie->createur_mediatisation = $partie->mediatisation;
			$partie->createur_partisans     = $partie->partisans;
			$partie->createur_sondages      = 50;

			$partie->adversaire_charisme      = $partie->charisme;
			$partie->adversaire_mediatisation = $partie->mediatisation;
			$partie->adversaire_partisans     = $partie->partisans;
			$partie->adversaire_sondages      = 50;
			
			return;
		}
			
		// On récupère toutes les valeurs de jeu dans un tableau
		$createur_jeu = explode('|', $partie->createur_jeu);
		$adversaire_jeu = explode('|', $partie->adversaire_jeu);

		// Créateur
		$partie->createur_charisme      = $createur_jeu[0];
		$partie->createur_mediatisation = $createur_jeu[1];
		$partie->createur_partisans     = $createur_jeu[2];
		$partie->createur_sondages      = $createur_jeu[3];

		// Adversaire
		$partie->adversaire_charisme      = $adversaire_jeu[0];
		$partie->adversaire_mediatisation = $adversaire_jeu[1];
		$partie->adversaire_partisans     = $adversaire_jeu[2];
		$partie->adversaire_sondages      = $adversaire_jeu[3];

		// On récupère les cartes des joueurs
		if ($jouer)
		{
			for ($i = 1 ; $i <= 6 ; $i++)
				$partie->{'createur_carte_'.$i} = $createur_jeu[3+$i];

			for ($i = 1 ; $i <= 6 ; $i++)
				$partie->{'adversaire_carte_'.$i} = $adversaire_jeu[3+$i];
		}

		else
		{
			if ($partie->createur_id == $this->CI->session->userdata('id'))
			{
				for ($i = 1 ; $i <= 6 ; $i++)
					$partie->{'carte_'.$i} = $createur_jeu[3+$i];
			}

			else
			{
				for ($i = 1 ; $i <= 6 ; $i++)
					$partie->{'carte_'.$i} = $adversaire_jeu[3+$i];
			}
		}

		unset($partie->createur_jeu);
		unset($partie->adversaire_jeu);
	}

	public function implode_jeux($partie)
	{
		// On recompose le jeu du créateur
		$createur_jeu = array($partie->createur_charisme, $partie->createur_mediatisation, $partie->createur_partisans, $partie->createur_sondages);

		for ($i = 1 ; $i <= 6 ; $i++)
			$createur_jeu[] = $partie->{'createur_carte_'.$i};

		$partie->createur_jeu = implode('|', $createur_jeu);

		// On recompose le jeu de l'adversaire
		$adversaire_jeu = array($partie->adversaire_charisme, $partie->adversaire_mediatisation, $partie->adversaire_partisans, $partie->adversaire_sondages);

		for ($i = 1 ; $i <= 6 ; $i++)
			$adversaire_jeu[] = $partie->{'adversaire_carte_'.$i};
			
		$partie->adversaire_jeu = implode('|', $adversaire_jeu);

		// On recompose les messages
		$partie->messages = $partie->flash_permanent.'|'.$partie->flash_temporaire;
	}

	public function tirer_nouvelle_carte($partie, $carte_a_remplacer = null)
	{
		// On récupère le joueur
		$joueur = ($partie->joueur_actuel == $partie->createur_id) ? 'createur' : 'adversaire';

		// Si pas de carte spécifiée, on en tire une au hasard pour la défausse
		if ( ! isset($carte_a_remplacer))
		{
			$i = mt_rand(1, 6);
			$carte_a_remplacer = $partie->{$joueur.'_carte_'.$i};
		}

		$partie->derniere_carte = $carte_a_remplacer;
		$carte_a_remplacer = (int)$carte_a_remplacer;
		
		// On récupère la liste de toutes les cartes
		$cartes = array();

		foreach ($this->cartes as $carte => $array)
			$cartes[$carte] = $carte;

		// On regarde si le joueur a déjà une carte 'rejouer'
		$rejouer = false;

		for ($i = 1 ; $i <= 6 ; $i++)
		{
			$carte = (int)$partie->{$joueur.'_carte_'.$i};
			
			if ($carte != $carte_a_remplacer && $this->cartes[$carte][2])
				$rejouer = true;
		}

		if ($this->cartes[$carte_a_remplacer][2])
			$rejouer = true;
			
		// Si le joueur a une carte 'rejouer', on enlève toutes les cartes 'rejouer' de la liste
		if ($rejouer)
			$this->supprimer_cartes_rejouer($cartes);
			
		// On trouve l'indice de la carte dans la main du joueur
		$indice = 1;
		
		for ($i = 1 ; $i <= 6 ; $i++)
		{
			if ($partie->{$joueur.'_carte_'.$i} == $carte_a_remplacer)
			{
				$indice = $i;
				break;
			}
		}
		
		// On enlève toutes les cartes du joueur de la liste (pour pas avoir de doublons)
		for ($i = 1 ; $i <= 6 ; $i++)
		{
			if (isset($cartes[(int)$partie->{$joueur.'_carte_'.$i}]))
				unset($cartes[(int)$partie->{$joueur.'_carte_'.$i}]);
		}
		
		// On tire une carte aléatoire parmis celles restantes et on l'ajoute au joueur
		$cartes = array_values($cartes);
		$carte = mt_rand(0, count($cartes) - 1);
		$partie->{$joueur.'_carte_'.$indice} = $cartes[$carte];
	}

	public function supprimer_cartes_rejouer(&$tab)
	{
		$supp = array();

		// On parcourt le tableau
		foreach ($tab as $cle => $carte)
		{
			// Si la carte est rejouer, on l'enlève
			if ($this->cartes[$carte][2])
				$supp[] = $cle;
		}

		// On supprime toutes les cartes 'rejouer'
		foreach ($supp as $cle)
			unset($tab[$cle]);
	}

	public function joueur_suivant($partie, $carte = null)
	{
		$joueur_actuel = $partie->joueur_actuel;

		// On tire une nouvelle carte au joueur
		if ( ! isset($carte))
		{
			$pseudo = $partie->joueur_actuel == $partie->createur_id ? $partie->createur_pseudo : $partie->adversaire_pseudo;
			$this->historique($partie, $pseudo.' se défausse');
		}

		$this->tirer_nouvelle_carte($partie, $carte);

		// On change de joueur
		if ( ! $partie->rejouer)
		{
			$joueur_actuel = ($partie->joueur_actuel == $partie->createur_id) ? $partie->adversaire_id : $partie->createur_id;

			// On augmente le tour quand on revient au joueur créateur
			if ($joueur_actuel == $partie->createur_id)
			{
				$partie->tour_actuel++;

				// Si c'est le dernier tour
				if ($partie->tour_actuel == $partie->nb_tours)
					$this->flash($partie, 'Dernier tour !');
					
				// On ajoute des partisans et de la médiatisation au joueur qui vient de jouer
				$this->changer_partisans($partie, 'adversaire', $partie->partisans);
				$this->changer_mediatisation($partie, 'adversaire', $partie->mediatisation);
			}

			else
			{
				// On ajoute des partisans et de la médiatisations au joueur qui vient de jouer
				$this->changer_partisans($partie, 'createur', $partie->partisans);
				$this->changer_mediatisation($partie, 'createur', $partie->mediatisation);
			}
		}

		// On réinitialise les chronos
		if ($joueur_actuel == $partie->createur_id)
		{
			$partie->createur_chrono = time() + $partie->chrono;
			$partie->adversaire_chrono = 0;
		}

		else
		{
			$partie->createur_chrono = 0;
			$partie->adversaire_chrono = time() + $partie->chrono;
		}

		$partie->joueur_actuel = $joueur_actuel;
	}

	public function verifier_partie_gagnee($partie)
	{
		// Une partie est gagnée si :
		// - le tour actuel a atteint le nombre de tours de la partie
		// - le créateur est à 0% de sondages
		// - l'adversaire est à 0% de sondages
		if ($partie->tour_actuel > $partie->nb_tours || $partie->createur_sondages == 0 || $partie->adversaire_sondages == 0)
		{
			$partie->statut = Lib_plouk::Terminee;

			if ($partie->tour_actuel > $partie->nb_tours)
				$partie->tour_actuel--;

			// Sondages égaux
			if ($partie->createur_sondages == $partie->adversaire_sondages)
			{
				// Charismes égaux : égalité totale
				if ($partie->createur_charisme == $partie->adversaire_charisme)
					$partie->gagnant_id = 0;

				// Le créateur a gagné
				else if ($partie->createur_charisme > $partie->adversaire_charisme)
					$partie->gagnant_id = $partie->createur_id;

				// L'adversaire a gagné
				else
					$partie->gagnant_id = $partie->adversaire_id;
			}

			// Le créateur a gagné
			else if ($partie->createur_sondages > $partie->adversaire_sondages)
				$partie->gagnant_id = $partie->createur_id;

			// L'adversaire a gagné
			else
				$partie->gagnant_id = $partie->adversaire_id;

			// On enlève les deux chronos
			$partie->createur_chrono = 0;
			$partie->adversaire_chrono = 0;

			// On affiche un message
			if ($partie->gagnant_id != 0)
			{
				$message  = ($partie->gagnant_id == $partie->createur_id) ? $partie->createur_pseudo : $partie->adversaire_pseudo;
				$message .= ' est<br>le vainqueur !';
			}

			else
				$message = 'Partie finie...<br>Egalité parfaite !';

			$this->flash($partie, $message, true);
			
			$partie->joueur_actuel = 0;

			// On distribue les objets
			if ($partie->objet_id != null)
			{
				// Si les deux joueurs sont gagnant
				if ($partie->gagnant_id == 0)
				{
					// On leur rend leurs objets
					$this->CI->bouzouk->ajouter_objets($partie->objet_id, 1, $partie->createur_objet_peremption, $partie->createur_id);
					$this->CI->bouzouk->ajouter_objets($partie->objet_id, 1, $partie->adversaire_objet_peremption, $partie->adversaire_id);
				}

				else
				{
					// On donne les deux objets au gagnant
					$this->CI->bouzouk->ajouter_objets($partie->objet_id, 1, $partie->createur_objet_peremption, $partie->gagnant_id);
					$this->CI->bouzouk->ajouter_objets($partie->objet_id, 1, $partie->adversaire_objet_peremption, $partie->gagnant_id);
				}
			}

			// Historique + compteurs
			$gain_objet = ($partie->objet_id != null) ? ' et tu gagnes donc 1 <span class="pourpre">'.$partie->objet_nom.'</span>' : '';
			$perte_objet = ($partie->objet_id != null) ? ' et tu perds donc 1 <span class="pourpre">'.$partie->objet_nom.'</span>' : '';
			
			// Égalité
			if ($partie->gagnant_id == 0)
			{
				$this->CI->bouzouk->historique(194, 195, array(profil($partie->adversaire_id, $partie->adversaire_pseudo)), $partie->createur_id);
				$this->CI->bouzouk->historique(194, 195, array(profil($partie->createur_id, $partie->createur_pseudo)), $partie->adversaire_id);
				$this->plouk_stats($partie->createur_id, 0, 0, 1);
				$this->plouk_stats($partie->adversaire_id, 0, 0, 1);
			}

			// Créateur vainqueur
			else if ($partie->gagnant_id == $partie->createur_id)
			{
				$this->CI->bouzouk->historique(196, 197, array(profil($partie->adversaire_id, $partie->adversaire_pseudo), $gain_objet), $partie->createur_id);
				$this->CI->bouzouk->historique(198, 199, array(profil($partie->createur_id, $partie->createur_pseudo), $perte_objet), $partie->adversaire_id);
				$this->plouk_stats($partie->createur_id, 1, 0, 0);
				$this->plouk_stats($partie->adversaire_id, 0, 1, 0);
			}

			// Adversaire vainqueur
			else
			{
				$this->CI->bouzouk->historique(196, 197, array(profil($partie->createur_id, $partie->createur_pseudo), $gain_objet), $partie->adversaire_id);
				$this->CI->bouzouk->historique(198, 199, array(profil($partie->adversaire_id, $partie->adversaire_pseudo), $perte_objet), $partie->createur_id);
				$this->plouk_stats($partie->createur_id, 0, 1, 0);
				$this->plouk_stats($partie->adversaire_id, 1, 0, 0);
			}

			// On met à jour les sessions pour faire disparaître le lien "Jouer"
			$this->CI->bouzouk->augmente_version_session($partie->createur_id);
			$this->CI->bouzouk->augmente_version_session($partie->adversaire_id);
			
			// On supprime la pige si il y en a une de faites pour cette partie
			$this->CI->db->delete('piges', array('lien' => site_url('plouk/suivre/'.$partie->id))); 
		}

		// On enregistre la partie pour le classement et pour les multicomptes
		$data_mc_plouk = array(
			'createur_id'           => $partie->createur_id,
			'adversaire_id'         => $partie->adversaire_id,
			'objet_id'              => $partie->objet_id,
			'createur_peremption'   => $partie->createur_objet_peremption,
			'adversaire_peremption' => $partie->adversaire_objet_peremption,
			'chrono'                => $partie->chrono,
			'nb_tours'              => $partie->nb_tours,
			'date_debut'            => $partie->date_statut,
			'date_fin'              => bdd_datetime(),
			'mot_de_passe'          => $partie->mot_de_passe,
			'abandon'               => isset($partie->abandon) && $partie->abandon,
			'version'               => $partie->version,
			'gagnant_id'            => $partie->gagnant_id
		);
		$this->CI->db->insert('mc_plouk', $data_mc_plouk);
	}
	
	public function jouer_carte($partie, $carte)
	{
		// On vérifie que la carte existe
		if ($carte === false || ! in_array($carte, array_keys($this->cartes)))
		{
			$partie->alert = 'Il faut sélectionner une carte à jouer';
			return false;
		}

		$joueur            = $this->CI->session->userdata('id') == $partie->createur_id ? 'createur' : 'adversaire';
		$adversaire        = $this->CI->session->userdata('id') == $partie->createur_id ? 'adversaire' : 'createur';
		$joueur_pseudo     = $this->CI->session->userdata('id') == $partie->createur_id ? $partie->createur_pseudo : $partie->adversaire_pseudo;
		$adversaire_pseudo = $this->CI->session->userdata('id') == $partie->createur_id ? $partie->adversaire_pseudo : $partie->createur_pseudo;
		
		// On vérifie que la carte appartient au joueur
		$possede = false;
		
		for ($i = 1; $i <= 6 ; $i++)
		{
			if ($carte == $partie->{$joueur.'_carte_'.$i})
				$possede = true;
		}

		if ( ! $possede)
		{
			$partie->alert = 'Tu ne possèdes pas cette carte';
			return false;
		}
		
		// On vérifie que le joueur a assez de médiatisation ou de partisans pour jouer cette carte
		if ($partie->{$joueur.'_mediatisation'} < $this->cartes[$carte][0])
		{
			$partie->alert = 'Il faut '.$this->cartes[$carte][0].' de médiatisation pour jouer cette carte';
			return false;
		}
		
		if ($partie->{$joueur.'_partisans'} < $this->cartes[$carte][1])
		{
			$partie->alert = 'Il faut '.$this->cartes[$carte][1].' partisans pour jouer cette carte';
			return false;
		}
		
		// On joue la carte
		if ($carte == 1)
			$this->changer_partisans($partie, $joueur, 10);

		else if ($carte == 2)
			$this->changer_charisme($partie, $adversaire, -17);

		else if ($carte == 3)
			$this->changer_charisme($partie, $joueur, 20);

		else if ($carte == 4)
		{
			$charisme = 5;
			
			if ($this->charisme($partie, $joueur) == 0)
				$charisme = 20;
				
			$this->changer_charisme($partie, $joueur, $charisme);
		}

		else if ($carte == 5)
			$this->changer_mediatisation($partie, $joueur, 5);

		else if ($carte == 6)
			$this->changer_charisme($partie, $adversaire, -15);

		else if ($carte == 7)
		{
			$this->set_charisme($partie, $joueur, 0);
			$this->set_charisme($partie, $adversaire, 0);
		}

		else if ($carte == 8)
			$this->changer_charisme($partie, $adversaire, -7);

		else if ($carte == 9)
		{
			$this->changer_mediatisation($partie, $joueur, -6);
			$this->changer_mediatisation($partie, $adversaire, -6);
			$this->changer_partisans($partie, $joueur, -5);
			$this->changer_partisans($partie, $adversaire, -5);
		}

		else if ($carte == 10)
		{
			$charisme = -5;

			if ($this->charisme($partie, $adversaire) > 15)
				$charisme = -20;
				
			$this->changer_charisme($partie, $adversaire, $charisme);
		}

		else if ($carte == 11)
			$this->changer_charisme($partie, $adversaire, -20);

		else if ($carte == 12)
		{
			$this->changer_mediatisation($partie, $adversaire, -4);
			$this->changer_partisans($partie, $adversaire, -4);
		}

		else if ($carte == 13)
			$this->changer_charisme($partie, $adversaire, -8);

		else if ($carte == 14)
			$this->changer_sondages($partie, $joueur, 4);

		else if ($carte == 15)
		{
			$tmp = $this->sondages($partie, $joueur);
			$this->set_sondages($partie, $joueur, $this->sondages($partie, $adversaire));
			$this->set_sondages($partie, $adversaire, $tmp);
		}

		else if ($carte == 16)
			$this->changer_charisme($partie, $joueur, 20);

		else if ($carte == 17)
			$this->changer_charisme($partie, $joueur, 10);

		else if ($carte == 18)
			$this->changer_sondages($partie, $joueur, 2);

		else if ($carte == 19)
			$this->changer_charisme($partie, $adversaire, -5);

		else if ($carte == 20)
			$this->changer_charisme($partie, $joueur, 15);

		else if ($carte == 21)
		{
			$this->changer_partisans($partie, $joueur, 10);
			$this->changer_charisme($partie, $joueur, 20);
		}

		else if ($carte == 22)
		{
			$partisans = 3;

			if ($this->partisans($partie, $joueur) == 0)
				$partisans = 15;
				
			$this->changer_partisans($partie, $joueur, $partisans);
		}

		else if ($carte == 23)
		{
			$mediatisation = 3;

			if ($this->mediatisation($partie, $joueur) == 0)
				$mediatisation = 15;

			$this->changer_mediatisation($partie, $joueur, $mediatisation);
		}

		else if ($carte == 24)
			$this->changer_sondages($partie, $joueur, 6);

		else if ($carte == 25)
			$this->changer_sondages($partie, $joueur, 2);

		else if ($carte == 26)
		{
			$tmp = $this->charisme($partie, $joueur);
			$this->set_charisme($partie, $joueur, $this->charisme($partie, $adversaire));
			$this->set_charisme($partie, $adversaire, $tmp);
		}

		else if ($carte == 27)
			$this->changer_charisme($partie, $adversaire, -10);

		else if ($carte == 28)
			$this->changer_charisme($partie, $adversaire, -10);

		else if ($carte == 29)
			$this->changer_charisme($partie, $joueur, 10);

		else if ($carte == 30)
		{
			$this->set_mediatisation($partie, $joueur, 0);
			$this->set_mediatisation($partie, $adversaire, 0);
			$this->set_partisans($partie, $joueur, 0);
			$this->set_partisans($partie, $adversaire, 0);
		}

		else if ($carte == 31)
		{
			$this->changer_sondages($partie, $joueur, 10);
			$this->set_charisme($partie, $joueur, 0);
		}

		else if ($carte == 32)
		{
			if ($this->sondages($partie, $adversaire) > 50)
			{
				$this->changer_sondages($partie, $joueur, 10);
				$this->changer_charisme($partie, $joueur, 25);
				$array_historique = 0;
			}

			else
			{
				$this->changer_mediatisation($partie, $joueur, 15);
				$array_historique = 1;
			}
		}

		else if ($carte == 33)
			$this->changer_partisans($partie, $joueur, 8);

		else if ($carte == 34)
			$this->changer_mediatisation($partie, $joueur, 15);

		else if ($carte == 35)
		{
			$this->changer_mediatisation($partie, $joueur, 10);
			$this->changer_mediatisation($partie, $adversaire, 10);
			$this->changer_partisans($partie, $joueur, 10);
			$this->changer_partisans($partie, $adversaire, 10);
		}

		else if ($carte == 36)
			$this->changer_charisme($partie, $adversaire, -16);

		else if ($carte == 37)
			$this->changer_charisme($partie, $joueur, 16);

		else if ($carte == 38)
			$this->changer_charisme($partie, $adversaire, -40);

		else if ($carte == 39)
			$this->changer_charisme($partie, $joueur, 10);

		else if ($carte == 40)
		{
			$this->set_charisme($partie, $adversaire, 0);
			$this->changer_partisans($partie, $adversaire, 10);
		}

		else if ($carte == 41)
		{
			$this->changer_charisme($partie, $adversaire, -10);
			$this->changer_partisans($partie, $adversaire, -5);
		}

		else if ($carte == 42)
		{
			$mediatisation = 4;
			
			if ($this->partisans($partie, $adversaire) > 20)
				$mediatisation = 15;
				
			$this->changer_mediatisation($partie, $joueur, $mediatisation);
		}

		else if ($carte == 43)
		{
			$this->changer_partisans($partie, $adversaire, -10);
			$this->changer_mediatisation($partie, $joueur, 5);
		}

		else if ($carte == 44)
		{
			$this->set_mediatisation($partie, $joueur, 20);
			$this->set_mediatisation($partie, $adversaire, 20);
			$this->set_partisans($partie, $joueur, 20);
			$this->set_partisans($partie, $adversaire, 20);
		}

		else if ($carte == 45)
			$this->changer_mediatisation($partie, $joueur, 6);

		else if ($carte == 46)
		{
			$tmp = $this->mediatisation($partie, $joueur);
			$this->set_mediatisation($partie, $joueur, $this->mediatisation($partie, $adversaire));
			$this->set_mediatisation($partie, $adversaire, $tmp);
		}

		else if ($carte == 47)
			$this->changer_charisme($partie, $adversaire, -10);

		else if ($carte == 48)
		{
			$charisme = -5;

			if ($this->sondages($partie, $joueur) < 45)
				$charisme = -20;
				
			$this->changer_charisme($partie, $adversaire, $charisme);
		}

		else if ($carte == 49)
		{
			$tmp = $this->partisans($partie, $joueur);
			$this->set_partisans($partie, $joueur, $this->partisans($partie, $adversaire));
			$this->set_partisans($partie, $adversaire, $tmp);
		}

		else if ($carte == 50)
			$this->changer_charisme($partie, $adversaire, -15);

		// On ajoute à l'historique
		$historique = $this->cartes_historique[$carte];
		
		if ($carte == 32)
			$historique = $historique[$array_historique];

		$this->historique($partie, $historique, $joueur_pseudo, $adversaire_pseudo, isset($charisme) ? $charisme : 0, isset($mediatisation) ? $mediatisation : 0, isset($partisans) ? $partisans : 0);
		
		// On enlève les partisans et la médiatisation
		if ($this->cartes[$carte][0] > 0)
			$this->changer_mediatisation($partie, $joueur, -$this->cartes[$carte][0]);
		
		if ($this->cartes[$carte][1] > 0)
			$this->changer_partisans($partie, $joueur, -$this->cartes[$carte][1]);
		
		// Rejouer ?
		$partie->rejouer = $this->cartes[$carte][2];

		if ($partie->rejouer)
			$this->flash($partie, $joueur_pseudo.' rejoue');
		
		return true;
	}

	// Getters
	private function charisme($partie, $joueur)
	{
		return $partie->{$joueur.'_charisme'};
	}

	private function partisans($partie, $joueur)
	{
		return $partie->{$joueur.'_partisans'};
	}

	private function mediatisation($partie, $joueur)
	{
		return $partie->{$joueur.'_mediatisation'};
	}

	private function sondages($partie, $joueur)
	{
		return $partie->{$joueur.'_sondages'};
	}

	// Setters
	private function set_charisme($partie, $joueur, $valeur)
	{
		$partie->{$joueur.'_charisme'} = $valeur;
	}

	public function set_sondages($partie, $joueur, $valeur)
	{
		$partie->{$joueur.'_sondages'} = $valeur;
	}

	private function set_mediatisation($partie, $joueur, $valeur)
	{
		$partie->{$joueur.'_mediatisation'} = $valeur;
	}

	private function set_partisans($partie, $joueur, $valeur)
	{
		$partie->{$joueur.'_partisans'} = $valeur;
	}

	// Modifiers
	public function changer_partisans($partie, $joueur, $valeur)
	{
		$resultat = $partie->{$joueur.'_partisans'} + $valeur;
		$partie->{$joueur.'_partisans'} = max(0, min($resultat, $this->max_partisans));
	}

	public function changer_mediatisation($partie, $joueur, $valeur)
	{
		$resultat = $partie->{$joueur.'_mediatisation'} + $valeur;
		$partie->{$joueur.'_mediatisation'} = max(0, min($resultat, $this->max_mediatisation));
	}

	private function changer_charisme($partie, $joueur, $valeur)
	{
		$resultat = $partie->{$joueur.'_charisme'} + $valeur;
		$partie->{$joueur.'_charisme'} = max(0, min($resultat, $this->max_charisme));

		// Le charisme absorbe le sondage
		if ($resultat < 0)
			$this->changer_sondages($partie, $joueur, $resultat);
	}

	private function changer_sondages($partie, $joueur, $valeur, $stop = false)
	{
		$resultat = $partie->{$joueur.'_sondages'} + $valeur;
		$partie->{$joueur.'_sondages'} = max(0, min($resultat, $this->max_sondages));

		// Le sondage adverse est affecté à chaque fois de l'inverse de la valeur
		if ( ! $stop)
		{
			$adversaire = ($joueur == 'createur') ? 'adversaire' : 'createur';
			$this->changer_sondages($partie, $adversaire, -$valeur, true);
		}
	}
}
