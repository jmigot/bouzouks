<?php
$this->layout->set_title('Gestion de mon compte');
$this->layout->ajouter_javascript('mon_compte.js');
$url_parrainage = site_url('visiteur/inscription/'.urlencode($this->session->userdata('pseudo')));
?>

<div id="mon_compte-index">
	<!-- Menu -->
	<?php $this->load->view('mon_compte/menu', array('lien' => 1)) ?>

	<!-- Parrainer un ami -->
	<div class="cellule_bleu_type1">
		<h4>Parrainer un ami</h4>
		<div class="bloc_bleu centre">
			<p class="margin noir">
				Tu peux parrainer un ami en lui envoyant automatiquement un email d'invitation. Si ton ami s'inscrit sur le jeu et commence à jouer, tu recevras un objet rare
				(<a href="<?= site_url('site/faq/inscription') ?>">après validation par un administrateur</a>).
			</p>

			<?= form_open('mon_compte/parrainer', array('class' => 'margin')) ?>
				<p>
					<span class="frameborder_bleu arrondi padd_vertical"> &nbsp;&nbsp;&nbsp; E-mail de ton ami : <input type="text" name="email" size="25" maxlength="100"></span>
					<input type="submit" value="Envoyer">
				</p>
			</form>
			<p class="pourpre margin partager">
				Partager ton lien de parrainage :<br><br>
				<a target="_blank" title="Twitter" href="https://twitter.com/share?url=<?= $url_parrainage ?>&amp;text=Rejoins+moi+vite+sur+Bouzouks.net&amp;via=Bouzouks" class="tweet-button" rel="nofollow" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=700');return false;">
						<img src="<?= img_url('reseaux_sociaux/twitter_icon.png') ?>" alt="Twitter" title="Twitter">
				</a>
				<a target="_blank" title="Facebook" href="https://www.facebook.com/sharer.php?u=<?= $url_parrainage ?>&amp;t=Rejoins+moi+vite+sur+Bouzouks.net" class="facebook-button" rel="nofollow" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=500,width=700');return false;">
					<img src="<?= img_url('reseaux_sociaux/facebook_icon.png') ?>" alt="Facebook" title="Facebook">
				</a>
				<a target="_blank" title="Google +" href="https://plus.google.com/share?url=<?= $url_parrainage ?>&amp;hl=fr" class="google-button" rel="nofollow" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=450,width=650');return false;">
					<img src="<?= img_url('reseaux_sociaux/gplus_icon.png') ?>" alt="Google +" title="Google +">
				</a>
				<a target="_blank" title="Linked In" href="https://www.linkedin.com/shareArticle?mini=true&amp;url=<?= $url_parrainage ?>&amp;title=Rejoins+moi+vite+sur+Bouzouks.net" class="linkedin-button" rel="nofollow" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=450,width=650');return false;">
					<img src="<?= img_url('reseaux_sociaux/linkedin_icon.png') ?>" alt="Linked In" title="Linked In">
				</a>
			</p>
		</div>
	</div>
	<!-- Association compte fb -->
		<div class="cellule_bleu_type1 marge_haut">
			<h4> Facebook </h4>
			<div class="bloc_bleu">
			<?php if($this->session->userdata('fb_id') == null): ?>
				Associer son compte facebook<br/>
				<?= form_open('mon_compte/assoc_fb', array('class'=>'centre margin')) ?>
				<input type="submit" value="Associer">
				<?= form_close(); ?>
			<?php else : ?>
				<p>Ton compte est bien associé à ton compte Facebook. :)</p>
			<?php endif; ?>
			</div>
		</div>
	<!-- Connexion Tobozon -->
	<?php if (count($robots) > 0): ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Connexion Tobozon</h4>
			<div class="bloc_bleu">
				<?= form_open('mon_compte/connexion_tobozon', array('class' => 'centre margin')) ?>
				<span class="frameborder_bleu arrondi padd_vertical"><select name="robot_id">
						<?php foreach ($robots as $robot): ?>
							<option value="<?= $robot->id ?>"><?= $robot->pseudo ?></option>
						<?php endforeach; ?>
					</select>
				</span>
					<input type="submit" value="Connexion">
				</form>
			</div>
		</div>
	<?php endif; ?>
	
	<!-- Changer de mot de passe -->
	<div class="cellule_gris_type2 marge_haut">
		<h4>Changer mon mot de passe</h4>
		<div class="bloc_gris">
			<?= form_open('mon_compte/changer_mot_de_passe', array('class' => 'marge_haut marge_bas')) ?>
				<table class="entier tab_separ">
					<!-- Ancien mot de passe -->
					<tr class="frameborder_gris">
						<td><label for="ancien_mot_de_passe">Mot de passe actuel</label></td>
						<td><input type="password" name="ancien_mot_de_passe" id="ancien_mot_de_passe" class="margin-mini" maxlength="30"></td>
					</tr>
					<!-- Nouveau mot de passe -->
					<tr class="frameborder_gris">
						<td><label for="nouveau_mot_de_passe">Nouveau mot de passe</label></td>
						<td><input type="password" name="nouveau_mot_de_passe" id="nouveau_mot_de_passe" class="margin-mini" maxlength="30" placeholder="6 caractères minimum"></td>
					</tr>

					<!-- Envoyer -->
					<tr>
						<td></td>
						<td><p><input type="submit" value="Valider"></p></td>
					</tr>
				</table>
			</form>
		</div>
	</div>

	<!-- Changer email -->
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Changer mon adresse email</h4>
		<div class="bloc_bleu">
			<?= form_open('mon_compte/changer_email', array('class' => 'marge_haut')) ?>
				<table class="entier tab_separ">
					<!-- Email actuel -->
					<tr class="frameborder_bleu">
						<td>Adresse email actuelle</td>
						<td><p class="pourpre margin-mini"><?= $this->session->userdata('email') ?></p></td>
					</tr>

					<!-- Nouvel email -->
					<tr class="frameborder_bleu">
						<td><label for="email">Nouvelle adresse email</label></td>
						<td><input type="email" name="email" id="email" maxlength="320" value="<?= set_value('email') ?>" class="margin-mini"></td>
					</tr class="frameborder_bleu">

					<!-- Ancien mot de passe -->
					<tr class="frameborder_bleu">
						<td><label for="mot_de_passe">Mot de passe</label></td>
						<td><input type="password" name="mot_de_passe" id="mot_de_passe" maxlength="30" class="margin-mini"></td>
					</tr>

					<!-- Envoyer -->
					<tr>
						<td></td>
						<td><input type="submit" value="Valider"></td>
					</tr>
				</table>
			</form>

			<p class="centre margin"><a href="<?= site_url('mon_compte/changer_email_confirmation') ?>">Changer d'email avec un code reçu</a></p>
		</div>
	</div>

	<!-- Mettre en pause -->
	<?php if (in_array($this->session->userdata('statut'), array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile))): ?>
		<div class="cellule_gris_type2 marge_haut">
			<h4>Mettre mon compte en pause</h4>
			<div class="bloc_gris">
				<p class="margin">Si tu ne peux pas t'occuper de ton bouzouk pendant un certain temps, mettre ton compte en pause permet de le bloquer jusqu'à ce que tu reviennes pour t'en occuper. Pendant la pause,
				les règles suivantes sont appliquées : </p>

				<ul>
					<li>La pause devra durer <span class="pourpre">2 jours minimum</span></li>
					<li>Tu ne pourras pas jouer à Bouzouks, tes stats ne bougeront pas, tu ne toucheras pas ton salaire, tu ne pourras pas voter, tu ne pourras pas envoyer de missive, etc.</li>
					<li>Si la pause dure plus de <span class="pourpre"><?= $this->bouzouk->config('maintenance_delai_pause_to_game_over') ?> jours</span>, ton compte passera en Game Over</li>
					<li>Si tu veux reprendre ta partie, il te suffira de te connecter et de cliquer sur &laquo; <span class="pourpre">Reprendre ma partie</span> &raquo;</li>
				</ul>

				<?= form_open('mon_compte/mettre_en_pause', array('class' => 'mettre_en_pause')) ?>
					<p class="centre margin">
						<!-- Payer les taxes -->
						<input type="checkbox" name="payer_taxes" id="payer_taxes" checked><label for="payer_taxes">Payer automatiquement mes taxes pendant la pause</label><br>

						<!-- Envoyer -->
						<input type="submit" name="mettre_en_pause" value="Mettre en pause">
					</p>
				</form>
			</div>
		</div>
	<?php endif; ?>
</div>

