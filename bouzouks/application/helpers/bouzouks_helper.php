<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function bouzouk_datetime($date = null, $format = 'long', $annee_bouzouk = true, $secondes = false)
{
	$CI =& get_instance();
	$CI->lang->load('calendar');
	$CI->load->helper('date');

	// Format court : 17/01/2012 à 09:53
	if ($format == 'court')
	{
		$format = '%d/%m/%Y à %H:%i';

		if ($secondes)
			$format .= ':%s';
			
		$date = mdate($format, strtotime($date));

		if ($date === false)
			$date = '';

		if ( ! $annee_bouzouk)
			return $date;

		// On ajoute 941 à l'année pour le calendrier bouzouk
		$date_string = explode(' ', $date);
		$tab_date = explode('/', $date_string[0]);
		$annee = $tab_date[2];
		$date = str_replace($annee, $annee + 941, $date);

		return $date;
	}

	// Format long : lundi 17 Janvier 2012 à 09:53
	$format = '%l %d %F %Y à %H:%i';
	$date = mdate($format, strtotime($date));

	if ($date === false)
		$date = '';

	// On récupère le jour et le mois qui seront en anglais
	$tab_date = explode(' ', $date);
	$jour = $tab_date[0];
	$mois = $tab_date[2];
	$annee = $tab_date[3];

	// On traduit le jour et le mois grâce au fichier de langue
	$jour_traduit = $CI->lang->line('cal_'.strtolower($jour));
	$mois_traduit = $CI->lang->line('cal_'.strtolower($mois));

	// On remplace les noms anglais originaux par les traductions
	if ($jour_traduit != '' AND $mois_traduit != '')
	{
		$date = str_replace($jour, $jour_traduit, $date);
		$date = str_replace($mois, $mois_traduit, $date);
	}

	// On ajoute 941 à l'année pour le calendrier bouzouk
	if ($annee_bouzouk)
		$date = str_replace($annee, $annee + 941, $date);

	return $date;
}

function bouzouk_date($date = '', $annee_bouzouk = true)
{
	$CI =& get_instance();
	$CI->load->helper('date');

	// Format : 26/08/2012
	$format = '%d/%m/%Y';
	$date = mdate($format, strtotime($date));

	if ($date === false)
		$date = '';

	if ( ! $annee_bouzouk)
		return $date;
	
	// On récupère l'année
	$tab_date = explode('/', $date);
	$annee = $tab_date[2];

	// On ajoute 941 à l'année pour le calendrier bouzouk
	$date = str_replace($annee, $annee + 941, $date);

	return $date;
}

function jour_mois($date = '')
{
	$CI =& get_instance();
	$CI->load->helper('date');

	// Format : 26/08
	$format = '%d/%m';
	$date = mdate($format, strtotime($date));

	if ($date === false)
		$date = '';

	return $date;
}

function jour_mois_heure_minute($date = '')
{
	$CI =& get_instance();
	$CI->load->helper('date');

	if ( ! ctype_digit($date))
		$date = strtotime($date);

	// Format : 26/08 à 10:51
	$format = '%d/%m à %H:%i';
	$date = mdate($format, $date);

	if ($date === false)
		$date = '';

	return $date;
}

function bdd_datetime()
{
	return date('Y-m-d H:i:s');
}

function bdd_date($date = null)
{
	if ( ! isset($date))
		$date = time();
		
	return date('Y-m-d', $date);
}

function tchat_datetime($date)
{
	$CI =& get_instance();
	$CI->load->helper('date');

	// Format : (26/08 à 13h05)
	$format = '(%d/%m %Hh%i)';
	$date = mdate($format, strtotime($date));

	if ($date === false)
		$date = '';

	return $date;
}

function creer_pagination($url, $nb_total, $par_page, $offset, $segment = 3)
{
	$config = array(
		'base_url'       => site_url($url),
		'uri_segment'    => $segment,
		'total_rows'     => $nb_total,
		'per_page'       => $par_page,
		'first_link'     => 'Première',
		'last_link'      => 'Dernière',
		'full_tag_open'  => '[',
		'full_tag_close' => ']'
	);

	$CI =& get_instance();
	$CI->load->library('pagination');
	$CI->pagination->initialize($config);
	$liens = $CI->pagination->create_links();

	/* L'offset doit être composé de chiffres uniquement
	   et ne doit pas dépasser le nombre total ou être en décalage par rapport au nombre par page
	*/
	if ( ! entier_naturel($offset) OR ($offset > 0 AND ($offset >= $nb_total OR $offset % $par_page != 0)))
	{
		$offset = 0;
		$CI->session->set_userdata('flash_echec', "Arrête de trafiquer l'url, nom d'un pioupiouk !");
	}

	$vars = array(
		'offset'   => $offset,
		'par_page' => $par_page,
		'liens'    => $liens
	);

	return $vars;
}

function entier_naturel($nombre)
{
	return isset($nombre) && ctype_digit((string)$nombre) AND (int)$nombre >= 0;
}

function entier_naturel_positif($nombre)
{
	return isset($nombre) && ctype_digit((string)$nombre) AND (int)$nombre > 0;
}

