<script type="text/javascript"> /* <![CDATA[ */

    var purgeDialog;
    var oDataTable;


    $(document).ready( function()
    {
        oDataTable = $( '#admin_list_table' ).dataTable({
                            'iDisplayLength': {$options.defaults.table.entries},
                            'aoColumns': [
                                null,
                                null,
                                null,
                                { 'bSortable': false, "bSearchable": false }
                            ]
                        });

        $( 'span[id|="toggle-active"]' ).click( function( event ){
            var id = $( event.target ).attr( 'id' ).substr( $( event.target ).attr( 'id' ).lastIndexOf( '-' ) + 1 );
            ossToggle( $( event.target ), "{genUrl controller='admin' action='ajax-toggle-active'}", { "aid": id } );
        });
    }); // document onready


    function toggleActive( id )
    {
        if( {$identity.admin.id} == id ) return;

        currentStatus = $( '#toggle-active-' + id ).html();
        nextStatus = ( currentStatus == 'Yes' ? 'No' : 'Yes' );

        $( '#toggle-active-' + id ).html( '<img src="{genUrl}/images/throbber.gif" alt="Processing..." title="Processing..." />' );

        $.ajax({
            url: "{genUrl controller='admin' action='ajax-toggle-active'}/aid/" + id,
            async: true,
            cache: false,
            type: 'GET',
            timeout: 3000, // milliseconds
            success: function( data )
                        {
                            if ( data != 'ok' )
                                $('#toggle-active-' + id ).html( currentStatus );
                            else
                                $('#toggle-active-' + id ).html( nextStatus );
                        },
            error: function( XMLHttpRequest, textStatus, errorThrown )
                        {
                            $( '#toggle-active-' + id ).html( currentStatus );
                            alert( 'An unexpected error occured. Please try again.' );
                        }
        });
    }

    function toggleSuper( id )
    {
        if( {$identity.admin.id} == id ) return;

        currentStatus = $( '#toggle_super_' + id ).html();
        nextStatus = ( currentStatus == 'Yes' ? 'No' : 'Yes' );

        $( '#toggle_super_' + id ).html( '<img src="{genUrl}/images/throbber.gif" alt="Processing..." title="Processing..." />' );

        $.ajax({
            url: "{genUrl controller='admin' action='ajax-toggle-super'}/aid/" + id,
            async: true,
            cache: false,
            type: 'GET',
            timeout: 3000, // milliseconds
            success: function( data )
                        {
                            if ( data != 'ok' )
                                $('#toggle_super_' + id ).html( currentStatus );
                            else
                                $('#toggle_super_' + id ).html( nextStatus );
                        },
            error: function( XMLHttpRequest, textStatus, errorThrown )
                        {
                            $( '#toggle_super_' + id ).html( currentStatus );
                            alert( 'An unexpected error occured. Please try again.' );
                        }
        });
    }



    function purgeAdmin( id, username )
    {
        purgeDialog = $( '<div id="purge_admin_dialog"></div>' )
            .html( 'Are you sure you want to purge <strong>' + username + '</strong>?'
                + '<br /><br />All logs and domain associations will be removed. If you simply want to '
                + 'close the user\'s account, deactivate it instead.<br /><br />'
                + '<span id="purge_msg"></span>'
            )
            .dialog({
                dialogClass : 'purge_admin_dialog',
                autoOpen: true,
                title: 'Are you sure?',
                resizable: false,
                modal: true,
                closeOnEscape: false,
                width: 420,
                height: 220,
                buttons: {
                    "Cancel": function() {
                        $(this).dialog("close");
                        $('#purge_admin_dialog').remove();
                    },
                    "Purge": function() {
                        doPurgeAdmin( id );
                    }
                }
        });
    }


    function doPurgeAdmin( id )
    {
        $( '#purge_msg' ).html( '<img src="{genUrl}/images/throbber.gif" alt="Processing..." title="Processing..." /> Processing...' );

        $.ajax({
            url: "{genUrl controller='admin' action='ajax-purge'}/aid/" + id,
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
