<?php $this->layout->set_title('La prof Miss Augine !'); ?>

<div id="controuille-prof">
		<!-- Image de la prof -->
		<div class="bd">
			<div class="antiseche2 postit_note rotate4">
				<p>
					Antisèche<br>
					Renseigne toi sur l'histoire en consultant le <a href="<?= site_url('site/lexique') ?>" title="Lexique">lexique</a> et la <a href="<?= site_url('site/faq') ?>" title="Foire aux questions">FAQ</a>.
				</p>
			</div>
			<div class="antiseche1 postit_note rotate-4">
				<p>
					Antisèche<br>
					Tu vas jouer à un jeu de rôle dans le quel tu incarne un personnage.
				</p>
			</div>
			<!-- Reviser -->
            <p><a href="<?= site_url('site/faq') ?>" class="reviser"></a></p>
			<!-- Envoyer -->
			<?= form_open('controuille/controuille1', array('method' => 'get')) ?>
				<p><input type="submit" value="" class="commencer surbrillance"></p>
			</form>
		</div>
</div>
