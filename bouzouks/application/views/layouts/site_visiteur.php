<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="description" content="Dans ce jeu de rôle textuel en ligne, multijoueurs et gratuit, deviens un Bouzouk et choisis de devenir un politique, patron d'entreprise ou maire de la ville.">

		<link rel="shortcut icon" href="<?= img_url('favicon.ico') ?>">
		<link rel="stylesheet" media="screen" type="text/css" href="<?= css_url('style.css') ?>">
		<link rel="stylesheet" href="<?= css_url('slider.css') ?>">
		<script src="<?= javascript_url('modernizr.custom.53451.js') ?>"></script>

		<title>Bouzouks - <?= $title_for_layout ?></title>
		<meta name="google-site-verification" content="x2wSGBsjanlGxGLLj-ExKRi2cv0kIYMHCRSdRnHBORw" />
	</head>

	<body>
		<div id="clouds_anim">
			<div id="superglobal_visiteur">
				<div class="bande_head"></div>
				<div class="persos"></div>
				
				<div id="page">
					<div class="tete-page">
						<div class="telescripteur">
							<div id="telescripteur2"></div>
						</div>

						<!-- Menu horizontal -->
						<ul class="menu_h">
							<li><a href="<?= site_url('site/accueil') ?>" >Accueil</a></li>
							<li><a href="<?= site_url('site/accueil') ?>" class="demo">Demo</a></li>
							<li><a href="<?= site_url('site/lexique') ?>" >Lexique</a></li>
							<li><a href="<?= site_url('gazette') ?>" >Gazette</a></li>
							<li><a href="<?= site_url('tobozon') ?>" >Toboz</a></li>
							<li><a href="<?= site_url('site/faq') ?>"  >Faq</a></li>
						</ul>

						<div class="info-team">
							<?= remplace_smileys($this->bouzouk->config('jeu_message_header_visiteur')) ?>
						</div>

						<p class="inscription surbrillance "><a href="<?= site_url('visiteur/inscription') ?>"></a></p>

						<!-- Bulle -->
						<div class="connexion">
							<!-- Formulaire connexion -->
							<?= form_open('visiteur/connexion') ?>
								<p class="form_connect">
									<input type="text" name="connexion_pseudo" id="connexion_pseudo" value="<?= set_value('connexion_pseudo') ?>" maxlength="15" class="input_text" placeholder="Pseudo">
									<input type="password" name="connexion_mot_de_passe" id="connexion_mot_de_passe" maxlength="30" class="input_text" placeholder="Mot de passe">
									<input type="submit" value="Jouer" class="bouton_rouge">
								</p>
								<div class="mdp_perdu">
										<a href="<?= site_url('visiteur/pass_perdu') ?>">Mot de passe perdu ?!</a>
										<a href="<?= site_url('visiteur/mail_non_recu') ?>">Mail non reçu ?</a>
								</div>
									<a href="<?= $fb_connexion ?>" class="connexion_fb">Se connecter</a>
							</form>
						</div>
					</div>

					<!-- Page -->
					<div class="corp-page">
						<h1>
							<a href="<?= site_url() ?>"><img src="<?= img_url('./design/titre.png') ?>" alt="Bouzouks.net"></a>
						</h1>

						<!-- Contenu de la page -->
						<div class="contenu">
							<!-- Titre de la page -->
							<div class="box">
								<!-- Alerte JavaScript désactivé -->
								<noscript>
									<div class="msg-erreur">
										<h2><img src="<?= img_url('echec.png') ?>" alt="Echec">Javascript est désactivé !</h2>
										<p>Ton navigateur ne semble pas accepter le JavaScript.<br>
										Son activation est indispensable pour pouvoir utiliser certaines fonctions du jeu.</p>
									</div>
								</noscript>

								<?php if ($this->session->userdata('flash_succes') !== false): ?>

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

									<div class="msg-erreur">
										<h2><img src="<?= img_url('echec.png') ?>" alt="Echec">Tu as fait des erreurs :</h2>
										<div><?= $erreurs_validation ?></div>
									</div>

								<?php endif; ?>

								<!-- Contenu de la page -->
								<?= $content_for_layout ?>
							</div>
						</div>
					</div>
					<div class="pied-page"></div>					
				</div>

				<?php $this->load->view('layouts/footer'); ?>
			</div>
		</div>
	</body>
</html>
