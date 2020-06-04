<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions de gestion de la mairie
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : décembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Lib_entreprise
{
	private $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function fonds_suffisants($entreprise_id, $montant)
	{
		$query = $this->CI->db->select('struls')
						      ->from('entreprises')
						      ->where('id', $entreprise_id)
						      ->get();

		if ($query->num_rows() > 0)
		{
			$entreprise = $query->row();
			return $entreprise->struls - $montant > $this->CI->bouzouk->config('entreprises_limite_faillite');
		}

		return false;
	}
	
	public function don_possible($montant)
	{
		// On va chercher le total des dons
		$query = $this->CI->db->select('SUM(montant) AS total_dons')
							  ->from('donations')
							  ->where('type', Bouzouk::Donation_Entreprise)
							  ->where('joueur_id', $this->CI->session->userdata('entreprise_id'))
							  ->where('date >= (NOW() - INTERVAL '.$this->CI->bouzouk->config('entreprises_intervalle_max_injection').' HOUR)')
							  ->get();
		$entreprise = $query->row();

		// On regarde si le don ne dépasse pas la limite
		return array(
			'total_dons'      => $entreprise->total_dons,
			'max_injection'   => $this->CI->bouzouk->config('entreprises_max_injection'),
			'limite_atteinte' => $entreprise->total_dons + $montant > $this->CI->bouzouk->config('entreprises_max_injection'),
			'intervalle'      => $this->CI->bouzouk->config('entreprises_intervalle_max_injection')
		);
	}
	
	private function supprimer_entreprise($entreprise_id)
	{
		// Cagnotte noire
		$query = $this->CI->db->select('struls')
							  ->from('entreprises')
							  ->where('id', $entreprise_id)
							  ->get();
		$entreprise = $query->row();

		// On débauche tous les employés
		$this->CI->db->where('entreprise_id', $entreprise_id)
					 ->delete('employes');

		// On vide les petites annonces
		$this->CI->db->where('entreprise_id', $entreprise_id)
					 ->delete('petites_annonces');

		// On vide les messages du tchat
		$this->CI->db->where('tchat_id', $entreprise_id)
					 ->delete('tchats_entreprises');

		// On vide l'historique
		$this->CI->db->where('entreprise_id', $entreprise_id)
					 ->delete('historique_entreprises');

		// On détruit tous les syndicats
		$this->CI->load->library('lib_clans');

		$query = $this->CI->db->select('id')
							  ->from('clans')
							  ->where('entreprise_id', $entreprise_id)
							  ->where('type', Bouzouk::Clans_TypeSyndicat)
							  ->get();
		$clans = $query->result();

		foreach ($clans as $clan)
			$this->CI->lib_clans->supprimer_clan($clan->id);

		// On détruit l'entreprise
		$this->CI->db->where('id', $entreprise_id)
					 ->delete('entreprises');
	}

	public function demission($entreprise_id)
	{
		$this->CI->load->library('lib_missive');
		
		// On va chercher les infos de l'entreprise
		$query = $this->CI->db->select('e.nom, j.id AS chef_id, j.pseudo AS chef_pseudo')
							  ->from('entreprises e')
							  ->join('joueurs j', 'j.id = e.chef_id')
							  ->where('e.id', $entreprise_id)
							  ->get();
		$entreprise = $query->row();

		// On récupère les chefs de syndicats
		$chefs_syndicats_ids = array(0);
		$query = $this->CI->db->select('chef_id')
							  ->from('clans')
							  ->where('type', Bouzouk::Clans_TypeSyndicat)
							  ->where('entreprise_id', $this->CI->session->userdata('entreprise_id'))
							  ->get();
		$chefs_syndicats = $query->result();

		foreach ($chefs_syndicats as $chef_syndicat)
			$chefs_syndicats_ids[] = $chef_syndicat->chef_id;

		// On va chercher l'employé le plus ancien
		$query = $this->CI->db->select('e.joueur_id AS id, j.pseudo')
							  ->from('employes e')
							  ->join('joueurs j', 'j.id = e.joueur_id')
							  ->where('e.entreprise_id', $entreprise_id)
							  ->where('j.statut', Bouzouk::Joueur_Actif)
							  ->where_not_in('j.id', $chefs_syndicats_ids)
							  ->order_by('e.anciennete', 'desc')
							  ->order_by('j.experience', 'desc')
							  ->limit(1)
							  ->get();

		$data_missives = array();
		$date          = bdd_datetime();
		$timbre        = $this->CI->lib_missive->timbres(0);

		// Si aucun employé n'est apte à remplacer le patron
		if ($query->num_rows() == 0)
		{
			// On récupère la liste des employés
			$query = $this->CI->db->select('j.id, j.pseudo')
								  ->from('employes e')
								  ->join('joueurs j', 'j.id = e.joueur_id')
								  ->where('e.entreprise_id', $entreprise_id)
								  ->get();

			if ($query->num_rows() > 0)
			{
				$employes = $query->result();

				// On envoie une missive à tous les employés licenciés
				foreach ($employes as $employe)
				{
					// Message
					$message  = "	Bonjour $employe->pseudo\n\n";
					$message .= "Nous t'informons que ".profil($entreprise->chef_id, $entreprise->chef_pseudo).", le patron de ton entreprise <span class='pourpre'>$entreprise->nom</span>, vient de démissionner.\n";
					$message .= "Vu qu'il n'y a aucun employé apte à le remplacer, l'entreprise à été détruite : tu te retrouves donc au chômage.\n\n";
					$message .= "	Amicalement, le ministère des démissions de Vlurxtrznbnaxl.";

					$data_missives[] = array(
						'expediteur_id'   => Bouzouk::Robot_Emploi,
						'destinataire_id' => $employe->id,
						'date_envoi'      => $date,
						'timbre'          => $timbre,
						'objet'           => 'Démission de ton patron',
						'message'         => $message
					);

					// On ajoute à l'historique de l'employé
					$this->CI->bouzouk->historique(179, 180, array(profil($entreprise->chef_id, $entreprise->chef_pseudo), $entreprise->nom), $employe->id);
				}
				$this->CI->db->insert_batch('missives', $data_missives);

				// La session doit être mise à jour
				$this->CI->bouzouk->augmente_version_session($employe->id);
			}

			// On supprime l'entreprise
			$this->supprimer_entreprise($entreprise_id);

			// On ajoute à l'historique du patron
			$this->CI->bouzouk->historique(181, 21, array($entreprise->nom, $this->CI->bouzouk->config('entreprises_perte_xp_demission')), $entreprise->chef_id);
		}

		// Si un employé peut remplacer le patron
		else
		{
			// On définit l'employé le plus ancien comme nouveau patron
			$employe_repreneur = $query->row();

			$data_entreprises = array(
				'chef_id' => $employe_repreneur->id
			);
			$this->CI->db->where('id', $entreprise_id)
						 ->update('entreprises', $data_entreprises);

			// On débauche l'employé de l'entreprise car il est toujours employé (et patron en même temps)
			$this->CI->db->where('joueur_id', $employe_repreneur->id)
						 ->delete('employes');

			// On lui fait quitter le syndicat
			$this->CI->load->library('lib_clans');
			$this->CI->lib_clans->quitter_syndicat($employe_repreneur->id);

			// Message
			$message  = "	Bonjour $employe_repreneur->pseudo\n\n";
			$message .= "Nous t'informons que ".profil($entreprise->chef_id, $entreprise->chef_pseudo).", le patron de ton entreprise <span class='pourpre'>$entreprise->nom</span>, vient de démissionner.\n";
			$message .= "Comme tu es l'employé le plus ancien de l'entreprise, tu as le devoir de prendre les commandes de l'entreprise\n";
			$message .= "En clair, c'est toi le patron désormais :)\n\n";
			$message .= "Tu as maintenant accès à ton nouveau bureau. N'hésite pas à lire la FAQ à ce propos !\n\n";
			$message .= "	Amicalement, le ministère du travail de Vlurxtrznbnaxl.";

			// On envoie une missive au nouveau patron
			$data_missives[] = array(
				'expediteur_id'   => Bouzouk::Robot_Emploi,
				'destinataire_id' => $employe_repreneur->id,
				'date_envoi'      => $date,
				'timbre'          => $timbre,
				'objet'           => 'Tu es patron :)',
				'message'         => $message
			);

			// On ajoute à l'historique du repreneur
			$this->CI->bouzouk->historique(182, null, array($entreprise->nom, profil($entreprise->chef_id, $entreprise->chef_pseudo)), $employe_repreneur->id);

			// On ajoute à l'historique du patron
			$this->CI->bouzouk->historique(183, 21, array($entreprise->nom, $this->CI->bouzouk->config('entreprises_perte_xp_demission'), profil($employe_repreneur->id, $employe_repreneur->pseudo)), $entreprise->chef_id);

			// La session du repreneur doit être mise à jour
			$this->CI->bouzouk->augmente_version_session($employe_repreneur->id);

			// On récupère la liste des employés
			$query = $this->CI->db->select('j.id, j.pseudo')
								  ->from('employes e')
								  ->join('joueurs j', 'j.id = e.joueur_id')
								  ->where('e.joueur_id !=', $employe_repreneur->id)
								  ->where('e.entreprise_id', $entreprise_id)
								  ->get();

			if ($query->num_rows() > 0)
			{
				$employes = $query->result();

				// On envoie une missive à tous les autres employés
				foreach ($employes as $employe)
				{
					$message  = "	Bonjour $employe->pseudo\n\n";
					$message .= "Nous t'informons que ton patron ".profil($entreprise->chef_id, $entreprise->chef_pseudo)." vient de démissionner.\n";
					$message .= "Ton nouveau patron est donc ".profil($employe_repreneur->id, $employe_repreneur->pseudo).".\n\n";
					$message .= "	Amicalement, le ministère du travail de Vlurxtrznbnaxl.";

					$data_missives[] = array(
						'expediteur_id'   => Bouzouk::Robot_Emploi,
						'destinataire_id' => $employe->id,
						'date_envoi'      => $date,
						'timbre'          => $timbre,
						'objet'           => 'Changement de patron',
						'message'         => $message
					);

					// On ajoute à l'historique du repreneur
					$this->CI->bouzouk->historique(184, null, array(profil($entreprise->chef_id, $entreprise->chef_pseudo), profil($employe_repreneur->id, $employe_repreneur->pseudo)), $employe->id);
				}
			}

			$this->CI->db->insert_batch('missives', $data_missives);
		}

		// On ajoute le patron aux faillites pour l'empêcher de recreer une entreprise tout de suite
		$data_faillites = array(
			'joueur_id'     => $entreprise->chef_id,
			'date_faillite' => $date
		);
		$this->CI->db->insert('faillites', $data_faillites);

		// On retire de l'expérience au patron
		$this->CI->bouzouk->retirer_experience($this->CI->bouzouk->config('entreprises_perte_xp_demission'), $entreprise->chef_id);

		// La session du patron doit être mise à jour
		$this->CI->bouzouk->augmente_version_session($entreprise->chef_id);
	}

	public function faillite($entreprise_id)
	{
		$this->CI->load->library('lib_missive');
		$datetime = bdd_datetime();

		// On va chercher les infos de l'entreprise
		$query = $this->CI->db->select('e.id, e.nom, e.chef_id, e.struls, j.pseudo')
							  ->from('entreprises e')
							  ->join('joueurs j', 'j.id = e.chef_id')
							  ->where('e.id', $entreprise_id)
							  ->get();
		$entreprise = $query->row();

		// On note la faillite du patron
		$data_faillites = array(
			'joueur_id'     => $entreprise->chef_id,
			'date_faillite' => $datetime
		);
		$this->CI->db->insert('faillites', $data_faillites);

		// On enlève de l'expérience au patron
		$perte_xp = $this->CI->bouzouk->config('entreprises_faillites_perte_xp');
		$this->CI->bouzouk->retirer_experience($perte_xp, $entreprise->chef_id);

		$data_missives = array();
		$timbre        = $this->CI->lib_missive->timbres(0);

		// On envoie une missive au patron concernant la faillite
		$message  = "	Bonjour $entreprise->pseudo\n\n";
		$message .= "Avec ".struls($entreprise->struls)." dans les caisses, ton entreprise <span class='pourpre'>$entreprise->nom</span> s'est retrouvée dernièrement en situation trés précaire.\n\n";
		$message .= "Or, le nouveau code pénal est trés strict à ce sujet :\n";
		$message .= "<i>Toute entreprise qui n'a plus de flouse et qui chouchoute ses employés fait du préférentisme. Le patron mérite des baffes.</i>\n";
		$message .= "Mais je ne peux pas te mettre de baffes, ce serait trop gentil.\n";
		$message .= "Donc ton entreprise a déposé le bilan et tes bouzouks se retrouvent à la rue. Toi de même.\n\n";
		$message .= "Quand on me cherche, on me trouve. Na !\n\n";
		$message .= "	Le percepteur de Vlurxtrznbnaxl.";

		$data_missives[] = array(
			'expediteur_id'   => Bouzouk::Robot_Percepteur,
			'destinataire_id' => $entreprise->chef_id,
			'date_envoi'      => $datetime,
			'timbre'          => $timbre,
			'objet'           => 'Concernant ton entreprise...',
			'message'         => $message
		);

		// On calcule la facture à envoyer au patron
		$perte = min($this->CI->bouzouk->config('entreprises_max_facture_faillite'), abs($entreprise->struls - $this->CI->bouzouk->config('entreprises_limite_faillite')));

		if ($perte > 0)
		{
			// On prépare une facture
			$data_factures = array(
				'joueur_id'  => $entreprise->chef_id,
				'titre'      => 'Facture entreprise',
				'montant'    => $perte,
				'majoration' => 0,
				'date'       => $datetime
			);
			$this->CI->db->insert('factures', $data_factures);

			$query = $this->CI->db->select('j.id, j.pseudo')
								  ->from('mairie m')
								  ->join('joueurs j', 'j.id = m.maire_id')
								  ->get();
			$maire = $query->row();

			// On prépare une missive concernant la facture
			$message  = "	Bonjour $entreprise->pseudo\n\n";
			$message .= "Tu as coulé ton entreprise suite à une gestion déplorable et tu penses que c'est la ville qui va payer tes dettes ?! Nous t'envoyons cette facture sur ordre de notre bon maire ".profil($maire->id, $maire->pseudo)." et de Miss Augine qui remarque que ton niveau en calcul ne s'est pas amélioré depuis le temps...\n\n";
			$message .= "Avec un bilan de ".struls($entreprise->struls).", c'est toi qui va devoir rembourser la communauté pour cet argent que ton entreprise ne possédait pas ! Voilà ce qui arrive quand on veut être trop généreux envers ses employés...\n\n";
			$message .= "Tu dois donc verser à la mairie la modique somme de <span class='pourpre'>".struls($perte)."</span>.\n";
			$message .= "Tu as <span class='pourpre'>".pluriel($this->CI->bouzouk->config('factures_delai_majoration'), 'jour')."</span> pour la payer, passé ce délai, tu ne seras plus accepté dans aucun service ni aucune boutique de la ville de Vlurxtrznbnaxl nécessitant de l'argent et tu devras travailler dur afin de rembourser ta dette majorée de <span class='pourpre'>".$this->CI->bouzouk->config('factures_pourcent_majoration')."%</span>.\n";
			$message .= "Ce serait dommage... :-)\n\n";
			$message .= "<a href='".site_url('factures')."'>Payer mes factures</a>\n\n";
			$message .= "	Amicalement, la mairie de Vlurxtrznbnaxl.";

			$data_missives[] = array(
				'expediteur_id'   => Bouzouk::Robot_Maire,
				'destinataire_id' => $entreprise->chef_id,
				'date_envoi'      => $datetime,
				'timbre'          => $timbre,
				'objet'           => 'Facture entreprise :)',
				'message'         => $message
			);
		}

		// On ajoute à l'historique du patron
		$this->CI->bouzouk->historique(185, null, array($entreprise->nom, $perte_xp), $entreprise->chef_id);

		// La session du patron doit être mise à jour
		$this->CI->bouzouk->augmente_version_session($entreprise->chef_id);

		// On récupère la liste des employés
		$query = $this->CI->db->select('j.id, j.pseudo')
							  ->from('employes e')
							  ->join('joueurs j', 'j.id = e.joueur_id')
							  ->where('e.entreprise_id', $entreprise->id)
							  ->get();
		$employes = $query->result();

		// On envoie une missive à tous les employés licenciés
		foreach ($employes as $employe)
		{
			// Message
			$message  = "	Bonjour $employe->pseudo\n\n";
			$message .= "Je suis au regret de t'annoncer le dépôt de bilan de l'entreprise <span class='pourpre'>$entreprise->nom</span> dans laquelle tu travaillais.\n";
			$message .= "Ton patron ".profil($entreprise->chef_id, $entreprise->pseudo)." a trop mal géré son affaire...Mais il n'a que ce qu'il mérite.\n\n";
			$message .= "Hélas, cette affaire a donc entrainé le renvoi de tous les employés.\n";
			$message .= "Tu en fais partie : tu te retrouves donc au chômage.\n\n";
			$message .= "Condoléances.\n\n";
			$message .= "	Le percepteur de Vlurxtrznbnaxl.";

			$data_missives[] = array(
				'expediteur_id'   => Bouzouk::Robot_Percepteur,
				'destinataire_id' => $employe->id,
				'date_envoi'      => $datetime,
				'timbre'          => $timbre,
				'objet'           => 'Concernant ton job...',
				'message'         => $message
			);

			// On ajoute à l'historique de l'employé
			$this->CI->bouzouk->historique(186, null, array(profil($entreprise->chef_id, $entreprise->pseudo)), $employe->id);

			// La session de l'employé doit être mise à jour
			$this->CI->bouzouk->augmente_version_session($employe->id);
		}

		// On envoie toutes les missives
		$this->CI->db->insert_batch('missives', $data_missives);

		// On supprime l'entreprise du jeu
		$this->supprimer_entreprise($entreprise->id);
	}

	public function liberer_annonces($joueur_id)
	{
		// On supprime l'annonce du chômeur
		$this->CI->db->where('joueur_id', $joueur_id)
					 ->delete('chomeurs');

		// On supprime toutes les propositions des autres entreprises
		$this->CI->db->where('type', Bouzouk::PetitesAnnonces_Chomeur)
					 ->where('joueur_id', $joueur_id)
					 ->delete('petites_annonces');
			 
		// On remet en ligne toutes les autres annonces acceptées par ce bouzouk
		$this->CI->db->set('joueur_id', null)
					 ->where('type', Bouzouk::PetitesAnnonces_Patron)
					 ->where('joueur_id', $joueur_id)
					 ->update('petites_annonces');
	}
}