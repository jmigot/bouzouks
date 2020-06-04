<?php $this->layout->set_title('Historique économique'); ?>

<div id="entreprises-historique">
	<!-- Menu -->
	<?php $this->load->view('entreprises/menu', array('lien' => 4)) ?>
	
	<div class="cellule_bleu_type1">
		<h4>Historique économique</h4>
		<div class="bloc_bleu">
			<table>
				<tr>
					<th>Date</th>
					<th>Employés</th>
					<th>Rentrée d'argent</th>
					<th>Pourcent achats</th>
					<th>Salaires employés</th>
					<th>Impôts</th>
					<th>Salaire patron</th>
					<th>Struls</th>
				</tr>
				
				<?php foreach ($historiques as $historique): ?>
					<tr>
						<td><p><?= bouzouk_date($historique->date) ?></p></td>
						<td><?= $historique->nb_employes ?></td>
						<td><?= $historique->rentree_argent ?></td>
						<td><?= $historique->pourcent_achats ?>%</td>
						<td><?= $historique->salaires_employes ?></td>
						<td><?= $historique->impots ?></td>
						<td><?= $historique->salaire_patron ?></td>
						<td><?= $historique->struls ?></p></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
</div>
