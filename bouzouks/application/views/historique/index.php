<?php
$this->layout->set_title('Historique');
$this->layout->ajouter_javascript('historique.js');
?>

<div id="historique-index">
	<!-- Menu -->
	<?php $this->load->view('historique/menu', array('lien' => 1)) ?>
	
	<div class="cellule_gris_type1 marge_haut">
		<h4>Historique des <?= $this->bouzouk->config('historique_joueur_duree_retention') ?> derniers jours</h4>
		<div class="bloc_gris padd_vertical">
			<?= form_open('historique/modifier_filtres', array('class' => 'filtres')) ?>
				<?php $n = 0; ?>
				<table class="margin">
					<tr>
						<?php foreach ($filtres as $cle => $filtre): ?>
						<?php if (($n++ % 4) == 0) echo '</tr><tr>'; ?>
							<td>
								<input type="checkbox" name="<?= $filtre[0] ?>" id="<?= $filtre[0] ?>"<?= in_array($cle, $this->session->userdata('filtres_historique')) ? '' : ' checked' ?>>
								<label for="<?= $filtre[0] ?>"><?= $filtre[1] ?></label>
							</td>
						<?php endforeach; ?>
					</tr>
				</table>

				<!-- Modifier -->
				<p class="centre marge_droite">
					<span class="boutons_filtre">
						<input type="button" value="Tout cocher" class="tout_cocher">
						<input type="button" value="Tout décocher" class="tout_decocher">
					</span>
					<input type="submit" value="Modifier les filtres">
				</p>
			</form>

			<p class="centre"><?= $pagination ?></p>
			
			<table class="lignes">
				<tr>
					<td class="centre">Date</td>
					<td>Texte</td>
				</tr>

				<?php foreach ($historique as $ligne): ?>
					<tr>
						<td><p class="tab_espace"><?= bouzouk_datetime($ligne->date, 'court') ?></p></td>
						<td><?= $this->bouzouk->construire_historique($ligne) ?></td>
					</tr>
				<?php endforeach; ?>
			</table>

			<p class="centre"><?= $pagination ?></p>
		</div>
	</div>
</div>
 
