<?php
$this->layout->set_title('Notifications');
?>

<div id="historique-index">
	<!-- Menu -->
	<?php $this->load->view('historique/menu', array('lien' => 3)) ?>
	
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Mes dernières notifications</h4>
		<div class="bloc_bleu padd_vertical">
			<p class="mini_bloc"><a href="<?= site_url('mon_compte/notifications') ?>">Modifier mes préférences</a></p>
			<table class="lignes">
				<tr>
					<td class="centre">Date</td>
					<td>Texte</td>
				</tr>

				<?php foreach ($notifications as $notif): ?>
					<tr<?= $notif->lue == 0 ? ' class="non_lue"' : '' ?>>
						<td><p class="tab_espace"><?= bouzouk_datetime($notif->date, 'court') ?></p></td>
						<td><?= $this->bouzouk->construire_historique($notif) ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
</div>

