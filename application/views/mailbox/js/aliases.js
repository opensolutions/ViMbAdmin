<script type="text/javascript"> /* <![CDATA[ */

    var deleteDialog;
    var oDataTable;


    $(document).ready( function()
    {
        oDataTable = $( '#mailbox_aliases_table' ).dataTable({
                            'iDisplayLength': {$options.defaults.table.entries},
                            "sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
                            "sPaginationType": "bootstrap",
                            'aoColumns': [
                                null,
                                null,
                                { 'bSortable': false, "bSearchable": false }
                            ]
                        });

        $('#ima').bind( 'click', function(e) {

            if( $('#ima').hasClass( 'active' ) )
                document.location.href = "{genUrl controller='mailbox' action='aliases' mid=$mailboxModel.id ima=0}";
            else
                document.location.href = "{genUrl controller='mailbox' action='aliases' mid=$mailboxModel.id ima=1}";
        });

        $( 'span[id|="delete-alias"]' ).click( function( event ){
            var id = $( event.target ).attr( 'id' ).substr( $( event.target ).attr( 'id' ).lastIndexOf( '-' ) + 1 );
            var data = $( event.target ).attr( 'ref' ).split( "/" );
            $( "#purge_alias_name" ).html( data[0] );

            delDialog = $( '#purge_dialog' ).modal({
                backdrop: true,
                keyboard: true,
                show: true
            });

            $( '#purge_dialog_delete' ).unbind().bind( 'click', function(){
                doDeleteAlias( id, data[1] );
            });
            $( '#purge_dialog_cancel' ).click( function(){
                delDialog.modal('hide');
            });
        });
    }); // document onready



    function doDeleteAlias( aliasId, mailboxId )
    {

        var Throb = tt_throbber( 32, 14, 1.8 ).appendTo( $( '#pdfooter' ).get(0) ).start();

        $( '#purge_dialog_delete' ).attr( 'disabled', 'disabled' ).addClass( 'disabled' );
        $( '#purge_dialog_cancel' ).attr( 'disabled', 'disabled' ).addClass( 'disabled' );

        $.ajax({
            url: "{genUrl controller='mailbox' action='ajax-delete-alias'}/mid/" + mailboxId + '/alid/' + aliasId,
            async: true,
            cache: false,
            type: 'POST',
            timeout: 3000, // milliseconds
            success: function( data )
                        {
                            delDialog.modal('hide');
                            if ( data != 'ok' )
                            {
                                ossAddMessage( 'An unexpected error has occured.', 'error' );
                            }
                            else
                            {
                                location.reload();
                            }
                        },
            error:      ossAjaxErrorHandler,
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
