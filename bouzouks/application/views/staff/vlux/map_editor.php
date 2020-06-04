<?php 
	$this->layout->set_title($title);
	$this->load->view('staff/vlux/menu_admin', array('lien' => $lien));
?>

<div id="staff-gerer_serveurs">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Edition : <?= ucfirst($map->nom); ?></h4>
		<div class="bloc_bleu">
			<div class="margin">
				<?php
					if ($map->id != 'tmp'){ 
						echo form_open('staff/gerer_maps/effacer_map/'.$map->id, array('class' => 'inline-block'));
						echo form_submit(array('class'=>'confirmation','name'=>'erase'),"Supprimer Map");
						echo form_close()."<br />";
					}
					//img_tag("map/objets/$map->type/$map->img.png",ucfirst($map->nom),'');
					// @TODO mise en place des miniature des maps
					echo form_open('staff/gerer_maps/map_editor/'.$map->id,array('class' => 'inline-block'),array('id'=>$map->id));
					$opt_nom= array (
									'name'		=>'nom',
									'id'		=>'nom',
									'maxlength'	=>'30',
									'value'		=> ucfirst($map->nom));
					echo "Nom de la map".form_input($opt_nom).'<br />';
					$opt_type = array(
									'exterieur' => 'Extérieure',
									'interieur' => 'Intérieure',
									'batiment'	=> 'Bâtiment',
									'prison'	=> 'Prison',
									'special'	=> 'Spécial'
									);
					echo "Type de la map : ".form_dropdown('type', $opt_type, $map->type).'<br />';
					echo $proprios.'<br/>';
					$opt_size = array(
									'10'	=>'10',
									'15'	=>'15',
									'20'	=>'20',
									'40'	=>'40',
									);
					echo "Taille de la map : ".form_dropdown('size',$opt_size, $map->size).' cases de côté.<br />';
					$opt_prix = array(
									'name'	=> 'prix',
									'id'	=> 'prix',
									'maxlength'=>'30',
									'value'	=> $map->prix
									);
					$opt_monnaie = array(
									'strul'	=>'Strul(s)',
									'fragment'=>'Fragment(s)'
									);
					echo "Prix de vente : ".form_input($opt_prix).form_dropdown('monnaie', $opt_monnaie,$map->monnaie).'<br/>';
					echo form_submit(array('class'=>'confirmation','name'=>'map_submit'),"Enregistrer");
					echo form_close();
				 ?>	
			</div>
		</div>
	</div>
</div>