<?php
$this->load->helper('form');
$this->layout->set_title($title);
$this->load->view('staff/vlux/menu_admin', array('lien' => $lien)); ?>
<div class="cellule_bleu_type1">
	<section class="bloc_bleu">
		<h3>Paramétrage de Vlux</h3>
		<article class="margin">
			<?php 
			echo form_open('staff/gerer_vlux/gestion');
			
			// Librairie 3Diso
			// Map
			echo form_fieldset(ucfirst($config->lib_client));
			// img_prefix
			echo form_label("Préfixe images ", 'img_prefix'); 
			$opt_img_prefix = array(
					'name'		=> 'img_prefix',
					'id'		=> 'img_prefix',
					'value'		=> $config->img_prefix
				);
			echo form_input($opt_img_prefix);
			// img_path
			echo form_label("Dossier des image ", 'img_path'); 
			$opt_img_path = array(
					'name'		=> 'img_path',
					'id'		=> 'img_path',
					'value'		=> ($config->img_path =='')?"Aucun":$config->img_path
				);
			echo form_input($opt_img_path);
			// map_slide
			echo form_label("Glissement map ", 'map_slide'); 
			$opt_map_slide = array(
					'0'		=> 'Désactivé',
					'1'		=> 'Activé'
					);
			echo form_dropdown('map_slide',$opt_map_slide,$config->map_slide);
			// fluid
			echo form_label("Fluidité ", 'fluid'); 
			$opt_fluid = array(
					'0'		=> 'Désactivé',
					'1'		=> 'Activé'
					);
			echo form_dropdown('fluid',$opt_fluid, $config->fluid );
			// speed_avatar
			echo form_label("Vitesse avatar ", 'speed_avatar'); 
			$opt_speed_avatar = array(
					'name'		=> 'speed_avatar',
					'id'		=> 'speed_avatar',
					'value'		=> $config->speed_avatar
				);
			echo form_input($opt_speed_avatar);
			echo form_label("Vitesse de l'animation", 'move_avatar_speed'); 
			$opt_move_avatar_speed = array(
				'name'		=>'move_avatar_speed',
				'id'		=>'move_avatar_speed',
				'value'		=> $config->move_avatar_speed
				);
			echo form_input($opt_move_avatar_speed);
			// speed_map
			echo form_label("Vitesse de la map ", 'speed_map'); 
			$opt_speed_map = array(
					'name'		=> 'speed_map',
					'id'		=> 'speed_map',
					'value'		=> $config->speed_map
				);
			echo form_input($opt_speed_map);
			// speed_map_while
			echo form_label("Vitesse de répétition ", 'speed_map_while'); 
			$opt_speed_map_while = array(
					'name'		=> 'speed_map_while',
					'id'		=> 'speed_map_while',
					'value'		=> $config->speed_map_while
				);
			echo form_input($opt_speed_map_while);
			// Fin Map
			echo form_fieldset_close();
			//Zoom
			echo form_fieldset("Zoom");
			// zoom_default
			echo form_label("Zoom par défaut ", 'zoom_default'); 
			$opt_zoom_default = array(
					'name'		=> 'zoom_default',
					'id'		=> 'zoom_default',
					'value'		=> $config->zoom_default
				);
			echo form_input($opt_zoom_default);
			// zoom_min
			echo form_label("Zoom mini ", 'zoom_min'); 
			$opt_zoom_min = array(
					'name'		=> 'zoom_min',
					'id'		=> 'zoom_min',
					'value'		=> $config->zoom_min
				);
			echo form_input($opt_zoom_min);
			// zoom_max
			echo form_label("Zoom maxi ", 'zoom_max'); 
			$opt_zoom_max = array(
					'name'		=> 'zoom_max',
					'id'		=> 'zoom_max',
					'value'		=> $config->zoom_max
				);
			echo form_input($opt_zoom_max);
			// zoom_pas
			echo form_label("Pas du zoom ", 'zoom_pas'); 
			$opt_zoom_pas = array(
					'name'		=> 'zoom_pas',
					'id'		=> 'zoom_pas',
					'value'		=> $config->zoom_pas
				);
			echo form_input($opt_zoom_pas);
			// Fin Zoom
			echo form_fieldset_close();

			// Curseur
			echo form_fieldset("Curseur");
			// cursor_z_index
			echo form_label("Décalage z du curseur ", 'cursor_z_index'); 
			$opt_cursor_z_index = array(
					'name'		=> 'cursor_z_index',
					'id'		=> 'cursor_z_index',
					'value'		=> $config->cursor_z_index
				);
			echo form_input($opt_cursor_z_index);
			// Fin Curseur
			echo form_fieldset_close();

			// Pathfinding
			echo form_fieldset("Pathfinder");
			// pathfinding
			echo form_label("Etat ", 'pathfinding'); 
			$opt_pathfinding = array(
					'0'		=> 'Désactivé',
					'1'		=> 'Activé'
					);
			echo form_dropdown('pathfinding',$opt_pathfinding, $config->pathfinding);
			// cursor_PF
			echo form_label("Curseur ", 'cursor_PF'); 
			$opt_cursor_PF = array(
					'name'		=> 'cursor_PF',
					'id'		=> 'cursor_PF',
					'value'		=> $config->cursor_PF
				);
			echo form_input($opt_cursor_PF);
			// PF_decx
			echo form_label("Décalage en x ", 'PF_decx'); 
			$opt_PF_decx = array(
					'name'		=> 'PF_decx',
					'id'		=> 'PF_decx',
					'value'		=> $config->PF_decx
				);
			echo form_input($opt_PF_decx);
			// PF_decy
			echo form_label("Décalage en y ", 'PF_decy'); 
			$opt_PF_decy = array(
					'name'		=> 'PF_decy',
					'id'		=> 'PF_decy',
					'value'		=> $config->PF_decy
				);
			echo form_input($opt_PF_decy);
			// PF_corners
			echo form_label("Gestion diagonale ", 'PF_corners'); 
			$opt_PF_corners = array(
					'0'		=> 'Désactivé',
					'1'		=> 'Activé'
					);
			echo form_dropdown('PF_corners',$opt_PF_corners, $config->PF_corners);
			// PF_max
			echo form_label("Nombre de case maxi ", 'PF_max'); 
			$opt_PF_max = array(
					'name'		=> 'PF_max',
					'id'		=> 'PF_max',
					'value'		=> $config->PF_max
				);
			echo form_input($opt_PF_max);
			// Fin Pathfinder
			echo form_fieldset_close();

			// Bulle
			echo form_fieldset("Bulles");
			// Centrage auto
			echo form_label("Etat ", 'bulle_auto_x'); 
			$opt_bulle_auto_x = array(
					'0'		=> 'Désactivé',
					'1'		=> 'Activé'
					);
			echo form_dropdown('bulle_auto_x',$opt_bulle_auto_x, $config->bulle_auto_x);
			// bulle_auto_y
			echo form_label("Position verticale ", 'bulle_auto_y'); 
			$opt_bulle_auto_y = array(
					'top'		=> 'Haut',
					'bottom'	=> 'Bas',
					'none'		=> 'None'
					);
			echo form_dropdown('bulle_auto_y',$opt_bulle_auto_y, $config->bulle_auto_y);
			// bulle_deca_y
			echo form_label("Décalage vertical ", 'bulle_deca_y'); 
			$opt_bulle_deca_y = array(
					'name'		=> 'bulle_deca_y',
					'id'		=> 'bulle_deca_y',
					'value'		=> $config->bulle_deca_y
				);
			echo form_input($opt_bulle_deca_y);
			// cursor_delay
			echo form_label("Délai curseur ", 'cursor_delay'); 
			$opt_cursor_delay = array(
					'name'		=> 'cursor_delay',
					'id'		=> 'cursor_delay',
					'value'		=> $config->cursor_delay
				);
			echo form_input($opt_cursor_delay);
			//Fin bulles
			echo form_fieldset_close();
			// Mairie
			echo form_fieldset('Mairie');
			// Prix des maps
			echo form_label("Taux d'achat par la mairie (pour 10 cases)");
			$opt_prix_mairie = array(
					'name'		=> 'map_prix_mairie',
					'id'		=> 'map_prix_mairie',
					'value'		=> $config->map_prix_mairie*10
				);
			echo form_input($opt_prix_mairie)." struls";
			echo form_fieldset_close();
			// Bouton
			echo form_submit('param_jeu', 'Valider');
			//Fin formulaire
			echo form_close();
			?>
		</article>
	</section>
</div>
