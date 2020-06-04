<?php
$this->layout->set_title('Inscription');
$this->layout->ajouter_javascript('visiteur.js');
?>

<div id="visiteur-inscription">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Inscription à Bouzouks.net</h4>

		<div class="bloc_bleu padd_vertical">
			<p class="mini_bloc">Ne reste pas tout seul dans ton coin... Rejoins nous !</p>
			<!-- Formulaire -->
			<?= form_open('visiteur/'.$validation_callback) ?>
				<table class="tab_separ">

					<tr>
						<td colspan="2"><p class="attention">L'inscription est simple, tu as juste à remplir les 2/3 bidules machins choses en dessous là...</p></td>
					</tr>

					<!-- Pseudo -->
					<tr>
						<td class="frameborder_bleu"><label for="pseudo" class="label">Pseudo :</label></td>
						<td class="frameborder_bleu"><input type="text" name="pseudo" id="pseudo" maxlength="15" value="<?= set_value('pseudo') ?>" placeholder="Nom de ton bouzouk"></td>
					</tr>

					<!-- Mot de passe -->
					<tr>
						<td class="frameborder_bleu"><label for="mot_de_passe" class="label">Mot de passe :</label></td>
						<td class="frameborder_bleu"><input type="password" name="mot_de_passe" id="mot_de_passe" maxlength="30" placeholder="6 caractères minimum"></td>
					</tr>

					<!-- Adresse e-mail -->
					<?php if($validation_callback == 'inscription' ): ?>
					<tr>
						<td class="frameborder_bleu"><label for="email" class="label">Adresse email :</label></td>
						<td class="frameborder_bleu"><input type="email" name="email" id="email" maxlength="320" value="<?= set_value('email') ?>" placeholder="Indispensable"></td>
					</tr>
					<?php endif; ?>
					<tr>
						<td colspan="2"><p class="attention">Attention : bouzouks.net est un site interdit aux enfants de moins de 11 ans et demi... !!!</p></td>
					</tr>

					<!-- Age minimum -->
					<tr>
						<td class="frameborder_bleu"><p class="label">Je certifie donc avoir plus de 11 ans et demi</p></td>
						<td class="frameborder_bleu">
							<input type="checkbox" name="age" id="age" value="0" <?= set_checkbox('age', '0') ?>>
							<label for="age"><strong>Oui</strong>, je le jure et je ne croise pas les doigts</label>
						</td>
					</tr>

					<!-- Charte du jeu -->
					<tr>
						<td class="frameborder_bleu"><p class="label">J'ai lu et j'accepte la <a href="<?= site_url('site/charte') ?>" class="charte">charte du jeu</a></p></td>
						<td class="frameborder_bleu">
							<input type="checkbox" name="charte" id="charte" value="0" <?= set_checkbox('charte', '0') ?>>
							<label for="charte"><strong>Oui</strong>, je le jure et je ne croise toujours pas les doigts</label>
						</td>
					</tr>

					<!-- Jamais 2 sans 3 -->
					<tr>
						<td class="frameborder_bleu"><p class="label">Eh, jamais 2 sans 3...</p></td>
						<td class="frameborder_bleu">
							<input type="checkbox" name="derniere" id="derniere" value="0" <?= set_checkbox('derniere', '0') ?>>
							<label for="derniere"><strong>Oui</strong>, je le juuuuure !!!</label>
						</td>
					</tr>

					<!-- Parrain -->
					<tr>
						<td class="frameborder_gris"><p class="label pourpre">Pseudo de mon parrain</p></td>
						<td class="frameborder_gris"><input type="text" name="parrain" id="parrain" maxlength="15" value="<?= set_value('parrain') != '' ? set_value('parrain') : form_prep(urldecode($this->uri->segment(3, ''))) ?>" placeholder="Optionnel"></td>
					</tr>

					<!-- Envoyer -->
					<tr>
						<td></td>
						<td><input type="submit" value="S'inscrire"></td>
					</tr>
				</table>
			</form>
		</div>
	</div>

	<div id="charte_div">
		<!-- Charte -->
		<div class="cellule_gris_type2 marge_haut">
			<h4>Charte du jeu</h4>
			<?php $this->load->view('site/charte_articles'); ?>
		</div>
	</div>
</div>