<?php $this->layout->set_title('Modération - Parrainages'); ?>

<div id="staff-moderer_parrainages">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Modérer les parrainages</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>
			
			<p class="margin centre noir">
				Les statuts sont affichés : un joueur game over ou banni ne devrait pas figurer ici<br>
				et NE DOIT PAS recevoir de récompense, dans un sens ou dans l'autre<br><br>

				Les parrainages ne peuvent être validés que quand le filleul devient actif<br>
				et qu'il n'est pas tombé game over entre temps.
			</p>

			<table>
				<tr>
					<th>Date</th>
					<th>Parrain</th>
					<th>Filleul</th>
					<th>Valider</th>
				</tr>

				<tr>
					<td colspan="5"><p class="hr"></p></td>
				</tr>

				<?php foreach ($parrainages as $parrainage): ?>
					<?= form_open('staff/moderer_parrainages/modifier') ?>
						<input type="hidden" name="parrainage_id" value="<?= $parrainage->id ?>">

						<tr>
							<td class="noir"><?= bouzouk_datetime($parrainage->date, 'court') ?></td>
							<td><p class="highlight"><?= profil($parrainage->parrain_id, $parrainage->parrain_pseudo, $parrainage->parrain_rang) ?> [<?= $statuts[$parrainage->parrain_statut] ?>]</p></td>
							<td><p class="highlight"><?= profil($parrainage->filleul_id, $parrainage->filleul_pseudo) ?> [<?= $statuts[$parrainage->filleul_statut] ?>]</p></td>
							<td>
								<?php if (in_array($parrainage->filleul_statut, array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))): ?>
									<input type="submit" name="valider" value="Valider" class="confirmation">
								<?php endif; ?>

								<input type="submit" name="refuser" value="Refuser" class="confirmation">
							</td>
						</tr>

						<tr>
							<td colspan="5"><p class="hr"></p></td>
						</tr>
					</form>
				<?php endforeach; ?>
			</table>
		</div>
	</div>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Les dernières invitations envoyées par email</h4>
		<div class="bloc_bleu">
			<table>
				<tr>
					<th>Date</th>
					<th>Joueur</th>
					<th>Email</th>
					<th>Joueur inscrit</th>
				</tr>

				<tr>
					<td colspan="5"><p class="hr"></p></td>
				</tr>

				<?php foreach ($demandes_email as $demande): ?>
					<tr>
						<td class="noir"><?= bouzouk_datetime($demande->date, 'court') ?></td>
						<td><p class="highlight"><?= profil($demande->joueur_id, $demande->joueur_pseudo, $demande->joueur_rang) ?></p></td>
						<td><p class="highlight pourpre"><?= form_prep($demande->email) ?></p></td>
						<td><p class="highlight"><?= isset($demande->filleul_id) ? profil($demande->filleul_id, $demande->filleul_pseudo) : '-' ?></p></td>
					</tr>

					<tr>
						<td colspan="5"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
</div>
