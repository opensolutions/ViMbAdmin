var purgeDialog;
var oDataTable;


$(document).ready( function()
{
    oDataTable = $( '#list_table' ).dataTable({
        'fnDrawCallback': function() {
            if( vm_prefs['iLength'] !=  $( "select[name|='list_table_length']" ).val() )
                vm_prefs['iLength'] = $( "select[name|='list_table_length']" ).val();

            $.jsonCookie( 'vm_prefs', vm_prefs, vm_cookie_options );
        },
        'iDisplayLength': ( typeof vm_prefs != 'undefined' && 'iLength' in vm_prefs )
                ? parseInt( vm_prefs['iLength'] )
                : {if isset( $options.defaults.table.entries )}{$options.defaults.table.entries}{else}10{/if},
        "sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
        "sPaginationType": "bootstrap",
        'aoColumns': [
            null,
            null,
            null,
            { 'bSortable': false, "bSearchable": false }
        ]
    });

    $( "a[id|='purge-admin']" ).bind( 'click', purgeAdmin );
}); // document onready


function toggleActive( elid, id ){
    ossToggle( $( '#' + elid ), "{genUrl controller='admin' action='ajax-toggle-active'}", { "aid": id } );
};

function toggleSuper( elid, id ){
    if( ossToggle( $( '#' + elid ), "{genUrl controller='admin' action='ajax-toggle-super'}", { "aid": id } ) )
        $( '#admin_domains_' + id ).hide();
    else
        $( '#admin_domains_' + id ).show();
};

function purgeAdmin( event ){
    event.preventDefault();

    if( $( event.target ).is( "i" ) )
        element = $( event.target ).parent();
    else
        element = $( event.target );

    $( "#purge_admin_name" ).html( element.attr( 'ref' ) );

    $( '#purge_dialog_delete' ).attr( 'href', element.attr( 'href' ) );

    delDialog = $( '#purge_dialog' ).modal({
        backdrop: true,
        keyboard: true,
        show: true
    });
    
    $( '#purge_dialog_cancel' ).click( function(){
        delDialog.modal('hide');
    });
};