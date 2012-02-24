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

        $( 'span[id|="toggle-super"]' ).click( function( event ){
            var id = $( event.target ).attr( 'id' ).substr( $( event.target ).attr( 'id' ).lastIndexOf( '-' ) + 1 );
            ossToggle( $( event.target ), "{genUrl controller='admin' action='ajax-toggle-super'}", { "aid": id } );
        });

        $( 'span[id|="purge-admin"]' ).click( function( event ){

            var id = $( event.target ).attr( 'id' ).substr( $( event.target ).attr( 'id' ).lastIndexOf( '-' ) + 1 );
            $( "#purge_admin_name" ).html( $( event.target ).attr( 'ref' ) );

            delDialog = $( '#purge_dialog' ).modal({
                backdrop: true,
                keyboard: true,
                show: true
            });

            $( '#purge_dialog_delete' ).unbind().bind( 'click', function(){
                doPurgeAdmin( id );
            });
            $( '#purge_dialog_cancel' ).click( function(){
                delDialog.modal('hide');
            });
        });
    }); // document onready

    function doPurgeAdmin( id )
    {
        var Throb = tt_throbber( 32, 14, 1.8 ).appendTo( $( '#pdfooter' ).get(0) ).start();

        $( '#purge_dialog_delete' ).attr( 'disabled', 'disabled' ).addClass( 'disabled' );
        $( '#purge_dialog_cancel' ).attr( 'disabled', 'disabled' ).addClass( 'disabled' );

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
                                ossAddMessage( 'An unexpected error has occured.', 'error' );
                            }
                            else
                            {
                                $('#admin_' + id).hide('fast');
                                ossAddMessage( 'You have successfully purged the admin record.', 'success' );
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
