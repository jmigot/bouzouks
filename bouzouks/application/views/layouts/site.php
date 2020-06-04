<?php $pige = $this->lib_cache->derniere_pige(); ?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="description" content="Deviens un bouzouk et choisis ton destin en vivant dans un monde complètement dingue ! Choisis de te faire exploiter par ton patron, d'être complètement endetté par les taxes, d'être un gros joueur et de flamber tes struls, d'être un chef d'entreprise et d'exploiter d'autres bouzouks ou encore de devenir le maire de la ville !">
		<meta name="keywords" content="bouzouks,bouzouk,maire,strul,struls,simulation,vie,jeu,tobozon,plouk,schnibble,jeu de gestion,argent,tweedy,jeu multi joueurs,robby">

		<link rel="shortcut icon" href="<?= img_url('favicon.ico') ?>">
		<link rel="stylesheet" media="screen" type="text/css" href="<?= css_url('style.css') ?>">

		<?= $css_for_layout ?>

		<?php if ($this->bouzouk->is_moderateur()): ?>
			<link rel="stylesheet" media="screen" type="text/css" href="<?= css_url('modos.css') ?>?v=2">
		<?php endif; ?>

		<?php if ($this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurMulticomptes)): ?>
			<link rel="stylesheet" media="screen" type="text/css" href="<?= css_url('multicomptes_alea_t7u2.css') ?>?v=2">
		<?php endif; ?>

		<?php if ($this->bouzouk->is_admin()): ?>
			<link rel="stylesheet" media="screen" type="text/css" href="<?= css_url('admins_alea_f9p5.css') ?>?v=2">
		<?php endif; ?>
		<?php if (isset($map) && is_array($map)): ?>
			<link rel="stylesheet" media="screen" type="text/css" href="<?= css_url('vlux.css') ?>?v=2">
		<?php endif; ?>

		<title>Bouzouks - <?= $title_for_layout ?></title>
	</head>

	<body>
		<!-- Notifications -->
		<?php if (in_array($this->session->userdata('statut'), array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile))): ?>
			<div id="notifications">
				<p class="margin-mini centre lien"><a href="<?= site_url('historique/notifications') ?>">Toutes les notifications</a></p>

				<div class="notifs_messages">					
					<div>
						<p class="0"></p>
					</div>
				</div>

				<div class="notifs_trigger">
					<p class="">Tu as <span class="nb_notifs">0</span> notification</p>
				</div>

				<audio id="son_notifications">
					<source src="<?= son_url('notifications/notification.mp3') ?>" type="audio/mpeg">
					<source src="<?= son_url('notifications/notification.ogg') ?>" type="audio/ogg">
					Ton navigateur ne supporte pas les formats audios.
				</audio>
			</div>
		<?php endif; ?>

		<div id="clouds">
			<div id="superglobal">
				<!-- Pige -->
				<?php if (in_array($this->session->userdata('statut'), array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Etudiant))): ?>
					<div id="pige">
						<div class="bloc_vide"></div>
						<p<?= $this->session->userdata('derniere_pige_vu') < $pige->id ? ' class="bleu"' : ''; ?>>
							<span class="gras"><a href="<?= site_url('piges') ?>">Actualité du <?= bouzouk_datetime($pige->date, 'court') ?></a> :</span> <?= $this->lib_parser->remplace_bbcode(form_prep($pige->texte)) ?><?= $pige->lien != '' ? ' => <a href="'.form_prep($pige->lien).'">En savoir plus...</a>' : '' ?>
						</p>
						
						<?php $this->session->set_userdata('derniere_pige_vu', $pige->id); ?>
					</div>
				<?php endif; ?>
				
				<div id="page">
					<div class="haut-page">
						<div class="telescripteur">
							<p id="telescripteur2">
							</p>
						</div>

						<!-- Bulle -->
						<?php if ($this->session->userdata('statut') == Bouzouk::Joueur_Actif): ?>
							<canvas id="jauges" width="232" height="247">
								<div id="jauges">
									<p>
										<img src="<?= img_url(avatar()) ?>" alt="Bouzouk"><br>
									</p>
									<p>
										<a href="<?= site_url('joueur/deconnexion') ?>" title="Se déconnecter" class="logoutBtn">Déconnexion</a>
									</p>
								</div>
							 </canvas>
						<?php else: ?>
							<div id="jauges">
								<a href="<?= site_url('joueur/deconnexion') ?>" title="Se déconnecter" class="logoutBtn">Déconnexion</a>
							</div>
						<?php endif; ?>

						<div class="info_team">
							<?= remplace_smileys($this->bouzouk->config('jeu_message_header_'.($this->session->userdata('connecte') ? 'connecte' : 'visiteur'))) ?>
						</div>

						<p class="logo">
							<?php
								if($this->bouzouk->etat_event_mlbobz()){
									$logo = 'uploads/rp_zoukettes/titre.png';
								}
								else{
									$logo = 'design/titre.png';
								}
							?>
							<a href="<?= site_url() ?>"><img src="<?= img_url('./'.$logo) ?>" alt="Bouzouks.net"></a>
						</p>

						<!-- Menu horizontal -->
						<p class="menu_h">
							<a href="<?= site_url('site/accueil') ?>" class="acceuil"></a>
							<a href="<?= site_url('site/lexique') ?>" class="lexique"></a>
							<a href="<?= site_url('gazette') ?>" class="gazette"></a>
							<a href="<?= site_url('tobozon') ?>" class="toboz"></a>
							<a href="<?= site_url('site/faq') ?>" class="faq"></a>
						</p>

						<!-- Infos diverses -->
						<p class="pseudo_joueur"><?= profil($this->session->userdata('id'), $this->session->userdata('pseudo'), $this->session->userdata('rang')) ?></p>

						<!-- Struls -->
						<p class="info_struls">
							<img src="<?= img_url('./design/icones/struls.png') ?>">
							<?php if ($this->session->userdata('statut') != Bouzouk::Joueur_GameOver): ?>
								<?= round($this->session->userdata('struls'), 1) ?>
							<?php endif; ?>
							$
						</p>

						<!-- Fragments -->
						<p class="info_struls info_fragments">
							<img src="<?= img_url('./design/icones/fragments.png') ?>">
							<?= $this->session->userdata('fragments') ?>
						</p>

						<!-- Experience -->
						<p class="info_xp">
							<span><?= $this->session->userdata('experience') ?></span>
							<img src="<?= img_url('./design/icones/xp.png') ?>">
						</p>

						<!-- Amis connectés -->
						<?php if (in_array($this->session->userdata('statut'), array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile))): ?>
							<p id="info_amis"><a href="<?= site_url('amis') ?>" title="Nombre d'amis connectés"><?= pluriel($this->lib_cache->nb_amis_connectes($this->session->userdata('id')), 'ami') ?></a></p>
						<?php endif; ?>

						<!-- Missives -->
						<?php if (in_array($this->session->userdata('statut'), array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile)))
							{
								$nb_missives_non_lues = $this->bouzouk->get_nb_missives_non_lues($this->session->userdata('id'));

								if ($nb_missives_non_lues > 0): ?>
									<p><a class="info_nb_missives_nonlu" href="<?= site_url('missives') ?>" title="Mes missives"><?= $nb_missives_non_lues ?></a></p>
						<?php   else: ?>
									<p><a class="info_nb_missives_lu" href="<?= site_url('missives') ?>" title="Mes missives"><?= $nb_missives_non_lues ?></a></p>
						<?php 	endif;
							}
						?>

						<!-- Tchat -->
						<p class="info_tchat"><?= $this->lib_cache->nb_connectes_tchat() ?></p>
						<p><a class="tchatbtn" href="<?= site_url('site/tchat') ?>">Le tchat IRC</a><p>

							<?php
							// ---------- Event Bouf'tête -------------
							if($this->session->userdata('bouf_tete') == 1){
								$data = $this->bouzouk->infection_layout($this->session->userdata('id'));
								$this->load->view('bouf_tete/infection', $data);
							}
							// --------- Event MLboobz ----------------
							if($this->session->userdata('mlbobz') == 1){
								$data = $this->bouzouk->malediction_mlbobz_layout($this->session->userdata('id'));
								$this->load->view('event_mlbobz/malediction.php', $data);
							}


							 // --------- Event RP Zombies ------------
							if ($this->session->userdata('event_zomies_mordu')) :
							
								// On va chercher les zombies
								$query = $this->db->select('joueur_id')
												  ->from('event_joueurs_zombies')
												  ->get();
							
								foreach ($query->result() as $joueur)
									$zombies[] = $joueur->joueur_id;

								if ($this->session->userdata('event_zomies_morsures') > 0):
									$select = $this->bouzouk->select_joueurs(array(
												'name'		    => 'proie_id',
												'status_not_in' => array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_ChoixPerso, Bouzouk::Joueur_GameOver, Bouzouk::Joueur_Robot, Bouzouk::Joueur_Banni),
												'non_inclus'    => $zombies,
												'connectes'		=> true,
												'empty_return'  => false,
											));
									if ($select) :
								?>
								<div class="event_zombie">
									<?= form_open('joueur/mordre') ?>
										<label for="proie_id"></label>
										<?= $select ?>
										<br>
										<input type="submit" value="Mordre ce bouzouk" class="bouton_rouge">
									</form>
								</div>
								<?php
									else:
										?>
										<p class="event_zombie">
											<span class="blanc">Zut, il n'y a personne en vue à mordre...</span>
										</p>
										<?php
									endif;
								else:
								?>
								<p class="event_zombie">
									<span class="blanc">Tu n'as plus de salive...</span>
								</p>
								<?php
								endif;
							endif;
							// --------- Event RP Zombies ------------ ?>
					</div>

					<!-- Page -->
					<div class="millieu-page">
						<!-- Menu vertical -->
						<div id="menu_g">
							<?php
								$vars = array(
									'statut'               => $this->session->userdata('statut'),
									'employe'              => $this->session->userdata('employe'),
									'chef_entreprise'      => $this->session->userdata('chef_entreprise'),
									'chomeur'              => ( ! $this->session->userdata('employe')) && ( ! $this->session->userdata('chef_entreprise')),
									'nb_missives_non_lues' => isset($nb_missives_non_lues) ? $nb_missives_non_lues : 0
								);
								$this->load->view('layouts/menu', $vars);
							?>
						</div>

						<!-- Contenu de la page -->
						<div id="contenu">
							<!-- Titre de la page -->
							<h4 id="titre_page"><?= $title_for_layout ?></h4>
							<div class="box">
								<!-- Alerte JavaScript désactivé -->
								<noscript>
									<div class="msg-erreur">
										<h2><img src="<?= img_url('echec.png') ?>" alt="Echec">Javascript est désactivé !</h2>
										<p>Ton navigateur ne semble pas accepter le JavaScript.<br>
										Son activation est indispensable pour pouvoir utiliser certaines fonctions du jeu.</p>
									</div>
								</noscript>

								<?php if ($this->session->userdata('admin_connecte') !== false): ?>
									
									<!-- Connexion admin -->
									<div class="msg-erreur">
										<h2><img src="<?= img_url('echec.png') ?>" alt="Echec">Connecté via l'administration</h2>
										<p class="rouge gras">Attention ! Tu es connecté sur le compte de <?= profil($this->session->userdata('id'), $this->session->userdata('pseudo')) ?> ! <a href="<?= site_url('staff/connexion_bouzouk/revenir_compte') ?>">Revenir sur mon compte</a></p>
									</div>
							
								<?php endif; if ($this->session->userdata('flash_succes') !== false): ?>

									<!-- Message flash -->
									<div class="msg-succes">
										<h2><img src="<?= img_url('succes.png') ?>" alt="Succès"><?= $this->session->userdata('flash_succes_title') !== false ? $this->session->userdata('flash_succes_title') : 'Succès !' ?></h2>
										<p><?= $this->session->userdata('flash_succes') ?></p>
									</div>

								<?php
									$this->session->unset_userdata('flash_succes');
									$this->session->unset_userdata('flash_succes_title');
									endif;
									if ($this->session->userdata('flash_attention') !== false): ?>

									<div class="msg-attention">
										<h2><img src="<?= img_url('attention.png') ?>" alt="Attention">Attention !!!</h2>
										<p><?= $this->session->userdata('flash_attention') ?></p>
									</div>

								<?php
									$this->session->unset_userdata('flash_attention');
									endif;

									if ($this->session->userdata('flash_echec') !== false): ?>

									<div class="msg-erreur">
										<h2><img src="<?= img_url('echec.png') ?>" alt="Echec"><?= $this->session->userdata('flash_echec_title') !== false ? $this->session->userdata('flash_echec_title') : 'Erreur :(' ?></h2>
										<p><?= $this->session->userdata('flash_echec') ?></p>
									</div>

								<?php
									$this->session->unset_userdata('flash_echec');
									$this->session->unset_userdata('flash_echec_title');
									endif; ?>

									<!-- Erreurs de formulaire -->
									<?php
									$erreurs_validation = '';

									if (function_exists('validation_errors'))
										$erreurs_validation = validation_errors();

									if ($erreurs_validation != ''):
								?>

									<div class="msg-attention">
										<h2><img src="<?= img_url('echec.png') ?>" alt="Echec">Tu as fait des erreurs :</h2>
										<div><?= $erreurs_validation ?></div>
									</div>

								<?php endif; ?>

								<!-- Contenu de la page -->
								<?= $content_for_layout ?>
							</div>
						</div>
					</div>
					
					<div class="bas-page"></div>

					<?php if ($this->bouzouk->is_admin()): ?>
						<p class="centre margin"><?php $query = $this->db->select('id')->from('joueurs')->order_by('id', 'desc')->get(); echo $query->row()->id; ?> joueurs inscrits - <?= $this->db->count_all('joueurs') ?> joueurs dans la base - <?= pluriel($this->db->where('date > (NOW() - INTERVAL 5 MINUTE)')->count_all_results('visiteurs'), 'visiteur') ?></p>
					<?php endif; ?>	
				</div>

				<?php $this->load->view('layouts/footer'); ?>
			</div>
		</div>
	</body>
</html>
