<?php $this->layout->set_title('Plouk - Rejoindre'); ?>

<div id="plouk-creer">
	<!-- Menu -->
	<?php $this->load->view('plouk/menu', array('lien' => 1)) ?>

	<div class="cellule_gris_type1">
		<h4>Plouk - Rejoindre une partie</h4>
		<div class="bloc_gris">
	
		<?= form_open('plouk/rejoindre', array('class' => 'margin')) ?>
			<input type="hidden" name="rejoindre" value="1">
			<input type="hidden" name="partie_id" value="<?= $partie->id ?>">
			
			<table>
				<!-- Créateur -->
				<tr>
					<td><p class="tab_espace">Créateur</p></td>
					<td><?= profil($partie->createur_id, $partie->createur_pseudo) ?></td>
				</tr>

				<!-- Objet parié -->
				<tr>
					<td><p class="tab_espace">Objet parié</p></td>
					<td class="pourpre">
						<?php if ($partie->objet_id == null): ?>
							Aucun
						<?php else: ?>
							<?= $partie->objet_nom ?> (<?= $partie->createur_objet_peremption == -1 ? 'illimité' : pluriel($partie->createur_objet_peremption, 'jour') ?>)
						<?php endif; ?>
					</td>
				</tr>
				
				<!-- Nombre de tours -->
				<tr>
					<td><p class="tab_espace">Nombre de tours</p></td>
					<td><?= $partie->nb_tours ?> tours</td>
				</tr>

				<!-- Chrono à chaque tour -->
				<tr>
					<td><p class="tab_espace">Chrono à chaque tour</p></td>
					<td><?= $chronos[$partie->chrono] ?></td>
				</tr>

				<!-- Charisme -->
				<tr>
					<td><p class="tab_espace">Charisme</p></td>
					<td><img src="<?= img_url('plouk/charisme.gif') ?>" alt="Charisme">&nbsp;<?= $partie->charisme ?></td>
				</tr>

				<!-- Médiatisation -->
				<tr>
					<td><p class="tab_espace">Médiatisation</p></td>
					<td><img src="<?= img_url('plouk/mediatisation.gif') ?>" alt="Médiatisation">&nbsp;<?= $partie->mediatisation ?></td>
				</tr>

				<!-- Partisans -->
				<tr>					
					<td><p class="tab_espace">Partisans</p></td>
					<td><img src="<?= img_url('plouk/partisans.gif') ?>" alt="Partisans">&nbsp;<?= $partie->partisans ?></td>
				</tr>

				<!-- Machine à café -->
				<tr>
					<td><p class="tab_espace">Machine à café</p></td>
					<td>
						<?php if ( ! $partie->tchat): ?>
							Spectateurs autorisés à parler <img src="<?= img_url('succes.png') ?>" alt="Publique" title="Publique">
						<?php else: ?>
							Spectateurs interdits de parler <img src="<?= img_url('echec.png') ?>" alt="Privée" title="Privée">
						<?php endif; ?>
					</td>
				</tr>

				<!-- Objet du pari -->
				<tr>
					<td><p class="tab_espace">Objet du pari</p></td>
					<td>
						<?php if ($partie->objet_id == null): ?>
							Aucun
						<?php else: ?>
							<select name="maison_id">
								<?php foreach ($objets as $objet): ?>
									<option value="<?= $objet->maison_id ?>"><?= $objet->nom ?> (<?= $objet->peremption == -1 ? 'illimité' : pluriel($objet->peremption, 'jour') ?>)</option>
								<?php endforeach; ?>
							</select>
						<?php endif; ?>
					</td>
				</tr>

				<!-- Mot de passe -->
				<tr>
					<td><p class="tab_espace">Mot de passe</p></td>
					<td>
						<?php if ($partie->mot_de_passe != ''): ?>
							<input type="text" name="mot_de_passe" size="10" maxlength="10">
						<?php else: ?>
							Aucun
						<?php endif; ?>
					</td>
				</tr>
			</table>

			<!-- Rejoindre -->
			<p class="centre"><input type="submit" value="Rejoindre cette partie"></p>
		</form>
	</div>
</div>
 
