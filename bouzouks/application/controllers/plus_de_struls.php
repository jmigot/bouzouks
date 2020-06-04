<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions pour le script Allopass
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Plus_de_struls extends MY_Controller
{
	private $allopass;
	private $starpass;

	public function __construct()
	{
		parent::__construct();

		$this->allopass = array(
			'appel' => array('auth' => 'xxx', 'prix' => 1.80, 'struls' => $this->bouzouk->config('plus_de_struls_gain_appel')),
			'sms'   => array('auth' => 'xxx', 'prix' => 1.50, 'struls' => $this->bouzouk->config('plus_de_struls_gain_sms1')),
			'sms2'  => array('auth' => 'xxx', 'prix' => 3.00, 'struls' => $this->bouzouk->config('plus_de_struls_gain_sms2'))
		);
	}

	public function index()
	{
		$vars = array(
			'allopass' => $this->allopass,
		);
		return $this->layout->view('plus_de_struls/index', $vars);
	}

	public function valider_appel()
	{
		return $this->valider('appel');
	}

	public function valider_sms()
	{
		return $this->valider('sms');
	}

	public function valider_sms2()
	{
		return $this->valider('sms2');
	}

	private function valider($type)
	{
		// On récupère le code d'accès entré par le joueur
		$RECALL = isset($_GET['RECALL']) ? $_GET['RECALL'] : '';

		// Si il est vide, erreur
		if (trim($RECALL) == '')
			return $this->erreur();

		// Code et identifiant à envoyer
		$RECALL  = urlencode($RECALL);
		$AUTH    = urlencode($this->allopass[$type]['auth']);
		$reponse = @file('http://payment.allopass.com/api/checkcode.apu?code='.$RECALL.'&auth='.$AUTH);

		// Si le code est invalide, erreur
		if (substr($reponse[0], 0, 2) != 'OK')
			return $this->erreur();

		// On valide
		return $this->confirmation($RECALL, 'allopass', $this->allopass[$type]['prix'], $this->allopass[$type]['struls']);
	}

	private function confirmation($code, $type, $prix, $struls)
	{
		// On vérifie que le code n'a pas déjà été validé cette dernière heure (les starpass ont 1 minute de latence)
		$deja_utilise = $this->db->where('code', $code)
								 ->where('date > (NOW() - INTERVAL 1 HOUR)')
								 ->where('type', $type)
								 ->count_all_results('plus_de_struls');

		if ($deja_utilise > 0)
			return $this->erreur();

		// On ajoute les struls au joueur
		$this->bouzouk->ajouter_struls($struls);

		// On enregistre la transaction
		$data_plus_de_struls = array(
			'joueur_id' => $this->session->userdata('id'),
			'code'      => $code,
			'type'      => $type,
			'montant'   => $prix,
			'struls'    => $struls,
			'date'      => bdd_datetime()
		);
		$this->db->insert('plus_de_struls', $data_plus_de_struls);

		// On ajoute à l'historique
		$this->bouzouk->historique(140, null, array(struls($struls)));

		// On affiche un message de confirmation
		$vars = array(
			'titre_layout' => 'Plus de struls !',
			'titre'        => 'Code validé',
			'image_url'    => 'struls.gif',
			'message'      => 'Tu as bien reçu '.struls($struls).' :)<br><a href="'.site_url('plus_de_struls').'">Retour</a>'
		);
		return $this->layout->view('blocage', $vars);
	}

	public function erreur()
	{
		// On affiche un message d'erreur
		$vars = array(
			'titre_layout' => 'Plus de struls !',
			'titre'        => 'Erreur',
			'image_url'    => 'echec.png',
			'message'      => 'Ton code est invalide...:(<br><a href="'.site_url('plus_de_struls').'">Retour</a>'
		);
		return $this->layout->view('blocage', $vars);
	}
}
