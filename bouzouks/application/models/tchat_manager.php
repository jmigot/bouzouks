<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//  Objet représentant la connexion au sgbd pour les données du serveur vlux
class Tchat_manager extends CI_Model {

const TABLE ='vlux_tchat';
const TABLE_SIGNALEMENT = 'vlux_signalements';

	public function add_message($chan_id, $joueur_id, $message_content, $date_envoi){
		$query = $this->db->set(array(
			'chan_id'			=> $chan_id,
			'joueur_id'			=> $joueur_id,
			'message_content'	=> $message_content,
			'date_envoi'		=> $date_envoi
			))
			->insert(self::TABLE);
		$id = $this->db->insert_id();
		return $id;	
	}

	public function delete_message($ids){
		$this->db->where_in('id', $ids)->delete(self::TABLE );
	}

	// Savoir si un bouzouk est connecté

	public function get_info($joueur_pseudo)
	{
		$query = $this->db->select('j.id, j.pseudo,  j.connecte, j.map_connecte, j.map_id, j.rang, m.nom AS map_nom')
							  ->like('pseudo', $joueur_pseudo)
							  ->where_in('statut', array(2,3,4,5))
							  ->from('joueurs j')
							  ->join('vlux_maps m', 'j.map_id = m.id')
							  ->get();
		if($query->num_rows>0){
			return $query->row();
		}
		else{
			return false;
		}
	}

	// Les signalements
	public function add_signalement($joueur_id, $message, $chan_id, $date_envoi){
		$this->db->set(array('id_auteur'=>$joueur_id, 'chan_id'=>$chan_id, 'date_envoi'=>$date_envoi, 'statut'=>1, 'content'=> $message))->insert(self::TABLE_SIGNALEMENT);
	}

	public function get_signalements_a_traiter(){
		$query = $this->db->order_by('s.date_envoi', 'desc')
						  ->select ('s.*, j.pseudo')
						  ->from('vlux_signalements s')
						  ->join('joueurs j', 'j.id = s.id_auteur')
						  ->where('s.statut', bouzouk::SignalementsTchatMapAttente)
						  ->get();
		$signalements = $query->result();
		return $signalements;
	}

	public function get_signalements_traites(){
		$query = $this->db->order_by('s.date_envoi', 'desc')
						  ->select('s.*, j.pseudo, , j2.pseudo AS pseudo_modo')
						  ->from('vlux_signalements s')
						  ->join('joueurs j', 'j.id = s.id_auteur')
						  ->join('joueurs j2', 'j2.id = s.id_modo')
						  ->where('s.statut', bouzouk::SignalementsTchatMapTraite)
						  ->where('s.date_envoi > (NOW() - INTERVAL 1 MONTH)')
						  ->get();
		$signalements = $query->result();
		return $signalements;
	}

	public function set_statut_signalement($id, $id_modo){
		// On verifie que le signalement existe et n'a pas été déjà traiter
		$signalement = $this->db->where('id', $id)
								->where('statut', 1)
								->get(self::TABLE_SIGNALEMENT);
		if($signalement->num_rows == 0){
			return false;
		}
		$query = $this->db->set(array('statut'=> 2, 'id_modo' => $id_modo, 'date_traitement'=>bdd_datetime()))
						  ->where('id', $id)
						  ->update(self::TABLE_SIGNALEMENT);
		return $query;
	}

	public function get_chans(){
		$query = $this->db->select('m.nom, m.id')
						  ->where('m.id >', 1)
						  ->from('vlux_maps m')
						  ->get();
		$query = $query->result_array();
		return $query;
	}

	public function get_connectes($chan_id){
		$query = $this->db->where('map_connecte ', 1);
		if(is_int($chan_id)){
			$query = $this->db->where('map_id', $chan_id);
		}
		$query = $this->db->select('id, pseudo, rang, map_id')
			  ->order_by('map_id')
			  ->from('joueurs')
			  ->get();
		$query = $query->result();
		return $query;
	}

	public function get_histo($chan, $limit){
		if($chan == 'global'){
			$chan = 'chan_'.$chan;
		}
		else{
			$chan = 'chan_map_room_'.$chan;
		}
		$query = $this->db->from(self::TABLE.' m')
						  ->where('chan_id', $chan)
						  ->select('m.*, j.pseudo, j.rang')
						  ->join('joueurs j', 'j.id = m.joueur_id')
						  ->order_by('date_envoi', 'desc')
						  ->limit($limit)
						  ->get();
		$query = $query->result();
		return $query;
	}

	public function get_list_auteurs($ids){
		$query = $this->db->select('tm.joueur_id, COUNT(tm.joueur_id) AS nb_messages, j.rang')
						  ->from(self::TABLE.' tm')
						  ->join('joueurs j', 'j.id = tm.joueur_id')
						  ->where_in('tm.id', $ids)
						  ->group_by('tm.joueur_id')
						  ->get();
		$auteurs = $query->result();
		return $auteurs;

	}

	public function get_chans_list($ids){
		$query = $this->db->select('chan_id')->where_in('id', $ids)->group_by('chan_id')->get(self::TABLE);
		$query = $query->result();
		return $query;
	}

	public function set_map_tchat_statut($id, $date){
		$query = $this->db->set('map_tchat_statut', $date)->where('id', $id)->update('joueurs');
	}

}