<!-- Footer -->
<div id="footer">
	<div id="resume_footer">
		<p class="social">Nous suivre:</p> 
		<p>
			<a class="facebook" href="http://www.facebook.com/bouzouks">Facebook</a>
			<a class="twitter" href="http://twitter.com/Bouzouks">Twitter</a>
			<a class="tchat" href="<?= site_url('site/tchat') ?>">Le T'chat</a>
			<a class="pub lien_bannieres" href="<?= site_url('site/pub') ?>">Nos partenaires</a>
		</p>

		<?php $visiteur = $this->session->userdata('connecte') ? '' : ' stats_visiteur' ?>

		<div class="stats<?= $visiteur ?>">
			<p class="nb_piouk"><?= $this->lib_cache->nb_pioupiouks() ?></p>
			<p class="nb_connectes">
				<?php if ($this->session->userdata('connecte') && ! in_array($this->session->userdata('statut'), array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_ChoixPerso))): ?>
					<a href="<?= site_url('communaute/connectes') ?>"><?= pluriel($this->lib_cache->nb_connectes(), '<span style="font-size: 14px; font-weight: normal;"><br>connecté') ?></span></a>
				<?php else: ?>
					<?= pluriel($this->lib_cache->nb_connectes(), '<span style="font-size: 14px; font-weight: normal;"><br>connecté') ?></span>
				<?php endif; ?>
			</p>
			<p class="nb_actifs">
				<?php if ($this->session->userdata('connecte')): ?>
					<?= $this->lib_cache->nb_joueurs_actifs() ?>
				<?php else: ?>
					<?php
						$query = $this->db->select('id')->from('joueurs')->order_by('id', 'desc')->get();
						echo $query->row()->id;
					?>
				<?php endif; ?>
			</p>
		</div>
	</div>

	<div id="infos_footer">
		<div id="nous_suivre">
			<p class="contact">
				<?php if ($this->session->userdata('connecte')): ?>
					<a href="<?= site_url('joueur/deconnexion') ?>">Déconnexion</a> -
				<?php else: ?>
					<a href="<?= site_url('visiteur/inscription') ?>">Inscription</a> -
				<?php endif; ?>

				<a href="<?= site_url('site/team') ?>"><?= $this->session->userdata('connecte') ? 'La TeamBouzouk' : 'Contact / La team' ?></a> -
				<a href="<?= site_url('site/charte') ?>">Charte du jeu</a> -
				<a href="<?= site_url('site/mentions_legales') ?>">Mentions légales</a>
			</p>
			<p class="copyright">Copyright © 2004-2017 Tous droits réservés à Bouzouks.net</p>
			<div class="piouk"></div>
			
		</div>
		<p id="pied"><b>Partenaires :</b>
			<a href="http://www.lesmeilleurs-jeux.net" title="Les meilleurs jeux gratuits en ligne pour gagner des cadeaux" target="blank">LesMeilleursJeux</a> -
			<a href="http://www.sitafamille.com/jeux-virtuels-r29.html" target="blank">Sitafamille</a>  -
			<a href="http://www.sitacados.com" title="guide des jeux du web gratuits" target="blank">Mine d'or de jeux</a> -
			<a href="http://www.ghostokdo.com" target="blank">Ghostokdo</a> -
			<a href="http://www.fourmiland.fr" title="Jeu de gestion et de stratégie." target="blank">Fourmiland</a> -
			<a href="http://www.zombiz.fr" title="Jeu de survie multijoueur." target="blank">Zombiz!</a> -
			<a href="http://www.exolandia.com" title="Jeu de colonisation." target="blank">Exolandia</a> - 
			<a href="http://www.bellamagus.fr" title="Ecole de magie virtuel." target="blank">Bellamagus</a> -
			<a href="http://www.empireduprince.com" title="Jeu en ligne où vous incarnez un gangster prêt à tout pour devenir le parrain de la mafia. Gérez votre Empire et éliminez vos ennemis." target="blank">Jeu de Mafia</a> - 
			<a href="http://www.heroic-legend.com" title="Explore un monde médiéval fantastique fascinant !" target="blank">Heroic Legend</a> -
			<a href="http://www.jeux-alternatifs.com/Bouzouks-jeu788_hit-parade_1_1.html" target="blank"><b class="orange">Votez pour le site !</b></a>
		</p>	
	</div>
</div>

<!-- Javascripts -->
<script>
	var site_url = "<?= site_url() ?>";
	var img_url  = "<?= img_url() ?>/";
	var csrf_token = '<?= $this->security->get_csrf_hash() ?>';
	<?php if(isset($map) && is_array($map)) : ;?>
		var map_id = "<?= $map['id'] ?>";
		var map_type = "<?= $map['type'] ?>";
	<?php endif; ?>
	<?php if ($this->session->userdata('connecte')): ?>
		var id            = <?= $this->session->userdata('id') ?>;
		var faim          = <?= $this->session->userdata('faim') ?>;
		var sante         = <?= $this->session->userdata('sante') ?>;
		var stress        = <?= $this->session->userdata('stress') ?>;
		var avatar        = "<?= avatar() ?>";
		var websocket_auth = "<?= $_COOKIE['websocket_auth'] ?>";
	<?php endif; ?>
	var joueur_normal = <?= ($this->session->userdata('connecte') && $this->session->userdata('statut') == Bouzouk::Joueur_Actif) ? 'true' : 'false' ?>;
</script>

<script src="<?= javascript_url('libs/jquery-1.8.0.min.js') ?>"></script>
<script src="<?= javascript_url('fonctions.js') ?>"></script>

<?php if (in_array($this->session->userdata('statut'), array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile))): ?>
	<script src="<?= javascript_url('notifications.js') ?>"></script>
<?php endif; ?>

<?php if (function_exists('smiley_js')) { echo smiley_js(); } ?>

<?= $javascripts_for_layout ?>



<script src="<?= javascript_url('jquery.gallery.js') ?>"></script>
		<script type="text/javascript">
			$(function() {
				$('#dg-container').gallery({
					autoplay	:	true
				});
			});
		</script/>



<!-- Javascripts google -->
<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	ga('create', 'UA-40586023-1', 'bouzouks.net');
	ga('send', 'pageview');
</script>