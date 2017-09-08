jQuery( document ).ready(function() {
	error_movil_web();
	footer_movil_web();
	jQuery(".youama-window-outside .close").click(function() {
		jQuery("#header-account2").css("display","none");
	});

	jQuery("#open_coupon").click(function() {
		jQuery( "#cupones" ).slideToggle( function() {
    		// Animation complete.
  		});
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

