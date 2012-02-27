<script type="text/javascript"> /* <![CDATA[ */

    var purgeDialog;
    var oDataTable;


    $(document).ready( function()
    {
        oDataTable = $( '#admin_list_table' ).dataTable({
                            'iDisplayLength': {$options.defaults.table.entries},
                            "sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
                            "sPaginationType": "bootstrap",
                            'aoColumns': [
                                null,
                                null,
                                null,
                                { 'bSortable': false, "bSearchable": false }
                            ]
                        });

    }); // document onready


    function toggleActive( elid, id ){
        ossToggle( $( '#' + elid ), "{genUrl controller='admin' action='ajax-toggle-active'}", { "aid": id } );
    };

    function toggleSuper( elid, id ){
        ossToggle( $( '#' + elid ), "{genUrl controller='admin' action='ajax-toggle-super'}", { "aid": id } );
    };

    function purgeAdmin( id, admin ){
        $( "#purge_admin_name" ).html( admin );

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
    };


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
