<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Exceptions extends CI_Exceptions
{
	/**
	 * Cette fonctions hookée permet de rediriger les erreurs vers un controller personnalisé, afin de pouvoir utiliser notre
     * système de layouts/vues (les templates d'erreur de CodeIgniter ne sont pas assez personnalisables sinon)
	 * Le but étant d'intégrer les messages d'erreur à l'intérieur du layout par défaut, donc avec tout le menu autour
	 */
	public function show_error($heading, $message, $template = 'error_general', $status_code = 500)
	{
		// On va chercher l'url de base
		$this->config =& get_config();
        $base_url = $this->config['base_url'];

		$message = '<p>'.implode('</p><p>', ( ! is_array($message)) ? array($message) : $message).'</p>';

		// En développement, on affiche l'erreur
		if (ENVIRONMENT != 'production' AND $status_code != 404)
		{
			$_SESSION['erreur_http'] = $message;
		}

		// On redirige vers un controller personnalisé
        header('location: '.$base_url.'site/error/'.$status_code);
		exit;
	}
}
