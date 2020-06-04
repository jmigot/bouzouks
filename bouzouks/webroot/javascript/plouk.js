$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Jouer                                   */
	/*--------------------------------------------------*/
	var Plouk = function()
	{
		var _this = this;

		/* Variables */
		this.partie_id             = $('#plouk-jouer #partie_id').text();
		this.messages_id           = '.machine_a_cafe .messages';
		this.connectes_id          = '.machine_a_cafe .connectes .pseudos';
		this.createur_id           = 0;
		this.adversaire_id         = 0;
		this.version               = -1;
		this.spectateur            = false;
		this.derniere_action       = 'jouer';
		this.createur_chrono       = 0;
		this.adversaire_chrono     = 0;
		this.chrono                = 0;
		this.intervalle_actualiser = 3000;
		this.carte_selectionnee    = 0;
		this.nb_messages_max       = parseInt($('.machine_a_cafe p[class*=nb_messages_max]').text());
		this.canvas                = $('#plouk-jouer #sondages')[0];
		this.context               = this.canvas.getContext('2d');
		this.W                     = this.canvas.width;
		this.H                     = this.canvas.height;
		this.attente_reseau        = false;
		this.activer_commentaires  = true;
		
		this.alerte_sonore = function(type)
		{
			if ($('#plouk-jouer #activer_son').is(':checked'))
				$('#plouk-jouer #son_'+type).trigger("play");
		}
		
		this.actualiser_interface = function(partie)
		{
			if (partie != '')
			{
				_this.chrono            = parseInt(partie.chrono);
				_this.createur_chrono   = parseInt(partie.createur_chrono_restant);
				_this.adversaire_chrono = parseInt(partie.adversaire_chrono_restant);
				_this.actualiser_chronos(false);

				if (partie.alert != '')
					alert(partie.alert);

				if (parseInt(partie.version) > _this.version)
				{
					_this.version = parseInt(partie.version);
					_this.createur_id = parseInt(partie.createur_id);
					_this.adversaire_id = parseInt(partie.adversaire_id);
					_this.derniere_action = partie.derniere_action;
					_this.spectateur = partie.createur_id != id && partie.adversaire_id != id;
					
					// Partie proposée ou terminée : on enlève les boutons
					if (partie.statut == 0 || partie.statut == 3)
					{
						$('#plouk-jouer #plouk_actions input[type=button]').fadeOut();

						if (partie.statut == 3)
						{
							if (this.spectateur || partie.gagnant_id == id || partie.gagnant_id == 0)
								this.alerte_sonore('gagne');

							else 
								this.alerte_sonore('perdu');

							// On autorise un hover sur la carte pour la montrer par-dessus le texte
							$('#plouk-jouer #defausse, #plouk-jouer #plouk_flash').live('mouseenter', function(event)
							{
								event.preventDefault();
								$('#plouk-jouer #defausse').css('z-index', 2000);
							}).live('mouseleave', function(event)
							{
								event.preventDefault();
								$('#plouk-jouer #defausse').css('z-index', 'auto');
							});
						}
					}

					// Partie en attente et le joueur n'est pas prêt
					else if (partie.statut == 1)
					{
						if ((partie.createur_id == id && partie.createur_pret == 0) || (partie.adversaire_id == id && partie.adversaire_pret == 0))
						{
							// On affiche le bouton "pret" et on lance l'alerte sonore
							$('#plouk-jouer #plouk_actions #plouk_commencer').fadeIn();
							this.alerte_sonore('notification');
						}

						$('#plouk-jouer #plouk_actions #plouk_quitter').fadeIn();
					}

					// Partie en cours : on affiche les boutons
					else if (partie.statut == 2)
					{
						$('#plouk-jouer #plouk_actions #plouk_commencer').fadeOut();
						$('#plouk-jouer #plouk_actions #plouk_abandonner').fadeIn();
						$('#plouk-jouer #plouk_actions #plouk_quitter').fadeIn();
					}

					// Un adversaire vient de rejoindre
					if (partie.adversaire_pseudo != null && $('#plouk-jouer #pseudo_adversaire').text() != partie.adversaire_pseudo)
					{
						$('#plouk-jouer #pseudo_adversaire').text(partie.adversaire_pseudo);
						$('#plouk-jouer #plouk_actions #plouk_commencer').fadeIn();
					}

					// On met à jour les infos du créateur
					$('#plouk-jouer #mediatisation_createur').text(partie.createur_mediatisation);
					$('#plouk-jouer #partisans_createur').text(partie.createur_partisans);
					$('#plouk-jouer #charisme_createur').text(partie.createur_charisme);
					$('#plouk-jouer #sondages_createur').text(partie.createur_sondages+'%');

					// On met à jour les infos de l'adversaire
					$('#plouk-jouer #mediatisation_adversaire').text(partie.adversaire_mediatisation);
					$('#plouk-jouer #partisans_adversaire').text(partie.adversaire_partisans);
					$('#plouk-jouer #charisme_adversaire').text(partie.adversaire_charisme);
					$('#plouk-jouer #sondages_adversaire').text(partie.adversaire_sondages+'%');

					// On affiche/cache les boutons de jeu
					if (partie.joueur_actuel == id)
					{
						$('#plouk-jouer #jouer').fadeIn();
						$('#plouk-jouer #defausser').fadeIn();
					}

					else
					{
						$('#plouk-jouer #jouer').fadeOut();
						$('#plouk-jouer #defausser').fadeOut();
					}

					// On met à jour les persos en fonction des sondages
					var createur_perso = partie.createur_sondages >= 65 ? 'content' : (partie.createur_sondages >= 35 ? 'normal' : 'triste');
					var adversaire_perso = createur_perso == 'content' ? 'triste' : (createur_perso == 'normal' ? 'normal' : 'content');
					$('#plouk-jouer #perso_createur img').attr('src', img_url+'perso/tete/'+partie.createur_perso+'_'+createur_perso+'.png');

					if (partie.adversaire_perso != '')
						$('#plouk-jouer #perso_adversaire img').attr('src', img_url+'perso/tete/'+partie.adversaire_perso+'_'+adversaire_perso+'.png');
					else
						$('#plouk-jouer #perso_adversaire img').attr('src', img_url+'vide.gif');
					
					// On met à jour les tribunes en fonction du joueur qui joue
					if (partie.joueur_actuel == partie.createur_id)
					{
						$('#plouk-jouer #pseudo_createur').css({'font-weight' : 'bold','-webkit-transform': 'skew(-10deg, 0deg)','transform': 'skew(-10deg, 0deg)','color' : '#F00','box-shadow' : '10px 5px 10px 3px #ff2a3f', 'opacity' : '1'});
						$('#plouk-jouer #pseudo_adversaire').css({'font-weight' : 'inherit','color' : '#000','box-shadow' : 'none', 'opacity' : '0.5'});
						$('#plouk-jouer #tribune_createur img').attr('src', img_url+'plouk/tribune_createur_parle.png');
						$('#plouk-jouer #tribune_adversaire img').attr('src', img_url+'plouk/tribune_adversaire_ecoute.png');

						// Ajout média/partisans à chaque tour
						$('#plouk-jouer #ajout_media_createur').fadeIn();
						$('#plouk-jouer #ajout_media_adversaire').fadeOut();
						$('#plouk-jouer #ajout_partisans_createur').fadeIn();
						$('#plouk-jouer #ajout_partisans_adversaire').fadeOut();
					}

					else if (partie.joueur_actuel == partie.adversaire_id)
					{
						$('#plouk-jouer #pseudo_adversaire').css({'font-weight' : 'bold','-webkit-transform': 'skew(10deg, 0deg)','transform': 'skew(10deg, 0deg)','color' : '#00F','box-shadow' : '-10px 5px 10px 3px #0084ff', 'opacity' : '1'});
						$('#plouk-jouer #pseudo_createur').css({'font-weight' : 'inherit','color' : '#000','box-shadow' : 'none', 'opacity' : '0.5'});
						$('#plouk-jouer #tribune_createur img').attr('src', img_url+'plouk/tribune_createur_ecoute.png');
						$('#plouk-jouer #tribune_adversaire img').attr('src', img_url+'plouk/tribune_adversaire_parle.png');

						// Ajout média/partisans à chaque tour
						$('#plouk-jouer #ajout_media_createur').fadeOut();
						$('#plouk-jouer #ajout_media_adversaire').fadeIn();
						$('#plouk-jouer #ajout_partisans_createur').fadeOut();
						$('#plouk-jouer #ajout_partisans_adversaire').fadeIn();
					}

					else
					{
						$('#plouk-jouer #pseudo_createur').css({'box-shadow' : 'none', 'opacity' : '1'});
						$('#plouk-jouer #pseudo_adversaire').css({'box-shadow' : 'none', 'opacity' : '1'});
						$('#plouk-jouer #tribune_createur img').attr('src', img_url+'plouk/tribune_createur_ecoute.png');

						// Ajout média/partisans à chaque tour
						$('#plouk-jouer #ajout_media_createur').fadeOut();
						$('#plouk-jouer #ajout_media_adversaire').fadeOut();
						$('#plouk-jouer #ajout_partisans_createur').fadeOut();
						$('#plouk-jouer #ajout_partisans_adversaire').fadeOut();
						
						if (partie.adversaire_id != null)
							$('#plouk-jouer #tribune_adversaire img').attr('src', img_url+'plouk/tribune_adversaire_ecoute.png');

						else
							$('#plouk-jouer #tribune_adversaire img').attr('src', img_url+'vide.gif');
					}

					// On monte/descend les tribunes en fonction du charisme
					var createur_margin = Math.floor((100 - parseInt(partie.createur_charisme)) * 128 / 100);
					var adversaire_margin = Math.floor((100 - parseInt(partie.adversaire_charisme)) * 128 / 100);
					var createur_margin_avant = parseInt($('#plouk-jouer #tribune_createur img').css('margin-top'));
					var adversaire_margin_avant = parseInt($('#plouk-jouer #tribune_adversaire img').css('margin-top'));
					
					// Si le createur a bougé
					if (createur_margin_avant != createur_margin && adversaire_margin_avant == adversaire_margin)
					{
						// Up
						if (createur_margin_avant < createur_margin)
							this.alerte_sonore('charisme_up');
						
						// Down
						else
							this.alerte_sonore('charisme_down');
					}
					
					// Si l'adversaire a bougé
					else if (adversaire_margin_avant != adversaire_margin && createur_margin_avant == createur_margin)
					{
						// Up
						if (adversaire_margin_avant < adversaire_margin)
							this.alerte_sonore('charisme_up');
						
						// Down
						else
							this.alerte_sonore('charisme_down');
					}
					
					$('#plouk-jouer #tribune_createur img').animate({'margin-top' : createur_margin}, 1000);
					$('#plouk-jouer #tribune_adversaire img').animate({'margin-top' : adversaire_margin}, 1000);

					// On met à jour la défausse
					if (partie.derniere_carte > 0)
					{
						$('#plouk-jouer #defausse img').fadeOut(500, function()
						{
							$(this).attr('src', img_url+'plouk/cartes/'+partie.derniere_carte+'.png');
						}).fadeIn(500);

						// Inversion
						if (partie.derniere_action == 'jouer' && (partie.derniere_carte == '15' || partie.derniere_carte == '26' || partie.derniere_carte == '46' || partie.derniere_carte == '49'))
							this.alerte_sonore('inversion');
					}

					else
						$('#plouk-jouer #defausse img').attr('src', img_url+'vide.gif');

					// On met à jour les infos de tours		
					$('#plouk-jouer #infos_tours').text(parseInt(partie.nb_tours) - parseInt(partie.tour_actuel));

					// On met à jour les cartes du joueur si il les a bien reçu (les spectateurs ne les reçoivent pas)
					_this.actualiser_carte(1, partie.carte_1);
					_this.actualiser_carte(2, partie.carte_2);
					_this.actualiser_carte(3, partie.carte_3);
					_this.actualiser_carte(4, partie.carte_4);
					_this.actualiser_carte(5, partie.carte_5);
					_this.actualiser_carte(6, partie.carte_6);

					// On redessine les sondages
					_this.dessiner_sondages(partie.createur_sondages, partie.adversaire_sondages);

					// Mise des messages flash
					if (partie.flash_permanent != '')
					{
						$('#plouk-jouer #plouk_flash').css('font-size', '20%').html(partie.flash_permanent).fadeIn().animate({'font-size' : '350%'}, 750);
						this.alerte_sonore('flash');
					}
					
					else if (partie.flash_temporaire != '')
					{
						$('#plouk-jouer #plouk_flash').css('font-size', '20%').html(partie.flash_temporaire).fadeIn().animate({'font-size' : '350%'}, 750).delay(1000).fadeOut('slow');
						this.alerte_sonore('flash');
					}
				}
			}
		}

		this.actualiser_tchat = function(reponse)
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
					var dernier_id = $(_this.messages_id+' p:last-child').attr('class');
					
					if (parseInt(message.id) > parseInt(dernier_id))
					{
						var supprimer = '';
						var invisible = '';
						
						if (_this.moderation == 1)
							supprimer = '<img src="'+img_url+'echec.png" class="machine_a_cafe_supprimer" id="message_'+message.id+'">';
						
						if ( ! _this.activer_commentaires && message.pseudo == '<span class="pourpre">Ella Poolett</span>')
							invisible = ' invisible';
							
						// Le /me est possible
						if (message.message.substring(0, 4) == '/me ')
							ajout += '<p class="'+message.id+invisible+'">'+supprimer+'<b>'+message.date+' *'+message.pseudo+' </b>'+message.message.substring(4)+'</p>';

						else
							ajout += '<p class="'+message.id+invisible+'">'+supprimer+'<b>'+message.date+' '+message.pseudo+': </b>'+message.message+'</p>';
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
		}

		this.actualiser_carte = function(indice, carte)
		{
			if (carte == '')
				return;
			
			var url = img_url+'plouk/cartes/'+carte+'.png';

			if (url != $('#plouk-jouer #carte_'+indice+' img').attr('src'))
			{
				$('#plouk-jouer #carte_'+indice+' img').fadeOut(500, function()
				{
					$('#plouk-jouer #carte_'+indice+' img').attr('src', url).attr('class', carte).fadeIn(500);
				});
			}
		}
		
		this.actualiser = function()
		{
			var dernier_id = $(_this.messages_id+' p:last-child').attr('class');
			
			$.post(site_url+'webservices_plouk/actualiser', { csrf_token: csrf_token, partie_id: _this.partie_id, dernier_id: dernier_id }, function(reponse)
			{
				if (reponse == '' || reponse.waiter == 1)
				{
					if ( ! _this.attente_reseau)
					{
						$('#waiter').fadeIn();
						var tmp = (this.spectateur) ? 'spectateur' : 'joueur';
						$('#plouk-jouer .partie .fond_'+tmp).animate({'opacity' : 0.2}, 400);
						_this.attente_reseau = true;
					}
				}

				else
				{
					if (_this.attente_reseau)
					{
						$('#waiter').fadeOut();
						var tmp = (this.spectateur) ? 'spectateur' : 'joueur';
						$('#plouk-jouer .partie .fond_'+tmp).animate({'opacity' : 1.0}, 400);
						_this.attente_reseau = false;
					}
				}

				if (reponse != '' && reponse.waiter != 1)
				{
					_this.actualiser_interface(reponse.partie);
					_this.actualiser_tchat(reponse);
				}
			}).always(function()
			{
				// On rappelle la fonction
				setTimeout(function() { _this.actualiser(); }, _this.intervalle_actualiser);
			});
		}

		this.poster_tchat = function()
		{
			// On récupère le token csrf et le message
			var message = $('.machine_a_cafe .formulaire input[name=message]').val();
			var max_length = $('.machine_a_cafe .formulaire input[name=message]').attr('maxlength');
			
			if (message != '')
			{
				if (message.length > max_length)
				{
					alert('Ton message est trop long ! Réduis le un peu...');
					return false;
				}
				
				// On grise le formulaire
				$('.machine_a_cafe .formulaire input[type=submit], .machine_a_cafe .formulaire input[name=message]').attr('disabled', 'disabled');
				
				// On envoie le message
				$.post(site_url+'webservices_plouk/poster_tchat', { csrf_token: csrf_token, message: message, partie_id: _this.partie_id }, function()
				{
					// Lorsque la requête est bien arrivée, on vide le champ message, on dégrise le formulaire et on remet à 0 le nombre de caractères restants
					$('.machine_a_cafe .formulaire input[name=message]').val('');
					$('.machine_a_cafe .formulaire input[type=submit], .machine_a_cafe .formulaire input[name=message]').removeAttr('disabled');
					$('.machine_a_cafe .formulaire #message').trigger('keypress');
					$('.machine_a_cafe .formulaire #message').focus();
				});
			}
			
			return false;
		}
		
		this.actualiser_chronos = function(recursif)
		{
			// On met à jour le chrono du créateur
			if (this.createur_chrono > 0 && this.createur_chrono <= this.chrono)
			{
				if (recursif)
					this.createur_chrono--;

				// Si c'est le joueur et que le chrono 
				$('#plouk-jouer #chrono_createur').text(this.createur_chrono);
			}

			else if ($('#plouk-jouer #chrono_createur').text() != '-- : --')
			{
				$('#plouk-jouer #chrono_createur').text('-- : --');

				// On prévient l'autre que c'est son tour
				if (this.adversaire_id == id)
					this.alerte_sonore('notification_2');

				else if (this.spectateur)
					this.alerte_sonore(this.derniere_action);
			}

			// On met à jour le chrono de l'adversaire
			if (this.adversaire_chrono > 0 && this.adversaire_chrono <= this.chrono)
			{
				if (recursif)
					this.adversaire_chrono--;

				$('#plouk-jouer #chrono_adversaire').text(this.adversaire_chrono);
			}

			else if ($('#plouk-jouer #chrono_adversaire').text() != '-- : --')
			{
				$('#plouk-jouer #chrono_adversaire').text('-- : --');

				// On prévient l'autre que c'est son tour
				if (this.createur_id == id)
					this.alerte_sonore('notification_2');

				else if (this.spectateur)
					this.alerte_sonore(this.derniere_action);
			}

			if (recursif)
				setTimeout(function() { _this.actualiser_chronos(true); }, 1000);
		}

		this.dessiner_sondages = function(createur_pourcent, adversaire_pourcent)
		{
			var createur_angle   = 2 * createur_pourcent / 100;
			var adversaire_angle = 2 * adversaire_pourcent / 100;
						
			// Clear
			this.canvas.width = this.canvas.width;
			this.context.clearRect(0, 0, this.W, this.H);
			this.context.globalAlpha = 0.9;
			
			// Sondage créateur
			this.context.beginPath();
			this.context.fillStyle = '#ff2a3f';
			this.context.arc(100, 50, 49, (1.5 + adversaire_angle) * Math.PI, (1.5 + adversaire_angle + createur_angle) * Math.PI);
			this.context.lineTo(100, 49);
			this.context.closePath();
			this.context.fill();

			// Sondage adversaire
			this.context.beginPath();
			this.context.fillStyle = '#0084ff';
			this.context.arc(100, 50, 49, 1.5 * Math.PI, (1.5 + adversaire_angle) * Math.PI);
			this.context.lineTo(100, 49);
			this.context.closePath();
			this.context.fill();
		}

		this.commencer = function()
		{
			$('#plouk-jouer #plouk_actions #plouk_commencer').fadeOut();
			$.post(site_url+'webservices_plouk/commencer', { csrf_token: csrf_token, partie_id: _this.partie_id });
		}
		
		this.abandonner = function()
		{
			if ( ! confirm("Tu es certain de vouloir abandonner cette partie ? Ce sera considéré comme une défaite. La honte..."))
				return false;
			
			$.post(site_url+'webservices_plouk/abandonner', { csrf_token: csrf_token, partie_id: _this.partie_id }, function(reponse)
			{
				if (reponse != '' && reponse.code == 1)
				{
					_this.actualiser_interface(reponse.partie);
					alert('Tu as abandonné la partie...lâche...');
				}
			});
		}

		this.quitter = function()
		{
			if ( ! confirm("Si la partie n'a pas commencé, tu sera libéré du jeu, si elle est en cours tu sera déclaré vainqueur. Ton adversaire doit être absent depuis un petit moment."))
				return false;
			
			$.post(site_url+'webservices_plouk/quitter', { csrf_token: csrf_token, partie_id: _this.partie_id }, function(reponse)
			{
				if (reponse != '')
				{
					// On remet la partie en jeu
					if (reponse.location == 1)
						location.replace(site_url+'plouk');

					// Le joueur a gagné
					else
						_this.actualiser_interface(reponse.partie);
				}
			});
		}
		
		this.jouer = function()
		{
			if (this.carte_selectionnee == 0)
			{
				alert('Il faut choisir une carte');
				return;
			}
			
			// On envoit la requête
			$.post(site_url+'webservices_plouk/jouer', { csrf_token: csrf_token, partie_id: _this.partie_id, carte: this.carte_selectionnee }, function(partie)
			{
				_this.deselectionner_cartes();
				_this.alerte_sonore('jouer');
				_this.actualiser_interface(partie);
			});
		}

		this.defausser = function()
		{
			if (this.carte_selectionnee == 0)
			{
				alert('Il faut choisir une carte');
				return;
			}
			
			// On envoit la requête
			$.post(site_url+'webservices_plouk/defausser', { csrf_token: csrf_token, partie_id: _this.partie_id, carte: this.carte_selectionnee }, function(partie)
			{
				_this.deselectionner_cartes();
				_this.alerte_sonore('defausse');				
				_this.actualiser_interface(partie);
			});
		}

		this.selectionner_carte = function(img)
		{
			if (this.carte_selectionnee == $(img).attr('class'))
			{
				// On désélectionne toutes les cartes
				plouk.deselectionner_cartes();
			}

			else
			{
				// On désélectionne toutes les cartes
				plouk.deselectionner_cartes();

				// On sélectionne la carte
				this.carte_selectionnee = $(img).attr('class');
				this.mouseover_carte($(img));
			}
		}

		this.deselectionner_cartes = function()
		{
			this.carte_selectionnee = 0;

			// On désélectionne toutes les cartes
			$('#plouk-jouer .carte img').css({'border': 'none', 'box-shadow' : 'none'});
		}

		this.mouseover_carte = function(carte)
		{
			$(carte).css({'border': '2px solid yellow', 'box-shadow' : '0 0 15px yellow'});
		}
		
		this.mouseout_carte = function(carte)
		{
			if (carte.attr('class') != this.carte_selectionnee)
				$(carte).css({'border': 'none'}).css({'box-shadow' : 'none'});
		}

		this.toggle_commentaires = function()
		{
			if (this.activer_commentaires && ! $('#plouk-jouer #activer_commentaires').is(':checked'))
			{
				this.activer_commentaires = false;

				var regex = /^.*<b>.*Ella Poolett.*<\/b>.*$/;

				$(_this.messages_id+' p').each(function()
				{
					if (regex.test($(this).html()))
						$(this).hide();
				});
			}

			else if ( ! this.activer_commentaires && $('#plouk-jouer #activer_commentaires').is(':checked'))
			{
				this.activer_commentaires = true;

				$(_this.messages_id+' p').each(function()
				{
					if ($(this).is(':hidden'))
						$(this).show();
				});

				$(_this.messages_id).animate({ scrollTop: $(_this.messages_id).prop('scrollHeight') - $(_this.messages_id).height() }, 50);
			}
		}
	};

	// On démarre le rafraichissement du jeu
	var plouk = new Plouk();
	plouk.actualiser();
	plouk.actualiser_chronos(true);

	// Clic sur "Je suis pret"
	$('#plouk-jouer #plouk_actions #plouk_commencer').click(function()
	{
		plouk.commencer();
	});

	$('#plouk-jouer #plouk_actions #plouk_abandonner').click(function()
	{
		plouk.abandonner();
	});

	$('#plouk-jouer #plouk_actions #plouk_quitter').click(function()
	{
		plouk.quitter();
	});

	// Clic sur une carte
	$('#plouk-jouer .carte img').click(function()
	{
		plouk.selectionner_carte(this);
	});

	// Clic sur "Jouer"
	$('#plouk-jouer #jouer').click(function()
	{
		plouk.jouer();
	});

	// Clic sur "Se défausser"
	$('#plouk-jouer #defausser').click(function()
	{
		plouk.defausser();
	});

	// Mouse over/out sur les cartes
	$('#plouk-jouer .carte img').mouseover(function()
	{
		plouk.mouseover_carte($(this));
	})
	.mouseout(function()
	{
		plouk.mouseout_carte($(this));
	});

	// Mouse over/out sur le bouton jouer
	$('#plouk-jouer #jouer img').mouseover(function()
	{
		$(this).css({'box-shadow' : 'none'});
	})
	.mouseout(function()
	{
		$(this).css({'box-shadow' : 'none'});
	});

	// Mouse over/out sur le bouton défausser
	$('#plouk-jouer #defausser img').mouseover(function()
	{
		$(this).css({'box-shadow' : 'none'});
	})
	.mouseout(function()
	{
		$(this).css({'box-shadow' : 'none'});
	});

	// Poster un message
	$('.machine_a_cafe .formulaire').submit(function()
	{
		return plouk.poster_tchat();
	});

	// Activer/Désactiver commentaires
	$('#plouk-jouer #activer_commentaires').click(function()
	{
		plouk.toggle_commentaires();
	});
});
