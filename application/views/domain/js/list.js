<script type="text/javascript"> /* <![CDATA[ */

    var delDialog;
    var oDataTable;


    $(document).ready(function()
    {
        oDataTable = $('#domain_list_table').dataTable({
            'iDisplayLength': {$options.defaults.table.entries},
            "sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
            "sPaginationType": "bootstrap",
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

    function toggleActive( elid, id){
        ossToggle( $( '#' + elid ), "{genUrl controller='domain' action='ajax-toggle-active'}", { "did": id } );
    };


    function purgeDomain( id, domain )
    {
        $( "#purge_domain_name" ).html( domain );

        delDialog = $( '#purge_dialog' ).modal({
            backdrop: true,
            keyboard: true,
            show: true
        });

        $( '#purge_dialog_delete' ).unbind().bind( 'click', function(){
            doPurgeDomain( id );
        });
        $( '#purge_dialog_cancel' ).click( function(){
            delDialog.modal('hide');
        });
    };

    function doPurgeDomain( id )
    {
        var Throb = tt_throbber( 32, 14, 1.8 ).appendTo( $( '#pdfooter' ).get(0) ).start();

        $( '#purge_dialog_delete' ).attr( 'disabled', 'disabled' ).addClass( 'disabled' );
        $( '#purge_dialog_cancel' ).attr( 'disabled', 'disabled' ).addClass( 'disabled' );

        $.ajax({
            url: "{genUrl controller='domain' action='ajax-purge'}/did/" + id,
            async: true,
            cache: false,
            type: 'POST',
            timeout: 4000, // milliseconds
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
