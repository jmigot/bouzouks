$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Notifications                           */
	/*--------------------------------------------------*/
	var Notifications = function()
	{
		var _this = this;
		this.notifications_id            = '#notifications';
		this.url_webservice              = site_url + 'webservices/rafraichir_notifications';
		this.url_webservice_marquer_lues = site_url + 'webservices/marquer_lues_notifications';
		this.nb_notifs_max               = 7;
		this.pause_rafraichir            = 10000;

		this.rafraichir = function()
		{
			var dernier_id = $(_this.notifications_id+' .notifs_messages div:last-child p').attr('class');

			$.post(_this.url_webservice, { csrf_token: csrf_token, dernier_id: dernier_id }, function(reponse)
			{
				// On ajoute les nouvelles notifications s'il y en a
				if (reponse.notifications != '')
				{
					var ajout = '';

					$.each(reponse.notifications, function(cle, notif)
					{
						var dernier_id = $(_this.notifications_id+' .notifs_messages div:last-child p').attr('class');

						if (parseInt(notif.id) > parseInt(dernier_id))
						{
							var classe_lue = parseInt(notif.lue) == 0 ? 'non_lue' : 'lue';
							ajout += '<div class="'+classe_lue+'"><p class="'+notif.id+'"></p><p class="message">'+notif.texte+'</p><p class="date">'+notif.date+'</p></div>';
						}
					});

					// On ajoute les messages
					$(ajout).hide().appendTo(_this.notifications_id+' .notifs_messages').slideDown('slow');

					// On met à jour le nombre de notifs
					_this.update_nb_non_lues(parseInt(reponse.nb_notifs));

					// Supprimer les messages les plus anciens si besoin
					_this.vidanger();
				}

				if (parseInt(reponse.nb_notifs) == 0 && parseInt(reponse.nb_notifs) != parseInt($(_this.notifications_id+' .nb_notifs').text()))
					_this.reset_lues(parseInt(reponse.nb_notifs));
			});

			setTimeout(function() { _this.rafraichir(); }, _this.pause_rafraichir);
		}

		this.marquer_lues = function()
		{
			$.post(_this.url_webservice_marquer_lues, { csrf_token: csrf_token }, function(reponse)
			{
				if (reponse.code == 'ok')
					_this.reset_lues(parseInt(reponse.nb_notifs));
			});
		}
		
		this.vidanger = function()
		{
			if ($(_this.notifications_id+' .notifs_messages').children().length > 7)
				$(_this.notifications_id+' .notifs_messages div:first-child').slideUp(400, function() { $(this).remove(); _this.vidanger(); });
		}

		this.update_nb_non_lues = function(nb_notifs)
		{
			// On met à jour le texte
			var pluriel = (nb_notifs > 1) ? 's' : '';
			$(_this.notifications_id+' .notifs_trigger p').html('Tu as <span class="nb_notifs">'+nb_notifs+'</span> notification'+pluriel);

			// On active une animation si besoin
			if (nb_notifs == 0)
				$(_this.notifications_id+' .notifs_trigger p').attr('class', '');

			else
			{
				$(_this.notifications_id+' .notifs_trigger p').attr('class', 'gras rouge_notif');
				
				// On joue une alerte sonore
				$(_this.notifications_id+' #son_notifications').trigger("play");
				
				// On lance une animation
				_this.eclairer($(_this.notifications_id+' .notifs_trigger'));
			}

			// On change le titre de la page
			var regex = /^(\(\d+\) )?(.*)$/;
			var matches = document.title.match(regex);
			document.title = (nb_notifs == 0) ? matches[2] : '('+nb_notifs+') '+matches[2];
		}
		
		this.eclairer = function(element)
		{
			element.animate({opacity: 1.0}, 500, function () { _this.assombrir(element) });
		}

		this.assombrir = function(element)
		{
			element.animate({opacity: 0.1}, 500, function () { _this.eclairer(element) });
		}
		
		this.reset_lues = function(nb_notifs)
		{
			// On stop l'animation
			$(_this.notifications_id+' .notifs_trigger').stop().css({opacity: 1.0});
			
			// On marque toutes les notifications comme lues
			$(_this.notifications_id+' .notifs_messages div').each(function()
			{
				$(this).attr('class', 'lue');
			});

			// On met à jour le nombre de notifs
			_this.update_nb_non_lues(nb_notifs);
		}
	};

	// On démarre les notifications
	var notifications = new Notifications();
	notifications.rafraichir();

	// Afficher/Cacher les notifications
	$('#notifications .notifs_trigger').click(function()
	{
		$('#notifications .lien').slideToggle('slow');
		$('#notifications .notifs_messages').slideToggle('slow');
	});

	// Marquer comme lu
	$('#notifications .notifs_messages').click(function()
	{
		notifications.marquer_lues();
	});
});
