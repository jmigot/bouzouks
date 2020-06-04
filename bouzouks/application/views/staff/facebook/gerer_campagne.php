<?php $this->layout->set_title($title); ?>

<div id="staff-gerer_site">
	<div class="cellule_bleu_type1 marge_haut">
		<h4><?= $title ?></h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>
			<p>
				<?php
					/*echo form_open('staff/gerer_campagne_fb/ajouter_pixel', array('class'=>'centre_margin'));
					echo form_submit('ajouter_pixel', 'Ajouter un Pixel');
					echo form_close();*/
				?>

			</p>
			<p>
				Cette page permet d'activer les Pixels en rapport avec les campagnes FB. Elle est suceptible d'évoluer en fonction de nos besoins
			</p>
			<?php
				if(!$pixels):
			?>
			<p>
				Il n'y aucun pixel d'enregistré pour le site.
			</p>
			<?php
				else:
			?>
			<table>
				<thead>
					<tr>
						<th>Nom du pixel</th>
						<th>Id du pixel</th>
						<th>Etat du pixel</th>
						<th>Modifer pixel</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($pixels as $pixel): ?>
						<tr>
							<td><?= $pixel->nom ?></td>
							<td><?= $pixel->id_fb ?></td>
							<td><?= $pixel->etat==0?'désactivé':'activé' ?></td>
							<td><a href="<?= site_url('staff/gerer_campagne_fb/modif_pixel/'.$pixel->id) ?>">modifer</a></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php
				endif;
			?>
		</div>
	</div>
</div>