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
        CourseEntries : {
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

                Tecnotek.CourseEntries.loadGroupsOfPeriod($('#period').val());
                Tecnotek.CourseEntries.initButtons();
            },
            initButtons : function() {
                $("#entryFormCancel").click(function(event){
                    event.preventDefault();
                    $.fancybox.close();
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
                        groupId: $('#groups').val()
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
            loadGroupsOfPeriod: function($periodId) {
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
            },
            loadCoursesOfGroupByTeacher: function($groupId) {
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
                                //$('#subentryFormParent').append('<option value="0"></option>');
                                $('#subentryFormParent').append(data.entries);
                                $('#subentryFormCourseClassId').val(data.courseClassId);
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                        }, true);
                }
            }
        }
	};
