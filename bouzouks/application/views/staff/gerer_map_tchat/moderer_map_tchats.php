<?php
$this->layout->set_title($title);
$this->load->view('staff/gerer_map_tchat/menu', array('lien' => $lien));
$this->layout->ajouter_css('vlux_modo_tchat.css');
$this->layout->ajouter_javascript('vlux/vlux_tchat.js');
$this->layout->ajouter_javascript('vlux/vlux_functions.js');
$io_url = get_io_url();
$this->layout->ajouter_javascript_externe($io_url);
?>
<div class="cellule_bleu_type1 marge_haut">
	<h4>Le Gros Chat</h4>
	<article id="vlux_tchat" class="bloc_bleu">
		<ul id="vlux_tchat_output">
			<li id="output_chan_global"></li>
		</ul>
		<ul id="list_connectes"></ul>
		<ul id="list_chan" class="menu">
			<li id="chan_global" class="chan_actif">Global</li>
			<!-- <li id="chan_map_room_<?php //echo $map['id']; ?>">&nbsp;</li> -->
		</ul>
		<div id="input_tchat">
			<form id="tchat_sender" action="">
				<input id="tchat_message" type="text" autocomplete="off" maxlength="150"/><button id="tchat_send">Envoyer</button>
				<button id="tchat_suppr_msg">Supprimer messages</button>
			</form>
		</div>
	</article>
</div>
<div class="cellule_bleu_type1 marge_haut">
	<h4>Listes des tchats</h4>
	<article class="bloc_bleu">
		<table>
			<thead>
				<tr>
					<th>Nom de la map</th>
					<th>Nombre de connecté(s)</th>
					<th>Aller sur le chan</th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach($chans as $chan):
				?>
				<tr>
					<td id="chan_nom_<?= $chan['id'] ?>"><?= $chan['nom']; ?></td>
					<td id="chan_<?=$chan['id'] ?>"></td>
					<td><button id="<?=$chan['id'];?>" class="join_chan">Rejoindre le chan</button></td>
				</tr>
				<?php
					endforeach;
				?>
			</tbody>
		</table>
	</article>
</div>