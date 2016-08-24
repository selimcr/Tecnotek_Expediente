var Tecnotek = Tecnotek || {};

Tecnotek.ReportConvocatorias = {
    translates : {},
    completeText: "",
    studentsIndex: 0,
    periodId: 0,
    groupId: 0,
    studentsLength: 0,
    init : function() {
        $("#reportType").change(function(event){
            console.debug("Changing type or report: " + $(this).val());
            switch($(this).val()) {
                case "1": // Por grupo
                    $("#groupsContainer").show();
                    $("#levelsContainer").hide();
                    $("#coursesContainer").hide();
                    break;
                case "2": // Por materia
                    $("#groupsContainer").hide();
                    $("#levelsContainer").show();
                    $("#coursesContainer").show();
                    break;
                default:
                    break;
            }
            $("#period").change();
        });
        $("#period").change(function(event){
            event.preventDefault();
            $('#subentryFormParent').empty();
            switch($("#reportType").val()) {
                case "1": // Por grupo
                    Tecnotek.ReportConvocatorias.loadGroupsOfPeriod($(this).val());
                    break;
                case "2": // Por materia
                    Tecnotek.ReportConvocatorias.loadPeriodLevels($(this).val());
                    break;
                default:
                    break;
            }

        });
        $("#groups").change(function(event){
            event.preventDefault();
            Tecnotek.ReportConvocatorias.loadGroupStudents($(this).val());
        });
        $("#levels").change(function(event){
            event.preventDefault();
            Tecnotek.ReportConvocatorias.loadCoursesOfPeriodAndLevel($('#period').val(), $('#levels').val());
        });
        Tecnotek.ReportConvocatorias.loadGroupsOfPeriod($('#period').val());
        Tecnotek.ReportConvocatorias.initButtons();
    },
    initButtons : function() {
        $('#btnPrint').click(function(event){
            $("#tableContainer").printElement({printMode:'popup', pageTitle:$(this).attr('rel')});
        });
    },
    loadGroupsOfPeriod: function($periodId) {
        $('input[name=conv]').val(0);
        console.debug("Load groups of period: " + $periodId);
        if(($periodId!==null)){
            $('#groups').children().remove();
            $('#levels').children().remove();
            $('#courses').children().remove();
            $('#students').children().remove();
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
                        //Tecnotek.PeriodGroupQualifications.loadGroupStudents($('#groups').val());
                    }
                },
                function(jqXHR, textStatus){
                    Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                    $(this).val("");
                }, true);
        }
    },
    loadPeriodLevels: function($periodId){
        if(($periodId!==null)){
            $('#levels').children().remove();
            $('#courses').children().remove();
            Tecnotek.ajaxCall(Tecnotek.UI.urls["loadLevelsOfPeriodURL"],
                {   periodId: $periodId },
                function(data){
                    if(data.error === true) {
                        Tecnotek.showErrorMessage(data.message,true, "", false);
                    } else {
                        for(i=0; i<data.levels.length; i++) {
                            $('#levels').append('<option value="' + data.levels[i].id + '">' + data.levels[i].name + '</option>');
                        }
                        Tecnotek.ReportConvocatorias.loadCoursesOfPeriodAndLevel($('#period').val(), $('#levels').val());
                    }
                },
                function(jqXHR, textStatus){
                    Tecnotek.showErrorMessage("Error getting data : " + textStatus + ".", true, "", false);
                    $(this).val("");
                }, true);
        }
    },
    loadCoursesOfPeriodAndLevel: function($periodId, $levelId) {
        console.debug("Load Courses of Period and Level [" + $periodId + "-" + $levelId + "]");
        if(($periodId!==null) && ($levelId!==null)){
            $('#courses').children().remove();
            $('#subentryFormParent').empty();
            Tecnotek.ajaxCall(Tecnotek.UI.urls["loadCoursesOfPeriodAndLevelURL"],
                {   periodId: $periodId,
                    levelId: $levelId},
                function(data){
                    if(data.error === true) {
                        Tecnotek.showErrorMessage(data.message,true, "", false);
                    } else {
                        $('#courses').append('<option value="0"></option>');
                        for(i=0; i<data.courses.length; i++) {
                            $('#courses').append('<option value="' + data.courses[i].id + '">' + data.courses[i].name + '</option>');
                        }
                        //Tecnotek.GroupCourseQualifications.loadQualificationsOfGroupByGroup($('#courses').val());
                    }
                },
                function(jqXHR, textStatus){
                    Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                    $(this).val("");
                }, false);
        }
    },
    loadGroupCourses: function($groupId) {
        if(($groupId!==null)){
            $('#courses').children().remove();
            $('#subentryFormParent').empty();
            Tecnotek.Qualifications.loadQualificationsOfGroup(0);
            Tecnotek.ajaxCall(Tecnotek.UI.urls["loadCoursesOfGroupURL"],
                {   groupId: $groupId.split("-")[0] },
                function(data){
                    if(data.error === true) {
                        Tecnotek.showErrorMessage(data.message,true, "", false);
                    } else {
                        $('#courses').append('<option value="0"></option>');
                        //$('#courses').append('<option value="-1">Solo Hoja</option>');
                        //$('#courses').append('<option value="0">Todo</option>');
                        for(i=0; i<data.courses.length; i++) {
                            $('#courses').append('<option value="' + data.courses[i].id + '">' + data.courses[i].name + '</option>');
                        }
                        //Tecnotek.GroupCourseQualifications.loadQualificationsOfGroupByGroup($('#courses').val());
                    }
                },
                function(jqXHR, textStatus){
                    Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                    $(this).val("");
                }, false);
        }
    },
    loadGroupStudents: function($groupId) {
        if(($groupId!==null)){
            $('#students').children().remove();
            $('#subentryFormParent').empty();
            Tecnotek.Qualifications.loadQualificationsOfGroup(0);
            Tecnotek.ajaxCall(Tecnotek.UI.urls["loadStudentsGroupURL"],
                {   groupId: $groupId.split("-")[0] },
                function(data){
                    if(data.error === true) {
                        Tecnotek.showErrorMessage(data.message,true, "", false);
                    } else {
                        $('#students').append('<option value="-2"></option>');
                        $('#students').append('<option value="-1">Solo Hoja</option>');
                        $('#students').append('<option value="0">Todo</option>');
                        for(i=0; i<data.students.length; i++) {
                            $('#students').append('<option value="' + data.students[i].id + '">' + data.students[i].lastname + ", " + data.students[i].firstname + '</option>');
                        }
                        Tecnotek.PeriodGroupQualifications.loadQualificationsOfGroup($('#students').val());
                    }
                },
                function(jqXHR, textStatus){
                    Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                    $(this).val("");
                }, false);
        }
    },
    loadQualificationsOfGroup: function(studentId) {
        $('.editEntry').unbind();
        $('#contentBody').empty();
        $('#tableContainer').hide();
        if(studentId === null || studentId == -2){//Clean page
        } else { if(studentId == -1){

            $('#fountainG').show();
            Tecnotek.PeriodGroupQualifications.periodId = $("#period").val();
            Tecnotek.PeriodGroupQualifications.groupId = $("#groups").val();

            studentId = 0;
            Tecnotek.PeriodGroupQualifications.completeText = "";
            Tecnotek.PeriodGroupQualifications.studentsIndex = $('#students option').length;
            Tecnotek.PeriodGroupQualifications.studentsLength = Tecnotek.PeriodGroupQualifications.studentsIndex;
            Tecnotek.PeriodGroupQualifications.loadStudentQualification(studentId);


        }else {
            $('#fountainG').show();
            Tecnotek.PeriodGroupQualifications.periodId = $("#period").val();
            Tecnotek.PeriodGroupQualifications.groupId = $("#groups").val();
            if(studentId != 0){//Single student

                Tecnotek.PeriodGroupQualifications.completeText = "";
                Tecnotek.PeriodGroupQualifications.studentsIndex = $('#students option').length;
                Tecnotek.PeriodGroupQualifications.studentsLength = Tecnotek.PeriodGroupQualifications.studentsIndex;
                Tecnotek.PeriodGroupQualifications.loadStudentQualification(studentId);

            } else {//All Students
                Tecnotek.ajaxCall(Tecnotek.UI.urls["loadQualificationsOfGroupURL"],
                    {   periodId: Tecnotek.PeriodGroupQualifications.periodId,
                        referenceId: studentId,
                        groupId: Tecnotek.PeriodGroupQualifications.groupId,
                        conv: $("#conv").val()},
                    function(data){
                        //$('#fountainG').hide();
                        if(data.error === true) {
                            Tecnotek.showErrorMessage(data.message,true, "", false);
                        } else {
                            Tecnotek.PeriodGroupQualifications.completeText = '<div class="center"><h3><img width="840" height="145" src="/expediente/web/images/' + data.imgHeader + '" alt="" class="image-hover"></h3></div>'
                                + data.html + '<div class="pageBreak"> </div>';
                            Tecnotek.PeriodGroupQualifications.studentsIndex = 2;
                            Tecnotek.PeriodGroupQualifications.studentsLength = $('#students option').length;
                            Tecnotek.PeriodGroupQualifications.processStudentResponse("");
                        }
                    },
                    function(jqXHR, textStatus){
                        $('#fountainG').hide();
                        $( "#spinner-modal" ).dialog( "close" );
                        Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                    }, false);
            }
        }
        }
    },
    loadAllStudentsQualifications: function() {
        studentId = $('#students option:eq(' + Tecnotek.PeriodGroupQualifications.studentsIndex + ')').val();
        Tecnotek.PeriodGroupQualifications.loadStudentQualification(studentId);
    },
    loadStudentQualification: function(studentId) {
        var studentHtml = "";

        Tecnotek.ajaxCall(Tecnotek.UI.urls["loadQualificationsOfGroupURL"],
            {   periodId: Tecnotek.PeriodGroupQualifications.periodId,
                referenceId: studentId,
                groupId: Tecnotek.PeriodGroupQualifications.groupId,
                conv: $("#conv").val()},
            function(data){
                //$('#fountainG').hide();
                if(data.error === true) {
                    Tecnotek.showErrorMessage(data.message,true, "", false);
                } else {
                    $period = $("#period").find(":selected").text();
                    $periodYear = $period.split("-")[1];
if(data.kinder1 == '1'){
                    studentHtml += '<div class="center"><h3><img width="840" height="245" src="/expediente/web/images/notaskinder.png" alt="" class="image-hover"></h3></div>';
}else{
                    studentHtml += '<div class="center"><h3><img width="840" height="145" src="/expediente/web/images/' + data.imgHeader + '" alt="" class="image-hover"></h3></div>';}

                    studentHtml += '<div class="reportContentHeader">';
if(data.kinder1 != '1'){
                    studentHtml += '<div class="left reportContentLabel" style="width: 100%; font-size: 18px; text-align: center;">TARJETA DE CALIFICACIONES</div>';}
                    studentHtml += '<div class="left reportContentLabel" style="width: 100%; font-size: 14px; text-align: center; margin-bottom: 15px;"> </div>';
                    studentHtml += '<div class="left reportContentLabel" style="width: 450px;">Alumno(a):&nbsp;&nbsp;' + data.studentName  + '</div>';
                    studentHtml += '<div class="left reportContentLabel" style="width: 350px;">Secci&oacute;n:&nbsp;&nbsp;' + $("#groups").find(":selected").text() + '</div>';
                    studentHtml += '<div class="clear"></div>';

                    studentHtml += '<div class="left reportContentLabel" style="width: 450px;">Carn&eacute;:&nbsp;&nbsp;' + data.carne  + '</div>';
                    studentHtml += '<div class="left reportContentLabel" style="width: 350px;">Trimestre:&nbsp;&nbsp;' + $period + '</div>';
                    studentHtml += '<div class="clear"></div>';

                    studentHtml += '<div class="left reportContentLabel" style="width: 450px;">&nbsp;&nbsp;</div>';
                    studentHtml += '<div class="left reportContentLabel" style="width: 350px;">Profesor:&nbsp;&nbsp;' + data.teacherGroup + '</div>';
                    studentHtml += '<div class="clear"></div>';
                    //studentHtml += '<div class="left reportContentLabel">Grado y Grupo:</div><div class="left reportContentText">' + $("#groups").find(":selected").text() + '</div><div class="clear"></div>';
                    // studentHtml += '<div class="left reportContentLabel">Estudiante:</div><div class="left reportContentText">' + $("#students").find(":selected").text() + '</div><div class="clear"></div>';
                    studentHtml += "</div>";
                    studentHtml += data.html  + '<div class="pageBreak"> </div>';

                    Tecnotek.PeriodGroupQualifications.processStudentResponse(studentHtml);
                    //$('#contentBody').html(data.html);
                    //$('#tableContainer').show();
                }
            },
            function(jqXHR, textStatus){
                $('#fountainG').hide();
                $( "#spinner-modal" ).dialog( "close" );
                Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
            }, false);

    },
    processStudentResponse: function(html){

        Tecnotek.PeriodGroupQualifications.completeText += html;
        Tecnotek.PeriodGroupQualifications.studentsIndex++;
        console.debug(Tecnotek.PeriodGroupQualifications.studentsIndex + " :: " + Tecnotek.PeriodGroupQualifications.studentsLength);
        if(Tecnotek.PeriodGroupQualifications.studentsIndex < Tecnotek.PeriodGroupQualifications.studentsLength){
            var studentId = $('#students option:eq(' + Tecnotek.PeriodGroupQualifications.studentsIndex + ')').val();
            console.debug("get student: " + studentId);
            Tecnotek.PeriodGroupQualifications.loadStudentQualification(studentId);
        } else {
            Tecnotek.PeriodGroupQualifications.terminateGetAllQualifications();
        }
    },
    terminateGetAllQualifications: function(){
        //console.debug(html);
        $('#fountainG').hide();
        $('#contentBody').html(Tecnotek.PeriodGroupQualifications.completeText);
        $('#tableContainer').show();
    }
};