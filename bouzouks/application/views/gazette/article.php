<div id="gazette-index">

	<div class="journal">
		<div class="journal_haut">
			<p class="date_edition">
				Édition du <?= bouzouk_date() ?>
			</p>
		</div>

		<div class="article_une">
			<!-- Titre -->
			<p class="titre"><?= form_prep($gazette[Bouzouk::Gazette_Article]->titre) ?></p>
			<!-- Image -->
			<?php if ($gazette[Bouzouk::Gazette_Article]->image_url != ''): ?>
				<p class="image"><img src="<?= img_url($gazette[Bouzouk::Gazette_Article]->image_url) ?>" alt="Illustration"></p>
			<?php endif; ?>
			<div class="colonne_centrale">
				<div class="texte justifier">
					<!-- Texte -->
					<p>
						<?php
							$texte = form_prep($gazette[Bouzouk::Gazette_Article]->texte);
							$texte = preg_replace_callback('#{(.+)\|(\d+)}#Ui', create_function('$matches', 'return profil($matches[2], $matches[1]);'), $texte);
							echo $this->lib_parser->remplace_bbcode(nl2br($texte), 'gazette');
						?>
					</p>
					<p class="droite margin">
						Par <?= profil($gazette[Bouzouk::Gazette_Article]->auteur_id, $gazette[Bouzouk::Gazette_Article]->pseudo) ?>, posté le <?= bouzouk_date($gazette[Bouzouk::Gazette_Article]->date) ?><br>

						<?php if (isset($gazette[Bouzouk::Gazette_Article]->nb_commentaires)): ?>
							<a href="<?= site_url('tobozon/viewtopic.php?id='.$gazette[Bouzouk::Gazette_Article]->topic_id) ?>"><?= pluriel($gazette[Bouzouk::Gazette_Article]->nb_commentaires, 'commentaire') ?></a>
						<?php endif; ?>
					</p>
				</div>
			</div>
		</div>

		<p class="article_gauche justifier">
			<b><?= $gazette[Bouzouk::Gazette_Lohtoh]->titre ?></b><br>
			<?= nl2br($gazette[Bouzouk::Gazette_Lohtoh]->texte) ?>
		</p>

		<p class="article_gauche article_gris">
			<b><?= $gazette[Bouzouk::Gazette_PubClan]->titre ?></b><br>
			<?= $this->lib_parser->remplace_bbcode(nl2br(form_prep($gazette[Bouzouk::Gazette_PubClan]->texte))) ?>
		</p>

		<p class="article_gauche justifier">
			<b><?= $gazette[Bouzouk::Gazette_Meteo]->titre ?></b><br>
			<?= nl2br($gazette[Bouzouk::Gazette_Meteo]->texte) ?>
		</p>

		<p class="article_gauche justifier">
			<b><?= $gazette[Bouzouk::Gazette_Fete]->titre ?></b><br>
			<?= nl2br($gazette[Bouzouk::Gazette_Fete]->texte) ?>
		</p>

		<?php if (count($anciens_articles) > 0): ?>
			<p class="article_precedent article_gris">
				<b><?= form_prep($anciens_articles[0]->titre) ?></b><br>
				<?php
					$texte = form_prep($anciens_articles[0]->resume);
					$texte = preg_replace_callback('#{(.+)\|(\d+)}#Ui', create_function('$matches', 'return profil($matches[2], $matches[1]);'), $texte);
					echo $this->lib_parser->remplace_bbcode(nl2br($texte), 'gazette');
				?><br>
				<span class="infos"> Par <?= profil($anciens_articles[0]->auteur_id, $anciens_articles[0]->pseudo) ?> le <?= bouzouk_date($anciens_articles[0]->date) ?> &nbsp;&nbsp;&nbsp;&nbsp; <a href="<?= site_url('gazette/article_id/'.$anciens_articles[0]->id) ?>">Revoir cet article</a></span>
			</p>
		<?php endif; ?>

		<p class="classements justifier">
			<b><?= $gazette[Bouzouk::Gazette_Classement]->titre ?></b><br>
			<?= nl2br($gazette[Bouzouk::Gazette_Classement]->texte) ?>
		</p>

		<div class="colonne_bas">
			<div class="texte">
				<b>Les derniers articles du journal :</b><br>
				
				<?php
				// Le premier ancien article est déjà affiché au-dessus
				array_shift($anciens_articles);
				
				foreach ($anciens_articles as $article): ?>
					<!-- Titre et infos -->
					<p class="margin"><a href="#" id="lien_<?= $article->id ?>"><?= form_prep($article->titre) ?></a><br>par <?= profil($article->auteur_id, $article->pseudo) ?> le <?= bouzouk_date($article->date) ?></p>
					<div id="ancien_article_<?= $article->id ?>" class="ancien_article article_gris">
						<!-- Image -->
						<?php if ($article->image_url): ?>
							<p class="image"><img src="<?= img_url($article->image_url) ?>" alt="Illustration"></p>
						<?php endif; ?>

						<!-- Texte -->
						<p>
							<?php
							$texte = form_prep($article->texte);
								$texte = preg_replace_callback('#{(.+)\|(\d+)}#Ui', create_function('$matches', 'return profil($matches[2], $matches[1]);'), $texte);
								echo $this->lib_parser->remplace_bbcode(nl2br($texte), 'gazette');
							?>
						</p>

						<?php if (isset($article->nb_commentaires)): ?>
							<p>
							&nbsp;&nbsp; => &nbsp; <a href="<?= site_url('tobozon/viewtopic.php?id='.$article->topic_id) ?>"><?= pluriel($article->nb_commentaires, 'commentaire') ?></a>
							</p>
						<?php endif; ?>
					<p class="clearfloat"></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

</div>

<?php if (isset($previsualisation)): ?>
	<p class="clearfloat centre margin">
		<input type="button" value="Fermer la prévisualisation" class="fermer_previsualisation">
	</p>
<?php endif; ?>
