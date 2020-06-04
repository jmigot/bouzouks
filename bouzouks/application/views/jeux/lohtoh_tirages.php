<?php $this->layout->set_title('T\'es deg hein ?!'); ?>

<div id="jeux-lohtoh_tirages">
	<!-- Menu -->
	<div class="onglet">
		<div class="menu">
			<a href="<?= site_url('jeux/lohtoh') ?>" title="Lohtoh">Lohtoh</a>
			<a class="actif" href="<?= site_url('jeux/lohtoh_tirages') ?>" title="Derniers tirages">Derniers tirages</a>
		</div>
		<div class="deco onglet2">
		</div>
	</div>
	
	<?php 
		// ---------- Hook clans ----------
		// Tag MLBiste (MLB)
		if (($tag_mlbiste = $this->bouzouk->clans_tag_mlbiste()) != null)
			$this->load->view('clans/tag_mlb', array('tag_mlbiste' => $tag_mlbiste));
	?>

	<div class="cellule_gris_type1">
		<h4>Liste des derniers tirages du lohtoh</h4>
		<div class="bloc_gris">
		<p class="centre margin"><?= $pagination ?></p>

		<table>
			<tr>
				<th>Date</th>
				<th>Numéros tirés</th>
				<th>Cagnotte</th>
				<th>Gagnants</th>
			</tr>

			<?php foreach ($tirages as $tirage): ?>
				<tr>
					<td><?= bouzouk_datetime($tirage->date, 'court') ?></td>
					<td class="pourpre"><?= $tirage->numeros ?></td>
					<td class="centre"><?= struls($tirage->cagnotte) ?></td>
					<td><?= $tirage->gagnants ?></td>
				</tr>
			<?php endforeach; ?>
		</table>

		<p class="centre margin"><?= $pagination ?></p>
	</div>
</div>
