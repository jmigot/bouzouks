<?php $this->layout->set_title('La prof Miss Augine !'); ?>

<div id="controuille-prof">

	<div class="suite">
			<!-- Texte de la bulle -->
				<?php if ($note_controuille1 < 10): ?>
				<p class="bulle_prof1">
					<br>Tu es vraiment nul !<br>
					Même pas foutu d'avoir la moyenne<br>
					au controuïlle précédent !<br><br>
					Pourtant il était facile et celui-ci<br>
					sera beaucoup plus dur !
				</p>
				<p class="bulle_prof2">
					<br>Je te préviens<br>c'est ta dernière chance !<br>Si tu n'as pas au dessus de 10/20,<br>
					tu seras viré de l'école !<br> Allez, hop hop hop...
				</p>
				<?php else: ?>
				<p class="bulle_prof1">
					<br>Bon, tu ne t'es<br>
					pas trop mal débrouillé au<br>
					premier controuïlle mais tu as l'air<br>
					un peut trop fier de toi alors on va<br>
					placer la barre un poil plus haut !<br><br>
					J'espere que tu as révisé...
				</p>
				<p class="bulle_prof2">
					<br>Attention !<br>
					si, au prochain controuïlle,<br>
					tu n'as pas au-dessus de 10/20,<br>
					tu seras viré de l'école !
				</p>
				<?php endif; ?>
		<!-- Reviser -->
        <p><a href="<?= site_url('site/faq') ?>" class="reviser"></a></p>
		<!-- Commencer -->
		<?= form_open('controuille/controuille2', array('method' => 'get')) ?>
			<p><input type="submit" value="" class="commencer surbrillance"></p>
		</form>
	</div>

</div> 
