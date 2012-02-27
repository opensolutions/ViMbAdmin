<script type="text/javascript"> /* <![CDATA[ */

    var oDataTable;


    $(document).ready(function()
    {
        oDataTable = $('#log_list_table').dataTable({
                            "sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
                            "sPaginationType": "bootstrap",
                            'iDisplayLength': {$options.defaults.table.entries},
                            'aaSorting': [[4, 'desc']]
                        });
    }); // document onready

/* ]]> */ </script>
