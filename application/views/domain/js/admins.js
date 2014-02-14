var removeDialog;
var addDialog;
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
        'aoColumns': [
            null,
            { 'bSortable': false, "bSearchable": false }
        ]
    });
    
    $( "a[id|='remove-admin']" ).bind( 'click', removeAdmin );

}); // document onready

function removeAdmin( event ) {

    event.preventDefault();

    if( $( event.target ).is( "i" ) )
        element = $( event.target ).parent();
    else
        element = $( event.target );

    $( "#purge_admin_name" ).html( element.attr( "ref" ) );

    delDialog = $( '#purge_dialog' ).modal({
        backdrop: true,
        keyboard: true,
        show: true
    });

    $( '#purge_dialog_delete' ).attr( "href", element.attr( "href" ) );

    $( '#purge_dialog_cancel' ).click( function(){
        delDialog.modal('hide');
    });
 };