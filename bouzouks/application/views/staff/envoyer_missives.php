<?php
$this->layout->set_title('Administration - Envoyer des missives');
$this->layout->ajouter_javascript('missives.js');
?>

<div id="missives-ecrire">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Envoyer des missives</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>
				
			<!-- Formulaire -->
			<?= form_open('staff/envoyer_missives/ecrire', array('class' => 'lettre')) ?>
				<input type="hidden" name="expediteur_robot" value="1">

				<!-- Image du timbre -->
				<p class="timbre_image">
					<?php $images_timbres = array_keys($timbres); $premier_timbre_image_url = $images_timbres[0]; ?>
					<img src="<?= img_url('missives/timbres/'.$premier_timbre_image_url) ?>" width="62" height="73" alt="Timbre">
				</p>

				<!-- Choix du timbre -->
				<p class="timbre">
					<select name="timbre" size="5">
						<?php foreach ($timbres as $image_url => $timbre_array): ?>
							<option value="<?= $image_url ?>"<?php if ($image_url == $premier_timbre_image_url) echo ' selected'; ?>><?= $timbre_array['titre'] ?></option>
						<?php endforeach; ?>
					</select>
				</p>

				<!-- Destinataire -->
				<p class="joueur">
					Destinataire
					<?= $select_destinataires ?>
				</p>

				<!-- Expéditeur -->
				<p class="joueur">
					De la part de<br>
					<?= $select_expediteurs ?><br>
					Vlurxtrznbnaxl
				</p>

				<div class="bloc">
					<div>
						<!-- Objet -->
						<p>
							Objet<br>
							<input type="text" name="objet" maxlength="60" value="<?= set_value('objet') ?>" placeholder="Ton sujet" size="30" class="objet">
						</p>

						<!-- Intro -->
						<p>
							<select name="intro" class="intro">
								<?php foreach ($intros as $index => $titre): ?>
									<option value="<?= $index ?>"<?= set_value('intro') == $index ? ' selected' : '' ?>><?= $titre ?></option>
								<?php endforeach; ?>
							</select>
						</p>

						<?= $this->lib_parser->bbcode('message') ?>

						<!-- Message -->
						<p><textarea name="message" id="message" class="message compte_caracteres" rows="12" cols="45" maxlength="5000" placeholder="Ton message"><?= set_value('message') ?></textarea></p>
					</div>

					<p id="message_nb_caracteres_restants" class="transparent centre">&nbsp;</p>

					<?= $table_smileys; ?>

					<p>
						<!-- Politesse -->
						<select name="politesse" class="politesse">
							<?php foreach ($politesses as $index => $titre): ?>
							<option value="<?= $index ?>"<?= set_value('politesse') == $index ? ' selected' : '' ?>><?= $titre ?></option>
							<?php endforeach; ?>
						</select>
					</p>

					<!-- Envoyer -->
					<p><input type="submit" value="Envoyer la missive" class="confirmation"></p>
				</div>
			</form>

			<p class="clearfloat"></p>
		</div>
	</div>
</div>

<!-- Prévisualisation -->
<div id="popup" class="invisible">
</div>
