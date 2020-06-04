<div class="cellule_bleu_type1 marge_haut">
	<h4><?= pluriel($this->lib_cache->nb_connectes_tchat(), 'connecté') ?> sur le tchat</h4>
	<div class="bloc_bleu">
		<table class="liste_bouzouks">
			<tr>
				<?php $i = 0; ?>
				<?php foreach ($this->lib_cache->liste_connectes_tchat() as $joueur): ?>
					<?php
						if (trim($joueur) == '')
							continue;
							
						// On récupère les modes du nick
						$modes_possibles = array('&', '~', '@', '%', '+');
						$modes = '';

						while (in_array($joueur[0], $modes_possibles))
						{
							$modes .= $joueur[0];
							$joueur = mb_substr($joueur, 1);
						}

						// Pseudo pas trop long
						if (mb_strlen($joueur) > 15)
							$joueur = substr($joueur, 0, 15).'...';

						$joueur = form_prep($joueur);
						
						// On rajoute un petit truc en fonction du mode
						if (strpos($modes, '&') !== false || strpos($modes, '~') !== false)
							$joueur = $joueur.' <span class="noir">[Bot]</span>';

						else if (strpos($modes, '@') !== false)
							$joueur = $joueur.' <span class="rouge">[Admin]</span>';

						else if (strpos($modes, '%') !== false)
							$joueur = $joueur.' <span class="bleu">[Modo]</span>';

						else if (strpos($modes, '+') !== false)
							$joueur = $joueur.' <span class="vert">[Starzouk]</span>';
					?>
					<td><?= $joueur ?></td>
					<?php if (++$i % 4 == 0): ?>
						</tr>
						<tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</tr>
		</table>
	</div>
</div>