$(document).ready(function()
{
	var Tchat = function(messages_id, connectes_id, url_webservice, nb_messages_max, moderation, actif)
	{
		var _this = this;

		this.messages_id      = messages_id;
		this.connectes_id     = connectes_id;
		this.url_webservice   = site_url + url_webservice;
		this.nb_messages_max  = parseInt(nb_messages_max);
		this.pause_rafraichir = 3000;
		this.moderation       = moderation;
		this.actif            = actif;

		this.desactiver = function()
		{
			_this.actif = 0;
		}

		this.activer = function()
		{
			_this.actif = 1;
			_this.rafraichir();
		}

		this.rafraichir = function()
		{
			if ( ! _this.actif)
				return;

			var dernier_id = $(_this.messages_id+' p:last-child').attr('class') || '0';

			$.post(_this.url_webservice, { csrf_token: csrf_token, dernier_id: dernier_id }, function(reponse)
			{
				// On rafraîchit la liste des connectés
				$(_this.connectes_id).empty();

				if (reponse.connectes != '')
				{
					$.each(reponse.connectes, function(cle, joueur) {
						$('<li>'+joueur+'</li>').appendTo(_this.connectes_id);
					});
				}

				// On ajoute les nouveaux messages s'il y en a
				if (reponse.messages != '')
				{
					var ajout = '';
					
					$.each(reponse.messages, function(cle, message) {
						var dernier_id = $(_this.messages_id+' p:last-child').attr('class') || '0';

						if (parseInt(message.id) > parseInt(dernier_id))
						{
							var supprimer = '';
							
							if (_this.moderation == 1)
								supprimer = '<input type="checkbox" name="messages_ids[]" value="'+message.id+'">';
							
							// Le /me est possible
							if (message.message.substring(0, 4) == '/me ')
								ajout += '<p class="'+message.id+'">'+supprimer+'<b>'+message.date+' *'+message.pseudo+' </b>'+message.message.substring(4)+'</p>';

							else
								ajout += '<p class="'+message.id+'">'+supprimer+'<b>'+message.date+' '+message.pseudo+' : </b>'+message.message+'</p>';
						}
					});

					var scroll = false;
					
					// Si on est en bas de la page
					if ($(_this.messages_id).prop('scrollHeight') - $(_this.messages_id).height() == $(_this.messages_id).prop('scrollTop'))
						scroll = true;

					// On ajoute les messages
					$(ajout).appendTo(_this.messages_id);

					// On scroll
					if (scroll)
						$(_this.messages_id).animate({ scrollTop: $(_this.messages_id).prop('scrollHeight') - $(_this.messages_id).height() }, 100);

					// Supprimer les messages trop anciens de l'affichage
					var nb_enfants = $(_this.messages_id).children().length;

					for (var i=0; i<nb_enfants-_this.nb_messages_max; i++)
						$(_this.messages_id+' p:first-child').remove();
				}
			}).always(function()
			{
				setTimeout(function() { _this.rafraichir(); }, _this.pause_rafraichir);
			});
		}
	};

	// On démarre les tchats
	var tchats = new Object();
	var url_supprimer = '';

	$('div[class=machine_a_cafe]').each(function(t){
		var tchat_id        = $(this).find('input[type=hidden][name=tchat_id]').val();
		var moderation      = $(this).find('input[type=hidden][name=moderation]').val();
		var actif           = parseInt($(this).find('input[type=hidden][name=actif]').val());
		var selecteur       = '.machine_a_cafe .selecteur' + (typeof(tchat_id) == 'undefined' ? '' : '_'+tchat_id);
		var url_rafraichir  = $(selecteur+' p[class*=url_rafraichir]').text();
		var url_poster      = $(selecteur+' p[class*=url_poster]').text();
		url_supprimer       = $(selecteur+' p[class*=url_supprimer]').text();
		var nb_messages_max = $(selecteur+' p[class*=nb_messages_max]').text();
		var tchat           = new Tchat(selecteur+' .messages', selecteur+' .connectes .pseudos', url_rafraichir, nb_messages_max, moderation, actif);
		tchats[tchat_id]    = tchat;
		tchat.rafraichir();

		// Poster un message
		$(selecteur+' .formulaire').submit(function()
		{
			// On récupère le token csrf et le message
			var message = $(selecteur+' .formulaire input[name=message]').val();
			var max_length = $(selecteur+' .formulaire input[name=message]').attr('maxlength');

			if (message != '')
			{
				if (message.length > max_length)
				{
					alert('Ton message est trop long ! Réduis le un peu...');
					return false;
				}

				// On grise le formulaire
				$(selecteur+' .formulaire input[type=submit], '+selecteur+' .formulaire input[name=message]').attr('disabled', 'disabled');

				// On envoie le message
				$.post(site_url+url_poster, { csrf_token: csrf_token, message: message }, function()
				{
					// Lorsque la requête est bien arrivée, on vide le champ message, on dégrise le formulaire et on remet à 0 le nombre de caractères restants
					$(selecteur+' .formulaire input[name=message]').val('');
					$(selecteur+' .formulaire input[type=submit], '+selecteur+' .formulaire input[name=message]').removeAttr('disabled');
					$(selecteur+' .formulaire #message').trigger('keypress');
					$(selecteur+' .formulaire #message').focus();
				});
			}

			return false;
		});
	});

	// Supprimer un message
	$('input[type=button][name=machine_a_cafe_supprimer]').click(function()
	{
		if ( ! confirm('Veux-tu vraiment supprimer les messages sélectionnés ?'))
			return;
		
		var messages_ids = [];

		$('.machine_a_cafe .messages p').each(function(cle, val)
		{
			if ($(this).find('input[type=checkbox]').is(':checked'))
				messages_ids.push($(this).attr('class'));
		});

		// On envoie la demande de suppression du message
		$.post(site_url+url_supprimer, { csrf_token: csrf_token, messages_ids: messages_ids }, function()
		{
			// Lorsque la requête est bien arrivée, on supprime les messages concernés
			for (var i=0; i<messages_ids.length;  i++)
				$('p[class='+messages_ids[i]+']').remove();

			alert('Les messages ont bien été supprimés');
		});
	});

	/* Ouais j'ai mis cette fonction ici à la vue de tous, et alors ? Je m'appelle Robby et je t'emmerde :) Si tu trouves ce messages, viens le dire à Robby en privé sur le tchat, tu gagneras un objet rare ! */
	$('#staff-moderer_tchats input[type=button][name*=afficher_tchat_]').click(function()
	{
		var tchat_id = $(this).attr('name');
		tchat_id = tchat_id.split('_');
		tchat_id = tchat_id[2];

		if ($('div[class=machine_a_cafe] div[class=selecteur_'+tchat_id+']').is(':visible'))
		{
			tchats[tchat_id].desactiver();
			$('div[class=machine_a_cafe] div[class=selecteur_'+tchat_id+'] .messages p').remove();
			$(this).attr('value', 'Machine à café');
		}

		else
		{
			tchats[tchat_id].activer();
			$(this).attr('value', 'Fermer');
		}

		$('div[class=machine_a_cafe] div[class=selecteur_'+tchat_id+']').slideToggle();
	});
});
