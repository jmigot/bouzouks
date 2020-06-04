<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Projet      : Bouzouks
 * Description : Librairie de gestion et de controls du tchat de map
 *
 * Auteur      : Hikingyo
 * Date        : Juillet 2015
 * Revision    : #############
 *
 * Copyright (C) 2015 Hikingyo - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */


class Tchat_factory {

const PSEUDO_INFO = '<span class="orange"><b>[Info]</b></span> : ';
const PSEUDO_BOT = '<span class="noir"><b>Robby</b></span> : ';

const MSG_JOUEUR_INCONNU = "Ce bouzouk n'existe pas ou alors il est trop loin de moi ...";

private $CI;
private $manager;
private $pattern_lien_interne;
private $commandes = array(
	'^(?P<commande>\\/me) ',
	'^(?P<commande>\\/grossier)( (?P<victime>[a-zA-Z0-9_-]+)?)?$',
	'^(?P<commande>\\/signaler)( (?P<message>[a-zA-Z0-9 _äàçéèêëïöôù-]{15,140}))?',
	'^(?P<commande>\\/ilovetweedy)$',
	'^(?P<commande>\\/fortune)( (?P<pseudo>[a-zA-Z0-9_-]{3,25})?)?', // /fortune <pseudo/entreprise>
	'^(?P<commande>\\/invite)( (?P<pseudo>[a-zA-Z0-9_-]{3,12})?)?', // /invite <pseudo>
	'^(?P<commande>\\/dispo)( (?P<pseudo>[a-zA-Z0-9_-]{3,12})?)?', // /dispo <pseudo>
	'^(?P<commande>\\/tp maison)$', // /tp maison : permet à un joueur de retourner chez lui.
	// commande modé
	'^(?P<commande>\\/chut)( (?P<pseudo>[a-zA-Z0-9_-]{3,12})?)?( (?P<duree>[1-9][0-9]*)?)?', // /chut <pseudo> [<durée> = 5min]
	'^(?P<commande>\\/dechut)( (?P<pseudo>[a-zA-Z0-9_-]{3,12})?)?', // /dechut <pseudo>
	// commandes admin
	'^(?P<commande>\\/invisible) (on|off)$',
	'^(?P<commande>\\/asile) (\\w{3,12})( (?P<duree>[1-9][0-9]*)?)?', // /asile <pseudo> [<durée>= 24h]
	'^(?P<commande>\\/tpto)( (?P<pseudo>\\w{3,12}))?', // /tpto <pseudo> -> se déplacer sur la map et la case du joueur
	'^(?P<commande>\\/tp)(\\w{3,12}) (\\w*)' // tp <pseudo> <destination> -> déplacer le joueur sur la map <destination>
	);

private $colors = array(
	'gold', 'lightseagreen', 'lightskyblue', 'magenta', 'seagreen', 'mediumorchid', 'rosybrown', 'lightslategray', 'deepskyblue', 'yellowgreen', 'tan', 'lightsteelblue'
	);

	public function __construct(){
		$this->CI =& get_instance();
		$this->manager = $this->get_manager();
		$this->pattern_lien_interne  = preg_quote(site_url('tobozon')); 
	}

	private function get_manager(){
		$this->CI->load->model('tchat_manager');
		return $this->CI->tchat_manager;
	}

	public function parse_message($joueur_id, $joueur_pseudo, $joueur_rang,  $message_content, $chan_id){
		// si une commande  est employée
		foreach ($this->commandes as $key => $value) {
			if(preg_match('#'.$value.'#i', $message_content, $matches)){
				return $this->action($joueur_id, $joueur_pseudo, $joueur_rang, $message_content, $chan_id, $matches);
			}
		}
		// Message standard
		$date_envoi = bdd_datetime();
		$id_message = $this->manager->add_message($chan_id, $joueur_id, $message_content, $date_envoi);
		return $this->send('message', $joueur_id, $this->get_profil($joueur_id, $joueur_pseudo, $joueur_rang)." : ", $message_content, $chan_id, $id_message, $date_envoi);
	}

	private function prep_message($message){
		$this->CI->load->helpers('form');
		// Echappement html
		$message = form_prep($message);
		// On remplace les liens internes
		$message = preg_replace('#('.$this->pattern_lien_interne.'/[^\s]*)#i', '<a href="$1">$1</a>', $message);
		// On remplace les smileys par leurs images
		$message = remplace_smileys($message);
		return $message;
	}

