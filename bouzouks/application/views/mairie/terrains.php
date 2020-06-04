<?php $this->layout->set_title($title); ?>

<div id="marche_noir-acheter">
	<?php
		$this->load->view('mairie/menu', array('lien'=> $lien));
	?>
	<?php 
		// ---------- Hook clans ----------
		// Tag MLBiste (MLB)
		if (($tag_mlbiste = $this->bouzouk->clans_tag_mlbiste()) != null)
			$this->load->view('clans/tag_mlb', array('tag_mlbiste' => $tag_mlbiste));
	?>
	<!-- Liste des maps -->
	<?php if($this->bouzouk->is_beta_testeur()) : ?>
	<div class="cellule_bleu_type1">
		<h4>Listes des ventes de la Mairie</h4>
		<div class="bloc_bleu">
			<?php foreach($maps as $map): ?>
			<div class="objet">
				<p class="titre"><?= $map->nom ?></p>
				<div class="infos">
					<p class="quantite">Dimensions : <?= $map->size ?></p>
					<p class="vendeur">Prix : <?= struls($map->prix) ?></p>
				</div>
				<div class="formulaire">
					<?php
					echo form_open('mairie/acheter_terrain/'.$map->id, array('class'=>'lien'));
					echo form_submit(array('name'=>'acheter', 'value'=>'Acheter'));
					echo form_close();
					?>
				</div>
			</div>
			<?php endforeach; ?>
			<p class="clearfloat"></p>
		</div>
	</div>
	<?php else : ?>
	<div class="cellule_bleu_type1">
		<h4>Coming soon</h4>
		<div class="bloc_bleu">
			<p>La liste des terrains n'est pas encore disponible.</p>
		</div>
	</div>
	<?php endif; ?>
</div>