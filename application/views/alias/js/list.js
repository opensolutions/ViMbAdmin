<script type="text/javascript"> /* <![CDATA[ */

    var oDataTable;
    var deleteDialog;


    $(document).ready( function()
    {
        oDataTable = $( '#domain_aliases_table' ).dataTable({
                            'iDisplayLength': {$options.defaults.table.entries},
                            'aoColumns': [
                                null,
                                null,
                                null,
                                null,
                                { 'bSortable': false, "bSearchable": false }
                            ]
                        });

        $('#ima').bind( 'click', function(e) {
            document.location.href = "{genUrl controller='alias' action='list' did=$domain.id|int ima=$includeMailboxAliases|flipflop}";
        });
    }); // document onready


    function toggleActive( id )
    {
        currentStatus = $( '#toggle_active_' + id ).html();
        nextStatus = ( currentStatus == 'Yes' ? 'No' : 'Yes' );

        $( '#toggle_active_' + id ).html( '<img src="{genUrl}/images/throbber.gif" alt="Processing..." title="Processing..." />' );

        $.ajax({
            url: "{genUrl controller='alias' action='ajax-toggle-active'}/alid/" + id,
            async: true,
            cache: false,
            type: 'GET',
            timeout: 3000, // milliseconds
            success: function( data )
                        {
                            if ( data != 'ok' )
                                $('#toggle_active_' + id ).html( currentStatus );
                            else
                                $('#toggle_active_' + id ).html( nextStatus );
                        },
            error: function( XMLHttpRequest, textStatus, errorThrown )
                        {
                            $( '#toggle_active_' + id ).html( currentStatus );
                            alert( 'An unexpected error occured. Please try again.' );
                        }
        });
    }


   function deleteAlias( id )
   {
        deleteDialog = $( '<div id="delete_alias_dialog"></div>' )
            .html( 'Are you sure you want to delete this alias?'
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
                width: 350,
                height: 160,
                buttons: {
                    "Cancel": function() {
                        $(this).dialog("close");
                        $('#delete_alias_dialog').remove();
                    },
                    "Delete": function() {
                        doDeleteAlias( id );
                    }
                }
        });
    }


    function doDeleteAlias( id )
    {
        $( '#delete_msg' ).html( '<img src="{genUrl}/images/throbber.gif" alt="Processing..." title="Processing..." /> Processing...' );

        $.ajax({
            url: "{genUrl controller='alias' action='ajax-delete'}/alid/" + id,
            async: true,
            cache: false,
            type: 'GET',
            timeout: 3000, // milliseconds
            success: function( data )
                        {
                            if ( data != 'ok' )
                            {
                                $( '#delete_msg' ).html( 'An unexpected error occured. Please try again.' );
                            }
                            else
                            {
                                $('#alias_' + id).hide('fast');
                                deleteDialog.dialog('close');
                                $('#delete_alias_dialog').remove();
                            }
                        },
            error: function( XMLHttpRequest, textStatus, errorThrown )
                        {
                            $( '#purge_msg' ).html( 'An unexpected error occured. Please try again.' );
                        }
        });
    }

/* ]]> */ </script>
