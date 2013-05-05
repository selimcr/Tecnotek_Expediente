var Tecnotek = Tecnotek || {};

Tecnotek.TicketsSearch = {
    translates : {},
    init : function() {
        $("#searchByStudent").change(function(){
            $this = $(this);
            if($this.is(':checked')){
                $("#" + $this.attr("rel")).removeAttr("disabled");
            } else {

                $("#" + $this.attr("rel")).val("").attr("disabled",true);
            }
        });
    }
};

Tecnotek.Tickets = {
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
            location.href = Tecnotek.UI.urls["show"] + "/" + $(this).attr("rel");
        });

        $('.deleteButton').click(function(event){
            event.preventDefault();
            var id = $(this).attr("rel");
            if (Tecnotek.showConfirmationQuestion(Tecnotek.UI.translates["confirmDelete"])){
                Tecnotek.ajaxCall(Tecnotek.UI.urls["deleteTicketURL"],
                    {
                        id: id
                    },
                    function(data){
                        if(data.error === true) {
                            Tecnotek.showErrorMessage(data.message,true, "", false);
                        } else {
                            $("#ticket_row_" + id).empty().remove();
                        }
                    },
                    function(jqXHR, textStatus){
                        Tecnotek.showErrorMessage("Error saving: " + textStatus + ".",
                            true, "", false);
                    }, true);
            }

        });
    }
};
