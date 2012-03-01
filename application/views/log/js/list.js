<script type="text/javascript"> /* <![CDATA[ */

    var oDataTable;


    $(document).ready(function()
    {
        oDataTable = $('#log_list_table').dataTable({
            "fnCookieCallback": function (sName, oData, sExpires, sPath) {
                vm_prefs['data_table_rows'] = oData['iLength'];
                $.jsonCookie( 'vm_prefs', vm_prefs, { 'expires': vm_cookie_expiry_days } );
                oData = null;
                return sName + "="+JSON.stringify(oData)+"; expires=" + sExpires +"; path=" + sPath;
            },
            "sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
            "sPaginationType": "bootstrap",
            'iDisplayLength': vm_prefs['data_table_rows']?vm_prefs['data_table_rows']:{$options.defaults.table.entries},
            "sCookiePrefix": "ViMbAdmin_DataTables_",
            'bStateSave': true,
            'aaSorting': [[4, 'desc']]
        });
    }); // document onready

/* ]]> */ </script>
