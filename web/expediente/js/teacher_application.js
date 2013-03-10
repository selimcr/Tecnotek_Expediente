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
                    zIndex: 2e9, // The z-index (defaults to 2000000000)
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
                case "absences":
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
                case "adminPeriod":
                    Tecnotek.AdminPeriod.init();
                    break;
                case "ticketsIndex":
                    Tecnotek.Tickets.init();
                    break;
                case "courseEntries":
                    Tecnotek.CourseEntries.init();
                    break;
                case "qualifications":
                    Tecnotek.Qualifications.init();
                    break;
                default:
					break;
				}
			}
			Tecnotek.UI.init();

		},
        ajaxCall : function(url, params, succedFunction, errorFunction, showSpinner) {
            if(showSpinner) $( "#spinner-modal" ).dialog( "open" );
            var request = $.ajax({
                url: url,
                type: "POST",
                data: params,
                dataType: "json"
            });

            request.done(function(data){
                succedFunction(data);
                $( "#spinner-modal" ).dialog( "close" );
            });
            request.fail(function(data){
                errorFunction(data);
                $( "#spinner-modal" ).dialog( "close" );
            });
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
        CourseEntries : {
            translates : {},
            init : function() {
                Tecnotek.UI.vars["fromEdit"] = false;

                $('#entriesTab').click(function(event){
                    $("#subentriesSection").hide();
                    $('#entriesSection').show();
                    $('#subentriesTab').removeClass("tab-current");
                    $('#entriesTab').addClass("tab-current");
                });

                $('#subentriesTab').click(function(event){
                    $('#subentriesSection').show();
                    $("#entriesSection").hide();
                    $('#subentriesTab').addClass("tab-current");
                    $('#entriesTab').removeClass("tab-current");
                });

                $('#subentryForm').submit(function(event){
                    event.preventDefault();
                    Tecnotek.CourseEntries.createEntry();
                });

                $("#period").change(function(event){
                    event.preventDefault();
                    Tecnotek.CourseEntries.loadGroupsOfPeriod($(this).val());
                });

                $("#groups").change(function(event){
                    event.preventDefault();
                    Tecnotek.CourseEntries.loadCoursesOfGroupByTeacher($(this).val());
                });

                $("#courses").change(function(event){
                    event.preventDefault();
                    Tecnotek.CourseEntries.loadEntriesByCourse($(this).val());
                });

                $("#openEntryForm").fancybox({
                    'beforeLoad' : function(){
                        if(Tecnotek.UI.vars["fromEdit"] === false){
                            Tecnotek.UI.vars["subentryId"]  = 0;
                            $("#subentryFormName").val("");
                            $("#subentryFormCode").val("");
                            $("#subentryFormPercentage").val("");
                            $("#subentryFormMaxValue").val("");
                            $("#subentryFormSortOrder").val("");
                        }
                        Tecnotek.UI.vars["fromEdit"] = false;
                        Tecnotek.CourseEntries.preloadEntryForm();
                    }
                });

                Tecnotek.CourseEntries.loadGroupsOfPeriod($('#period').val());
                Tecnotek.CourseEntries.initButtons();
            },
            initButtons : function() {
                $("#entryFormCancel").click(function(event){
                    event.preventDefault();
                    $.fancybox.close();
                });
            },
            preloadEntryForm: function(){
                if($("#courses").val() === undefined || $("#courses").val() === "0"){
                    Tecnotek.showErrorMessage("Por favor seleccione una materia.",true, "", false);
                    $.fancybox.close();
                } else {
                    if($("#subentryFormParent :selected").length <= 0){
                        Tecnotek.showErrorMessage("No es posible ingresar un subrubro sin un padre. \nNotifique al administrador para la creacion de los rubros previa.",true, "", false);
                        $.fancybox.close();
                    } else {
                        $("#entryTitleOption").text((Tecnotek.UI.vars["subentryId"] === 0)? "Incluir":"Editar");
                    }
                    //TODO Must load the list of courses again???
                }
            },
            initializeSubEntriesButtons: function(){
                $('.editSubEntry').unbind();
                $('.editSubEntry').click(function(event){
                    event.preventDefault();
                    var subentryId = $(this).attr("rel");
                    Tecnotek.UI.vars["subentryId"]  = subentryId;
                    $("#subentryFormName").val($("#subentryNameField_" + subentryId).text());
                    $("#subentryFormCode").val($("#subentryCodeField_" + subentryId).text());
                    $("#subentryFormPercentage").val($("#subentryPercentageField_" + subentryId).text());
                    $("#subentryFormMaxValue").val($("#subentryMaxValueField_" + subentryId).text());
                    $("#subentryFormSortOrder").val($("#subentryOrderField_" + subentryId).text());
                    $("#subentryFormParent").val($(this).attr("entryparent"));
                    Tecnotek.UI.vars["fromEdit"] = true;
                    $('#openEntryForm').trigger('click');

                });

                $('.deleteSubEntry').unbind();
                $('.deleteSubEntry').click(function(event){
                    event.preventDefault();
                    Tecnotek.CourseEntries.deleteSubEntry($(this).attr("rel"));
                });
            },
            createEntry: function() {
                Tecnotek.ajaxCall(Tecnotek.UI.urls["createEntryURL"],
                    {   parentId: $('#subentryFormParent').val(),
                        name: $('#subentryFormName').val(),
                        code: $('#subentryFormCode').val(),
                        maxValue: $('#subentryFormMaxValue').val(),
                        percentage: $('#subentryFormPercentage').val(),
                        sortOrder: $('#subentryFormSortOrder').val(),
                        groupId: $('#groups').val(),
                        subentryId: Tecnotek.UI.vars["subentryId"]
                    },
                    function(data){
                        if(data.error === true) {
                            Tecnotek.showErrorMessage(data.message,true, "", false);
                        } else {
                            $.fancybox.close();
                            Tecnotek.CourseEntries.loadEntriesByCourse($("#courses").val());
                        }
                    },
                    function(jqXHR, textStatus){
                        Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                    }, true);
            },
            deleteSubEntry: function(subentryId){
                if(Tecnotek.showConfirmationQuestion("Esta seguro que desea eliminar el subrubro?")) {
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["deleteSubEntryURL"],
                        {   subentryId: subentryId },
                        function(data){
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message,true, "", false);
                            } else {
                                $("#subentryRow_" + subentryId).fadeOut('slow', function(){});
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.showErrorMessage("Error deleting subentry: " + textStatus + ".", true, "", false);
                        }, true);
                }

            },
            loadGroupsOfPeriod: function($periodId) {
                if(($periodId!==null)){
                    $('#groups').children().remove();
                    $('#courses').children().remove();
                    $('#subentryFormParent').empty();
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["loadGroupsOfPeriodURL"],
                        {   periodId: $periodId },
                        function(data){
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message,true, "", false);
                            } else {
                                for(i=0; i<data.groups.length; i++) {
                                    $('#groups').append('<option value="' + data.groups[i].id + '">' + data.groups[i].name + '</option>');
                                }
                                Tecnotek.CourseEntries.loadCoursesOfGroupByTeacher($('#groups').val());
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
                    Tecnotek.CourseEntries.loadEntriesByCourse(0);
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["loadCoursesOfGroupByTeacherURL"],
                        {   groupId: $groupId },
                        function(data){
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message,true, "", false);
                            } else {
                                for(i=0; i<data.courses.length; i++) {
                                    $('#courses').append('<option value="' + data.courses[i].id + '">' + data.courses[i].name + '</option>');
                                }
                                Tecnotek.CourseEntries.loadEntriesByCourse($('#courses').val());
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                            $(this).val("");
                        }, true);
                }
            },
            loadEntriesByCourse: function(courseId) {
                $('.editEntry').unbind();
                $('#entriesRows').empty();
                $('#subentriesRows').empty();
                $('#subentryFormParent').empty();
                if(courseId == 0){//Clean page
                } else {
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["loadEntriesByCourseURL"],
                        {   periodId: $("#period").val(),
                            courseId: courseId,
                            groupId: $("#groups").val()},
                        function(data){
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message,true, "", false);
                            } else {
                                $('#entriesRows').append(data.entriesHtml);
                                $('#subentriesRows').append(data.subentriesHtml);
                                $('#subentryFormParent').append(data.entries);
                                $('#subentryFormCourseClassId').val(data.courseClassId);
                                Tecnotek.CourseEntries.initializeSubEntriesButtons();
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                        }, true);
                }
            }
        },
        Qualifications : {
            translates : {},
            init : function() {
                $('#entriesTab').click(function(event){
                    $("#subentriesSection").hide();
                    $('#entriesSection').show();
                    $('#subentriesTab').removeClass("tab-current");
                    $('#entriesTab').addClass("tab-current");
                });

                $('#subentriesTab').click(function(event){
                    $('#subentriesSection').show();
                    $("#entriesSection").hide();
                    $('#subentriesTab').addClass("tab-current");
                    $('#entriesTab').removeClass("tab-current");
                });

                $('#subentryForm').submit(function(event){
                    event.preventDefault();
                    Tecnotek.Qualifications.createEntry();
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
                        }, true);
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
                                $('#contentBody').html(data.html);
                                $('#studentsHeader').html(data.studentsHeader);
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
                        }, true);
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
                            $totalField = $("#" + $this.attr('rel'));
                            //console.debug("Type = " + $type + ", Nota: " + $nota + ", Perc = " + $percentage + " :: " + $totalField);
                            if($nota == "") {
                                $totalField.html("-");
                            } else {
                                //console.debug("Calcular total para " + $(this).attr('rel') + ", total = " + ($percentage * $nota / 100));
                                $totalField.html("" + Tecnotek.roundTo(($percentage * $nota / 100)));
                            }
                        } else {
                            $childs = $this.attr('child');
                            $parent = $this.attr('parent');

                            //console.debug("Type = " + $type + ", Nota: " + $nota + " :: childs = " + $childs + " :: $stdId = " + $stdId);
                            $sum = 0;
                            $counter = 0;
                            $('.item_' + $parent + "_" + $stdId).each(function() {
                                $temp = $(this).val();
                                if($temp != ""){
                                    $sum += parseFloat( $temp );
                                    $counter++;
                                }
                                if($counter == 0){
                                    $("#prom_" + $parent + "_" + $stdId).html("-");
                                    $totalField = $("#" + $this.attr('rel'));
                                    $("#total_" + $parent + "_" + $stdId).html("-");
                                } else {
                                    $percentage =  $("#prom_" + $parent + "_" + $stdId).attr('perc');
                                    $("#prom_" + $parent + "_" + $stdId).html("" + Tecnotek.roundTo(($sum/$childs)));
                                    $("#total_" + $parent + "_" + $stdId).html("" + Tecnotek.roundTo(($percentage * ($sum/$childs) / 100)));
                                }
                            });
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
                                        $totalField = $("#" + $this.attr('rel'));
                                        //console.debug("Type = " + $type + ", Nota: " + $nota + ", Perc = " + $percentage + " :: " + $totalField);
                                        if($nota == "") {
                                            $totalField.html("-");
                                        } else {
                                            //console.debug("Calcular total para " + $(this).attr('rel') + ", total = " + ($percentage * $nota / 100));
                                            $totalField.html("" + Tecnotek.roundTo(($percentage * $nota / 100)));
                                        }
                                        /*$sum = 0;
                                         $counter = 0;
                                         $('.nota_' + $stdId).each(function() {
                                         $temp = $(this).html();
                                         if($temp != "-"){
                                         $temp = $temp.slice(0, -1);
                                         $sum += parseFloat( $temp );
                                         $counter++;
                                         }
                                         });
                                         if($counter == 0){
                                         $("#total_trim_" + $stdId).html("-");
                                         } else {
                                         $("#total_trim_" + $stdId).html("" + $sum);
                                         }*/
                                        //p / 100 = x / nota
                                        //p * nota / 100;
                                    } else {
                                        $childs = $this.attr('child');
                                        $parent = $this.attr('parent');

                                        //console.debug("Type = " + $type + ", Nota: " + $nota + " :: childs = " + $childs + " :: $stdId = " + $stdId);
                                        $sum = 0;
                                        $counter = 0;
                                        $('.item_' + $parent + "_" + $stdId).each(function() {
                                            $temp = $(this).val();
                                            if($temp != ""){
                                                $sum += parseFloat( $temp );
                                                $counter++;
                                            }
                                            if($counter == 0){
                                                $("#prom_" + $parent + "_" + $stdId).html("-");
                                                $totalField = $("#" + $this.attr('rel'));
                                                $("#total_" + $parent + "_" + $stdId).html("-");
                                            } else {
                                                $percentage =  $("#prom_" + $parent + "_" + $stdId).attr('perc');
                                                $("#prom_" + $parent + "_" + $stdId).html("" + Tecnotek.roundTo(($sum/$childs)));
                                                $("#total_" + $parent + "_" + $stdId).html("" + Tecnotek.roundTo(($percentage * ($sum/$childs) / 100)));
                                            }
                                        });
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
