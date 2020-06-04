<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions de gestion des caches
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : février 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Lib_cache
{
	const Config              = 'config';
	const ListeConnectes      = 'liste_connectes';
	const ListeConnectesIRC   = 'liste_connectes_irc';
	const ListeConnectesPlouk = 'liste_connectes_plouk';
	const NbJoueursActifs     = 'nb_joueurs_actifs';
	const NbPioupiouks        = 'nb_pioupiouks';
	const NettoyagePlouk      = 'nettoyage_plouk';
	const DernierePige		  = 'derniere_pige';
	const RobotActifs         = 'robots_actifs';
	const RobotsInactifs      = 'robots_inactifs';
	
	private $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function config($update = false)
	{
		if ($update || ! $this->fetch(self::Config))
		{
			$configs = array();
			
			// On lit la config en base de données
			$query = $this->CI->db->select('cle, valeur')
								  ->from('config')
								  ->get();

			foreach ($query->result() as $config)
			{
				$valeur = $config->valeur;

				if (is_numeric($valeur))
					$valeur = (int) $valeur;

				$configs[$config->cle] = $config->valeur;
			}

			$this->store(self::Config, $configs);
			return $configs;
		}

		return $this->fetch(self::Config);
	}
	
	public function nb_joueurs_actifs()
	{
		if ( ! $this->fetch(self::NbJoueursActifs))
		{
			$nb = $this->CI->db->where_in('statut', array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_Actif, Bouzouk::Joueur_Pause, Bouzouk::Joueur_Asile))
							   ->count_all_results('joueurs');
			$this->store(self::NbJoueursActifs, $nb, 3600);
			return $nb;
		}

		return $this->fetch(self::NbJoueursActifs);
	}
	
	public function derniere_pige()
	{
		if ( ! $this->fetch(self::DernierePige))
		{
			// On va chercher la dernière pige
			$query = $this->CI->db->select('p.id, p.auteur_id, j.pseudo, j.rang, p.texte, p.date, p.lien, p.en_ligne')
							  ->from('piges p')
							  ->join('joueurs j', 'j.id = p.auteur_id')
							  ->where('p.en_ligne', Bouzouk::Piges_Active)
							  ->order_by('p.date', 'desc')
							  ->limit(1)
							  ->get();
			
			$pige = $query->row();
			
			$this->store(self::DernierePige, $pige, 10);
			return $pige;
		}

		return $this->fetch(self::DernierePige);
	}

	public function nb_connectes()
	{
		return count($this->liste_connectes());
	}

	public function liste_connectes()
	{
		if ( ! $this->fetch(self::ListeConnectes))
		{
			$query = $this->CI->db->query('SELECT id, pseudo, rang '.
										  'FROM joueurs '.
										  'WHERE connecte > (NOW() - INTERVAL 1 MINUTE) '.
										  'ORDER BY IF(rang = '.Bouzouk::Rang_BetaTesteur.', '.Bouzouk::Rang_Aucun.', rang) desc, pseudo');
			$joueurs = $query->result();	
			$this->store(self::ListeConnectes, $joueurs, 60);
			return $joueurs;
		}
		
		return $this->fetch(self::ListeConnectes);
	}

	public function nb_connectes_tchat()
	{
		return count($this->liste_connectes_tchat());
	}

	public function nb_amis_connectes($joueur_id)
	{
		$liste_connectes = $this->liste_connectes();
		$ids = array($this->CI->session->userdata('id'));

		foreach ($liste_connectes as $connecte)
			$ids[] = $connecte->id;
		
		return $this->CI->db->where('a.joueur_id', $joueur_id)
        					->where_in('a.ami_id', $ids)
        					->where('a.etat', Bouzouk::Amis_Accepte)
        					->join('joueurs j', 'a.ami_id = j.id')
        					->count_all_results('amis a');
	}
	
	public function liste_connectes_tchat()
	{
		if ( ! $this->fetch(self::ListeConnectesIRC))
		{
			$context = stream_context_create(array(
				'http'=> array(
					'timeout' => 2, // secondes
    		)));

			$connectes_tchat = false;
			$connectes_tchat = @file_get_contents('http://www.powanet.org/powanet.php?a=222', false, $context);
			
			// En cas de panne de Powanet
			if ($connectes_tchat === false)
			{
				$this->store(self::ListeConnectesIRC, array(), 30);
				return array();
			}

			else
			{
				$connectes_tchat = explode('<br>', $connectes_tchat);
				$this->store(self::ListeConnectesIRC, $connectes_tchat, 30);
				return $connectes_tchat;
			}
		}
		
		return $this->fetch(self::ListeConnectesIRC);
	}

	public function liste_connectes_plouk()
	{
		if ( ! $this->fetch(self::ListeConnectesPlouk))
		{
			// On supprime les connectés trop vieux
			$this->CI->db->where('derniere_visite < (NOW() - INTERVAL 10 SECOND)')
						 ->where('partie_id', 0)
						 ->delete('plouk_connectes');
						 
			// On récupère les connectés récents
			$query = $this->CI->db->select('j.id, j.pseudo, j.rang')
								  ->from('plouk_connectes pc')
								  ->join('joueurs j', 'j.id = pc.joueur_id')
								  ->where('pc.partie_id', 0)
								  ->order_by('j.pseudo')
								  ->get();
			$joueurs = $query->result();

			$this->store(self::ListeConnectesPlouk, $joueurs, 10);
			return $joueurs;
		}

		return $this->fetch(self::ListeConnectesPlouk);
	}

	public function nb_pioupiouks()
	{
		if ( ! $this->fetch(self::NbPioupiouks))
		{
			$nb_pioupiouks = 0;

			// On compte le nombre en magasin
			$query = $this->CI->db->select('quantite')
								  ->from('magasins')
								  ->where('objet_id', 5)
								  ->get();
			$nb_pioupiouks += $query->row()->quantite;

			// On compte le nombre dans les maisons
			$query = $this->CI->db->select('SUM(quantite) AS quantites')
								  ->from('maisons')
								  ->where('objet_id', 5)
								  ->get();
			$nb_pioupiouks += $query->row()->quantites;
			
			// On compte le nombre au marché noir
			$query = $this->CI->db->select('SUM(quantite) AS quantites')
								  ->from('marche_noir')
								  ->where('objet_id', 5)
								  ->get();
			$nb_pioupiouks += $query->row()->quantites;

			$this->store(self::NbPioupiouks, $nb_pioupiouks, 30);
			return $nb_pioupiouks;
		}

		return $this->fetch(self::NbPioupiouks);
	}

	public function nettoyage_plouk()
	{
		if ( ! $this->fetch(self::NettoyagePlouk))
		{
			$this->store(self::NettoyagePlouk, 'false', 10);
			return true;
		}

		return false;
	}

	public function robots_actifs($update = false)
	{
		if ($update || ! $this->fetch(self::RobotActifs))
		{
			// On relit les robots actifs
			$robots = $this->CI->bouzouk->config('jeu_robots_actifs');
			$this->store(self::RobotActifs, $robots);
			return $robots;
		}

		return $this->fetch(self::RobotActifs);
	}

	public function robots_inactifs($update = false)
	{
		if ($update || ! $this->fetch(self::RobotsInactifs))
		{
			// On relit les robots inactifs
			$robots = $this->CI->bouzouk->config('jeu_robots_inactifs');
			$this->store(self::RobotsInactifs, $robots);
			return $robots;
		}

		return $this->fetch(self::RobotsInactifs);
	}

	public function fetch($cle)
	{
		if ( ! function_exists('apc_fetch'))
			return false;

		return apc_fetch($cle);
	}

	public function store($cle, $contenu, $timeout = 0)
	{
		if (function_exists('apc_store'))
			apc_store($cle, $contenu, $timeout);
	}
}