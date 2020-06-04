<?php $this->layout->set_title('FAQ - Jobs'); ?>

<div id="site-faq-jobs">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Jobs</h4>
		<div class="bloc_bleu">
			<!-- Retour à la FAQ -->
			<p class="highlight centre"><a href="<?= site_url('site/faq') ?>">Retour à la FAQ</a></p>
			
			<p class="margin">
				Voici la liste des jobs possibles dans le jeu, ainsi que le salaire conseillé et l'expérience requise pour accéder à ce job.
			</p>

			<p class="margin pourpre">
				L'expérience du joueur est additionnée à son ancienneté dans l'entreprise pour déterminer son job. Ainsi, si un joueur démissionne il perd son ancienneté et risque d'avoir un job
				moins important dans sa prochaine entreprise.
			</p>

			<p class="margin">
				Si tu fais baisser ton expérience dans la journée, ton job sera automatiquement rétrogradé lors de la <a href="<?= site_url('site/faq/maintenance') ?>">maintenance</a>, avec le salaire conseillé de la FAQ.<br><br>

				Si tu as des stats de force, charisme ou intelligence suffisamment élevées, tu feras gagner un petit bonus à ton patron lors de la production. À toi de lui demander une augmentation de salaire en conséquence.
				Les bonus ne fonctionnent que si tu es dans de bonnes conditions physiques : <span class="pourpre">≥ 50% pour la faim et la santé</span> et <span class="pourpre">≤ 50% de stress</span>.<br><br>

				Ton patron peut savoir chaque jour si tu as eu droit au bénéfice lors de la production de la veille grâce à une icône verte <img src="<?= img_url('entreprises/bonus_oui.png') ?>" alt="Bonus oui" width="15"> ou rouge <img src="<?= img_url('entreprises/bonus_non.png') ?>" alt="Bonus non" width="15"> dans la gestion d'entreprise.
			</p>

			<table>
				<tr>
					<th>Job</th>
					<th>Expérience<br>+ ancienneté</th>
					<th>Salaire<br>conseillé</th>
					<th>Condition<br>pour le bonus</th>
					<th>Bonus pour<br>le patron</th>
				</tr>
				
				<?php
				$query = $this->db->select('nom, salaire, experience, stat, valeur, bonus')
								  ->from('jobs')
								  ->order_by('salaire')
								  ->get();
				$jobs = $query->result();

				foreach ($jobs as $job):
				?>
					<tr><td colspan="5"><p class="hr"></p></td></tr>
					<tr>
						<td><p class="highlight"><?= $job->nom ?></p></td>
						<td><?= $job->experience ?> xp</td>
						<td class="pourpre"><?= pluriel($job->salaire, 'strul') ?></td>
						<td>
							<?php if ($job->stat != ''): ?>
								<p class="highlight"><?= $job->stat.' ≥ '.$job->valeur ?></p>
							<?php endif; ?>
						</td>
						<td><?= $job->stat != '' ? couleur('+').struls($job->bonus) : '' ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
</div>