function creer_table_smileys($input_id, $nb_colonnes = 27)
{
	$CI =& get_instance();
	$CI->load->helper('smiley');
	$CI->load->library('table');

	if ($CI->bouzouk->is_admin())
		$nb_colonnes = 70;
		
	$image_array = get_clickable_smileys(smileys_url(), $input_id);
	$col_array = $CI->table->make_columns($image_array, $nb_colonnes);
	return $CI->table->generate($col_array);
}

function remplace_smileys($message)
{
	$CI =& get_instance();
	$CI->load->helper('smiley');
	return parse_smileys($message, smileys_url());
}

function avatar($faim = null, $sante = null, $stress = null, $perso = null)
{
	if ($perso == 'avatar_jf')
		return 'mairie/avatar_jf.png';

	if ( ! isset($faim, $sante, $stress, $perso))
	{
		$CI     =& get_instance();
		$faim   = $CI->session->userdata('faim');
		$sante  = $CI->session->userdata('sante');
		$stress = $CI->session->userdata('stress');
		$perso  = $CI->session->userdata('perso');
	}
	
	$moyenne = (int)round(($faim + $sante + (100 - $stress)) / 3);
	$tete = 'normal';

	if ($moyenne < 36)
		$tete = 'triste';

	else if ($moyenne > 70)
		$tete = 'content';
	
	return 'perso/tete/'.$perso.'_'.$tete.'.png';
}

function img_url_avatar($faim = null, $sante = null, $stress = null, $perso = null, $utiliser_avatar_toboz = false, $joueur_id = null)
{
	if ($utiliser_avatar_toboz)
	{
		$avatars = glob(FCPATH.'tobozon/img/avatars/'.$joueur_id.'.*');

		if (count($avatars) == 1)
			return site_url('tobozon/img/avatars/'.basename($avatars[0]));
	}
	
	// --------- Event RP Bouf'têtes ------------
	$CI =& get_instance();
	if ($CI->bouzouk->est_infecte($joueur_id))
		$perso = 'zombi/'.$perso;
	// --------- Event RP Boobz ------------
	if($CI->bouzouk->est_maudit_mlbobz($joueur_id)){
		$perso = 'rp_zoukette/'.$perso;
	}

	return img_url(avatar($faim, $sante, $stress, $perso));
}

function select_bouzouk($sexe, $perso_male, $perso_femelle)
{
	// On récupère les persos autorisés
	$CI =& get_instance();
	$persos = $CI->bouzouk->get_persos();
	$img_males = array_keys($persos['male']);
	$img_femelles = array_keys($persos['femelle']);

	// On choisis un sexe par défaut
	if ( ! $sexe || ! in_array($sexe, array('male', 'femelle')))
		$sexe = 'male';

	// On choisit des bouzouk par défaut
	if ( ! $perso_male || ! in_array($perso_male, $img_males))
		$perso_male = $img_males[0];

	if ( ! $perso_femelle || ! in_array($perso_femelle, $img_femelles))
		$perso_femelle = $img_femelles[0];

	// On affiche/cache la sélection male/femelle
	$male_invisible = ($sexe == 'male') ? '' : ' class="invisible"';
	$femelle_invisible = ($sexe == 'femelle') ? '' : ' class="invisible"';

	// On choisit la bonne image au départ ou une par défaut
	if ($sexe == 'male')
		$img_url = img_url('perso/ensemble/'.$perso_male.'.png');

	else if($sexe == 'femelle')
		$img_url = img_url('perso/ensemble/'.$perso_femelle.'.png');
	?>
	<div class="img-perso">
		<p><img src="<?= $img_url ?>" alt="Aperçu"></p>
	</div>

	<div class="fl-gauche">
	
		<!-- Bouzouk male -->
		<select name="perso_male" id="select_male" size="13"<?= $male_invisible ?>>
			<?php foreach ($persos['male'] as $perso_img => $perso_titre): ?>
				<option value="<?= $perso_img ?>"<?php if ($perso_male == $perso_img) echo ' selected'; ?>><?= $perso_titre ?></option>
			<?php endforeach; ?>
		</select>

		<!-- Bouzouk femelle -->
		<select name="perso_femelle" id="select_femelle" size="10"<?= $femelle_invisible ?>>
			<?php foreach ($persos['femelle'] as $perso_img => $perso_titre): ?>
				<option value="<?= $perso_img ?>"<?php if ($perso_femelle == $perso_img) echo ' selected'; ?>><?= $perso_titre ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<?php
}

function tab2spaces($string)
{
	return str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $string);
}

function pluriel($nb, $singulier, $pluriel = null)
{
	if ( ! isset($pluriel))
		$pluriel = $singulier.'s';
	
	if ($nb >= -1 && $nb <= 1)
		return $nb.' '.$singulier;

	return $nb.' '.$pluriel;
}

