if (typeof console == "undefined" || typeof console.log == "undefined" || typeof console.debug == "undefined") var console = { log: function() {}, debug: function() {} }; 
if (typeof jQuery !== 'undefined') {
	console.debug("JQuery found!!!");
	(function($) {
		$('#spinner').ajaxStart(function() {
			$(this).fadeIn();
		}).ajaxStop(function() {
			$(this).fadeOut();
		});
	})(jQuery);
} else {
	console.debug("JQuery not found!!!");
}
$(document).ready(function() {
	Tecnotek.init();
});
var Tecnotek = {
		module : "",
		imagesURL : "",
                assetsURL : "",
		isIe: false,
		rowsCounter: 0,
		companiesCounter: 0,
		ftpCounter: 0,
		session : {},
		logout:function(url){
			location.href= url;
		},
		init : function() {
			var module = Tecnotek.module;
			console.debug("Module: " + module)
			if (module) {
				switch (module) {
				case "administratorList":
					Tecnotek.AdministratorList.init();
					break;
                                case "showAdministrador":
					Tecnotek.AdministratorShow.init();
					break;
				default:
					break;
				}
			}
			Tecnotek.UI.init();
			
		},
		UI : {
			translates : {},
			urls : {},
			intervals : {},
			init : function() {
				/*Tecnotek.Setup.setWaterMark();
				if($.browser.msie){
					Tecnotek.isIe = true;
				}*/
				Tecnotek.UI.initLocales();
				/*$(".tooltip").tooltip({
					showURL:false,
					top: -30});*/
			},
			initLocales: function(){
				/*$("#localeSelector").click(function(){
					//$("#localesLayer").slideUp("slow");
					$( "#localesLayer" ).toggle( "slide", { direction: "down" }, 500 );
				});
				$(".localeLink").click(function(){
					var locale = $(this).attr("lang");
					var url = location.href;

					url = url.split( '?' )[0];
					location.href = url+"?lang="+locale;
				});*/
			},
			btnAccept : "Accept",
			initModal : function(targetDiv, buttonsOpts) {
				$(targetDiv).dialog({
					title : '',
					dialogClass : 'alert',
					closeText : '',
					show : 'highlight',
					hide : 'highlight',
					autoOpen : false,
					bgiframe : true,
					modal : true,
					buttons : buttonsOpts
				});
			},
			closeModal : function(targetDiv) {
				$(targetDiv).dialog('close');
			},
			addModalEvent: function(targetDiv, eventName, toDo){
				
				$(targetDiv).bind( eventName, function(){
					//console.log("closing");
					toDo();					
				});
			},
			modal : function(targetDiv, title, htmlSelector, html, isNewOpen,
					buttonsOpts, width, height) {
				
				Tecnotek.UI.initModal(targetDiv, buttonsOpts);
				/* Assign div content */
				if (html != '' && htmlSelector != '') {
					$(htmlSelector).html(html);
				} else if (html != '') {
					$(targetDiv).html(html);
				}

				/* Assign title */
				if (title != '') {
					$(targetDiv).dialog('option', 'title', title);
				}

				if (width == 0) {
					width = 280;
				}

				$(targetDiv).dialog('option', 'width', width);
				$(targetDiv).dialog('option', 'closeOnEscape', true);

				if (height != 0) {
					$(targetDiv).dialog('option', 'height', height);
				}

				// true if the modal is not open/ flase if the modal is already open
				// with different content
				if (isNewOpen) {

					$(targetDiv).dialog('open');
				}

				$(targetDiv).css("z-index", "5000");
			},
			validateForm : function(formSelector) {
				//alert("validating form");
				var result = $(formSelector).validationEngine('validate');
				//alert("result "+result);
				return result;
			}
		},
		AdministratorList : {
			init : function() {
				Tecnotek.AdministratorList.initComponents();
				Tecnotek.AdministratorList.initButtons();
			},
			initComponents : function() {
			},
			initButtons : function() {
				$('.userRow').dblclick(function(event){
                    location.href = Tecnotek.UI.urls["show"] + "/" + $(this).attr("rel");
				});
			},
			submit : function() {
				//$("#frmCreateAccount").submit();
			}
		},
		AdministratorShow : {
			init : function() {
				Tecnotek.AdministratorShow.initComponents();
				Tecnotek.AdministratorShow.initButtons();
			},
			initComponents : function() {
			},
			initButtons : function() {
				$('#btnEditar').click(function(event){
                                    console.debug("editar");
				});
                                $('#btnCambiarPass').click(function(event){
                                    console.debug("Cambiar password");
                                    $("#buttonsContainer").hide();
                                    $("#changePasswordContainer").fadeIn('slow', function() {
                                        // Animation complete
                                    });
				});
                                $('#btnEliminar').click(function(event){
                                    console.debug("eliminar");
				});
                                $('#btnActualizarPassword').click(function(event){
                                    console.debug("btnActualizarPassword");
                                    $("#changePasswordContainer").hide();
                                    $("#buttonsContainer").fadeIn('slow', function() {
                                        // Animation complete
                                    });
				});
			},
			submit : function() {
				//$("#frmCreateAccount").submit();
			}
		}
	};
