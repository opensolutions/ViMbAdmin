<script type="text/javascript"> /* <![CDATA[ */

    var deleteDialog;
    var oDataTable;


    $(document).ready( function()
    {
        oDataTable = $( '#mailbox_aliases_table' ).dataTable({
                            'iDisplayLength': {$options.defaults.table.entries},
                            'aoColumns': [
                                null,
                                null,
                                { 'bSortable': false, "bSearchable": false }
                            ]
                        });

        $('#ima').bind( 'click', function(e) {
            document.location.href = "{genUrl controller='mailbox' action='aliases' mid=$mailboxModel.id ima=$includeMailboxAliases|flipflop}";
        });
    }); // document onready


    function deleteAlias( aliasId, mailboxId, address )
    {
        purgeDialog = $( '<div id="delete_alias_dialog"></div>' )
            .html( 'Are you sure you want to remove ' + address + ' from this entry?'
                + '<br /><br />'
                + '<span id="delete_msg"></span>'
            )
            .dialog({
                dialogClass : 'delete_alias_dialog',
                autoOpen: true,
                title: 'Are you sure?',
                resizable: false,
                modal: true,
                closeOnEscape: false,
                width: 450,
                height: 180,
                buttons: {
                    "Cancel": function() {
                        $(this).dialog("close");
                        $('#delete_alias_dialog').remove();
                    },
                    "Remove": function() {
                        doDeleteAlias( aliasId, mailboxId );
                    }
                }
        });
    }


    function doDeleteAlias( aliasId, mailboxId )
    {
        $( '#delete_msg' ).html( '<img src="{genUrl}/images/throbber.gif" alt="Processing..." title="Processing..." /> Processing...' );

        $.ajax({
            url: "{genUrl controller='mailbox' action='ajax-delete-alias'}/mid/" + mailboxId + '/alid/' + aliasId,
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
                                //$('#alias_' + aliasId).hide('fast');
                                //purgeDialog.dialog('close');
                                //$('#purge_admin_dialog').remove();
                                document.location.reload();
                            }
                        },
            error: function( XMLHttpRequest, textStatus, errorThrown )
                        {
                            $( '#purge_msg' ).html( 'An unexpected error occured. Please try again.' );
                        }
        });
    }

/* ]]> */ </script>
