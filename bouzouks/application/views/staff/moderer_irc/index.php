<?php $this->layout->set_title('Administration - Gestion du bot IRC'); ?>

<div id="staff-moderer_irc-index">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Gestion du bot IRC</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>
		
			<p class="highlight pourpre">Commandes</p>

			<div class="margin">
				<p class="centre pourpre italique">
					Les commandes en pourpre fonctionnent sur le chan, en notice ou en privé au bot.<br>
					Les autres commandes sont celles du réseau IRC. Des crochets indiquent un argument facultatif.
				</p>

				<?php if ($this->bouzouk->is_admin()): ?>
					<p class="margin rouge"><u>Administrateurs @</u></p>
					
					<ul>
						<li><span class="pourpre">!start</span> : active l'animation</li>
						<li><span class="pourpre">!stop</span> : désactive l'animation</li>
						<li><span class="pourpre">!say &lt;texte&gt;</span> : faire parler le bot</li>
						<li><span class="pourpre">!autovoice</span> : activer/désactiver l'autovoice pour chaque nouvel entrant</li>
					</ul>

					<ul>
						<li><span class="pourpre">!quizz start</span> : lance le quizz (il relit les questions de la config)</li>
						<li><span class="pourpre">!quizz stop</span> : arrête le quizz en cours</li>
						<li><span class="pourpre">!quizz regles</span> : lance l'affichage des règles</li>
						<li><span class="pourpre">!quizz pause</span> : mets le quizz en pause</li>
						<li><span class="pourpre">!quizz reprise</span> : reprend après une pause</li>
						<li><span class="pourpre">!quizz scores</span> : affiche les scores (toujours disponibles après la fin d'un quizz)</li>
						<li><span class="pourpre">!quizz moderation</span> : activer/désactiver les chut ban après plusieurs réponses fausses</li>
						<li><span class="pourpre">!quizz teams on</span> : ouvre les équipes</li>
						<li><span class="pourpre">!quizz teams off</span> : ferme les équipes</li>
						<li><span class="pourpre">!quizz reset</span> : supprime toutes les équipes</li>
						<li><span class="pourpre">!quizz teams</span> : affiche toutes les équipes</li>
						<li><span class="pourpre">!quizz leave &lt;pseudo&gt;</span> : retire &lt;pseudo&gt; de son équipe</li>
					</ul>

					<ul>
						<li><span class="rouge">!op [pseudo]</span> : mettre le statut d'admin</li>
						<li><span class="rouge">!deop [pseudo]</span> : enlever le statut d'admin</li>
						<li><span class="rouge">!halfop [pseudo]</span> : mettre le statut de modérateur</li>
						<li><span class="rouge">!dehalfop [pseudo]</span> : enlever le statut de modérateur</li>
					</ul>
				<?php endif; ?>

				<p class="margin bleu"><u>Modérateurs %</u></p>
				<ul>
					<li><span class="pourpre">!couleur &lt;couleur&gt;</span> : change la couleur du bot</li>
					<li><span class="pourpre">!gras</span> : active/désactive l'écriture en gras du bot</li>
					<li><span class="pourpre">!rumeur</span> : affiche une rumeur</li>
					<li><span class="pourpre">!chut &lt;pseudo ou ip&gt; [minutes]</span> : mets un <span class="pourpre">Chut Ban</span> sur le pseudo ou l'ip (5min par défaut)</li>
					<li><span class="pourpre">!dechut &lt;pseudo ou ip&gt;</span> : enlève un <span class="pourpre">Chut Ban</span> sur le pseudo ou l'ip</li>
					<li><span class="pourpre">!kick [pseudo]</span> : kicker quelqu'un</li>
					<li><span class="pourpre">!ban [pseudo]</span> : bannir et kicker</li>
					<li><span class="pourpre">!unban [pseudo]</span> : débannir quelqu'un</li>
				</ul>

				<ul>
					<li><span class="bleu">!halfop</span> : mettre le statut de modérateur</li>
					<li><span class="bleu">!dehalfop</span> : enlever le statut de modérateur</li>
					<li><span class="bleu">!voice [pseudo]</span> : mettre le statut de voice</li></li>
					<li><span class="bleu">!devoice [pseudo]</span> : enlever le statut de voice</li>
					<li><span class="bleu">!kick [pseudo] [raison]</span> : kicker quelqu'un</li>
					<li><span class="bleu">!kb [pseudo] [raison]</span> : bannir puis kicker quelqu'un</li>
					<li><span class="bleu">!unban [pseudo]</span> : débannir quelqu'un</li>
				</ul>

				<!-- /!\ Ce qui est ajouté ici doit aussi être intégré à la page tchat du jeu pour l'aide aux joueurs /!\ -->
				<p class="margin"><u>Tout le monde</u></p>
				<ul>
					<li><span class="pourpre">!fortune &lt;pseudo&gt;</span> : donne la fortune d'un bouzouk</li>
					<li><span class="pourpre">!invite &lt;pseudo&gt;</span> : envoie une notification d'invite à un bouzouk (fonctionne en privé à <span class="pourpre">Bouzouk</span>)</li>
					<li><span class="pourpre">!quizz join Mon équipe</span> : créer/rejoindre l'équipe "Mon équipe"</li>
					<li><span class="pourpre">!quizz join Mon équipe/mot_de_passe</span> : créer/rejoindre l'équipe "Mon équipe" avec un mot de passe</li>
					<li><span class="pourpre">!quizz leave</span> : quitter son équipe actuelle</li>
					<li><span class="pourpre">!bol &lt;1, 2 ou 3&gt;</span> : jouer au bonneteau</li>
				</ul>

				<ul>
					<li>!seen &lt;pseudo&gt;: voir la dernière fois que quelqu'un est venu</li>
				</ul>
			</div>

			<p class="highlight">Voir les fichiers logs</p>

			<table class="liste_bouzouks">
				<tr>
					<?php $i = 0; ?>
						<?php foreach ($logs as $log): ?>
							<td><a href="<?= site_url('staff/moderer_irc/voir_log/'.$log) ?>"><?= bouzouk_date($log, false) ?></a></td>
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
