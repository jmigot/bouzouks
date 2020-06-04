<?php $this->layout->set_title('Administration - Multicomptes'); ?>

<div id="staff-multicomptes-marche_noir">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Multicomptes - Marché noir</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<p class="margin centre">Les ventes basses du marché noir</p>

			<table>
				<tr>
					<th>Date</th>
					<th>Vendeur</th>
					<th>Acheteur</th>
					<th>Objet</th>
					<th>Qté</th>
					<th>Pér.</th>
					<th>Prix</th>
				</tr>

				<tr>
					<td colspan="7"><p class="hr"></p></td>
				</tr>
					
				<?php foreach ($ventes as $vente): ?>
					<tr>
						<td class="pourpre"><?= bouzouk_datetime($vente->date, 'court', false) ?></td>
						<td><p class="highlight"><?= profil($vente->vendeur_id, $vente->vendeur_pseudo) ?></p></td>
						<td><p class="highlight"><?= profil($vente->acheteur_id, $vente->acheteur_pseudo) ?></p></td>
						<td><?= mb_substr($vente->nom, 0, 19) ?><?= mb_substr($vente->nom, 19) != '' ? '.' : '' ?></td>
						<td><?= $vente->quantite ?></td>
						<td><?= $vente->peremption ?></td>
						<td><?= struls($vente->prix) ?></td>
					</tr>

					<tr>
						<td colspan="7"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
</div>
 
