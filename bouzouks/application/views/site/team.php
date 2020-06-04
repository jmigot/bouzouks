<?php
$this->layout->set_title('Contact - La TeamBouzouk');

function lister_membres($joueurs, $couleur, $connecte = false)
{
	echo '<div class="centre">';

	foreach ($joueurs as $joueur)
	{
		echo '<div class="inline-block">
					<!-- Titre -->
					<p class="highlight">'.profil($joueur->id, $joueur->pseudo, $joueur->rang).'</p>

					<!-- Infos -->
					<div>
						<p class="inline-block avatar">
							<img src="'.img_url(avatar(100, 100, 0, $joueur->perso)).'" alt="Avatar">
						</p>
						<p class="inline-block infos">
							<b>'.couleur(form_prep($joueur->rang_description), $couleur).'</b><br><br>';

		if ($connecte)
			echo '<a href="'.site_url('missives/ecrire/'.$joueur->id).'">Lui écrire</a>';

		echo '			</p>
					</div>
				</div>';
	}
	
	echo '</div>';
}

?>

<div id="site-team">
	<!-- Contact -->
	<div class="cellule_gris_type1 marge_haut">
		<h4>Contact</h4>
		<div class="bloc_gris">
			<p class="margin">
				Pour contacter les responsables du site par email :
				
				<?php if ($this->session->userdata('connecte')): ?>
					<span class="gras pourpre"><?= $this->bouzouk->config('email_from') ?></span>
				<?php else: ?>
					<img src="<?= img_url('teambouzouk.png') ?>" alt="L'adresse de la TeamBouzouk">
				<?php endif ?><br>
				Pour un contact rapide et direct, tu peux demander un administrateur sur <a href="<?= site_url('site/tchat') ?>">le tchat</a>.<br><br>

				Si tu veux contacter un administrateur ou un modérateur pour un sujet qui concerne le jeu, merci d'utiliser
				<a href="<?= site_url('missives/ecrire') ?>">la messagerie privée</a> ou <a href="<?= site_url('site/tchat') ?>">le tchat</a>.
			</p>
		</div>
	</div>
	
	<!-- Admins -->
	<div class="cellule_bleu_type2 marge_haut">
		<h4>Administrateurs</h4>
		<div class="bloc_bleu">
			<p class="margin centre">
				Les administrateurs <span class="rouge">[admin]</span> sont les créateurs du jeu, ils gèrent tout le site<br>
				ainsi que le serveur et nomment les autres membres de l'équipe.
			</p>

			<?php lister_membres($admins, 'rouge', $this->session->userdata('connecte')); ?>
		</div>
	</div>

	<!-- Développeurs -->
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Ancien Dieu Bouzouk</h4>
		<div class="bloc_bleu">
			<p class="margin centre">
				Ex PDG de la TeamBouzouk Corporation, actuellement toujours là en temps que consultant<br>et en attente de son Golden parachute.
			</p>

			<?php lister_membres($stars, 'pourpre', $this->session->userdata('connecte')); ?>
		</div>
	</div>

	<!-- Aides -->
	<div class="cellule_gris_type2 marge_haut">
		<h4>Aide développement et illustrations</h4>
		<div class="bloc_gris">
			<p class="margin centre">
				Les aides au développement et illustrations sont ceux qui nous aident ponctuellement<br>
				 à travailler sur le site, certains sont des anciens administrateurs.
			</p>

			<?php lister_membres($honneurs, 'pourpre', $this->session->userdata('connecte')); ?>
		</div>
	</div>

	<!-- Maîtres de jeu -->
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Maîtres de Jeu</h4>
		<div class="bloc_bleu">
			<p class="margin centre">
				Les Maître de Jeu <span class="vert_fonce">[MdJ]</span> sont des modérateurs spécialisés dans le rôle-play (RP)<br>
				qui font respecter la charte du site sur <span class="pourpre">le jeu</span> et sur <span class="pourpre">le tobozon</span>.
			</p>

			<?php lister_membres($mdj, 'vert_fonce', $this->session->userdata('connecte')); ?>
		</div>
	</div>

	<!-- Modérateurs -->
	<div class="cellule_bleu_type2 marge_haut">
		<h4>Modérateurs</h4>
		<div class="bloc_bleu">
			<p class="margin centre">Les modérateurs <span class="bleu">[modo]</span> font respecter la charte du site sur <span class="pourpre">le jeu</span>, sur <span class="pourpre">le tobozon</span> et sur <span class="pourpre">le tchat</span>.</p>

			<?php lister_membres($moderateurs, 'bleu', $this->session->userdata('connecte')); ?>
		</div>
	</div>

	<!-- Particiations -->
	<div class="cellule_gris_type1 marge_haut">
		<h4>Participations</h4>
		<div class="bloc_gris">
			<p class="margin centre">
			Un grand merci à <span class="pourpre">AdPatres</span>, <?= profil(69, 'Leela') ?> et <a href="http://nicolaspagan.com/">Todz</a> pour toutes leurs superbes illustrations réalisées pour le jeu.<br><br>
				Merci à <span class="pourpre">Diego</span> pour les sons du Plouk.<br>
				Merci à <?= profil(189, 'Jeoff') ?> pour sa contribution littéraire.<br>
				Merci à <span class="pourpre">caCtus</span> pour, entre autres, avoir complété des pages de la FAQ.<br>
				Merci à <?= profil(3239, 'Dedel') ?> pour ses illustrations réalisées pour les event.<br><br>
				Et merci  à <a href="http://www.doublurestylo.com/">Doublure Stylo</a> pour l'énorme travail de corrections de textes.<br>
			</p>
		</div>
	</div>
</div> 
