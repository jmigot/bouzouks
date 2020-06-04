<?php $this->layout->set_title('Administration - Multicomptes'); ?>

<div id="staff-multicomptes-plouk_parties">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Multicomptes - Parties de Plouk</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<p class="margin">Toutes les parties jouées</p>

			<table>
				<tr>
					<th>Date</th>
					<th>Gagnant</th>
					<th>Contre</th>
					<th>Objet (p.c,p.a)</th>
					<th>Durée</th>
					<th>Tours</th>
					<th>Mdp</th>
					<th>Ab.</th>
					<th>V.</th>
				</tr>

				<?php foreach ($parties as $partie): ?>
					<tr>
						<td colspan="9"><p class="hr"></p></td>
					</tr>

					<tr>
						<td><?= jour_mois_heure_minute($partie->date_debut) ?></td>
						<td><p class="highlight"><?= $partie->gagnant_id == $partie->createur_id ? profil($partie->createur_id, $partie->createur_pseudo) : profil($partie->adversaire_id, $partie->adversaire_pseudo) ?></p></td>
						<td><p class="highlight"><?= $partie->gagnant_id != $partie->createur_id ? profil($partie->createur_id, $partie->createur_pseudo) : profil($partie->adversaire_id, $partie->adversaire_pseudo) ?></p></td>
						<td class="pourpre"><?= isset($partie->objet) ? mb_substr($partie->objet, 0, 12).' ('.$partie->createur_peremption.','.$partie->adversaire_peremption.')' : 'Aucun' ?></td>
						<td class="pourpre"><p class="highlight"><?= strtotime($partie->date_fin) - strtotime($partie->date_debut) ?>s</p></td>
						<td><?= $partie->nb_tours ?></td>
						<td><?= $partie->mot_de_passe != '' ? form_prep($partie->mot_de_passe) : '-' ?></td>
						<td class="pourpre"><?= $partie->abandon ? '<p class="highlight">oui</p>' : '-' ?></td>
						<td><?= $partie->version ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
</div>
 
