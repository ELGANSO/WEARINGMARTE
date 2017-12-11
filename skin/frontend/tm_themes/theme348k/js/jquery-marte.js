jQuery( document ).ready(function() {
	error_movil_web();
	footer_movil_web();
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
	jQuery(".skip-cart, #bag-active, .minicart-wrapper .close, .remove").click(function(){
		if(jQuery("#bag-active").css('display') !== 'none') {
			
			jQuery("#bag-active").hide();

		}else{
			if(jQuery(window).width() >= 1024){
				jQuery("#bagcount").text(jQuery(".count_t").text());
				jQuery("#bag-active").show();
			}
			
		}
	});

	//Compruebo si ha añadido algo al carrito
	if(jQuery(".messages .success-msg li span").length && !jQuery("#message-popup").length){
		jQuery(".count_t").click();
	}

	//Actualizar cantidad del carrito
	jQuery("#cart-sidebar .remove").click(function(){
	
		if(window.confirm){
			var old = parseInt(jQuery(".count_t #qty").text());
			old -= parseInt(jQuery(this).parent().find('.qty').val());
			jQuery(".count_t #qty").text(old);
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

