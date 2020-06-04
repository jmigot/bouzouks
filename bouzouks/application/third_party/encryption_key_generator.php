<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet	   : Bouzouks
 * Description : générateur de clé pour CodeIgniter
 *
 * Auteur	   : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date		   : décembre 2012
 *
 * Copyright (C) 2012-2014 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

function encryption_key_generator()
{
	mt_srand();

	$key = '';
	$alphabet = 'azertyuiopqsdfghjklmwxcvbn0123456789AZERTYUIOPQSDFGHJKLMWXCVBN';

	for ($i=0; $i<32; $i++)
	{
		$random_index = mt_rand(0, mb_strlen($alphabet) - 1);
		$key .= $alphabet[$random_index];
	}

	return $key;
}
