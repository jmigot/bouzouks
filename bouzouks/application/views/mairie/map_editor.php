<?php
	$this->layout->set_title($title);
?>
<div id="mairie-gerer">
	<?php
		$this->load->view('mairie/menu_gestion', array('lien'=> $lien));
	?>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Edition de Map</h4>
		<div class="bloc_bleu">
			<div class="margin">
				<?php
					echo form_open('mairie/map_editor_validation', array('class'=>'inline-block'));
					$opt_nom= array(
									'name'		=>'nom',
									'id'		=>'nom',
									'maxlength'	=>'30',
									'value'		=> ucfirst($map->nom));
					echo "Nom de la map".form_input($opt_nom).'<br />';
					if($map->id == 'tmp'){
						$opt_size = array(
										'20'	=>'20',
										'40'	=>'40'
										);
						echo "Taille de la map : ".form_dropdown('size',$opt_size, $map->size).' cases de côté.<br />';
					}
					else{
						$opt_prix = array(
							'name'	=>'prix',
							'id'	=>'prix',
							'value' => $map->prix);
						echo "Prix de vente de la map : ".form_input($opt_prix).' struls.<br/>';
					}
					echo form_submit(array('name'=>'map_submit'),"Enregistrer");
					echo form_close();
				?>
			</div>
		</div>
	</div>
</div>