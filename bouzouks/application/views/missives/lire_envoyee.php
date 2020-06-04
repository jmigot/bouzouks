<?php $this->layout->set_title('Missives envoyée - Lire'); ?>

<div id="missives-lire">
	<!-- Menu -->
	<?php $this->load->view('missives/menu', array('lien' => 2)) ?>

	<div class="lettre">
	<!-- Supprimer -->
		<?= form_open('missives/supprimer_envoyees', array('class' => 'supprimer')) ?>
					<input type="hidden" name="ids" value="<?= $missive->id ?>">
					<button type="submit"></button>
		</form>
		<!-- Message -->
		<div class="feuille">
			<div class="tete">
			</div>
			<!-- Date -->
				<p class="date">Le <?= bouzouk_datetime($missive->date_envoi, 'court') ?></p>
				<p class="message">
				<span class="pourpre"><?= form_prep($missive->objet) ?></span><br><br>
				<?= remplace_smileys($this->lib_parser->remplace_bbcode(nl2br(tab2spaces(form_prep($missive->message))))) ?>
				</p>
			</div>
		<!-- Enveloppe -->
		<div class="enveloppe">
			<p class="timbre">
				<img src="<?= img_url('missives/timbres/'.$missive->timbre) ?>" width="62" height="73" alt="Timbre">
			</p>
			<div class="effet_lumiere">
			</div>
			<div class="contact">
			<p class="expediteur">
			De la part de <?= $this->session->userdata('pseudo') ?><br>
			<?= $this->session->userdata('adresse') ?><br>
			Vlurxtrznbnaxl
			</p>
			<p class="destinataire">
			A l'attention de <?= profil($missive->destinataire_id, $missive->destinataire_nom, $missive->destinataire_rang) ?><br>
			<?= $missive->destinataire_adresse ?><br>
			Vlurxtrznbnaxl
			</p>
			</div>
		</div>
	</div>
</div>
