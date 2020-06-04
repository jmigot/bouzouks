<?php $this->layout->set_title('Recensement'); ?>

<div>
	<div class="cellule_gris_type1">
		<h4>Où qu'il est ?!</h4>
		<div class="bloc_gris">
			<p class="mini_bloc">
				Rechercher un bouzouk en inscrivant son nom dans le formulaire.
			</p>
			<!-- Recherche par nom -->
			<?= form_open('communaute/recherche_bouzouks') ?>
				<p class="centre marge_haut marge_bas frameborder_bleu">
					<input type="text" name="nom" maxlength="12" size="25">
					<input type="submit" value="Rechercher">
				</p>
			</form>
		</div>
	</div>
	
	<div class="cellule_bleu_type2 marge_haut">
		<h4>Liste des bouzouks de Vlurxtrznbnaxl</h4>
		<div class="bloc_bleu padd_vertical">
			<!-- Menu -->
			<p class="centre frameborder_bleu">
				<a href="<?= site_url('communaute/lister_bouzouks/tous') ?>">Tous</a>
				<?php for ($i = 65; $i < 65+26; $i++): ?>
					&nbsp;|&nbsp;<a href="<?= site_url('communaute/lister_bouzouks/'.chr($i)) ?>"><?= chr($i) ?></a>
				<?php endfor; ?>

				<?php if ($this->bouzouk->is_admin()): ?>
					<br><a href="<?= site_url('communaute/lister_bouzouks/robots') ?>">Robots</a> &nbsp; | &nbsp;
					<a href="<?= site_url('communaute/lister_bouzouks/inactifs') ?>">Inactif</a> &nbsp; | &nbsp;
					<a href="<?= site_url('communaute/lister_bouzouks/etudiants') ?>">Etudiant</a> &nbsp; | &nbsp;
					<a href="<?= site_url('communaute/lister_bouzouks/beta-testeurs') ?>">Bêta-Testeurs</a> &nbsp; | &nbsp;
					<a href="<?= site_url('communaute/lister_bouzouks/asile') ?>">Asile</a> &nbsp; | &nbsp;
					<a href="<?= site_url('communaute/lister_bouzouks/pause') ?>">Pause</a> &nbsp; | &nbsp;
					<a href="<?= site_url('communaute/lister_bouzouks/game_over') ?>">Game over</a> &nbsp; | &nbsp;
					<a href="<?= site_url('communaute/lister_bouzouks/bannis') ?>">Bannis</a>
				<?php endif; ?>
			</p>

			<p class="hr"></p>
			
			<!-- Liste des bouzouks -->
			<?php if ($filtre == '%' && ! isset($recherche)): ?>
				<div class="margin">
					<p class="centre pourpre">
						Clique sur une lettre de l'alphabet pour afficher les bouzouks<br>
						dont le pseudo commence par cette lettre
					</p>
				</div>
			<?php else: ?>
				<p class="mini_bloc"><span class="pourpre"><?= pluriel(count($joueurs), 'bouzouk') ?></span> trouvé(s) dans le répertoire</p>
			<?php endif; ?>
			
			<table class="liste_bouzouks">
				<tr>
					<?php $i = 0; ?>
					<?php foreach ($joueurs as $joueur): ?>
						<td><?= profil($joueur->id, $joueur->pseudo, $joueur->rang) ?></td>
						<?php if (++$i % 4 == 0): ?>
							</tr>
							<tr>
						<?php endif; ?>
					<?php endforeach; ?>
				</tr>
			</table>
		</div>
	</div>
</div>
