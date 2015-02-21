var Tecnotek = Tecnotek || {};

Tecnotek.Emails = {
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
            Tecnotek.Emails.loadPeriodLevels($(this).val());
        });

        $("#levels").change(function(event){
            event.preventDefault();
            //: function($periodId, $levelId)
            Tecnotek.Emails.loadGroupsOfPeriodAndLevel($('#period').val(), $(this).val());
        });

        $("#groups").change(function(event){
            event.preventDefault();
            $("#emails-ta").val("");
        });
/*
        $("#copyBtn").click(function(e){
            var ta =
            Copied = $("#emails-ta".val()).createTextRange();
            Copied.execCommand("Copy");
        });*/

        Tecnotek.Emails.loadPeriodLevels($('#period').val());
        Tecnotek.Emails.initButtons();
    },
    initButtons : function() {
        $('#btnLoad').click(function(event){
            Tecnotek.Emails.loadEmails();
        });
    },
    loadPeriodLevels: function($periodId){
        if(($periodId!==null)){
            $('#levels').children().remove();
            $('#groups').children().remove();
            $("#emails-ta").val("");
            Tecnotek.ajaxCall(Tecnotek.UI.urls["loadLevelsOfPeriodURL"],
                {   periodId: $periodId },
                function(data){
                    if(data.error === true) {
                        Tecnotek.showErrorMessage(data.message,true, "", false);
                    } else {
                        $('#levels').append('<option value="0">Todos</option>');
                        for(i=0; i<data.levels.length; i++) {
                            $('#levels').append('<option value="' + data.levels[i].id + '">' + data.levels[i].name + '</option>');
                        }
                        Tecnotek.Emails.loadGroupsOfPeriodAndLevel($('#period').val(), $('#levels').val());
                    }
                },
                function(jqXHR, textStatus){
                    Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                    $(this).val("");
                }, true);
        }
    },
    loadGroupsOfPeriodAndLevel: function($periodId, $levelId) {
        if(($periodId!==null)){
            $('#groups').children().remove();
            $("#emails-ta").val("");
            Tecnotek.ajaxCall(Tecnotek.UI.urls["loadGroupsOfPeriodAndLevelsURL"],
                {   periodId:   $periodId,
                    levelId:    $levelId},
                function(data){
                    if(data.error === true) {
                        Tecnotek.showErrorMessage(data.message,true, "", false);
                    } else {
                        $('#groups').append('<option value="0-0">Todos</option>');
                        for(i=0; i<data.groups.length; i++) {
                            $('#groups').append('<option value="' + data.groups[i].id + '">' + data.groups[i].name + '</option>');
                        }
                    }
                },
                function(jqXHR, textStatus){
                    Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                    $(this).val("");
                }, true);
        }
    },
    loadEmails: function() {
        $("#emails-ta").val("");
        Tecnotek.ajaxCall(Tecnotek.UI.urls["loadEmailsURL"],
            {   periodId:   $('#period').val(),
                levelId:    $('#levels').val(),
                groupId:    $('#groups').val()},
            function(data){
                if(data.error === true) {
                    Tecnotek.showErrorMessage(data.message,true, "", false);
                } else {
                    /*for(i=0; i<data.emails.length; i++) {
                        $("#emails-ta").val(data.emails[i].emails);
                    }*/
                    $("#emails-ta").val(data.emails);
                }
            },
            function(jqXHR, textStatus){
                Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".", true, "", false);
                $(this).val("");
            }, true);
    }
};