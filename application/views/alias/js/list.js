<script type="text/javascript"> /* <![CDATA[ */

    var oDataTable;
    var deleteDialog;


    $(document).ready( function()
    {
        oDataTable = $( '#domain_aliases_table' ).dataTable({
                            'iDisplayLength': {$options.defaults.table.entries},
                            "sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
                            "sPaginationType": "bootstrap",
                            'aoColumns': [
                                null,
                                null,
                                null,
                                null,
                                { 'bSortable': false, "bSearchable": false }
                            ]
                        });

        $('#ima').bind( 'click', function(e) {

            if( $('#ima').hasClass( 'active' ) )
                document.location.href = "{genUrl controller='alias' action='list' did=$domain.id|int ima=0}";
            else
                document.location.href = "{genUrl controller='alias' action='list' did=$domain.id|int ima=1}";
        });

        $( 'span[id|="toggle-active"]' ).click( function( event ){
            var id = $( event.target ).attr( 'id' ).substr( $( event.target ).attr( 'id' ).lastIndexOf( '-' ) + 1 );
            ossToggle( $( event.target ), "{genUrl controller='alias' action='ajax-toggle-active'}", { "alid": id } );
        });

        $( 'span[id|="delete-alias"]' ).click( function( event ){
            var id = $( event.target ).attr( 'id' ).substr( $( event.target ).attr( 'id' ).lastIndexOf( '-' ) + 1 );

            delDialog = $( '#purge_dialog' ).modal({
                backdrop: true,
                keyboard: true,
                show: true
            });

            $( '#purge_dialog_delete' ).unbind().bind( 'click', function(){
                doDeleteAlias( id );
            });

            $( '#purge_dialog_cancel' ).click( function(){
                delDialog.modal('hide');
            });
        });

    }); // document onready


    function doDeleteAlias( id )
    {
        var Throb = tt_throbber( 32, 14, 1.8 ).appendTo( $( '#pdfooter' ).get(0) ).start();

        $( '#purge_dialog_delete' ).attr( 'disabled', 'disabled' ).addClass( 'disabled' );
        $( '#purge_dialog_cancel' ).attr( 'disabled', 'disabled' ).addClass( 'disabled' );

        $.ajax({
            url: "{genUrl controller='alias' action='ajax-delete'}/alid/" + id,
            async: true,
            cache: false,
            type: 'POST',
            timeout: 3000, // milliseconds
            success: function( data )
                        {
                            if ( data != 'ok' )
                            {
                                ossAddMessage( 'An unexpected error has occured.', 'error' );
                            }
                            else
                            {
                                $('#alias_' + id).hide('fast');
                                delDialog.modal('hide');
                                ossAddMessage( 'Alias has bean removed successfully', 'succsess' );
                            }
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
