<script type="text/javascript"> /* <![CDATA[ */

    var removeDialog;
    var addDialog;
    var oDataTable;


    $(document).ready( function()
    {
        oDataTable = $( '#admin_list_table' ).dataTable({
            "fnCookieCallback": function (sName, oData, sExpires, sPath) {
                vm_prefs['data_table_rows'] = oData['iLength'];
                $.jsonCookie( 'vm_prefs', vm_prefs, { 'expires': vm_cookie_expiry_days } );
                oData = null;
                return sName + "="+JSON.stringify(oData)+"; expires=" + sExpires +"; path=" + sPath;
            },
            'iDisplayLength': vm_prefs['data_table_rows']?vm_prefs['data_table_rows']:{$options.defaults.table.entries},
            "sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
            "sPaginationType": "bootstrap",
            'bStateSave': true,
            "sCookiePrefix": "ViMbAdmin_DataTables_",
            'aoColumns': [
                null,
                { 'bSortable': false, "bSearchable": false }
            ]
        });

        $( '#open_add_admin' ).click( function( event ){

            addDialog = $( '#add_dialog' ).modal({
                backdrop: true,
                keyboard: true,
                show: true
            });

            $( '#add_dialog_add' ).unbind().bind( 'click', function(){
                doAddAdmin( );
            });
            $( '#add_dialog_cancel' ).click( function(){
                addDialog.modal('hide');
            });
        });

    }); // document onready

    function removeAdmin( id, admin ) {

        $( "#purge_admin_name" ).html( admin );

        delDialog = $( '#purge_dialog' ).modal({
            backdrop: true,
            keyboard: true,
            show: true
        });

        $( '#purge_dialog_delete' ).unbind().bind( 'click', function(){
            doRemoveAdmin( id );
        });
        $( '#purge_dialog_cancel' ).click( function(){
            delDialog.modal('hide');
        });
     };


    function doRemoveAdmin( adminId )
    {
        var Throb = tt_throbber( 32, 14, 1.8 ).appendTo( $( '#pdfooter' ).get(0) ).start();

        $( '#purge_dialog_delete' ).attr( 'disabled', 'disabled' ).addClass( 'disabled' );
        $( '#purge_dialog_cancel' ).attr( 'disabled', 'disabled' ).addClass( 'disabled' );

        $.ajax({
            url: "{genUrl controller='domain' action='ajax-remove-admin'}/did/{$domainModel.id}/aid/" + adminId,
            async: true,
            cache: false,
            type: 'GET',
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
                                document.location.reload();
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



    function doAddAdmin(  )
    {
        if( $( '#admin_list' ).val() == null )
        {
            $( '#add_admin_msg' ).html( '<span class="red">No admin selected.</span>' );
            return;
        }

        var Throb = tt_throbber( 32, 14, 1.8 ).appendTo( $( '#aafooter' ).get(0) ).start();

        $( '#purge_dialog_delete' ).attr( 'disabled', 'disabled' ).addClass( 'disabled' );
        $( '#purge_dialog_cancel' ).attr( 'disabled', 'disabled' ).addClass( 'disabled' );

        $.ajax({
            url: "{genUrl controller='domain' action='ajax-add-admin'}/did/{$domainModel.id}/aid/" + $( '#admin_list' ).val(),
            async: true,
            cache: false,
            type: 'GET',
            timeout: 3000, // milliseconds
            success: function( data )
                        {
                            addDialog.modal('hide');
                            if (data != 'ok')
                            {
                                ossAddMessage( 'An unexpected error has occured.', 'error' );
                            }
                            else
                            {
                                document.location.reload();
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
