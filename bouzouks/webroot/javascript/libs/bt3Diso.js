/**
 * bt3Diso
 *
 * @author Hikingyo
 * @version 0.1
 * 
 *
 * Bouzoks Team (c)2015 tous droits réservés
 *
 */
 (function($) {

        $.fn.bt3Diso = function(params) {
	 	var params = $.extend( {
	 		mode : 'walker',
	 		CSSid:'',	// se place devant chaque id pour du multi-cartes
			mapId:1,
			map: '',
			mapZones: '',
			mapZonesColors: '',
			zone:'',
			tx:0,
			ty:0,
			ty2:0,
			zoom:1,
			positionFixe:false,
			pathfinding:false,
			PF_corners:true,
			cursorPF:'',
			cursorZindex:0,		//-- décalage zindex du cursor (par rapport à la base objets)
			PF_decx:0,
			PF_decy:0,
			PF_max:false,
			mousewheel:false,		// zoom avec la molette
			zoom_min:0.25,
			zoom_max:10,
			zoom_pas:0.5,
			fluide:true,			//--- on bouge la map de façon fluide ou pas
			nbrTitleSetsSlide:1,		//-- nbr de tuiles avant de faire bouger la map
			auto_resize:true,
			
			titre_map:{},
			titre_case:'',
			move_avatar_speed:50,	//-- vitesse du changement de sprite lors d'un déplacement
			
			bulle_auto_x:true,
			bulle_auto_y:'top',
			bulle_obj_deca_y:6,
			
			keys:false,			//--- active le clavier

			affichage_pseudo : 0, // Affichage du pseudo des autres joueurs (au survol | permanent)
			
			keys_tab: {
				'key_n': [38,104],
				'key_ne':[105],
				'key_e':[39,102],
				'key_se':[99],
				'key_s':[40,98],
				'key_so':[97],
				'key_o':[37,100],
				'key_no':[103],
				'key_zoom':[33],
				'key_dezoom':[34],
				'key_clic':[32,101]
			},
			
			prefix:'map-',
			path:'images/',
			fogofwar:0,
			speed_avatar:250,
			speed_map:100,
			speed_map_while:100,
			speed_by_titleset:1,
			drag:true,

			cursorDelay:false,		// une fonction après un délai donné sur une case
			oncursordelay:function(x, y, mapId) {},
								// 
			onmoveavatar:function(x, y, mapId){},
			beforeonemoveavatar:function(x, y){},			//--- après que l'avatar ait bougé
			beforemoveavatar:function(x, y, mapId){ return true; },		// avant que l'avatar bouge
			onmovepathfinding:function(x, y, mapId){},			//--- après chaque mouvement du pathfinding
			beforeclosewin:function(id, etat) { return true; },	// avant de fermer le dialogue
			afterclosewin:function(id, etat) {},			// après fermeture
			onenterbuilding:function(x, y, mapId){},			// la souris survol un batiment
			onleavebuilding:function(x, y, mapId){},			// la souris sort d'un batiment
			onenterobject:function(x, y, mapId){},			// la souris entre un objet
			//onmoveobjet:function(x, y, mapId){},			// la souris survol un objet
			onleaveobject:function(x, y, mapId){},			// la souris sort un objet
			onclicbuilding:function(x, y, mapId){},	// clic sur un bâtiment
			onclicobject:function(x, y, mapId){},	// clic sur un objet
			onchangezoom:function(zoom){},// lorsque le zoom est changé par la molette
			onclicmap:function(x,y){}, // Lorsqu'on clique sur la map			
			onclicright:'',
			}, params);
	 	
		var obj = $(this);
		var obj_this = this;
		//var HAS_TOUCH = ('ontouchstart' in window);
		var HAS_TOUCH =false; // patch touche enable browser.

		if(params.positionFixe == true) {
			params.drag = false;
		}
		
		params.ty2 = (params.tx >> 2);
		var idMapForWebKit = 1;	//--- pour le bug sur WebKit des map dynamic
		
		var theBrowser = '';
		if($.browser.msie && (($.browser.version)|0)<9) {
			theBrowser = 'msie';
		}
		var padx = 0;
		var pady = 0;
		var padxFixe = 0;
		var padyFixe = 0;
		
		var zoom;
		
		var paramstx = params.tx;	// sauvegarde des données de base
		var paramsty = params.ty;
		var paramsty2 = params.ty2;
		
		var avatar = '';
		var avatar_decx = 0;
		var avatar_decy = 0;
		var avatar_posx = 1;
		var avatar_posy = 1;
		var avatar_decx_ori, avatar_decy_ori, avatar_ny;
		var avatar_img_tx, avatar_img_ty;
		var move_avatar_step = 0;	//--- varie de 0 à avatar_ny
		var move_avatar_sens = 0;	//--- 1:bas, 2:gauche, 3:droite, 4:haut
		var move_avatar_time = null;//--- timerInterval
		var move_avatar_sens_memory = [];
		var avatar_z_index = [];	//-- pour les animations de l'avatar
		var avatar_animation = false;

		var players = [];
		var move_player_sens = [];
		var move_player_sens_memory = [];
		var player_PFz = [];
		var player_z_index = [];
		var move_player_time = [];
		var player_img_tx = [];
		var player_img_ty = [];
		var move_player_step = [];
		var player_mapPF;
		var mapPlayers =[];
		var obj_bulle_pseudo; // Bulle pour affichage du pseudo d'un joueur
		var timer_bulle_tchat = [];
		var duree_bulle_tchat = 4000;
		
		var cursor_tx, cursor_ty;
		var cursor_nb_frame = 1; // Nombre de frame pour l'animation du curseur
		var allreadyCursorAnimate = false; //Si le curseur est en cours d'animation
		var cursor_animate_step = 0;
		var cursor_on = '';
		var cursor_off = '';
		var cursor_decx = 0;
		var cursor_decy = 0;
		var cursor_posx = 0;
		var cursor_posy = 0;
		var alt = 0; // Position en z du curseur
		
		var move_map_while_flag = false;	// on bouge ou pas la map en timer
		var timer_map_move;			// le timer pour bouger la map
		var move_map_actually = false;	// est-ce que la map est en train de bouger ?
		
		var touchpad_flag = false;		// pour contrer le coup du simple click / touchstart sur iOS
		
		var mapPF;	// map pour le pathfinding;
		var avatar_PFz = [];	//-- mémorise le z-index de l'avatar pour les mouvements
		var PF_max = params.PF_max;

		var obj_wait;
		
		var obj_tip;		//-- la bulle d'info
		var etat_tip = false;//--- la bulle est visible ?
		
		var fileBase, cols, Mapy, Mapx, zone, fow, monde;
		var posx, posy, tx2, ty2, tailleMapX, tailleMapY, tailleMapX2;
		
		var o_objets = [];		//-- liste des objets
		
		var Zones, canvas, canvas_ctx;
		var taille3DisoX, taille3DisoY, obj_conteneur;
		var mouseDownX, mouseDownY;
		var mouseDownEtat = false;
		
		var init_pos_map_x, init_pos_map_y;	//--- position initiale avant un drag & drop
		var allreadyDragDrop = false;		//-- déjà un drag & drop en cours ?
		
		var nbrObjLoad = 0;
		move_map_waiting = false;
		init();

		var positions;	//--- cache pour les fonction posX et posY
		
		var cursorDelayTimer = null;	// le timer pour setTimeout
		var cursorDelayObj = '';		// l'objet sur lequel on passe
		var cursorDelayEtat = 1;		// si 0, alors pas de fonction (changé par un appel)

		/**
		 * Initialisation du module
		 */
		function init() {
			zoom = ~~(params.zoom*100) + '%';
			params.tx = paramstx * params.zoom;
			params.ty = paramsty * params.zoom;
			params.ty2 = paramsty2 * params.zoom;
			
			etat_tip = false;
			
			taille3DisoX = obj.width();
			taille3DisoY = obj.height();
		
			if(params.zone == 'undefined') params.zone = '';
			var user_interface = obj.children('.pp3diso_users').detach();
			obj.children().remove().empty();

			obj.append(user_interface);
			$('.pp3diso_users').css({
				'z-index':760000
			});
			
			obj.append('<div id="pp3Diso-wait"></div>');
			obj_wait = $('#pp3Diso-wait');
			
			cols = params.map.split(':');
			if(params.zone != '') {	//---- définition des zones
				var dummyZone = params.zone.split(':');
			}
			fileBase = params.path + params.prefix;
			
			
			dummy = cols[0].split(',');
			Mapy = cols.length;
			Mapx = dummy.length;
			
			mapPF = new Array(Mapx);
			for(x = 0; x<Mapy;x++) {
				mapPF[x] = new Array(Mapy);
			}
			
			tx2 = (params.tx >> 1);
			ty2 = params.ty2;
			
			//tailleMapX = Mapx * (params.tx);
			//tailleMapY = Mapy * (ty2 << 1) + (params.ty2);
			tailleMapX = (Mapx * ((params.tx) >> 1)) + (Mapy * ((params.tx) >> 1));
			tailleMapY = (Mapx * ((params.ty) >> 1)) + (Mapy * ((params.ty) >> 1));
			tailleMapX2 = tailleMapX >> 1;
			
			if(params.mapZones!= '') {	//---- définition des zones de couleurs
				var dummyMapZones = params.mapZones.split(':');
			}
					
			zone = new Array(Mapy);		//--- les zones : sol, batiments, ...
			fow = new Array(Mapy);		//--- niveau de visibilité fog of war
			monde = new Array(Mapy);	//--- le sol
			mapPlayers = new Array(Mapy); // La carte des joueurs
			positions = new Array(Mapy);
			Zones = new Array(Mapy);	//--- les zones de couleurs
			
			posx = 0;
			posy = 0;
			ppkill_avatar();
			for(var y = 1;y<=Mapy;y++) {
				monde[y] = new Array(Mapx);
				zone[y] = new Array(Mapx);
				Zones[y] = new Array(Mapx);
				fow[y] = new Array(Mapx);
				mapPlayers[y] = new Array(Mapx);
				positions[y] = new Array(Mapx);
				var rows = cols[y-1].split(',');
				if(params.zone != '') {
					var rowsZone = dummyZone[y-1].split(',');
				}
				if(params.mapZones != '') {
					var rowsZones = dummyMapZones[y-1].split(',');
				}
				for(var x=1;x<=Mapx;x++) {
					monde[y][x] = ~~(rows[x-1]);
					mapPlayers[y][x]=0;
					if(params.fogofwar>0) {
						fow[y][x] = 0;
					}else{
						fow[y][x] = 1;
					}
					_killBatiment(x, y);
					
					if(params.zone != '') {
						zone[y][x] = ~~(rowsZone[x-1]);
					}else{
						if(monde[y][x] > 0) {
							zone[y][x] = 	1;
						}else{
							zone[y][x] = 	0;
						}
					}
					
					if(params.mapZones != '') {
						Zones[y][x] = (rowsZones[x-1]|0);
					}else{
						Zones[y][x] = 0;
					}
				}
			}
			
			if(params.auto_size) {
				obj.css({
					'width':tailleMapX,
					'height':tailleMapY
				});
			}
			padx = ((taille3DisoX - tailleMapX) >> 1);
			pady = ((taille3DisoY - tailleMapY) >> 1);
			
			padxFixe = padx;
			padyFixe = pady;
			
			first(monde);
			
			_moveTo((Mapx|0), (Mapy|0));

			view(monde);

			$('#ppISO').bind('mouseenter', function() {
				$('#ppISO').focus();
			});
			$('#ppISO').focus();
		}
		
				
		/**
		* Création des div
                * @param map
		*/
		function first(map) {
			cursor_tx = 0;
			cursor_ty = 0;
			var id = '';
			var coord = [0,ty2, tx2,0, params.tx,ty2, tx2,ty2<<1];
			
			var borderZ = (params.zoom << 3);
			var borderZ2 = (borderZ>>1);
			var coordZ = [-borderZ,ty2, tx2,-borderZ2, params.tx+borderZ,ty2, tx2,(ty2<<1)+borderZ2];
			
			if($('#pp3Diso-conteneur').length == 0) {
				obj.append('<div id="pp3Diso-conteneur"></div>');
			}
			$('.pp3diso').unbind('keyup');
			obj_conteneur = $('#pp3Diso-conteneur');
			obj_conteneur.css({
				'display':'block',
				'position':'absolute',
				'top':9999,
				left:9999,
				'width':tailleMapX,
				'height':tailleMapY
			});
			
			if(params.mousewheel) {
				getMouseWheel();
			}
			var colors = params.mapZonesColors.split(':');
			if(params.mapZones!= '' && $('#pp3Diso-mapZone').length == 0) {	//---- définition des zones de couleurs
				obj_conteneur.append('<div id="pp3Diso-mapZone"></div>');

				var nbCanvas = colors.length;
				canvas = new Array(nbCanvas);
				canvas_ctx = new Array(nbCanvas);
				var canvasHTMLDOM = new Array(nbCanvas);
				for(var i=0;i<nbCanvas;i++) {

					canvasHTMLDOM[i] = document.createElement("canvas");
					canvasHTMLDOM[i].id = 'pp3Diso-mapZone-canvas-' + i;
					$('#pp3Diso-mapZone').append(canvasHTMLDOM[i]);
					if($.browser.msie && !window.HTMLCanvasElement) {
						canvas[i] = window.G_vmlCanvasManager.initElement(canvasHTMLDOM[i]);
					}else{
						canvas[i] = $('#pp3Diso-mapZone-canvas-' + i)[0];
					}
					canvas_ctx[i] = canvas[i].getContext("2d");

					canvas[i].width = tailleMapX;
					canvas[i].height = tailleMapY;
					
					$('#pp3Diso-mapZone-canvas-' + i).css({
						'display':'none',
						'position':'absolute',
						'top':0,
						'left':0,
						'z-index':299000,
						'-moz-opacity':0.5,
						'opacity': 0.5,
						'filter':'alpha(opacity=50)'
					});
				}
				
				$('#pp3Diso-mapZone').css({
					'display':'block',
					'position':'absolute',
					'top':0,
					'left':0,
					'width':tailleMapX,
					'height':tailleMapY,
					'z-index':299000
				});
				
			}
			obj_wait.css('display', 'block');
			
			if($('#pp3Diso-bulle').length == 0) {
				obj_conteneur.append('<div id="pp3Diso-bulle"></div>');
			}
			obj_tip = $('#pp3Diso-bulle');
			obj_tip.css('display', 'none');
			// Si affichage du pseudo au survol
			if(params.affichage_pseudo == 0){
				if($('#bulle-pseudo').length == 0 ){
					obj_conteneur.append('<div id="bulle-pseudo" class="bulle_player_pseudo"></div>');
				}
				obj_bulle_pseudo = $('#bulle-pseudo');
				obj_bulle_pseudo.css('display', 'none');
			}
			var idMap = 'pp3diso-Map-' + idMapForWebKit;
			idMapForWebKit++;
			
			var laMap = '<map name="' + idMap + '" id="' + idMap + '">';
													
			for(var y = 1;y<=Mapy;y++) {
				for(var x=1;x<=Mapx;x++) {
					id = 'c_' + y + '_' + x;
					var posY = getPosYInit(y, x);
					var posX = getPosXInit(y, x);
					
					positions[y][x] = new Array(2);
					positions[y][x][0] = (posX|0);
					positions[y][x][1] = (posY|0);
					
					//---- création des zones de couleurs
					if(params.mapZones != '') {
						if(Zones[y][x] > 0) {
							var n = Zones[y][x] - 1;
							canvas_ctx[n].fillStyle = colors[n];
							canvas_ctx[n].beginPath();
							canvas_ctx[n].moveTo(~~(coordZ[0] + posX), ~~(coordZ[1] + posY));
							var boucleCond = coord.length-1;
							for(var item=2; item < boucleCond; item+=2){
								canvas_ctx[n].lineTo(~~(coordZ[item] + posX), ~~(coordZ[item+1] + posY));
							}
							canvas_ctx[n].closePath();
							canvas_ctx[n].fill();
							//canvas_ctx.stroke();
						}
					}

					var co = '';
					var boucleCond = coord.length;
					for(var i=0;i<boucleCond;i+=2) {
						var cx = ~~((coord[i]) + posX);
						var cy = ~~((coord[i+1]) + posY);
						co+= "," + cx + "," + cy;
					}
					co = co.substring(1);
					var idShap = 's_' + y + '_' + x;
					co = '<area id="' + idShap + '" class="pp3diso-shap" shape="poly" coords="' + co + '" alt="" />';
					laMap += co;
				}
			}
			
			laMap += '</map>';
		
			obj_conteneur.prepend('<div id="pp3diso-clicks"><img src="' + params.path + 'vide.png" id="pp3diso-mapControl" width="' + tailleMapX + '" height="' + tailleMapY + '" alt="" />' + laMap + '</div>');	//--- zone de click
		
			$('#pp3diso-mapControl').attr('usemap', '#' + idMap);
		
			$('#pp3diso-clicks').css({
				'z-index': 750000,
				'position':'absolute',
				'left':0,
				'top':0,
				'display':'block'
			});
			
			if(params.keys) {
				$(window).keyup(function(event) {
					var x = cursor_posx;
					var y = cursor_posy;
					var ok = false;
					
					var keycode = null;
					for(var tab in params.keys_tab) {
						for(var key in params.keys_tab[tab]) {
							if(params.keys_tab[tab][key] == event.keyCode) {
								keycode = tab;
								ok = true;
								break;
							}
						}
					}
					switch(keycode) {
						case 'key_n':		// Nord
							y--;
							break;
						case 'key_ne':		// NE
							y--; x++;
							break;
						case 'key_e':		// Est
							x++;
							break;
						case 'key_se':		// SE
							y++; x++;
							break;
						case 'key_s':		// Sud
							y++;
							break;
						case 'key_so':		// SO
							y++; x--;
							break;
						case 'key_o':		// Ouest
							x--;
							break;
						case 'key_no':		// NO
							y--; x--;
							break;
						case 'key_clic':		// espace
							if(ifPossible(x, y)) {
								moveAvatar(x, y, true);
							}
							break;
						case 'key_zoom':		//--- zoom +
							_zoomMap(params.zoom + params.zoom_pas);
							break;
						case 'key_dezoom':		//--- zoom -
							
							_zoomMap(params.zoom - params.zoom_pas);
							break;
					}
					//console.debug(Math.random() * 1000 + ' : ' + keycode);
					if(ok) {
						var idShap = $('#s_' + y + '_' + x);
						if(idShap.length) {
							moveCursor(idShap);
						}
					}
					if(ok === true) {
						event.stopPropagation();
						event.preventDefault();
						return;
					} else {
						return false;
					}
				});

			}
			
			if(params.onclicright != '') {
				$('.pp3diso-shap').bind('contextmenu', (function(event) {
					params.onclicright(avatar_posx, avatar_posy, params.mapId);
				}));
			}
			$('.pp3diso-shap').bind('click', (function(event) {
				if(window.Touch) moveCursor($(this));
				if(!allreadyDragDrop) {
					evenement($(this));
				}
			}));

			if(params.cursorDelay !== false) {
				$('.pp3diso-shap').bind('mouseenter', function() {
					cursorDelayObj = $(this).attr('id');
					if(cursorDelayEtat == 1) {
						cursorDelayTimer = window.setTimeout(function() {
							window.clearTimeout(cursorDelayTimer);
							var id = cursorDelayObj.split('_');
							params.oncursordelay(id[2], id[1], params.mapId);
						}, params.cursorDelay);
					}
				});
				$('.pp3diso-shap').bind('mouseout', function() {
					window.clearTimeout(cursorDelayTimer);
				});
			}
			
			if(!HAS_TOUCH) {
				$('.pp3diso-shap').bind('mouseenter', function() {
					moveCursor($(this));
				});
			}
			
			if(params.drag) {
				$('#pp3Diso-conteneur').bind('touchstart', function(e){
					allreadyDragDrop = false;
										
					var orig = e.originalEvent; 
					var dummy = obj_conteneur.position();
					init_pos_map_x = orig.changedTouches[0].clientX - dummy.left;
					init_pos_map_y = orig.changedTouches[0].clientY - dummy.top;
					$('#pp3Diso-conteneur').bind('click', function() { return false; });
					$('#pp3Diso-conteneur').bind('touchmove', function(e) { dragIOS(e); });
					
					$('#pp3Diso-conteneur').bind('touchend touchcancel', function(e){
						dragStop(e, $(this)); 
					});
					
					if(!touchpad_flag) {
						touchpad_flag = true;
						setTimeout(function(){ touchpad_flag = false; }, 100);
					}
					
				});
				
				
				$('.pp3diso-shap').bind('mousedown', function(e) {

						allreadyDragDrop = false;
						var evt = e || window.event;
						var dummy = obj_conteneur.position();
						init_pos_map_x = evt.clientX - dummy.left;
						init_pos_map_y = evt.clientY - dummy.top;
						
						$('#pp3Diso-conteneur').bind('click', function() { return false; });
						$('#pp3Diso-conteneur').bind('mousemove', dragAndDrop).bind('mouseup', dragStop);

					return false;
				});
			}
			
			obj.focus();
		}
		
				
		/**
		 * Drag & Drop
         * @param e
		 */
		function dragAndDrop(e) {
			//$('#infos').html("Y'a un truc...");
			e.preventDefault();
			allreadyDragDrop = true;
			var evt = e || window.event;
			var dX = evt.clientX - init_pos_map_x;
			var dY = evt.clientY - init_pos_map_y;

			if(dY > (params.ty<<1)) dY = params.ty<<1;
			if(dX > (params.tx)) dX = params.tx;
			if(dX < -((tailleMapX - taille3DisoX) + (params.tx * 2))) dX = -((tailleMapX - taille3DisoX) + (params.tx * 2));
			if(dY < -((tailleMapY - taille3DisoY) + params.ty)) dY = -((tailleMapY - taille3DisoY) + params.ty);
			 
			obj_conteneur.css({
				'left':dX,
				'top':dY
			});
			//var d = Math.floor(Math.random()*999);
			//$('#infos').html(d + ' dX=' + dX + ' / dY=' + dY + '<br>initX=' + init_pos_map_x + ' / initY=' + init_pos_map_y);
			return false;
		}
        
		
		/**
		 * Fin du Drag & Drop
		 */
		function dragStop(e, vis) {
			var dummy = obj_conteneur.position();
			var dX = ~~(-dummy.left / params.tx);
			var dY = ~~(-dummy.top / params.ty);
			var old_padx = padx;
			var old_pady = pady;
			padx = dummy.left;
			pady = dummy.top;
			
			//move_map();
			//$('#infos').html(' dX=' + dX + ' / dY=' + dY);
			$('#pp3Diso-conteneur').unbind('mousemove', dragAndDrop).unbind('mouseup', dragStop);
			
			//$(document).unbind('mousemove', dragAndDrop);
			//$(document).unbind('mouseup', dragStop);
			//$('#pp3Diso-conteneur').unbind('mousemove', pp3dISO_drag).unbind('mouseup', pp3Diso_drag_stop);
			if(old_padx != padx && old_pady != pady) {
				view(monde);
				//$('#infos').html(' Nbr=' + nbrDragStop);
				//nbrDragStop++;
			}
			$('.pp3diso-shap').unbind('touchmove');
			$('.pp3diso-shap').unbind('ontouchend');
			if(touchpad_flag) {
				touchpad_flag = false;
				//$('#infos').html('*ok*');
				evenement(vis);
			}
			allreadyDragDrop=false;
			return false;
		}
        
		/**
         * et la version mobile (touch)
         */
		function dragIOS(e) {
			//$('#infos').html("Y'a un truc...");
			e.preventDefault();
			allreadyDragDrop = true;
			var orig = e.originalEvent;
			var dX = orig.changedTouches[0].clientX - init_pos_map_x;
			var dY = orig.changedTouches[0].clientY - init_pos_map_y;

			if(dY > (params.ty<<1)) dY = params.ty<<1;
			if(dX > (params.tx)) dX = params.tx;
			if(dX < -((tailleMapX - taille3DisoX) + params.tx)) dX = -((tailleMapX - taille3DisoX) + params.tx);
			if(dY < -((tailleMapY - taille3DisoY) + params.ty)) dY = -((tailleMapY - taille3DisoY) + params.ty);
			 
			obj_conteneur.css({
				'left':dX,
				'top':dY
			});
			//var d = Math.floor(Math.random()*999);
			//$('#infos').html(d + ' dX=' + dX + ' / dY=' + dY + '<br>initX=' + init_pos_map_x + ' / initY=' + init_pos_map_y);
			return false;
		}

		
		/**
		 * permet le mouseWheel
		 */
		function getMouseWheel() {
			var MW_types = ['DOMMouseScroll', 'mousewheel'];
			if(this.addEventListener) {
            			for(var i=MW_types.length; i; ) {
                			obj[0].addEventListener(MW_types[--i], MW_handler, false );
            			}
			}else{
				obj[0].onmousewheel = MW_handler;
			}
		}

		var MW_time_handler = 0;

		function MW_handler(event) {
			var MW_Date = new Date();
			event.preventDefault();
			event.stopPropagation();
			var orgEvent = event || window.event, args = [].slice.call( arguments, 1 ), delta = 0, returnValue = true, deltaX = 0, deltaY = 0;
			
			event = $.event.fix(orgEvent);
			
			event.type = "mousewheel";
			
			// Old school scrollwheel delta
		    if ( orgEvent.wheelDelta ) { delta = orgEvent.wheelDelta/120; }
		    if ( orgEvent.detail     ) { delta = -orgEvent.detail/3; }
		    
		    // New school multidimensional scroll (touchpads) deltas
		    deltaY = delta;
		    
		    // Gecko
		    if ( orgEvent.axis !== undefined && orgEvent.axis === orgEvent.HORIZONTAL_AXIS ) {
		        deltaY = 0;
		        deltaX = -1*delta;
		    }
		    
		    // Webkit
		    if ( orgEvent.wheelDeltaY !== undefined ) { deltaY = orgEvent.wheelDeltaY/120; }
		    if ( orgEvent.wheelDeltaX !== undefined ) { deltaX = -1*orgEvent.wheelDeltaX/120; }
		    
		    // Add event and delta to the front of the arguments
		    args.unshift(event, delta, deltaX, deltaY);
    			
			if((MW_Date.valueOf() - MW_time_handler) > 1000) {
				if(args[1] < 0) {	//---- zoom Plus
					_zoomMap(params.zoom - params.zoom_pas);
				}else{
					_zoomMap(params.zoom + params.zoom_pas);
				}
				MW_time_handler = MW_Date.valueOf();
				
			}
			//console.log(MW_Date.valueOf());
			return false;
		}


		
		/**
		 * affiche la partie visible de la map
		 */
		function viewVisibleMap() {
			for(var y = 1;y<=Mapy;y++) {
				for(var x=1;x<=Mapx;x++) {
					id = 'c_' + y + '_' + x;
					var posY = getPosY(y, x) + pady;
					var posX = getPosX(y, x) + padx;
					if(posX>-tx && posX<(taille3DisoX + tx) && 
					    posY>-ty && posY<(taille3DisoY + ty)) {
						obj_conteneur.append('<div id="' + id + '" class="pp3diso-sol"></div>');
							$('#' + id).css({
							'z-index': getZindex(y, x),
							'position':'absolute',
							'left':(posX|0),
							'top':(posY|0),
							'width':params.tx,
							'height':params.ty,
							'display':'block'
						});
					}
				}
			}
		}
		
		/**
		 * Bouge toute la map
		 */
		function move_map() {
			
			if(nbrObjLoad == 0) {
				if(move_map_waiting) obj_conteneur.css('display', 'none');
				move_map_waiting = false;
				move_map_actually = true;
				if(params.positionFixe == true) {
					obj_conteneur.animate({
						left:(padxFixe|0),
						top:(padyFixe|0)
					}, params.speed_map, function() {
						view(monde);						
						move_map_actually = false;
						obj_conteneur.fadeIn('100');
						obj_wait.css('display', 'none');
					});
					
				} else {
					if(params.fluide) {
						obj_conteneur.animate({
							left:(padx|0),
							top:(pady|0)
						}, params.speed_map, function() {
							view(monde);						
							move_map_actually = false;
							obj_conteneur.fadeIn('100');
							obj_wait.css('display', 'none');
						});
						
					}else{
						obj_conteneur.css({
							'left':(padx|0),
							'top':(pady|0)
							});
						
						view(monde);
						move_map_actually = false;
						obj_wait.css('display', 'none');
					}
				}

			}else{
				move_map_waiting = true;
			}
		}
		
		
		/**
		 * affecte le niveau de visibilité en fonction du fow
		 */
		function viewFOW(x, y) {
			x = (x|0);
			y = (y|0);
			id = 'c_' + y + '_' + x;
			$('#' + id).fadeTo(0, fow[y][x]);
			
			id = 'b_' + y + '_' + x;
			$('#' + id).fadeTo(0, 1);
			
			id = 'o_' + y + '_' + x;
			$('#' + id).fadeTo(0, 1);
		}
		
		/**
		 * change le niveau de visibilité en fonction du fow
		 */
		function changeFOW(x, y, val){
			x = (x|0);
			y = (y|0);
			val = (val|0);
			if(y < 1 || x < 1 || y > Mapy || x > Mapx) return;
			if(fow[y][x]<val) fow[y][x] = val;
			if(fow[y][x] > 1) fow[y][x] = 1;
			viewFOW(x, y);
			var n = params.fogofwar;
			for(var xx = x-n;xx <= x+n;xx++) {
				var lenx = Math.abs(xx-x);
				for(var yy=y-n;yy<=y+n;yy++) {
					var len = Math.abs(yy-y);
					if(lenx > len) len = lenx;
					if(xx != x || yy != y) {
						if(!(yy < 1 || xx < 1 || yy > Mapy || xx > Mapx)) {
							var dummy = (val/len);
							if(fow[yy][xx]<dummy) fow[yy][xx] = dummy;
							if(fow[yy][xx] > 1) fow[yy][xx] = 1;
							viewFOW(xx, yy);
						}
					}
				}
			}
			
		}
		
		/**
		 * Affichage de la map
         * @param map
		 */
		function view(map) {
			var src = '';
			var totalFrag = '';
			var dtx2 = params.tx*3;
			var dty2 = params.ty*3;
			if(cursor_posx < 1) cursor_posx = 1;
			if(cursor_posy < 1) cursor_posy = 1;
			
			var dumx = taille3DisoX + dtx2;
			var dumy = taille3DisoY + dty2;
			for(var y = 1;y<=Mapy;y++) {
				var id1 = 'c_' + y;
				for(var x=1;x<=Mapx;x++) {
					
					var posX = positions[y][x][0];
					var posY = positions[y][x][1];
					
					var xx = posX + padx;
					var yy = posY + pady;
					
					var id = id1 + '_' + x;
					var objidCase = $('#' + id);
					
					if(xx>-dtx2 && xx<(dumx) && 
					    yy>-dty2 && yy<(dumy)) {
					    	if(objidCase.length == 0) {
							totalFrag = totalFrag + '<div id="' + id + '" class="pp3diso-sol"></div>';
						}else{
							if(params.fogofwar > 0) {
								viewFOW(x, y);
							}
							if(objidCase[0].getAttribute('display') != 'block') {
								objidCase.css('display', 'block');
							}
						}
					}else{
						objidCase.remove().empty();
					}
				}
			}
			
			//------------------------------ et pour finir...
			obj_conteneur.append(totalFrag); 
			var zoom2 = '100%';
			//var dtx2 = params.tx*2;
			//var dty2 = params.ty*2;
			for(var y = 1;y<=Mapy;y++) {
				for(var x=1;x<=Mapx;x++) {
					var posX = getPosX(y, x);
					var posY = getPosY(y, x);
					var xx = posX + padx;
					var yy = posY + pady;
					
					var id = 'c_' + y + '_' + x;
					var objidCase = $('#' + id);
					
					if(xx>-dtx2 && xx<(taille3DisoX + dtx2) && 
					    yy>-dty2 && yy<(taille3DisoY + dty2)) {
					    	if(!objidCase.length == 0) {
							src = map[y][x] + '.png';
							if(src != 'NaN.png') {
								src = fileBase + src;
								if(params.zoom!=1 && theBrowser=='msie') {
									objidCase.css({
										'z-index': getZindex(y, x),
										'position':'absolute',
										'left':(posX|0),
										'top':(posY|0),
										'width':params.tx,
										'height':params.ty,
										'background-image':"url('" + src + "')",
										'display':'block',
										'-webkit-background-size': zoom2 + ' ' + zoom2, 
										'-o-background-size': zoom2 + ' ' + zoom2, 
										'-moz-background-size': zoom2 + ' ' + zoom2, 
										'background-size': zoom2 + ' ' + zoom2,

										//'background': 'none url('+src+') center center fixed no-repeat',
										'background-position':'center center',
										'background-repeat':'no-repeat',
										'-ms-filter':"progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + src + "', sizingMethod='scale')",
										'filter':"progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + src + "', sizingMethod='scale')"
									});
								}else{
									objidCase.css({
										'z-index': getZindex(y, x),
										'position':'absolute',
										'left':(posX|0),
										'top':(posY|0),
										'width':params.tx,
										'height':params.ty,
										'background-image':"url('" + src + "')",
										'display':'block',
										'-webkit-background-size': zoom2 + ' ' + zoom2, 
										'-o-background-size': zoom2 + ' ' + zoom2, 
										'-moz-background-size': zoom2 + ' ' + zoom2, 
										'background-size': zoom2 + ' ' + zoom2
									});
								}
							}
						}
					}
				} //-- for x
			}  //-- for y
			
		}
		
		/**
		 * Remplace une chaîne dans une autre
		 * @param SRs chaîne de base
		 * @param SRt chaîne à chercher
		 * @param SRu chaîne de remplacement
		 * @returns {String}
		 */
		function strReplace(SRs, SRt, SRu) {
			SRRi = SRs.indexOf(SRt);
			SRRr = '';
			if (SRRi == -1) return SRs;
			SRRr += SRs.substring(0,SRRi) + SRu;
			if(SRRi + SRt.length < SRs.length) {
				SRRr += strReplace(SRs.substring(SRRi + SRt.length, SRs.length), SRt, SRu);
			}
			return SRRr;
  		}
		
		/**
		 * Le z-index en fonction de x et y
		 * @param y
		 * @param x
		 * @returns
		 */
		function getZindex(y, x) {
			//return (x+y);
			return (x << 1) + (y << 1);
		}

		this.getZIndex = function(x, y){
			return getZindex(y, x);
		}
		
		/**
		 * position x 2D en 3D
		 * @param y
		 * @param x
		 * @returns
		 */
		function getPosXInit(y, x) {
			x = x|0;
			y = y|0;
			var dummy = tailleMapX2 + ((x-1) * tx2);
			dummy = dummy - (tx2*(y+1)) + tx2;		
			return (dummy|0);
		}
		
		/**
		 * position y 2D en 3D
		 * @param y
		 * @param x
		 * @returns
		 */
		function getPosYInit(y, x) {
			x = x|0;
			y = y|0;
			var dummy = (y-1) * (ty2);
			dummy = dummy + (ty2*(x-1));
			return (dummy|0);
		}
		
		/**
		 * position x 2D => 3D version en cache
		 * @param y
		 * @param x
		 * @returns
		 */
		function getPosX(y, x) {
			return positions[y|0][x|0][0];
		}
		
		this.getPX = function(x, y){
			return getPosX(y, x);
		}

		/**
		 * position y 2D => 3D version en cache
		 * @param y
		 * @param x
		 * @returns
		 */
		function getPosY(y, x) {
			return positions[y|0][x|0][1];
		}
		
		this.getPY = function(x, y){
			return getPosY(y, x);
		}
		/**
		 * ParseInt optimisé
		 * @param val
		 * @returns
		 */
		function parseIntDec(val) {
			return (val|0);
		}
		
		
		/**
		 * Est-ce qu'un objet se trouve sur cette case ?
		 * @param x
		 * @param y
		 * @returns
		 */
		function objetIsOnCase(x, y) {
			//var l = o_objets.length;
			//for(var i = 0;i<l;i++) {
			for(var i in o_objets) {
				if(o_objets[i]['x'] == x && o_objets[i]['y'] == y) return true;
			}
			return false;	
		}
		/**
		 * Version public de objectIsOnCase
		 * @param x
		 * @param y
		 * @returns id de l'objet dans le tableau o_objets sinon false
		**/
		this.getObjetOnCase = function(x, y, z){
			if(typeof(z)=='undefined'){
				z=0;
			}
			for(var i in o_objets) {
				if(o_objets[i]['x'] == x && o_objets[i]['y'] == y && o_objets[i]['z'] == z){
					return o_objets[i]
				};
			}
			return false;	
		}

		this.objetOnCase = function(x, y){
			return objetIsOnCase(x, y);
		}
		
		/**
		 * retourne les titre des objets sur une case
		 * @param x
		 * @param y
		 * @param separateur
		 * @returns
		 */
		function getTitreObjets(x, y, separateur) {
			if(typeof(separateur) == "undefined") separateur = ' / ';
			//var l = o_objets.length;
			var titre = '';
			//for(var i = 0;i<l;i++) {
			for(var i in o_objets) {
				if(o_objets[i]['x'] == x && o_objets[i]['y'] == y) {
					titre = titre + separateur + o_objets[i]['titre'];
				}
			}
			titre = titre.substr(separateur.length);
			return titre;		
		}

		function getDecZ(x, y){
			var decZ = 0;
			if(typeof(bt.getItem)!='undefined'){
				var sup = bt.getItem (x, y, 0);
				if(sup){
					var sZone = JSON.parse(sup[0].zone);
					var sZl = sZone.length;
					if(sZl>1){
						if(((x|0)+(y|0))<(sup[0].x+sup[0].y)){
							decZ += ((sup[0].x+sup[0].y)-((x|0)+(y|0)))<<1;
						}
					}
				}
			}
			return decZ
		}

		function getDecZZone(x, y, z, zone, zonelength){
			var dZ = 0;
			if(z>0){
				z--;
			}
			// On récupère le point de ref le plus bas
			var supports = bt.getItemZone((x|0), (y|0), z, zone, zonelength);
			if(!!supports){
				var dummy = 0;
				var dummy_id = 0;
				var supl = supports.length;
				for(var sid=0; sid<supl; sid++){
					var sx = supports[sid].x;
					var sy = supports[sid].y;
					if((sx+sy)>dummy){
						dummy = sx+sy;
						dummy_id = sid;
					}
				}
				var ref = bt.getItem(supports[dummy_id].x, supports[dummy_id].y, 0)[0];
				var sZone = JSON.parse(ref.zone);
				var sZl = sZone.length;
				if(sZl>1){
					if(((x|0)+(y|0))<(ref.x+ref.y)){
						dZ = ((ref.x+ref.y)-((x|0)+(y|0)))<<1;
					}
				}
			}
			return dZ;
		}
		
		/**
		 * bouge le curseur et change sa forme 
		 * @param titleset
		 * @returns
		 */
		function moveCursor(titleset) {
			var dummy = titleset.attr('id');
			if(typeof(dummy) == "undefined") return;
			if(allreadyCursorAnimate) return;
			var infos = dummy.split('_');
			var posx = infos[2];
			var posy = infos[1];
			var decZ = 0;
			if(params.mode=='creator' && bt.items[Choix]['type']!='sols'){
				if(Choix !=0){
					var cZone = JSON.parse(bt.items[Choix].zone);
					var cZl = cZone.length;
					if(cZl>1){
						cursorAlti = bt.getMaxAltZone(posx, posy, cZone, cZl);
						decZ = getDecZZone(posx, posy, cZone, cZl);
					}
					else{
						cursorAlti = bt.getMaxAlt(posx, posy);
						decZ = getDecZ(posx, posy);
					}
				}
				else{
					cursorAlti = bt.getMaxAlt(posx, posy);
					decZ = getDecZ(posx, posy);
				}
			}
			else{
				cursorAlti = 0;
				decZ = getDecZ(posx, posy);
			}
			if(typeof(bt.myCursorDelay)!='undefined'){
				bt.myCursorDelay(posx, posy);
			}
			
			//if(etat_tip) {
				if(cursor_posx != posx || cursor_posy != posy) {
					if(zone[cursor_posy][cursor_posx] == 2) {
						params.onleavebuilding(cursor_posx, cursor_posy, params.mapId);

					}else if(objetIsOnCase(cursor_posx, cursor_posy)) {
						params.onleaveobject(cursor_posx, cursor_posy, params.mapId);	
					}
				}
			//}
			cursor_posx = posx;
			cursor_posy = posy;
			var x = getPosX(posy, posx) + (cursor_decx * params.zoom);
			var y = getPosY(posy, posx) + (cursor_decy * params.zoom)-(params.tx/2*cursorAlti);
			//x = x+ 'px';
			//y = y + 'px';
			
			if(ifPossible(posx, posy)) {
				$('#pp3diso-cursor-img').attr('src', cursor_on);
			}else{
				$('#pp3diso-cursor-img').attr('src', cursor_off);
			}
			
			if(params.cursorZindex >= 0) {
				var myzindex = 300000 + getZindex(posy|0, posx|0) + (params.cursorZindex | 0)+decZ;
				$('#pp3diso-cursor').css({
					'display':'block',
					'left':x,
					'top':y,
					'z-index':myzindex
				});
			}
			 else {
				$('#pp3diso-cursor').css({
					'display':'block',
					'left':x,
					'top':y
				});
			}
			
			if(zone[posy][posx] == 2) {	// --- il y a un batiment ici
				params.onenterbuilding(posx, posy, params.mapId);
			}
			else if(objetIsOnCase(posx, posy)) {	// --- il y a un objet ici
				params.onenterobject(posx, posy, params.mapId);
			}
			if(params.affichage_pseudo == 0){
				_pseudo_hide();
			}
			if(mapPlayers[posx][posy]!= 0 && params.affichage_pseudo == 0){
				_pseudo_show(mapPlayers[posx][posy], x, y);
			}
		}
		
		/**
		 * PathFinding
		 * @returns
		 */
		var initPathAllready = false;
		function initPathfinding() {
			if(!initPathAllready) {
				avatar_PFz = null;
				avatar_PFz = [];
				for(y = 0; y<Mapy;y++) {
					for(x = 0; x<Mapy;x++) {
						if(zone[y+1][x+1] == 1){
							mapPF[x][y] = 1;
						}else{
							mapPF[x][y] = 0;
						}
					}
				}
			}
		}

		function initPlayerPathfinding(player_id){
			if(params.affichage_pseudo == 0){
				_pseudo_hide();
			}
			player_PFz[player_id] = null;
			player_PFz[player_id] = [];
			for(var y=0; y<Mapy; y++){
				for(var x=0; x<Mapx; x++){
					if(zone[y+1][x+1] == 1){
						mapPF[x][y] = 1;
					}
					else{
						mapPF[x][y] = 0;
					}
				}
			}
		}

		function pathfinding(x, y) {
			var debx = avatar_posx;
 			var deby = avatar_posy;
			var endx = x;
			var endy = y;
			var pfMinx, pfMaxx, pfMiny, pfMaxy;
			initPathfinding();
			if(PF_max != false) {
				pfMinx = debx - (PF_max + 1);
				pfMaxx = debx + PF_max;
				pfMiny = deby - (PF_max + 1);
				pfMaxy = deby + PF_max;
				if(pfMinx < 0) {
					pfMinx = 0;
				}
				if(pfMaxx >= Mapx) {
					pfMaxx = Mapx;
				}
				if(pfMiny < 0) {
					pfMiny = 0;
				}
				if(pfMaxy >= Mapy) {
					pfMaxy = Mapy;
				}
			} else {
				pfMinx = false;
				pfMaxx = false;
				pfMiny = false;
				pfMaxy = false;
			}
			var chemin = astar(debx-1, deby-1, endx-1, endy-1, mapPF, params.PF_corners, pfMiny, pfMaxy, pfMinx, pfMaxx);
			
			if(chemin != null) {
				return true;
			}else{
				return false;
			}
		}
		
		/**
		 * Bouge jusqu'à la case en pathinfing
		 * @param x
		 * @param y
		 * @param callback
		 * @returns
		 */
		function moveToPF(x, y, callback) {
			var debx = avatar_posx;
 			var deby = avatar_posy;
			var endx = x;
			var endy = y;
			var debut_x, debut_y, fin_x, fin_y;
			var pfMinx, pfMaxx, pfMiny, pfMaxy;
			initPathfinding();
			if(PF_max != false) {
				pfMinx = debx - (PF_max + 1);
				pfMaxx = debx + PF_max;
				pfMiny = deby - (PF_max + 1);
				pfMaxy = deby + PF_max;
				if(pfMinx < 0) {
					pfMinx = 0;
				}
				if(pfMaxx >= Mapx) {
					pfMaxx = Mapx;
				}
				if(pfMiny < 0) {
					pfMiny = 0;
				}
				if(pfMaxy >= Mapy) {
					pfMaxy = Mapy;
				}
			} else {
				pfMinx = false;
				pfMaxx = false;
				pfMiny = false;
				pfMaxy = false;
			}
			var path = astar(debx-1, deby-1, endx-1, endy-1, mapPF, params.PF_corners, pfMiny, pfMaxy, pfMinx, pfMaxx);
			
			if(path != null) {
				var boucleCond = path.length;
				for(var i = 0; i < boucleCond; i++) {
					var laposx = getPosX(path[i].col+1, path[i].row+1);
					var laposy = getPosY(path[i].col+1, path[i].row+1);
					var tx2 = params.tx>>1;
					var ty2 = params.ty>>1;
					if(!params.beforemoveavatar(x, y, params.mapId)) return;
					
					if(i< 1) {
						debut_x = avatar_posx;
						debut_y = avatar_posy;
					}else{
						debut_x = path[i-1].row+1;
						debut_y = path[i-1].col+1;
					}
					fin_x = path[i].row+1;
					fin_y = path[i].col+1;
					
					var dummy_sens = 1;
					if(fin_x > debut_x) {
						dummy_sens = 3;
					}else if(fin_x < debut_x) {
						dummy_sens = 2;
					}else if(fin_y > debut_y) {
						dummy_sens = 1;
					}else if(fin_y < debut_y){
						dummy_sens = 4;
					}else{
						dummy_sens = 1;
					}
					
					if(i < 1) {
						move_avatar_sens = dummy_sens;
					}else{
						move_avatar_sens_memory.push(dummy_sens);
					}
					
					avatar_posx = x;
					avatar_posy = y;
					avatar_PFz.push(path[i].col+1 + ',' + path[i].row+1);
					

					var dummy_obj_av = $('#pp3diso-avatar'); 
					
					avatar_z_index.push(300000 + getZindex(path[i].col+1, path[i].row+1));
					dummy_obj_av.animate({
						left:laposx+avatar_decx,
						top:laposy+avatar_decy
						}, {
							step:function() {
								move_avatar_time++;
								if(move_avatar_time > params.move_avatar_speed) {
									moveAvatarSprite();
									move_avatar_time = 0;
								}
							},
							duration:params.speed_avatar,
							complete:function() {
								var index = path.length - move_avatar_sens_memory.length;
								if(!!path[index]) {
									params.onmovepathfinding(path[index].row+1, path[index].col+1, params.mapId);
								}
								
								if(move_avatar_sens_memory.length) {
									move_avatar_sens = move_avatar_sens_memory.shift();

								} else if(callback) {
									params.onmoveavatar(x, y, params.mapId);
								}
								var dummy = avatar_PFz.pop();
								//dummy_obj_av.css('z-index', );
								var localZindex = avatar_z_index.shift(); 
								if(avatar_z_index.length <= 1) {
									//localZindex = 300000 + getZindex(avatar_posy|0, avatar_posx|0) + 1;
								}
								dummy_obj_av.css('z-index', localZindex);


							}
						}
					);
					
					
					if(params.fogofwar>0) {
						changeFOW(x, y, 1);
					}
				}
			}
			
		}
		
		/**
		 * affiche le bon sprite en fonction du sens
		 * 1 : vers le bas
		 * 2 : vers la gauche
		 * 3 : vers la droite
		 * 4 : vers le haut
		 * @returns
		 */
		function moveAvatarSprite() {
			if(avatar_animation) {
				var x = ~~((avatar_img_tx * (move_avatar_step)) * params.zoom);
				var y = ~~((avatar_img_ty * (move_avatar_sens-1)) * params.zoom);
				$('#pp3diso-avatar-img').css({
					'left': -x,
					'top':-y	
				});
				move_avatar_step++;
				if(move_avatar_step >= avatar_ny) move_avatar_step = 0;
			}			
		}

		function movePlayerToPF(pseudo, x, y, callback){
			var player = getPlayer (pseudo);
			// Position actuelle
			var debx = player['x'];
			var deby = player['y'];
			// Position finale
			var endx = x;
			var endy = y;
			var debut_x, debut_y, fin_x, fin_y;
			var pfMinx, pfMaxx, pfMiny, pfMaxy;
			initPlayerPathfinding(player['id']);
			if(PF_max != false) {
				pfMinx = debx - (PF_max + 1);
				pfMaxx = debx + PF_max;
				pfMiny = deby - (PF_max + 1);
				pfMaxy = deby + PF_max;
				if(pfMinx < 0) {
					pfMinx = 0;
				}
				if(pfMaxx >= Mapx) {
					pfMaxx = Mapx;
				}
				if(pfMiny < 0) {
					pfMiny = 0;
				}
				if(pfMaxy >= Mapy) {
					pfMaxy = Mapy;
				}
			} else {
				pfMinx = false;
				pfMaxx = false;
				pfMiny = false;
				pfMaxy = false;
			}
			var path = astar(debx-1, deby-1, endx-1, endy-1, mapPF, params.PF_corners, pfMiny, pfMaxy, pfMinx, pfMaxx);
			if(path != null){
				var boucleCond = path.length;
				for(var i = 0; i < boucleCond; i++){
					var laposx = getPosX(path[i].col+1, path[i].row+1);
					var laposy = getPosY(path[i].col+1, path[i].row+1);
					var tx2 = params.tx>>1;
					var ty2 = params.ty>>1;
					if(i < 1){
						debut_x = player['x'];
						debut_y = player['y'];
					}
					else{
						debut_x = path[i-1].row+1;
						debut_y = path[i-1].col+1;
					}
					fin_x = path[i].row+1;
					fin_y = path[i].col+1;
					console.log( debut_x +' '+ debut_y+' '+fin_x+' '+fin_y);
					mapPlayers[debut_x][debut_y] = 0;
					mapPlayers[fin_x][fin_y] = player['pseudo'];
					var dummy_sens = 1;
					if(fin_x > debut_x) {
						dummy_sens = 3;
					}else if(fin_x < debut_x) {
						dummy_sens = 2;
					}else if(fin_y > debut_y) {
						dummy_sens = 1;
					}else if(fin_y < debut_y){
						dummy_sens = 4;
					}else{
						dummy_sens = 1;
					}
					if(i<1){
						move_player_sens[player['id']] = dummy_sens;
					}
					else{
						move_player_sens_memory[player['id']].push(dummy_sens);
					}
					player_PFz[player['id']].push(path[i].col+1 + ',' + path[i].row+1);
					
					// Déplacement de la bulle de tchat
					var dummy_bulle = $('#bulle-tchat-'+pseudo);
					dummy_bulle.animate({
						left: laposx+((player['decx']|0)*params.zoom),
						top: laposy+((player['decy']|0)*params.zoom),
						duration : params.speed_avatar
					});
					// Déplacement de la bulle du pseudo
					if(params.affichage_pseudo == 1){
						$('#bulle_pseudo_'+pseudo).animate({
						left: laposx+(params.ty/2*params.zoom),
						top: laposy+(player['decy']|0),
						duration : params.speed_avatar
						})
					}
					// Déplacement de l'avatar.
					var dummy_obj_player = $('#p_'+player['id']);
					player_z_index[player['id']].push(300000 + getZindex(path[i].col+1, path[i].row+1));
					dummy_obj_player.animate({
						left: laposx+((player['decx']|0)*params.zoom),
						top: laposy+((player['decy']|0)*params.zoom)
					}, {
						step:function(){
							move_player_time[player['id']]++;
							if(move_player_time[player['id']] > params.move_avatar_speed){
								movePlayerSprite(player['id']);
								move_player_time[player['id']] = 0;
							}
						},
						duration: params.speed_avatar,
						complete:function(){
							var index = path.length - move_player_sens_memory[player['id']].length;
							/*if(!!path[index]){
								params.onmovepathfinding(path[index].row+1, path[index].col+1, params.mapId);
							}*/
							if(move_player_sens_memory[player['id']].length){
								move_player_sens[player['id']] = move_player_sens_memory[player['id']].shift();
							}
							var dummy = player_PFz[player['id']].pop();
							var localZindex = player_z_index[player['id']].shift();
							dummy_obj_player.css('z-index', localZindex);
						}
					});
				}
				player['x'] = x;
				player['y'] = y;
			}
		}

		function movePlayerSprite(player_id){
			var x = ~~((player_img_tx[player_id] * (move_player_step[player_id])) * params.zoom);
			var y = ~~((player_img_ty[player_id] * (move_player_sens[player_id]-1)) * params.zoom);
			$('#p_'+player_id+' img').css({
				'left': -x,
				'top' : -y
			});
			move_player_step[player_id]++;
			if(move_player_step[player_id] >= 4) move_player_step[player_id] = 0;
		}

		function getPlayer(pseudo){
			for(var i in players) {
				if(players[i]['pseudo'] == pseudo){
					return players[i];
				}
				else{
					return false;
				}
			}
		}
		
		/**
		 * est-ce que l'avatar peut aller sur cette case ? 
		 * (en fait, c'est plutôt est-ce que le curseur...)
		 * @param x
		 * @param y
		 * @returns
		 */
		function ifPossible(x, y) {
			if(params.pathfinding) {
				return pathfinding(x, y);
			}else{
				var ok = false;
				//if($('#pp3diso-avatar-img').length == 0) {
				if(avatar == '') {
					ok = true;
				}else{
					x = (x|0);
					y = (y|0);
					if(x >= avatar_posx-1 && x <= avatar_posx+1
						&& y >= avatar_posy-1 && y <= avatar_posy+1) {
						ok = true;
					}else{
						ok = false;
					}
				}
				
				if(zone[y][x] == 1 && ok) {
					return true;
				}else{
					return false;
				}
			}
		}
		
		
		/**
		 * bouge l'avatar
		 * @param x
		 * @param y
		 * @param callback
		 * @returns
		 */
		function moveAvatar(x, y, callback) {
			x = (x|0);
			y = (y|0);
			//alert('ok');
			var laposx = getPosX(y, x);
			var laposy = getPosY(y, x);
			var tx2 = params.tx>>1;
			var ty2 = params.ty>>1;
			if(params.pathfinding) {
				moveToPF(x, y, callback);

			}else{
				//var laposx = getPosX(y, x);
				//var laposy = getPosY(y, x);
				//var tx2 = params.tx/2;
				//var ty2 = params.ty/2;
				if(!params.beforemoveavatar(x, y, params.mapId)) return;
				
				var dummy_obj_av = $('#pp3diso-avatar'); 
				dummy_obj_av.animate({
					left:laposx+avatar_decx,
					top:laposy+avatar_decy
				}, params.speed_avatar);
				dummy_obj_av.css({
					'z-index': 300000 + getZindex(y, x)
				});
				avatar_posx = x;
				avatar_posy = y;
				if(params.fogofwar>0) {
					changeFOW(x, y, 1);
				}
				if(callback) {
					params.onmoveavatar(x, y, params.mapId);
				}
			}
			modif_move = false;
			if(taille3DisoX < tailleMapX + (params.tx>>1)) {
				var dummy = params.nbrTitleSetsSlide*params.tx;
				
				var position_avatar = ~~(laposx + padx + tx2);
				var verif = ((taille3DisoX>>1) + dummy);
				if(position_avatar >  verif) {
					while(position_avatar > verif) {
						padx -= dummy;
						position_avatar = ~~(laposx + padx + tx2);
					}
					modif_move = true;
				}
				verif = ((taille3DisoX>>1) - dummy);
				if(position_avatar < verif) {
					while(position_avatar < verif) {
						padx += dummy;
						position_avatar = ~~(laposx + padx + tx2);
					}
					modif_move = true;
				}
			}
			if(taille3DisoY < tailleMapY + (params.ty>>1)) {
				var dummy = params.nbrTitleSetsSlide*params.ty;
				
				var position_avatar = ~~(laposy + pady + ty2);
				var verif = ((taille3DisoY>>1) + dummy);
				if(position_avatar > verif) {
					while(position_avatar > verif) {
						pady -= dummy;
						position_avatar = ~~(laposy + pady + ty2);
					}
					modif_move = true;
				}
				verif = ((taille3DisoY>>1) - dummy);
				if(position_avatar < verif) {
					while(position_avatar < verif) {
						pady += dummy;
						position_avatar = ~~(laposy + pady + ty2);
					}
					modif_move = true;
				}
			}
			
			cursor_posx = avatar_posx;
			cursor_posy = avatar_posy;
			
			var idShap = $('#s_' + cursor_posy + '_' + cursor_posx);
			if(idShap.length) {
				moveCursor(idShap);
				}
			
			if(modif_move) move_map();
		}
		
		/**
		 * on vient de cliquer
		 * @param titleset
		 * @returns
		 */
		function evenement(titleset) {
			var dummy = titleset.attr('id');
			var infos = dummy.split('_');
			var x = infos[2];
			var y = infos[1];
			params.onclicmap(x,y);
			obj_tip.css('display', 'none');
			if(params.affichage_pseudo == 0){
				_pseudo_hide();
			}
			
			if(zone[y][x] == 2) {	// --- il y a un batiment ici
				params.onclicbuilding(x, y, params.mapId);
			}else if(objetIsOnCase(x, y)) {	// --- il y a un objet ici
				params.onclicobject(x, y, params.mapId);
			}
			// Animation du curseur
			if(cursor_nb_frame>1){
				allreadyCursorAnimate = false;
				_animateCursor();
			}
			//Déplacement de l'avatar
			if(ifPossible(x, y)) {
				params.beforeonemoveavatar(x, y);
				moveAvatar(x, y, true);
			}
		
		}
		
		/**
		 * transforme '12px' en '12'
		 * @param x
		 * @returns
		 */
		function px2val(x) {
			x = x.substring(0, x.length - 2);
			return (x|0);
		}
		
		function ppkill_avatar() {
			$('#pp3diso-avatar').remove().empty();
		}
		
		/* ------------------------------------------- Méthodes public --------------------- */

		/**
		 * recharge une map
		 */
		this.reload = function(map, zone, mapZones, mapId) {
			o_objets = null;
			o_objets = [];
			players = null;		//-- liste des objets
			players = [];
			clearMemory();
			
			avatar_posx = 1;
			avatar_posy = 1;
			cursor_posx = 1;
			cursor_posy = 1;
			
			params.map = map;
			params.zone = zone;
			params.mapZones = mapZones;
			params.mapId = mapId;
			init();
			move_map();
			//$('#infos').html(Math.floor(Math.random()*999) + '> ' + obj.children().length);
		}
		
		/**
		 * Affiche tout
		 */
		this.show = function(callback) {
			//$('.pp3diso-sol').show();
			//$('.pp3diso-sol').css('display', 'block');
			if(!params.fogofwar>0) {
				//$('.pp3diso-batiment').css('display', 'block');
				//$('.pp3diso-objet').css('display', 'block');
			}
			
			moveAvatar(avatar_posx, avatar_posy, callback);
			obj_conteneur.css('display', 'none');
		}
		
		/**
		 * Définition du curseur
		 */
		this.changeCursor = function(on, off, decx, decy, nf) {
			$('#pp3diso-cursor').remove().empty();
			cursor_tx = 0;
			_interneCursor(on, off, decx, decy, nf);
		};
		
		/**
		 * Affiche ou masque le curseur à une position
		 */
		this.cursor = function(on, off, decx, decy, nf) {
			_interneCursor(on, off, decx, decy, nf);
		}
		
		/**
		 * Affichage du curseur
		 * @param on
		 * @param off
		 * @param decx
		 * @param decy
		 * @param zindex
		 * @returns
		 */
		function _interneCursor(on, off, decx, decy, nf) {
			cursor_on = on;
			cursor_off = off;
			cursor_decx = decx;
			cursor_decy = decy;
			if(typeof(nf) == 'undefined'){
				cursor_nb_frame = 1;
			}
			else{
				cursor_nb_frame = nf;
			}
			
			obj_conteneur.append('<div id="pp3diso-cursor"><img id="pp3diso-cursor-img" src="' + on + '" alt="" /></div>');

			$('#pp3diso-cursor-img').load(function() {
				if(this.width < 1) {
					//this.width = zoom;
					//this.height = zoom;
					//alert(zoom);
				}else{
					if(cursor_tx < 1) {
						$('#pp3diso-cursor-img').attr('rel', (this.width)+ ':' + this.height).css({'position':'relative'});
						$('#pp3diso-cursor').css({'width' : this.width/cursor_nb_frame*params.zoom});
						cursor_tx = ~~((this.width) * params.zoom);
						cursor_ty = ~~(this.height * params.zoom);
						this.width = cursor_tx;
						this.height = cursor_ty;
					}
				}
			});
			$('#pp3diso-cursor').css({
				'z-index': 299000,
				'position':'absolute',
				'left':0,
				'top':0,
				'display':'block',
				'overflow' : 'hidden'
			});
		};

		/**
		 * Fonction d'animation du curseur
		**/
		function _animateCursor(){
			var dummy_obj = $('#pp3diso-cursor img');
			var left_step = dummy_obj.width()/cursor_nb_frame;
			var animationCursor = setInterval(function(){
				allreadyCursorAnimate = true;
				var position = -1*(cursor_animate_step*left_step);
				dummy_obj.css('left', position);
				cursor_animate_step++;
				if(cursor_animate_step == cursor_nb_frame){
					clearInterval(animationCursor);
					dummy_obj.css('left', 0);
					cursor_animate_step = 0;
					allreadyCursorAnimate = false;
				}
			}, 20);
		};
		
		/**
		 * Bouge la map jusqu'au point x, y
		 * @param x
		 * @param y
		 * @returns
		 */
		function _moveTo(x, y) {
			var laposx = getPosX(y, x);
			var laposy = getPosY(y, x);
			
			padx = -((laposx - (taille3DisoX>>1))|0);
			pady = -((laposy - (taille3DisoY>>1))|0);
			cursor_posx = x;
			cursor_posy = y;
			avatar_posx = x;
			avatar_posy = y;
			if(move_map_actually) {
				setTimeout(function() {_moveTo(x, y)}, 250);
			} else {
				move_map();
				obj_conteneur.css('display', 'block');
			}
		}
		
		/**
		 * Bouge la map jusqu'au point x, y
		 */
		this.moveTo = function(x, y) {
			_moveTo(x, y);
		}
		
		
		/**
		 * Récupère la map
		 */
		this.getMonde = function() {
			var dummy = new Array(Mapx+1);
			for(x=1; x<=Mapx; x++) {
				dummy[x] = new Array(Mapy+1);
				for(y=1; y<=Mapy; y++) {
					dummy[x][y] = monde[y][x];
				}
			}
			return dummy;		
		}
		
		/**
		* Récupère la carte des zones
		**/
		this.getZone = function(){
			return zone;
		}

		/**
		 * Récupère les objets
		 */
		this.getObjects = function() {
			return o_objets;		
		}

		this.getObjet = function (oid){
			return o_objets[oid-1];
		}
		
		/**
		 * Récupère les bâtiments
		 */
		this.getBuilding = function() {
			var dummy = new Array(Mapx+1);
			for(x=1; x<=Mapx; x++) {
				dummy[x] = new Array(Mapy+1);
				for(y=1; y<=Mapy; y++) {
					src = '';
					var id = 'b_' + y + '_' + x;
					if($('#' + id + ' img').length > 0) {
						var src = $('#' + id + ' img').attr('src');
					}
					dummy[x][y] = src;
				}
			}
			return dummy;		
		}
		
		
		/**
		 * bouge un objet
		 */
		this.moveObject = function(id, x, y, animate, vitesse) {
			//var l = o_objets.length;
			//for(var i = 0; i < l ;i++) {
			for(var i in o_objets) {
				if(o_objets[i]['id'] == id) {
					o_objets[i]['x'] = x;
					o_objets[i]['y'] = y;
					
					var px = getPosX(y, x) + (o_objets[i]['decx'] * params.zoom);
					var py = getPosY(y, x) + (o_objets[i]['decy'] * params.zoom);
					var idobj = 'o_' + id;
					if(animate) {
						var z = 300000 + getZindex(y, x);
						$('#' + idobj).css('z-index', z).animate({
							'left':(px|0),
							'top':(py|0)
						}, vitesse);
					}else{
						$('#' + idobj).css({
							'z-index': 300000 + getZindex(y, x),
							'left':(px|0),
							'top':(py|0)
						});
					}
					break;
				}
			}		
		}
		
		
		/**
		 * kill l'avatar
		 */
		this.killAvatar = function() {
			ppkill_avatar();
		}
		
		/**
		 * déclare l'avatar
		 */
		this.avatar = function(x, y, src, decx, decy, animation, ny) {
			_avatar(x, y, src, decx, decy, animation, ny);
		}
		
		function _avatar(x, y, src, decx, decy, animation, ny) {
			if(move_map_actually) {
				setTimeout(function() {_avatar(x, y, src, decx, decy, animation, ny)}, 250);
				return;
			}
			ppkill_avatar();
			if(typeof(animation) == "undefined") {
				animation = false;
				ny = 1;
				var nbr_x = 1;
			}else{
				var nbr_x = 4;
			}
			var zoom2 = ~~(100 * ny) + '%';
			avatar_decx = decx * params.zoom;
			avatar_decy = decy * params.zoom;
			avatar_decx_ori = decx;
			avatar_decy_ori = decy;
			avatar_posx = x;
			avatar_posy = y;
			avatar_ny = ny;
			avatar_animation = animation;
			avatar = src;
			obj_conteneur.append('<div id="pp3diso-avatar"><img id="pp3diso-avatar-img" src="' + src + '" alt="" /></div>');
			$('#pp3diso-avatar-img').load(function() {
				var imgx = this.width;
				var imgy = this.height;
				avatar_img_tx = ~~(imgx/ny);
				avatar_img_ty = ~~(imgy/nbr_x);
				var ltx = ~~(avatar_img_tx * params.zoom);
				var lty = ~~(avatar_img_ty * params.zoom);
				var obj_img = $('#pp3diso-avatar-img');
				//obj_img.attr('rel', this.width+ ':' + this.height + ':' + decx + ':' + decy);
				$('#pp3diso-avatar').css({
					'z-index': 300000 + getZindex(y,x),
					'position':'absolute',
					'left':getPosX(y, x)+decx,
					'top':getPosY(y, x)+decy,
					'width':ltx,
					'height':lty,
					'overflow':'hidden',
					'display':'block'
				});
				obj_img.css({
					'position':'absolute',
					'display':'block',
					'left':0,
					'top':0,
					'width':ltx*ny,
					'height':lty*nbr_x
				});
				
				moveAvatar(avatar_posx, avatar_posy, false);
			});
		}
		
		
		this.moveAvatarTo = function(x, y) {
			avatar_posx = x;
			avatar_posy = y;
			moveAvatar(x, y, false);
		}

		/**
		 * Gestion des joueurs en multi
		 */
		this.add_player = function(x, y, src, decx, decy, ny, pseudo){
			_add_player(x, y, src, decx, decy, ny, pseudo);
		}

		function _add_player(x, y, src, decx, decy, ny, pseudo){

			id = players.length + 1;
			move_player_sens_memory[id] = [];
			player_PFz[id] = [];
			player_z_index[id] = [];
			move_player_step[id] = 0;
			move_player_time[id] = null;

			var local_player = [];
			local_player['id'] = id;
			local_player['sprite'] = src;
			local_player['x'] = x;
			local_player['y'] = y;
			local_player['decx'] = decx;
			local_player['decy'] = decy;
			local_player['ny'] = ny;
			local_player['pseudo'] = pseudo;
			players.push(local_player);
			mapPlayers[x][y] = pseudo;

			var idplayer = 'p_' + id;
			obj_conteneur.append(' <div id="' + idplayer + '" class="pp3diso-objet btiso-player"><img src="' + src + '" alt="" /></div>');
			$('#' + idplayer).attr('rel', src +':' + decx + ':' + decy);
			nbrObjLoad++;
			$('#' + idplayer + ' img').load(function(){
				var imgx = this.width;
				var imgy = this.height;
				player_img_tx[id] = ~~(imgx/ny);
				player_img_ty[id] = ~~(imgy/4);
				var ltx = ~~(player_img_tx[id] * params.zoom);
				var lty = ~~(player_img_ty[id] * params.zoom);
				var player_img = $('#'+ idplayer + ' img');

				nbrObjLoad--;
				if(move_map_waiting) move_map();
				$('#' + idplayer).css({
					'z-index' : 300000 + getZindex(y, x)/*+ dZ*/,
					'position': 'absolute',
					'left' :getPosX(y, x)+(decx*params.zoom),
					'top':getPosY(y, x)+(decy*params.zoom),
					'width' : ltx,
					'height' : lty,
					'overflow' : 'hidden',
					'display' : 'block'
				});

				player_img.css({
					'position' : 'absolute',
					'display' : 'block',
					'left' : 0,
					'top' : 0,
					'width' : ltx*ny,
					'height' : lty*4
				});
				movePlayerToPF(pseudo, x, y, false);
			});
			// La bulle pour le tchat
			obj_conteneur.append('<div id="bulle-tchat-'+pseudo+'" class="bulle-tchat"></div>');
			$('#bulle-tchat-'+pseudo).css('display', 'none');
			// La bulle pour le pseudo
			if(params.affichage_pseudo == 1){
				obj_conteneur.append('<div id="bulle_pseudo_'+pseudo+'" class="bulle_player_pseudo">'+pseudo+'</div>');
				$('#bulle_pseudo_'+pseudo).css({
					'top' : y,
					'left': x+(params.ty/2*params.zoom)
				});
			}
			return id;
		}

		this.movePlayer = function (pseudo, x, y){
			movePlayerToPF(pseudo, x, y, false)
		}

		this.killPlayer = function (pseudo){
			_killPlayer(pseudo);
		}

		function _killPlayer(pseudo){
			console.log(pseudo);
			var idplayer;
			for(var i in players){
				if(players[i]['pseudo'] == pseudo){
					idplayer = '#p_'+ players[i]['id'];
					players[i]['id'] = '';
					break;
				}
			}
			_playersClean();
			if($(idplayer).length > 0){
				$(idplayer).remove();
			}
			// On supprime la bulle pour le tchat
			if($('#bulle-tchat-'+pseudo).length>0){
				$('#bulle-tchat-'+pseudo).remove();
				delete(timer_bulle_tchat[pseudo]);
			}
			// On supprime la bulle du pseudo
			if(params.affichage_pseudo == 1){
				$('#bulle_pseudo_'+pseudo).remove();
			}
		}

		function _playersClean(){
			var dummy = [];
			for(var i in players){
				if(players[i]['id']!=''){
					dummy[i] = [];
					dummy[i] = players[i];
				}
			}
			players = null;
			players = dummy;
		}
		
		/**
		 * Change un carré de la map
		 * @param x
		 * @param y
		 * @param sprite
		 */
		this.changeOneMap = function(x, y, sprite) {
			var id = 'c_' + y + '_' + x;
			var objidCase = $('#' + id);
			var zoom2 = '100%';
			var src = fileBase + sprite + '.png';
			monde[y][x] = sprite;
			if(params.zoom!=1 && theBrowser=='msie') {
				objidCase.css({
					'background-image':"url('" + src + "')",
					'-webkit-background-size': zoom2 + ' ' + zoom2, 
					'-o-background-size': zoom2 + ' ' + zoom2, 
					'-moz-background-size': zoom2 + ' ' + zoom2, 
					'background-size': zoom2 + ' ' + zoom2,
					'background-position':'center center',
					'background-repeat':'no-repeat',
					'-ms-filter':"progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + src + "', sizingMethod='scale')",
					'filter':"progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + src + "', sizingMethod='scale')"
				});
			}else{
				objidCase.css({
					'background-image':"url('" + src + "')",
					'-webkit-background-size': zoom2 + ' ' + zoom2, 
					'-o-background-size': zoom2 + ' ' + zoom2, 
					'-moz-background-size': zoom2 + ' ' + zoom2, 
					'background-size': zoom2 + ' ' + zoom2
				});
			}
			
		}

		/**
		 * change l'état d'une case
		 * @param x case x
         * @param y case y
         * @param etat : 1=on peut y aller, 2:y'a un batiment
         */
		this.changeState = function(x, y, etat) {
			zone[y][x] = etat;
		}

		/**
		 * Retourne l'état d'une zone
		 *
		 */
		this.getState = function(x, y) {
			return zone[y][x];
		}

		/**
		 * Retourne le continue d'une case de la map
		 *
		 */
		this.getOneMap = function(x, y) {
			return monde[y][x];
		}
			
		/**
		 * détruit un batiment
                 * @param x case x contenant le bâtiment
                 * @param y case y contenant le bâtiment
		 */
		this.killBuilding = function(x, y) {
			_killBatiment(x, y);
		}
		
        /**
         * Méthode interne pour supprimer un bâtiment
         * @param x
         * @param y
         */
        function _killBatiment(x, y) {
			var id = '#b_' + y + '_' + x;
			zone[y][x] = 1;
			if($(id).length) {
				$(id).remove().empty();
			}
		}
                
		/**
		 * ajoute un bâtiment
                 * @param x case x pour la position du bâtiment
                 * @param y case y pour la position du bâtiment
                 * @param sprite
                 * @param decx décalage du bâtiment sur la case en x
                 * @param decy décalage du bâtiment sur la case en y
		 */
		this.addBuilding = function (x, y, sprite, decx, decy) {
			_addBatiment(x, y, sprite, decx, decy);
		};
		
        /**
         * Méthode interne pour ajouter un bâtiment
         * @param x
         * @param y
         * @param sprite
         * @param decx
         * @param decy
         */
		function _addBatiment(x, y, sprite, decx, decy) {
			var zoom2 = zoom;
			var px = getPosX(y, x) + (decx * params.zoom);
			var py = getPosY(y, x) + (decy * params.zoom);
			zone[y][x] = 2;
			var id = 'b_' + y + '_' + x;
			
			obj_conteneur.append('<div id="' + id + '" class="pp3diso-batiment"><img src="' + sprite + '" alt="" /></div>');
			var idBat = $('#' + id);
			idBat.attr('rel', sprite + ':' + decx + ':' + decy);
			nbrObjLoad++;
			$('#' + id + ' img').load(function() {
				var ltx = ~~(this.width * params.zoom);
				var lty = ~~(this.height * params.zoom);
				//idBat.html('' + ltx + ' x ' + lty);
				//idBat.html('' + this.src);
				$(this).attr('rel', this.width + ':' + this.height + ':' + this.top + ':' + this.left);
				$(this).width(ltx).height(lty);
				//$('#' + id + ' img').height(lty);
				nbrObjLoad--;
				if(move_map_waiting) move_map();
			});
			
			idBat.css({
				'z-index': 300000 + getZindex(y, x),
				'position':'absolute',
				'left':(px|0),
				'top':(py|0),
				//'border':'2px solid red',
				//'width':zoom,
				//'height':zoom,
				'display':'block'
			});
		};
		
		
		/**
		 * nettoie le tableau des objets
		 */
		function _objetsClean() {
			//var l = o_objets.length;
			var dummy = [];
			//for(var i = 0;i<l;i++) {
			for(var i in o_objets) {
				if(o_objets[i]['id'] != '') {
					dummy[i] = [];
					dummy[i] = o_objets[i];
					//for(var j = i; j<l-1;j++) {
					//	for(var propriete in o_objets[j]) {
							//o_objets[j][propriete] = o_objets[j+1][propriete];
					//	}
					//	o_objets[j] = o_objets[j+1];
					//}
					//o_objets[l-1] = null;
				}
			}
			o_objets = null;
			o_objets = dummy;
		}
		
		/**
		 * détruit un objet
                 * @param {int} id id de l'objet à suprimer
		 */
		this.killObject = function(id) {
			_killObject(id);
		}
		
        /**
         * méthode interne pour supprimer un objet
         * @param {int} id 
         */
        function _killObject(id) {
			var idobj = '#o_' + id
			//var l = o_objets.length;

			//for(var i = 0;i<l;i++) {
			for(var i in o_objets) {
				//if(o_objets[i].length > 0) {
					if(o_objets[i]['id'] == id) {
						o_objets[i]['id'] = '';
						break;
					}
				//}
			}
			_objetsClean();
			if($(idobj).length > 0) {
				$(idobj).remove();
			}
		}
                
		/**
		 * ajoute un objet
         * @param {Int} x
         * @param {Int} y
         * @param sprite
         * @param {Int} decx
         * @param {Int} decy
         * @param {String} titre
         * @param {String} bulle
         * @param {Int} id
		 */
		this.addObject = function (x, y, z, sprite, decx, decy, vid, titre, bulle, id) {
			return _addObject(x, y, z, sprite, decx, decy, vid, titre, bulle, id);
		};
		
	    /**
	     * Méthode interne pour ajouter un objet
	     */
		function _addObject(x, y, z, sprite, decx, decy, vid, titre, bulle, id) {
			var px = getPosX(y, x) + (decx * params.zoom);
			var py = getPosY(y, x) + (decy * params.zoom);
			//objets[y][x] = 3;
			//objets_titre[y][x] = titre;
			
			if(typeof(bulle) == "undefined") bulle = '';
			dummy = o_objets.length + 1;
			if(typeof(id) == "undefined") id = dummy;

			var o_objets_local = [];
			o_objets_local['id'] = id;
			o_objets_local['x'] = x;
			o_objets_local['y'] = y;
			o_objets_local['z'] = z;
			o_objets_local['sprite'] = sprite;
			o_objets_local['decx'] = decx;
			o_objets_local['decy'] = decy;
			o_objets_local['vid'] = vid;
			o_objets_local['titre'] = titre;
			o_objets_local['bulle'] = bulle;
			o_objets.push(o_objets_local);
			
			var dZ = 0;
			if(typeof(bt.items[vid])!='undefined'){
				var itzone = JSON.parse(bt.items[vid].zone);
				var itzl = itzone.length;
				if(itzl>1){
					dZ = getDecZZone(x, y, z, itzone, itzl);
				}
				else{
					dZ = getDecZ(x, y);
				}
			}
			
			var idobj = 'o_' + id;
			$('#' + idobj).remove().empty();
			
			var labulle = '';
			if(bulle != '') {
				labulle = '<div class="pp3diso-obj-bulle">' + bulle + '<div>';
			}
			obj_conteneur.append('<div id="' + idobj + '" class="pp3diso-objet"><img src="' + sprite + '" alt="" />' + labulle + '</div>');
			
			$('#' + idobj).attr('rel', sprite + ':' + decx + ':' + decy);
			nbrObjLoad++;
			$('#' + idobj + ' img').load(function() {
				var ltx = ~~(this.width * params.zoom);
				var lty = ~~(this.height * params.zoom);
				$(this).width(ltx).height(lty);
				if(bulle != '') {
					var b = $('#' + idobj + ' .pp3diso-obj-bulle');					

					if(params.bulle_auto_x) {
						var w = b.outerWidth();
						var bpx = ~~(((ltx - w)>>1));
						b.css({
							left:bpx
						});
					}
					if(params.bulle_auto_y == 'top') {
						var h = b.height();
						var bpy = -(h + params.bulle_obj_deca_y);
						b.css({
							top:bpy
						});
					}else if(params.bulle_auto_y == 'bottom') {
						var h = b.height();
						var bpy = -(h + params.bulle_obj_deca_y);
						b.css({
							bottom:bpy
						});
					}
				}
				
				nbrObjLoad--;
				if(move_map_waiting) move_map();
			});
			$('#' + idobj).css({
				'z-index': 300000 + getZindex(y, x)+dZ,
				'position':'absolute',
				'left':~~(px),
				'top':~~(py)-(params.tx/2*z),
				//'width':ltx,
				//'height':lty,
				'display':'block'
			});
			return id;
		};
		
		
		/**
		 * Change le contenuu d'une bulle d'un objet
		 * @param idObj
		 * @param bulle
		 */
		this.changeBulle = function (idObj, bulle) {
			return _changeBulle(idObj, bulle);
		};
		
		/**
		 * Change le contenu d'une bulle d'un objet
		 * @param idObj
		 * @param bulle
		 */
		function _changeBulle(idObj, bulle) {
			for(var i in o_objets) {
				if(o_objets[i]['id'] == idObj) {
					o_objets[i]['bulle'] = bulle;
					var obj = $('#o_' + idObj + ' .pp3diso-obj-bulle');
					obj.html(bulle);
					break;
				}
			}
		}
		

		/**
		 * kill message
		 * @returns
		 */
		function killMessage() {
			$('#pp3diso-win-fond').fadeTo(500, 0, function() {
				$('#pp3diso-win-fond').remove().empty();				
			});
			$('#pp3diso-win').remove().empty();
			$('#pp3diso-clicks').css('display', 'block');
		}
		
		/**
		 * Affichage d'un message
		 * @param id i du message pour le callback
		 */
		this.message = function(id, texte, contenu) {
			if($('#pp3diso-win').length != 0) return;
			obj_tip.css('display', 'none');
			
			var win = '<div id="pp3diso-win-fond"></div>';
			win += '<div id="pp3diso-win"><div id="pp3diso-close"></div><div class="pp3diso-win-texte">'+texte+'</div>';
			
			if(contenu != '') {
				var type = contenu.split('||');
				win += '<div id="pp3diso-win-form">';
				var boucleCond = type.length;
				for(var i=0;i<boucleCond;i++) {
					dummy = type[i].split('::');
					win += '<a href="#" rel="' + dummy[1] + '" class="pp3diso-win-button" id="pp3diso-win-' + i + '">' + dummy[0] + '</a>';
				}
				win += '</div>';
			}
			win += '</div>';
			obj.append(win);
			$('#pp3diso-win-0').focus();
			var idWinObj = $('#pp3diso-win');
 			dummy = (taille3DisoX / 3);
			var width = (dummy << 1);
			var left = (dummy >> 1);
			var etat = -1;
			$('#pp3diso-win-fond').css({
				'position':'absolute',
				'width':tailleMapX,
				'height':tailleMapY,
				'left':0,
				'top':0,
				'z-index': 800000,
				'display':'block'
			}).fadeTo(0, 0.40);
			idWinObj.css({
				'position':'absolute',
				'width':width,
				'left':left,
				'top':50,
				'z-index': 800001,
				'display':'none'
			});
			idWinObj.slideDown(100);
			
			$('#pp3diso-clicks').css('display', 'none');
			
			$('#pp3diso-close').click(function(e) {
				e.preventDefault();
				e.stopImmediatePropagation();
				params.beforeclosewin(0);
				idWinObj.slideUp('fast', function() {
					killMessage();
					params.afterclosewin(id, etat);
				});
			});
			if(contenu != '') {
				var type = contenu.split('||');
				var boucleCond = type.length;
				for(var i=0;i<boucleCond;i++) {
					var dummy = type[i].split('::');
					
					$('#pp3diso-win-' + i).click(function(e){
						e.preventDefault();
						e.stopImmediatePropagation();
						var value = $(this).attr('rel');
						params.beforeclosewin(value);
						idWinObj.slideUp('fast', function() {
							killMessage();
							params.afterclosewin(id, value);
						});
					});
				}
			}
		}
		
		/**
		 * Zoom
		 */
		this.zoomMap = function(new_zoom) {
			_zoomMap(new_zoom);
		}
		
		/**
		 * Nettoyage de la mémoire
		 * @returns
		 */
		function clearMemory() {
			$('#pp3diso-clicks').remove().empty();
			$('#pp3diso-Map').remove().empty();
			
			if(params.mapZonesColors != '') {
				var colors = params.mapZonesColors.split(':');
				var boucleCond = colors.length;
				for(i = 0;i<boucleCond;i++) {
					canvas_ctx[i].clearRect(0,0,canvas[i].width,canvas[i].height);
				}
				$('pp3Diso-mapZone').remove().empty();
			}
		}
		
		/**
		 * Effectue un zoom sur la map
		 * @param new_zoom
		 * @returns
		 */
		function _zoomMap(new_zoom) {
			$('#pp3diso-avatar').clearQueue();
			move_avatar_sens_memory = null;
			move_avatar_sens_memory = [];
			avatar_z_index = null;
			avatar_z_index = [];
			
			$('.pp3Diso_PF').remove().empty();
			
			if(new_zoom < params.zoom_min) new_zoom = params.zoom_min;
			if(new_zoom > params.zoom_max) new_zoom = params.zoom_max;
			
			clearMemory();
			
			var old_zoom = params.zoom;
			params.zoom = new_zoom;
			var decal_zoom = old_zoom - new_zoom;
			var padx2 = padx - ((padx * decal_zoom)|0);
			var pady2 = pady - ((pady * decal_zoom)|0);
			
			padx = padx2;
			pady = pady2;
			
			zoom = ~~(params.zoom*100) + '%';
			params.tx = paramstx * params.zoom;
			params.ty2 = (params.tx >> 1);
			params.ty = paramsty * params.zoom;
			params.ty2 = paramsty2 * params.zoom;
			
			tx2 = (params.tx >> 1);
			ty2 = params.ty2;
			
			//tailleMapX = ~~(Mapx * (params.tx));
			//tailleMapY = ~~(Mapy * (ty2 << 1) + (params.ty2));

			tailleMapX = ~~((Mapx * ((params.tx) >> 1)) + (Mapy * ((params.tx) >> 1)));
			tailleMapY = ~~((Mapx * ((params.ty) >> 1)) + (Mapy * ((params.ty) >> 1)));
			tailleMapX2 = (tailleMapX >> 1);
			
			padxFixe = ((taille3DisoX - tailleMapX) >> 1);
			padyFixe = ((taille3DisoY - tailleMapY) >> 1);
			
			if(params.mapZonesColors != '') {
				var colors = params.mapZonesColors.split(':');
				var boucleCond = colors.length;
				for(i = 0;i<boucleCond;i++) {
					canvas[i].width = tailleMapX;
					canvas[i].height = tailleMapY;
				}
			}
			
			if(params.auto_size) {
				obj.css({
					'width':tailleMapX,
					'height':tailleMapY
				});
			}
			
			first(monde);

			obj_wait.css('display', 'block');

			view(monde);
			var dummy = [];
			dummy = o_objets;
			//var l = o_objets.length;
			o_objets = null;
			o_objets = [];
			//for(var i = 0;i<l;i++) {
			for(var i in dummy) {
				if(dummy[i]['id'] != '') {
					var id = 'o_' + dummy[i]['id'];
					$('#' + id).remove().empty();
					_addObject(dummy[i]['x'], dummy[i]['y'],dummy[i]['z'], dummy[i]['sprite'], dummy[i]['decx'], dummy[i]['decy'], dummy[i]['vid'], dummy[i]['titre'], dummy[i]['bulle'], dummy[i]['id']);
				}
			}
			for(var y = 1;y<=Mapy;y++) {
				for(var x=1;x<=Mapx;x++) {
					if(zone[y][x] == 2) {
						var id = 'b_' + y + '_' + x;
						dummy = ($('#' + id).attr('rel')).split(':');
						$('#' + id).remove().empty();
						_addBatiment(x, y, dummy[0], dummy[1], dummy[2]);
					}
					
				}
			}
			
			//---- le curseur 
			var dummy_cursor = $('#pp3diso-cursor-img');
			if(!dummy_cursor.length==0) {
				var dummy = dummy_cursor.attr('rel').split(':');
				cursor_tx = ~~(dummy[0] * params.zoom);
				cursor_ty = ~~(dummy[1] * params.zoom);	
				dummy_cursor.attr('width', cursor_tx).attr('height', cursor_ty);
			}
			$('#pp3diso-cursor').css({'width' : cursor_tx/cursor_nb_frame});
			//---- l'avatar 
			var dummy_avatar = $('#pp3diso-avatar-img');
			if(!(dummy_avatar.length == 0)) {
				if(avatar_animation) {
					nbr_x = 4;
				}else{
					nbr_x = 1;
				}
				var zoom2 = ~~(100 * avatar_ny) + '%';
				var ltx = ~~((avatar_img_tx * avatar_ny * params.zoom)/avatar_ny);
				var lty = ~~((avatar_img_ty * params.zoom));
				var obj_img = $('#pp3diso-avatar-img');

				$('#pp3diso-avatar').css({
					'width':ltx,
					'height':lty
				});
				obj_img.css({
					'position':'absolute',
					'overflow':'hidden',
					'display':'block',
					'left':0,
					'top':0,
					'width':ltx*avatar_ny,
					'height':lty*nbr_x
				});
				
				
			}
			
			avatar_decx = avatar_decx_ori * params.zoom;
			avatar_decy = avatar_decy_ori * params.zoom;
			var dummy = params.speed_avatar;
			params.speed_avatar = 1;
			moveAvatar(avatar_posx, avatar_posy);
			params.speed_avatar = dummy;
			
			//obj_conteneur.css('display', 'none');
			move_map();
			params.onchangezoom(new_zoom);
		}
		
		/**
		 * déclare le mouvement de la map
		 */
		this.moveMapOn = function() {
			var sens = ['s', 'n', 'e', 'o', 'se', 'so', 'ne', 'no'];
			var boucleCond = sens.length;
			for(var i = 0; i < boucleCond; i++) {
				var dummy = '#pp3diso-fleche-' + sens[i];
				if($(dummy).length) {
					$(dummy).bind('mouseover', function() {
						var dummy = $(this).attr('id');
						dummy = dummy.substr(15);
						if(!move_map_while_flag) {
							move_map_while_flag = true;
							_moveMapOne(dummy, params.speed_by_titleset);
						}
					});
					$(dummy).bind('mouseout', function() {
						_moveMapStop();
					});
				}
			}
		}
		
		
		/**
		 * Bouge la map
		 * @param my_sens n, s, e, o (ne, no, se, so)
		 * @param val
		 * @returns
		 */
		function _moveMapOne(my_sens, val) {
			if(move_map_actually) return;
			var ty = (params.ty>>1)*val;
			var tx = (params.tx>>1)*val;
			switch(my_sens) {
				case 'n':	pady+=ty;
						break;
				case 'ne':	pady-=ty; padx-=tx;
						break;
				case 'e':	padx-=tx;
						break;
				case 'se':	padx-=tx; pady+=ty;
						break;
				case 's':	pady-=ty;
						break;
				case 'so':	pady+=ty; padx+=tx;
						break;
				case 'o':	padx+=tx;
						break;
				case 'no':	pady-=ty; padx+=tx;
						break;
			}
			if(padx < -(tailleMapX - taille3DisoX)) {
				padx = -(tailleMapX - taille3DisoX);
			}
			if(padx > 0) {
				padx = 0;
			}
			if(pady < -(tailleMapY - taille3DisoY)) {
				pady = -(tailleMapY - taille3DisoY);
			}
			if(pady > 0) {
				pady = 0;
			}
			
			move_map();
			if(move_map_while_flag) {
				clearInterval(timer_map_move);
				timer_map_move = setInterval(function(){
					if(move_map_while_flag && !move_map_actually) {
						_moveMapOne(my_sens, val);
					}
				}, params.speed_map_while);
			}
		}
		
		
		/**
		 * Bouge la map
		 * @param sens n, s, e, o (ne, no, se, so)
		 * @param val
		 */
		this.moveMapOne = function (sens, val) {
			_moveMapOne(sens, val);
		}
		
		/**
		 * bouge la map en répétition
		 * @param sens n, s, e, o (ne, no, se, so)
		 * @param val
		 */
		this.moveMapWhile = function(sens, val) {
			move_map_while_flag = true;
			_moveMapOne(sens, val);
		}
		
		/**
		 * Arrête le mouvement de la map
		 * @returns
		 */
		function _moveMapStop() {
			move_map_while_flag = false;
			clearInterval(timer_map_move);
		}
		
		/**
		 * Arrête le mouvement de la map
		 */
		this.moveMapStop = function() {
			_moveMapStop();
		}
		
		
		/**
		 * Bulle d'info
		 * @param texte html du contenu
		 * @param genre 0: aux côtés de l'objet survolé, 1:position définie par le css
		 * @param posx
		 * @param posy  
		 */
		this.tipShow = function(texte, genre, posx, posy) {
			obj_tip.css({'display':'none'});
			obj_tip.html(texte);
			if(genre==0) {	//---- position par rapport à l'objet
				posx = getPosX(cursor_posy, cursor_posx) + cursor_decx + posx;
				posy = getPosY(cursor_posy, cursor_posx) + cursor_decy + posy;
			}else if(genre==1){	//---- position fixe
				if(posx < 0) {
					posx += taille3DisoX;
					posx -= obj_tip.outerWidth();
				}
				if(posy < 0) {
					posy += taille3DisoY;
					posy -= obj_tip.outerHeight();
				}
				posx -= padx;
				posy -= pady;
			}
			obj_tip.css({
				'top':posy,
				'left':posx,
				'display':'none'
			});
			
			obj_tip.stop(true, true).fadeIn('normal');
			etat_tip = true;
		}
		
		/**
		 * masque la bulle d'info
		 */
		this.tipHide = function() {
			obj_tip.stop(true, true).fadeOut('normal', function() {
				etat_tip = false;
			});
		}

		function _pseudo_show(pseudo, x, y){
			console.log('Show pseudo '+pseudo);
			console.log (x+' '+y);
			obj_bulle_pseudo.css('display', 'none');
			obj_bulle_pseudo.html(pseudo);
			obj_bulle_pseudo.css({
				'top' : y,
				'left' : x+(params.ty/2*params.zoom),
				'display' : 'block'
			});
		}

		function _pseudo_hide(){
			obj_bulle_pseudo.css('display', 'none');
		}

		this.show_text = function(pseudo, text){
			return _text_tchat_show(pseudo, text);
		}
		
		function _text_tchat_show(pseudo, text){
			console.log(pseudo);
			console.log(text);
			var player = getPlayer(pseudo);
			// Si le joueur n'est pas sur la même map
			if(!player){
				return false;
			}
			else{
				var obj_bulle_tchat = $('#bulle-tchat-'+pseudo);
				var player_x = getPosX(player.y, player.x)+((player['decx']|0)*params.zoom) ;
				var player_y = getPosY(player.y, player.x)+((player['decy']|0)*params.zoom) ;
				console.log(player_x+' '+player_y);
				obj_bulle_tchat.text(text);
				obj_bulle_tchat.css({
					'display' : 'block',
					'left' : player_x,
					'top' : player_y
				});
				timer_bulle_tchat[pseudo] = setTimeout(function(){_hide_bulle(obj_bulle_tchat)}, duree_bulle_tchat);
			}
		}

		function _hide_bulle(bulle){
			bulle.fadeOut(250,function(){ bulle.css('display', 'none')});

		}

		/**
		 * Montre ou masque une zone
		 */
		this.toggleZone = function(zone, vitesse) {
			if(vitesse == '') vitesse = 'normal';
			zone--;
			var zo = $('#pp3Diso-mapZone-canvas-' + zone);
			if(zo.css('display') == 'none') {
				zo.fadeIn(vitesse);
			}else{
				zo.fadeOut(vitesse);
			}
		}
		
		/**
		 * Montre une zone
		 */
		this.showZone = function(zone, vitesse) {
			if(vitesse == '') vitesse = 'normal';
			zone--;
			$('#pp3Diso-mapZone-canvas-' + zone).fadeIn(vitesse);
		}
		
		/**
		 * Masque une zone
		 */
		this.hideZone = function(zone, vitesse) {
			if(vitesse == '') vitesse = 'normal';
			zone--;
			$('#pp3Diso-mapZone-canvas-' + zone).fadeOut(vitesse);
		
		}
		

		/**
		 * permet de stopper le cursorDelay
		 * @param number etat : 1=ok, 0:stoppé
		 */
		this.switchCursorDelay = function(etat) {
			cursorDelayEtat = etat;
		}

		return this;
	};
})(jQuery);

