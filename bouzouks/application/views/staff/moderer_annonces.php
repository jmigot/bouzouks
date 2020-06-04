<?php $this->layout->set_title('Modération - Annonces'); ?>

<div id="staff-moderer_annonces">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Modérer les annonces</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>
			
			<p class="centre margin"><?= $pagination ?></p>

			<table>
				<tr>
					<th>Nom</th>
					<th>Patron</th>
					<th>Date</th>
					<th>Modifier</th>
					<th>Supprimer<th>
				</tr>

				<tr>
					<td colspan="5"><p class="hr"></p></td>
				</tr>

				<?php foreach ($annonces as $annonce): ?>
					<?= form_open('staff/moderer_annonces/modifier') ?>
						<input type="hidden" name="annonce_id" value="<?= $annonce->id ?>">

						<tr>
							<td class="pourpre gras"><?= $annonce->nom ?></td>
							<td><?= profil($annonce->chef_id, $annonce->chef_pseudo) ?></td>
							<td><?= bouzouk_datetime($annonce->date_annonce, 'court') ?></td>
							<td><input type="submit" name="modifier" value="Modifier"></td>
							<td><input type="submit" name="supprimer" value="Supprimer"></td>
						</tr>

						<tr>
							<td colspan="5"><textarea name="message" maxlength="250"><?= form_prep($annonce->message) ?></textarea></td>
						</tr>

						<tr>
							<td colspan="5"><p class="hr"></p></td>
						</tr>
					</form>
				<?php endforeach; ?>
			</table>

			<p class="centre margin"><?= $pagination ?></p>
		</div>
	</div>
</div>




