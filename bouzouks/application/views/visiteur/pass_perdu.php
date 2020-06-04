<?php $this->layout->set_title('Mot de passe perdu'); ?>

<div class="cellule_bleu_type1 marge_haut">
	<h4>J'ai perdu mon mot de passe</h4>
	<div class="bloc_bleu" id="visiteur-pass_perdu">
		<p class="mini_bloc">Tu as perdu ton mot de passe ? Médor l'a mangé ?!</p>
		<p class="margin-petit">Pas de problème ! Pour en récupérer un nouveau, il te suffit d'entrer ton pseudo et ton e-mail ci-dessous ;)</p>

		<!-- Formulaire -->
		<?= form_open('visiteur/pass_perdu') ?>
			<table class="entier tab_separ">
				<!-- Pseudo -->
				<tr>
					<td class="frameborder_bleu droite moitie"><label for="pseudo">Ton pseudo</label></td>
					<td class="frameborder_bleu"><input type="text" name="pseudo" id="pseudo" maxlength="15" value="<?= set_value('pseudo') ?>"></td>
				</tr>

				<!-- Email -->
				<tr>
					<td class="frameborder_bleu droite moitie"><label for="email">Ton email</label></td>
					<td class="frameborder_bleu"><input type="email" name="email" id="email" maxlength="320" value="<?= set_value('email') ?>"></td>
				</tr>

				<!-- Valider -->
				<tr>
					<td colspan="2" class="centre"><input type="submit" value="Recuperer mon mot de passe"></td>
				</tr>
			</table>
		</form>

		<p class="lien margin"><a href="<?= site_url('visiteur/pass_perdu_confirmation') ?>">Changer de mot de passe avec un code reçu</a></p>
	</div>
</div>
