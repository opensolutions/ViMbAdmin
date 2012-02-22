<script type="text/javascript"> /* <![CDATA[ */

    var delDialog;
    var oDataTable;


    $(document).ready(function()
    {
        oDataTable = $('#domain_list_table').dataTable({
                            'iDisplayLength': {$options.defaults.table.entries},
                            'aoColumns': [
                                null,
                                { 'sType': 'num-html' },
                                { 'sType': 'num-html' },
                                null,
                                null,
                                null,
                                null,
                                { 'bSortable': false, "bSearchable": false }
                            ]
                        });

        $( 'span[id|="toggle-active"]' ).click( function( event ){
            var id = $( event.target ).attr( 'id' ).substr( $( event.target ).attr( 'id' ).lastIndexOf( '-' ) + 1 );
            ossToggle( $( event.target ), "{genUrl controller='domain' action='ajax-toggle-active'}", { "did": id } );
        });

    }); // document onready


    function toggleActive( id )
    {
        currentStatus = $( '#toggle-active-' + id ).html();
        nextStatus = ( currentStatus == 'Yes' ? 'No' : 'Yes' );

        $( '#toggle-active-' + id ).html( '<img src="{genUrl}/images/throbber.gif" alt="Processing..." title="Processing..." />' );

        $.ajax({
            url: "{genUrl controller='domain' action='ajax-toggle-active'}/did/" + id,
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


   function purgeDomain( domainId, domain )
   {
        purgeDialog = $( '<div id="purge_domain_dialog"></div>' )
            .html( 'Are you sure you want to purge <b>' + domain + '</b>?'
                + '<br /><br />All mailboxes, aliases and logs will be removed.'
                + '<br /><br />'
                + '<span id="purge_msg"></span>'
            )
            .dialog({
                dialogClass : 'purge_domain_dialog',
                autoOpen: true,
                title: 'Are you sure?',
                resizable: false,
                modal: true,
                closeOnEscape: false,
                width: 400,
                height: 200,
                buttons: {
                    "Cancel": function() {
                        $(this).dialog("close");
                        $('#purge_domain_dialog').remove();
                    },
                    "Purge": function() {
                        doPurgeDomain( domainId );
                    }
                }
        });
    }


    function doPurgeDomain( id )
    {
        $( '#purge_msg' ).html( '<img src="{genUrl}/images/throbber.gif" alt="Processing..." title="Processing..." /> Processing...' );

        $.ajax({
            url: "{genUrl controller='domain' action='ajax-purge'}/did/" + id,
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
                                $('#domain_' + id).hide('fast');
                                purgeDialog.dialog('close');
                                $('#purge_domain_dialog').remove();
                            }
                        },
            error: function( XMLHttpRequest, textStatus, errorThrown )
                        {
                            $( '#purge_msg' ).html( 'An unexpected error occured. Please try again.' );
                        }
        });
    }

/* ]]> */ </script>
