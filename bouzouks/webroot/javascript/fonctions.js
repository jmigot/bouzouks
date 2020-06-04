$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Jauges d'état                           */
	/*--------------------------------------------------*/
	var Jauges = function(element_id, faim, sante, stress, avatar)
	{
		var _this = this;

		/* Variables du canvas */
		this.canvas  = $(element_id)[0];
		this.context = this.canvas.getContext('2d');
		this.W       = this.canvas.width;
		this.H       = this.canvas.height;

		/* Variables faim */
		this.faim_pourcent = 0;
		this.faim_total = faim;

		/* Variables santé */
		this.sante_pourcent = 0;
		this.sante_total = sante;

		/* Variables stress */
		this.stress_pourcent = 0;
		this.stress_total = stress;
		this.stress_x = 0;
		this.stress_y = 0;

		/* Images */
		this.avatar = new Image();
		this.avatar.src = img_url+avatar;
		this.bulles = new Image();
		this.bulles.src = img_url+'bulles2.png';
		this.deconnexion_1 = new Image();
		this.deconnexion_1.src = img_url+'design/boutons/logout_off.png';
		this.deconnexion_2 = new Image();
		this.deconnexion_2.src = img_url+'design/boutons/logout_on.png';

		this.deconnexion_image = 1;
		this.mouse_in = false;

		this.dessiner_texte_arc = function(context, texte, x, y, radius, angle)
		{
			var len = texte.length;
			
			context.save();
			context.translate(x, y);
			context.rotate(-1 * angle / 2);
			context.rotate(-1 * (angle / len) / 2);

			for (var i = 0; i < len; i++)
			{
				context.rotate(angle / len);
				context.save();
				context.translate(0, -1 * radius);
				context.fillText(texte[i], 0, 0);
				context.restore();
			}

			context.restore();
		}
		
		/* Dessins du bouton déconnexion */
		this.dessiner_deconnexion_1 = function()
		{
			// On remet l'image deconnexion_1
			_this.context.drawImage(_this.deconnexion_1, 135, 190);
			_this.deconnexion_image = 1;

			// On remet le curseur de la souris par défaut du navigateur
			$('body').css('cursor', 'default');
		}

		this.dessiner_deconnexion_2 = function()
		{
			// On met deconnexion_2 comme image pour l'effet
			_this.context.drawImage(_this.deconnexion_2, 135, 190);
			_this.deconnexion_image = 2;

			// On change le cuseur de la souris
			$('body').css('cursor', 'pointer');
		}

		this.souris_hover_deconnexion = function(x, y)
		{
			return x > 135 && x <= 185 && y > 190 && y <= 240;
		}
		
		/* Evènements du bouton déconnexion */
		this.mouse_out = function(e)
		{
			_this.dessiner_deconnexion_1();
			_this.mouse_in = false;
		}
		
		this.mouvement_canvas = function(e)
		{
			var position   = _this.position_curseur(_this.canvas, e);
			var x          = position.x;
			var y          = position.y;
			_this.mouse_in = true;
			
			// Si la souris est sur l'image de déconnexion
			if (_this.souris_hover_deconnexion(x, y))
			{
				// Si l'image n'est pas encore deconnexion_2
				if (_this.deconnexion_image == 1)
				{
					// On redessine les jauges sinon elles sont un peu mangées par le clear
					_this.clear();
					_this.dessiner_jauges();
					_this.dessiner_avatar();
					_this.dessiner_deconnexion_2();
				}
			}

			// Sinon si on sort de l'image et que l'image est toujours deconnexion_2
			else
			{
				if (_this.deconnexion_image == 2)
				{
					// On redessine les jauges sinon elles sont un peu mangées par le clear
					_this.clear();
					_this.dessiner_jauges();
					_this.dessiner_avatar();
					_this.dessiner_deconnexion_1();
				}
			}
		}

		this.clic_canvas = function(e)
		{
			// On récupère la position de la souris
			var position = _this.position_curseur(_this.canvas, e);
			var x = position.x;
			var y = position.y;

			// Si la souris est sur l'image de déconnexion
			if (_this.souris_hover_deconnexion(x, y))
				location.replace(site_url+'joueur/deconnexion');
		}

		this.position_curseur = function(el, event)
		{
			var ox = el.scrollLeft - el.offsetLeft;
			var oy = el.scrollTop - el.offsetTop;

			while (el = el.offsetParent)
			{
				ox += el.scrollLeft - el.offsetLeft;
				oy += el.scrollTop - el.offsetTop;
			}

			// Pour ceux qui n'ont pas Chrome il faut ajouter l'offset de la page
			if ( ! window.chrome)
			{
				ox += window.pageXOffset;
				oy += window.pageYOffset;
			}
			
			return { x: event.clientX + ox, y: event.clientY + oy };
		}

		/* Dessins des jauges */
		this.dessiner_jauges = function()
		{
			// Jauge faim
			if (this.faim_total > 0)
			{
				this.context.beginPath();
				this.context.strokeStyle = "#999";
				this.context.globalAlpha = 0.9;
				this.context.lineWidth = 9;
				this.context.lineCap = 'round';
				this.context.arc(117, 125, 110, 0.38*Math.PI, this.faim_pourcent/100*1.26*Math.PI+0.38*Math.PI);
				this.context.stroke();
			}

			// Dessin de la valeur en texte
			if (this.mouse_in)
			{
				this.context.font = '8pt Arial';
				this.context.fillStyle = 'purple';
				this.context.lineWidth = 5;
				this.dessiner_texte_arc(this.context, 'Faim : '+this.faim_total+'%           ', 117, 125, 107, Math.PI * 0.42);
			}

			// Jauge santé
			if (this.sante_total > 0)
			{
				this.context.beginPath();
				this.context.strokeStyle = "#97e";
				this.context.globalAlpha = 0.9;
				this.context.lineWidth = 9;
				this.context.lineCap = 'round';
				this.context.arc(117, 125, 97, 0.385*Math.PI, this.sante_pourcent/100*1.25*Math.PI+0.385*Math.PI);
				this.context.stroke();
			}

			// Dessin de la valeur en texte
			if (this.mouse_in)
			{
				this.context.font = '8pt Arial';
				this.context.fillStyle = 'blue';
				this.context.lineWidth = 5;
				this.dessiner_texte_arc(this.context, 'Santé : '+this.sante_total+'%                                      ', 116, 125, 93, Math.PI * 0.9);
			}
		
			// Jauge stress
			if (this.stress_total > 0)
			{
				this.context.save();
				this.context.globalAlpha = 0.6;
				this.context.beginPath();
				this.context.arc(117, 125, 82, 0, Math.PI * 2, false);
				this.context.clip();
				this.context.drawImage(this.bulles, this.stress_x, this.stress_y, 180, 228, 20, ((100 - this.stress_pourcent) * 164 / 100) + 30, 230, 228);
				this.context.restore();
			}

			// Dessin de la valeur en texte
			if (this.mouse_in)
			{
				this.context.font = '9pt Arial';
				this.context.fillStyle = 'red';
				this.context.lineWidth = 5;
				this.context.fillText('Stress : '+this.stress_total+'%', 80, 190);
			}
		}

		this.dessiner_avatar = function()
		{
			this.context.globalAlpha = 1.0;
			this.context.drawImage(this.avatar, (this.W - this.avatar.width) / 2, (this.H - this.avatar.height) / 2);
		}

		this.clear = function()
		{
			this.canvas.width = this.canvas.width;
			this.context.clearRect(0, 0, this.W, this.H);
		}

		/* Animation */
		this.dessiner = function()
		{
			// On efface l'animation
			this.clear();

			// On dessine les éléments
			this.dessiner_jauges();
			this.dessiner_avatar();
			this.dessiner_deconnexion_1();

			// Si l'animation n'est pas finie, on continue
			if (this.faim_pourcent < this.faim_total || this.sante_pourcent < this.sante_total || this.stress_pourcent < this.stress_total)
			{
				// On augmente les pourcentages
				if (this.faim_pourcent < this.faim_total)
					this.faim_pourcent++;

				if (this.sante_pourcent < this.sante_total)
					this.sante_pourcent++;

				if (this.stress_pourcent < this.stress_total)
					this.stress_pourcent++;

				// On rappelle la fonction
				setTimeout(function() { _this.dessiner(); }, 5);
			}

			// Sinon, fin de l'animation
			else
			{
				// On ajoute les évènements sur le bouton déconnexion
				this.canvas.addEventListener('mousemove', this.mouvement_canvas, false);
				this.canvas.addEventListener('mouseout', this.mouse_out, false);
				this.canvas.addEventListener('click', this.clic_canvas, false);

				// On lance l'animation du stress
				this.animation_stress();
			}
		}

		this.animation_stress = function()
		{
			_this.stress_x += 170;

			// x a atteint la fin du sprite sur la dernière ligne
			if (_this.stress_x >= 3 * 170 && _this.stress_y >= 6*230)
			{
				_this.stress_x = 0;
				_this.stress_y = 0;
			}
			
			// x a atteint la fin du sprite
			if (_this.stress_x >= 12 * 170)
			{
				_this.stress_x = 0;
				_this.stress_y += 230;					
			}
			
			_this.clear();
			_this.dessiner_jauges();
			_this.dessiner_avatar();

			if (_this.deconnexion_image == 1)
				_this.dessiner_deconnexion_1();

			else
				_this.dessiner_deconnexion_2();

			// On rappelle la fonction
			setTimeout(function() { _this.animation_stress(); }, 33);
		}
	};

	/*--------------------------------------------------*/
	/*          Téléscripteur                           */
	/*--------------------------------------------------*/
	var Telescripteur = function(element_id)
	{
		var _this = this;

		/* Variables */
		this.element = element_id;
		this.rumeurs = [];
		this.index_rumeur = null;
		this.index_caractere = null;
		this.pause_entre_caracteres = 50;
		this.pause_entre_rumeurs = 2000;

		/* Recharge un ensemble de rumeurs depuis un webservice */
		this.recharger_rumeurs = function()
		{
			$.post(site_url+'webservices/recharger_rumeurs', { csrf_token: csrf_token }, function(rumeurs)
			{
				if (rumeurs != '')
				{
					// On vide le tableau de rumeurs
					_this.rumeurs = [];
					_this.index_rumeur = null;

					// On ajoute chaque rumeur reçue au tableau
					$.each(rumeurs, function(cle, rumeur) {
						_this.rumeurs.push(rumeur);
					});

					_this.lancer_rumeur_suivante();
				}
			});
		}

		/* Lance le défilement de la prochaine rumeur du tableau de rumeurs */
		this.lancer_rumeur_suivante = function()
		{
			// Si l'index de rumeur est nul, on vient de recharger_rumeurs() et on commencer un nouveau pack de rumeurs
			if (_this.index_rumeur === null)
				_this.index_rumeur = 0;

			// Sinon on prend la rumeur suivante dans le tableau
			else
				_this.index_rumeur++;

			// Si on a fini les rumeurs, on en redemande des nouvelles
			if (_this.index_rumeur == _this.rumeurs.length)
				return _this.recharger_rumeurs();

			// On efface la rumeur actuelle du téléscripteur
			$(_this.element).empty();

			_this.index_caractere = 0;
			_this.afficher_rumeur();
		}

		/* Affiche le prochain caractère de la rumeur en cours */
		this.afficher_rumeur = function()
		{
			// Si la rumeur est finie, on lance la rumeur suivante
			if (_this.index_caractere >= _this.rumeurs[_this.index_rumeur].length)
			{
				var pause = _this.pause_entre_rumeurs;
				return setTimeout(function() { _this.lancer_rumeur_suivante(); }, pause);
			}

			// On ajoute le caractère à la rumeur en cours de défilement
			var caractere = _this.rumeurs[_this.index_rumeur].charAt(_this.index_caractere);
			$(_this.element).text($(_this.element).text()+caractere);
			_this.index_caractere++;

			// On rapelle cette fonction pour afficher le caractère suivant
			var pause = _this.pause_entre_caracteres;
			setTimeout(function() { _this.afficher_rumeur(); }, pause);
		}
	};

	/* Comptage des caractères */
	$("[class*='compte_caracteres']").bind("keyup keydown keypress mouseover", function()
	{
		// On calcule le nombre de caractères restants
		var nb_caracteres_restants = $(this).attr('maxlength') - $(this).val().length;
		var pluriel = (nb_caracteres_restants > 1) ? "s" : "";

		// Si on est sous Chrome, on compte le nombre de saut de lignes pour enlever autant de caractères restants
		if (window.chrome)
		{
			var lns = $(this).val().match(/\n/g);
	
			if (lns)
				nb_caracteres_restants -= lns.length;
		}

		// On met à jour le texte et on l'affiche en fonction du format
		if ($('#'+$(this).attr('name')+'_nb_caracteres_restants').hasClass('format_1'))
			$('#'+$(this).attr('name')+'_nb_caracteres_restants').html(nb_caracteres_restants+' caractère'+pluriel).fadeTo('fast', 1);

		else if ($('#'+$(this).attr('name')+'_nb_caracteres_restants').hasClass('format_2'))
			$('#'+$(this).attr('name')+'_nb_caracteres_restants').html('('+nb_caracteres_restants+')').fadeTo('fast', 1);

		else
			$('#'+$(this).attr('name')+'_nb_caracteres_restants').html('Il te reste '+nb_caracteres_restants+' caractère'+pluriel).fadeTo('fast', 1);
	});

	/* Confirmations */
	$("[class*='confirmation']").click(function() {
		return confirm('Tu es certain ?');
	});
	
	// Si le joueur est un joueur actif, on affiche l'animation
	// Sinon il est en pause ou à l'asile, on n'affiche rien
	if (joueur_normal)
	{
		// Canvas HTML5 si possible
		if (!!window.HTMLCanvasElement && !!window.CanvasRenderingContext2D)
		{
			var jauges = new Jauges('canvas#jauges', faim, sante, stress, avatar);
			jauges.dessiner();
		}
	}

	else
	{
		$('.menu_h .demo').click(function()
		{
			if ( ! $('#site-video-demo').is(':visible'))
			{
				var div = $('<div id="site-video-demo"><iframe width="516" height="316" src="//www.youtube.com/embed/0pyvE_rP94Y?autohide=1&amp;vq=hd720" frameborder="0" allowfullscreen></iframe></div>');
				var w = $(window);
				$(div).css(
				{
					'top': Math.abs((w.height() - 488) / 2 + w.scrollTop()),
					'left': Math.abs((w.width() - 700) / 2 + w.scrollLeft())
				}).appendTo('body').fadeIn('slow');
				$('#superglobal_visiteur').animate({'opacity': '0.5'}, 500);
			}
			return false;
		});

		$('#superglobal_visiteur').live('click', function(event)
		{
			if ($('#site-video-demo').is(':visible'))
			{
				$('#site-video-demo').remove();
				$('#superglobal_visiteur').animate({'opacity': '1.0'}, 1000);
			}
		});
	}

	// On démarre le téléscripteur
	var telescripteur = new Telescripteur('#telescripteur2');
	telescripteur.recharger_rumeurs();
});
