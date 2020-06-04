<?php $this->layout->set_title('Changement de mot de passe'); ?>

<div class="cellule_bleu_type1 marge_haut">
	<h4>Changer mon mot de passe</h4>

	<div class="bloc_bleu" id="visiteur-pass_perdu_confirmation">
		<p class="margin-petit">Entre ci-dessous ton pseudo, le code qui t'a été donné dans le mail et choisis ton nouveau mot de passe.</p>

		<!-- Formulaire -->
		<?= form_open('visiteur/pass_perdu_confirmation') ?>
			<table class="entier tab_separ">
				<!-- Pseudo -->
				<tr>
					<td class="frameborder_bleu droite moitie"><label for="pseudo">Pseudo</label></td>
					<td class="frameborder_bleu"><input type="text" name="pseudo" id="pseudo" maxlength="15" value="<?= set_value('pseudo') ?>" placeholder="Ton pseudo"></td>
				</tr>

				<!-- Code d'activation -->
				<tr>
					<td class="frameborder_bleu droite moitie"><label for="code">Code</label></td>
					<td class="frameborder_bleu"><input type="text" name="code" id="code" maxlength="8" value="<?= set_value('code') ?>" placeholder="Celui donné dans le mail"></td>
				</tr>

				<!-- Mot de passe -->
				<tr>
					<td class="frameborder_bleu droite moitie"><label for="mot_de_passe">Mot de passe</label></td>
					<td class="frameborder_bleu"><input type="password" name="mot_de_passe" id="mot_de_passe" maxlength="30" placeholder="6 caractères minimum"></td>
				</tr>

				<!-- Valider -->
				<tr>
					<td colspan="2" class="centre"><input type="submit" value="Modifier mon mot de passe"></td>
			</table>
		</form>
	</div>
</div>
