<?php $this->layout->set_title('Résultat du controuïlle'); ?>

<div id="controuille-prof">

	<div class="suite">
				<?php if ($note == 0): ?>
				<p class="bulle_prof1">
					<br>Quoi ?????? 0/20 ???!!!!<br>
					Mais tu es la honte de la ville !<br><br>
					Tu as du cocher les réponses au hazard<br>
					mais tu es tellement poissard que tu n'as<br>
					même pas trouvé une réponse !
				</p>
				<p class="bulle_prof2">
					<br>Tu as intérêt à<br>
					t'améliorer au prochain<br>
					controuïlle !!<br><br>
					Vas réviser avant !
				</p>
				<?php elseif($note < 6): ?>
				<p class="bulle_prof1">
					<br>Quelle honte ! <?= $note ?>/20 !<br><br>
					C'est inadmissible un zlotage pareil !!<br><br>
					Tu as du cocher les réponses au hazard<br>
					gros fainéant !!<br>
				</p>
				<p class="bulle_prof2">
					<br>Tu as intérêt à<br>
					t'améliorer au prochain<br>
					controuïlle !!<br><br>
					Vas réviser avant !
				</p>
				<?php elseif ($note < 10): ?>
				<p class="bulle_prof1">
					<br>Et bien...<br>
					Il y a du zlotage dans l'air !<br><br>

					Avec une note de <?= $note ?>/20 je crois<br>
					qu'un stimulateur d'intelligence ne te<br>
					ferais pas de mal....
				</p>
				<p class="bulle_prof2">
					<br>Tu as intérêt<br>
					à te ratraper au prochain<br>
					controuïlle !<br><br>
					Vas réviser avant !
				</p>
				<?php elseif ($note == 10): ?>
				<p class="bulle_prof1">
					<br>Ouf 10/20 !<br>
					Juste la moyenne !<br><br>
					Tu gagnes <?= struls($this->bouzouk->config('controuille_gain_struls')) ?> mais<br>
					je ne sais pas si tu les mérites<br>
					vraiment.....
				</p>
				<p class="bulle_prof2">
					<br><br>J'espère que tu<br>
					feras mieux que ça au<br>
					prochain controuïlle !
				</p>
				<?php elseif ($note < 14): ?>
				<p class="bulle_prof1">
					<br>Hum... <?= $note ?>/20....<br>
					C'est moyen quand même....<br><br>
					Tu es vraiment aller réviser ? Mouais...<br>
					C'était pas la peine de t'inscrire pour<br>
					craquer au bout de 10 minutes !
				</p>
				<p class="bulle_prof2">
					<br>Tu as intérêt à<br>
					réviser sérieusement si<br>
					tu ne veux pas louper le<br>
					prochain controuïlle !
				</p>
				<?php elseif ($note < 17): ?>
				<p class="bulle_prof1">
					<br>Tu as <?= $note ?>/20,<br>
					c'est bien mais peut mieux faire...<br><br>
					Puis je te soupçonne d'avoir triché!<br>
					Tu as une bonne note mais tu n'as<br>
					pas l'air d'avoir révisé !
				</p>
				<p class="bulle_prof2">
					<br>Je te préviens<br>
					qu'au prochain controuïlle,<br>
					je ne te lâcherais pas<br>
					d'une trompe !<br>
				</p>
				<?php else: ?>
				<p class="bulle_prof1">
					<br>Oh <?= $note ?>/20...
					<br>Oui en effet c'est pas mal...<br><br>

					Mais moi, à ton âge, je n'avais que<br>
					des notes aux abords de 25/20<br>
					alors ce n'est pas la peine de<br>
					prendre la grosse trompe !
				</p>
				<p class="bulle_prof2">
					<br>Et on verra si<br>
					tu vas faire un aussi<br>
					bon score au prochain<br>
					controuïlle...
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
