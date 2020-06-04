<?php $this->layout->set_title('Historique actions clans'); ?>

<div id="clans-historique">
	<!-- Menu -->
	<?php $this->load->view('clans/menu_liste', array('lien' => 3)) ?>

	<div class="cellule_bleu_type1">
		<h4>Historique de toutes les actions de clans</h4>
		<div class="bloc_bleu padd_vertical">
			<p class="centre"><?= $pagination ?></p>
		
			<table>
				<tr>
					<th>Date début</th>
					<th>Clan</th>
					<th></th>
					<th>Action</th>
					<th>Coût action</th>
				</tr>

				<?php foreach ($actions_lancees as $action): ?>

					<tr>
						<td><?= bouzouk_datetime($action->date_debut, 'court') ?></td>
						<td><?= $action->mode_recrutement == Bouzouk::Clans_RecrutementInvisible ? '<span class="pourpre">Clan caché</span>' : form_prep($action->nom_clan) ?></td>
						<td><img src="<?= img_url('clans/actions/'.$action->image) ?>" alt="Image" width="45"></td>
						<td class="pourpre"><?= $action->nom ?> <?= $this->lib_clans->parametres(unserialize($action->parametres)) ?></td>
						<td><?= $action->cout ?> p.a</td>
					</tr>
				<?php endforeach; ?>
			</table>

			<p class="centre"><?= $pagination ?></p>
		</div>
	</div>
</div>