	private function send($type, $id, $pseudo, $content, $chan_id, $id_message, $date_envoi){
		$message = array(
			'action'	=> $type,
			'chan_id'	=> $chan_id,
			'pseudo'	=> $pseudo,
			'content'	=> $this->prep_message($content),
			'date'      => tchat_datetime($date_envoi),
			'id'		=> $id_message
			);
		return $message;
	}

	private function action($id, $pseudo, $rang, $content, $chan_id,  $matches){
		$date_envoi = bdd_datetime();
		$commande = $matches['commande'];
		if($commande == '/me'){
			// Logs
			$id_message = $this->manager->add_message($chan_id, $id,  mb_substr($content, 3), $date_envoi);
			return $this->send('message', $id, '<b>* '.$this->get_profil($id, $pseudo, $rang).'</b>',  mb_substr($content, 3), $chan_id, $id_message, $date_envoi);
		}
		elseif($commande == '/grossier'){
			// Logs
			$id_message = $this->manager->add_message($chan_id, $id, $content, $date_envoi);
			return $this->send('message', $id, '<b>* '.$this->get_profil($id, $pseudo, $rang).'</b>', " fait un geste grossier à ".$matches['victime'], $chan_id, $id_message, $date_envoi);
		}
		elseif($commande == '/ilovetweedy'){
			// Logs
			$id_message = $this->manager->add_message($chan_id, $id, $content, $date_envoi);
			return $this->send('commande', $id, self::PSEUDO_BOT, "Continues comme çà $pseudo ! Tu es sur la bonne voie.", $chan_id, $id_message, $date_envoi);
		}
		elseif($commande == '/dispo'){
			return $this->get_dispo($id, $pseudo, $chan_id, $matches);
		}
		elseif($commande == '/fortune'){
			return $this->get_fortune($id, $pseudo, $rang, $chan_id, $matches);
		}
		elseif($commande == '/invite'){
			return $this->invite($id, $pseudo, $rang, $chan_id, $matches);
		}
		elseif($commande == '/signaler'){
			return $this->signaler($id, $pseudo, $chan_id, $matches);
		}
		elseif($commande == '/chut'){
			return $this->chut($id, $pseudo, $rang, $chan_id, $matches);
		}
		elseif($commande == '/dechut'){
			return $this->chut($id, $pseudo, $rang, $chan_id, $matches);
		}
		elseif($commande == '/tp maison'){
			return $this->tp_home($id);
		}
		elseif($commande == '/tpto'){
			return $this->tp_to($id, $pseudo, $rang, $chan_id, $matches);
		}
	}

	private function get_pseudo_color($pseudo, $rang){
		if ($this->CI->bouzouk->is_admin(Bouzouk::Rang_Admin, $rang))
			return 'rouge';

		else if ($this->CI->bouzouk->is_mdj($rang))
			return 'vert_fonce';

		// Spécial ouah
		else if ($this->CI->bouzouk->is_moderateur(null, $rang))
			return 'bleu';
			
		else if ($this->CI->bouzouk->is_journaliste(null, $rang))
			return 'pourpre';

		$hash_color = 7;
		$pl = mb_strlen($pseudo);
		for ($i=0; $i < $pl ; $i++) { 
			$char_code_at = hexdec(bin2hex(mb_substr($pseudo, $i, 1)));
			$hash_color = $char_code_at + ($hash_color<<5) - $hash_color;
		}
		$index_color = abs($hash_color%count($this->colors));
		return $this->colors[$index_color];
	}

	public function get_profil($id, $pseudo, $rang){
		return '<a href="'.site_url('communaute/profil/'.$id).'" title="Voir le profil de '.$pseudo.'"><span class="'.$this->get_pseudo_color($pseudo, $rang).'">'.$pseudo."</span></a>";
	}

