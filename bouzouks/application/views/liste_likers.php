<div id="liste_likers">
	<input type="button" value="Fermer" name="fermer" class="fl-droite">
	
	<ul>
		<?php foreach ($joueurs as $joueur): ?>
			<li><?= profil($joueur->id, $joueur->pseudo, $joueur->rang) ?></li>
		<?php endforeach; ?>
	</ul>
</div>
