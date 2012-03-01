<script type="text/javascript"> /* <![CDATA[ */

    var purgeDialog;
    var oDataTable;


    $(document).ready( function()
    {
        oDataTable = $( '#mailbox_list_table' ).dataTable({
                            'iDisplayLength': {$options.defaults.table.entries},
                            "sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
                            "sPaginationType": "bootstrap",
                            'bStateSave': true,
                            'aoColumns': [
                                null,
                                null,
                                null,
                                null,
                                { 'bSortable': false, "bSearchable": false }
                            ]
                        });

    }); // document onready


    function toggleActive(elid, id){

        ossToggle( $( '#' + elid ), "{genUrl controller='mailbox' action='ajax-toggle-active'}", { "mid": id } );
    };

    function sendEmail( id, email ) {
        $( "#email_name" ).html( email );

        elDialog = $( '#email_dialog' ).modal({
            backdrop: true,
            keyboard: true,
            show: true
        });

        $( '#email_dialog_send' ).unbind().bind( 'click', function(){
            elDialog.modal('hide');
            window.location.href = "{genUrl controller='mailbox' action='email-settings'}/mid/" + id;

        });
        $( '#email_dialog_cancel' ).click( function(){
            elDialog.modal('hide');
        });
    };

/* ]]> */ </script>
