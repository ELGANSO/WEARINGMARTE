jQuery( document ).ready(function() {
	error_movil_web();
	footer_movil_web();
	//resolveInstagram();

	jQuery(".youama-window-outside .close").click(function() {
		jQuery("#header-account2").css("display","none");
	});

	//Menu lateral version escritorio
	jQuery("#header-navBtn, #menu-active").click(function(){
		if(jQuery("#header-nav").css('display') !== 'none') {
			
			jQuery("#header-nav").hide("slide");
			jQuery("#header, .main-container").css("cssText", "margin-left: 0 !important;");
			jQuery("#menu-active").hide();

		}else{

			jQuery("#header-nav").show("slide","fast");
			jQuery("#header, .main-container").css("cssText", "margin-left: 20% !important;");
			jQuery("#menu-active").show();
		}
	});
	//fondo blanco al abrir carrito
	jQuery(".skip-cart, #bag-active, .skip-link, .close, .remove, .skip-active,.btn-cart").click(function(){
		
		if(jQuery("#bag-active").css('display') !== 'none' || jQuery('body').hasClass('stop-scrolling') ) {

			jQuery("#bag-active").hide();
			jQuery('body').removeClass('stop-scrolling');

		}else{
			if(jQuery(window).width() >= 1024){
				jQuery("#bagcount").text(jQuery(".count_t").text());
					if(jQuery("#header-cart").css("display") != 'none'){
						jQuery("#bag-active").show();
					}
			}
			jQuery('body').addClass('stop-scrolling');
			
		}
	});

	jQuery(".modal-content .close").click(function(){
		jQuery('body').removeClass('stop-scrolling');
	});

	jQuery(".page-header-container .skip-link .iconMenu").click(function(){
		jQuery("#bag-active").hide();
	});

	jQuery(" .remove,#bag-active,#header-account, #menu-active").click(function(){
		jQuery('body').removeClass('stop-scrolling');

	});
	if(jQuery("#messages_product_view .messages .notice-msg li span").length){
		jQuery("#messages_product_view .messages .notice-msg li span").hide();
		jQuery("#messages_product_view .messages .notice-msg li").hide();
		jQuery(".btnsizes").hover(function() {
		  jQuery(this).css("border-color","red");
		  jQuery(this).css("color","red");
		});
		jQuery(".btnsizes").addClass("error");
		jQuery(".btnsizes, #tallas").css("border-color","red");
        jQuery("#tallas").css("border-top","none");
		jQuery(".btnsizes").css("color","red");

        jQuery(".sizes").addClass("error");
        //jQuery(".sizes").css("border","1px solid red");
        jQuery(".sizes li").css("color","red");

	}

	jQuery(".sizes li").click(function(){
        jQuery(".sizes li").css("color","#333");
        jQuery(".sizes").removeClass("error");
        jQuery(".btnsizes").removeClass("error");
        jQuery(".btnsizes, #tallas").css("border-color","#333");
        jQuery("#tallas").css("border-top","none");
        jQuery(".btnsizes").css("color","#333");
	});

	//Compruebo si ha a�adido algo al carrito
	if(jQuery(".messages .success-msg li span").length && !jQuery("#message-popup").length){
		if(jQuery(window).width() >= 1024){
			jQuery(".count_t").click();
		}else{
			jQuery(".skip-cart").click();
			jQuery('body').addClass('stop-scrolling');
		}
	}
	jQuery(".youama-window-outside .close").click(function(){
		jQuery('body').removeClass('stop-scrolling');
	});

	//Actualizar cantidad del carrito
	jQuery("#cart-sidebar .remove").click(function(){
	
		if(window.confirm){
			var old = parseInt(jQuery(".count_t #qty").text());
			old -= parseInt(jQuery(this).parent().find('.qty').val());
			jQuery(this).parent().parent().parent().parent().remove();
			jQuery(".count_t #qty").text(old);
			jQuery(".mobile .count").text("("+old+")");
			jQuery("#bag-active").hide();
		}
	});

	jQuery("#guia-tallas-1").click(function(){
				jQuery(".bs-wizard-step").removeClass('active');
				jQuery(".bs-wizard-step").removeClass('disabled');
				jQuery(".bs-wizard-step").removeClass('complete');
				jQuery("#guia-tallas-1").addClass('active');
				jQuery("#guia-tallas-2").addClass('disabled');
				jQuery("#guia-tallas-3").addClass('disabled');
				jQuery("#guia-tallas-4").addClass('disabled');
				jQuery("#guia-tallas-5").addClass('disabled');
				jQuery(".bs-wizard-stepnum").removeClass('disabled');
				jQuery(".bs-wizard-stepnum").removeClass('active');
				jQuery("#stepnum-1").addClass('active');
				jQuery("#stepnum-2").addClass('disabled');
				jQuery("#stepnum-3").addClass('disabled');
				jQuery("#stepnum-4").addClass('disabled');
				jQuery("#stepnum-5").addClass('disabled');
				jQuery(".guia-tallas-body").hide();
				jQuery("#guia-tallas-body-1").show();
			});
			jQuery("#guia-tallas-2").click(function(){
				jQuery(".bs-wizard-step").removeClass('active');
				jQuery(".bs-wizard-step").removeClass('disabled');
				jQuery(".bs-wizard-step").removeClass('complete');
				jQuery("#guia-tallas-1").addClass('complete');
				jQuery("#guia-tallas-2").addClass('active');
				jQuery("#guia-tallas-3").addClass('disabled');
				jQuery("#guia-tallas-4").addClass('disabled');
				jQuery("#guia-tallas-5").addClass('disabled');
				jQuery(".bs-wizard-stepnum").removeClass('disabled');
				jQuery(".bs-wizard-stepnum").removeClass('active');
				jQuery("#stepnum-1").addClass('active');
				jQuery("#stepnum-2").addClass('active');
				jQuery("#stepnum-3").addClass('disabled');
				jQuery("#stepnum-4").addClass('disabled');
				jQuery("#stepnum-5").addClass('disabled');
				jQuery(".guia-tallas-body").hide();
				jQuery("#guia-tallas-body-2").show();
			});

			jQuery("#guia-tallas-3").click(function(){
				jQuery(".bs-wizard-step").removeClass('active');
				jQuery(".bs-wizard-step").removeClass('disabled');
				jQuery(".bs-wizard-step").removeClass('complete');
				jQuery("#guia-tallas-1").addClass('complete');
				jQuery("#guia-tallas-2").addClass('complete');
				jQuery("#guia-tallas-3").addClass('active');
				jQuery("#guia-tallas-4").addClass('disabled');
				jQuery("#guia-tallas-5").addClass('disabled');
				jQuery(".bs-wizard-stepnum").removeClass('disabled');
				jQuery(".bs-wizard-stepnum").removeClass('active');
				jQuery("#stepnum-1").addClass('active');
				jQuery("#stepnum-2").addClass('active');
				jQuery("#stepnum-3").addClass('active');
				jQuery("#stepnum-4").addClass('disabled');
				jQuery("#stepnum-5").addClass('disabled');
				jQuery(".guia-tallas-body").hide();
				jQuery("#guia-tallas-body-3").show();
			});


			jQuery("#guia-tallas-4").click(function(){
				jQuery(".bs-wizard-step").removeClass('active');
				jQuery(".bs-wizard-step").removeClass('disabled');
				jQuery(".bs-wizard-step").removeClass('complete');
				jQuery("#guia-tallas-1").addClass('complete');
				jQuery("#guia-tallas-2").addClass('complete');
				jQuery("#guia-tallas-3").addClass('complete');
				jQuery("#guia-tallas-4").addClass('active');
				jQuery("#guia-tallas-5").addClass('disabled');
				jQuery(".bs-wizard-stepnum").removeClass('disabled');
				jQuery(".bs-wizard-stepnum").removeClass('active');
				jQuery("#stepnum-1").addClass('active');
				jQuery("#stepnum-2").addClass('active');
				jQuery("#stepnum-3").addClass('active');
				jQuery("#stepnum-4").addClass('active');
				jQuery("#stepnum-5").addClass('disabled');
				jQuery(".guia-tallas-body").hide();
				jQuery("#guia-tallas-body-4").show();
			});


			jQuery("#guia-tallas-5").click(function(){
				jQuery(".bs-wizard-step").removeClass('active');
				jQuery(".bs-wizard-step").removeClass('disabled');
				jQuery(".bs-wizard-step").removeClass('complete');
				jQuery("#guia-tallas-1").addClass('complete');
				jQuery("#guia-tallas-2").addClass('complete');
				jQuery("#guia-tallas-3").addClass('complete');
				jQuery("#guia-tallas-4").addClass('complete');
				jQuery("#guia-tallas-5").addClass('active');
				jQuery(".bs-wizard-stepnum").removeClass('disabled');
				jQuery(".bs-wizard-stepnum").removeClass('active');
				jQuery("#stepnum-1").addClass('active');
				jQuery("#stepnum-2").addClass('active');
				jQuery("#stepnum-3").addClass('active');
				jQuery("#stepnum-4").addClass('active');
				jQuery("#stepnum-5").addClass('active');
				jQuery(".guia-tallas-body").hide();
				jQuery("#guia-tallas-body-5").show();
			});

		jQuery(".card-header a").click(function(){
			jQuery(".collapse").removeClass("in");
			jQuery(this).parent().find(".collapse").addClass("in");
		});

    jQuery(".btnsizes").click(function () {

    	if(!jQuery(this).css("border-bottom").includes('none'))
			jQuery(this).css("border-bottom","none");
    	else
            jQuery(this).css("border","1px solid #333");
    });

});

function footer_movil_web() {
	var ventana_ancho = jQuery(window).width(); 	
	if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) && ventana_ancho<='768'){
	    jQuery('#contenido-suscribe').appendTo('#col-1-footer');
		jQuery('#help-me').appendTo('#col-2-footer');
	} 
}

function abrirlogin() {
	jQuery("#header-account2").css("display","block");
}

function error_movil_web() {
	var ventana_ancho = jQuery(window).width(); 	
	if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) && ventana_ancho<='768'){
	    jQuery('#imagen-1').appendTo('#col-1-error');
		jQuery('#imagen-2').appendTo('#col-2-error');
          jQuery('#texto').appendTo('#col-3-error');
	} 

}

function resolveInstagram()
	{
		var ventana_ancho = jQuery(window).width();
		var agent = navigator.userAgent.split(" "); 
		
		if( agent.indexOf("Instagram") != -1 && /iPhone|iPad|iPod/i.test(navigator.userAgent))
		{
		   	jQuery('#product-info').css('padding-bottom','45px');
	    	jQuery('.minicart-wrapper .block-content').css('bottom','80px');
	    	jQuery('.minicart-actions').css('bottom','20px');
	    	jQuery('.collapse div').css('margin-bottom','30px');
		}
	}


