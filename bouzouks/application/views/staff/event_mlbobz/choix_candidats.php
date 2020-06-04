<?php 
	$this->layout->set_title($title);
 ?>
 <div id="staff-gerer_event">
 	<div class="cellule_bleu_type1 marge_haut">
 		<h4><?= $title ?></h4>
 		<div class="bloc_bleu">

 			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

 			<?php
 				echo form_open('staff/gerer_event_mlbobz/validation_candidats'); 
 				echo form_fieldset("Choisir le candidat");
 			?>
 				<p class="centre">
 					Candidat :
 					<?= $select_joueurs_candidat ?><br/>
 					Suppléant :
 					<?= $select_joueurs_suppleant ?><br/>

 					<input type="submit" name="maudire_mlbobz" value="Boobzer">
 				</p>
 			<?php
 				echo form_fieldset_close();
 				echo form_close();
 			?>
 		</div>
 	</div>
 </div>