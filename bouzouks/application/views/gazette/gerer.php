<?php
$this->layout->set_title('La rédaction');
?>

<div id="gazette-gerer">
	<div class="cellule_gris_type1 marge_haut">
		<h4>La rédaction de Vlurxtrznbnaxl</h4>
		<div class="bloc_gris articles">
			<?= form_open('gazette/rediger', array('method' => 'get', 'class' => 'centre margin')) ?>
				<p class="mini_bloc">
					<input type="submit" value="Rédiger un nouvel article">
				</p>
			</form>

			<p class="margin centre pourpre">Merci de ne pas divulguer le contenu d'un article non publié à votre entourage. Pour des raisons de RP, les articles non publiés doivent rester confidentiels.</p>

			<table>
				<tr>
					<th>Auteur</th>
					<th>Date</th>
					<th>Titre</th>
					<th>Lire</th>
					<th>Publié</th>
				</tr>

				<?php foreach ($articles as $article): ?>
					<tr>
						<td><?= profil($article->auteur_id, $article->pseudo, $article->rang) ?></td>
						<td><p class="tab_espace"><?= bouzouk_date($article->date) ?></p></td>
						<td><?= form_prep($article->titre) ?></td>
						<td>
							<?= form_open('gazette/lire/'.$article->id) ?>
								<p>
									<input type="submit" value="Lire">
								</p>
							</form>
						</td>
						<td class="centre">
							<?php if ($article->en_ligne == Bouzouk::Gazette_Brouillon): ?>
								<img src="<?= img_url('attention.png') ?>" title="Brouillon" alt="Brouillon">
							<?php elseif ($article->en_ligne == Bouzouk::Gazette_Publie): ?>
								<img src="<?= img_url('succes.png') ?>" title="Publié" alt="Publié">
							<?php elseif ($article->en_ligne == Bouzouk::Gazette_Refuse): ?>
								<img src="<?= img_url('echec.png') ?>" title="Refusé" alt="Refusé">
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>

			<p class="centre margin"><?= $pagination ?></p>
		</div>
	</div>
	<div class="entier clearfloat">&nbsp;</div>

	<!-- Le tchat des journalistes -->
	<?php
		$vars = array(
			'titre'           => 'Le tchat des journalistes',
			'url_rafraichir'  => 'webservices/rafraichir_tchat_journalistes',
			'url_poster'      => 'webservices/poster_tchat_journalistes',
			'nb_messages_max' => $this->bouzouk->config('maintenance_tchats_messages')
		);
		$this->load->view('machine_a_cafe', $vars);
	?>

	<!-- Liste des journalistes -->
	<?= $this->load->view('gazette/liste_journalistes'); ?>

	<?php if ($this->bouzouk->is_journaliste(Bouzouk::Rang_JournalisteChef)): ?>
		<!-- Nombre d'articles par joueur -->
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Nombre d'articles par bouzouk</h4>
			<div class="bloc_bleu nb_articles">
				<table class="entier">
					<?php foreach ($nb_articles as $joueur): ?>
						<tr>
							<td><p class="tab_espace"><?= profil($joueur->id, $joueur->pseudo, $joueur->rang) ?></p></td>
							<td class="pourpre"><?= pluriel($joueur->nb_articles, 'article') ?></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>
		</div>
	<?php endif; ?>
</div>