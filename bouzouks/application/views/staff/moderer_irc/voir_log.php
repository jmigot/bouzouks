<?php $this->layout->set_title('Administration - Gestion du bot IRC'); ?>

<div id="staff-moderer_irc-voir_log">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Voir les logs IRC du <?= bouzouk_date($date, false) ?></h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/moderer_irc') ?>">Retour</a></p>

			<div class="logs">
				<table>
					<?= $contenu ?>
				</table>
			</div>
		</div>
	</div>
</div>



