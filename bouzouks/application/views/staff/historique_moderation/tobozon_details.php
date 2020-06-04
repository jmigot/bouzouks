<?php $this->layout->set_title('Administration - Historique'); ?>

<div id="staff-historique_moderation">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Historique de modération Tobozon</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/historique_moderation/tobozon') ?>">Retour historique modération Tobozon</a></p>
			
			<p class="noir margin centre">
				Le post Tobozon original a été créé par <?= profil($details->poster_id, $details->poster_pseudo, $details->poster_rang) ?> puis<br>
				<?= isset($details->moderateur_texte) ? 'modifié' : 'supprimé' ?> par <?= profil($details->moderateur_id, $details->moderateur_pseudo, $details->moderateur_rang) ?> le <?= bouzouk_datetime($details->date, 'court', false) ?>.
			</p>
		</div>
	</div>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Message original de <?= profil($details->poster_id, $details->poster_pseudo, $details->poster_rang) ?></h4>
		<div class="bloc_bleu">
			<p class="margin"><?= $this->lib_parser->remplace_bbcode(nl2br(form_prep($details->poster_texte))) ?></p>
		</div>
	</div>

	<?php if (isset($details->moderateur_texte)): ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Message modifié par <?= profil($details->moderateur_id, $details->moderateur_pseudo, $details->moderateur_rang) ?></h4>
			<div class="frame">
				<p class="margin"><?= $this->lib_parser->remplace_bbcode(nl2br(form_prep($details->moderateur_texte))) ?></p>
			</div>
		</div>
	<?php endif; ?>
</div>
