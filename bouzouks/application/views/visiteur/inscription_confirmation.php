<?php $this->layout->set_title("Confirmation d'inscription"); ?>

<div class="cellule_bleu_type1 marge_haut">
	<h4>Confirmer mon inscription</h4>
	<div class="bloc_bleu" id="visiteur-inscription_confirmation">
		<p class="margin-petit">Entre ci-dessous ton pseudo et le code qui t'a été donné dans le mail.</p>

		<!-- Formulaire -->
		<?= form_open('visiteur/inscription_confirmation') ?>
			<table class="entier tab_separ">
				<!-- Pseudo -->
				<tr>
					<td class="frameborder_bleu droite moitie"><label for="pseudo">Pseudo</label></td>
					<td class="frameborder_bleu"><input type="text" name="pseudo" id="pseudo" maxlength="15" value="<?= set_value('pseudo') ?>" placeholder="Ton pseudo"></td>
				</tr>

				<!-- Code d'activation -->
				<tr>
					<td class="frameborder_bleu droite moitie"><label for="code">Code</label></td>
					<td class="frameborder_bleu"><input type="text" name="code" id="code" maxlength="8" value="<?= set_value('code') ?>" placeholder="Celui du mail"></td>
				</tr>

				<!-- Valider -->
				<tr>
					<td></td>
					<td><input type="submit" value="Valider"></td>
			</table>
		</form>
	</div>
</div>
