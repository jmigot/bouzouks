;(function($, window, document, undefined)
    {
        $.fn.mapWalker = function (params){

            var options = {};
            var settings = $.extend(options, params);

            var Choix = 0; // token pour la gestion des outils

            var socket = false; // object représantant la connection à socket.io
            var socketEvent, socketRequest;

            var items = [];
            var config = [];

            var map = [];
            var mapConfig= [];
            var map_size;
            var decor = []; // Liste des éléments du décor

            var pseudo; // Le pseudo du joueur
            var param_joueur; // Paramètres personnalisés du joueur

            var avatar;
            var map_x = 1; // Point d'arrivée de l'avatar
            var map_y = 1;
            var avatar_pos_x = 1; // Position actuelle de l'avatar
            var avatar_pos_y = 1;

            var btmap;
            var oZone = []; // Tableau tridim de la répartition des objets
            var natMap = []; // Carte de la nature des sols

            // Configuration du zoom
            var zoom_min;
            var zoom_max;
            var zoom_pas;
            var zoom;

            var img_path;
            var img_interface;
            var img_item;
            var img_avatar;

            var token_tp = false;

            var mask = false;
            var maskAvatar = false;

            var self = this;// L'élément du dom auquel est attaché le plugin
            var element = $(this); // Sa version jquery

            var tchat = {};

            var blink_state = 0;
            var blink_step =0;
            var nb_blink_step = 20;
            var old_opacity

            var auth_level = 0; // Niveau d'acréditation (0 = aucun, 1 = joueur, 2 = modo, 3 = admin)

            // Fonction d'initialisation du plugin
            var init = function(){
                if ( ! window.WebSocket)
                {
                    alert('Ton navigateur ne supporte pas les WebSocket, tu ne peux pas visualiser la carte');
                    return;
                }
                else if(typeof(io) == 'undefined'){
                    alert('La map n\'est pas disponible');
                    return;
                }
                else{
                    // connection au serveur node
                    socket = getSocket();
                    // Initialisation du tchat
                    _vlux_tchat();
                }
            };

            window.onbeforeunload = function(){
                socket.emit('disconnect_unload', true);
            };

            /*############################################# 
            *
            *   Gestion de la connexion au serveur node.js 
            *
            *############################################*/

            var getSocket = function(){
                var host = site_url.replace('http', 'ws');
                host = host.substring(0, host.length-1)+':8080';
                // Connexion au serveur Vlux
                return new io.connect(host,{
                    'reconnection' : false
                });
            };


            /*############################
            *           Le tchat         *
            ############################*/
            var _vlux_tchat = function(){
                tchat.div = $('#vlux_tchat');
                tchat.input = $('#input_tchat');
                tchat.inputMessage = $('#tchat_message');
                tchat.chan = $('#list_chan');
                tchat.chan_actif = $('.chan_actif');
                $('#output_chan_global').css('display', 'inline-block');
                // Gestion des onglets
                tchat.chan.find('li').each(function(){
                    $(this).bind('click', function(event){
                        event.preventDefault();
                        var chan_id = $(this).attr('id');
                        console.log(chan_id);
                        if(tchat.chan_actif.attr('id')!= chan_id){
                            var old_chan_id = tchat.chan_actif.attr('id');
                            tchat.chan_actif.toggleClass('chan_actif');
                            $('#output_'+old_chan_id).css('display', 'none');
                            tchat.chan_actif = $(this);
                            tchat.chan_actif.toggleClass('chan_actif');
                            $('#output_'+chan_id).css('display', 'inline-block');
                        }
                    });
                });
                // Envoie du message
                tchat.input.find('#tchat_send').bind('click', function(event){
                    event.preventDefault();
                    tchat.sendMessage();
                });

                tchat.input.keydown(function(event){
                    if(event.wich === 13){
                        event.preventDefault();
                        tchat.sendMessage();
                    }
                });

                tchat.input.find('#tchat_suppr_msg').bind('click', function(event){
                    event.preventDefault();
                    tchat.delete_msg();
                })
            };

            tchat.reload = function (old_map_id, map_id){
                // Suppresion du chan de map précédent
                $('#output_chan_map_room_'+old_map_id).remove();
                // Configuration du nouveau chan de map
                $('#chan_map_room_'+old_map_id).attr('id', 'chan_map_room_'+map_id);
                $('#vlux_tchat_output ul').append('<li id="output_chan_map_room_'+map_id+'"></li>');
                console.log(tchat.chan_actif.attr('id'));
                if(tchat.chan_actif.attr('id') == 'chan_map_room_'+map_id){
                    $('#output_chan_map_room_'+map_id).css('display', 'inline-block');
                }
            }

            tchat.onmessage = function(data){
                console.log(data);
                var chan = $('#output_'+data.chan);
                var supprimer = '';
                if(auth_level >1 && data.message_id>0){
                    supprimer = '<input type="checkbox" name="messages_ids[]" value="'+data.message_id+'"> ';
                }

                var scroll = false;
                // Si on est en bas de pages, on autorise l'auto-scroll.
                if(chan.prop('scrollHeight')- chan.height() == chan.prop('scrollTop')){
                    scroll = true;
                }
                // Ajout du message
                chan.append('<p class="vlux-tchat-message">'+supprimer+'<b>'+data.date+'</b> '+data.pseudo+data.content+'</p>');
                // Scroll
                if(scroll){
                    chan.animate({ scrollTop : chan.prop('scrollHeight')- chan.height()}, 100);
                }
                // Notif quand le pseudo du joueur apparait dans un message
                var regx = new RegExp( pseudo, 'i');
                if(regx.exec(data.content) && data.message_id>0){
                    // On fait clignoter l'onglet.
                    tchat.notif(data.chan);
                }
                //On supprime les messages trop anciens de l'affichage
                var nb_child = $(chan).children().length;
                for(var i=0; i<nb_child-50; i++){
                    $('#output_'+data.chan+' p').first().remove();
                }
                // Si le message vient d'un autre joueur et du chan de map, on affiche un bulle
                if(data.raw_pseudo != pseudo && data.chan!="chan_global"){
                    btmap.show_text(data.raw_pseudo, data.content);
                }
                
            };

            tchat.sendMessage = function(){
                var message = tchat.inputMessage.val();
                tchat.inputMessage.val('');
                var data = {
                    "chan_id" : tchat.chan_actif.attr('id'),
                    "message_content" : message
                };
                socket.emit('tchat_message', data);
            }

            tchat.delete_msg = function(){
                console.log('demande suppr msg');
                if ( ! confirm('Veux-tu vraiment supprimer les messages sélectionnés ?'))
                    return;

                var messages_ids = [];
                $('.vlux-tchat-message').each(function(cle, val){
                    var input = $(this).find('input[type=checkbox');
                    if (input.is(':checked')){
                        messages_ids.push(input.attr('value'));
                    }
                });
                socket.emit('suppr_msg', {'messages_ids' : JSON.stringify(messages_ids)});
            };

            tchat.suppr_messages = function (ids){
                console.log(ids);
                $('.vlux-tchat-message').each(function(){
                    var id_message = $(this).find('input[type=checkbox]').attr('value');
                    if(ids.indexOf(id_message) != -1){
                        $(this).remove();
                    }
                });
            };

            tchat.notif = function(chan){
                var onglet = $('#'+chan);
                // On joue une alerte sonore
                if(param_joueur.son_notif == 1){
                    $(' #son_notifications_map_tchat').trigger("play");
                }
                old_opacity = onglet.css('opacity');
                _blink_tab(onglet);
            };

            var _blink_tab = function(onglet){
                if(blink_step <= nb_blink_step){
                    console.log(blink_step);
                    if(blink_state == 0){
                        onglet.fadeTo(250, 0.1, function(){
                         blink_state = 1;
                         blink_step++;
                         _blink_tab(onglet);
                     });
                        
                    }
                    else{
                        onglet.fadeTo(250, 1, function(){
                            blink_state = 0;
                            blink_step++;
                            _blink_tab(onglet);
                        });
                        
                    }
                }
                else{
                    blink_step = 0;
                    blink_state = 0;
                    onglet.css('opacity', old_opacity);
                }
            };

            init();

            // Définition des écouteurs de io.socket
            socket.on('connect', function(){
                socket.emit('authentifier',{'id': id, 'token':websocket_auth, 'mode': 1})
            });

            socket.on('disconnect', function(){
                console.log("Déconnexion du serveur node");
            });

            socket.on('error', function (){
                alert('Le serveur est inaccessible. Veuiller en référer à un administrateur.');
            });

            // Universal Eventhandler from io.socket
            socket.onevent = function(p){
                socketEvent = p.data.shift();
                this[socketEvent](p.data[0]);
            };

            socket.authentifie = function(data){
                if(data >= 1){
                    socketRequest = {
                        "id" : map_id,
                        "type" : map_type,
                        "bouzouk" : id
                        };
                    socket.emit('afficher_map', socketRequest);
                    auth_level = data;
                }
            };

            socket.afficher_map = function(data){
                items = data.items;
                map = data.map;
                mapConfig = data.config;
                avatar = data.avat;
                pseudo = avatar.pseudo;
                param_joueur = data.param_joueur;
                $('#vlux_loader').remove();
                getBtmap();
            };

            socket.alert = function(msg){
                alert(msg);
            };

            socket.change_map = function(data){
                items = data.items;
                map = data.map;
                avatar = data.avat;
                _change_map();
            };

            socket.message = function(msg){
                var type = msg[0];
                var message = msg[1];
                if (type != null){
                    switch (type){
                        case 'notice' :
                        type = 'showNoticeToast';
                        break;

                        case 'succes' :
                        type = 'showSuccessToast';
                        break;
                        case 'alerte' :
                        type = 'showWarningToast';
                        break;
                        default :
                        type = 'showErrorToast';
                        break;
                    }
                    $().toastmessage(type, message);
                }
            };

            socket.afficher_form_porte = function(data){
                $().toastmessage('showToast',{
                    text : data,
                    sticky : true,
                    type : 'notice'
                });
            };

            socket.tchat = function(data){
                tchat.onmessage(data);
            };
            // Un joueur arrive sur la map
            socket.incoming_player = function (player){
                // On affiche l'avatar de l'arrivant
                btmap.add_player(player.map_x, player.map_y, img_avatar+'/'+player.img, player.decx, player.decy, 4, player.pseudo);
                // On lui envoie les coordonnées et info sur l'owner
                return _send_avatar_info(player.socket_id);
            };

            socket.player_info = function(player){
                btmap.add_player(player.actual_x, player.actual_y, img_avatar+'/'+player.img, player.decx, player.decy, 4, player.pseudo);
                if(player.actual_x != player.map_x || player.actual_y != player.map_y){
                    btmap.movePlayer(player.pseudo, player.map_x, player.map_y);
                }
            }

            socket.outcoming_player = function(pseudo){
                btmap.killPlayer(pseudo);
            }

            socket.movePlayer = function(player){
                btmap.movePlayer(player.pseudo, player.x, player.y);
            }

            socket.delete_msg_confirm = function(data){
                tchat.suppr_messages(data);
            }
            // Gestion de la map

            // Initialisation de la librairie bt3Diso
            var getBtmap = function(){
                btmap = $('#ppISO').bt3Diso({
                    mode : 'walker',
                    map : map.tuiles,
                    zone : map.zone,
                    mapId:map.id,              // id de la map
                    nbrTitleSetsSlide:mapConfig.map_slide,       // pas de mouvement de la map lorsque l'on click dessus
                    tx:100,                 // dimension x des tuiles
                    ty:65,                  // dimension y des tuiles
                    prefix:mapConfig.img_prefix,
                    zoom: param_joueur.zoom_defaut,
                    fluid : false,
                    nbrTitleSetsSlide : 10,
                    zoom_min : parseFloat(mapConfig.zoom_min),
                    zoom_max : parseFloat(mapConfig.zoom_max),
                    zoom_pas : parseFloat(mapConfig.zoom_pas),
                    speed_avatar : mapConfig.speed_avatar|0,
                    move_avatar_speed : mapConfig.move_avatar_speed|0,
                    speed_map : mapConfig.speed_map|0,
                    speed_map_while : mapConfig.speed_map_while|0,
                    speed_by_titleset : mapConfig.speed_by|0,
                    path: mapConfig.img_path+"objets/",
                    pathfinding : mapConfig.pathfinding|0,
                    PF_corners : Boolean(mapConfig.PF_corners|0),
                    cursorPF : mapConfig.cursor_PF,
                    PF_decx : mapConfig.PF_decx|0,
                    PF_decy : mapConfig.PF_decy|0,
                    auto_resize:true,
                    mousewheel:mapConfig.mousewheel|0,
                    cursorDelay : mapConfig.cursor_delay|0,
                    affichage_pseudo : param_joueur.affichage_pseudo,
                    onmoveavatar:function(x, y, id) {
                        _avatarEvent(x, y, id); 
                        _gestionMaskAvatar(x, y);
                        token_move_player = true;
                    },
                    onmovepathfinding: function(x, y, mapId){
                        _gestionMaskAvatar(x, y);
                        // On actualise la position du joueur pour la synchro
                        avatar_pos_x = x;
                        avatar_pos_y = y;
                    },
                    beforeonemoveavatar : function(x, y, mapId){
                        _move_player(x, y);
                        return true;
                    },
                    onclicmap : function (x, y){
                        _my_event(x, y);
                    }
                });

                zoom_min = parseFloat(mapConfig.zoom_min);
                zoom_max = parseFloat(mapConfig.zoom_max);
                zoom_pas = parseFloat(mapConfig.zoom_pas);
                zoom = parseFloat(mapConfig.zoom_default);
                img_path = mapConfig.img_path;
                img_interface = img_path+"/interface/";
                img_item = img_path+"objets/";
                img_avatar = img_path+"avatars";

                // Affichage
                load_map();
            };

            // Peuplement et affichage de la map
            var load_map = function(){
                token_tp = true;
                self.items = items;
                map_size = map.size|0;
                map_id = map.id;
                map_x = avatar['map_x']|0;
                map_y = avatar['map_y']|0;
                avatar_pos_x = map_x;
                avatar_pos_y = map_y;
                decor = map.decor;
                // Définition de la carte des objets
                oZone = new Array(11);
                for ( var oz=0; oz<=10; oz++){
                    oZone[oz] = new Array(map_size+1);
                    for(var ox=1; ox<=map_size; ox++) {
                        oZone[oz][ox] = new Array(map_size+1);
                        for(var oy=1; oy<=map_size; oy++){
                            oZone[oz][ox][oy] = [];
                        }
                    }
                }
                //Peuplement de la map
                var dl = decor.length;
                var item;
                var item_id;
                var item_ref;
                var item_img;
                var item_zone;
                var bt_id;
                for(var i=0; i<dl; i++){
                    item = decor[i];
                    item_id = item.vid;
                    item_ref = items[item_id];
                    item_img = img_item+item_ref['img']+'.png';
                    var bt_id = btmap.addObject(item.x, item.y, item.z, item_img, item_ref['decx'], item_ref['decy'],item_id);
                    if(item_ref.zone){
                        item_zone = JSON.parse(item_ref.zone);
                        set_Ozone(bt_id, item_ref, item.x|0, item.y|0, item.z|0);
                        if(item_ref.infranchissable){
                            var cl = item_zone.length;
                            for(var c=0; c<cl; c++){
                                btmap.changeState(parseInt(item_zone[c].x)+parseInt(item.x), parseInt(item_zone[c].y)+parseInt(item.y), 0);
                            }
                        }
                    }
                }
                // Affichage
                _gestionMaskAvatar(map_x, map_y);
                btmap.avatar(map_x, map_y, img_avatar+'/'+avatar.img, avatar['dec_x'], avatar['dec_y'], true, 4);
                btmap.cursor(img_interface+'cursor-on.png', img_interface+'cursor-off.png', 0, -30, 8);
                btmap.moveMapOn();
                btmap.moveTo(map_x, map_y);
                //Affichage du nom de la map
                $('#chan_map_room_'+map_id).text(map.nom);
                // Option tchat onglet par défaut
                if(param_joueur.chan_defaut == 'map'){
                    $('#chan_map_room_'+map_id).trigger('click');
                }
            };

            var _change_map = function(){
                var chan_global = $('#output_chan_global');
                // Position actuelle du scroll du chan global
                var scrollPosition = chan_global.scrollTop();
                // Changement de chan de map
                var old_map_id = map_id;
                tchat.reload(map_id, map.id);
                //Rechargement de bt3Diso
                btmap.reload(map.tuiles, map.zone, '', map.id);
                // Peuplement de la map
                load_map();
                // On remet le chan global à sa position
                chan_global.scrollTop(scrollPosition);
            }

            // Modification de la carte 3D de peuplement
            var set_Ozone = function(id, item, x, y, z){
                if(typeof(z) =='undefined'){ z=0; }
                var zItem = {
                    'id':id,
                    'support': JSON.parse(item.support),
                    'nature' : item.nature
                };
                var itemZone = JSON.parse(item.zone);
                var iH = parseInt(item.hauteur)+z;
                var itl = itemZone.length;
                // Les items qui n'ont pas d'épaisseur remplisse tout de même un cube.
                if(iH == z){ iH = 1+z};
                for( var iz=0; iz<itl; iz++){
                    var zx=(x+itemZone[iz].x);
                    var zy=(y+itemZone[iz].y);
                    for (var zz = z; zz<iH; zz++){
                        oZone[zz][zx][zy].push(zItem);
                    }
                }
            };

            // Renvoie l'objet au sein de bt3Diso
            var _getObjet = function (x, y, z){
                if(x<=0 || x>map_size || y<=0 || y>map_size || z>10 || z<0){
                    return false;
                }
                if(typeof(z) =='undefined'){ z=0; }
                var ozl = oZone[z][x][y].length;
                var dummy = [];
                for(var i=0; i<ozl; i++){
                    dummy.push(btmap.getObjet(oZone[z][x][y][i].id));
                }
                if(dummy.length==0){
                    return false;
                }
                else{
                    return dummy;
                }
            }

            var _getItem = function(x, y, z){
                if(typeof(z) =='undefined' || z<0){ z=0; }
                var objet_array = _getObjet(x, y, z);
                if(objet_array){
                    var oal = objet_array.length;
                    var dummy_a = [];
                    for(var i=0; i<oal; i++){
                        var ovid =(objet_array[i].vid|0);
                        var item = JSON.parse(JSON.stringify(items[ovid]));
                        item.x = objet_array[i].x|0;
                        item.y = objet_array[i].y|0;
                        item.z = objet_array[i].z|0;
                        item.btId = objet_array[i].id;
                        dummy_a.push(item);
                    }
                    return dummy_a;
                }
                else{
                    return false;
                }
            };

            var _getItemZone= function(x, y, z, zone, zonelength){
                var dummy_o = [];
                var o_objets = [];
                var ax, ay;
                for(var az=0; az<zonelength; az++){
                    ax = zone[az].x+x;
                    ay = zone[az].y+y;
                    o_objet = _getItem(ax, ay, z);
                    if(o_objet && dummy_o.indexOf(o_objet.btId)==-1){
                        o_objets.push(o_objet);
                        dummy_o.push(o_objet.btId);
                    }
                }
                if(o_objets.length>0){
                    return o_objets;
                }
                else{
                    return false;
                }
            };


            var _myCursorDelay = function (x, y){
                _gestionMask((x|0), (y|0));
            };

            var _gestionMask = function(x, y){
                var oldMask = mask;
                // On retire les entrées du maskAvatar
                if(maskAvatar && oldMask){
                    var dummy_om = oldMask.slice(0);
                    var iam=0;
                    var ml=oldMask.length;
                    for(iam; iam<ml; iam++){
                        // Si l'objet à masquer est déjà dans le maskAvatar
                        if(maskAvatar.indexOf(oldMask[iam]) !=-1){
                            //On le supprime du mask
                            dummy_om.splice(dummy_om.indexOf(oldMask[iam]), 1);
                        }
                    }
                    if(dummy_om.length == 0){
                        oldMask = false;
                    }
                    else{
                        oldMask = dummy_om;
                    }
                }
                mask = _getMask(x,y);
                // On retire les entrées du maskAvatar
                if(maskAvatar && mask){
                    var dummy_m = mask.slice(0);
                    var iam=0;
                    var ml=mask.length;
                    for(iam; iam<ml; iam++){
                        // Si l'objet à masquer est déjà dans le maskAvatar
                        if(maskAvatar.indexOf(mask[iam]) !=-1){
                            //On le supprime du mask
                            dummy_m.splice(dummy_m.indexOf(mask[iam]), 1);
                        }
                    }
                    if(dummy_m.length == 0){
                        mask = false;
                    }
                    else{
                        mask = dummy_m;
                    }
                }
                // Il y a un qqch devant le curseur
                if(mask){
                    // Il n'y avait pas de masque précédemment
                    if(!oldMask){
                        var im=0;
                        var ml=mask.length;
                        for(im; im<ml; im++){
                            var idobj = '#o_' + mask[im]+ ' img';
                            $(idobj).css("opacity", 0.15);
                        }
                    }
                    else if(oldMask.length>0){
                        var iom=0;
                        var oml=oldMask.length;
                        for( iom; iom<oml; iom++){
                            if(mask.indexOf(oldMask[iom])==-1 ){
                                var idobj = '#o_' + oldMask[iom]+ ' img';
                                $(idobj).css("opacity", 1);
                            }
                        }
                        var im=0;
                        var ml=mask.length;
                        for(im; im<ml; im++){
                            if(oldMask.indexOf(mask[im]) ==-1){
                                var idobj = '#o_' + mask[im]+ ' img';
                                $(idobj).css("opacity", 0.15);
                            }
                        }
                    }
                        
                }
                // Il n'y a rien dans le nouveau mask
                else if(!mask && oldMask){
                    var im=0;
                    var oml=oldMask.length;
                    for( im; im<oml; im ++){
                        var idobj =  '#o_' + oldMask[im]+ ' img';
                        $(idobj).css("opacity", 1);
                    }
                }    
            };

            var _getMask = function(x, y, z){
                if(typeof(z)=='undefined'){
                    z=0;
                }
                var dummy_mask = [];
                var oneMask_array = false;
                var oneMask = false;
                var c= 0;
                while ( z<=10){
                    var iz = 0;
                    for(iz; iz<=1; iz=iz+1)
                    {
                        var ix=0;
                        for(ix; ix<=1; ix=ix+1)
                        {
                            var iy=0;
                            for(iy; iy<=1; iy=iy+1)
                            {
                                if((ix==0 && iy==0 && iz==0)||(z+iz>10)||(x+c+ix)>map_size||(y+c+iy)>map_size){
                                    continue;
                                }
                                oneMask_array = _getItem(x+c+ix, y+c+iy,z+iz);
                                // Si un objet est dans la case
                                if(oneMask_array){
                                    omal = oneMask_array.length;
                                    for(var i=0; i<omal; i++){
                                        oneMask = oneMask_array[i]
                                        //Si l'objet est au sol et n'a pas de hauteur
                                        if(oneMask.hauteur == 0 && iz==0){
                                            continue;
                                        }
                                        // Si l'objet n'est pas présent dans le masque
                                        if(dummy_mask.indexOf(oneMask.btId) == -1){
                                            // On l'ajoute au tableau 
                                            dummy_mask.push(oneMask.btId);
                                        }
                                    }
                                    
                                }
                            }
                        }
                    }
                    z=z+1;
                    c=c+1;
                }
                if(dummy_mask.length >0){
                    return dummy_mask;
                }
                else{
                    return false;
                }
            };

            var _gestionMaskAvatar = function(x,y){
                var oldMaskAvatar = maskAvatar;
                maskAvatar = _getMaskAvatar(x,y);
                // Il y a un qqch devant le curseur
                if(maskAvatar){
                    // Il n'y avait pas de masque précédemment
                    if(!oldMaskAvatar){
                        var iml = maskAvatar.length;
                        for(var im=0; im<iml; im++){
                            var idobj = 'o_' + maskAvatar[im];
                            $('#' + idobj + ' img').css('opacity', .15);
                        }
                    }
                    else if(oldMaskAvatar.length>0){
                        var ioml = oldMaskAvatar.length;
                        for(var iom=0; iom<ioml; iom++){
                            if(maskAvatar.indexOf(oldMaskAvatar[iom])==-1 ){
                                var idobj = 'o_' + oldMaskAvatar[iom];
                                $('#' + idobj + ' img').css('opacity', 1);
                            }
                        }
                        var iml = maskAvatar.length;
                        for(var im=0; im<iml; im++){
                            if(oldMaskAvatar.indexOf(maskAvatar[im]) ==-1){
                                var idobj = 'o_' + maskAvatar[im];
                                $('#' + idobj + ' img').css('opacity', .15);
                            }
                        }
                    }
                        
                }
                // Il n'y a rien
                else if(!maskAvatar && oldMaskAvatar.length>0){
                    var ioml = oldMaskAvatar.length;
                    for(var im=0; im<ioml; im ++){
                        var idobj =  'o_' + oldMaskAvatar[im];
                        $('#' + idobj + ' img').css('opacity', 1);
                    }
                }   
            };

            var _getMaskAvatar = function(x, y, z){
                if(typeof(z)=='undefined'){
                    z=0;
                }
                var dummy_mask = [];
                var oneMask_array = false;
                var oneMask = false;
                var c= 0;
                while ( z<=10){
                    var iz = 0;
                    for(iz; iz<=3; iz=iz+1)
                    {
                        var ix=0;
                        for(ix; ix<=1; ix=ix+1)
                        {
                            var iy=0;
                            for(iy; iy<=1; iy=iy+1)
                            {
                                if((ix==0 && iy==0 && iz==0)||(z+iz>10)){
                                    continue;
                                }
                                oneMask_array = _getItem(x+c+ix, y+c+iy,z+iz);
                                // Si un objet est dans la case
                                if(oneMask_array){
                                    omal = oneMask_array.length;
                                    for(var i=0; i<omal; i++){
                                        oneMask = oneMask_array[i]
                                        //Si l'objet est au sol et n'a pas de hauteur
                                        if(oneMask.hauteur == 0 && iz==0){
                                            continue;
                                        }
                                        // Si l'objet n'est pas présent dans le masque
                                        if(dummy_mask.indexOf(oneMask.btId) == -1){
                                            // On l'ajoute au tableau 
                                            dummy_mask.push(oneMask.btId);
                                        }
                                    }
                                    
                                }
                            }
                        }
                    }
                    z=z+1;
                    c =c+1;
                }
                if(dummy_mask.length >0){
                    return dummy_mask;
                }
                else{
                    return false;
                }
            };

            var _avatarEvent = function (x, y, map_id){
                var objet = _getItem(x, y, 0);
                if(!!objet){
                    // Téléportation
                    if(objet[0].cat == "portes"){
                        var data = {
                            'x' : objet[0].x,
                            'y' : objet[0].y
                        };
                        if(token_tp==true){
                            socket.emit('teleportation_request', data);
                            token_tp = false;
                        }
                    }   
                }
                map_x = x;
                map_y = y;
            };

            var _teleport_request = function(pop){
                var form = document.forms["destination_choix"];
                //Récupération des données
                teleport = form['destination'].value;
                //fermeture du popup
                var wrapper = $(pop).parent().parent().parent();
                $().toastmessage('removeToast',wrapper,{'close':null});
                socket.emit('teleportation', {"id" : teleport});
            };

            var _send_avatar_info = function(incoming_player_id){
                var data = {
                    'map_x' : map_x,
                    'map_y' : map_y, 
                    'actual_x' : avatar_pos_x,
                    'actual_y' : avatar_pos_y,
                    'to' : incoming_player_id
                }
                socket.emit('avatar_info', data);
            }

            var _move_player = function(x, y){
                //Si l'avatar se déplace
                if(x!=map_x || y!=map_y){
                    console.log('movePlayer');
                    socket.emit('move_player', {'x' : x, 'y' : y});
                }
            }

            var _my_event = function (x, y){
                console.log('clic my_event');
            }
            /*############################
            *      Méthodes publiques    *
            ############################*/

            self.getObjet = function (x, y, z){
                return _getObjet(x, y, z);
            };

            self.getItem = function(x, y, z){
                return _getItem(x, y, z);
            };

            self.getItemZone = function (x, y, z, zone, zonelength){
                return _getItemZone(x, y, z, zone, zonelength);
            };

            self.myCursorDelay = function (x, y){
                return _myCursorDelay(x, y);
            };

            self.teleport_request = function(pop){
                return _teleport_request(pop);
            };

            return self;
        }
    })(jQuery, window, document);

$(document).ready(function()
{
    bt = $('#map-index').mapWalker();
});