	// Commande joueurs
	private function get_dispo($id, $pseudo, $chan_id, $matches){
		$date_envoi = bdd_datetime();
		// Si pas de pseudo spécifié
		if(!isset($matches['pseudo'])){
			return $this->send('commande', $id, self::PSEUDO_BOT, "T'as pas l'impression qu'il manque quelque chose $pseudo ?", $chan_id, 0, $date_envoi );
		}
		// Le joueur a taper son pseudo
		elseif(mb_strtolower($matches['pseudo']) == mb_strtolower($pseudo)){
			return $this->send('commande', $id, self::PSEUDO_BOT, "Alors toi, $pseudo, t'en tiens une bonne !", $chan_id, 0, $date_envoi);
		}
		// On va chercher le joueur demandé
		else{
			$destinataire = $this->manager->get_info($matches['pseudo']);
			// Le joueur demandé n'existe pas
			if(!$destinataire){
				return $this->send('commande', $id, self::PSEUDO_BOT, self::MSG_JOUEUR_INCONNU, $chan_id, 0, $date_envoi);
			}
			// Le joueur demandé n'est plus connecté
			elseif(strtotime($destinataire->connecte)< strtotime('-2 MINUTE')){
				return $this->send('commande', $id, self::PSEUDO_BOT, "Ce bouzouk n'est plus connecté, mets tes lunettes $pseudo ...", $chan_id, 0, $date_envoi);
			}
			elseif($destinataire->map_connecte == 0){
				return $this->send('commande', $id, self::PSEUDO_BOT, $matches['pseudo']." est bien là.", $chan_id, 0, $date_envoi);
			}
			elseif($destinataire->map_connecte == 2){
				return $this->send('commande', $id, self::PSEUDO_BOT, $matches['pseudo']." est en pleine création.", $chan_id, 0, $date_envoi);
			}
			else{
				return $this->send('commande', $id, self::PSEUDO_BOT, $matches['pseudo']." est quelque part dans \"$destinataire->map_nom\".", $chan_id, 0, $date_envoi);
			}
		}
	}

	private function invite($id, $pseudo, $rang, $chan_id, $matches){
		$date_envoi = bdd_datetime();
		// Pas de pseudo spécifié
		if(!isset($matches['pseudo'])){
			return $this->send('commande', $id, self::PSEUDO_BOT, "T'as pas l'impression qu'il manque quelque chose $pseudo ?", $chan_id, 0, $date_envoi );
		}
		// Le joueur s'invite lui-même
		elseif(mb_strtolower($matches['pseudo']) == mb_strtolower($pseudo)){
			return $this->send('commande', $id, self::PSEUDO_BOT, "T'aurais pas abusé du Raki $pseudo ?", $chan_id, 0, $date_envoi);
		}
		// On va chercher le joueur demandé
		else{
				$emetteur = $this->manager->get_info($pseudo);
				$destinataire = $this->manager->get_info($matches['pseudo']);
				// Le joueur demandé n'existe pas
				if(!$destinataire){
					return $this->send('commande', $id, self::PSEUDO_BOT, self::MSG_JOUEUR_INCONNU, $chan_id, 0, $date_envoi);
				}
				// Le joueur demandé n'est plus connecté
				elseif(strtotime($destinataire->connecte)< strtotime('-2 MINUTE')){
					return $this->send('commande', $id, self::PSEUDO_BOT, "Ce bouzouk n'est plus connecté, mets tes lunettes $pseudo ...", $chan_id, 0, $date_envoi);
				}
				// Le joueur est sur la même map
				elseif($emetteur->map_id == $destinataire->map_id && $destinataire->map_connecte==1){
					return $this->send('commande', $id, self::PSEUDO_BOT, "$pseudo, fais pas semblant de pas avoir remarqué ".$matches['pseudo'].'.', $chan_id, 0, $date_envoi);
				}
				// On invite le joueur ( notif + message)
				else{
					$this->CI->bouzouk->notification(255, array($this->get_profil($id, $pseudo, $rang), $emetteur->map_nom), $destinataire->id, Bouzouk::Historique_Notification);
					$this->CI->bouzouk->augmente_version_session($destinataire->id);
					return $this->send('commande', $id, self::PSEUDO_BOT, $matches['pseudo']." a été invité par $pseudo à venir sur la map.", $chan_id, 0, $date_envoi);
				}
		}
	}

	private function signaler($id, $pseudo, $chan_id, $matches){
		$date_envoi = bdd_datetime();
		if(!isset($matches['message'])){
			return $this->send('message', $id, self::PSEUDO_INFO, "Le message doit avoir entre 15 et 150 caractères.", $chan_id, 0, $date_envoi);
		}
		else{
			$this->manager->add_signalement($id, $matches['message'], $chan_id, $date_envoi);
			return $this->send('commande', $id, self::PSEUDO_INFO, "Le signalemnet a été pris en compte.", $chan_id, 0, $date_envoi);
		}
	}

