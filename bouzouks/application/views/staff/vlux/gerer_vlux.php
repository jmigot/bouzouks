<?php 
	$this->layout->set_title($title); 
	// Menu onglet
	$this->load->view('staff/vlux/menu_admin', array('lien' => $lien));
	?>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Gestion du jeu nom de code "Vlux 3D"</h4>
		<section class="bloc_bleu">
			<article class="margin">
			<h5>Résumé</h5>
				<p>
					Etat du serveur : <?php img_tag($etat_serveur['img'],$etat_serveur['title'], $etat_serveur['alt']);?><br />
					Cartes : <?= $nb_maps_open ?> / <?= $nb_maps ?><br />
				</p>
				<br />
				<p> Tout bientôt, un panneau de gestion complet du jeu avec :
					<ul>
						<li>gestion, modération et administration du jeu</li>
						<li>modération des joueurs</li>
						<li>création et gestion de pnj et monstre</li>
						<li>création et gestion de quête</li>
						<li>des calamités ? Des dons du ciel ? </li>
					</ul></p>
			</article>
			<article class="margin">
				<h5>Map pp3dIso</h5>
				<p>
					Préfixe de l'image : <?= ($config->img_prefix =='')?"Aucun":$config->img_prefix ?><br/>
					Dossier des images : <?= $config->img_path ?><br/>
					Map Slide : <?= ($config->map_slide == 0)?"Désactivé":"Activé" ?><br/>
					Fluidité : <?= ($config->fluid == 0)?"Désactivée":"Activée" ?><br/>
					Vitesse Avatar : <?= $config->speed_avatar ?><br/>
					Vitesse de défilement de la map : <?= $config->speed_map ?><br/>
					Vitesse de répétition : <?= $config->speed_map_while ?><br/>
					Zoom par défaut : <?= $config->zoom_default ?><br/>
					Zoom mini : <?= $config->zoom_min ?><br/>
					Zoom maxi : <?= $config->zoom_max ?><br/>
					Pas du zoom : <?= $config->zoom_pas ?><br/>
					Décalage du curseur (z-index) : <?= $config->cursor_z_index ?>
				</p>
			</article>
			<article class="margin">
				<h5>Path Finder</h5>
				<p>
					Etat : <?= ($config->pathfinding == 0)?"Désactivé":"Activé"; ?><br/>
					Curseur : <?= ($config->cursor_PF =='')?"Aucun":$config->cursor_PF ?><br/>
					Décalage en x : <?= $config->PF_decx ?><br/>
					Décalage en y : <?= $config->PF_decy ?><br/>
					Passage en diagonal : <?= ($config->PF_corners == 0)?"Désactivé":"Activé" ?><br/>
					Nombre de cases maxi : <?= $config->PF_max ?>
				</p>
			</article>
			<article class="margin">
				<h5>Bulles</h5>
				<p>
					Centrage auto : <?= ($config->bulle_auto_x == 0)?"Désactivé":"Activé" ?><br/>
					Position vertical par rapport aux objets : <?= $config->bulle_auto_y ?><br/>
					Décalage vertical (top, bottom, none) : <?= $config->bulle_deca_y ?><br/><br/>
					Délai du curseur : <?= $config->cursor_delay ?>
				</p>
			</article>
		</section>

	</div>