/**
 * pathfinding
 * http://www.prelude-prod.fr
 *
 * @author Jean-François RENAULD
 * @version 1.0
 * Cette oeuvre est mise à disposition selon les termes de la Licence Creative Commons Paternité - Partage à l'Identique 3.0 non transcrit.
 * http://creativecommons.org/licenses/by-sa/3.0/deed.fr
 *
 * Si vous utilisez ce plugin, vous devez mettre un lien retour vers le site : http://www.prelude-prod.fr
 *
 * Date: Fri Apr 9 11:48:00 UTC 2013
 */

var mapLength, mapRowLength, gMinx, gMaxx, gMiny, gMaxy;

function astar(debx, deby, endx, endy, map, cutCorners, minx, maxx, miny, maxy) {
	var listOpen = [];
	var listClosed = [];
	var listPath = [];
	
	if(maxx == false) {
		gMaxx = map.length;
	} else {
		gMaxx = maxx;
	}
	if(minx == false) {
		gMinx = 0;
	} else {
		gMinx = minx;
	}

	if(maxy == false) {
		gMaxy = map[0].length;
	} else {
		gMaxy = maxy;
	}
	if(miny == false) {
		gMiny = 0;
	} else {
		gMiny = miny;
	}
	mapLength = gMaxx;
	mapRowLength = gMaxy;
	
	var nodeGoal = new Node(endx, endy, map, null, null);
	var nodeStart = new Node(debx, deby, map, null, nodeGoal);
	
	addNodeToList(nodeStart, listOpen);
	
	var n;
	while(!isListEmpty(listOpen)) {
		n = returnNodeWithLowestFScore(listOpen);
		addNodeToList(n, listClosed);
		removeNodeFromList(n, listOpen);
		if(areNodesEqual(n, nodeGoal)) {
			pathTo(n, listPath);
			listPath.reverse();
			return listPath;
		}
		n.makeChildNodes(map, cutCorners, nodeGoal);
		cullUnwantedNodes(n.childNodes, listOpen);
		cullUnwantedNodes(n.childNodes, listClosed);
		removeMatchingNodes(n.childNodes, listOpen);
		removeMatchingNodes(n.childNodes, listClosed);
		addListToList(n.childNodes, listOpen);
	}
	return null;
}

