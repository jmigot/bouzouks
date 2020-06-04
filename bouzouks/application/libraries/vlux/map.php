<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : définition des mapsVlux 3d
 *
 * Auteur      : Hikingyo
 * Date        : septembre 2014
 *
 * Copyright (C) 2014 Hikingyo - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */
class Map {

	public $id;
	public $nom;
	public $type;
	public $prix;
	public $monnaie;
	public $proprio_id;
	public $proprio_pseudo;
	public $size;
	public $tuiles;
	public $zone;
	public $etat;
	public $decor =array();
	public $batiments = array();
	public $auth_creator = array();
	public $statut_vente;
	public $changement_nom = 0;

	public function __construct($data=NULL)
	{
		if($data != NULL ){
			foreach ($data as $attribut=>$valeur) {
			$this->$attribut = $valeur;
			}
		}
	}

}
	