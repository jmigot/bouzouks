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
class Item {
	
	public $id;
	public $type;
	public $cat;
	public $nom;
	public $prix;
	public $monnaie;
	public $img;
	public $tx;
	public $ty;
	public $decx;
	public $decy;
	public $titre;
	public $bulle;
	public $zone;
	public $infranchissable;
	public $nature;
	public $support;
	public $dropable;
	public $water_dropable;
	public $hauteur;
	public $auth_level;
	
	public function __construct($data)
	{
		foreach ($data as $attribut=>$valeur) {
			$this->$attribut = $valeur;
		}
	}

}