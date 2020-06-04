<?php $this->layout->set_title('Admin - Plus de struls'); ?>

<div id="staff-plus_de_struls">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Plus de struls - Liste des dons par date</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>
			
			<table>
				<tr>
					<th>Date</th>
					<th>Joueur</th>
					<th>Struls</th>
					<th>Montant</th>
					<th>Code</th>
				</tr>

				<tr>
					<td colspan="5"><p class="hr"></p></td>
				</tr>
				
				<?php foreach ($dons as $don): ?>
					<tr>
						<td><p class="highlight"><?= bouzouk_datetime($don->date, 'court', false) ?></p></td>
						<td><?= profil($don->id, $don->pseudo) ?></td>
						<td><p class="highlight"><?= $don->struls ?> struls</p></td>
						<td class="pourpre"><?= $don->montant ?> €</td>
						<td><?= $don->code ?></td>
					</tr>

					<tr>
						<td colspan="5"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>
			</table>

			<!-- Pagination -->
			<p class="centre"><?= $pagination ?></p>
		</div>
	</div>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Les meilleurs donneurs</h4>
		<div class="bloc_bleu">
			<table>
				<tr>
					<th>Joueur</th>
					<th>Nombre de dons</th>
					<th>Montant total</th>
					<th>Struls total</th>
				</tr>

				<tr>
					<td colspan="4"><p class="hr"></p></td>
				</tr>

				<?php foreach ($dons_groupes as $don): ?>
					<tr>
						<td><?= profil($don->id, $don->pseudo) ?></td>
						<td><p class="highlight"><?= pluriel($don->nb_dons, 'don') ?></p></td>
						<td class="pourpre"><?= round($don->montant_total) ?> €</td>
						<td><?= $don->struls_total ?> struls</td>
					</tr>

					<tr>
						<td colspan="4"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Plus de struls - Infos</h4>
		<div class="bloc_bleu">
			<p class="margin">
				Montant total payé : <span class="pourpre"><?= round($plus_de_struls->montant_total) ?> €</span><br>
				Struls injectés dans le jeu : <?= struls($plus_de_struls->struls_total) ?><br>
				Nombre de joueurs ayant utilisé le service : <span class="pourpre"><?= $plus_de_struls->joueurs_total ?> joueurs sur <?= $plus_de_struls->nb_joueurs ?> (<?= $plus_de_struls->pourcentage_joueurs ?>%)</span>
			</p>
		</div>
	</div>
</div>
