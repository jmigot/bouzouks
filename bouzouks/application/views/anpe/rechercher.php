<?php $this->layout->set_title('Chercher un boulot'); ?>

<div id="anpe-rechercher">
	<!-- Menu -->
	<?php $this->load->view('anpe/menu', array('lien' => 1)) ?>
	
	<!-- Image de l'entreprise -->
	<p class="inline-block"><img src="<?= img_url('entreprises/usine.png') ?>" alt="Entreprise" width="400" height="282"></p>

	<!-- Texte à droite -->
	<p class="attention inline-block">
		Envie de changer de vie ?<br>De démarrer une nouvelle carrière ?<br>Ou juste enfin te décider à bosser un peu...<br><br>
		
		L'ANPC (Association Nationale des Pochtrons Chômeurs) est là pour toi ! Tu peux consulter les offres disponibles, il te suffit de remplir le formulaire ci-dessous.
	</p>

	<!-- Formulaire -->
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Consultation des offres d'emploi disponibles</h4>
		<div class="bloc_bleu">
			<?= form_open('anpe/lister') ?>
				<div class="frameborder1">
					<div class="frameborder2">
						<p class="highlight">Parcourir les annonces à la recherche d'un emploi</p>

						<table>
							<!-- Job recherché -->
							<tr>
								<td><label for="job_id">Job recherché :</label></td>
								<td>
									<select name="job_id" id="job_id">
										<option value="">Peu importe</option>
										<?php foreach ($jobs as $job): ?>
											<option value="<?= $job->id ?>"><?= $job->nom ?></option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>

							<!-- Entreprise -->
							<tr>
								<td><label for="entreprise_id">Entreprise (facultatif) :</label></td>
								<td>
									<select name="entreprise_id" id="entreprise_id">
										<option value="">Toutes</option>
										<?php foreach ($entreprises as $entreprise): ?>
											<option value="<?= $entreprise->id ?>"><?= $entreprise->nom.' ('.$entreprise->pseudo.')' ?></option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>

							<!-- Salaire minimum -->
							<tr>
								<td><label for="salaire_minimum">Salaire minimum (facultatif) :</label></td>
								<td><input type="text" name="salaire_minimum" id="salaire_minimum" size="3" maxlength="4" value="<?= set_value('salaire') ?>"> struls</td>
							</tr>
						</table>
					</div>
				</div>

				<!-- Rechercher -->
				<p class="centre clearfloat"><input type="submit" value="Rechercher"></p>
			</form>
		</div>
	</div>
</div> 
