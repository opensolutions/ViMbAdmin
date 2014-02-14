var oDataTable;
var deleteDialog;


$(document).ready( function()
{
    $( "a[id|='delete-alias']" ).bind( 'click', deleteAlias );
    
    oDataTable = $( '#list_table' ).dataTable({
        'fnDrawCallback': function() {
            if( vm_prefs['iLength'] !=  $( "select[name|='list_table_length']" ).val() )
                vm_prefs['iLength'] = $( "select[name|='list_table_length']" ).val();
            
            {if isset($options.defaults.server_side.pagination.enable) && $options.defaults.server_side.pagination.enable }
                if( !$( "#list_table_filter_div" ).html() ){
                    $( "#list_table_filter_div" ).html( '\
                        <div id="list_table_filter" class="dataTables_filter">\
                            <label>\
                            Search:\
                            <input type="text" aria-controls="list_table">\
                            </label>\
                        </div>');
                    $( "#list_table_filter > label > input" ).unbind().bind( "keyup", getEntries );
                };
                $( "a[id|='delete-alias']" ).unbind().bind( 'click', deleteAlias );
                $( "a[id|='modal-dialog']" ).unbind().bind( 'click', tt_openModalDialog );
                $( '.have-tooltip-long' ).tooltip("destroy").tooltip( { html: true, trigger: 'hover', placement: 'top' } );
                $( '.have-tooltip' ).tooltip("destroy").tooltip( { html: true, delay: { show: 500, hide: 2 }, trigger: 'hover' } );
                $( '.oss-dropdown' ).each( ossDropdown );
            {/if}
            $.jsonCookie( 'vm_prefs', vm_prefs, vm_cookie_options );
            
        },
        'iDisplayLength': ( typeof vm_prefs != 'undefined' && 'iLength' in vm_prefs )
                ? parseInt( vm_prefs['iLength'] )
                : {if isset( $options.defaults.table.entries )}{$options.defaults.table.entries}{else}10{/if},
        "sDom": "<'row'<'span6'l><'span6' <'#list_table_filter_div'f>>r>t<'row'<'span6'i><'span6'p>>",
        {if isset($options.defaults.server_side.pagination.enable) && $options.defaults.server_side.pagination.enable }
            "bFilter": false,
            "oLanguage": {
              "sEmptyTable": "No data available in table. Use search field to look for entries."
            },
        {/if}
        "sPaginationType": "bootstrap",
        'aoColumns': [
            null,
            null,
            null,
            null,
            { 'bSortable': false, "bSearchable": false }
        ]
    });
    
}); // document onready

function toggleActive( elid, id ){
    ossToggle( $( '#' + elid ), "{genUrl controller='alias' action='ajax-toggle-active'}", { "alid": id } );
};



function deleteAlias( event ){
    event.preventDefault();

    delDialog = $( '#purge_dialog' ).modal({
        backdrop: true,
        keyboard: true,
        show: true
    });

    if( $( event.target ).is( "i" ) )
        element = $( event.target ).parent();
    else
        element = $( event.target );

    $( '#purge_dialog_delete' ).attr( 'href', element.attr( 'href' ) );

    $( '#purge_dialog_cancel' ).click( function(){
        delDialog.modal('hide');
    });
};

{if isset($options.defaults.server_side.pagination.enable) && $options.defaults.server_side.pagination.enable }
var timeOut = null;
var ignore_keys = [ 13, 38, 40, 37, 39 ,27, 32, 17, 18, 9, 16, 20, 36, 35, 33, 34, 144 ];
{if isset( $options.defaults.server_side.pagination.min_search_str ) }
    var str_len = {$options.defaults.server_side.pagination.min_search_str};
{else}
    var str_len = 3;
{/if}

function getEntries( event ){
    event.preventDefault();
    if( jQuery.inArray( event.which, ignore_keys ) != -1 )
        return;
     
    clearTimeout( timeOut );    
    if( $.trim( $( event.target ).val() ).length >= str_len ){ 
        timeOut = setTimeout( function(){ 
            $('body').css('cursor', 'wait');
            setTimeout( function(){
                oDataTable.fnClearTable();
                $.ajax({
                  async: false,
                  url: "{genUrl controller='alias' action='list-search' ima=$ima}/search/" + $.trim( $( event.target ).val() ),
                  success: function(data){
                    if( data !== "ko" && data.substr( 0, 1 ) == "[" )
                    {
                        data = jQuery.parseJSON( data );
                        $.each( data, function( index, row ){
                               oDataTable.fnAddData([
                                    row.address,
                                    row.domain,
                                    formatActive( row.id, row.active ),
                                    formatGoto( row.id, row.goto ),
                                    formatControlls( row.id )
                         ]);
                        });
                    }
                  }
                });
                $('body').css('cursor', 'default');
            }, 300);
        }, 500 );
        
    }
    else
    {
        oDataTable.fnClearTable();
    }
}

