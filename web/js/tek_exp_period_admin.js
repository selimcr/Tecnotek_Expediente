var Tecnotek = Tecnotek || {};

Tecnotek.AdminPeriod = {
    init : function() {
        Tecnotek.UI.vars["fromEdit"] = false;
        $('#groupTab').click(function(event){
            $('#courseSection').hide();
            $('#entriesSection').hide();
            $('#teachersSection').hide(); //agregado
            $("#coursesContainer").hide();
            $("#teacherContainer").hide();
            $('#groupSection').show();
            $('#entriesTab').removeClass("tab-current");
            $('#groupTab').addClass("tab-current");
            $('#teacherTab').removeClass("tab-current");   //agregado
            $('#courseTab').removeClass("tab-current");
        });
        $('#courseTab').click(function(event){
            $('#groupSection').hide();
            $('#entriesSection').hide();
            $("#coursesContainer").hide();
            $('#teachersSection').hide(); //agregado
            $('#courseSection').show();
            $("#teacherContainer").hide();
            $('#entriesTab').removeClass("tab-current");
            $('#courseTab').addClass("tab-current");
            $('#teacherTab').removeClass("tab-current");   //agregado
            $('#groupTab').removeClass("tab-current");
        });
        $('#teacherTab').click(function(event){
            $('#groupSection').hide();
            $('#courseSection').hide();
            $('#entriesSection').hide();
            $('#teachersSection').show(); //agregado
            $("#coursesContainer").hide();
            $("#teacherContainer").show();
            $('#courseTab').removeClass("tab-current");
            $('#groupTab').removeClass("tab-current");
            $('#teacherTab').addClass("tab-current");   //agregado
            $('#entriesTab').removeClass("tab-current");  //agregado
        });
        $('#entriesTab').click(function(event){
            $('#groupSection').hide();
            $('#courseSection').hide();
            $('#entriesSection').show();
            $('#teachersSection').hide(); //agregado
            $("#coursesContainer").show();
            $("#teacherContainer").hide();
            $('#courseTab').removeClass("tab-current");
            $('#groupTab').removeClass("tab-current");
            $('#teacherTab').removeClass("tab-current");   //agregado
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
        $('#teacherForm').submit(function(event){
            event.preventDefault();
            Tecnotek.AdminPeriod.createTeacherAssigned();
        });
        $("#periodCourses").change(function(event){
            event.preventDefault();
            Tecnotek.AdminPeriod.loadEntriesByCourse($(this).val());
        });
        $("#teachers").change(function(event){
            event.preventDefault();
            Tecnotek.AdminPeriod.loadCoursesAssignedByTeacher($(this).val());
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

        $("#openTeacherForm").fancybox({
            'beforeLoad' : function(){
                /*if(Tecnotek.UI.vars["fromEdit"] === false){
                 Tecnotek.UI.vars["entryId"]  = 0;
                 $("#entryFormName").val("");
                 $("#entryFormCode").val("");
                 $("#entryFormPercentage").val("");
                 $("#entryFormMaxValue").val("");
                 $("#entryFormSortOrder").val("");
                 }*/
                //Tecnotek.UI.vars["fromEdit"] = false;
                //Tecnotek.AdminPeriod.preloadEntryForm();
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
                                $data += '      <span class="searchheading">' + data.students[i].carne
                                    + ' ' + data.students[i].lastname +  ' ' + data.students[i].firstname +  '</span>';
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
                                            $html = '<div id="student_row_' + data.id + '" class="row userRow" rel="' + data.id + '">';
                                            $html += '<div class="option_width" style="float: left; width: 300px;">' + Tecnotek.UI.vars["studentName"] + '</div>';
                                            $html += '<div class="right imageButton deleteButton deleteStudentOfGroup" style="height: 16px;"  title="delete???"  rel="' + data.id + '"></div>';
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
        $("#courseFormTeacherCancel").click(function(event){
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
        $("#groupToFormTeacher").change(function(event){
            event.preventDefault();
            Tecnotek.AdminPeriod.loadCourseClassByGroup();
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
    loadCourseClassByGroup: function() {
        $('#courseToAsociateFormTeacher').children().remove();
        //alert($('#groupToFormTeacher').val());
        Tecnotek.ajaxCall(Tecnotek.UI.urls["loadCoursesClassForGroupURL"],
            {   periodId: Tecnotek.UI.vars["periodId"],
                groupId: $('#groupToFormTeacher').val()},
            function(data){
                if(data.error === true) {
                    Tecnotek.showErrorMessage(data.message,true, "", false);
                } else {
                    for(i=0; i<data.courses.length; i++) {
                        $('#courseToAsociateFormTeacher').append('<option value="' + data.courses[i].courseclass + '">' + data.courses[i].name + '</option>');
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
    loadCoursesAssignedByTeacher: function(teacherId) {
        //$('.editEntry').unbind();
        $('#courseToAsociateFormTeacher').empty();
        $('#groupToFormTeacher').empty();
        $('#courseTeacherRows').empty();
        if(teacherId == 0){//Clean page
            console.debug("Clean page!!!");
        } else {
            Tecnotek.ajaxCall(Tecnotek.UI.urls["loadCoursesByTeacherURL"],
                {   periodId: Tecnotek.UI.vars["periodId"],
                    teacherId: teacherId},
                function(data){
                    if(data.error === true) {
                        Tecnotek.showErrorMessage(data.message,true, "", false);
                    } else {

                        $('#courseTeacherRows').append(data.entriesHtml);
                        $('#groupToFormTeacher').append(0);
                        $('#groupToFormTeacher').append(data.groupOptions);
                        Tecnotek.AdminPeriod.initializeTeachersButtons();
                    }
                },
                function(jqXHR, textStatus){
                    Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                }, true);
        }
    },
    loadCoursesByTeacher: function() {
        $('#courseToAsociateFormTeacher').children().remove();
        Tecnotek.ajaxCall(Tecnotek.UI.urls["loadAvailableCoursesForTeacherURL"],
            {   periodId: Tecnotek.UI.vars["periodId"],
                teachers: $('#teachers').val()},
            function(data){
                if(data.error === true) {
                    Tecnotek.showErrorMessage(data.message,true, "", false);
                } else {
                    for(i=0; i<data.courses.length; i++) {
                        $('#courseToAsociateFormTeacher').append('<option value="' + data.courses[i].id + '">' + data.courses[i].name + '</option>');
                    }
                }
            },
            function(jqXHR, textStatus){
                Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                $(this).val("");
            }, true);
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
                        $html += '    <div id="groupInstitutionField_' + data.groups[i].id + '" name="groupInstitutionField_' + data.groups[i].id + '" class="option_width" style="float: left; width: 200px;">' + ((data.groups[i].institutionName == null)? "":data.groups[i].institutionName) + '</div>';

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
    deleteTeacherAssigned: function(teacherAssignedId){
        if(Tecnotek.showConfirmationQuestion("Esta seguro que desea eliminar el grupo asignado?")) {
            Tecnotek.ajaxCall(Tecnotek.UI.urls["deleteTeacherAssignedURL"],
                {   teacherAssignedId: teacherAssignedId },
                function(data){
                    if(data.error === true) {
                        Tecnotek.showErrorMessage(data.message,true, "", false);
                    } else {
                        $("#courseTeacherRows_" + teacherAssignedId).fadeOut('slow', function(){});
                    }
                },
                function(jqXHR, textStatus){
                    Tecnotek.showErrorMessage("Error deleting relacion: " + textStatus + ".", true, "", false);
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
    initializeTeachersButtons: function(){
        /*$('.edit').unbind();
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

         });*/

        $('.deleteTeacherAssigned').unbind();
        $('.deleteTeacherAssigned').click(function(event){
            event.preventDefault();
            Tecnotek.AdminPeriod.deleteTeacherAssigned($(this).attr("rel"));
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
    },
    createTeacherAssigned: function() {
    Tecnotek.ajaxCall(Tecnotek.UI.urls["createTeacherAssignedURL"],
        {   periodId: Tecnotek.UI.vars["periodId"],
            teacherId: $('#courseFormTeacher').val(),
            courseClassId: $('#courseToAsociateFormTeacher').val(),
            groupId: $('#groupToFormTeacher').val()
        },
        function(data){
            if(data.error === true) {
                Tecnotek.showErrorMessage(data.message,true, "", false);
            } else {
                $.fancybox.close();
                Tecnotek.AdminPeriod.loadCoursesAssignedByTeacher($('#teachers').val());
            }
        },
        function(jqXHR, textStatus){
            Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
            $(this).val("");
        }, true);
}
};