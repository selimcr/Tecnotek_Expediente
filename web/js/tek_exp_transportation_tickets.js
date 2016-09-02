var Tecnotek = Tecnotek || {};

Tecnotek.TransportationTicket = {
    translates : {},
    init : function() {
        $('#searchText').keyup(function(event){
            Tecnotek.UI.vars["page"] = 1;
            Tecnotek.TransportationTicket.searchTickets();
        });
        $('#btnSearch').unbind().click(function(event){
            Tecnotek.TransportationTicket.searchTickets();
        });
        $("#state").unbind().change(function(e){
            Tecnotek.TransportationTicket.loadCantones();
            Tecnotek.TransportationTicket.searchTickets();
        });
        $(".sort_header").click(function() {
            Tecnotek.UI.vars["sortBy"] = $(this).attr("field-name");
            Tecnotek.UI.vars["order"] = $(this).attr("order");
            $(this).attr("order", Tecnotek.UI.vars["order"] == "asc"? "desc":"asc");
            $(".header-title").removeClass("asc").removeClass("desc").addClass("sortable");
            $(this).children().addClass(Tecnotek.UI.vars["order"]);
            Tecnotek.TransportationTicket.searchTickets();
        });
        Tecnotek.UI.vars["order"] = "asc";
        Tecnotek.UI.vars["sortBy"] = "carne";
        Tecnotek.UI.vars["page"] = 1;
        Tecnotek.TransportationTicket.searchTickets();
    },
    loadCantones: function() {
        $("#canton").unbind();
        $('#canton').empty()
            .append('<option selected="selected" value="0">Todos</option>');
        $("#district").unbind();
        $('#district').empty()
            .append('<option selected="selected" value="0">Todos</option>');
        Tecnotek.ajaxGetCall(Tecnotek.UI.urls["load-cantones"],
            {state: $("#state").val()},
            function(data){
                if(data.code === 200) {
                    for(var i=0; i <data.cantones.length; i++) {
                        $('#canton').append('<option value="' + data.cantones[i].id + '">' + data.cantones[i].name + '</option>');
                    }
                    $("#canton").change(function(e){
                        Tecnotek.TransportationTicket.loadDistricts();
                        Tecnotek.TransportationTicket.searchTickets();
                    });
                    Tecnotek.hideWaiting();
                    //$("#new-relative-btn").hide();
                } else {
                    Tecnotek.showErrorMessage("No se han podido cargar los cantones",true, "", false);
                    Tecnotek.hideWaiting();
                }
            },
            function(jqXHR, textStatus){
                if (textStatus != "abort") {
                    Tecnotek.hideWaiting();
                    console.debug("Error getting data: " + textStatus);
                }
            }, true);
    },
    loadDistricts: function() {
        $("#district").unbind();
        $('#district').empty()
            .append('<option selected="selected" value="0">Todos</option>');
        Tecnotek.ajaxGetCall(Tecnotek.UI.urls["load-districts"],
            {canton: $("#canton").val()},
            function(data){
                if(data.code === 200) {
                    for(var i=0; i <data.districts.length; i++) {
                        $('#district').append('<option value="' + data.districts[i].id + '">' + data.districts[i].name + '</option>');
                    }
                    $("#district").change(function(e){
                        Tecnotek.TransportationTicket.searchTickets();
                    });
                    Tecnotek.hideWaiting();
                    //$("#new-relative-btn").hide();
                } else {
                    Tecnotek.showErrorMessage("No se han podido cargar los distritos",true, "", false);
                    Tecnotek.hideWaiting();
                }
            },
            function(jqXHR, textStatus){
                if (textStatus != "abort") {
                    Tecnotek.hideWaiting();
                    console.debug("Error getting data: " + textStatus);
                }
            }, true);
    },
    searchTickets: function() {
        $("#students-container").html("");
        $("#pagination-container").html("");
        Tecnotek.showWaiting();
        Tecnotek.uniqueAjaxCall(Tecnotek.UI.urls["search"],
            {
                text: $("#searchText").val(),
                state: $("#state").val(),
                canton: $("#canton").val(),
                district: $("#district").val(),
                sortBy: Tecnotek.UI.vars["sortBy"],
                order: Tecnotek.UI.vars["order"],
                page: Tecnotek.UI.vars["page"]
            },
            function(data){
                if(data.error === true) {
                    Tecnotek.hideWaiting();
                    Tecnotek.showErrorMessage(data.message,true, "", false);
                    //$("#new-relative-btn").hide();
                } else {
                    var baseHtml = $("#studentRowTemplate").html();
                    /*$data = "";
                    $data += '<p id="searchresults">';
                    $data += '    <span class="category">Estudiantes</span>';*/
                    for(i=0; i<data.tickets.length; i++) {
                        //console.debug(data.students[i]);
                        var row = '<div id="studentRowTemplate" class="row userRow ROW_CLASS" rel="STUDENT_ID">' +
                            baseHtml + '</div>';
                        row = row.replaceAll('ROW_CLASS', (i % 2 == 0? 'tableRowOdd':'tableRow'));
                        row = row.replaceAll('TICKET_ID', data.tickets[i].id);
                        row = row.replaceAll('TICKET_DATE', data.tickets[i].date);
                        row = row.replaceAll('STUDENT_FULLNAME', data.tickets[i].fullName);
                        row = row.replaceAll('TICKET_ADDRESS', data.tickets[i].fullAddress);
                        row = row.replaceAll('TICKET_TYPE', Tecnotek.UI.translates["ticket-type-"+data.tickets[i].service]);


                        /*if (data.students[i].gender == 1) {
                            row = row.replaceAll('STUDENT_GENDER', "Hombre");
                        } else {
                            row = row.replaceAll('STUDENT_GENDER', "Mujer");
                        }*/

                        $("#students-container").append(row);
                    }
                    Tecnotek.AdministratorList.initButtons();
                    Tecnotek.UI.printPagination(data.total, data.filtered, Tecnotek.UI.vars["page"], 30, "pagination-container");
                    $(".paginationButton").unbind().click(function() {
                        Tecnotek.UI.vars["page"] = $(this).attr("page");
                        Tecnotek.Students.searchStudents();
                    });
                    Tecnotek.hideWaiting();
                    //$data += '</p>';
                }
            },
            function(jqXHR, textStatus){
                if (textStatus != "abort") {
                    Tecnotek.hideWaiting();
                    console.debug("Error getting data: " + textStatus);
                }
            }, true, 'searchStudents');
    }
};
