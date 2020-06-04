<?php $this->layout->set_title('Admin - Dons Paypal'); ?>

<div id="staff-dons_paypal">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Dons Paypal - Liste des dons par date</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>
			
			<table>
				<tr>
					<th>Date</th>
					<th>Joueur</th>
					<th>Montant</th>
					<th>Récompense</th>
				</tr>

				<tr>
					<td colspan="4"><p class="hr"></p></td>
				</tr>
				
				<?php foreach ($dons as $don): ?>
					<tr>
						<td><p class="highlight"><?= bouzouk_datetime($don->date, 'court', false) ?></p></td>
						<td><?= profil($don->id, $don->pseudo) ?></td>
						<td><p class="highlight"><?= $don->montant ?> €</p></td>
						<td class="pourpre"><?= isset($don->nom) ? $don->nom : (isset($don->struls) ? struls($don->struls) : '<i>aucun</i>') ?></td>
					</tr>

					<tr>
						<td colspan="4"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>
			</table>

			<!-- Pagination -->
			<p class="centre"><?= $pagination ?></p>
		</div>
	</div>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Ajouter un donneur</h4>
		<div class="bloc_bleu donner">
			<?= form_open('staff/dons_paypal/ajouter') ?>
				<table>
					<tr>
						<td><p class="highlight">Pseudo (id)</p></td>
						<td><input type="text" name="joueur"></td>
					</tr>

					<tr>
						<td><p class="highlight">Montant €</p></td>
						<td><input type="text" name="montant"></td>
					</tr>

					<tr>
						<td></td>
						<td><input type="submit" value="Ajouter"></td>
				</table>
			</form>

			<p class="gras rouge centre margin">Bien vérifier que le don est terminé sur Paypal, on peut payer avec retardement et se rétracter</p>
		</div>
	</div>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Dons Paypal - Infos</h4>
		<div class="bloc_bleu">
			<p class="margin">
				Montant total payé : <span class="pourpre"><?= round($dons_paypal->montant_total) ?> €</span><br>
				Nombre de joueurs ayant utilisé le service : <span class="pourpre"><?= $dons_paypal->joueurs_total ?> joueurs sur <?= $dons_paypal->nb_joueurs ?> (<?= $dons_paypal->pourcentage_joueurs ?>%)</span>
			</p>
		</div>
	</div>
</div>
