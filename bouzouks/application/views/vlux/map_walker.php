<?php
$this->layout->set_title($title);
$this->load->view('vlux/menu', array('lien' => $lien));
$this->layout->ajouter_javascript('libs/bt3Diso.js');

$this->layout->ajouter_css('pp3diso.css');
$this->layout->ajouter_javascript('vlux/map_walker.js');
$this->layout->ajouter_javascript('vlux/vlux_functions.js');
$this->layout->ajouter_javascript_externe($io_url);
?>
<!-- Notification sonore pour le tchat -->
<audio id="son_notifications_map_tchat">
					<source src="<?= son_url('notifications/blop.mp3') ?>" type="audio/mpeg">
					<source src="<?= son_url('notifications/blop.ogg') ?>" type="audio/ogg">
					Ton navigateur ne supporte pas les formats audios.
				</audio>
<div id="map-index">
	<div id="total">
		<div class="lumiere">
			<div id="carte">
				<div id="ppISO">
					<div id="pp3diso-fleche-se" class="pp3diso_users fleche"></div>
					<div id="pp3diso-fleche-ne" class="pp3diso_users fleche"></div>
					<div id="pp3diso-fleche-so" class="pp3diso_users fleche"></div>
					<div id="pp3diso-fleche-no" class="pp3diso_users fleche"></div>
					<div id="vlux_tchat" class="pp3diso_users">
						<ul id="list_chan">
							<li id="chan_global" class="chan_actif">Global</li>
							<li id="chan_map_room_<?php echo $map['id']; ?>">&nbsp;</li>
						</ul>
						<div id="vlux_tchat_output">
							<ul>
								<li id="output_chan_global"></li>
								<li id="output_chan_map_room_<?php echo $map['id']; ?>" ></li>
							</ul>
						</div>
						
					</div>
				</div>
			</div>
			<div id="vlux_interface" >
				<ul id="menu_std" class="vlux_menu vlux_menu_tchat ppd3diso_users">
					<li id="input_tchat">
						<?php if ( ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats) && $this->session->userdata('interdit_tchat') == 1): ?>
							<p class="rouge centre gras">Tu as été temporairement interdit de tchat par un modérateur ou un administrateur.</p>
						<?php else: ?>
							<form id="tchat_sender" action="#">
								<input id="tchat_message" type="text" autocomplete="off" maxlength="150"/><button id="tchat_send">Envoyer</button>
								<?php if ($this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats)): ?>
									<button id="tchat_suppr_msg">Supprimer messages</button>
								<?php endif; ?>
							</form>
						<?php endif; ?>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
<section class="cellule_bleu_type1 marge_bas">
	<h4>Vlurx 3D v0.5.2 &beta;</h4>
	<article class="bloc_bleu">
		<p class="margin">
			Pour cette mise à jour, une nouvelle commande "/tp maison" ainsi que les paramètres personnels concernant la map dans la page "mon compte".
			<ul>
				<li>/me [texte]: l'incourtournable</li>
				<li>/invite [pseudo] : permet d'inviter un joueur à vous rejoindre.(Les non béta-testeurs n'ayant pas accès à la map, merci de n'inviter que des béta-testeurs).</li>
				<li>/dispo [pseudo] : permet de savoir si un joueur est connecté et, si oui, s'il est sur une map.</li>
				<li>/signaler [texte] : en cas d'abus ou de non respect de la charte ou des règles du jeu, vous pouvez nous le signaler directement avec cette commande.</li>
				<li>/tp maison : cette commande vous permet de retourner dans votre résidence principale à tout moment.
			</ul>
			Comme d'hab', n'hésitez pas à nous faire par des bugs et problêmes que vous rencontrer sur le <a href="<?= site_url('tobozon/viewtopic.php?id=3149')?>">tobozon</a>
		</p>
	</article>
</section>
