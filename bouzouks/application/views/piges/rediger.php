<?php
	$this->layout->set_title('Rédiger une pige');
?>

<div id="pige-rediger">
	<!-- Menu -->
	<?php $this->load->view('piges/menu', array('lien' => 2)) ?>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Rédiger une pige</h4>
		<div class="bloc_bleu padd_vertical">
			<?= form_open_multipart('piges/modifier', array('class' => 'pige')) ?>
				<div class="centre">
					<input type="hidden" name="pige_id" value="<?= $pige_id ?>">

					<!-- Texte -->
					<p class="frameborder_bleu">
						Texte<br>
						<textarea name="texte" id="texte" maxlength="200" class="compte_caracteres" rows="3" cols="70"><?= $pige_id != '0' ? form_prep($pige->texte) : set_value('texte') ?></textarea>
						
					</p>
					<p id="texte_nb_caracteres_restants" class="centre transparent">&nbsp;</p>

					<!-- Lien -->
					<p class="frameborder_bleu padd_vertical">
						Lien : <input type="text" name="lien" size="75" value="<?= $pige_id != '0' ? form_prep($pige->lien) : set_value('lien') ?>"><br>
					</p>
					
					<div class="margin">
						<p class="highlight">Options de BBCode</p>
						<table>
							<tr>
								<td><p><?= $this->lib_parser->bbcode('texte', false) ?></p></td>
							</tr>
						</table>
					</div>				
				</div>

				<?php if ($this->bouzouk->is_journaliste(Bouzouk::Rang_JournalisteChef)): ?>
					<!-- Admin -->
					<div class="margin">
						<p class="highlight centre">Options du rédacteur en chef</p>
						<table>
							<tr>
								<td><label for="statut">Statut : </label></td>
								<td>
									<select name="statut" id="statut">
										<option value="<?= Bouzouk::Piges_Active ?>"<?= $pige->en_ligne == Bouzouk::Piges_Active ? ' selected' : '' ?>>Activé</option>
										<option value="<?= Bouzouk::Piges_Desactive ?>"<?= $pige->en_ligne == Bouzouk::Piges_Desactive ? ' selected' : '' ?>>Désactivé</option>
									</select>
								</td>
							</tr>
						</table>
					</div>
				<?php endif; ?>
					
				<!-- Valider -->
				<div class="margin">
					<p class="centre margin">
						<input type="submit" name="modifier" value="<?= $pige_id != '0' ? "Modifier la pige" : "Créer la pige" ?>">
					</p>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Prévisualisation -->
<div id="popup" class="invisible">
</div>