function formatActive( id, active )
{
    var active_class = active ? 'success': 'danger';
    var active_msg = active ? 'Yes': 'No';
    return '<div id="throb-toggle-active-' + id + '" style="float: right;"></div>\
    <span id="toggle-active-' +id + '" onclick="toggleActive( \'toggle-active-' + id +  '\', ' + id +  ' );" class="btn btn-mini btn-' + active_class + '">' + active_msg + '</span>';
}

function formatGoto( id, goto )
{
    var str = '<div id="alias-goto-' + id + '" ';
    if( goto.length  > 50 )
    {
        str += 'class="have-tooltip-long" title="' + goto.replace( /[,]/g, ", ") + '"';
        goto = goto.substr( 0, 50 ) + '...'; 
    }
    str += '>' + goto + '</div>';
    return str;
}

function formatControlls( id )
{
    var tmpstr = "";
    var item_id = "";
    var href = "";
                    
                    
    var str = '<div class="btn-group">\
            <a class="btn btn-mini have-tooltip" id="edit_alias_' + id + '" title="Edit" href="{genUrl controller="alias" action="edit"}/alid/' + id + '">\
                <i class="icon-pencil"></i>\
            </a>';
            {if isset( $alias_actions ) }
                {foreach $alias_actions as $action}
                    {if isset( $action.menu ) }
                        {assign var="action_list_menu" value=$action}
                    {else}
                        str += '<{$action.tagName} ';
                            {foreach $action as $attrib => $value}
                                {if !in_array( $attrib, [ "tagName", "child"] )}
                                    tmpstr = "{$value}";
                                    str += '{$attrib}="' + tmpstr.replace( "%id%",id ) + '" ';
                                {/if}
                         {/foreach}
                         str += '>';
                        {if !is_array( $action.child ) }
                            str += '{$action.child}';
                        {else}
                            str += '<{$action.child.tagName} {foreach $action.child as $attrib => $value}{if $attrib != "tagName"}{$attrib}="{$value}" {/if}{/foreach} {if $action.child.tagName != "img"}></{$action.child.tagName}>{else}/>{/if}';
                        {/if}
                        str += '</{$action.tagName}>';
                    {/if}
                {/foreach}
            {/if}
            
    str += '<a class="btn btn-mini have-tooltip" id="delete-alias-' + id + '" title="Delete" href="{genUrl controller="alias" action="delete"}/alid/' + id + '">\
                <i class="icon-trash"></i>\
            </a>';
            
            {if isset( $action_list_menu)}
                {assign var="action" value=$action_list_menu}
                str += '<{$action.tagName} ';
                    {foreach $action as $attrib => $value}
                        {if !in_array( $attrib, [ "tagName", "child", "menu" ] )}
                            tmpstr = "{$value}";
                            str += '{$attrib}="' + tmpstr.replace( "%id%",id ) + '" ';
                       {/if}
                    {/foreach}
                str += '>';
                {if !is_array( $action.child ) }
                    str += '{$action.child}';
                {else}
                    str += '<{$action.child.tagName} {foreach $action.child as $attrib => $value}{if $attrib != "tagName"}{$attrib}="{$value}" {/if}{/foreach} {if $action.child.tagName != "img"}></{$action.child.tagName}>{else}/>{/if}';
                {/if}
                str += '<span class="caret"></span>\
                </{$action.tagName}>\
                <ul class="dropdown-menu pull-right">';
                {foreach $action.menu as $item}
                    str += '<li><a ';
                    {if isset( $item.id)}
                        item_id = "{$item.id}";
                        str += 'id="' + item_id.replace( '%id%', id ) + '" ';
                    {/if}
                    href = '{$item.url}';
                    str += 'href="' + href.replace( '%id%', id ) + '" ';
                    str+= '>{$item.text}</a></li>';
                {/foreach}
                str+= '</ul>';
            {/if}
    str += '</div>';
    return str;
    
}
{/if}
