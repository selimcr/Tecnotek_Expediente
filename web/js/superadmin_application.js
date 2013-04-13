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
        spinTarget: document.getElementById('spin'),
        spinner: new Spinner({
            lines: 11, // The number of lines to draw
            length: 20, // The length of each line
            width: 4, // The line thickness
            radius: 10, // The radius of the inner circle
            corners: 1, // Corner roundness (0..1)
            rotate: 0, // The rotation offset
            color: '#000', // #rgb or #rrggbb
            speed: 1, // Rounds per second
            trail: 60, // Afterglow percentage
            shadow: false, // Whether to render a shadow
            hwaccel: false, // Whether to use hardware acceleration
            className: 'spinner', // The CSS class to assign to the spinner
            zIndex: 2e9 // The z-index (defaults to 2000000000)
            //top: 'auto', // Top position relative to parent in px
            //left: 'auto' // Left position relative to parent in px
        }).spin(document.getElementById('spin')),
		logout:function(url){
			location.href= url;
		},
        roundTo: function(original){
            //return Math.round(original*100)/100;
            return original.toFixed(2);
        },
		init : function() {
            $( "#spinner-modal" ).dialog({
                height: 140,
                modal: true,
                width: 160,
                resizable: false,
                draggable: false,
                autoOpen: false
            });
            $("#spinner-modal").siblings('div.ui-dialog-titlebar').remove();
            Tecnotek.spinner.spin(document.getElementById('spin'));
			var module = Tecnotek.module;
			console.debug("Module: " + module)
			if (module) {
				switch (module) {
                case "penalties":
                    Tecnotek.AdministratorList.init();
                    Tecnotek.Penalties.init();
                    break;
                case "absencesByGroup":
                    Tecnotek.AbsencesByGroup.init();
                    break;
                case "absences":
                    Tecnotek.AdministratorList.init();
                    Tecnotek.Absences.init();
                    break;
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
                case "showRoute":
                    Tecnotek.EntityShow.init();
                    Tecnotek.RouteShow.init();
                    break;
                case "showStudent":
                    Tecnotek.EntityShow.init();
                    Tecnotek.StudentShow.init();
                    break;
                case "adminPeriod":
                    Tecnotek.AdminPeriod.init();
                    break;
                case "ticketsIndex":
                    Tecnotek.Tickets.init();
                    break;
                case "reports":
                    Tecnotek.Reports.init();
                    break;
                case "reportClubs":
                    Tecnotek.Reports.init();
                    Tecnotek.ReportClubs.init();
                    break;
                case "permisosUsuarios":
                    Tecnotek.PermisosUsuarios.init();
                    break;
                case "qualifications":
                    Tecnotek.Qualifications.init();
                    break;
                case "printQualifications":
                    Tecnotek.PrintQualifications.init();
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
        Reports : {
            init : function() {
                Tecnotek.Reports.initComponents();
                Tecnotek.Reports.initButtons();
            },
            initComponents : function() {
                $('.check').change(function(event){
                    $("#" + $(this).attr("rel")).val("");
                    if($(this).is(':checked')){
                        $("#" + $(this).attr("rel")).attr("disabled",false);
                    } else {
                        $("#" + $(this).attr("rel")).attr("disabled",true);
                    }
                });
            },
            initButtons : function() {
                $('#btnEliminar').click(function(event){
                    if (Tecnotek.showConfirmationQuestion(Tecnotek.UI.translates["confirmDelete"])){
                        location.href = Tecnotek.UI.urls["deleteURL"];
                    }
                });
                $('#btnPrint').click(function(event){
                    $("#report").printElement({printMode:'iframe', pageTitle:$(this).attr('rel')});
                });

            }
        },
        ReportClubs : {
            init : function() {
                Tecnotek.ReportClubs.initComponents();
                Tecnotek.ReportClubs.initButtons();
            },
            initComponents : function() {
                $('#withStudents').change(function(event){
                    if($(this).is(':checked')){
                        $("#details").show();
                    } else {
                        $("#details").hide();
                    }
                });
            },
            initButtons : function() {
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
                $('.editButton').click(function(event){
                    event.preventDefault();
                    location.href = Tecnotek.UI.urls["edit"] + "/" + $(this).attr("rel");
                });
                $('.viewButton').click(function(event){
                    event.preventDefault();
                    location.href = Tecnotek.UI.urls["show"] + "/" + $(this).attr("rel");
				});
                $('.adminButton').click(function(event){
                    event.preventDefault();
                    location.href = Tecnotek.UI.urls["admin"] + "/" + $(this).attr("rel");
                });
                $('#btnSearch').click(function(event){
                    event.preventDefault();
                    var url = location.href;
                    var text = $("#searchText").val();
                    if( url.indexOf("?") > -1 ){
                        if( url.indexOf("text=") > -1 ){
                            url += "&q=1";
                            url = url.replace(/(text=).*?(&)/,'$1' + text + '$2');
                            url = url.replace("&q=1","");
                        } else {
                            url += "&text=" + text;
                        }
                    } else {
                        url += "?text=" + text;
                    }
                    window.location.href= url;
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
        AbsencesByGroup : {
            init : function() {
                $( "#date" ).datepicker({
                    defaultDate: "0d",
                    changeMonth: true,
                    dateFormat: "yy-mm-dd",
                    showButtonPanel: true,
                    currentText: "Hoy",
                    numberOfMonths: 1,
                    onClose: function( selectedDate ) {
                        $("#absencesDate").val($("#date").val());
                    }
                });
                $("#date").datepicker('setDate', new Date()).keypress(function(event){event.preventDefault();});
                $("#absencesDate").val($("#date").val());
                $('#period').change(function(event){
                    event.preventDefault();
                    Tecnotek.AbsencesByGroup.loadGroupsOfPeriod();
                });
                $('#groups').change(function(event){
                    event.preventDefault();
                    Tecnotek.AbsencesByGroup.loadStudentsOfGroup();
                });

                Tecnotek.AbsencesByGroup.loadGroupsOfPeriod();

            },
            setRowsComponentsAction: function(){
                $(".cbRow").unbind().change(function(event){
                    event.preventDefault();
                    var $id = $(this).attr("rel");
                    var $str = $("#studentsIds").val();
                    if($(this).is(":checked") ) {
                        $("#justify_" + $id).removeAttr("disabled");
                        $("#number_" + $id).removeAttr("disabled");
                        $("#comments_" + $id).removeAttr("disabled");
                        $("#type_" + $id).focus().removeAttr("disabled");
                        $str += " " + $id; //Add the id
                    } else {
                        $("#type_" + $id).attr("disabled", true);
                        $("#justify_" + $id).attr("disabled", true);
                        $("#number_" + $id).attr("disabled", true);
                        $("#comments_" + $id).attr("disabled", true);
                        //studentsIds //Remove the id
                        var Re = new RegExp(" " + $id,"g");
                        $str = $str.replace(Re, "");
                    }
                    $("#studentsIds").val($str);
                });

                $(".commentsArea").unbind().focus(function(event){
                    $("#stdRow_" + $(this).attr("rel")).css("line-height", "60px").css("height", "60px");
                    $("#comments_" + $(this).attr("rel")).attr("rows", 3).animate({'height': '50px'}, 'slow' );
                }).blur(function(event){
                    $("#comments_" + $(this).attr("rel")).attr("rows", 1).animate({'height': '18px'}, 'slow', function() {
                        $("#stdRow_" + $(this).attr("rel")).css("line-height", "30px").css("height", "30px");
                    });

                });
            },
            loadGroupsOfPeriod : function(){
                $period = $("#period").val();
                if($period !== undefined && $period !== "undefined" && $period !== null) {
                    $('#groups').children().remove();
                    $('#studentsRows').empty();
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["loadGroupsOfPeriodURL"],
                        {   periodId: $period },
                        function(data){
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message,true, "", false);
                            } else {
                                for(i=0; i<data.groups.length; i++) {
                                    $('#groups').append('<option value="' + data.groups[i].id + '">' + data.groups[i].name + ' - ' + data.groups[i].name_group +  '</option>');
                                }
                                Tecnotek.AbsencesByGroup.loadStudentsOfGroup();
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                            $(this).val("");
                        }, true);
                }
            },
            loadStudentsOfGroup : function(){
                $group = $("#groups").val();
                if($group !== undefined && $group !== "undefined" && $group !== null) {
                    $('#studentsRows').empty();
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["getStudentsURL"],
                        {   groupId: $group,
                            searchType: 1},
                        function(data){
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message,true, "", false);
                            } else {
                                var html = "";
                                var Re = new RegExp("STDYID","g");
                                for(i=0; i<data.students.length; i++) {
                                    html = $("#stdRow_STDYID").clone().attr("id","stdRow_" + data.students[i].id).attr("name","stdRow_" + data.students[i].id).css("display", "block").wrap('<p>').parent().html();
                                    html = html.replace(Re, data.students[i].id).replace("STDNAME", data.students[i].name);
                                    $('#studentsRows').append(html);
                                }
                                Tecnotek.AbsencesByGroup.setRowsComponentsAction();
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                            $(this).val("");
                        }, true);
                }
            }
        },
        Absences : {
            init : function() {

                $( "#from" ).datepicker({
                    defaultDate: "0d",
                    changeMonth: true,
                    dateFormat: "yy-mm-dd",
                    showButtonPanel: true,
                    currentText: "Hoy",
                    numberOfMonths: 1,
                    onClose: function( selectedDate ) {
                        $( "#to" ).datepicker( "option", "minDate", selectedDate );
                    }
                });
                $( "#to" ).datepicker({
                    defaultDate: "0d",
                    changeMonth: true,
                    dateFormat: "yy-mm-dd",
                    showButtonPanel: true,
                    currentText: "Hoy",
                    numberOfMonths: 1,
                    onClose: function( selectedDate ) {
                        $( "#from" ).datepicker( "option", "maxDate", selectedDate );
                    }
                });
                $( "#date" ).datepicker({
                    defaultDate: "0d",
                    changeMonth: true,
                    dateFormat: "yy-mm-dd",
                    showButtonPanel: true,
                    currentText: "Hoy",
                    numberOfMonths: 1
                });

                $("#date").keypress(function(event){event.preventDefault();});
                $("#from").keypress(function(event){event.preventDefault();});
                $("#to").keypress(function(event){event.preventDefault();});

                $("#searchByStudent").change(function(){
                    $this = $(this);
                    if($this.is(':checked')){
                        $("#" + $this.attr("rel")).removeAttr("disabled");
                    } else {

                        $("#" + $this.attr("rel")).val("").attr("disabled",true);
                    }
                });

                $('#createAbsenceForm').submit(function(event){
                    event.preventDefault();
                    Tecnotek.Absences.save();
                });

                $('.cancelButton').click(function(event){
                    $.fancybox.close();
                });
                $('#searchBox').focus(function(event){
                    $("#tecnotek_expediente_absenceformtype_student").val(0);
                    $('#searchBox').val("");
                });
                $('#searchBox').keyup(function(event){
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
                                        $("#studentId").val($(this).attr("rel"));
                                        $('#searchBox').val("");
                                        $('#newAbsence').trigger('click');

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

                $(".deleteButton").click(function(event){
                    event.preventDefault();
                    Tecnotek.Absences.delete($(this).attr("rel"));
                });
            },
            delete : function(absenceId){
              //TODO delete Absence
                if (Tecnotek.showConfirmationQuestion(Tecnotek.UI.translates["confirmDelete"])){
                    location.href = Tecnotek.UI.urls["deleteURL"] + "/" + absenceId;
                }
            },
            save : function(){
                if(Tecnotek.UI.vars["currentPeriod"] == 0){
                    Tecnotek.showErrorMessage("Es necesario definir un periodo como actual antes de guardar.",true, "", false);
                    return;
                }
                var $studentId = $("#studentId").val();
                var $date = $("#date").val();
                var $type = $("#typeId").val();
                var $justify = $("#justify").is(':checked');
                var $comments = $("#comments").val();

                if($comments === ""){
                    Tecnotek.showErrorMessage("Debe incluir un comentario.",
                        true, "", false);
                } else {
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["saveAbsenceURL"],
                        {studentId: $studentId,
                            date: $date,
                            type: $type,
                            justify: $justify,
                            comments: $comments,
                            periodId: Tecnotek.UI.vars["currentPeriod"]
                        },
                        function(data){
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message,true, "", false);
                            } else {
                                $.fancybox.close();
                                Tecnotek.showInfoMessage("La ausencia se ha ingresado correctamente.", true, "", false)
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.showErrorMessage("Error saving absence: " + textStatus + ".",
                                true, "", false);
                        }, true);
                }

            }
        },
        Penalties : {
            init : function() {
                $( "#from" ).datepicker({
                    defaultDate: "0d",
                    changeMonth: true,
                    dateFormat: "yy-mm-dd",
                    showButtonPanel: true,
                    currentText: "Hoy",
                    numberOfMonths: 1,
                    onClose: function( selectedDate ) {
                        $( "#to" ).datepicker( "option", "minDate", selectedDate );
                    }
                });
                $( "#to" ).datepicker({
                    defaultDate: "0d",
                    changeMonth: true,
                    dateFormat: "yy-mm-dd",
                    showButtonPanel: true,
                    currentText: "Hoy",
                    numberOfMonths: 1,
                    onClose: function( selectedDate ) {
                        $( "#from" ).datepicker( "option", "maxDate", selectedDate );
                    }
                });
                $( "#date" ).datepicker({
                    defaultDate: "0d",
                    changeMonth: true,
                    dateFormat: "yy-mm-dd",
                    showButtonPanel: true,
                    currentText: "Hoy",
                    numberOfMonths: 1
                });

                $("#date").keypress(function(event){event.preventDefault();});
                $("#from").keypress(function(event){event.preventDefault();});
                $("#to").keypress(function(event){event.preventDefault();});

                $("#searchByStudent").change(function(){
                    $this = $(this);
                    if($this.is(':checked')){
                        $("#" + $this.attr("rel")).removeAttr("disabled");
                    } else {

                        $("#" + $this.attr("rel")).val("").attr("disabled",true);
                    }
                });

                $('#createPenaltyForm').submit(function(event){
                    event.preventDefault();
                    Tecnotek.Penalties.save();
                });

                $('.cancelButton').click(function(event){
                    $.fancybox.close();
                });
                $('#searchBox').focus(function(event){
                    $("#studentId").val(0);
                    $('#searchBox').val("");
                });
                $('#searchBox').keyup(function(event){
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
                                        $("#studentId").val($(this).attr("rel"));
                                        $('#searchBox').val("");
                                        $('#newAbsence').trigger('click');
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
                //TODO Penalties
            },
            save : function(){
                if(Tecnotek.UI.vars["currentPeriod"] == 0){
                    Tecnotek.showErrorMessage("Es necesario definir un periodo como actual antes de guardar.",true, "", false);
                    return;
                }
                var $studentId = $("#studentId").val();
                var $date = $("#date").val();
                var $type = $("#penaltyType").val();
                var $comments = $("#comments").val();

                if($comments === ""){
                    Tecnotek.showErrorMessage("Debe incluir un comentario.",
                        true, "", false);
                } else {
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["savePenaltyURL"],
                        {studentId: $studentId,
                            date: $date,
                            type: $type,
                            comments: $comments,
                            periodId: Tecnotek.UI.vars["currentPeriod"]
                        },
                        function(data){
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message,true, "", false);
                            } else {
                                $.fancybox.close();
                                Tecnotek.showInfoMessage("La sancion se ha ingresado correctamente.", true, "", false)
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.showErrorMessage("Error saving absence: " + textStatus + ".",
                                true, "", false);
                        }, true);
                }

            }
        },
        AdminPeriod : {
            init : function() {
                Tecnotek.UI.vars["fromEdit"] = false;
                $('#groupTab').click(function(event){
                    $('#courseSection').hide();
                    $('#entriesSection').hide();
                    $("#coursesContainer").hide();
                    $('#groupSection').show();
                    $('#entriesTab').removeClass("tab-current");
                    $('#groupTab').addClass("tab-current");
                    $('#courseTab').removeClass("tab-current");
                });
                $('#courseTab').click(function(event){
                    $('#groupSection').hide();
                    $('#entriesSection').hide();
                    $("#coursesContainer").hide();
                    $('#courseSection').show();
                    $('#entriesTab').removeClass("tab-current");
                    $('#courseTab').addClass("tab-current");
                    $('#groupTab').removeClass("tab-current");
                });
                $('#entriesTab').click(function(event){
                    $('#groupSection').hide();
                    $('#courseSection').hide();
                    $('#entriesSection').show();
                    $("#coursesContainer").show();
                    $('#courseTab').removeClass("tab-current");
                    $('#groupTab').removeClass("tab-current");
                    $('#entriesTab').addClass("tab-current");
                });
                $('#groupForm').submit(function(event){
                    event.preventDefault();
                    Tecnotek.AdminPeriod.saveGroup();
                });
                $('#courseForm').submit(function(event){
                    event.preventDefault();
                    Tecnotek.AdminPeriod.associateCourse();
                });
                $('#entryForm').submit(function(event){
                    event.preventDefault();
                    Tecnotek.AdminPeriod.createEntry();
                });
                $("#periodCourses").change(function(event){
                    event.preventDefault();
                    Tecnotek.AdminPeriod.loadEntriesByCourse($(this).val());
                });
                $("#openGroupForm").fancybox({
                    'afterLoad' : function(){
                        Tecnotek.UI.vars["groupId"] = 0;
                        $("#groupFormName").val("");
                    }
                });

                $("#openCourseForm").fancybox({
                    'beforeLoad' : function(){
                        Tecnotek.AdminPeriod.loadAvailableCoursesForGrade();
                    }
                });

                $("#openEntryForm").fancybox({
                    'beforeLoad' : function(){
                        if(Tecnotek.UI.vars["fromEdit"] === false){
                            Tecnotek.UI.vars["entryId"]  = 0;
                            $("#entryFormName").val("");
                            $("#entryFormCode").val("");
                            $("#entryFormPercentage").val("");
                            $("#entryFormMaxValue").val("");
                            $("#entryFormSortOrder").val("");
                        }
                        Tecnotek.UI.vars["fromEdit"] = false;
                        Tecnotek.AdminPeriod.preloadEntryForm();
                    }
                });

                $("#openStudentsToGroup").fancybox({
                    'beforeLoad' : function(){

                    },
                    'modal': true,
                    'width': 650
                });
                $('#searchBox').keyup(function(event){
                    event.preventDefault();
                    if($(this).val().length == 0) {
                        $('#suggestions').fadeOut(); // Hide the suggestions box
                    } else {
                        Tecnotek.ajaxCall(Tecnotek.UI.urls["getStudentsURL"],
                            {text: $(this).val(), groupId: Tecnotek.UI.vars["groupId"], periodId: Tecnotek.UI.vars["periodId"]},
                            function(data){
                                if(data.error === true) {
                                    Tecnotek.showErrorMessage(data.message,true, "", false);
                                } else {
                                    $data = "";
                                    $data += '<p id="searchresults">';
                                    $data += '    <span class="category">Estudiantes</span>';
                                    for(i=0; i<data.students.length; i++) {
                                        $data += '    <a class="searchResult" rel="' + data.students[i].id + '" name="' +
                                            data.students[i].lastname + ' ' + data.students[i].firstname + '">';
                                        $data += '      <span class="searchheading">' + data.students[i].lastname
                                            + ' ' + data.students[i].firstname +  '</span>';
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

                                        Tecnotek.ajaxCall(Tecnotek.UI.urls["setStudentToGroup"],
                                            {studentId: $(this).attr("rel"), groupId: Tecnotek.UI.vars["groupId"], periodId: Tecnotek.UI.vars["periodId"]},
                                            function(data){
                                                if(data.error === true) {
                                                    Tecnotek.showErrorMessage(data.message,true, "", false);
                                                } else {
                                                    console.debug("Add Student with Id: " + Tecnotek.UI.vars["studentId"]);
                                                    $html = '<div id="student_row_' + Tecnotek.UI.vars["studentId"] + '" class="row userRow" rel="' + Tecnotek.UI.vars["studentId"] + '">';
                                                    $html += '<div class="option_width" style="float: left; width: 300px;">' + Tecnotek.UI.vars["studentName"] + '</div>';
                                                    $html += '<div class="right imageButton deleteButton deleteStudentOfGroup" style="height: 16px;"  title="delete???"  rel="' + Tecnotek.UI.vars["studentId"] + '"></div>';
                                                    $html += '<div class="clear"></div>';
                                                    $html += '</div>';
                                                    $("#studentsList").append($html);
                                                    Tecnotek.AdminPeriod.initDeleteButtonsOfStudents();
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
                Tecnotek.AdminPeriod.initButtons();
                Tecnotek.AdminPeriod.loadPeriodInfoByGrade();
            },
            initDeleteButtonsOfStudents: function(){
                $(".deleteStudentOfGroup").unbind();
                $(".deleteStudentOfGroup").click(function(event){
                    event.preventDefault();
                    $studentId = $(this).attr("rel");
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["removeStudentFromGroupURL"],
                        {studentId: $studentId, periodId: Tecnotek.UI.vars["periodId"]},
                        function(data){
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message,true, "", false);
                            } else {
                                $("#student_row_" + $studentId).empty().remove();
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.showErrorMessage("Error executing request: " + textStatus + ".",
                                true, "", false);
                        }, true);
                });
            },
            initButtons : function() {
                $("#groupFormCancel").click(function(event){
                    event.preventDefault();
                    $.fancybox.close();
                });
                $("#courseFormCancel").click(function(event){
                    event.preventDefault();
                    $.fancybox.close();
                });
                $("#entryFormCancel").click(function(event){
                    event.preventDefault();
                    $.fancybox.close();
                });
                $("#studentsToGroupCancel").click(function(event){
                    event.preventDefault();
                    $.fancybox.close();
                });
                $("#grade").change(function(event){
                    event.preventDefault();
                    Tecnotek.AdminPeriod.loadPeriodInfoByGrade();
                });
            },
            preloadEntryForm: function(){
                if($("#periodCourses").val() === "0"){
                    Tecnotek.showErrorMessage("Por favor seleccione una materia.",true, "", false);
                    $.fancybox.close();
                } else {
                    $("#entryTitleOption").text((Tecnotek.UI.vars["entryId"] === 0)? "Incluir":"Editar");

                    //TODO Must load the list of courses again???
                }
            },
            loadAvailableCoursesForGrade: function() {
                $('#courseToAsociate').children().remove();
                Tecnotek.ajaxCall(Tecnotek.UI.urls["loadAvailableCoursesForGradeURL"],
                    {   periodId: Tecnotek.UI.vars["periodId"],
                        gradeId: $('#grade').val()},
                    function(data){
                        if(data.error === true) {
                            Tecnotek.showErrorMessage(data.message,true, "", false);
                        } else {
                            for(i=0; i<data.courses.length; i++) {
                                $('#courseToAsociate').append('<option value="' + data.courses[i].id + '">' + data.courses[i].name + '</option>');
                            }
                        }
                    },
                    function(jqXHR, textStatus){
                        Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                        $(this).val("");
                    }, true);
            },
            loadEntriesByCourse: function(courseId) {
                $('.editEntry').unbind();
                $('#entriesRows').empty();
                $('#entryFormParent').empty();

                if(courseId == 0){//Clean page
                    console.debug("Clean page!!!");
                } else {
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["loadEntriesByCourseURL"],
                        {   periodId: Tecnotek.UI.vars["periodId"],
                            courseId: courseId,
                            gradeId: $('#grade').val()},
                        function(data){
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message,true, "", false);
                            } else {

                                $('#entriesRows').append(data.entriesHtml);
                                $('#entryFormParent').append('<option value="0"></option>');
                                $('#entryFormParent').append(data.entries);
                                $('#entryFormCourseClassId').val(data.courseClassId);
                                Tecnotek.AdminPeriod.initializeEntriesButtons();
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                        }, true);
                }
            },
            loadPeriodInfoByGrade : function() {
                $('.editGroup').unbind();
                $('.deleteGroup').unbind();
                $('#groupRows').empty();

                $('.editEntry').unbind();
                $('#entriesRows').empty();


                $('.editCourse').unbind();
                $('.deleteCourse').unbind();
                $('#courseRows').empty();

                $("#periodCourses").empty();

                $gradeId = $('#gradeId').val();
                Tecnotek.ajaxCall(Tecnotek.UI.urls["loadPeriodInfoByGradeURL"],
                    {   periodId: Tecnotek.UI.vars["periodId"],
                        gradeId: $('#grade').val()},
                    function(data){
                        if(data.error === true) {
                            Tecnotek.showErrorMessage(data.message,true, "", false);
                        } else {
                            for(i=0; i<data.groups.length; i++) {
                                $html = '<div id="groupRow_' + data.groups[i].id  + '" class="row userRow tableRowOdd">';
                                $html += '    <div id="groupNameField_' + data.groups[i].id + '" name="groupNameField_' + data.groups[i].id + '" class="option_width" style="float: left; width: 250px;">' + data.groups[i].name + '</div>';
                                $html += '    <div id="groupTeacherField_' + data.groups[i].id + '" name="groupTeacherField_' + data.groups[i].id + '" class="option_width" style="float: left; width: 250px;">' + data.groups[i].teacherName + '</div>';
                                $html += '    <div id="groupInstitutionField_' + data.groups[i].id + '" name="groupInstitutionField_' + data.groups[i].id + '" class="option_width" style="float: left; width: 250px;">' + ((data.groups[i].institutionName == null)? "":data.groups[i].institutionName) + '</div>';

                                $html += '    <div class="right imageButton deleteButton deleteGroup" style="height: 16px; width: 22px;" title="Eliminar"  rel="' + data.groups[i].id + '"></div>';
                                $html += '    <div class="right imageButton editButton editGroup"  title="Editar" style=" width: 22px;"  rel="' + data.groups[i].id + '" groupName="' + data.groups[i].name + '" teacher="' + data.groups[i].teacherId + '" institution="' + data.groups[i].institutionId + '"></div>';
                                $html += '    <div class="right imageButton studentsButton studentsToGroup"  title="Asociar estudiantes"  rel="' + data.groups[i].id + '" groupName="' + data.groups[i].name + '"></div>';
                                $html += '    <div class="clear"></div>';
                                $html += '</div>';
                                $('#groupRows').append($html);
                            }

                            $("#periodCourses").append('<option value="0"></option>');

                            for(i=0; i<data.courses.length; i++) {
                                $html = '<div id="courseRow_' + data.courses[i].id  + '" class="row userRow tableRowOdd">';
                                $html += '    <div id="courseNameField_' + data.courses[i].id + '" name="courseNameField_' + data.courses[i].id + '" class="option_width" style="float: left; width: 250px;">' + data.courses[i].name + '</div>';
                                $html += '    <div id="courseTeacherField_' + data.courses[i].id + '" name="courseTeacherField_' + data.courses[i].id + '" class="option_width" style="float: left; width: 250px;">' + data.courses[i].teacherName + '</div>';
                                $html += '    <div class="right imageButton deleteButton deleteCourse" style="height: 16px;" title="Eliminar"  rel="' + data.courses[i].id + '"></div>';
                                //$html += '    <div class="right imageButton editButton editCourse"  title="Editar"  rel="' + data.courses[i].id + '" teacher="' + data.courses[i].teacherId + '"></div>';
                                $html += '    <div class="clear"></div>';
                                $html += '</div>';
                                $('#courseRows').append($html);

                                $("#periodCourses").append('<option value="' + data.courses[i].courseId + '">' + data.courses[i].name + '</option>');
                            }

                            Tecnotek.AdminPeriod.initializeGroupsButtons();
                            Tecnotek.AdminPeriod.initializeCourseButtons();

                        }
                    },
                    function(jqXHR, textStatus){
                        Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                        $(this).val("");
                    }, true);
            },
            deleteGroup: function(groupId){
                console.debug("Delete Group with id " + groupId);
                if(Tecnotek.showConfirmationQuestion("Esta seguro que desea eliminar el grupo?")) {
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["deleteGroupURL"],
                        {   groupId: groupId },
                        function(data){
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message,true, "", false);
                            } else {
                                $("#groupRow_" + groupId).fadeOut('slow', function(){});
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.showErrorMessage("Error deleting group: " + textStatus + ".", true, "", false);
                        }, true);
                }

            },
            deleteEntry: function(entryId){
                if(Tecnotek.showConfirmationQuestion("Esta seguro que desea eliminar el rubro?")) {
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["deleteEntryURL"],
                        {   entryId: entryId },
                        function(data){
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message,true, "", false);
                            } else {
                                $("#entryRow_" + entryId).fadeOut('slow', function(){});
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.showErrorMessage("Error deleting entry: " + textStatus + ".", true, "", false);
                        }, true);
                }

            },
            deleteCourseAssociation: function(associationId){
                if(Tecnotek.showConfirmationQuestion("Esta seguro que desea desasociar esta materia del grado?")) {
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["deleteCourseAssociationURL"],
                        {   associationId: associationId },
                        function(data){
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message,true, "", false);
                            } else {
                                $("#courseRow_" + associationId).fadeOut('slow', function(){});
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.showErrorMessage("Error deleting course association: " + textStatus + ".", true, "", false);
                        }, true);
                }

            },
            editGroup: function(groupId, groupName, teacherId, institutionId){
                $('#openGroupForm').trigger('click');
                $("#groupFormName").val(groupName);
                $("#groupFormTeacher").val(teacherId);
                $("#groupFormInstitution").val(institutionId);
                Tecnotek.UI.vars["groupId"] = groupId;
            },
            openStudentsToGroup: function(groupId, groupName){
                $("#studentsList").empty();
                $("#groupNameOfList").html(groupName);
                Tecnotek.ajaxCall(Tecnotek.UI.urls["getGroupStudentsURL"],
                    {groupId: Tecnotek.UI.vars["groupId"]},
                    function(data){
                        if(data.error === true) {
                            Tecnotek.showErrorMessage(data.message,true, "", false);
                        } else {
                            for(i=0; i<data.students.length; i++) {
                                $html = '<div id="student_row_' + data.students[i].id + '" class="row userRow" rel="' + data.students[i].id + '">';
                                $html += '<div class="option_width" style="float: left; width: 300px;">' + data.students[i].firstname + ' ' + data.students[i].lastname + '</div>';
                                $html += '<div class="right imageButton deleteButton deleteStudentOfGroup" style="height: 16px;"  title="delete???"  rel="' + data.students[i].id + '"></div>';
                                $html += '<div class="clear"></div>';
                                $html += '</div>';
                                $("#studentsList").append($html);
                            }
                            Tecnotek.AdminPeriod.initDeleteButtonsOfStudents();
                        }
                    },
                    function(jqXHR, textStatus){
                        Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".",
                            true, "", false);
                        $(this).val("");
                        $('#suggestions').fadeOut(); // Hide the suggestions box
                    }, true);

                $('#openStudentsToGroup').trigger('click');
                //$("#groupFormName").val(groupName);
                //Tecnotek.UI.vars["groupId"] = groupId;
            },
            initializeEntriesButtons: function(){
                $('.editEntry').unbind();
                $('.editEntry').click(function(event){
                    event.preventDefault();
                    var entryId = $(this).attr("rel");
                    Tecnotek.UI.vars["entryId"]  = entryId;
                    $("#entryFormName").val($("#entryNameField_" + entryId).text());
                    $("#entryFormCode").val($("#entryCodeField_" + entryId).text());
                    $("#entryFormPercentage").val($("#entryPercentageField_" + entryId).text());
                    $("#entryFormMaxValue").val($("#entryMaxValueField_" + entryId).text());
                    $("#entryFormSortOrder").val($("#entryOrderField_" + entryId).text());
                    $("#entryFormParent").val($(this).attr("entryparent"));
                    Tecnotek.UI.vars["fromEdit"] = true;
                    $('#openEntryForm').trigger('click');

                });

                $('.deleteEntry').unbind();
                $('.deleteEntry').click(function(event){
                    event.preventDefault();
                    Tecnotek.AdminPeriod.deleteEntry($(this).attr("rel"));
                });
            },
            initializeGroupsButtons: function(){
                $('.editGroup').unbind();
                $('.editGroup').click(function(event){
                    event.preventDefault();
                    Tecnotek.UI.vars["groupId"] = $(this).attr("rel");
                    Tecnotek.UI.vars["teacherId"] = $(this).attr("teacher");
                    Tecnotek.UI.vars["institutionId"] = $(this).attr("institution");
                    Tecnotek.editingGroup = $(this);
                    Tecnotek.AdminPeriod.editGroup(Tecnotek.UI.vars["groupId"], $(this).attr("groupName"),
                        Tecnotek.UI.vars["teacherId"], Tecnotek.UI.vars["institutionId"]);

                });

                $('.deleteGroup').unbind();
                $('.deleteGroup').click(function(event){
                    event.preventDefault();
                    Tecnotek.AdminPeriod.deleteGroup($(this).attr("rel"));
                });

                $('.studentsToGroup').unbind();
                $('.studentsToGroup').click(function(event){
                    event.preventDefault();
                    Tecnotek.UI.vars["groupId"] = $(this).attr("rel");
                    Tecnotek.editingGroup = $(this);
                    Tecnotek.AdminPeriod.openStudentsToGroup(Tecnotek.UI.vars["groupId"], $(this).attr("groupName"));

                });

            },
            initializeCourseButtons: function(){
                $('.editCourse').unbind();
                $('.editCourse').click(function(event){
                    event.preventDefault();
                    /*Tecnotek.UI.vars["groupId"] = $(this).attr("rel");
                    Tecnotek.UI.vars["teacherId"] = $(this).attr("teacher");
                    Tecnotek.editingGroup = $(this);
                    Tecnotek.AdminPeriod.editGroup(Tecnotek.UI.vars["groupId"], $(this).attr("groupName"),
                        Tecnotek.UI.vars["teacherId"]);*/

                });

                $('.deleteCourse').unbind();
                $('.deleteCourse').click(function(event){
                    event.preventDefault();
                    Tecnotek.AdminPeriod.deleteCourseAssociation($(this).attr("rel"));
                });
            },
            saveGroup: function() {
                $groupId = Tecnotek.UI.vars["groupId"];
                $gradeId = $('#gradeId').val();
                $name = $('#groupFormName').val();
                $teacherId = $('#groupFormTeacher').val();
                Tecnotek.ajaxCall(Tecnotek.UI.urls["saveGroupURL"],
                    {   groupId: $groupId,
                        periodId: Tecnotek.UI.vars["periodId"],
                        name: $name,
                        teacherId: $teacherId,
                        institutionId: $('#groupFormInstitution').val(),
                        gradeId: $('#grade').val()},
                    function(data){
                        if(data.error === true) {
                            Tecnotek.showErrorMessage(data.message,true, "", false);
                        } else {

                            if($groupId == 0){
                                $html = '<div id="groupRow_' + data.groupId  + '" class="row userRow tableRowOdd">';
                                $html += '    <div id="groupNameField_' + data.groupId + '" name="groupNameField_' + data.groupId + '" class="option_width" style="float: left; width: 250px;">' + $name + '</div>';
                                $html += '    <div id="groupTeacherField_' + data.groupId + '" name="groupTeacherField_' + data.groupId + '" class="option_width" style="float: left; width: 250px;">' + $('#groupFormTeacher :selected').text() + '</div>';
                                $html += '    <div id="groupInstitutionField_' + data.groupId + '" name="groupInstitutionField_' + data.groupId + '" class="option_width" style="float: left; width: 250px;">' + $('#groupFormInstitution :selected').text() + '</div>';
                                $html += '    <div class="right imageButton deleteButton deleteGroup" style="height: 16px;" title="Eliminar"  rel="' + data.groupId + '"></div>';
                                $html += '    <div class="right imageButton editButton editGroup"  title="Editar"  rel="' + data.groupId + '" groupName="' + $name + '" teacher="' + $teacherId + '" institution="' + $('#groupFormInstitution').val() + '"></div>';
                                $html += '    <div class="right imageButton studentsButton studentsToGroup"  title="Asociar estudiantes"  rel="' + data.groupId + '" groupName="' + $name + '"></div>';
                                $html += '    <div class="clear"></div>';
                                $html += '</div>';

                                $('#groupRows').append($html);
                                Tecnotek.AdminPeriod.initializeGroupsButtons();
                                Tecnotek.showInfoMessage("Grupo guardado correctamente.", true);
                            } else {
                                $("#groupNameField_" + $groupId).html($name);
                                $("#groupTeacherField_" + $groupId).html($('#groupFormTeacher :selected').text());
                                $("#groupInstitutionField_" + $groupId).html($('#groupFormInstitution :selected').text());
                                Tecnotek.editingGroup.attr("teacher", $('#groupFormTeacher').val());
                                Tecnotek.editingGroup.attr("institution", $('#groupFormInstitution').val())

                                Tecnotek.showInfoMessage("Grupo actualizado correctamente.", true);
                            }

                            $.fancybox.close();

                        }
                    },
                    function(jqXHR, textStatus){
                        Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                        $(this).val("");
                    }, true);
            },
            associateCourse: function() {
                Tecnotek.ajaxCall(Tecnotek.UI.urls["associateCourseURL"],
                    {   courseId: $('#courseToAsociate').val(),
                        periodId: Tecnotek.UI.vars["periodId"],
                        teacherId: $('#courseFormTeacher').val(),
                        gradeId: $('#grade').val()},
                    function(data){
                        if(data.error === true) {
                            Tecnotek.showErrorMessage(data.message,true, "", false);
                        } else {

                            //if($groupId == 0){
                                $html = '<div id="courseRow_' + data.courseClass  + '" class="row userRow tableRowOdd">';
                                $html += '    <div id="courseNameField_' + data.courseClass + '" name="courseNameField_' + data.courseClass + '" class="option_width" style="float: left; width: 250px;">' + $('#courseToAsociate :selected').text() + '</div>';
                                $html += '    <div id="courseTeacherField_' + data.courseClass + '" name="courseTeacherField_' + data.courseClass + '" class="option_width" style="float: left; width: 250px;">' + $('#courseFormTeacher :selected').text() + '</div>';
                                $html += '    <div class="right imageButton deleteButton deleteCourse" style="height: 16px;" title="Eliminar"  rel="' + data.courseClass + '"></div>';
                                //$html += '    <div class="right imageButton editButton editCourse"  title="Editar"  rel="' + data.courseId + '" teacher="' + $('#courseFormTeacher').val() + '"></div>';
                                $html += '    <div class="clear"></div>';
                                $html += '</div>';

                                $('#courseRows').append($html);
                                Tecnotek.AdminPeriod.initializeGroupsButtons();
                                Tecnotek.showInfoMessage("Materia asociada correctamente.", true);
                            /*} else {
                                $("#groupNameField_" + $groupId).html($name);
                                $("#groupTeacherField_" + $groupId).html($('#groupFormTeacher :selected').text());
                                Tecnotek.editingGroup.attr("teacher", $('#groupFormTeacher').val())

                                Tecnotek.showInfoMessage("Grupo actualizado correctamente.", true);
                            }*/

                            $.fancybox.close();
                            Tecnotek.AdminPeriod.initializeCourseButtons();
                        }
                    },
                    function(jqXHR, textStatus){
                        Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                        $(this).val("");
                    }, true);
            },
            createEntry: function() {
                Tecnotek.ajaxCall(Tecnotek.UI.urls["createEntryURL"],
                    {   parentId: $('#entryFormParent').val(),
                        name: $('#entryFormName').val(),
                        code: $('#entryFormCode').val(),
                        maxValue: $('#entryFormMaxValue').val(),
                        percentage: $('#entryFormPercentage').val(),
                        sortOrder: $('#entryFormSortOrder').val(),
                        courseClassId: $('#entryFormCourseClassId').val(),
                        entryId: Tecnotek.UI.vars["entryId"]
                    },
                    function(data){
                        if(data.error === true) {
                            Tecnotek.showErrorMessage(data.message,true, "", false);
                        } else {
                            $.fancybox.close();
                            Tecnotek.AdminPeriod.loadEntriesByCourse($("#periodCourses").val());
                        }
                    },
                    function(jqXHR, textStatus){
                        Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                        $(this).val("");
                    }, true);
            }
        },
        RouteShow : {
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
                            {text: $(this).val(), routeId: Tecnotek.UI.vars["routeId"]},
                            function(data){
                                if(data.error === true) {
                                    Tecnotek.showErrorMessage(data.message,true, "", false);
                                } else {
                                    $data = "";
                                    $data += '<p id="searchresults">';
                                    $data += '    <span class="category">Estudiantes</span>';
                                    for(i=0; i<data.students.length; i++) {
                                        $data += '    <a class="searchResult" rel="' + data.students[i].id + '" name="' +
                                            data.students[i].lastname + ' ' + data.students[i].firstname + '">';
                                        $data += '      <span class="searchheading">' + data.students[i].lastname
                                            + ' ' + data.students[i].firstname +  '</span>';
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
                                            {studentId: $(this).attr("rel"), routeId: Tecnotek.UI.vars["routeId"]},
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
                                                    Tecnotek.RouteShow.initDeleteButtons();
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

                Tecnotek.RouteShow.initDeleteButtons();
            },
            initDeleteButtons : function() {
                $('.deleteButton').unbind();
                $('.deleteButton').click(function(event){
                    event.preventDefault();
                    if (Tecnotek.showConfirmationQuestion(Tecnotek.UI.translates["confirmRemoveStudent"])){
                        Tecnotek.UI.vars["studentId"] = $(this).attr("rel");
                        Tecnotek.ajaxCall(Tecnotek.UI.urls["removeStudentsFromRouteURL"],
                            {studentId: Tecnotek.UI.vars["studentId"],
                             routeId: Tecnotek.UI.vars["routeId"],
                             routeType: Tecnotek.UI.vars["routeType"]
                            },
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
                                            data.students[i].lastname + ' ' + data.students[i].firstname + '">';
                                        $data += '      <span class="searchheading">' + data.students[i].lastname
                                            + ' ' + data.students[i].firstname +  '</span>';
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
                    $phonec = $("#phonec").val();
                    $phonew = $("#phonew").val();
                    $phoneh = $("#phoneh").val();
                    $workplace = $("#workplace").val();
                    $email = $("#email").val();
                    $adress = $("#adress").val();
                    $restriction = $("#restriction").val();
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
                                'tecnotek_expediente_contactformtype[phonec]': $phonec,
                                'tecnotek_expediente_contactformtype[phonew]': $phonew,
                                'tecnotek_expediente_contactformtype[phoneh]': $phoneh,
                                'tecnotek_expediente_contactformtype[workplace]': $workplace,
                                'tecnotek_expediente_contactformtype[email]': $email,
                                'tecnotek_expediente_contactformtype[adress]': $adress,
                                'tecnotek_expediente_contactformtype[restriction]': $restriction,
                                'kinship': $("#kinship").val(), 'detail': $detail},
                            function(data){
                                if(data.error === true) {
                                    Tecnotek.showErrorMessage(data.message,true, "", false);
                                } else {
                                    $html = '<div id="relative_row_' + data.id + '" class="row userRow" rel="' + data.id + '" style="padding: 0px;">';
                                    $html += '<div class="option_width" style="float: left; width: 350px;">' + $firstname + " " + $lastname + '</div>';
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

                $('.viewButton').click(function(event){
                    event.preventDefault();
                    var id = $(this).attr("rel");
                    //Lo que quiere que haga con el id
                    location.href = Tecnotek.UI.urls["show"] + "/" + $(this).attr("rel");
                    //console.debug("ver boleta con id: " + id);
                });
            }
        },
        PermisosUsuarios : {
            init : function() {
                Tecnotek.PermisosUsuarios.initComponents();
                Tecnotek.PermisosUsuarios.initButtons();
            },
            initComponents : function() {
                // TO CREATE AN INSTANCE
                // select the tree container using jQuery

                $("#demo1").jstree({
                        "plugins" : [ "themes", "html_data", "checkbox", "ui" ],
                        "core" : {  }
                    }).bind("loaded.jstree", function (event, data) {
                        // you get two params - event & data - check the core docs for a detailed description
                    });

                $("#btnSave").click(function(event){
                    event.preventDefault();

                    //$("#7").find('.jstree-checkbox').trigger("click");

                    if($("#users").val() == null || $("#users").val() === "null"){
                        Tecnotek.showErrorMessage("No se ha seleccionado un usuario.", true, "", false);
                    } else {
                        var checked_ids = [];
                        $('#demo1').jstree("get_checked",null,true).each(function(){
                            checked_ids.push(this.id);
                        });
                        //setting to hidden field
                        console.debug($("#users").val() + " :: " + checked_ids.join(","));

                        Tecnotek.ajaxCall(Tecnotek.UI.urls["savePrivilegesURL"],
                            {userId: $("#users").val(), access: checked_ids.join(",")},
                            function(data){
                                if(data.error === true) {
                                    Tecnotek.showErrorMessage(data.message,true, "", false);
                                } else {
                                }
                            },
                            function(jqXHR, textStatus){
                                Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".",
                                    true, "", false);
                            }, true);
                    }

                });

                $("#users").change(function(event){
                    event.preventDefault();
                    $("#demo1").jstree("uncheck_all")
                    $("#demo1").jstree('close_all');
                    if($("#users").val() == null || $("#users").val() === "null"){
                        $("#privilegesContainer").hide();
                        return;
                    } else {
                        Tecnotek.ajaxCall(Tecnotek.UI.urls["getPrivilegesURL"],
                            {userId: $("#users").val()},
                            function(data){
                                if(data.error === true) {
                                    Tecnotek.showErrorMessage(data.message,true, "", false);
                                } else {

                                    console.debug("-> " + data.privileges);
                                    for(i=0; i<data.privileges.length; i++) {
                                        $("#" + data.privileges[i]).find('.jstree-checkbox').trigger("click");
                                    }
                                    $("#privilegesContainer").show();
                                }
                            },
                            function(jqXHR, textStatus){
                                Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".",
                                    true, "", false);
                            }, true);
                    }

                });

            },
            initButtons : function() {
            }
        },
        Qualifications : {
            translates : {},
            init : function() {
                $('#viewPrintable').click(function(event){
                    event.preventDefault();
                    console.debug("print!!!");

                    var url = Tecnotek.UI.urls["viewPrintableVersionURL"];
                    var windowName = "Calificaciones de Grupo";
                    //var windowSize = windowSizeArray[$(this).attr("rel")];

                    var periodId = $("#period").val();
                    var courseId = $("#courses").val();
                    var groupId = $("#groups").val();

                    if(periodId != null && courseId != null && groupId != null){
                        url += "?periodId=" + periodId + "&groupId=" + groupId + "&courseId=" + courseId;
                        window.open(url, windowName);
                    }

                });

                $("#period").change(function(event){
                    event.preventDefault();
                    $('#subentryFormParent').empty();
                    Tecnotek.Qualifications.loadGroupsOfPeriod($(this).val());
                });

                $("#groups").change(function(event){
                    event.preventDefault();
                    Tecnotek.Qualifications.loadCoursesOfGroupByTeacher($(this).val());
                });

                $("#courses").change(function(event){
                    event.preventDefault();
                    Tecnotek.Qualifications.loadQualificationsOfGroup($(this).val());
                });

                Tecnotek.Qualifications.loadGroupsOfPeriod($('#period').val());
                Tecnotek.Qualifications.initButtons();
            },
            initButtons : function() {
                $("#entryFormCancel").click(function(event){
                    event.preventDefault();
                    $.fancybox.close();
                });
            },
            loadGroupsOfPeriod: function($periodId) {
                if(($periodId!==null)){
                    $('#groups').children().remove();
                    $('#courses').children().remove();
                    $('#subentryFormParent').empty();
                    $('#tableContainer').hide();
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["loadGroupsOfPeriodURL"],
                        {   periodId: $periodId },
                        function(data){
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message,true, "", false);
                            } else {
                                for(i=0; i<data.groups.length; i++) {
                                    $('#groups').append('<option value="' + data.groups[i].id + '">' + data.groups[i].name + '</option>');
                                }
                                Tecnotek.Qualifications.loadCoursesOfGroupByTeacher($('#groups').val());
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                            $(this).val("");
                        }, true);
                }
            },
            loadCoursesOfGroupByTeacher: function($groupId) {
                if(($groupId!==null)){
                    $('#courses').children().remove();
                    $('#subentryFormParent').empty();
                    Tecnotek.Qualifications.loadQualificationsOfGroup(0);
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["loadCoursesOfGroupByTeacherURL"],
                        {   groupId: $groupId },
                        function(data){
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message,true, "", false);
                            } else {
                                for(i=0; i<data.courses.length; i++) {
                                    $('#courses').append('<option value="' + data.courses[i].id + '">' + data.courses[i].name + '</option>');
                                }
                                Tecnotek.Qualifications.loadQualificationsOfGroup($('#courses').val());
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                            $(this).val("");
                        }, false);
                }
            },
            loadQualificationsOfGroup: function(courseId) {
                $('.editEntry').unbind();
                $('#entriesRows').empty();
                $('#subentriesRows').empty();
                $('#subentryFormParent').empty();
                $('#contentBody').empty();
                $('#studentsHeader').empty();
                if(courseId == 0){//Clean page
                } else {
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["loadQualificationsOfGroupURL"],
                        {   periodId: $("#period").val(),
                            courseId: courseId,
                            groupId: $("#groups").val()},
                        function(data){
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message,true, "", false);
                            } else {
                                console.debug("WIDHT----> " + data.codesCounter);

                                /*$("#tableContainer").width(data.codesCounter * 46 + 280);
                                if(data.codesCounter * 46 + 280 > 960) {
                                    $("#wrap").width(data.codesCounter * 46 + 280);
                                } else {
                                    $("#wrap").width(960);
                                }*/

                                $('#contentBody').html(data.html);
                                $('#tableContainer').show();

                                var height = data.studentsCounter * 26.66 + 300;
                                $("#studentsTableContainer").css("height", height + "px");



                                $(".textField").each(function(){
                                    if($(this).attr("val") !== "-1" && $(this).attr("val").indexOf("val") !== 0){
                                        $(this).val($(this).attr("val"));
                                    }
                                    $(this).trigger("blur");
                                });

                                Tecnotek.Qualifications.initializeTable();
                                Tecnotek.UI.vars["forzeBlur"] = true;
                                $(".textField").each(function(){
                                    $(this).trigger("focus");
                                    $(this).trigger("blur");
                                });
                                Tecnotek.UI.vars["forzeBlur"] = false;
                                //$( "#spinner-modal" ).dialog( "close" );
                            }
                        },
                        function(jqXHR, textStatus){
                            $( "#spinner-modal" ).dialog( "close" );
                            Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                        }, false);
                }
            },
            initializeTable: function() {
                $('.editEntry').unbind();
                $('#entriesRows').empty();
                $('#subentriesRows').empty();
                $('#subentryFormParent').empty();

                $(".textField").focus(function(e){
                    Tecnotek.UI.vars["textFieldValue"] = $(this).val();
                });
                $(".textField").blur(function(e){
                    e.preventDefault();
                    $this = $(this);
                    $type = $this.attr('tipo');
                    $max = $this.attr('max');
                    $nota = $this.val();
                    $stdId = $this.attr('std');

                    if(($nota * 1) > ($max * 1)){
                        Tecnotek.showInfoMessage("El valor maximo permitido es " + $max,true, "", false);
                        $this.val("");
                        $nota = "";
                    }
                    if(Tecnotek.UI.vars["forzeBlur"] == true){
                        if($type == 1){
                            $percentage = $this.attr('perc');
                            $max = $this.attr('max');
                            $totalField = $("#" + $this.attr('rel'));
                            //console.debug("Type = " + $type + ", Nota: " + $nota + ", Perc = " + $percentage + " :: " + $totalField);
                            if($nota == "") {
                                $totalField.html("-");
                            } else {
                                //console.debug("Calcular total para " + $(this).attr('rel') + ", total = " + ($percentage * $nota / 100));
                                $totalField.html("" + Tecnotek.roundTo(($percentage * $nota / $max)));
                            }
                        } else {
                            $childs = $this.attr('child');
                            $parent = $this.attr('parent');

                            //console.debug("Type = " + $type + ", Nota: " + $nota + " :: childs = " + $childs + " :: $stdId = " + $stdId);
                            $sum = 0;
                            $sumaPorcentage = 0;
                            $counter = 0;
                            $sumaPorcentagesAsignados = 0;
                            $('.item_' + $parent + "_" + $stdId).each(function() {
                                $notaDigitada = $(this).val();
                                $valorMax = $(this).attr("max");
                                $porcentageAsignado = parseFloat($(this).attr("perc"));
                                $sumaPorcentagesAsignados = $porcentageAsignado;
                                if($notaDigitada != ""){
                                    //100/valor max * nota digitada * %asignado
                                    $sumaPorcentage += (100 / parseFloat($valorMax) * parseFloat($notaDigitada) * ($porcentageAsignado / 100));
                                    $sum += parseFloat( $notaDigitada );
                                    $counter++;
                                }
                            });

                            if($counter == 0){
                                $("#prom_" + $parent + "_" + $stdId).html("-");
                                $totalField = $("#" + $this.attr('rel'));
                                //$("#total_" + $parent + "_" + $stdId).html("-");
                            } else {
                                $percentage =  $("#prom_" + $parent + "_" + $stdId).attr('perc');
                                $max = $("#prom_" + $parent + "_" + $stdId).attr('max');
                                $("#prom_" + $parent + "_" + $stdId).html("" + Tecnotek.roundTo(($sum/$childs)));
                                //$("#total_" + $parent + "_" + $stdId).html("" + Tecnotek.roundTo(($percentage * ($sum/$childs) / $max)));


                                $porcentageRubro = $("#total_" + $parent + "_" + $stdId).attr("perc");
                                if($sumaPorcentagesAsignados == $porcentageRubro){
                                    $("#total_" + $parent + "_" + $stdId).html("" + Tecnotek.roundTo($sumaPorcentage));
                                } else {
                                    $("#total_" + $parent + "_" + $stdId).html("" + Tecnotek.roundTo($sumaPorcentage / $counter));
                                }

                            }
                        }

                        $sum = 0;
                        $counter = 0;
                        $('.nota_' + $stdId).each(function() {
                            $temp = $(this).html();
                            if($temp != "-"){
                                /*console.debug("Bandera Temp: " + $temp + "<-");
                                 $temp = $temp.slice(0, -1);
                                 console.debug("Bandera Temp: " + $temp + "<-");*/
                                $sum += parseFloat( $temp );
                                $counter++;
                            }
                        });

                        if($counter == 0){
                            $("#total_trim_" + $stdId).html("-");
                        } else {
                            $("#total_trim_" + $stdId).html("" + Tecnotek.roundTo($sum));
                        }
                    } else {
                        if(Tecnotek.UI.vars["textFieldValue"] === $nota) return;
                        Tecnotek.ajaxCall(Tecnotek.UI.urls["saveStudentQualificationURL"],
                            {   subentryId: $this.attr('entry'),
                                studentYearId: $this.attr('stdyid'),
                                qualification: $nota},
                            function(data){
                                if(data.error === true) {
                                    Tecnotek.showErrorMessage(data.message,true, "", false);
                                    $this.val("");
                                } else {
                                    if($type == 1){
                                        $percentage = $this.attr('perc');
                                        $max = $this.attr('max');
                                        $totalField = $("#" + $this.attr('rel'));
                                        //console.debug("Type = " + $type + ", Nota: " + $nota + ", Perc = " + $percentage + " :: " + $totalField);
                                        if($nota == "") {
                                            $totalField.html("-");
                                        } else {
                                            //console.debug("Calcular total para " + $(this).attr('rel') + ", total = " + ($percentage * $nota / 100));
                                            $totalField.html("" + Tecnotek.roundTo(($percentage * $nota / $max)));
                                        }
                                    } else {
                                        $childs = $this.attr('child');
                                        $parent = $this.attr('parent');

                                        //console.debug("Type = " + $type + ", Nota: " + $nota + " :: childs = " + $childs + " :: $stdId = " + $stdId);
                                        $sum = 0;
                                        $counter = 0;
                                        $sumaPorcentage = 0;
                                        $sumaPorcentagesAsignados = 0;
                                        $('.item_' + $parent + "_" + $stdId).each(function() {
                                            $notaDigitada = $(this).val();
                                            $valorMax = $(this).attr("max");
                                            $porcentageAsignado = parseFloat($(this).attr("perc"));
                                            $sumaPorcentagesAsignados += $porcentageAsignado;
                                            if($notaDigitada != ""){
                                                //100/valor max * nota digitada * %asignado
                                                $sumaPorcentage += (100 / parseFloat($valorMax) * parseFloat($notaDigitada) * ($porcentageAsignado / 100));
                                                $sum += parseFloat( $notaDigitada );
                                                $counter++;
                                            }

                                        });

                                        if($counter == 0){
                                            $("#prom_" + $parent + "_" + $stdId).html("-");
                                            $totalField = $("#" + $this.attr('rel'));
                                            $("#total_" + $parent + "_" + $stdId).html("-");
                                        } else {
                                            $percentage =  $("#prom_" + $parent + "_" + $stdId).attr('perc');
                                            $max =  $("#prom_" + $parent + "_" + $stdId).attr('max');
                                            $("#prom_" + $parent + "_" + $stdId).html("" + Tecnotek.roundTo(($sum/$childs)));

                                            $porcentageRubro = $("#prom_" + $parent + "_" + $stdId).attr("perc");
                                            console.debug("" + $sumaPorcentagesAsignados + " :: " + $porcentageAsignado + " :: " + ($sumaPorcentagesAsignados == $porcentageRubro) + " :: " + ($sumaPorcentagesAsignados === $porcentageRubro));
                                            if($sumaPorcentagesAsignados == $porcentageRubro){
                                                $("#total_" + $parent + "_" + $stdId).html("" + Tecnotek.roundTo($sumaPorcentage));
                                            } else {
                                                $("#total_" + $parent + "_" + $stdId).html("" + Tecnotek.roundTo($sumaPorcentage / $counter));
                                            }
                                        }

                                    }

                                    $sum = 0;
                                    $counter = 0;
                                    $('.nota_' + $stdId).each(function() {
                                        $temp = $(this).html();
                                        if($temp != "-"){
                                            //$temp = $temp.slice(0, -1);
                                            $sum += parseFloat( $temp );
                                            $counter++;
                                        }
                                    });
                                    if($counter == 0){
                                        $("#total_trim_" + $stdId).html("-");
                                    } else {
                                        $("#total_trim_" + $stdId).html("" + Tecnotek.roundTo($sum));
                                    }
                                }
                            },
                            function(jqXHR, textStatus){
                                Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                            }, false);
                    }

                });
            }
        },
        PrintQualifications : {
            translates : {},
            init : function() {
                $('#btnPrint').click(function(event){
                    console.debug("print!!!");
                    $("#tablaCalificacion").printElement({printMode:'iframe', pageTitle:$(this).attr('rel')});
                });

                Tecnotek.Qualifications.initializeTable();
                $(".noPrint").hide();
                Tecnotek.UI.vars["forzeBlur"] = true;
                $(".textField").each(function(){
                    var value = $(this).attr("val");
                    if(value.indexOf("val") > -1 ){
                        $(this).val("");
                    } else {
                        $(this).val(value);
                    }

                    $(this).trigger("focus");
                    $(this).trigger("blur");
                });
                Tecnotek.UI.vars["forzeBlur"] = false;
            },
            initializeTable: function() {
                $('.editEntry').unbind();
                $('#entriesRows').empty();
                $('#subentriesRows').empty();
                $('#subentryFormParent').empty();

                $(".textField").focus(function(e){
                    Tecnotek.UI.vars["textFieldValue"] = $(this).val();
                });
                $(".textField").blur(function(e){
                    e.preventDefault();
                    $this = $(this);
                    $type = $this.attr('tipo');
                    $max = $this.attr('max');
                    $nota = $this.val();
                    $stdId = $this.attr('std');

                    if(($nota * 1) > ($max * 1)){
                        Tecnotek.showInfoMessage("El valor maximo permitido es " + $max,true, "", false);
                        $this.val("");
                        $nota = "";
                    }
                    if(Tecnotek.UI.vars["forzeBlur"] == true){
                        if($type == 1){
                            $percentage = $this.attr('perc');
                            $max = $this.attr('max');
                            $totalField = $("#" + $this.attr('rel'));
                            //console.debug("Type = " + $type + ", Nota: " + $nota + ", Perc = " + $percentage + " :: " + $totalField);
                            if($nota == "") {
                                $totalField.html("-");
                            } else {
                                //console.debug("Calcular total para " + $(this).attr('rel') + ", total = " + ($percentage * $nota / 100));
                                $totalField.html("" + Tecnotek.roundTo(($percentage * $nota / $max)));
                            }
                        } else {
                            $childs = $this.attr('child');
                            $parent = $this.attr('parent');

                            //console.debug("Type = " + $type + ", Nota: " + $nota + " :: childs = " + $childs + " :: $stdId = " + $stdId);
                            $sum = 0;
                            $sumaPorcentage = 0;
                            $counter = 0;
                            $sumaPorcentagesAsignados = 0;
                            $('.item_' + $parent + "_" + $stdId).each(function() {
                                $notaDigitada = $(this).val();
                                $valorMax = $(this).attr("max");
                                $porcentageAsignado = parseFloat($(this).attr("perc"));
                                $sumaPorcentagesAsignados = $porcentageAsignado;
                                if($notaDigitada != ""){
                                    //100/valor max * nota digitada * %asignado
                                    $sumaPorcentage += (100 / parseFloat($valorMax) * parseFloat($notaDigitada) * ($porcentageAsignado / 100));
                                    $sum += parseFloat( $notaDigitada );
                                    $counter++;
                                }
                            });

                            if($counter == 0){
                                $("#prom_" + $parent + "_" + $stdId).html("-");
                                $totalField = $("#" + $this.attr('rel'));
                                //$("#total_" + $parent + "_" + $stdId).html("-");
                            } else {
                                $percentage =  $("#prom_" + $parent + "_" + $stdId).attr('perc');
                                $max = $("#prom_" + $parent + "_" + $stdId).attr('max');
                                $("#prom_" + $parent + "_" + $stdId).html("" + Tecnotek.roundTo(($sum/$childs)));
                                //$("#total_" + $parent + "_" + $stdId).html("" + Tecnotek.roundTo(($percentage * ($sum/$childs) / $max)));


                                $porcentageRubro = $("#total_" + $parent + "_" + $stdId).attr("perc");
                                if($sumaPorcentagesAsignados == $porcentageRubro){
                                    $("#total_" + $parent + "_" + $stdId).html("" + Tecnotek.roundTo($sumaPorcentage));
                                } else {
                                    $("#total_" + $parent + "_" + $stdId).html("" + Tecnotek.roundTo($sumaPorcentage / $counter));
                                }

                            }
                        }

                        $sum = 0;
                        $counter = 0;
                        $('.nota_' + $stdId).each(function() {
                            $temp = $(this).html();
                            if($temp != "-"){
                                /*console.debug("Bandera Temp: " + $temp + "<-");
                                 $temp = $temp.slice(0, -1);
                                 console.debug("Bandera Temp: " + $temp + "<-");*/
                                $sum += parseFloat( $temp );
                                $counter++;
                            }
                        });

                        if($counter == 0){
                            $("#total_trim_" + $stdId).html("-");
                        } else {
                            $("#total_trim_" + $stdId).html("" + Tecnotek.roundTo($sum));
                        }
                    } else {
                        if(Tecnotek.UI.vars["textFieldValue"] === $nota) return;
                        Tecnotek.ajaxCall(Tecnotek.UI.urls["saveStudentQualificationURL"],
                            {   subentryId: $this.attr('entry'),
                                studentYearId: $this.attr('stdyid'),
                                qualification: $nota},
                            function(data){
                                if(data.error === true) {
                                    Tecnotek.showErrorMessage(data.message,true, "", false);
                                    $this.val("");
                                } else {
                                    if($type == 1){
                                        $percentage = $this.attr('perc');
                                        $max = $this.attr('max');
                                        $totalField = $("#" + $this.attr('rel'));
                                        //console.debug("Type = " + $type + ", Nota: " + $nota + ", Perc = " + $percentage + " :: " + $totalField);
                                        if($nota == "") {
                                            $totalField.html("-");
                                        } else {
                                            //console.debug("Calcular total para " + $(this).attr('rel') + ", total = " + ($percentage * $nota / 100));
                                            $totalField.html("" + Tecnotek.roundTo(($percentage * $nota / $max)));
                                        }
                                    } else {
                                        $childs = $this.attr('child');
                                        $parent = $this.attr('parent');

                                        //console.debug("Type = " + $type + ", Nota: " + $nota + " :: childs = " + $childs + " :: $stdId = " + $stdId);
                                        $sum = 0;
                                        $counter = 0;
                                        $sumaPorcentage = 0;
                                        $sumaPorcentagesAsignados = 0;
                                        $('.item_' + $parent + "_" + $stdId).each(function() {
                                            $notaDigitada = $(this).val();
                                            $valorMax = $(this).attr("max");
                                            $porcentageAsignado = parseFloat($(this).attr("perc"));
                                            $sumaPorcentagesAsignados += $porcentageAsignado;
                                            if($notaDigitada != ""){
                                                //100/valor max * nota digitada * %asignado
                                                $sumaPorcentage += (100 / parseFloat($valorMax) * parseFloat($notaDigitada) * ($porcentageAsignado / 100));
                                                $sum += parseFloat( $notaDigitada );
                                                $counter++;
                                            }

                                        });

                                        if($counter == 0){
                                            $("#prom_" + $parent + "_" + $stdId).html("-");
                                            $totalField = $("#" + $this.attr('rel'));
                                            $("#total_" + $parent + "_" + $stdId).html("-");
                                        } else {
                                            $percentage =  $("#prom_" + $parent + "_" + $stdId).attr('perc');
                                            $max =  $("#prom_" + $parent + "_" + $stdId).attr('max');
                                            $("#prom_" + $parent + "_" + $stdId).html("" + Tecnotek.roundTo(($sum/$childs)));

                                            $porcentageRubro = $("#prom_" + $parent + "_" + $stdId).attr("perc");
                                            console.debug("" + $sumaPorcentagesAsignados + " :: " + $porcentageAsignado + " :: " + ($sumaPorcentagesAsignados == $porcentageRubro) + " :: " + ($sumaPorcentagesAsignados === $porcentageRubro));
                                            if($sumaPorcentagesAsignados == $porcentageRubro){
                                                $("#total_" + $parent + "_" + $stdId).html("" + Tecnotek.roundTo($sumaPorcentage));
                                            } else {
                                                $("#total_" + $parent + "_" + $stdId).html("" + Tecnotek.roundTo($sumaPorcentage / $counter));
                                            }
                                        }

                                    }

                                    $sum = 0;
                                    $counter = 0;
                                    $('.nota_' + $stdId).each(function() {
                                        $temp = $(this).html();
                                        if($temp != "-"){
                                            //$temp = $temp.slice(0, -1);
                                            $sum += parseFloat( $temp );
                                            $counter++;
                                        }
                                    });
                                    if($counter == 0){
                                        $("#total_trim_" + $stdId).html("-");
                                    } else {
                                        $("#total_trim_" + $stdId).html("" + Tecnotek.roundTo($sum));
                                    }
                                }
                            },
                            function(jqXHR, textStatus){
                                Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                            }, false);
                    }

                });
            }
        }
	};