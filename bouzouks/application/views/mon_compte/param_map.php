<?php
$this->layout->set_title($title);
?>

<!-- Menu -->
<?php $this->load->view('mon_compte/menu', array('lien' => 4)) ?>

<!-- Parrainer un ami -->
<div class="cellule_bleu_type1">
	<h4>Mes options</h4>
	<article class="bloc_bleu centre">
		<?php
			echo form_open('mon_compte/modifier_option_map', array('class'=>'inline-block'));
			/* Zoom par défaut*/
			$opt_zoom = array('0.4'=>0.4, '0.8'=>0.8, '1.2'=>1.2, '1.6'=>1.6);
			echo "Zoom par défaut : ".form_dropdown('zoom_defaut', $opt_zoom, $params->zoom_defaut).'<br/>';
			/* Son notif tchat */
			$checked_son = ($params->son_notif==1)?TRUE:FALSE;
			echo "Jouer un son lorsque votre pseudo apparait dans le tchat : ".form_checkbox('son_notif', 1, $checked_son).'<br/>';
			/* chan actif par défaut */
			$opt_chan = array('map' => 'Map', 'global'=> 'Global');
			echo "Onglet de tchat actif par défaut".form_dropdown('chan_defaut', $opt_chan, $params->chan_defaut).'<br/>';
			/* affichage pseudo des autres joueurs présents */
			echo "Affichage de pseudo des autres joueurs :<br/>";
			echo form_label('Au survol de la souris', 'affichage_pseudo').form_radio('affichage_pseudo',0, ($params->affichage_pseudo==0)?TRUE:FALSE);
			echo form_label('Tout le temps','affichage_pseudo').form_radio('affichage_pseudo',1, ($params->affichage_pseudo==1)?TRUE:FALSE)."<br/>";
			// Résidence principale
			echo "Résidenceprincipale : ".form_dropdown('res_principale', $list_maps, $params->res_principale).'<br/>';
			echo form_submit('validation_param_map', 'Modifier les options');
			echo form_close();
		?>
	</article>
</div>