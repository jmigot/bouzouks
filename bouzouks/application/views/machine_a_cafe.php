<?php
// Dans l'interface admin on affiche plein de tchats en même temps donc on évite de charger plusieurs fois le même fichier avec cette astuce
if ( ! isset($no_javascript_include))
{
	$this->layout->ajouter_javascript('tchat.js');
}
?>

<div class="machine_a_cafe">
	<div class="selecteur<?= isset($tchat_id) ? '_'.$tchat_id : '' ?>">
		<?php if (isset($tchat_id)): ?>
			<p><input type="hidden" name="tchat_id" value="<?= $tchat_id ?>"></p>
		<?php endif; ?>

		<p><input type="hidden" name="moderation" value="<?= isset($moderation) ? true : false ?>"></p>
		<p><input type="hidden" name="actif" value="<?= isset($actif) ? $actif : 1 ?>"></p>

		<!-- Urls webservice -->
		<p class="invisible url_rafraichir"><?= $url_rafraichir ?></p>
		<p class="invisible nb_messages_max"><?= $nb_messages_max ?></p>
		<p class="invisible url_poster"><?= $url_poster ?></p>
		<?= isset($url_supprimer) ? '<p class="invisible url_supprimer">'.$url_supprimer.'</p>' : '' ?>
		
		<!-- Messages -->
		<div class="div_messages">
			<p class="titre"><?= isset($titre) ? $titre : 'La machine à café' ?></p>
			<div class="messages">
			</div>
			<div class="tableau">
				<div class="connectes">
					<ul class="pseudos">
					</ul>
				</div>
			</div>

		<?php if ( ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats) && $this->session->userdata('interdit_tchat') == 1): ?>
			<p class="rouge centre gras margin">Tu as été temporairement interdit de tchat par un modérateur ou un administrateur.</p>
		<?php else: ?>
			<!-- Formulaire -->
			<div class="zone_saisi">
				<?= form_open('#', array('class' => 'formulaire')) ?>
					<p>
						<input type="text" name="message" id="message<?= isset($tchat_id) ? '_'.$tchat_id : '' ?>" maxlength="150" class="compte_caracteres">
						<span id="message_nb_caracteres_restants" class="format_2 centre transparent">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
						<input type="submit" value="ENVOYER">
					</p>
				</form>
			</div>
<!-- Smileys -->
			<div class="smileys">
				<?= $table_smileys ?>
			</div>
		<?php endif; ?>
		</div>
	</div>
</div>
