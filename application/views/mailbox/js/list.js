<script type="text/javascript"> /* <![CDATA[ */

    var purgeDialog;
    var oDataTable;


    $(document).ready( function()
    {
        oDataTable = $( '#mailbox_list_table' ).dataTable({
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
                null,
                null,
                null,
                { 'bSortable': false, "bSearchable": false }
            ]
        });

    }); // document onready


    function toggleActive(elid, id){

        ossToggle( $( '#' + elid ), "{genUrl controller='mailbox' action='ajax-toggle-active'}", { "mid": id } );
    };

    function sendEmail( id, email ) {
        $( "#email_name" ).html( email );

        elDialog = $( '#email_dialog' ).modal({
            backdrop: true,
            keyboard: true,
            show: true
        });

        $( '#email_dialog_send' ).unbind().bind( 'click', function(){
            elDialog.modal('hide');
            window.location.href = "{genUrl controller='mailbox' action='email-settings'}/mid/" + id;

        });
        $( '#email_dialog_cancel' ).click( function(){
            elDialog.modal('hide');
        });
    };

/* ]]> */ </script>
