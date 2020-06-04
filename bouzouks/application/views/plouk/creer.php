<?php $this->layout->set_title('Plouk - Créer'); ?>

<div id="plouk-creer">
	<!-- Menu -->
	<?php $this->load->view('plouk/menu', array('lien' => 2)) ?>
	
	<div class="cellule_bleu_type1">
		<h4>Plouk - Créer une partie</h4>
		<div class="bloc_bleu">
			<p class="mini_bloc">
			Pour jouer il te suffit de créer une partie puis d'attendre qu'un joueur la rejoigne.
			</p>
		
			<?= form_open('plouk/creer', array('class' => 'margin')) ?>
				<table>
					<!-- Nombre de tours -->
					<tr>
						<td><p class="tab_espace">Nombre de tours</p></td>
						<td>
							<select name="nb_tours">
								<?php for ($i = 10 ; $i <= 100; $i++): ?>
									<option value="<?= $i ?>"<?= $i == 20 ? ' selected' : '' ?>><?= $i ?></option>
								<?php endfor; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td  colspan="2"></td>
					</tr>

					<!-- Chrono à chaque tour -->
					<tr>
						<td><p class="tab_espace">Chrono</p></td>
						<td>
							<select name="chrono">
								<?php foreach ($chronos as $valeur => $texte): ?>
									<option value="<?= $valeur ?>"<?= $valeur == 45 ? ' selected' : '' ?>><?= $texte ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td  colspan="2"></td>
					</tr>

					<!-- Charisme -->
					<tr>
						<td><p class="tab_espace">Charisme</p></td>
						<td>
							<select name="charisme">
								<?php for ($i = 0 ; $i <= 30; $i++): ?>
									<option value="<?= $i ?>"<?= $i == 15 ? ' selected' : '' ?>><?= $i ?></option>
								<?php endfor; ?>
							</select>
							<img src="<?= img_url('plouk/charisme.gif') ?>" alt="Charisme">
						</td>
					</tr>
					<tr>
						<td  colspan="2"></td>
					</tr>

					<!-- Médiatisation -->
					<tr>
						<td><p class="tab_espace">Médiatisation</p></td>
						<td>
							<select name="mediatisation">
								<?php for ($i = 1 ; $i <= 5; $i++): ?>
									<option value="<?= $i ?>"<?= $i == 2 ? ' selected' : '' ?>><?= $i ?></option>
								<?php endfor; ?>
							</select>
							<img src="<?= img_url('plouk/mediatisation.gif') ?>" alt="Médiatisation">
						</td>
					</tr>
					<tr>
						<td  colspan="2"></td>
					</tr>

					<!-- Partisans -->
					<tr>
						<td><p class="tab_espace">Partisans</p></td>
						<td>
							<select name="partisans">
								<?php for ($i = 1 ; $i <= 5; $i++): ?>
									<option value="<?= $i ?>"<?= $i == 2 ? ' selected' : '' ?>><?= $i ?></option>
								<?php endfor; ?>
							</select>
							<img src="<?= img_url('plouk/partisans.gif') ?>" alt="Partisans">
						</td>
					</tr>
					<tr>
						<td  colspan="2"></td>
					</tr>

					<!-- Machine à café -->
					<tr>
						<td><p class="tab_espace">Machine à café</p></td>
						<td>
							<input type="checkbox" name="machine_a_cafe" id="machine_a_cafe"><label for="machine_a_cafe">interdire aux spectateurs de parler</label>
						</td>
					</tr>
					<tr>
						<td  colspan="2"></td>
					</tr>

					<!-- Objet du pari -->
					<tr>
						<td><p class="tab_espace">Objet du pari</p></td>
						<td>
							<select name="maison_id">
								<option value="0">Aucun</option>

								<?php foreach ($objets as $objet): ?>
									<option value="<?= $objet->maison_id ?>"><?= $objet->nom ?> (<?= $objet->peremption == -1 ? 'illimité' : pluriel($objet->peremption, 'jour') ?>)</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td  colspan="2"></td>
					</tr>

					<!-- Mot de passe -->
					<tr>
						<td><p class="tab_espace">Mot de passe (optionnel)</p></td>
						<td><input type="text" name="mot_de_passe" size="10" maxlength="10"></td>
					</tr>
				</table>

				<!-- Créer -->
				<p class="centre"><input type="submit" value="Créer une nouvelle partie"></p>
			</form>

			<p class="centre margin">
				Le delai d'attente maximum est de 30min, passé ce delai, si personne ne t'a rejoint la partie sera supprimée.
			</p>
		</div>
	</div>
</div>
