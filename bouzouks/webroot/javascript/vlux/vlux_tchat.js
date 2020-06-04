;(function($, window, document, undefined)
    {
        $.fn.vluxTchat = function (params){   

        	var self = this;
        	var element = $(this);

        	var socket;

        	var div;
        	var output;
        	var input;
        	var inputMessage;
        	var list_chan;
        	var chan_actif;
        	var list_connectes;
        	var obj_list_connectes;
        	var chan_actif_id;

        	var nb_chan_open = 1;
        	var max_chan_open = 4;

        	var auth_level;

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

            var getSocket = function(){
            	var host = site_url.replace('http', 'ws');
                host = host.substring(0, host.length-1)+':8080';
                // Connexion au serveur Vlux
                return new io.connect(host,{
                    'reconnection' : false
                });
            };

            var _vlux_tchat = function(){
                div = $('#vlux_tchat');
                output = $('#vlux_tchat_output');
                input = $('#input_tchat');
                inputMessage = $('#tchat_message');
                list_chan = $('#list_chan');
                chan_actif = $('.chan_actif');
                chan_actif_id = 'global';
                obj_list_connectes = $('#list_connectes');
                $('#output_chan_global').css('display', 'inline-block');
                // Gestion des onglets
                list_chan.find('li').each(function(){
                    $(this).bind('click', function(event){
                        event.preventDefault();
                        var chan_id = $(this).attr('id');
                        if(chan_actif.attr('id')!= chan_id){
                            var old_chan_id = chan_actif.attr('id');
                            chan_actif.toggleClass('chan_actif');
                            $('#output_'+old_chan_id).css('display', 'none');
                            chan_actif = $(this);
                            chan_actif.toggleClass('chan_actif');
                            $('#output_'+chan_id).css('display', 'inline-block');
                        }
                		chan_actif_id = 'global';
                        _display_connectes();
                    });
                });
                // Envoie du message
                input.find('#tchat_send').bind('click', function(event){
                    event.preventDefault();
                    _sendMessage();
                });

                input.keydown(function(event){
                    if(event.wich === 13){
                        event.preventDefault();
                        _sendMessage();
                    }
                });

                input.find('#tchat_suppr_msg').bind('click', function(event){
                    event.preventDefault();
                    _delete_msg();
                });

                $('.join_chan').each(function(){
                	var obj_id = $(this).attr('id');
                	var map_nom = $('#chan_nom_'+obj_id).text();
                	$(this).bind('click', function(e){
                		e.preventDefault;
                		_get_chan(obj_id, map_nom);
                	});
                });


            };

            init();

            /*############################
            *           Le tchat         *
            ############################*/

            reload = function (old_map_id, map_id){
                // Suppresion du chan de map précédent
                $('#output_chan_map_room_'+old_map_id).remove();
                // Configuration du nouveau chan de map
                $('#chan_map_room_'+old_map_id).attr('id', 'chan_map_room_'+map_id);
                output.append('<li id="output_chan_map_room_'+map_id+'"></li>');
                console.log(chan_actif.attr('id'));
                if(chan_actif.attr('id') == 'chan_map_room_'+map_id){
                    $('#output_chan_map_room_'+map_id).css('display', 'inline-block');
                }
            }

            onmessage = function(data){
                var chan = $('#output_'+data.chan);
                var supprimer = '';
                if(data.message_id>0){
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
                    chan.animate({ scrollTop : chan.prop('scrollHeight')- chan.height()}, 10);
                }
                //On supprime les messages trop ancien de l'affichage
                var nb_child = $(chan).children().length;
                for(var i=0; i<nb_child-400; i++){
                    $('#output_'+data.chan+' p').first().remove();
                }
            };

            _sendMessage = function(){
                console.log('sendMessage clicked !');
                var message = inputMessage.val();
                inputMessage.val('');
                var data = {
                    "chan_id" : chan_actif.attr('id'),
                    "message_content" : message
                };
                socket.emit('tchat_message', data);
                console.log(message);
            };

            _delete_msg = function(){
                if ( ! confirm('Veux-tu vraiment supprimer les messages sélectionnés ?'))
                    return;

                var messages_ids = [];
                $('.vlux-tchat-message').each(function(cle, val){
                	var input = $(this).find('input[type=checkbox]');
                	if (input.is(':checked')){
                		messages_ids.push(input.attr('value'));
                	}
                });
                console.log(messages_ids);
                socket.emit('suppr_msg', {'messages_ids' : JSON.stringify(messages_ids)});
            };

            _suppr_messages = function (ids){
            	console.log(ids);
            	$('.vlux-tchat-message').each(function(){
            		var id_message = $(this).find('input[type=checkbox]').attr('value');
            		if(ids.indexOf(id_message) != -1){
            			$(this).remove();
            		}
            	});
            };

            _get_chan = function(map_id, map_nom){
            	if(nb_chan_open == max_chan_open){
            		$().toastmessage('showNoticeToast', "Le nombre maximum d'onglets ouverts est de "+max_chan_open+'.');
            		return;
            	}
            	// On ajoute l'onglet
            	output.append('<li id="output_chan_map_room_'+map_id+'"></li>');
            	list_chan.append('<li id="chan_map_room_'+map_id+'" >'+map_nom+'<img class="tab_closer"  src="'+site_url+'webroot/images/map/interface/tab_close.png" alt="fermer le chan '+map_nom+'" title="fermer le chan'+map_nom+'" width="12" height="12"/></li>');
            	// Gestion onglet
            	var obj_chan = $('#chan_map_room_'+map_id);
                var chan_id = $(obj_chan).attr('id');
            	obj_chan.bind('click', function(event){
                        event.preventDefault();
                        if(chan_actif.attr('id')!= chan_id){
                            var old_chan_id = chan_actif.attr('id');
                            chan_actif.toggleClass('chan_actif');
                            $('#output_'+old_chan_id).css('display', 'none');
                            chan_actif = $(this);
                            chan_actif.toggleClass('chan_actif');
                            $('#output_'+chan_id).css('display', 'inline-block');
                        }
                   		chan_actif_id = map_id;
            			_display_connectes();
                    });
            	obj_chan.find('img').bind('click', function(){
            		_close_tab(chan_id);
            	});
            	nb_chan_open++;
            	// On bascule dessus
            	obj_chan.trigger('click');
            	// On va chercher l'historique du chan
            	socket.emit('get_histo', {"chan_id" : map_id});

            };

            _close_tab = function(chan_id){
            	// On bascule sur le global
            	$('#chan_global').trigger('click');
            	$('#'+chan_id).remove();
            	$('#output_'+chan_id).remove();
            	nb_chan_open--;
            }

            _display_connectes = function(){
            	obj_list_connectes.empty();
            	for(var j in list_connectes){
            		if(chan_actif_id =='global' || chan_actif_id==list_connectes[j].map_id){
            			obj_list_connectes.append('<li id="joueur_'+list_connectes[j].id+'">'+list_connectes[j].pseudo+'</li>');
            		}
            		
            	}
            }

            /*############################
            *           La socket        *
            ############################*/

            // Définition des écouteurs de io.socket
            socket.on('connect', function(){
                socket.emit('authentifier',{'id': id, 'token':websocket_auth, 'mode': 10})
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
                console.log(socketEvent);
                this[socketEvent](p.data[0]);
            };

            socket.tchat_connecte = function(data){
            	console.log('Connexion au tchat.');
            	for(var i in data){
            		$('#chan_' + data[i].chan_id).text(data[i].connectes);
            	}
            	list_connectes = data.list_connectes;
            	_display_connectes();

            	//Demande de la liste des connectés et de l'histo du chan global
            	socket.emit('get_histo', {"chan" : "global"});
            }

            socket.tchat = function(data){
            	onmessage(data);
            }

            socket.incoming_player = function(data){
            	var chan =$("#chan_" + data.chan_id);
            	var nb = ((chan.text())|0)+1;
            	chan.text(nb);
            	list_connectes.push({'map_id': data.chan_id, 'pseudo' : data.pseudo, 'id' : data.id});
            	_display_connectes();
            }

            socket.outcoming_player = function(data){
            	var chan =$("#chan_" + data.chan_id);
            	var nb = ((chan.text())|0)-1;
            	if(nb<=0)nb=0;
            	chan.text(nb);
            	// Mise à jour de la liste des connectés
            	console.log(list_connectes);
            	console.log(data);
            	for(var i in list_connectes){
            		if(list_connectes[i].id == data.id){
            			list_connectes.splice(i, 1);
            		}
            	}
            	_display_connectes();
            }

            socket.delete_msg_confirm = function(data){
            	_suppr_messages(data);
            }

        	return self;
        }
    })(jQuery, window, document);

$(document).ready(function()
{
    v_tchat = $().vluxTchat();
});