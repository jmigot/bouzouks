//plugin d'affichage des messages

/*
 * Copyright 2010 akquinet
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

(function($)
{
	var settings = {
				inEffect: 			{opacity: 'show'},	// in effect
				inEffectDuration: 	600,				// in effect duration in miliseconds
				stayTime: 			3000,				// time in miliseconds before the item has to disappear
				text: 				'',					// content of the item. Might be a string or a jQuery object. Be aware that any jQuery object which is acting as a message will be deleted when the toast is fading away.
				sticky: 			false,				// should the toast item sticky or not?
				type: 				'notice', 			// notice, warning, error, success
                position:           'middle-center',        // top-left, top-center, top-right, middle-left, middle-center, middle-right ... Position of the toast container holding different toast. Position can be set only once at the very first call, changing the position after the first call does nothing
                closeText:          '',                 // text which will be shown as close button, set to '' when you want to introduce an image via css
                close:              null                // callback function when the toastmessage is closed
            };

    var methods = {
        init : function(options)
		{
			if (options) {
                $.extend( settings, options );
            }
		},

        showToast : function(options)
		{
			var localSettings = {};
            $.extend(localSettings, settings, options);

			// declare variables
            var toastWrapAll, toastItemOuter, toastItemInner, toastItemClose, toastItemImage;

			toastWrapAll	= (!$('.toast-container').length) ? $('<div></div>').addClass('toast-container').addClass('toast-position-' + localSettings.position).appendTo('body') : $('.toast-container');
			toastItemOuter	= $('<div></div>').addClass('toast-item-wrapper');
			toastItemInner	= $('<div></div>').hide().addClass('toast-item toast-type-' + localSettings.type).appendTo(toastWrapAll).html($('<p>').append (localSettings.text)).animate(localSettings.inEffect, localSettings.inEffectDuration).wrap(toastItemOuter);
			toastItemClose	= $('<div></div>').addClass('toast-item-close').prependTo(toastItemInner).html(localSettings.closeText).click(function() { $().toastmessage('removeToast',toastItemInner, localSettings) });
			toastItemImage  = $('<div></div>').addClass('toast-item-image').addClass('toast-item-image-' + localSettings.type).prependTo(toastItemInner);

            if(navigator.userAgent.match(/MSIE 6/i))
			{
		    	toastWrapAll.css({top: document.documentElement.scrollTop});
		    }

			if(!localSettings.sticky)
			{
				setTimeout(function()
				{
					$().toastmessage('removeToast', toastItemInner, localSettings);
				},
				localSettings.stayTime);
			}
            return toastItemInner;
		},

        showNoticeToast : function (message)
        {
            var options = {text : message, type : 'notice'};
            return $().toastmessage('showToast', options);
        },

        showSuccessToast : function (message)
        {
            var options = {text : message, type : 'success'};
            return $().toastmessage('showToast', options);
        },

        showErrorToast : function (message)
        {
            var options = {text : message, type : 'error'};
            return $().toastmessage('showToast', options);
        },

        showWarningToast : function (message)
        {
            var options = {text : message, type : 'warning'};
            return $().toastmessage('showToast', options);
        },

		removeToast: function(obj, options)
		{
			obj.animate({opacity: '0'}, 600, function()
			{
				obj.parent().animate({height: '0px'}, 300, function()
				{
					obj.parent().remove();
				});
			});
            // callback
            if (options && options.close !== null)
            {
                options.close();
            }
		}
	};

    $.fn.toastmessage = function( method ) {

        // Method calling logic
        if ( methods[method] ) {
          return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
          return methods.init.apply( this, arguments );
        } else {
          $.error( 'Method ' +  method + ' does not exist on jQuery.toastmessage' );
        }
    };

})(jQuery);

function display_type(type){
	if(lock_menu == false){
		$('.type.vlux_display_on').toggleClass('vlux_display_on').toggleClass('vlux_display_off');
		$('.cat.vlux_display_on').toggleClass('vlux_display_on').toggleClass('vlux_display_off');
		if(cat_slider){
			cat_slider = false;
		}
		$('#vlux_type_'+type).toggleClass('vlux_display_off').toggleClass('vlux_display_on');
		if(Choix != 0){
			reset_cursor();
		}
	}
}


function display_cat(cat){
	if(lock_menu == false){
		$('.cat.vlux_display_on').toggleClass('vlux_display_on').toggleClass('vlux_display_off');
		car_slider = false;
		$('#vlux_'+cat).toggleClass('vlux_display_off').toggleClass('vlux_display_on');
		cat_slider = new slider ($('#vlux_'+cat));
		if(Choix != 0){
			reset_cursor();
		}
	}
}



var slider = function(id){
	var self = this;
	this.div = $(id);
	this.widthCache = 754;
	//this.widthCache = this.div.width();
	this.slider = this.div.find(".vlux_slider");
	this.widthSlider = 0;
	this.div.find('a').each(function(){
		self.widthSlider += $(this).width();
		self.widthSlider += parseInt($(this).css("padding-left"));
		self.widthSlider += parseInt($(this).css("padding-right"));
		self.widthSlider += parseInt($(this).css("margin-left"));
		self.widthSlider += parseInt($(this).css("margin-right"));
	});
	this.stepLength = this.widthCache / 3;
	this.nbStep = Math.ceil(this.widthSlider/this.stepLength - this.widthCache/this.stepLength);
	this.step = 0;

	this.prec = this.div.find(".prec");
	this.suiv = this.div.find(".suiv");
	if(this.nbStep <=0){
		this.suiv.css('display','none');
	}

	this.prec.click(function(){
		if (self.step > 0){
			self.step --;
			self.slider.animate(
				{ left : -self.step*self.stepLength }, 500);
		}
		if(self.step == 0){
			self.prec.css( 'display', 'none');
		}
		if(self.step < self.nbStep){
			self.suiv.css('display', 'block');
		}
	});

	this.suiv.click(function(){
		if (self.step <= self.nbStep){
			self.step ++;
			self.slider.animate(
				{ left : -self.step*self.stepLength }, 500);

		}

		if(self.step == self.nbStep){
			self.suiv.css('display','none');
		}

		if(self.step>0){
			self.prec.css('display', 'block');
		}
	});
};