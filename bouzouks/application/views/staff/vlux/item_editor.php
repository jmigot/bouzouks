<?php 
$this->layout->set_title($title);
$this->load->view('staff/vlux/menu_admin', array('lien' => $lien));
$this->layout->ajouter_javascript('libs/jquery.pp3Diso.js');
$this->layout->ajouter_css('pp3diso.css');
$this->layout->ajouter_css('vlux.css');
$this->layout->ajouter_javascript('vlux/item_editor.js');
$this->layout->ajouter_javascript('vlux/vlux_functions.js');
if($item->id != 'tmp'){
	$this->layout->ajouter_javascript_script("$(document).ready(function(){Map.addItem (6, 10, 0, '$item->img.png',  $item->decx , $item->decy , $item->id );});");
}
?>

<div id="staff-gerer_serveurs">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Edition : <?= ucfirst($item->nom); ?></h4>
		<div class="bloc_bleu">
			<div class="margin">
				<?php
				if ($item->id != 'tmp'){
					echo form_open('staff/gerer_items/item_editor/new_item', array('class' => 'inline-block'));
					echo form_submit(array('name'=>'new'),"Créer Item");
					echo form_close();
					echo form_open('staff/gerer_items/effacer_item/'.$item->id, array('class' => 'inline-block'));
					echo form_submit(array('class'=>'confirmation','name'=>'erase'),"Supprimer Item");
					echo form_close()."<br />";
				}
				echo form_open_multipart('staff/gerer_items/item_editor/'.$item->id,array('class' => 'inline-block'),array('id'=>$item->id,'nature'=>'normale'));

				$opt_nom= array (
					'name'	=>'nom',
					'id'	=>'nom',
					'maxlength'=>'30',
					'value'	=> $item->nom);
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

				$opt_type = array(
					'id'		=> 'type',
					'exterieur' => 'Extérieure',
					'interieur' => 'Intérieure',
					'utilitaires' => 'Utilitaire',
					'special'	=> 'Spécial'
					);
				echo "Type d'item : ".form_dropdown('type', $opt_type, $item->type).'<br />';

				$opt_cat = array(
					'id'		=> 'cat',
					'bâtiments'	=> 'Bâtis',
					'brico'		=> 'Bricolage',
					'vegetation'=> 'Végétations',
					'mobilier'	=> 'Mobilers',
					'deco'		=> 'Décoration',
					'cuisine'	=> 'Cuisine',
					'chambre'	=> 'Chambre',
					'sdb'		=> 'Salle de bain',
					'garage'	=> 'Garage',
					'living'	=> 'Séjour',
					'jardin'	=> 'Jardin',
					'plage'		=> 'Plage',
					'urbain'	=> 'Urbain',
					'foret'		=> 'Forêt',
					'outils'	=> 'Interface',
					'portes' 	=> 'Téléporteurs'
					);
				echo "Catégorie : ".form_dropdown('cat', $opt_cat, $item->cat).'<br />';

				$checked_s = ($item->support==1)?TRUE:FALSE;
				echo "Est support :".form_checkbox('support',1, $checked_s).'<br/>';

				$checked_d = ($item->dropable==1)?TRUE:FALSE;
				echo "Est posable sur un objet :".form_checkbox('dropable',1, $checked_d).'<br/>';

				$checked_dw = ($item->water_dropable==1)?TRUE:FALSE;
				echo "Est posable sur un l'eau :".form_checkbox('water_dropable',1, $checked_dw).'<br/>';
				
				$checked_i = ($item->infranchissable==1)?TRUE:FALSE;
				echo "Infranchissable :".form_checkbox('infranchissable',1, $checked_i).'<br/>';

				$opt_titre = array(
					'name'	=>'titre',
					'id'	=>'titre',
					'maxlength'=>'30',
					'size'	=>'30',
					'value'	=> $item->titre,
					'placeholder' => 'optionel');
				echo "Titre de l'infobulle : ".form_input($opt_titre).'<br />';

				$opt_hauteur = array(
					0	=> 0,
					1	=> 1,
					2	=> 2,
					3	=> 3,
					4	=> 4,
					5	=> 5,
					6	=> 6,
					7	=> 7,
					8	=> 8,
					9	=> 9,
					10	=> 10
					);
				echo "Hauteur : ".form_dropdown('hauteur', $opt_hauteur, $item->hauteur).'<br />';

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
				?>
				Décalage en X <input id="decx_range" name='decx' type="range" min="-600" max="350" step="1" value="<?= $item->decx ?>" oninput="set_range('x', this.value)"/>
				<input type="number" id="decx_number" value="<?= $item->decx ?>" oninput="set_number('x', this.value)"/><br/>
				Décalage en Y <input id="decy_range" name='decy' type="range" min="-600" max="350" step="1" value="<?= $item->decy ?>" oninput="set_range('y', this.value)"/>
				<input type="number" id="decy_number" value="<?= $item->decy ?>" oninput="set_number('y', this.value)"/><br/>
				<?php
				echo form_hidden('zone', $item->zone);	
				echo form_submit(array('class'=>'confirmation','name'=>'item_submit'),"Enregistrer");
				echo form_close();
				?>	
				<div id="map-index">
					<div id="total">
						<div class="lumiere">
							<div id="carte">
								<div id="ppISO">
									<div id="pp3diso-fleche-se" class="pp3diso_users fleche"></div>
									<div id="pp3diso-fleche-ne" class="pp3diso_users fleche"></div>
									<div id="pp3diso-fleche-so" class="pp3diso_users fleche"></div>
									<div id="pp3diso-fleche-no" class="pp3diso_users fleche"></div>
									<div id="vlux_sub_menu" class="pp3diso_users">
										<div class="deco">
										</div>
										<div id="display_item">
											<div class="cat vlux_display_on" >
												<div class="vlux_slider">
													<ul class="saut"> 	
														<li>
															<a href="javascript:getTool();"><?php img_tag('map/objets/tuile_zone.png', 'tuile zone',''); ?></a>
														</li>
													</ul>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div id="vlux_interface" >
								<ul id="vlux_zoom" class="pp3diso_users">
									<li id="loupe-plus" ><a href="javascript:Map.zoomPlus();"></a></li>
									<li id="loupe-moins" ><a href="javascript:Map.zoomMoins();"></a></li>
								</ul>
								<ul id="menu_std" class="vlux_menu pp3diso_users">
									<li class="marge_droite">
										<a href="javascript:reset_zone()">Effacer zone</a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div> 	
