var Tecnotek = Tecnotek || {};

Tecnotek.PeriodGroupQualifications = {
    translates : {},
    completeText: "",
    studentsIndex: 0,
    periodId: 0,
    groupId: 0,
    studentsLength: 0,
    init : function() {


        $("#period").change(function(event){
            event.preventDefault();
            $('#subentryFormParent').empty();
            Tecnotek.PeriodGroupQualifications.loadGroupsOfPeriod($(this).val());
        });

        $("#groups").change(function(event){
            event.preventDefault();
            Tecnotek.PeriodGroupQualifications.loadGroupStudents($(this).val());
        });

        $("#students").change(function(event){
            event.preventDefault();
            Tecnotek.PeriodGroupQualifications.loadQualificationsOfGroup($(this).val());
        });

        Tecnotek.PeriodGroupQualifications.loadGroupsOfPeriod($('#period').val());
        Tecnotek.PeriodGroupQualifications.initButtons();
    },
    initButtons : function() {
        $('#btnPrint').click(function(event){
            $("#tablaCalificacion").printElement({printMode:'popup', pageTitle:$(this).attr('rel')});
        });
    },
    loadGroupsOfPeriod: function($periodId) {
        console.debug("Load groups of period: " + $periodId);
        if(($periodId!==null)){
            $('#groups').children().remove();
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
                        Tecnotek.PeriodGroupQualifications.loadGroupStudents($('#groups').val());
                    }
                },
                function(jqXHR, textStatus){
                    Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                    $(this).val("");
                }, true);
        }
    },
    loadGroupStudents: function($groupId) {
        console.debug("Load students of group: " + $groupId);
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
                        //$('#students').append('<option value="0">Todos</option>');
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
        if(studentId === null){//Clean page
        } else {
            $('#tableContainer').hide();
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
                        groupId: Tecnotek.PeriodGroupQualifications.groupId},
                    function(data){
                        //$('#fountainG').hide();
                        if(data.error === true) {
                            Tecnotek.showErrorMessage(data.message,true, "", false);
                        } else {
                            Tecnotek.PeriodGroupQualifications.completeText = '<div class="center"><h3><img width="840" height="145" src="/expediente/web/images/' + data.imgHeader + '" alt="" class="image-hover"></h3></div>'
                                + data.html + '<div class="pageBreak"> </div>';
                            Tecnotek.PeriodGroupQualifications.studentsIndex = 0;
                            Tecnotek.PeriodGroupQualifications.studentsLength = $('#students option').length;
                            Tecnotek.PeriodGroupQualifications.processStudentResponse("");
                            //$('#contentHeader').html(tableHeader);
                            //$('#contentBody').html(tableHeader + data.html);
                            //$('#tableContainer').show();
                        }
                    },
                    function(jqXHR, textStatus){
                        $('#fountainG').hide();
                        $( "#spinner-modal" ).dialog( "close" );
                        Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                    }, false);
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
                groupId: Tecnotek.PeriodGroupQualifications.groupId},
            function(data){
                //$('#fountainG').hide();
                if(data.error === true) {
                    Tecnotek.showErrorMessage(data.message,true, "", false);
                } else {
                    $period = $("#period").find(":selected").text();
                    $periodYear = $period.split("-")[1];
                    studentHtml += '<div class="center"><h3><img width="840" height="145" src="/expediente/web/images/' + data.imgHeader + '" alt="" class="image-hover"></h3></div>';

                    studentHtml += '<div class="reportContentHeader">';
                    studentHtml += '<div class="left reportContentLabel" style="width: 100%; font-size: 18px; text-align: center;">TARJETA DE CALIFICACIONES</div>';
                    studentHtml += '<div class="left reportContentLabel" style="width: 100%; font-size: 14px; text-align: center; margin-bottom: 15px;">' + $periodYear + '</div>';
                    studentHtml += '<div class="left reportContentLabel" style="width: 450px;">Alumnno(a):&nbsp;&nbsp;' + data.studentName  + '</div>';
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
