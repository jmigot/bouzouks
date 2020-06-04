<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions de vérification des emails
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : février 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Lib_email
{
	private $CI;

	// Mettre les hosts en minuscule pour les comparaisons (-1 pour les domaines cachés)
	private	$hosts_autorises = array(
		'hotmail.fr',
		'gmail.com',
		'yahoo.fr',
		'yahoo.ca',
		'laposte.net',
		'hotmail.com',
		'wanadoo.fr',
		'free.fr',
		'msn.com',
		'live.fr',
		'live.com',
		'orange.fr',
		'hotmail.be',
		'hotmail.ca',
		'neuf.fr',
		'voila.fr',
		'sfr.fr',
		'legtux.org',
		'outlook.com',
		'alicemail.fr',
		'bbox.fr',
		'gmx.com',
		'gmx.fr',
		-1 => 'foixet.com', // J'ai que ça comme mail :(
	);

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function emails_autorises()
	{
		$hosts = '';
		
		// On enlève les domaines cachés
		unset($this->hosts_autorises[-1]);
		
		// On ajoute tous les hosts
		foreach ($this->hosts_autorises as $host)
			$hosts .= $host.', ';

		// On enlève la dernière virgule
		$hosts = mb_substr($hosts, 0, mb_strlen($hosts) - 2);

		return $hosts;
	}

	public function email_existe($email)
	{
		if(!$email){
			return FALSE;
		}
		
		$email = $this->nettoyer_email($email);

		$existe_site = $this->CI->db->where('email', mb_strtolower($email))
									->count_all_results('joueurs');

		$existe_tobozon = $this->CI->db->where('email', mb_strtolower($email))
									   ->count_all_results('tobozon_users');

		$existe_site_fb = $this->CI->db->where('fb_mail', mb_strtolower($email))
									   ->count_all_results('joueurs');

		// La somme des trois sera > 0 si l'un des trois est > 0
		return ($existe_site + $existe_tobozon + $existe_site_fb) > 0;
	}

	public function email_valide($email)
	{
		$email = $this->nettoyer_email($email);

		// Liste blanche des hosts autorisés
		$tmp = explode('@', $email);

		return in_array($tmp[1], $this->hosts_autorises);
	}

	public function nettoyer_email($email)
	{
		$tmp = explode('@', $email);

		// On met le host en minuscule
		$tmp[1] = mb_strtolower($tmp[1]);

		// Si le host est de gmail
		if (strstr($tmp[1], 'gmail'))
		{
			// On enlève tous les points (.) du username
			$tmp[0] = mb_strtolower(str_replace('.', '', $tmp[0]));

			// On enlève les + suivis de quelque chose (alias gmail)
			if (strstr($tmp[0], '+'))
			{
				$tmp_plus = explode('+', $tmp[0]);
				$tmp[0] = $tmp_plus[0];
			}
		}

		return implode($tmp, '@');
	}
}