function pathTo(n, listPath) {
	listPath.push(new NodeCoordinate(n.row, n.col));
	if(n.parentNode == null) {
		return;
	}
	pathTo(n.parentNode, listPath);
}

function addListToList(listA, listB) {
	for(x in listA) {
		listB.push(listA[x]);
	}
}

function removeMatchingNodes(listToCheck, listToClean) {
	var listToCheckLength = listToCheck.length;
	var dummy = listToClean.length;
	for(var i = 0; i < listToCheckLength; i++) {
		for(var j = 0; j < dummy; j++) {
			if(listToClean[j].row == listToCheck[i].row && listToClean[j].col == listToCheck[i].col) {
				listToClean.splice(j, 1);
				dummy = listToClean.length;
			}
		}
	}
}

function cullUnwantedNodes(listToCull, listToCompare) {
	var listToCompareLength = listToCompare.length;
	var dummy = listToCull.length;
	for(var i = 0; i < listToCompareLength; i++) {
		for(var j = 0; j < dummy; j++) {
			if(listToCull[j].row == listToCompare[i].row && listToCull[j].col == listToCompare[i].col) {
				if(listToCull[j].f >= listToCompare[i].f) {
					listToCull.splice(j, 1);
					dummy = listToCull.length;
				}
			}
		}
	}
}

