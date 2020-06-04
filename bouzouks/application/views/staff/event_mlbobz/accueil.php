<?php 
	$this->layout->set_title($title);
 ?>
 <div id="staff-gerer_event">
 	<div class="cellule_bleu_type1 marge_haut">
 		<h4><?= $title ?></h4>
 		<div class="bloc_bleu">

 			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

 			<?php 
 			if(!$etat_event){
 				echo(form_open('staff/gerer_event_mlbobz/lancer_event'));
 				echo ('<p class="centre"><input type="submit" value="Lancer l\'envent" class="confirmation"></p>');
 				echo form_close();
 			}
 			elseif($etat_event){
 				echo(form_open('staff/gerer_event_mlbobz/stop_event'));
 				echo('<p class="centre"><input type="submit" value="Arrêter l\'event" class="confirmation"></p>');
 				echo form_close().'<br/>';
 				
 				echo form_open('staff/gerer_event_mlbobz/malediction_mlbobz'); 
 				echo form_fieldset("Boobzer un joueur");
 				?>
 				<p class="centre">
 					Pseudo :
 					<?= $select_joueurs ?><br/>
 					<input type="submit" name="maudire_mlbobz" value="Bizouter">
 				</p>
 				<?php
 				echo form_fieldset_close();
 				echo form_close();
 			}
 			elseif(ENVIRONMENT == 'development'){
 				show_error(" Etat $etat invalide", 500);
 			}
 			?>
 		</div>
 	</div>
 </div>