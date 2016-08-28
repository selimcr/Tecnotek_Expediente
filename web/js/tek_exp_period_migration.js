var Tecnotek = Tecnotek || {};

Tecnotek.periodMigration = {
    init : function() {

        Tecnotek.periodMigration.initButtons();
    },
    initButtons : function() {
        $('.btn-step').click(function(event){
            event.preventDefault();
            event.stopPropagation();
            var $this = $(this);
            var $step = $this.attr("rel");
            if (!$this.is(":disabled")) {
                if (Tecnotek.showConfirmationQuestion(Tecnotek.UI.translates['migration-step-confirmation'])) {
                    Tecnotek.periodMigration.executeStep($step);
                }
            }
            return false;
        });
    },
    executeStep: function($step) {
        Tecnotek.ajaxCall(Tecnotek.UI.urls["execute-migration-step"],
            {
                migrationId: Tecnotek.UI.vars["migration-id"],
                step: $step,
                periodSourceId: Tecnotek.UI.vars["period-source-id"],
                periodDestinationId: Tecnotek.UI.vars["period-destination-id"]
            },
            function(data){
                if(data.error === true) {
                    Tecnotek.showErrorMessage(data.message,true, "", false);
                } else {
                    Tecnotek.UI.vars["migration-id"] = data.migrationId;
                    Tecnotek.periodMigration.updateUIAfterStepExecution($step);
                    Tecnotek.showInfoMessage("Se ha ejecutado correctamente el paso #" + $step, true, '', false);
                }
            },
            function(jqXHR, textStatus){
                Tecnotek.showErrorMessage("Error executing action: " + textStatus + ".", true, "", false);
            }, true);
    },
    updateUIAfterStepExecution: function($step) {
        $("#step-action-" + $step).html(Tecnotek.UI.translates['label-completed']);
        switch ($step) {
            case "1":
                $("#btn-step-2").prop('disabled', false);
                $("#btn-step-3").prop('disabled', false);
                break;
            case "3":
                $("#btn-step-4").prop('disabled', false);
                $("#btn-step-5").prop('disabled', false);
                $("#btn-step-6").prop('disabled', false);
                break;
            case "6":
                $("#btn-step-7").prop('disabled', false);
            default: // We don't know the step or we don't have something to do
                break;
        }
    }
};