function areNodesEqual(nodeA, nodeB) {
	if (nodeA.row == nodeB.row && nodeA.col == nodeB.col) {
		return true;
	} else {
		return false;
	}
}

function returnNodeWithLowestFScore(list) {
	var lowestNode = list[0];
	for(x in list) {
		if(list[x].f < lowestNode.f) {
			lowestNode = list[x];
		} 
	}
	return lowestNode;
}

function isListEmpty(list) {
	return (list.length < 1) ? true : false;
}

function removeNodeFromList(node, list) {
	var listLength = list.length;
	for(var i = 0; i < listLength; i++) {
		if(node.row == list[i].row && node.col == list[i].col) {
			list.splice(i, 1);
			break;
		}
	}
}

function addNodeToList(node, list) {
	list.push(node);
}

function returnHScore(node, nodeGoal) {
	var y = node.row - nodeGoal.row;
	if(y < 0) {
		y = -y;
	}
	var x = node.col - nodeGoal.col;
	if(x < 0) {
		x = -x;
	}
	if(x > y) {
		return (y * 14) + 10 * (x - y);
	} else {
		return (x * 14) + 10 * (y - x);
	}
}

function NodeCoordinate(row, col) {
	this.row = row;
	this.col = col;
}

function Node(row, col, map, parentNode, nodeGoal) {
	this.row = row;
	this.col = col;
	if(row <= gMiny) {
		this.northAmbit = gMiny;
	} else {
		this.northAmbit = row - 1;
	}
	if(row >= gMaxy - 1) {
		this.southAmbit = gMaxy - 1;
	} else {
		this.southAmbit = row + 1;
	}
	if(col <= gMinx) {
		this.westAmbit = gMinx;
	} else {
		this.westAmbit = col - 1;
	}
	if(col >= gMaxx - 1) {
		this.eastAmbit = gMaxx - 1;
	} else {
		this.eastAmbit = col + 1;
	}
	this.parentNode = parentNode;
	this.childNodes = [];

	if(parentNode != null) {
		if(row == parentNode.row || col == parentNode.col) {
			this.g = parentNode.g + 10;
		} else {
			this.g = parentNode.g + 14;
		}
		this.h = returnHScore(this, nodeGoal);

	} else {
		this.g = 0;
		if (map[row][col] == 's') {
			this.h = returnHScore(this, nodeGoal);
		} else {
			this.h = 0;
		}
	}
	this.f = this.g + this.h;
	
	this.makeChildNodes = function(map, cutCorners, nodeGoal) {
		for(var i = this.northAmbit; i <= this.southAmbit; i++) {
			for(var j = this.westAmbit; j <= this.eastAmbit; j++) {
				if(i != this.row || j != this.col) {
					if(map[i][j] != '0') {
						if(cutCorners) {
							this.childNodes.push(new Node(i, j, map, this, nodeGoal));
						} else {
							if(i == this.row || j == this.col) {
								this.childNodes.push(new Node(i, j, map, this, nodeGoal));
							}
						}
					}
				}
			}
		}
	}
}