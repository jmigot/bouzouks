<?php
$this->layout->set_title('Administration - Envoyer un email');
?>

<div id="staff-envoyer_email">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Envoyer un email</h4>
		<div class="bloc_bleu centre">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>
				
			<!-- Formulaire -->
			<?= form_open('staff/envoyer_email/ecrire') ?>
				<div class="margin">
					<table>
						<!-- De la part de -->
						<tr>
							<td>De la part de :</td>
							<td><input type="text" name="de" value="<?= set_value('de') != '' ? set_value('de') : 'Bouzouks' ?>"></td>
						</tr>

						<!-- Email expéditeur -->
						<tr>
							<td>Email expéditeur :</td>
							<td><input type="text" name="email_expediteur" value="<?= set_value('email_expediteur') != '' ? set_value('email_expediteur') : $this->bouzouk->config('email_from') ?>"></td>
						</tr>

						<!-- Email destinataire -->
						<tr>
							<td>Email destinataire :</td>
							<td><input type="text" name="email_destinataire" value="<?= set_value('email_destinataire') ?>"></td>
						</tr>
					</table>
				</div>

				<p class="hr"></p>
				
				<!-- Sujet -->
				<p class="margin">
					<span class="pourpre">Objet</span><br>
					<input type="text" name="objet" size="50" value="<?= set_value('objet') ?>">
				</p>

				<!-- Message -->
				<p>
					<span class="pourpre">Message</span><br>
					<textarea name="message" cols="70" rows="30"><?= set_value('message') ?></textarea>
				</p>

				<!-- Envoyer -->
				<p>
					<input type="submit" value="Envoyer">
				</p>
			</form>
		</div>
	</div>
</div>
