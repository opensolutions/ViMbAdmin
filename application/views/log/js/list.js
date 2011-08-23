<script type="text/javascript"> /* <![CDATA[ */

    var oDataTable;


    $(document).ready(function()
    {
        oDataTable = $('#log_list_table').dataTable({
                            'iDisplayLength': {$options.defaults.table.entries},
                            'aaSorting': [[4, 'desc']]
                        });
    }); // document onready

/* ]]> */ </script>
