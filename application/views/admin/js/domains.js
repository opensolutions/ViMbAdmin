<script type="text/javascript"> /* <![CDATA[ */

    var removeDialog;
    var oDataTable;


    $(document).ready( function()
    {
        oDataTable = $( '#admin_domain_list_table' ).dataTable({
                            'iDisplayLength': {$options.defaults.table.entries},
                            'aoColumns': [
                                null,
                                { 'bSortable': false, "bSearchable": false }
                            ]
                        });
    }); // document onready


    function removeAdmin( domainId, adminId, domain )
    {
        removeDialog = $( '<div id="remove_admin_dialog"></div>' )
            .html( 'Are you sure you want to remove <b>{$targetAdmin->username}</b> from <b>' + domain + '</b>?'
                + '<br /><br />'
                + '<span id="remove_msg"></span>'
            )
            .dialog({
                dialogClass : 'remove_admin_dialog',
                autoOpen: true,
                title: 'Are you sure?',
                resizable: false,
                modal: true,
                closeOnEscape: false,
                width: 500,
                height: 180,
                buttons: {
                    "Cancel": function() {
                        $(this).dialog("close");
                        $('#remove_admin_dialog').remove();
                    },
                    "Remove": function() {
                        doRemoveAdmin( domainId, adminId );
                    }
                }
        });
    }


    function doRemoveAdmin( domainId, adminId )
    {
        $( '#remove_msg' ).html( '<img src="{genUrl}/images/throbber.gif" alt="Processing..." title="Processing..." /> Processing...' );

        $.ajax({
            url: "{genUrl controller='admin' action='ajax-remove-domain'}/aid/" + adminId + '/domain/' + domainId,
            async: true,
            cache: false,
            type: 'GET',
            timeout: 3000, // milliseconds
            success: function( data )
                        {
                            if ( data != 'ok' )
                            {
                                $( '#remove_msg' ).html( 'An unexpected error occured. Please try again.' );
                            }
                            else
                            {
                                $('#domain_' + domainId).hide('fast');
                                removeDialog.dialog('close');
                                $('#remove_admin_dialog').remove();
                            }
                        },
            error: function( XMLHttpRequest, textStatus, errorThrown )
                        {
                            $( '#remove_msg' ).html( 'An unexpected error occured. Please try again.' );
                        }
        });

    }

/* ]]> */ </script>
