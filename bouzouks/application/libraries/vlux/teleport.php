<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Classe définissant les téléporteurs au sein de la map Vlurx 3D
 * 
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 * 
 * @author       Hikingyo <hikingyo@outlook.com>
 * @copyright    Team Bouzouk 2015 (C) 2015 Hikingyo - Tous droits réservés
 * @package      bouzouks\vlux\libs
 * @see          /application/libraries/jeu/vlux_factory
 * 
 */

class Teleport {

	const Etat_Lock = 0;
	const Etat_Blacklist = 1;
	const Etat_Whitelist = 2;
	const Etat_Open = 3;

	const Mode_bloquee = -1;
	const Mode_Sens_Unique = 0;
	const Mode_Double_Sens = 1;
	const Mode_Restreint = 2;
	const Mode_Invisible = 3;

	public $id;
	public $item_id;
	public $type;
	public $map_id;
	public $x;
	public $y;
	public $etat;
	public $mode;
	public $destination;
	public $whitelist;
	public $blacklist;

	public function __construct($teleport=NULL){
		if($teleport != NULL){
			foreach($teleport as $a => $v){
				$this->$a = $v;
			}
		}
	}

}