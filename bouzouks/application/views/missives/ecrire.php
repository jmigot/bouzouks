<?php
$this->layout->set_title('Missives - Ecrire');
$this->layout->ajouter_javascript('missives.js');
?>

<div id="missives-ecrire">
	<!-- Menu -->
	<?php $this->load->view('missives/menu', array('lien' => 3)) ?>

	<?php if ($this->session->userdata('interdit_missives') == 1): ?>
		<div class="highlight">
			<p class="gras centre rouge margin">Tu as été interdit de missives par un modérateur ou un administrateur.</p>
		</div>
	<?php endif; ?>

	<?= form_open('missives/ecrire', array('class' => 'lettre')) ?>
		<!-- Enveloppe -->
		<div class="enveloppe">
			<!-- Formulaire -->
			<div class="formulaire">
				<input type="hidden" name="expediteur_robot" value="0">
				<input type="hidden" name="missive_originale" value="<?= form_prep($message_original) ?>">

				<!-- Image du timbre -->
				<p class="timbre_image">
					<?php $images_timbres = array_keys($timbres); $premier_timbre_image_url = $images_timbres[0]; $timbre = (set_value('timbre') != '') ? set_value('timbre') : $premier_timbre_image_url; ?>
					<img src="<?= img_url('missives/timbres/'.$timbre) ?>" width="62" height="73" alt="Timbre">
				</p>

				<!-- Choix du timbre -->
				<p class="timbre">
					<select name="timbre" size="5">
						<?php foreach ($timbres as $image_url => $timbre_array): ?>
							<option value="<?= $image_url ?>"<?php if (set_value('timbre') == $image_url || $image_url == $premier_timbre_image_url) echo ' selected'; ?>><?= $timbre_array['titre'] ?></option>
						<?php endforeach; ?>
					</select>
				</p>

				<!-- Destinataire -->
				<p class="joueur">
					Destinataire<br>
					<?= $select_destinataires ?>
				</p>

				<!-- Expéditeur -->
				<p class="expediteur">
					De la part de <?= $this->session->userdata('pseudo') ?><br>
					<?= $this->session->userdata('adresse') ?><br>
					Vlurxtrznbnaxl
				</p>
			</div>
		</div>
	
		<!-- Message -->
		<div class="feuille">
			<div class="tete">
			</div>
		
			<div class="bloc">
				<div>
					<!-- Objet -->
					<p>
						Objet<br>
						<input type="text" name="objet" maxlength="60" value="<?php if ($objet == '') echo set_value('objet'); else echo form_prep($objet); ?>" placeholder="Ton sujet" size="30" class="objet">
					</p>
					
					<div class="bbcode">
						<?= $this->lib_parser->bbcode('message') ?>
					</div>
					
					<!-- Intro -->
					<p>
						<select name="intro" class="intro">
							<?php foreach ($intros as $index => $titre): ?>
								<option value="<?= $index ?>"<?= set_value('intro') == $index ? ' selected' : '' ?>><?= $titre ?></option>
							<?php endforeach; ?>
						</select>
					</p>

					<!-- Message -->
					<p>
						<textarea name="message" id="message" class="message compte_caracteres" rows="12" cols="45" maxlength="5000" placeholder="Ton message"><?= set_value('message') ?></textarea>
					</p>
				</div>
	
				<p id="message_nb_caracteres_restants" class="transparent centre">&nbsp;</p>
				<p>
					<!-- Politesse -->
					<select name="politesse" class="politesse">
						<?php foreach ($politesses as $index => $titre): ?>
						<option value="<?= $index ?>"<?= set_value('politesse') == $index ? ' selected' : '' ?>><?= $titre ?></option>
						<?php endforeach; ?>
					</select>
				</p>
	
				<div class="smileys">
					<?= $table_smileys; ?>
				</div>
	
				<!-- Envoyer -->
				<p class="btn_envoi"><input type="submit" class="bouton_rouge" value="Envoyer la missive"></p>
			</div>
			
			<?php if ($message_original != ''): ?>
				<div class="text_origine">
					<p class="gras">Missive originale</p>
					<p class="margin"><?= remplace_smileys($this->lib_parser->remplace_bbcode(nl2br(tab2spaces(form_prep($message_original))))) ?></p>
				</div>
			<?php endif; ?>
		</div>
	</form>	
</div>

<!-- Prévisualisation -->
<div id="popup" class="invisible">
</div>
