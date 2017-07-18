jQuery( document ).ready(function() {
	footer_movil_web();
	jQuery(".youama-window-outside .close").click(function() {
	jQuery("#header-account2").css("display","none");
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

