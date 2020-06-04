<?php $this->layout->set_title('Bouzouks connectés'); ?>

<div id="communaute-connectes">
	<div class="cellule_bleu_type1 marge_haut">
		<h4><?= pluriel(count($joueurs), 'connecté') ?> sur Bouzouks</h4>
		<div class="bloc_bleu">
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

	<?php $this->load->view('communaute/connectes_tchat'); ?>
</div>