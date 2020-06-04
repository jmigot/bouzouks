<?php $this->layout->set_title("Changement d'email"); ?>

<!-- Menu -->
<?php $this->load->view('mon_compte/menu', array('lien' => 1)) ?>

<div class="cellule_bleu_type1 marge_haut">
	<h4>Changer mon adresse email</h4>
	<div class="bloc_bleu">
		<p>Entre ci-dessous ton pseudo et le code qui t'a été donné dans le mail.</p>

		<!-- Formulaire -->
		<?= form_open('mon_compte/changer_email_confirmation') ?>
			<table>
				<!-- Code d'activation -->
				<tr>
					<td><label for="code">Code</label></td>
					<td><input type="text" name="code" id="code" maxlength="8" value="<?= set_value('code') ?>" placeholder="Celui donné dans le mail"></td>
				</tr>

				<!-- Valider -->
				<tr>
					<td></td>
					<td><input type="submit" value="Valider"></td>
			</table>
		</form>
	</div>
</div>

