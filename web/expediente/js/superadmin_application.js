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
        updateFail: false,
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
                case "coordinadorList":
                case "profesorList":
                case "entityList":
                    Tecnotek.AdministratorList.init(); break;
                case "showAdministrador":
                case "showCoordinador":
                case "showProfesor":
                    Tecnotek.AdministratorShow.init(); break;
                case "showEntity":
                    Tecnotek.EntityShow.init(); break;
                case "showClub":
                    Tecnotek.EntityShow.init();
                    Tecnotek.ClubShow.init();
                    break;
                case "showStudent":
                    Tecnotek.EntityShow.init();
                    Tecnotek.StudentShow.init();
                    break;
                case "ticketsIndex":
                    Tecnotek.Tickets.init();
                    break;
                default:
					break;
				}
			}
			Tecnotek.UI.init();
			
		},
        ajaxCall : function(url, params, succedFunction, errorFunction, showSpinner) {
            var request = $.ajax({
                url: url,
                type: "POST",
                data: params,
                dataType: "json"
            });

            request.done(succedFunction);

            request.fail(errorFunction);
        },
        showInfoMessage : function(message, showAlert, divId, showDiv) {
            if ( showAlert ) {
                alert(message);
            }
            if ( showDiv ) {
                $("#" + divId).html(message);
            }
        },
        showErrorMessage : function(message, showAlert, divId, showDiv) {
            if ( showAlert ) {
                alert(message);
            }
            if ( showDiv ) {
                $("#" + divId).html(message);
            }
        },
        showConfirmationQuestion : function(message) {
            return confirm(message);
        },
		UI : {
			translates : {},
			urls : {},
            vars : {},
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
				$('.viewButton').click(function(event){
                    event.preventDefault();
                    location.href = Tecnotek.UI.urls["show"] + "/" + $(this).attr("rel");
				});
                $('.editButton').click(function(event){
                    event.preventDefault();
                    location.href = Tecnotek.UI.urls["edit"] + "/" + $(this).attr("rel");
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
                    $("#firstname").val($("#labelFirstname").html());
                    $("#lastname").val($("#labelLastname").html());
                    $("#username").val($("#labelUsername").html());
                    $("#email").val($("#labelEmail").html());
                    if($("#labelActive").html() == "Activo") {
                        $("#active").attr('checked', true);
                    } else {
                        $("#active").attr('checked', false);
                    }
                    $("#showContainer").hide();
                    $("#editContainer").fadeIn('slow', function() {});
				});
                $('#btnCancelEdit').click(function(event){
                    $("#editContainer").hide();
                    $("#showContainer").fadeIn('slow', function() {});
                });
                $('#btnCambiarPass').click(function(event){
                    $("#buttonsContainer").hide();
                    $("#changePasswordContainer").fadeIn('slow', function() {});
				});
                $('#btnEliminar').click(function(event){
                    if (Tecnotek.showConfirmationQuestion(Tecnotek.UI.translates["confirmDelete"])){
                        location.href = Tecnotek.UI.urls["deleteAdminURL"];
                    }
				});
                $('#btnCancelarPassword').click(function(event){
                    $("#changePasswordContainer").hide();
                    $("#buttonsContainer").fadeIn('slow', function() {});
                });
                $('#btnActualizarPassword').click(function(event){
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["changePasswordURL"],
                        {newPassword: $("#newPassword").val(), confirmPassword: $("#confirmPassword").val(),
                        userId: $("#userId").val()},
                        function(data){
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message,true, "", false);
                            } else {
                                $("#changePasswordContainer").hide();
                                $("#buttonsContainer").fadeIn('slow', function() {});
                                Tecnotek.showInfoMessage(data.message,true, "", false);
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.showErrorMessage("Error updating password: " + textStatus + ".",
                                true, "", false);
                        }, true);
				});
			},
			submit : function() {
				//$("#frmCreateAccount").submit();
			}
		},
        EntityShow : {
            init : function() {
                Tecnotek.EntityShow.initComponents();
                Tecnotek.EntityShow.initButtons();
            },
            initComponents : function() {
            },
            initButtons : function() {
                $('#btnEliminar').click(function(event){
                    if (Tecnotek.showConfirmationQuestion(Tecnotek.UI.translates["confirmDelete"])){
                        location.href = Tecnotek.UI.urls["deleteURL"];
                    }
                });
            }
        },
        ClubShow : {
            init : function() {
                $('#generalTab').click(function(event){
                    $('#studentsSection').hide();
                    $('#generalSection').show();
                    $('#generalTab').toggleClass("tab-current");
                    $('#studentsTab').toggleClass("tab-current");
                });
                $('#studentsTab').click(function(event){
                    $('#generalSection').hide();
                    $('#studentsSection').show();
                    $('#generalTab').toggleClass("tab-current");
                    $('#studentsTab').toggleClass("tab-current");
                });

                $('#searchBox').keyup(function(event){
                    event.preventDefault();
                    if($(this).val().length == 0) {
                        $('#suggestions').fadeOut(); // Hide the suggestions box
                    } else {
                        Tecnotek.ajaxCall(Tecnotek.UI.urls["getStudentsURL"],
                            {text: $(this).val(), clubId: Tecnotek.UI.vars["clubId"]},
                            function(data){
                                if(data.error === true) {
                                    Tecnotek.showErrorMessage(data.message,true, "", false);
                                } else {
                                    $data = "";
                                    $data += '<p id="searchresults">';
                                    $data += '    <span class="category">Estudiantes</span>';
                                    for(i=0; i<data.students.length; i++) {
                                        console.debug();
                                        $data += '    <a class="searchResult" rel="' + data.students[i].id + '" name="' +
                                            data.students[i].firstname + ' ' + data.students[i].lastname + '">';
                                        $data += '      <span class="searchheading">' + data.students[i].firstname
                                            + ' ' + data.students[i].lastname +  '</span>';
                                        $data += '      <span>Incluir este estudiante.</span>';
                                        $data += '    </a>';
                                    }
                                    $data += '</p>';

                                    $('#suggestions').fadeIn(); // Show the suggestions box
                                    $('#suggestions').html($data); // Fill the suggestions box
                                    $('.searchResult').unbind();
                                    $('.searchResult').click(function(event){
                                        event.preventDefault();
                                        Tecnotek.UI.vars["studentId"] = $(this).attr("rel");
                                        Tecnotek.UI.vars["studentName"] = $(this).attr("name");
                                        Tecnotek.ajaxCall(Tecnotek.UI.urls["associateStudentsURL"],
                                            {studentId: $(this).attr("rel"), clubId: Tecnotek.UI.vars["clubId"]},
                                            function(data){
                                                if(data.error === true) {
                                                    Tecnotek.showErrorMessage(data.message,true, "", false);
                                                } else {
                                                    console.debug("Add Student with Id: " + Tecnotek.UI.vars["studentId"]);
                                                    $html = '<div id="student_row_' + Tecnotek.UI.vars["studentId"] + '" class="row userRow" rel="' + Tecnotek.UI.vars["studentId"] + '">';
                                                    $html += '<div class="option_width" style="float: left; width: 300px;">' + Tecnotek.UI.vars["studentName"] + '</div>';
                                                    $html += '<div class="right imageButton deleteButton" style="height: 16px;"  title="delete???"  rel="' + Tecnotek.UI.vars["studentId"] + '"></div>';
                                                    $html += '<div class="clear"></div>';
                                                    $html += '</div>';
                                                    $("#studentsList").append($html);
                                                    Tecnotek.ClubShow.initDeleteButtons();
                                                }
                                            },
                                            function(jqXHR, textStatus){
                                                Tecnotek.showErrorMessage("Error setting data: " + textStatus + ".",
                                                    true, "", false);
                                                $(this).val("");
                                                $('#suggestions').fadeOut(); // Hide the suggestions box
                                            }, true);
                                    });
                                }
                            },
                            function(jqXHR, textStatus){
                                Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".",
                                    true, "", false);
                                $(this).val("");
                                $('#suggestions').fadeOut(); // Hide the suggestions box
                            }, true);
                    }
                });

                $('#searchBox').blur(function(event){
                    event.preventDefault();
                    $(this).val("");
                    $('#suggestions').fadeOut(); // Hide the suggestions box
                });

                Tecnotek.ClubShow.initDeleteButtons();
            },
            initDeleteButtons : function() {
                console.debug("entro a initDeleteButtons!!!");
                $('.deleteButton').unbind();
                $('.deleteButton').click(function(event){
                    event.preventDefault();
                    if (Tecnotek.showConfirmationQuestion(Tecnotek.UI.translates["confirmRemoveStudent"])){
                        Tecnotek.UI.vars["studentId"] = $(this).attr("rel");
                        console.debug("Delete student: " + Tecnotek.UI.vars["studentId"] + " :: " + Tecnotek.UI.vars["clubId"]);
                        Tecnotek.ajaxCall(Tecnotek.UI.urls["removeStudentsFromClubURL"],
                            {studentId: Tecnotek.UI.vars["studentId"], clubId: Tecnotek.UI.vars["clubId"]},
                            function(data){
                                if(data.error === true) {
                                    Tecnotek.showErrorMessage(data.message,true, "", false);
                                } else {
                                    $("#student_row_" + Tecnotek.UI.vars["studentId"]).empty().remove();
                                }
                            },
                            function(jqXHR, textStatus){
                                Tecnotek.showErrorMessage("Error in request: " + textStatus + ".",
                                    true, "", false);
                            }, true);
                    }
                });
            }
        },
        StudentShow : {
            translates : {},
            init : function() {
                $('#generalTab').click(function(event){
                    event.preventDefault();
                    $('#relativesSection').hide();
                    $('#generalSection').show();
                    $('#generalTab').toggleClass("tab-current");
                    $('#relativesTab').toggleClass("tab-current");
                });
                $('#relativesTab').click(function(event){
                    event.preventDefault();
                    $('#generalSection').hide();
                    $('#relativesSection').show();
                    $('#generalTab').toggleClass("tab-current");
                    $('#relativesTab').toggleClass("tab-current");
                });
                $('#kinship').change(function(event){
                    event.preventDefault();
                    if($(this).val() == 99){
                        $('#otherDetail').show();
                    } else {
                        $('#otherDetail').hide();
                    }
                });

                $('#asociateButton').click(function(event){
                    event.preventDefault();
                    console.debug("asociateButton click!!");
                    $firstname = $("#firstname").val();
                    $lastname = $("#lastname").val();
                    $identification = $("#identification").val();
                    $detail = "";

                    switch($("#kinship").val()){
                        case "1": $detail = "Padre"; break;
                        case "2": $detail = "Madre"; break;
                        case "3": $detail = "Hermano"; break;
                        case "4": $detail = "Hermana"; break;
                        case "99": $detail = $('#description').val(); break;
                    }
                    console.debug("FirstName: "+ $firstname + ", lastname: " + $lastname +
                        ", id: " + $identification + ", val: " + $("#kinship").val() + ", detail: " + $detail);
                    if($firstname == "" || $lastname == "" || $identification == "" || $detail == ""){
                        Tecnotek.showErrorMessage(Tecnotek.StudentShow.translates["emptyFields"], true, "", false)
                    } else {
                        Tecnotek.ajaxCall(Tecnotek.UI.urls["saveNewContactURL"],
                            {studentId: Tecnotek.UI.vars["studentId"],
                                'tecnotek_expediente_contactformtype[firstname]': $firstname,
                                'tecnotek_expediente_contactformtype[lastname]': $lastname,
                                'tecnotek_expediente_contactformtype[identification]': $identification,
                                'kinship': $("#kinship").val(), 'detail': $detail},
                            function(data){
                                if(data.error === true) {
                                    Tecnotek.showErrorMessage(data.message,true, "", false);
                                } else {
                                    $html = '<div id="relative_row_' + data.id + '" class="row userRow" rel="' + data.id + '">';
                                    $html += '<div class="option_width" style="float: left; width: 200px;">' + $firstname + " " + $lastname + '</div>';
                                    $html += '<div class="option_width" style="float: left; width: 100px;">' + $detail + '</div>';
                                    $html += '<div class="right imageButton deleteButton" style="height: 16px;"  title="delete???"  rel="' + data.id + '"></div>';
                                    $html += '<div class="clear"></div>';
                                    $html += '</div>';
                                    $("#relativesList").append($html);
                                    //Clean fields
                                    $("#firstname").val("");
                                    $("#lastname").val("");
                                    $("#identification").val("");
                                    $('#description').val("");
                                    Tecnotek.StudentShow.initDeleteButtons();
                                    Tecnotek.showInfoMessage(Tecnotek.StudentShow.translates["confirmRelative"], true, "", false);
                                }
                            },
                            function(jqXHR, textStatus){
                                Tecnotek.showErrorMessage("Error saving data: " + textStatus + ".",
                                    true, "", false);
                            }, true);
                    }
                });


                $('#searchBox').keyup(function(event){
                    event.preventDefault();
                    if($(this).val().length == 0) {
                        $('#suggestions').fadeOut(); // Hide the suggestions box
                    } else {
                        Tecnotek.ajaxCall(Tecnotek.UI.urls["getContactsURL"],
                            {text: $(this).val(), studentId: Tecnotek.UI.vars["studentId"]},
                            function(data){
                                if(data.error === true) {
                                    Tecnotek.showErrorMessage(data.message,true, "", false);
                                } else {
                                    $data = "";
                                    $data += '<p id="searchresults">';
                                    $data += '    <span class="category">Contactos</span>';
                                    for(i=0; i<data.contacts.length; i++) {
                                        console.debug();
                                        $data += '    <a class="searchResult" rel="' + data.contacts[i].id + '" name="' +
                                            data.contacts[i].firstname + ' ' + data.contacts[i].lastname + '">';
                                        $data += '      <span class="searchheading">' + data.contacts[i].firstname
                                            + ' ' + data.contacts[i].lastname +  '</span>';
                                        $data += '      <span>Asociar este contacto.</span>';
                                        $data += '    </a>';
                                    }
                                    $data += '</p>';

                                    $('#suggestions').fadeIn(); // Show the suggestions box
                                    $('#suggestions').html($data); // Fill the suggestions box
                                    $('.searchResult').unbind();
                                    $('.searchResult').click(function(event){
                                        event.preventDefault();
                                        /*Tecnotek.UI.vars["studentId"] = $(this).attr("rel");
                                        Tecnotek.UI.vars["studentName"] = $(this).attr("name");
                                        Tecnotek.ajaxCall(Tecnotek.UI.urls["associateStudentsURL"],
                                            {studentId: $(this).attr("rel"), clubId: Tecnotek.UI.vars["clubId"]},
                                            function(data){
                                                if(data.error === true) {
                                                    Tecnotek.showErrorMessage(data.message,true, "", false);
                                                } else {
                                                    console.debug("Add Student with Id: " + Tecnotek.UI.vars["studentId"]);
                                                    $html = '<div id="relative_row_' + Tecnotek.UI.vars["studentId"] + '" class="row userRow" rel="' + Tecnotek.UI.vars["studentId"] + '">';
                                                    $html += '<div class="option_width" style="float: left; width: 300px;">' + Tecnotek.UI.vars["studentName"] + '</div>';
                                                    $html += '<div class="right imageButton deleteButton" style="height: 16px;"  title="delete???"  rel="' + Tecnotek.UI.vars["studentId"] + '"></div>';
                                                    $html += '<div class="clear"></div>';
                                                    $html += '</div>';
                                                    $("#studentsList").append($html);
                                                    Tecnotek.ClubShow.initDeleteButtons();
                                                }
                                            },
                                            function(jqXHR, textStatus){
                                                Tecnotek.showErrorMessage("Error setting data: " + textStatus + ".",
                                                    true, "", false);
                                                $(this).val("");
                                                $('#suggestions').fadeOut(); // Hide the suggestions box
                                            }, true);*/
                                    });
                                }
                            },
                            function(jqXHR, textStatus){
                                Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".",
                                    true, "", false);
                                $(this).val("");
                                $('#suggestions').fadeOut(); // Hide the suggestions box
                            }, true);
                    }
                });

                $('#searchBox').blur(function(event){
                    event.preventDefault();
                    $(this).val("");
                    $('#suggestions').fadeOut(); // Hide the suggestions box
                });

                Tecnotek.StudentShow.initDeleteButtons();
            },
            initDeleteButtons : function() {
                console.debug("entro a initDeleteButtons!!!");
                $('.deleteButton').unbind();
                $('.deleteButton').click(function(event){
                    event.preventDefault();
                    if (Tecnotek.showConfirmationQuestion(Tecnotek.UI.translates["confirmRemoveRelative"])){
                        Tecnotek.UI.vars["relativeId"] = $(this).attr("rel");
                        Tecnotek.ajaxCall(Tecnotek.UI.urls["removeRelativeURL"],
                            {relativeId: Tecnotek.UI.vars["relativeId"]},
                            function(data){
                                if(data.error === true) {
                                    Tecnotek.showErrorMessage(data.message,true, "", false);
                                } else {
                                    $("#relative_row_" + Tecnotek.UI.vars["relativeId"]).empty().remove();
                                }
                            },
                            function(jqXHR, textStatus){
                                Tecnotek.showErrorMessage("Error in request: " + textStatus + ".",
                                    true, "", false);
                            }, true);
                    }
                });
            }
        },
        Tickets : {
            translates : {},
            init : function() {
                $('#generalTab').click(function(event){
                    event.preventDefault();
                    $('#relativesSection').hide();
                    $('#generalSection').show();
                    $('#generalTab').toggleClass("tab-current");
                    $('#relativesTab').toggleClass("tab-current");
                });

                $('#asociateButton').click(function(event){
                    event.preventDefault();
                    console.debug("asociateButton click!!");
                    $firstname = $("#firstname").val();
                    $lastname = $("#lastname").val();
                    $identification = $("#identification").val();
                    $detail = "";

                    switch($("#kinship").val()){
                        case "1": $detail = "Padre"; break;
                        case "2": $detail = "Madre"; break;
                        case "3": $detail = "Hermano"; break;
                        case "4": $detail = "Hermana"; break;
                        case "99": $detail = $('#description').val(); break;
                    }

                    if($firstname == "" || $lastname == "" || $identification == "" || $detail == ""){
                        Tecnotek.showErrorMessage(Tecnotek.StudentShow.translates["emptyFields"], true, "", false)
                    } else {
                        Tecnotek.ajaxCall(Tecnotek.UI.urls["saveNewContactURL"],
                            {studentId: Tecnotek.UI.vars["studentId"],
                                'tecnotek_expediente_contactformtype[firstname]': $firstname,
                                'tecnotek_expediente_contactformtype[lastname]': $lastname,
                                'tecnotek_expediente_contactformtype[identification]': $identification,
                                'kinship': $("#kinship").val(), 'detail': $detail},
                            function(data){
                                if(data.error === true) {
                                    Tecnotek.showErrorMessage(data.message,true, "", false);
                                } else {
                                    $html = '<div id="relative_row_' + data.id + '" class="row userRow" rel="' + data.id + '">';
                                    $html += '<div class="option_width" style="float: left; width: 200px;">' + $firstname + " " + $lastname + '</div>';
                                    $html += '<div class="option_width" style="float: left; width: 100px;">' + $detail + '</div>';
                                    $html += '<div class="right imageButton deleteButton" style="height: 16px;"  title="delete???"  rel="' + data.id + '"></div>';
                                    $html += '<div class="clear"></div>';
                                    $html += '</div>';
                                    $("#relativesList").append($html);
                                    //Clean fields
                                    $("#firstname").val("");
                                    $("#lastname").val("");
                                    $("#identification").val("");
                                    $('#description').val("");
                                    Tecnotek.StudentShow.initDeleteButtons();
                                    Tecnotek.showInfoMessage(Tecnotek.StudentShow.translates["confirmRelative"], true, "", false);
                                }
                            },
                            function(jqXHR, textStatus){
                                Tecnotek.showErrorMessage("Error saving data: " + textStatus + ".",
                                    true, "", false);
                            }, true);
                    }
                });
                $('#student').focus(function(event){
                    event.preventDefault();
                    $('#student').attr("rel", 0);
                    $('#student').val("");
                    $("#relative").empty();
                });

                $('#save').click(function(event){
                    event.preventDefault();
                    $id = $("#student").attr("rel");
                    if($id != 0){
                        $relative = $("#relative").val();
                        if($relative != null){
                            $comments = $("#comments").val();
                            console.debug("Add ticket: student-" + $id + " :: relative-" + $relative + " :: commments-" + $comments );
                            Tecnotek.ajaxCall(Tecnotek.UI.urls["saveTicketURL"],
                                {
                                    studentId: $id,
                                    relativeId: $relative,
                                    comments: $comments
                                },
                                function(data){
                                    if(data.error === true) {
                                        Tecnotek.showErrorMessage(data.message,true, "", false);
                                    } else {
                                        window.location.reload(true);
                                    }
                                },
                                function(jqXHR, textStatus){
                                    Tecnotek.showErrorMessage("Error saving: " + textStatus + ".",
                                        true, "", false);
                                }, true);
                            /*
                            //Insertar la fila
                            $data = '<div class="row userRow tableRow">';
                            $data += '    <div class="option_width" style="float: left; width: 150px;">' + $('#student').val() + '</div>';
                            $data += '    <div class="option_width" style="float: left; width: 150px;">' + $('#relative').find(":selected").text() + '</div>';
                            $data += '    <div class="option_width" style="float: left; width: 75px;">' + '</div>';
                            $data += '    <div class="clear"></div>';
                            $data += '</div>';
                            $("#ticketsList").append($data);
                            //Limpiar valores
                            $('#student').attr("rel", 0);
                            $('#student').val("");
                            $("#relative").empty();
                            $("#comments").val("");*/
                        } else {
                            Tecnotek.showErrorMessage(Tecnotek.StudentShow.translates["relative.not.selected"], true, "", false);
                        }
                    } else {
                        Tecnotek.showErrorMessage(Tecnotek.StudentShow.translates["student.not.selected"], true, "", false);
                    }
                });

                $('#student').keyup(function(event){
                    event.preventDefault();
                    if($(this).val().length == 0) {
                        $('#suggestions').fadeOut(); // Hide the suggestions box
                    } else {
                        Tecnotek.ajaxCall(Tecnotek.UI.urls["getStudentsURL"],
                            {text: $(this).val()},
                            function(data){
                                if(data.error === true) {
                                    Tecnotek.showErrorMessage(data.message,true, "", false);
                                } else {
                                    $data = "";
                                    $data += '<p id="searchresults">';
                                    $data += '    <span class="category">Estudiantes</span>';
                                    for(i=0; i<data.students.length; i++) {
                                        $data += '    <a class="searchResult" style="height: 20px; line-height: 20px;" rel="' + data.students[i].id + '" name="' +
                                            data.students[i].firstname + ' ' + data.students[i].lastname + '">';
                                        $data += '      <span class="searchheading">' + data.students[i].firstname
                                            + ' ' + data.students[i].lastname +  '</span>';
                                        $data += '    </a>';
                                    }
                                    $data += '</p>';

                                    $('#suggestions').fadeIn(); // Show the suggestions box
                                    $('#suggestions').html($data); // Fill the suggestions box
                                    $('.searchResult').unbind();
                                    $('.searchResult').click(function(event){
                                         event.preventDefault();
                                         Tecnotek.UI.vars["studentId"] = $(this).attr("rel");
                                         $('#student').attr("rel", Tecnotek.UI.vars["studentId"]);
                                         Tecnotek.UI.vars["studentName"] = $(this).attr("name");
                                         $('#student').val(Tecnotek.UI.vars["studentName"]);

                                         Tecnotek.ajaxCall(Tecnotek.UI.urls["loadStudentRelativesURL"],
                                             {studentId: Tecnotek.UI.vars["studentId"]},
                                             function(data){
                                                 if(data.error === true) {
                                                    Tecnotek.showErrorMessage(data.message,true, "", false);
                                                 } else {
                                                     if( data.relatives.length == 0){
                                                        Tecnotek.showInfoMessage(Tecnotek.StudentShow.translates["relative.not.exists"], true, "", false);
                                                         $('#student').attr("rel", 0);
                                                         $('#student').val("");
                                                     } else {
                                                         for(i=0; i<data.relatives.length; i++) {
                                                             $("#relative").append('<option value="' + data.relatives[i].id
                                                                 +'">' + data.relatives[i].contact + ' - ' + data.relatives[i].kinship + '</option>');
                                                         }
                                                     }
                                                }
                                             },
                                             function(jqXHR, textStatus){
                                             Tecnotek.showErrorMessage("Error setting data: " + textStatus + ".",
                                             true, "", false);
                                             $(this).val("");
                                             $('#suggestions').fadeOut(); // Hide the suggestions box
                                             }, true
                                          );
                                    });
                                }
                            },
                            function(jqXHR, textStatus){
                                Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".",
                                    true, "", false);
                                $(this).val("");
                                $('#suggestions').fadeOut(); // Hide the suggestions box
                            }, true);
                    }
                });

                $('#student').blur(function(event){
                    event.preventDefault();
                    $(this).val("");
                    $('#suggestions').fadeOut(); // Hide the suggestions box
                });
            }
        }
	};
