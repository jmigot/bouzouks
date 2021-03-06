<?php
$this->layout->set_title('Missives reçues');
$this->layout->ajouter_javascript('missives.js');
?>

<div id="missives">
	<!-- Menu -->
	<?php $this->load->view('missives/menu', array('lien' => 1)) ?>

	<!-- Titre -->
	<div class="cellule_bleu_type1">
		<h4>Dernières missives reçues</h4>
		<div class="bloc_bleu">
		<div class="mini_bloc">
		<!-- Jauge nombre de messages -->
				<?php
					$limite_atteinte = $nb_missives > $this->bouzouk->config('missives_limite') * 90 / 100;
					$jauge = 'jauge_grise';
					
					if ($limite_atteinte)
					{
						$jauge = 'jauge_rouge';
					}
				?>
				<p class="barre inline-block">
					<img src="<?= img_url($jauge.'_1.png') ?>" width="1" alt="Jauge 1">
					<img src="<?= img_url($jauge.'_2.png') ?>" width="<?= round(min($nb_missives, $this->bouzouk->config('missives_limite')) * 150 / $this->bouzouk->config('missives_limite')) ?>" alt="Jauge 2">
					<img src="<?= img_url($jauge.'_3.png') ?>" width="<?= round(max(0, $this->bouzouk->config('missives_limite') - $nb_missives) * 150 / $this->bouzouk->config('missives_limite')) ?>" alt="Jauge 3">
					<img src="<?= img_url($jauge.'_1.png') ?>" width="1" alt="Jauge 1">
				</p>
				<p class="inline-block <?= $limite_atteinte ? 'rouge' : '' ?>"><?= $nb_missives.'/'.$this->bouzouk->config('missives_limite') ?> missives</p>
		</div>	

	<!-- Missives reçues -->
	<?= form_open('missives/supprimer_recues', array('class' => 'clearfloat')) ?>
		<?php if ($nb_missives >= $this->bouzouk->config('missives_limite')): ?>
			<p class="rouge centre gras">Tu as trop de missives reçues/envoyées, fais le ménage !</p>
		<?php endif; ?>

		<?php if (count($missives) == 0): ?>
			<p class="margin">Tu n'as reçu aucune missive, personne n'a pensé à toi même pas le percepteur !</p>
		<?php else: ?>
			<table>
				<!-- En-tête -->
				<tr>
					<th></th>
					<th>Expéditeur</th>
					<th>Objet</th>
					<th>Date</th>
					<th><p><input type="checkbox" id="case_supprimer_tous"></p></th>
				</tr>

				<!-- Messages -->
				<?php foreach ($missives as $missive): ?>
					<?php if ($missive->lue == 0): ?>
						<tr class="non-lue">
							<td><img src="<?= img_url('missives/recue_non_lue.png') ?>" alt="Non lue"></td>
					<?php else: ?>
						<tr>
							<td><img src="<?= img_url('missives/recue_lue.png') ?>" alt="Lue"></td>
					<?php endif; ?>

						<td><?= profil($missive->expediteur_id, $missive->expediteur, $missive->expediteur_rang) ?></td>
						<td><a href="<?= site_url('missives/lire_recue/'.$missive->id) ?>" title="Lire cette missive"><?= form_prep($missive->objet) ?></a></td>
						<td><?= bouzouk_datetime($missive->date_envoi, 'court') ?></td>
						<td><input type="checkbox" name="ids[]" value="<?= $missive->id ?>"></td>
					</tr>
				<?php endforeach; ?>
			</table>
		<?php endif; ?>

		<div>
			<?php if (count($missives) > 0): ?>
				<p class="fl-droite marge_bas marge_droite"><input type="submit" value="Supprimer"></p>
				<p class="centre"><?= $pagination ?></p>
				<p class="clearfloat"></p>
			<?php endif; ?>
		</div>
	</form>
</div>
</div>
</div>