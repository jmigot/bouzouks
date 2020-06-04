 <?php $this->layout->set_title('Admin - Gestion des news'); ?>

<div id="staff-gerer_news">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Gérer les news</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<?= form_open('staff/gerer_news/rediger', array('method' => 'get', 'class' => 'centre margin')) ?>
			<p>
				<input type="submit" value="Rédiger une news">
			</p>
			</form>

			<table>
				<tr>
					<th>Auteur</th>
					<th>Date</th>
					<th>Titre</th>
					<th>Modifier</th>
					<th>Publié</th>
				</tr>

				<tr>
					<td colspan="5"><p class="hr"></p></td>
				</tr>

				<?php foreach ($news as $new): ?>
					<tr>
						<td><?= profil($new->auteur_id, $new->pseudo, $new->rang) ?></td>
						<td><p class="highlight"><?= bouzouk_date($new->date, 'court') ?></p></td>
						<td><?= form_prep($new->titre) ?></td>
						<td>
							<?= form_open('staff/gerer_news/rediger/'.$new->id) ?>
								<p><input type="submit" value="Modifier"></p>
							</form>
						</td>
						<td class="centre">
							<?php if ($new->en_ligne == Bouzouk::News_Brouillon): ?>
								<img src="<?= img_url('echec.png') ?>" title="Brouillon" alt="Brouillon">
							<?php elseif ($new->en_ligne == Bouzouk::News_Publie): ?>
								<img src="<?= img_url('succes.png') ?>" title="Publié" alt="Publié">
							<?php endif; ?>
						</td>
					</tr>

					<tr>
						<td colspan="5"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>
			</table>

			<p class="centre margin"><?= $pagination ?></p>	
		</div>
	</div>
</div>