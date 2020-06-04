<?php $this->layout->set_title('Mendier'); ?>

<div id="mendiants-mendier">
	<?php 
		// ---------- Hook clans ----------
		// Tag MLBiste (MLB)
		if (($tag_mlbiste = $this->bouzouk->clans_tag_mlbiste()) != null)
			$this->load->view('clans/tag_mlb', array('tag_mlbiste' => $tag_mlbiste));
	?>

	<div class="mendier">
		<h4>Une p'tite pièce s'vous plaît m'sieur dame !</h4>
		<div class="infos bloc_bleu">
			<?php if (isset($escroquerie_mendicite)): ?>
				<p class="highlight padding margin noir centre">Ton clan <?= couleur(form_prep($escroquerie_mendicite->nom_clan)) ?> a lancé l'action <?= couleur(form_prep($escroquerie_mendicite->nom_action)) ?>, tu peux donc mendier en tant que riche aujourd'hui</p>
			<?php endif; ?>

			<p class="margin-mini">Aïe, aïe, aïe ! Tes finances sont dans un état lamentable ! Plus qu'une seule solution : descendre dans la rue et tendre le bras.
				<br>À toi de trouver les mots justes !</p>

			<?= form_open('mendiants/mendier', array('class' => 'formulaire')) ?>
				<p class="frameborder_bleu">
					<textarea name="message" class="compte_caracteres" cols="35" rows="3" maxlength="250" placeholder="Entre ici tes arguments pour convaincre les passants"><?= set_value('message') ?></textarea>
				</p>
				<p id="message_nb_caracteres_restants" class="transparent"></p>
				
				<p><input type="submit" value="Aller mendier"></p>
			</form>
			<p class="clearfloat"></p>
		</div>
	</div>

	<p>
		ATTENTION : les termes racistes, injurieux ou qui portent atteinte à l'intégrité de la personne sont formellement interdits sous peine d'exclusion du site.
	</p>
</div>
