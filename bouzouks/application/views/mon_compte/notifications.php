<?php
$this->layout->set_title('Gestion de mes notifs');

$notification_phrase = array(
	Bouzouk::Notification_PloukNouvellePartie   => "Être averti si une partie de plouk publique est créée",
	Bouzouk::Notification_DonMendiant		    => "Être averti si on vous fait un don dans la ruelle des mendiants",
	Bouzouk::Notification_PromoMairie		    => "Être averti si le maire fait une promotion sur un objet",
	Bouzouk::Notification_MissiveJoueur			=> "Être averti si un joueur vous envoit une missive",
	"Tobozon" => -1,
	Bouzouk::Notification_PseudoPrononceTobozon => "Être averti si on prononce votre pseudo sur le Tobozon",
	Bouzouk::Notification_ZlikeTobozon			=> "Être averti si un joueur Z'like un de vos posts",
);

if ($this->session->userdata('chef_entreprise') || $this->session->userdata('employe'))
{
	$notification_phrase["Entreprise"] = -1;
	$notification_phrase[Bouzouk::Notification_NouvelEmploye] = "Être averti si un nouvel employé rejoint votre entreprise";
	if ($this->session->userdata('chef_entreprise')) $notification_phrase[Bouzouk::Notification_AnnonceANPC] = "Être averti si un chômeur poste une annonce";
}
if ($this->session->userdata('chef_clan'))
{
	$notification_phrase["Clans"] = -1;
	$notification_phrase[Bouzouk::Notification_DonMembreClan] = "Être averti si membre de votre clan fait un don de p.a.";
	$notification_phrase[Bouzouk::Notification_QuitterMembreClan] = "Être averti si membre de votre clan le quitte";
}
?>
<div id="mon_compte-index">
	<!-- Menu -->
	<?php $this->load->view('mon_compte/menu', array('lien' => 3)) ?>

	<?php if ($this->session->userdata('statut') == Bouzouk::Joueur_Actif): ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Changer mes notifications</h4>
			<div class="bloc_bleu padd_vertical">
				<?= form_open('mon_compte/changer_notifications') ?>
				<table class="entier tab_separ">
					<tr>
						<td colspan="2"><p class="centre pourpre marge_haut">Général</p></td>
					</tr>
				<?php foreach ($notification_phrase as $notif_id => $phrase): ?>
					<?php if ($phrase == -1): ?>
					<tr>
						<td colspan="2"><p class="centre pourpre marge_haut"><?= $notif_id ?></p></td>
					</tr>
					<?php else: ?>
					<tr>
						<td class="frameborder_bleu"><?= $phrase ?> :</td>
						<td class="frameborder_bleu">
							<select name="notifs[<?= $notif_id ?>]">
								<option value="<?= Bouzouk::Notifications_Desactive ?>"<?= $notifications[$notif_id] == Bouzouk::Notifications_Desactive ? ' selected' : '' ?>>Jamais</option>
								<?php if ($notif_id == Bouzouk::Notification_PloukNouvellePartie): ?>
								<option value="<?= Bouzouk::Notifications_QuandConnecteEtAmi ?>"<?= $notifications[$notif_id] == Bouzouk::Notifications_QuandConnecteEtAmi ? ' selected' : '' ?>>Quand je suis connecté (et si c'est un ami)</option>
								<?php endif; ?>
								<option value="<?= Bouzouk::Notifications_QuandConnecte ?>"<?= $notifications[$notif_id] == Bouzouk::Notifications_QuandConnecte ? ' selected' : '' ?>>Quand je suis connecté</option>
								<option value="<?= Bouzouk::Notifications_ToutLeTemps ?>"<?= $notifications[$notif_id] == Bouzouk::Notifications_ToutLeTemps ? ' selected' : '' ?>>Tout le temps</option>
							</select>
						</td>
					</tr>
					<?php endif; ?>
				<?php endforeach; ?>
				</table>
				<p class="centre margin">
					<input type="submit" value="Modifier">
				</p>
				</form>
			</div>
		</div>
	<?php endif; ?>
</div>