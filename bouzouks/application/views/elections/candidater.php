<?php $this->layout->set_title('Candidature aux élections'); ?>

<div class="elections-candidater">
	<?php $this->load->view('elections/avancement') ?>

	<div class="cellule_gris_type1">
		<h4>Candidater pour les prochaines élections du maire</h4>
		<div class="bloc_gris">
			<p class="mini_bloc gras">Il y a actuellement <?= pluriel($nb_candidatures, 'candidat') ?></p>
					
			<?= form_open('elections/candidater', array('class' => 'padd_vertical')) ?>
				<?php if (isset($texte)): ?>
					<p class="highlight pourpre">Tu as candidaté pour les prochaines élections, tu peux encore modifier ton slogan et ton texte de campagne.</p>
				<?php endif; ?>

				<p class="centre">
					Écris ton texte de campagne qui sera visible des autres bouzouks lors deuxième tour. Ton slogan, lui, le sera tout le temps.<br>
					Sois convaincant, tu dois motiver les bouzouks à voter pour toi ! Candidater coûte <?= struls($this->bouzouk->config('elections_prix_candidater')) ?>.<br>
					Tu pourras toujours modifier ton slogan et ton texte de campagne jusqu'au début du 1er tour.
				</p>
				<p class="frameborder_bleu padding marge_haut">Slogan : <input maxlength="60" type="text" name="slogan" value="<?= isset($slogan) ? form_prep($slogan) : set_value('slogan') ?>" size="60"></p>
				<p class="frameborder_bleu padding marge_haut">Programme électoral: <span id="texte_nb_caracteres_restants" class="centre rond_blanc transparent">&nbsp;</span><br><textarea name="texte" class="compte_caracteres" cols="80" rows="15" maxlength="500"><?= isset($texte) ? form_prep($texte) : set_value('texte') ?></textarea></p>

				<p class="centre margin">
					Tu peux créer un topic dans la partie <a href="<?= site_url('tobozon/viewforum.php?id=5') ?>">Propagande du tobozon</a> la veille du 1er tour et un lien vers ton programme complet apparaîtra automatiquement sur la page des élections.
				</p>
				
				<p class="centre"><input type="submit" value="<?= isset($texte) ? 'Modifier ma candidature' : 'Candidater' ?>"></p>
			</form>

			<p class="hr"></p>
			
			<p class="centre margin">
				<i>Attention : tout propos ne respectant pas la <a href="<?= site_url('site/charte') ?>">charte du site</a> entraînera<br>
				l'annulation de la candidature voire l'exclusion du site.</i>
			</p>
		</div>
	</div>
</div>