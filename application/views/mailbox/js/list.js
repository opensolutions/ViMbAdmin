<script type="text/javascript"> /* <![CDATA[ */

    var purgeDialog;
    var oDataTable;


    $(document).ready( function()
    {
        oDataTable = $( '#mailbox_list_table' ).dataTable({
                            'iDisplayLength': {$options.defaults.table.entries},
                            "sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
                            "sPaginationType": "bootstrap",
                            'aoColumns': [
                                null,
                                null,
                                null,
                                null,
                                { 'bSortable': false, "bSearchable": false }
                            ]
                        });

        $( 'span[id|="toggle-active"]' ).click( function( event ){
            var id = $( event.target ).attr( 'id' ).substr( $( event.target ).attr( 'id' ).lastIndexOf( '-' ) + 1 );
            ossToggle( $( event.target ), "{genUrl controller='mailbox' action='ajax-toggle-active'}", { "mid": id } );
        });

        $( 'span[id|="send-email"]' ).click( function( event ){

            var id = $( event.target ).attr( 'id' ).substr( $( event.target ).attr( 'id' ).lastIndexOf( '-' ) + 1 );
            $( "#email_name" ).html( $( event.target ).attr( 'ref' ) );

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
        });

    }); // document onready


    function doPurgeMailbox( id )
    {
        $( '#purge_msg' ).html( '<img src="{genUrl}/images/throbber.gif" alt="Processing..." title="Processing..." /> Processing...' );

        $.ajax({
            url: "{genUrl controller='admin' action='ajax-purge'}/mid/" + id,
            async: true,
            cache: false,
            type: 'GET',
            timeout: 3000, // milliseconds
            success: function( data )
                        {
                            if ( data != 'ok' )
                            {
                                $( '#purge_msg' ).html( 'An unexpected error occured. Please try again.' );
                            }
                            else
                            {
                                $('#admin_' + id).hide('fast');
                                purgeDialog.dialog('close');
                                $('#purge_admin_dialog').remove();
                            }
                        },
            error: function( XMLHttpRequest, textStatus, errorThrown )
                        {
                            $( '#purge_msg' ).html( 'An unexpected error occured. Please try again.' );
                        }
        });
    }

/* ]]> */ </script>
