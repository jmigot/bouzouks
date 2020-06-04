<?php 
	$this->layout->set_title($title);
	$this->load->view('staff/vlux/menu_admin', array('lien' => $lien));
	$this->layout->ajouter_javascript('libs/jquery.pp3Diso.js');
	$this->layout->ajouter_css('pp3diso.css');
	$this->layout->ajouter_css('vlux.css');
	$this->layout->ajouter_javascript('vlux/item_editor.js');
	$this->layout->ajouter_javascript('vlux/vlux_functions.js');
	if($item->id != 'tmp'){
		$this->layout->ajouter_javascript_script("$(document).ready(function(){Map.changeTuile(2, 2, '$item->img');});");
	}
?>

<div id="staff-gerer_serveurs">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Edition : <?= ucfirst($item->nom); ?></h4>
		<div class="bloc_bleu">
			<div class="margin">
				<?php
					if ($item->id != 'tmp'){
						echo form_open('staff/gerer_items/item_editor/new_tuile', array('class' => 'inline-block'));
						echo form_submit(array('name'=>'new'),"Créer Tuile");
						echo form_close(); 
						echo form_open('staff/gerer_items/effacer_item/'.$item->id, array('class' => 'inline-block'));
						echo form_submit(array('class'=>'confirmation','name'=>'erase'),"Supprimer Item");
						echo form_close()."<br />";
					}
					echo form_open_multipart('staff/gerer_items/item_editor/'.$item->id,array('class' => 'inline-block'),array('id'=>$item->id, 'hauteur'=>0));
					$opt_nom= array (
									'name'	=>'nom',
									'id'	=>'nom',
									'maxlength'=>'30',
									'value'	=> ucfirst($item->nom));
					echo "Nom de l'item".form_input($opt_nom).'<br />';

					$opt_auth_level = array(
						Bouzouk::Rang_Aucun => 'Joueur',
						Bouzouk::Rang_BetaTesteur => 'Testeur',
						Bouzouk::Rang_MaitreJeu => 'MdJ',
						Bouzouk::Rang_Admin => 'Admin'
						);
					echo "Niveau d'accès : ".form_dropdown('auth_level', $opt_auth_level, $item->auth_level).'<br/>';

					$opt_prix = array(
									'name'	=> 'prix',
									'id'	=> 'prix',
									'maxlength'=>'30',
									'value'	=> $item->prix
									);
					echo "Prix de vente : ".form_input($opt_prix).form_dropdown('monnaie', $opt_monnaie,$item->monnaie).'<br/>';

					echo form_hidden('type', 'sols');
					$opt_cat = array(
									'id'		=> 'cat',
									'bâtiments'	=> 'Bâtis',
									'brico'		=> 'Bricolage',
									'vegetation'=> 'Végétations',
									'mobilier'	=> 'Mobilers',
									'deco'		=> 'Décoration',
									'jardin'	=> 'Jardin',
									'plage'		=> 'Plage',
									'urbain'	=> 'Urbain',
									'foret'		=> 'Forêt'
									);
					echo "Catégorie : ".form_dropdown('cat', $opt_cat, $item->cat).'<br />';

					$opt_titre = array(
									'name'	=>'titre',
									'id'	=>'titre',
									'maxlength'=>'30',
									'size'	=>'30',
									'value'	=> $item->titre,
									'placeholder' => 'optionel');
					echo "Titre de l'infobulle : ".form_input($opt_titre).'<br />';

					$opt_bulle = array(
									'name'	=>'bulle',
									'id'	=>'bulle',
									'rows'=>'4',
									'cols'	=>'30',
									'value'	=> $item->bulle,
									'placeholder' => 'optionel');
					echo "Contenu de l'infobulle : ".form_textarea($opt_bulle).'<br />';

					$opt_upload = array(
									'name'	=>'itemfile',
									'id'	=>'itemfile',
									'size'	=> '20');
					echo "Télécharger une";
					if($item->nom !='vide'){
						echo " nouvelle ";
					}
					echo "image (max 500Mo) : ".form_upload($opt_upload).'<br />';

					$checked_i = ($item->infranchissable==1)?TRUE:FALSE;
					echo "Infranchissable :".form_checkbox('infranchissable',1, $checked_i).'<br/>';

					$opt_nat = array(
									'normale'	=> 'Normale',
									'eau'		=> 'Eau',
									'lave'		=> 'Lave',
									'acide'		=> 'Acide'
									);
					echo "Nature de la tuile : ".form_dropdown('nature', $opt_nat, $item->nature).'<br />';
					
					echo form_hidden('decx', '0');
					echo form_hidden('decy', '0');
					echo form_hidden('img', $item->img);
					echo form_hidden('zone', '[{"x":0,"y":0}]');
					echo form_submit(array('class'=>'confirmation','name'=>'item_submit'),"Enregistrer");
					echo form_close();
				 ?>	
				 <div id="map-index">
				 	<div id="total">
				 		<div class="lumiere">
				 			<div id="carte">
				 				<div id="ppISO">
				 				</div>
				 			</div>
				 		</div>
				 	</div>
				 </div>
			</div>
		</div>
	</div>
</div>
