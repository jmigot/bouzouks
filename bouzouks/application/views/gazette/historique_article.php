<?php $this->layout->set_title('Historique article'); ?>

<div id="gazette-historique_article">
	<!-- Menu -->
	<?php $this->load->view('gazette/menu', array('lien' => 3, 'article_id', $article_id)) ?>

	<div class="cellule_bleu_type1">
		<h4>Historique de l'article</h4>
		<div class="bloc_bleu">
			<?php if (count($historique) == 0): ?>
				<p class="pourpre margin">Il n'y a aucun historique pour cet article</p>
			<?php else: ?>
				<table>
					<tr>
						<th>Infos</th>
						<th>Diff</th>
						<th>Commentaire</th>
					</tr>

					<tr>
						<td colspan="3"><p class="hr"></p></td>
					</tr>

					<?php foreach ($historique as $ligne): ?>
						<tr>
							<td>
								<p class="highlight">
									<?= bouzouk_datetime($ligne->date, 'court') ?><br>
									Par <?= profil($ligne->auteur_id, $ligne->auteur_pseudo, $ligne->auteur_rang) ?>
								</p>
							</td>
							<td><?= $ligne->modification >= 0 ? '<span class="vert">+'.pluriel($ligne->modification, 'lettre').'</span>' : '<span class="rouge">'.pluriel($ligne->modification, 'lettre').'</span>' ?></td>
							<td><?= form_prep($ligne->commentaire) ?></td>
						</tr>

						<tr>
							<td colspan="3"><p class="hr"></p></td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endif; ?>
		</div>
	</div>
</div>