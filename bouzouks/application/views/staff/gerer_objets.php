<?php
$this->layout->set_title('Admin - Objets');
$this->layout->ajouter_javascript('staff/objets.js');
?>

<div id="staff-gerer_objets">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Gestion des objets [Faim, Santé, Stress]</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<table>
				<tr>
					<th>Nom</th>
					<th>Rentabilité</th>
					<th>Faim</th>
					<th>Santé</th>
					<th>Stress</th>
					<th>Prix</th>
					<th>Péremption</th>
					<th>Modifier</th>
				</tr>

				<tr>
					<td colspan="8"><p class="hr"></p></td>
				</tr>

				<?php foreach ($objets_fss as $objet): ?>
					<?= form_open('staff/gerer_objets/modifier_objet_fss') ?>
						<input type="hidden" name="objet_id" value="<?= $objet->id ?>">
						<tr>
							<td><input type="text" name="nom" value="<?= $objet->nom ?>" size="12"></td>
							<td><p class="highlight"><span class="objet_<?= $objet->id ?>"><?= $objet->rentabilite ?></span></p></td>
							<td><input type="text" name="faim" size="2" class="objet_<?= $objet->id ?>" value="<?= $objet->faim ?>"></td>
							<td><input type="text" name="sante" size="2" class="objet_<?= $objet->id ?>" value="<?= $objet->sante ?>"></td>
							<td><input type="text" name="stress" size="2" class="objet_<?= $objet->id ?>" value="<?= $objet->stress ?>"></td>
							<td><input type="text" name="prix" size="2" class="objet_<?= $objet->id ?>" value="<?= $objet->prix ?>"> struls</td>
							<td><input type="text" name="peremption" value="<?= $objet->peremption ?>" size="2"> jours</td>
							<td><input type="submit" value="Modifier"></td>
						</tr>
					</form>

					<tr>
						<td colspan="8"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Gestion des objets [Boost]</h4>
		<div class="bloc_bleu">
			<table>
				<tr>
					<th>Nom</th>
					<th>Péremption</th>
					<th>Expérience</th>
					<th>Quantité max</th>
					<th>Prix</th>
					<th>Péremption</th>
					<th>Modifier</th>
				</tr>

				<tr>
					<td colspan="8"><p class="hr"></p></td>
				</tr>

				<?php foreach ($objets_boost as $objet): ?>
					<?= form_open('staff/gerer_objets/modifier_objet_boost') ?>
						<input type="hidden" name="objet_id" value="<?= $objet->id ?>">
						<tr>
							<td><input type="text" name="nom" value="<?= $objet->nom ?>" size="12"></td>
							<td><input type="text" name="jours_peremption" size="2" class="objet_<?= $objet->id ?>" value="<?= $objet->jours_peremption ?>"> <span class="pourpre">jours</span></td>
							<td><input type="text" name="experience" size="2" class="objet_<?= $objet->id ?>" value="<?= $objet->experience ?>"> <span class="pourpre">xp</span></td>
							<td><input type="text" name="quantite_max" size="2" class="objet_<?= $objet->id ?>" value="<?= $objet->quantite_max ?>"></td>
							<td><input type="text" name="prix" size="2" class="objet_<?= $objet->id ?>" value="<?= $objet->prix ?>"> <span class="pourpre">struls</span></td>
							<td><input type="text" name="peremption" value="<?= $objet->peremption ?>" size="2"> <span class="pourpre">jours</span></td>
							<td><input type="submit" value="Modifier"></td>
						</tr>
					</form>

					<tr>
						<td colspan="7"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Quantités d'ojets dans le jeu</h4>
		<div class="bloc_bleu quantites">
			<table>
				<tr>
					<th>Nom</th>
					<th>Magasins</th>
					<th>Maisons</th>
					<th>Marché noir</th>
					<th>Total</th>
				</tr>

				<tr>
					<td colspan="5"><p class="hr"></p></td>
				</tr>

				<?php foreach ($quantites_objets as $objet): ?>
					<tr>
						<td><p class="highlight"><?= $objet->nom ?></p></td>
						<td><?= $objet->quantite_magasins ?></td>
						<td><?= $objet->quantite_maisons ?></td>
						<td><?= $objet->quantite_marche_noir ?></td>
						<td class="pourpre"><?= $objet->quantite_totale ?></td>
					</tr>

					<tr>
						<td colspan="5"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
</div>
