<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


// On s'assure que la session est bien démarrée
if( session_status() == PHP_SESSION_NONE ){
	session_start();
}

// Chargement de l'autoload du SDK de Facebook
require_once( APPPATH.'libraries/fb/facebook/autoload.php' );
require_once ( APPPATH.'libraries/fb/fb_persitent_data_interface.php');

use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException as FbException;
use Facebook\Exceptions\FacebookResponseException as FbRepException;

class Fb_api {

	private $CI;
	private $permissions;
	private $fb;

	public $user = null;

	public function __construct(){
		$this->CI =& get_instance();
		$this->permissions = $this->CI->config->item('permissions', 'facebook');

		$app_id = $this->CI->config->item('api_id', 'facebook');
		$app_secret = $this->CI->config->item('app_secret', 'facebook');
		$graph_version = $this->CI->config->item('default_graph_version', 'facebook');

		// Initialisation du SDK
		$this->fb = new Facebook( ['app_id'=>$app_id, 'app_secret'=>$app_secret, 'default_graph_version'=>$graph_version, 'persitent_data_handler'=> new CIFacebookPersistentDataHandler()]);

	}

	public function get_login_url($redirect_url=null){
		$helper = $this->fb->getRedirectLoginHelper();
		if($redirect_url == null){
			$redirect_url = $this->CI->config->item('redirect_url', 'facebook');
		}
		$loginUrl = $helper->getLoginUrl($redirect_url, $this->permissions);
		return $loginUrl;
	}

	public function connexion(){
		$helper = $this->fb->getRedirectLoginHelper();
		try {
			$access_token = $helper->getAccessToken();
		}
		catch(FbException $e){
			log_message('error', "Erreur SDK ".$e->getMessage());
			return false;
		}
		if( isset($access_token)){
			// Authentification ok
			// On sauvegarde le token d'authentification
			$this->fb->setDefaultAccessToken($access_token);
			//$this->session->set_userdata('fb_token', $access_token->getValue());
			$this->get_user();
			return true;
		}
		elseif( $helper->getError()){
			// L'utilisateur n'a pas autoriser la connexion
			log_message('error', "Erreur : ".$helper->getError());
			return false;
		}
	}

	public function get_user(){
		if(!$this->user){
			try{
				$user = $this->fb->get('/me');
			}
			catch(FbException $e){
				log_message('error', 'Le SDK Facebook a retourné une erreur : '.$e->getMessage());
				return false;
			}
			catch (FbRepException $e){
				log_message('error', 'Graph a retourné une erreur : '.$e->getMessage());
				return false;
			}
			$this->user = $user->getGraphUser();
		}
		return $this->user;
	}

	public function get_id(){
		$user = $this->get_user();
		$id = $user->getId();
		return $id;
	}

	public function get_birthday(){
		$user = $this->get_user();
		$bd = $user->getBirthday();
		if(!$bd){
			$bd = bdd_datetime();
		}
		else{
			$bd = $bd->format('Y-m-d');
		}
		return $bd;
	}

	public function get_email(){
		$user = $this->get_user();
		$email = $user->getEmail();
		return $email;
	}

	public function get_sexe(){
		$user = $this->get_user();
		$sexe = $user->getGender();
		return $sexe;
	}

	public function is_registered(){
		$rep = $this->CI->db->select('fb_id')->where('id', $this->CI->session->userdata('id'))->get('joueurs');
		$rep = $rep->row();
		if(ctype_digit($rep->fb_id)){
			return true;
		}
		else{
			return false;
		}
	}

	public function is_granted($fb_id){
		if(ctype_digit($fb_id)){
			$joueur_id = $this->CI->db->select('id')
									  ->where('fb_id', $fb_id)
									  ->where_in('statut',array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_ChoixPerso, Bouzouk::Joueur_Actif, Bouzouk::Joueur_Pause, Bouzouk::Joueur_Asile))
									  ->get('joueurs');
			if($joueur_id->num_rows()> 0){
				$joueur_id = $joueur_id->row();
				return $joueur_id->id;
			}
			else{
				return false;
			}
		}
	}

	public function is_exist_id($fb_id){
		$query = $this->CI->db->select('id')->where('fb_id', $fb_id)->get('joueurs');
		// Si l'id FB est déjà enregistré
		if($query->num_rows>0){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
}