<?php $this->layout->set_title('Tu as provoqué une erreur '.$status_code); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Erreur <?= $status_code ?></h4>
		<div class="bloc_bleu">
			<p><img src="<?= img_url('becool.png') ?>" alt="Be cool" width="221" height="356" class="fl-droite"></p>

			<p class="margin">Alors là, incroyable, tu as réussi à casser complètement le site !!<br><br>

			La bouzopolice est déjà en chemin pour venir te chercher et t'enregistrer comme délinquant de Vlurxtrznbnaxl...</p>

			<?php if (isset($message) AND $message != ''): ?>
				<div class="msg-erreur">
					<?= $message ?>
				</div>
			<?php endif; ?>
			<p class="clearfloat"></p>
		</div>
	</div>
</div>
