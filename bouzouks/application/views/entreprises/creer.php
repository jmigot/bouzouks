<?php
$this->layout->set_title('Créer son entreprise');
$this->layout->ajouter_javascript('entreprises.js');
?>

<div id="entreprises-creer">
	<!-- Image de l'entreprise -->
	<p class="inline-block"><img src="<?= img_url('entreprises/usine.png') ?>" alt="Entreprise" width="400" height="282"></p>

	<!-- Texte à droite -->
	<p class="attention inline-block">
		<b>ATTENTION :</b> en créant ton entreprise, tu t'apprêtes à avoir beaucoup de responsabilités envers tes futurs employés.<br><br>

		De plus, tu ne pourras plus changer de job ni retourner chez ta mère si ta société dépose le bilan...<br><br>

		N'hésite pas à consultez <a href="<?= site_url('site/faq') ?>">la FAQ</a> pour plus d'infos.
	</p>

	<!-- Formulaire -->
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Formulaire de création d'entreprise</h4>
		<div class="bloc_bleu">
			<?= form_open('entreprises/creer') ?>
				<div class="frameborder1">
					<div class="frameborder2">
						<p class="highlight">
							Prix à débourser pour créer une entreprise : <span class="pourpre"><?= $prix_entreprise ?> struls</span><br>
							<i>Coût : <span class="pourpre"><?= $this->bouzouk->config('entreprises_prix_entreprise') ?> struls</span> - Aide de la mairie : <span class="pourpre"><?= $aide_mairie ?> struls</span> - Reste à payer :
							<span class="pourpre"><?= $prix_entreprise ?> struls</span></i>
						</p>

						<p class="image_objet"><img src="<?= img_url('vide.gif') ?>" alt="Objet"></p>
						
						<table>
							<!-- Nom de l'entreprise -->
							<tr>
								<td><label for="nom">Nom de l'entreprise :</label></td>
								<td><input type="text" name="nom" id="nom" size="20" maxlength="20" value="<?= set_value('nom') ?>" placeholder="20 caractères max"></td>
							</tr>

							<!-- Type de production (objet) -->
							<tr>
								<td><label for="objet_id">Type de production:</label></td>
								<td><select name="objet_id" id="objet_id">
										<option value="" id="vide.gif">-- Objet qui sera produit --</option>

										<!-- Faim -->
										<optgroup label="Bouffzouk">
										<?php foreach ($objets['faim'] as $objet): ?>
											<option value="<?= $objet->id ?>" id="<?= $objet->image_url ?>"><?= $objet->nom.' ('.$objet->nb_entreprises.')' ?></option>
										<?php endforeach; ?>
										</optgroup>

										<!-- Santé -->
										<optgroup label="Indispenzouk">
										<?php foreach ($objets['sante'] as $objet): ?>
											<option value="<?= $objet->id ?>" id="<?= $objet->image_url ?>"><?= $objet->nom.' ('.$objet->nb_entreprises.')' ?></option>
										<?php endforeach; ?>
										</optgroup>

										<!-- Stress -->
										<optgroup label="Luxezouk">
										<?php foreach ($objets['stress'] as $objet): ?>
											<option value="<?= $objet->id ?>" id="<?= $objet->image_url ?>"><?= $objet->nom.' ('.$objet->nb_entreprises.')' ?></option>
										<?php endforeach; ?>
										</optgroup>
									</select>
								</td>
							</tr>
						</table>

						<p class="parentheses centre">(entre parenthèses le nombre d'entreprises existant déjà pour chaque produit)</p>
					</div>
				</div>

				<!-- Créer -->
				<p class="centre clearfloat"><input type="submit" value="Créer l'entreprise"></p>
			</form>
		</div>
	</div>
</div>