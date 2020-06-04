<?php $this->layout->set_title('Le controuïlle - La prof !'); ?>

<div id="controuille-prof">

	<div class="suite">
			<!-- Texte de la bulle -->
				<?php if ($note == 0): ?>
				<p class="bulle_prof1">
					Tu as eu <strong><?= $note ?>/20</strong> à ce controuïlle !<br>
					Tu es vraiment lamentable !<br><br>
					Tes mauvaises notes risquent de<br>
					contaminer les autres eleves !<br><br>

					<strong>Tu es viré !</strong><br><br>
				</p>
				<p class="bulle_prof2">
					Il ne te reste plus qu'à<br>
					aller chercher un boulot minable<br>
					quand tu seras apte à travailler !
				</p>

				<?php elseif ($note <= 2): ?>
				<p class="bulle_prof1">
					<br><strong><?= $note ?>/20 ?!</strong> Tu es vraiment nul !<br><br>
					Même avec un stimulateur d'intelligence<br>
					je ne suis pas sûr que tu réussisses<br>
					à avoir un jour la moyenne<br>
					à un controuïlle !<br><br>
				</p>
				<p class="bulle_prof2">
					<br><strong>Je ne veux plus<br>
					te voir dans cette école !</strong><br><br>

					C'est une perte de temps d'essayer de<br>t'enseigner quelque chose !
				</p>

				<?php elseif ($note <= 4): ?>
				<p class="bulle_prof1">
					<br>Tu n'as reussi<br>à avoir que <strong><?= $note ?>/20</strong>,<br>
					tu devrais avoir honte!<br>
					Personnellement, j'ai honte de<br>
					t'avoir dans cette école !<br><br>

					<strong>Tu es viré !</strong>
				</p>
				<p class="bulle_prof2">
					<br>Avec aussi peu<br>
					d'intelligence tu vas sûrement<br>
					finir nettoyeur de cuvettes !
				</p>

				<?php elseif ($note <= 6): ?>
				<p class="bulle_prof1">
					<br>Je me doutais bien<br>
					que tu raterais ce controuïlle !<br>
					<strong><?= $note ?>/20 ! Ah ah !</strong><br><br>
					Vu ton niveau médiocre...<br>
					Tu peux faire tes valises !
				</p>
				<p class="bulle_prof2">
					<br><br><strong>Je ne veux plus te voir<br>
					dans cette école !</strong>
				</p>
				<?php else: ?>
				<p class="bulle_prof1">
					<br>Seulement <strong><?= $note ?>/20 !</strong><br>
					Avec un niveau aussi bas tu<br>
					ne risques pas de devenir chef<br>
					d'entreprise un jour !<br>
					Au mieux tu deviendras<br>
					nettoyeur de cuvettes !
				</p>
				<p class="bulle_prof2">

					<br><br><strong>Allez dehors !</strong><br><br>
					Je ne veux plus te voir dans<br>
					cette école !
				</p>

				<?php endif; ?>

		<!-- Reviser -->
        <p><a href="<?= site_url('site/faq') ?>" class="reviser"></a></p>
		<!-- Envoyer -->
		<?= form_open('joueur/choix_perso', array('method' => 'get')) ?>
			<p><input type="submit" value="" class="cherchejob surbrillance"></p>
		</form>
	</div>

</div>
