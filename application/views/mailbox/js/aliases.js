<script type="text/javascript"> /* <![CDATA[ */

    var deleteDialog;
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
            'bStateSave': true,
            "sCookiePrefix": "ViMbAdmin_DataTables_",
            'aoColumns': [
                null,
                null,
                { 'bSortable': false, "bSearchable": false }
            ]
        });

        $('#ima').bind( 'click', function(e) {
            if( $('#ima > i').hasClass( 'icon-eye-close' ) )
                document.location.href = "{genUrl controller='mailbox' action='aliases' mid=$mailboxModel.id ima=0}";
            else
                document.location.href = "{genUrl controller='mailbox' action='aliases' mid=$mailboxModel.id ima=1}";
        });

    }); // document onready

    function deleteAlias( alid, mid, alias ) {

        $( "#purge_alias_name" ).html( alias );

        delDialog = $( '#purge_dialog' ).modal({
            backdrop: true,
            keyboard: true,
            show: true
        });

        $( '#purge_dialog_delete' ).unbind().bind( 'click', function(){
            doDeleteAlias( alid, mid );
        });
        $( '#purge_dialog_cancel' ).click( function(){
            delDialog.modal('hide');
        });
    };

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
