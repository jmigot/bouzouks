<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions de gestion des missives
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : février 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Lib_missive
{
	private $CI;

	// Intros possibles
	private $intros = array(
		'Madame, Monsieur',
		'Monsieur',
		'Madame',
		'Mademoiselle',
		'Salut',
		'Cher ami',
		'Chère amie',
		'Mon Amour',
		'Mon petit pioupiouk adoré',
		'Ma crapouille adorée',
		'Espèce de crapouille',
		'Espèce de face de schnibble',
		"Oh toi, être béni à qui sont adressées ces saintes paroles !",
		"Tronche d'excrément de Crocomouth",
		'Sale pochtron',
		'Sale peurkeur moisi',
		'Hé ! toi !',
		'Yo man !'
	);

	// Formules de politesse possibles
	private $politesses = array(
		'Veuillez agréer, Madame, Monsieur, mes salutations les plus distinguées',
		'Veuillez agréer, Monsieur, mes salutations les plus distinguées',
		'Veuillez agréer, Madame, mes salutations les plus distinguées',
		'Veuillez agréer, Mademoiselle, mes salutations les plus distinguées',
		"Je te prie d'agréer, tronche de beurkeur, à mes menaces les plus certaines",
		"Amicalement, ton petit canari au sucre qui ne t'a jamais tant aimé",
		'Cordialement',
		'Cordialement, votre ennemi de tout temps',
		"Très cordialement, votre pioupiouk d'amour",
		"Que le Schnibble te guide vers la lumière jusqu'à la fin des temps",
		"Dans l'attente d'une reponse...",
		'À bientôt',
		'@ +',
		'Bye, espèce de pochtron !',
		'Bisous',
		'Affectueusement',
		'Amicalement',
		'Peace...'
	);

	// Timbres du jeu
	private $timbres = array(
		'0.gif'  => array('titre' => 'Classique rouge',    'prix' => 0.1),
		'7.gif'  => array('titre' => 'Classique vert',     'prix' => 0.1),
		'11.gif' => array('titre' => 'Stylo',              'prix' => 0.1),
		'1.gif'  => array('titre' => 'Rimel',              'prix' => 0.2),
		'13.gif' => array('titre' => 'Fleur',              'prix' => 0.1),
		'12.gif' => array('titre' => 'Frankensteimouth',   'prix' => 0.2),
		'10.gif' => array('titre' => 'Star',               'prix' => 0.2),
		'2.gif'  => array('titre' => 'Bain de soleil',     'prix' => 0.3),
		'3.gif'  => array('titre' => 'Einsteimouth',       'prix' => 0.3),
		'4.gif'  => array('titre' => 'Draculoplastoc',     'prix' => 0.3),
		'5.gif'  => array('titre' => 'Rock',               'prix' => 0.3),
		'6.gif'  => array('titre' => 'J.F Sébastien',      'prix' => 0.3),
		'9.gif'  => array('titre' => 'Schnibble',          'prix' => 0.3),
		'14.gif' => array('titre' => 'Bible du Schnibble', 'prix' => 0.3),
		'8.gif'  => array('titre' => 'Anniversouille',     'prix' => 0.5),
		'15.gif' => array('titre' => 'Modération',		   'prix' => 0.0),
	);
	
	public function __construct()
	{
		$this->CI =& get_instance();
	}
	
	public function timbres($i = null)
	{
		if( ! $this->CI->bouzouk->is_moderateur())
			unset($this->timbres['15.gif']);
		
		if (isset($i))
		{
			// Timbre par index
			if (entier_naturel($i))
			{
				if ($i >= count($this->timbres))
					$i = 0;

				$timbres = array_keys($this->timbres);
				return $timbres[$i];
			}

			// Timbre par varchar
			else
			{
				return $this->timbres[$i];
			}
		}

		return $this->timbres;
	}

	public function intros($i = null)
	{
		if (isset($i) && $i < count($this->intros))
			return $this->intros[$i];

		return $this->intros;
	}

	public function politesses($i = null)
	{
		if (isset($i) && $i < count($this->politesses))
			return $this->politesses[$i];

		return $this->politesses;
	}

	public function envoyer_missive($expediteur_id, $destinataire_id, $sujet, $message, $datetime = false, $timbre = false)
	{
		if ($datetime === false)
			$datetime = bdd_datetime();

		if ($timbre === false)
			$timbre = $this->timbres(0);

		$data_missives = array(
			'expediteur_id'   => $expediteur_id,
			'destinataire_id' => $destinataire_id,
			'date_envoi'      => $datetime,
			'timbre'          => $timbre,
			'objet'           => $sujet,
			'message'         => $message
		);
		$this->CI->db->insert('missives', $data_missives);
	}
}