<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : parser de bbcode
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : juillet 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Lib_parser
{
	private $CI;

	// BBCode possibles
	private $bbcodes = array(
		'#\[b\](.+)\[/b\]#Us'  => '<b>$1</b>',
		'#\[i\](.+)\[/i\]#Us'  => '<i>$1</i>',
		'#\[u\](.+)\[/u\]#Us'  => '<u>$1</u>',
	);

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->layout->ajouter_javascript('parser.js');
	}

	public function bbcode($texte_id, $previsualisation = true)
	{
		$retour = '<p class="centre">';
		$retour .= '<input type="button" value="Gras" class="bbcode_gras_'.$texte_id.'"><span id="bbcode_gras_'.$texte_id.'" class="invisible">'.$texte_id.'|[b]|[/b]</span>';
		$retour .= '<input type="button" value="Italique" class="bbcode_italique_'.$texte_id.'"><span id="bbcode_italique_'.$texte_id.'" class="invisible">'.$texte_id.'|[i]|[/i]</span>';
		$retour .= '<input type="button" value="Souligné" class="bbcode_souligne_'.$texte_id.'"><span id="bbcode_souligne_'.$texte_id.'" class="invisible">'.$texte_id.'|[u]|[/u]</span>';
		if ($previsualisation) $retour .= '<input type="button" value="Prévisualiser" class="previsualiser" id="previsualiser_'.$texte_id.'">';

		return $retour.'</p>';
	}

	public function remplace_bbcode($texte, $type = null)
	{
		foreach ($this->bbcodes as $balise => $remplacement)
			$texte = preg_replace($balise, $remplacement, $texte);

		// Remplacement des liens images
		if ($type == 'gazette')
		{
			$texte = preg_replace('#\[img=(.+)\|taille=(\d+)\|class=\]#Usi', '</p><p class="centre"><img src="'.img_url('uploads/gazette').'/$1" alt="$1" width="$2"></p><p>', $texte);
			$texte = preg_replace('#\[img=(.+)\|taille=(\d+)\|class=(fl-gauche|fl-droite)\]#Usi', '<img src="'.img_url('uploads/gazette').'/$1" alt="$1" width="$2" class="margin $3">', $texte);
		}
				
		return $texte;
	}
} 
