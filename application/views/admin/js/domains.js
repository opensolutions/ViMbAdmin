<script type="text/javascript"> /* <![CDATA[ */

    var removeDialog;
    var oDataTable;


    $(document).ready( function()
    {
        oDataTable = $( '#list_table' ).dataTable({
            'fnDrawCallback': function() {
                if( vm_prefs['iLength'] !=  $( "select[name|='list_table_length']" ).val() )
                    vm_prefs['iLength'] = $( "select[name|='list_table_length']" ).val();

                $.jsonCookie( 'vm_prefs', vm_prefs, vm_cookie_options );
            },
            'iDisplayLength': vm_prefs['iLength']? vm_prefs['iLength']: {$options.defaults.table.entries},
            "sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
            "sPaginationType": "bootstrap",
            'aoColumns': [
                null,
                { 'bSortable': false, "bSearchable": false }
            ]
        });

    }); // document onready

    function domainRemove(id, domain){

        $( "#purge_domain_name" ).html( domain );
        $( "#purge_admin_name" ).html( "{$targetAdmin->username}" );

        delDialog = $( '#purge_dialog' ).modal({
            backdrop: true,
            keyboard: true,
            show: true
        });

        $( '#purge_dialog_delete' ).unbind().bind( 'click', function(){
            doRemoveAdmin( id, domain );
        });
        $( '#purge_dialog_cancel' ).click( function(){
            delDialog.modal('hide');
        });
    };

    function doRemoveAdmin( domainId, domain )
    {
        var Throb = tt_throbber( 32, 14, 1.8 ).appendTo( $( '#pdfooter' ).get(0) ).start();

        $( '#purge_dialog_delete' ).attr( 'disabled', 'disabled' ).addClass( 'disabled' );
        $( '#purge_dialog_cancel' ).attr( 'disabled', 'disabled' ).addClass( 'disabled' );

        $.ajax({
            url: "{genUrl controller='admin' action='ajax-remove-domain'}/aid/{$targetAdmin.id}/domain/" + domainId,
            async: true,
            cache: false,
            type: 'GET',
            timeout: 3000, // milliseconds
            success: function( data )
                        {
                            if ( data != 'ok' )
                            {
                                 ossAddMessage( 'An unexpected error has occured.', 'error' );
                            }
                            else
                            {
                                $('#domain_' + domainId).hide( 'fast' );
                                ossAddMessage( 'You have successfully removed the admin from domain <em>' + domain + '</em>.', 'success' );
                            }
                            delDialog.modal('hide');
                        },
            error: ossAjaxErrorHandler,
            complete: function()
                        {
                            $( '#purge_dialog_delete' ).removeAttr( 'disabled' ).removeClass( 'disabled' );
                            $( '#purge_dialog_cancel' ).removeAttr( 'disabled' ).removeClass( 'disabled' );
                            if( $('canvas').length ){
                                $('canvas').remove();
                            }
                        }
        });

    }

/* ]]> */ </script>
