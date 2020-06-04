<?php
$this->layout->set_title($title);
?>

<div id="mairie-gerer">
	<?php 
		$this->load->view('mairie/menu_gestion', array('lien' => $lien));
		// ---------- Hook clans ----------
		// Tag MLBiste (MLB)
		if (($tag_mlbiste = $this->bouzouk->clans_tag_mlbiste()) != null)
			$this->load->view('clans/tag_mlb', array('tag_mlbiste' => $tag_mlbiste));
	?>
	<?php if($this->bouzouk->is_beta_testeur() ): ?>
	<div class="cellule_bleu_type1">
		<div class="bloc_bleu margin">
			<p>Fonds de la mairie : <?= struls($struls) ?></p>
			<?php
				echo form_open('mairie/map_editor/tmp');
				echo form_submit(array('class'=>'confirmation', 'name'=>'achat_map'), "Acheter une map");
				echo form_close();
			?>
		</div>
	</div>
	<div class="cellule_bleu_type1">
		<h4>Liste de maps</h4>
		<div class="bloc_bleu gestion padd_vertical">
			<p> Cliquer sur le nom d'une map pour la modifier </p>
			<?php
				// Si le mairie possède des maps
			if($maps): 
				foreach($maps as $type):?>
					<table class="entier tab_separ">
						<caption><?= ucfirst($type[0]->type) ?></caption>
						<thead>
							<tr>
								<th>Nom</th>
								<th>Dimensions</th>
								<th>Prix</th>
								<th>Disponibilité</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($type as $map): ?>
							<tr>
								<td><a href="<?= site_url('mairie/map_editor/'.$map->id) ?>" ><?= $map->nom; ?></a></td>
								<td><?= $map->size.' par '.$map->size; ?></td>
								<td><?= pluriel($map->prix, $map->monnaie); ?></td>
								<?php
									$dispo = $map->statut_vente?"En vente":"Mettre en vente";
								?>
								<td><a href="<?= site_url('mairie/switch_statut_vente/'.$map->id)?>"><?= $dispo ?></a></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table> 
			<?php
				endforeach; 
			else : ?>
			<p>La mairie ne possède aucune map.</p>
			<?php endif; ?>
		</div>
	</div>
	<?php else : ?>
	<div class="cellule_bleu_type1">
		<div class="bloc_bleu margin">
			<p>La map n'est disponible qu'au béta-testeurs. :p</p>
		</div>
	</div>
	<?php endif; ?>
</div>