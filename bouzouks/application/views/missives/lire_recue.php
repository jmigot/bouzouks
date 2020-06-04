<?php
$this->layout->set_title('Missives Reçues - Lire');
$this->layout->ajouter_javascript('missives.js');
?>

<div id="missives-lire">
	<!-- Menu -->
	<?php $this->load->view('missives/menu', array('lien' => 1)) ?>
	
	<div class="lettre">
		<!-- Répondre -->
		<?php if ( ! $expediteur_robot): ?>
			<?= form_open('missives/repondre/'.$missive->expediteur_id, array('class' => 'repondre')) ?>
				<input type="hidden" name="objet" value="<?= form_prep($missive->objet) ?>">
				<input type="hidden" name="message_original" value="<?= form_prep($missive->message) ?>">
				<button type="submit"></button>
			</form>
		<?php endif; ?>

		<!-- Supprimer -->
		<?= form_open('missives/supprimer_recues', array('class' => 'supprimer')) ?>
			<input type="hidden" name="ids" value="<?= $missive->id ?>">
			<button type="submit"></button>
		</form>

		<!-- Message -->
		<div class="feuille">
			<div class="tete">
			</div>
			<!-- Date -->
				<p class="date">Le <?= bouzouk_datetime($missive->date_envoi, 'court') ?></p>
				<div class="message">
					<!-- Objet -->
					<span class="pourpre"><?= form_prep($missive->objet) ?></span><br><br><br>
					<!-- Message -->
					<?php
						// Si l'expéditeur n'est pas un robot, on protège
						if ( ! $expediteur_robot)
						{
							$missive->message = form_prep($missive->message);
						}

						echo remplace_smileys($this->lib_parser->remplace_bbcode(nl2br(tab2spaces($missive->message))));
					?>
				</div>
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
				De la part de <?= profil($missive->expediteur_id, $missive->expediteur_nom, $missive->expediteur_rang) ?><br>
				<?= $missive->expediteur_adresse ?><br>
				Vlurxtrznbnaxl
			</p>
			<p class="destinataire">
				A l'attention de <?= $this->session->userdata('pseudo') ?><br>
				<?= $this->session->userdata('adresse') ?><br>
				Vlurxtrznbnaxl
			</p>
			</div>
		</div>
	</div>

	<p class="clearfloat">
	<ul>
		<li class="rouge">Les administrateurs ne te demanderont jamais ton mot de passe par missive !</li>
		<li>Les missives injurieuses sont strictement interdites. En cas de réception d'une missive d'insulte, contacte un administrateur en précisant la date du message.</li>
		<li><b>Note aux jeunes bouzouks :</b> n'accepte pas le Skype ou l'email d'une personne inconnue !</li>
	</ul>
</div>