	private function tp_home($id){
		// On récupère l'id du téléport de la résidence principale du joueur
		$this->CI->load->library('vlux/vlux_param_joueur');
		$map_id = $this->CI->vlux_param_joueur->get_param($id);
		$map_id = $map_id->res_principale;
		$this->CI->load->library('vlux/teleport_factory');
		$teleport_id = $this->CI->teleport_factory->get_one_map_teleport($map_id);
		// S'il n'y a pas de tp sur la map
		if(!$teleport_id){
			// On envoie sur la place de la maire
			$teleport_id = $this->CI->teleport_factory->get_one_map_teleport(2);
		}
		$action = array(
			'action'	=> 'tp_home',
			'dest_id'	=> $teleport_id
			);
		return $action;
	}

	// Commande modération

	private function chut($id, $pseudo, $rang, $chan_id, $matches){
		if(isset($matches['pseudo'])){
			$destinataire = $this->manager->get_info($matches['pseudo']);
		}
		$date_envoi = bdd_datetime();
		// Si le joueur n'a pas le rang nécessaire, on le chute lui
		if(!$this->CI->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats, $rang)){
			$duree = 5;
		}
		// Si aucun pseudo n'est précisé
		elseif(!isset($matches['pseudo'])){
			return $this->send('commande', $id, self::PSEUDO_BOT, "T'as pas l'impression qu'il manque quelque chose $pseudo ?", $chan_id, 0, $date_envoi );
		}
		// Si le joueur se chut lui-même
		elseif(mb_strtolower($matches['pseudo']) == mb_strtolower($pseudo)){
			return $this->send('commande', $id, self::PSEUDO_BOT, "T'aurais pas abusé du Raki $pseudo ?", $chan_id, 0, $date_envoi);
		}
		// Si le destinataire n'est pas actif ou existant ou n'est pas présent sur la map
		elseif(!$destinataire){
			return $this->send('commande', $id, self::PSEUDO_BOT, self::MSG_JOUEUR_INCONNU, 'chan_global', 0, $date_envoi);
		}
		// Le joueur n'est pas présent sur le tchat
		elseif($destinataire->map_connecte != 1){
			return $this->send('commande', $id, self::PSEUDO_BOT, "Ce bouzouk n'est plus connecté sur la tchat.", $chan_id, 0, $date_envoi);
		}
		// Si le destinataire est un modo ou un admin
		elseif($this->CI->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats, $destinataire->rang) || $this->CI->bouzouk->is_admin(null, $destinataire->rang)){
			return FALSE;
		}
		else{
			$pseudo = $destinataire->pseudo;
			$id = $destinataire->id;
			$action_name = 'chut';
			if(isset($matches['duree']) && $matches['duree']!= 0){
				$duree = $matches['duree'];
			}
			elseif ($matches['commande'] == '/dechut') {
				$duree = 0;
				$action_name = 'dechut';
			}
			else{
				$duree = 5;
			}
			$action = array(
			'action'	=> $action_name,
			'pseudo'	=> $pseudo,
			'id'		=> $id,
			'duree'		=> $duree);
		}
		
		// Mise à jour du statut du joueur en bdd
		$this->manager->set_map_tchat_statut($destinataire->id, date('Y-m-d H:i:s',time()+$duree*60));
		return $action;
	}

	// Commande Admin

	private function get_fortune($id, $pseudo, $rang, $chan_id, $matches){
		$date_envoi = bdd_datetime();
		// Accès admin
		if(!$this->CI->bouzouk->is_admin(null, $rang)){
			return $this->send('commande', $id, self::PSEUDO_BOT, "Tu t'es cru sur IRC là ?", $chan_id, 0, $date_envoi);
		}
		// Si pas de pseudo spécifié
		if(!isset($matches['pseudo'])){
			return $this->send('commande', $id, self::PSEUDO_BOT, "La fortune de n'importe qui s'élève à n'importe quoi !", $chan_id, 0, $date_envoi );
		}
		else{
			// On récupère l'id du joueur ou de l'entreprise
			$query = $this->CI->db->select('id')
								  ->where('pseudo', $matches['pseudo'])
								  ->where_in('statut', array(4, 5, 6))
								  ->get('joueurs');
			if($query->num_rows() == 0){
				// On regarde si c'est une entreprise
				$query = $this->CI->db->select('struls')
									  ->where('nom', $matches['pseudo'])
									  ->get('entreprises');
				if($query->num_rows() == 0){
					return $this->send('commande', $id, self::PSEUDO_BOT, "Désole $pseudo, mais ce bouzouk n'existe pas ... revois tes sources.", $chan_id, 0, $date_envoi);
				}
				else{
					// C'est une entreprise
					$entreprise = $query->row();
					$fortune = $entreprise->struls;
				}
			}
			else{
				// C'est un joueur
				$joueur = $query->row();
				$fortune = $this->CI->bouzouk->fortune_totale($joueur->id)['total'];
			}
			// Une fois de temps en temps le nombre est faux
			if(mt_rand(1, 10) == 1){
				$fortune = (int)($fortune * mt_rand(15, 350) / 100);
			}
			return $this->send('commande', $id, self::PSEUDO_BOT, "La fortune de ".$matches['pseudo']." s'élève à ".number_format($fortune, 1, '.', ' ').($fortune <= 1 ? ' strul' : ' struls').'.', $chan_id, 0, $date_envoi);
		}		
	}

	private function tp_to($id, $pseudo, $rang, $chan_id, $matches){
		$date_envoi = bdd_datetime();
		// Accès admin
		if(!$this->CI->bouzouk->is_admin(null, $rang)){
			return $this->send('commande', $id, self::PSEUDO_BOT, "Tu t'es cru où là, $pseudo ?", $chan_id, 0, $date_envoi);
		}
		// Si aucun pseudo n'est précisé
		elseif(!isset($matches['pseudo'])){
			return $this->send('commande', $id, self::PSEUDO_BOT, "T'as pas l'impression qu'il manque quelque chose $pseudo ?", $chan_id, 0, $date_envoi );
		}
		else{
			//On récupère les coordonnées du joueur cible
			$cible = $this->manager->get_info($matches['pseudo']);
			// Si le joueur n'existe pas
			if(!$cible){
				return $this->send('commande', $id, self::PSEUDO_BOT, self::MSG_JOUEUR_INCONNU, $chan_id, 0, $date_envoi);
			}
			//Si le joueur n'est pas en mode aventure
			elseif($cible->map_connecte != 1){
				return $this->send('commande', $id, self::PSEUDO_BOT, "Ce bouzouk n'est pas en mode aventure.", $chan_id, 0, $date_envoi);
			}
			else{
				$coord = $this->CI->vlux_factory->vlux->gps($cible->id);
				$coord = array(
					'map_id'	=> $coord['map_id'],
					'map_x'		=> $coord['map_x'],
					'map_y'		=> $coord['map_y']
					);
				$action = array(
					'action'	=> 'tp_to',
					'coord'		=> $coord
					);
				return $action;
			}
			
		}
	}

	// Modération tchat
	public function get_signalements_a_traiter(){
		$signalements = $this->manager->get_signalements_a_traiter();
		return $signalements;
	}

	public function get_signalements_traites(){
		$signalements = $this->manager->get_signalements_traites();
		return $signalements;
	}

	public function set_statut_signalement($id, $id_modo){
		return $this->manager->set_statut_signalement($id, $id_modo);
	}

	public function get_chans(){
		return $this->manager->get_chans();
	}

	public function get_connectes($chan_id){
		return $this->manager->get_connectes($chan_id);
	}

	public function get_histo($chan, $limit){
		$histo =  $this->manager->get_histo($chan, $limit);
		foreach ($histo as $key => $message) {
			$histo[$key] = $this->send('message', $message->joueur_id, $this->get_profil($message->joueur_id, $message->pseudo, $message->rang).' : ', $message->message_content, $message->chan_id, $message->id, $message->date_envoi);
		}
		$histo = array_reverse($histo);
		return $histo;
	}

	public function delete_message($ids, $modo_id, $modo_pseudo, $modo_rang){
		//List des chans
		$chans = $this->manager->get_chans_list($ids);
		// On récupère les infos des messages
		$auteurs = $this->manager->get_list_auteurs($ids);
		// Notification aux joueurs censurés
		foreach($auteurs as $auteur){
			$this->CI->bouzouk->historique(147, null, array(profil($modo_id, $modo_pseudo, $modo_rang), pluriel($auteur->nb_messages, 'message')), $auteur->joueur_id, Bouzouk::Historique_Full);
		}
		// On supprime les messages
		$this->manager->delete_message($ids);
		// Historique modération
		$this->CI->bouzouk->historique_moderation(profil($modo_id, $modo_pseudo, $modo_rang)." a supprimé des messages sur le tchat de map.");
		return $chans;
	}

	public function get_chans_list($ids){
		return $this->manager->get_chans_list($ids);
	}

}