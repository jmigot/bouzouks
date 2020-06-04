<?php $this->layout->set_title('Administration - Historique'); ?>

<div id="staff-historique_moderation">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Historique de modération Tobozon</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>
			
			<p class="centre"><?= $pagination ?></p>

			<table class="lignes">
				<tr>
					<th>Date</th>
					<th>Auteur</th>
					<th>Modérateur</th>
					<th>Lien</th>
					<th>Détails</th>
				</tr>

				<tr>
					<td colspan="5"><p class="hr"></p></td>
				</tr>

				<?php foreach ($historique as $ligne): ?>
					<tr>
						<td><p class="highlight"><?= bouzouk_datetime($ligne->date, 'court', false) ?></p></td>
						<td><?= profil($ligne->poster_id, $ligne->poster_pseudo, $ligne->poster_rang) ?></td>
						<td><?= profil($ligne->moderateur_id, $ligne->moderateur_pseudo, $ligne->moderateur_rang) ?></td>
						<td>
							<?php if (isset($ligne->lien_id)): ?>
								<?php if (isset($ligne->moderateur_texte)): ?>
									<a href="<?= site_url('tobozon/viewtopic.php?pid='.$ligne->lien_id.'#p'.$ligne->lien_id) ?>">Modifié</a>
								<?php else: ?>
									<a href="<?= site_url('tobozon/viewtopic.php?id='.$ligne->lien_id) ?>">Supprimé</a>
								<?php endif; ?>
							<?php else: ?>
								<span class="noir">Supprimé</span>
							<?php endif; ?>
						</td>
						<td><a href="<?= site_url('staff/historique_moderation/tobozon_details/'.$ligne->id) ?>">Détails</a></td>
					</tr>

					<tr>
						<td colspan="5"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>
			</table>

			<p class="centre"><?= $pagination ?></p>
		</div>
	</div>
</div>




