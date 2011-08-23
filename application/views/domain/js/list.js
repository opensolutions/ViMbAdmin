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
    }); // document onready


    function toggleActive( id )
    {
        currentStatus = $( '#toggle_active_' + id ).html();
        nextStatus = ( currentStatus == 'Yes' ? 'No' : 'Yes' );

        $( '#toggle_active_' + id ).html( '<img src="{genUrl}/images/throbber.gif" alt="Processing..." title="Processing..." />' );

        $.ajax({
            url: "{genUrl controller='domain' action='ajax-toggle-active'}/did/" + id,
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
