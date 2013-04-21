var Tecnotek = Tecnotek || {};

Tecnotek.PeriodGroupQualifications = {
    translates : {},
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
                        $('#students').append('<option value="0">Todos</option>');
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
            Tecnotek.ajaxCall(Tecnotek.UI.urls["loadQualificationsOfGroupURL"],
                {   periodId: $("#period").val(),
                    referenceId: studentId,
                    groupId: $("#groups").val()},
                function(data){
                    $('#fountainG').hide();
                    if(data.error === true) {
                        Tecnotek.showErrorMessage(data.message,true, "", false);
                    } else {
                        var tableHeader = "";
                        if(studentId != 0){
                            tableHeader += '<div class="reportContentHeader">';
                            tableHeader += '<div class="left reportContentLabel">Periodo:</div><div class="left reportContentText">' + $("#period").find(":selected").text() + '</div><div class="clear"></div>';
                            tableHeader += '<div class="left reportContentLabel">Grado y Grupo:</div><div class="left reportContentText">' + $("#groups").find(":selected").text() + '</div><div class="clear"></div>';
                            tableHeader += '<div class="left reportContentLabel">Estudiante:</div><div class="left reportContentText">' + $("#students").find(":selected").text() + '</div><div class="clear"></div>';
                            tableHeader += "</div>";
                        }
                        //
                        $('#contentHeader').html(tableHeader);
                        $('#contentBody').html(data.html);
                        $('#tableContainer').show();
                    }
                },
                function(jqXHR, textStatus){
                    $('#fountainG').hide();
                    $( "#spinner-modal" ).dialog( "close" );
                    Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                }, false);
        }
    }
};
