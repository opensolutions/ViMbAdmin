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

        $( 'span[id|="domain-purge"]' ).click( function( event ){

            var id = $( event.target ).attr( 'id' ).substr( $( event.target ).attr( 'id' ).lastIndexOf( '-' ) + 1 );
            $( "#purge_domain_name" ).html( $( event.target ).attr( 'ref' ) );

            delDialog = $( '#purge_dialog' ).modal({
                backdrop: true,
                keyboard: true,
                show: true
            });

            $( '#purge_dialog_delete' ).click( function(){
                doPurgeDomain( id );
            });
            $( '#purge_dialog_cancel' ).click( function(){
                delDialog.modal('hide');
            });
        });

    }); // document onready


    function doPurgeDomain( id )
    {
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
                             ossAddMessage( 'An unexpected error has occured.', 'error' );
                         }
                         else
                         {
                             $('#domain_' + id).hide('fast');
                             ossAddMessage( 'You have successfully purged the domain record.', 'success' );
                         }

                         delDialog.modal('hide');
                     },
            error:  ossAjaxErrorHandler
        });
    }

/* ]]> */ </script>
