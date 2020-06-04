<?php $this->layout->set_title('Toutes les entreprises'); ?>

<div id="communaute-lister_entreprises">
	<p class="centre margin"><?= $pagination ?></p>

	<?php foreach ($entreprises as $entreprise): ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4><?= form_prep($entreprise->nom) ?></h4>
			<div class="bloc_bleu">
				<!-- Image de l'objet produit -->
				<div class="fl-gauche cadre objet">
					<div class="frameborder1">
						<div class="frameborder2">
							<p class="highlight pourpre">Objet produit</p>
							<p><img src="<?= img_url($entreprise->image_url) ?>" alt="<?= $entreprise->objet_nom ?>"></p>
							<p class="pourpre margin"><?= $entreprise->objet_nom ?></p>
						</div>
					</div>
				</div>

				<!-- Infos de l'entreprise -->
				<div class="fl-gauche">
					<table>
						<tr>
							<td><p class="highlight">Position</p></td>
							<td class="pourpre">#<?= $entreprise->position ?></td>
						</tr>

						<tr>
							<td><p class="highlight">Score</p></td>
							<td class="pourpre"><?= $entreprise->score ?> points</td>
						</tr>

						<tr>
							<td><p class="highlight">Créée depuis</p></td>
							<td class="pourpre"><?= jours_ecoules($entreprise->date_creation) ?></td>
						</tr>
					</table>
				</div>

				<!-- Avatar du patron -->
				<div class="fl-droite cadre avatar">
					<div class="frameborder1">
						<div class="frameborder2">
							<p class="highlight pourpre">Patron</p>
							<p><img src="<?= img_url(avatar($entreprise->faim, $entreprise->sante, $entreprise->stress, $entreprise->perso)) ?>" alt="Perso"></p>
							<p class="margin"><?= profil($entreprise->chef_id, $entreprise->pseudo, $entreprise->rang) ?></p>
						</div>
					</div>
				</div>

				<p class="clearfloat"></p>
			</div>
		</div>
	<?php endforeach; ?>

	<p class="centre margin"><?= $pagination ?></p>
</div>
