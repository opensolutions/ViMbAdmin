<script type="text/javascript"> /* <![CDATA[ */

    var oDataTable;


    $(document).ready(function()
    {
        oDataTable = $('#list_table').dataTable({
            'fnDrawCallback': function() {
                if( vm_prefs['iLength'] !=  $( "select[name|='list_table_length']" ).val() )
                    vm_prefs['iLength'] = $( "select[name|='list_table_length']" ).val();

                $.jsonCookie( 'vm_prefs', vm_prefs, vm_cookie_options );
            },
            'iDisplayLength': vm_prefs['iLength']? vm_prefs['iLength']: {$options.defaults.table.entries},
            "sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
            "sPaginationType": "bootstrap",
            'aaSorting': [[4, 'desc']]
        });
    }); // document onready

/* ]]> */ </script>
