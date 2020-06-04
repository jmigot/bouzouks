<?php $this->layout->set_title('Administration - Multicomptes'); ?>

<div id="staff-multicomptes-payes_employes">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Multicomptes - Payes employés</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<p class="margin">Les joueurs qui sont sous-payés par rapport à la FAQ</p>

			<table>
				<tr>
					<th>Date</th>
					<th>Patron</th>
					<th>Employé</th>
					<th>Salaire</th>
					<th>FAQ</th>
					<th>Job</th>
				</tr>

				<?php foreach ($joueurs as $joueur): ?>
					<tr>
						<td colspan="6"><p class="hr"></p></td>
					</tr>

					<tr>
						<td><?= bouzouk_date($joueur->date) ?></td>
						<td><p class="highlight"><?= profil($joueur->patron_id, $joueur->patron_pseudo) ?></p></td>
						<td><p class="highlight"><?= profil($joueur->employe_id, $joueur->employe_pseudo) ?></p></td>
						<td class="pourpre centre"><?= $joueur->salaire ?></td>
						<td class="pourpre centre"><?= $joueur->salaire_recommande ?></td>
						<td><?= $joueur->job ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
</div>
 
