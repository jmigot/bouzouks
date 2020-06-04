<?php $this->layout->set_title('Factures'); ?>

<div id="missives_factures-lister">
	<!-- Menu -->
	<?php $this->load->view('missives/menu', array('lien' => 4)) ?>

	<!-- Liste des factures -->
	<div class="cellule_bleu_type1">
		<h4>Dernières missives reçues</h4>
		<div class="bloc_bleu">
		<?php if (count($factures) == 0): ?>
			<p class="margin">Tu n'as aucune facture impayée, tu es un bon bouzouk :)</p>
		<?php else: ?>
			<p class="mini_bloc noir centre gras">
				Montant total des factures à payer : <?= struls($total) ?>
			</p>

			<table>
				<tr>
					<th>Titre</th>
					<th>Montant</th>
					<th>Dont majoration</th>
					<th>Date</th>
					<th>Payer</th>
				</tr>

				<?php foreach ($factures as $facture): ?>
					<?php if ($facture->majoration > 0): ?>
						<tr class="urgent">
					<?php else: ?>
						<tr>
					<?php endif; ?>
							<td><?= $facture->titre ?></td>
							<td><?= struls($facture->montant + $facture->majoration) ?></td>
							<td><?= pluriel($facture->majoration, 'strul') ?></td>
							<td><?= bouzouk_date($facture->date) ?></td>
							<td>
								<?= form_open('factures/payer') ?>
									<input type="hidden" name="facture_id" value="<?= $facture->id ?>">
									<input type="submit" value="Payer">
								</form>
							</td>
						</tr>
				<?php endforeach; ?>
			</table>
		<?php endif; ?>
	</div>
</div>
</div>
