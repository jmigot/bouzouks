<?php
$this->layout->set_title($title);
$this->load->view('staff/gerer_map_tchat/menu', array('lien' => $lien));
?>
<div class="cellule_bleu_type1 marge_haut">
	<h4>Les signalements en attente</h4>
	<article class="bloc_bleu">
		<?php
			if(!$signalements_a_traiter):
		?>
				<p>Pas de signalements à traiter</p>
		<?php
			else :
		?>
				<table>
					<thead>
						<tr>
							<th>Date</th>
							<th>Contenu</th>
							<th>Auteur</th>
							<th>Traiter</th>
						</tr>
					</thead>
					<tbody>
		<?php
				foreach($signalements_a_traiter as $signalement_a_traiter):
		?>
					<tr>
						<td><?= $signalement_a_traiter->date_envoi; ?></td>
						<td><?= $signalement_a_traiter->content; ?></td>
						<td><?= $signalement_a_traiter->pseudo; ?></td>
						<td><a href="<?= site_url('staff/moderer_map_tchats/traiter_signalement/'.$signalement_a_traiter->id) ?>">Traiter</a></td>
					</tr>
		<?php
				endforeach;
		?>
				</table>
					</tbody>
		<?php
			endif;	 
		?>
	</article>
</div>

<div class="cellule_bleu_type1 marge_haut">
	<h4>Les signalements traités</h4>
	<article class="bloc_bleu">
		<table>
			<thead>
				<tr>
					<th>Date du signalement</th>
					<th>Auteur</th>
					<th>Contenu</th>
					<th>Modérateur</th>
					<th>Date traitement</th>
				</tr>
			</thead>
			<tbody>
		<?php
			foreach($signalements_traites as $signalement_traite):
		?>
			<tr>
				<td><?= $signalement_traite->date_envoi; ?></td>
				<td><?= $signalement_traite->pseudo; ?></td>
				<td><?= $signalement_traite->content; ?></td>
				<td><?= $signalement_traite->pseudo_modo; ?></td>
				<td><?= $signalement_traite->date_traitement; ?></td>
			</tr>
		<?php
			endforeach;	 
		?>
			</tbody>
		</table>
	</article>
</div>