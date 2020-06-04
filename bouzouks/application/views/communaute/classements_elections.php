<?php $this->layout->set_title('Statistiques élections'); ?>

<div id="elections-historique">

	<div class="cellule_bleu_type1">
		<h4>Statistiques des élections du <?= bouzouk_date($date) ?></h4>
		<div class="bloc_bleu">
			<p class="mini_bloc">
				<?= $pagination ?>
			</p>
			<table>
				<tr>
					<th>Bouzouk</th>
					<th>Tour atteint</th>
					<th>Tour 1</th>
					<th>Tour 1 %</th>
					<th>Tour 2</th>
					<th>Tour 2 %</th>
					<th>Tour 3</th>
					<th>Tour 3 %</th>
					<th>Position</th>
				</tr>
				
				<?php foreach ($candidats as $candidat): ?>
					<tr>
						<td><?= profil($candidat->id, $candidat->pseudo) ?></td>
						<td><p><?= $candidat->tour == Bouzouk::Elections_Banni ? 'Abandon' : $candidat->tour ?></p></td>
						
						<?php if ($candidat->tour >= Bouzouk::Elections_Tour1): ?>
							<td><?= $candidat->votes_tour1 ?></td>
							<td class="pourpre"><p><?= $candidat->pourcentage_tour1 ?>%</p></td>
						<?php else: ?>
							<td>-</td>
							<td>-</td>
						<?php endif; ?>

						<?php if ($candidat->tour >= Bouzouk::Elections_Tour2): ?>
							<td><?= $candidat->votes_tour2 ?></td>
							<td class="pourpre"><p><?= $candidat->pourcentage_tour2 ?>%</p></td>
						<?php else: ?>
							<td>-</td>
							<td>-</td>
						<?php endif; ?>

						<?php if ($candidat->tour >= Bouzouk::Elections_Tour3): ?>
							<td><?= $candidat->votes_tour3 ?></td>
							<td class="pourpre"><p><?= $candidat->pourcentage_tour3 ?>%</p></td>
						<?php else: ?>
							<td>-</td>
							<td>-</td>
						<?php endif; ?>
						
						<td>#<?= $candidat->position ?></td>
					</tr>
				<?php endforeach; ?>
			</table>

			
		</div>
	</div>
</div>
