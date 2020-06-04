<?php
$this->layout->set_title($title);
?>

<section class="cellule_bleu_type1 marge_bas">
	<h4><?= $title ?></h4>
	<article class="bloc_bleu">
		<p class="margin">
			<?php if($this->bouzouk->is_admin()): ?>
					Faudrait peut-être voir à démarrer le serveur non ? -.-
			<?php elseif($this->bouzouk->is_beta_testeur()) : ?>
					Les développeurs ont encore foiré un truc. Ou alors, tu n'as pas été assez gentil.<br/>
					Repasse voir quand ils seront plus détendus.
			<?php else: ?>
					Les développeurs sont à la plage et Martine chez le coiffeur. Donc, tu peux toujours apeller, y'a personne.<br/>
					Reviens plus tard.
			<?php endif; ?>
		</p>
	</article>
</section>