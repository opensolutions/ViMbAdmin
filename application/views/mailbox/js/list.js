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

    }); // document onready

    function purgeMailbox( id, email )
    {
        purgeDialog = $( '<div id="purge_mailbox_dialog"></div>' )
            .html( 'Are you sure you want to purge <b>' + email + '</b> and all of its aliases?'
                + '<br /><br />'
                + '<span id="purge_msg"></span>'
            )
            .dialog({
                dialogClass : 'purge_mailbox_dialog',
                autoOpen: true,
                title: 'Are you sure?',
                resizable: false,
                modal: true,
                closeOnEscape: false,
                width: 400,
                height: 170,
                buttons: {
                    "Cancel": function() {
                        $(this).dialog("close");
                        $('#purge_mailbox_dialog').remove();
                    },
                    "Delete": function() {
                        doPurgeMailbox( id );
                    }
                }
        });
    }


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