function struls($struls, $colorier = true)
{
	// On sauvegarde l'entier
	$nb = $struls;
	
	// Formatage
	$struls = number_format($struls, 1, '.', ' ');

	if (substr($struls, strlen($struls) - 2) == '.0')
		$struls = substr($struls, 0, strlen($struls) - 2);

	// Mise au pluriel
	if ($nb >= -1 && $nb <= 1)
	{
		$struls .= ' strul';
	}

	else
	{
		$struls .= ' struls';
	}

	// Coloriage
	if ($colorier)
		$struls = couleur($struls, 'pourpre');

	return $struls;
}

function heures_ecoulees($date)
{
	$intervalle = time() - strtotime($date);
	$nb_heures = (int) ($intervalle / 3600);

	return pluriel($nb_heures, 'heure');
}

function jours_ecoules($date)
{
	$intervalle = time() - strtotime($date);
	$nb_jours = (int) ($intervalle / (3600*24));

	return pluriel($nb_jours, 'jour');
}

function couleur($string, $couleur = 'pourpre')
{
	return '<span class="'.$couleur.'">'.$string.'</span>';
}

function profil($id = -1, $pseudo = '', $rang = null, $statut = null)
{
	if ( ! isset($id))
		return '';
	
	$CI =& get_instance();

	if ($id == -1)
	{
		$id     = $CI->session->userdata('id');
		$pseudo = $CI->session->userdata('pseudo');
	}

	else if ($pseudo == '')
	{
		$query = $CI->db->select('pseudo, rang')
						->from('joueurs')
						->where('id', $id)
						->get();

		if ($query->num_rows() == 0)
			return 'bouzouk inconnu';
		
		$joueur = $query->row();
		$pseudo = $joueur->pseudo;
		$rang = $joueur->rang;
	}

	// Si le joueur est un robot du jeu ou un compte inactif
	if (in_array($id, $CI->bouzouk->get_robots()) || in_array($id, $CI->bouzouk->get_inactifs()))
		return couleur($pseudo, 'pourpre');

	// Si le joueur est un compte créé pour un clan
	if (in_array($id, $CI->bouzouk->get_clans()))
		return couleur($pseudo, 'rouge');
		
	// Si le joueur a un rang spécial
	if ($rang == null)
		$rang = Bouzouk::Rang_Aucun;
		
	if ($CI->bouzouk->is_admin(Bouzouk::Rang_Admin, $rang))
		$rang = couleur(' [Admin]', 'rouge');

	else if ($CI->bouzouk->is_mdj($rang))
		$rang = couleur(' [MdJ]', 'vert_fonce');

	// Spécial ouah
	else if ($CI->bouzouk->is_moderateur(null, $rang))
		$rang = couleur(' [Modo]', 'bleu');
		
	else if ($CI->bouzouk->is_journaliste(null, $rang))
		$rang = couleur(' [Journal]', 'pourpre');

	else
		$rang = '';

	// Si le statut est précisé, on l'ajoute
	if ($statut != null)
	{
		if ($statut == Bouzouk::Joueur_Inactif)
			$statut = ' [<b>'.couleur('I', 'orange').'</b>]';

		else if ($statut == Bouzouk::Joueur_Etudiant || $statut == Bouzouk::Joueur_ChoixPerso)
			$statut = ' [<b>'.couleur('E', 'orange').'</b>]';

		else if ($statut == Bouzouk::Joueur_Asile)
			$statut = ' [<b>'.couleur('A', 'pourpre').'</b>]';
			
		else if ($statut == Bouzouk::Joueur_Pause)
			$statut = ' [<b>'.couleur('P', 'pourpre').'</b>]';

		else if ($statut == Bouzouk::Joueur_GameOver)
			$statut = ' [<b>'.couleur('G', 'rouge').'</b>]';

		else if ($statut == Bouzouk::Joueur_Banni)
			$statut = ' [<b>'.couleur('B', 'rouge').'</b>]';
			
		else
			$statut = '';
	}

	else
		$statut = '';

	// On écrit le lien vers le profil
	return '<a href="'.site_url('communaute/profil/'.$id).'" title="Voir le profil de '.$pseudo.'">'.$pseudo.$rang.$statut.'</a>';
}

function get_profil($id, $rang = Bouzouk::Rang_Aucun)
{
	$CI =& get_instance();

	$query = $CI->db->select('pseudo')
				    ->from('joueurs')
					->where('id', $id)
					->get();
	$joueur = $query->row();
	
	return profil($id, $joueur->pseudo, $rang);
}

function menu_actif($lien = null, $compare = null)
{
	static $menu_i = 0;
	static $trouve = false;

	// On veut obtenir le compteur
	if ($lien == null)
		return $menu_i;
	
	// Le compteur augmente tant que le bon lien n'a pas été trouvé
	if ( ! $trouve)
		$menu_i++;

	// Si c'est le bon lien, c'est trouvé et on renvoit l'actif
	if ($lien == $compare)
	{
		$trouve = true;
		return 'class="actif" ';
	}

	return '';
}

function img_tag($src, $title, $alt='', $width='', $height='') {
	echo("<img src='".img_url($src)."' title='".$title."' alt='".$alt."' width='".$width."' height='".$height."' />");
}

function get_io_url(){
	$url = substr(base_url(), 0, -1).':8080/socket.io/socket.io.js';
	return $url;
}
