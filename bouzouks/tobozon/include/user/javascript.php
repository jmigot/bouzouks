<?php
$site_url = str_replace('tobozon', '', get_base_url());

if ( ! $pun_user['is_guest']): ?>
	<div id="notifications">
		<p class="lien"><a href="<?= $site_url ?>historique/notifications">Toutes les notifications</a></p>

		<div class="notifs_messages">
			<div>
				<p class="0"></p>
			</div>
		</div>

		<div class="notifs_trigger">
			<p class="">Tu as <span class="nb_notifs">0</span> notification</p>
		</div>

		<audio id="son_notifications">
			<source src="<?= $site_url ?>webroot/sons/notifications/notification.mp3" type="audio/mpeg">
			<source src="<?= $site_url ?>webroot/sons/notifications/notification.ogg" type="audio/ogg">
			Ton navigateur ne supporte pas les formats audios.
		</audio>
	</div>
<?php endif; ?>

<script type="text/javascript">
	var site_url = "<?= $site_url ?>";
	var csrf_token = '<?= (isset($_COOKIE['csrf_token']) && preg_match('#^[0-9a-f]{32}$#iS', $_COOKIE['csrf_token']) === 1) ? $_COOKIE['csrf_token'] : '' ?>';
</script>
<script type="text/javascript" src="<?= $site_url ?>webroot/javascript/libs/jquery-1.8.0.min.js"></script>
<script type="text/javascript" src="javascript/functions.js"></script>
<script type="text/javascript" src="<?= $site_url ?>webroot/javascript/notifications.js"></script>
