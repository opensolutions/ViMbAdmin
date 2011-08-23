<script type="text/javascript"> /* <![CDATA[ */

    var removeDialog;
    var addDialog;
    var oDataTable;


    $(document).ready( function()
    {
        addDialog = $('#add_admin_dialog')
                            .dialog({
                                dialogClass : 'add_admin_dialog',
                                autoOpen: false,
                                title: 'Add Admin',
                                resizable: false,
                                modal: true,
                                closeOnEscape: false,
                                width: 450,
                                height: 160,
                                buttons: {
                                    "Cancel": function() {
                                        $(this).dialog("close");
                                    },
                                    "Add": function() {
                                        doAddAdmin( {$domainModel.id} );
                                    }
                                }
                            });

        oDataTable = $( '#admin_list_table' ).dataTable({
                            'iDisplayLength': {$options.defaults.table.entries},
                            'aoColumns': [
                                null,
                                { 'bSortable': false, "bSearchable": false }
                            ]
                        });
    }); // document onready


    function removeAdmin( domainId, adminId , username)
    {
        removeDialog = $('<div id="remove_admin_dialog"></div>')
                            .html('Are you sure you want to remove <b>' + username + '</b>?\
                                <br /' + '><br /' + '>\
                                <span id="remove_msg"></span>')
                            .dialog({
                                dialogClass : 'remove_admin_dialog',
                                autoOpen: true,
                                title: 'Remove Admin?',
                                resizable: false,
                                modal: true,
                                closeOnEscape: false,
                                width: 550,
                                height: 160,
                                buttons: {
                                    "Cancel": function() {
                                        $(this).dialog("close");
                                        $('#remove_admin_dialog').remove();
                                    },
                                    "Delete": function() {
                                        doRemoveAdmin( domainId, adminId );
                                    }
                                }
                            });
    }


    function doRemoveAdmin( domainId, adminId )
    {
        $( '#remove_msg' ).html( '<img src="{genUrl}/images/throbber.gif" alt="Processing..." title="Processing..." /> Processing...' );

        $.ajax({
            url: "{genUrl controller='domain' action='ajax-remove-admin'}/did/" + domainId + '/aid/' + adminId,
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
                                /*
                                $( '#remove_msg' ).html( '' );
                                $( '#admin_' + adminId ) . hide( 'fast' );
                                removeDialog.dialog( 'close' );
                                $('#remove_admin_dialog').remove();
                                */

                                document.location.reload();
                            }
                        },
            error: function( XMLHttpRequest, textStatus, errorThrown )
                        {
                            $( '#remove_msg' ).html( 'An unexpected error occured. Please try again.' );
                        }
        });
    }


    function addAdmin( )
    {
        addDialog.dialog( 'open' );
    }


    function doAddAdmin( domainId )
    {
        if( $( '#admin_list' ).val() == null )
        {
            $( '#add_admin_msg' ).html( '<span class="red">No admin selected.</span>' );
            return;
        }

        $( '#add_admin_msg' ).html( '<img src="{genUrl}/images/throbber.gif" alt="Processing..." title="Processing..." /> Processing...' );

        $.ajax({
            url: "{genUrl controller='domain' action='ajax-add-admin'}/did/" + domainId + '/aid/' + $( '#admin_list' ).val(),
            async: true,
            cache: false,
            type: 'GET',
            timeout: 3000, // milliseconds
            success: function( data )
                        {
                            if (data != 'ok')
                            {
                                $( '#add_admin_msg' ).html( '<span class="red">An unexpected error occured. Please try again.</span>' );
                            }
                            else
                            {
                                document.location.reload();
                            }
                        },
            error: function( XMLHttpRequest, textStatus, errorThrown )
                        {
                            $( '#add_admin_msg' ).html( '<span class="red">An unexpected error occured. Please try again.</span>' );
                        }
        });

    }

/* ]]> */ </script>
