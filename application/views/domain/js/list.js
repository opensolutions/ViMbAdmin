var delDialog;
var oDataTable;


$(document).ready(function()
{
    oDataTable = $('#list_table').dataTable({
        'fnDrawCallback': function() {
            if( vm_prefs['iLength'] !=  $( "select[name|='list_table_length']" ).val() )
                vm_prefs['iLength'] = $( "select[name|='list_table_length']" ).val();
            
            {if isset($options.defaults.server_side.pagination.domain.enable) && $options.defaults.server_side.pagination.domain.enable }
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
                $( "a[id|='modal-dialog']" ).unbind().bind( 'click', tt_openModalDialog );
                $( '.have-tooltip' ).tooltip("destroy").tooltip( { html: true, delay: { show: 500, hide: 2 }, trigger: 'hover' } );
                $( '.oss-dropdown' ).each( ossDropdown );
            {/if}
            $.jsonCookie( 'vm_prefs', vm_prefs, vm_cookie_options );
        },
        'iDisplayLength': ( typeof vm_prefs != 'undefined' && 'iLength' in vm_prefs )
                ? parseInt( vm_prefs['iLength'] )
                : {if isset( $options.defaults.table.entries )}{$options.defaults.table.entries}{else}10{/if},
        "sDom": "<'row'<'span6'l><'span6' <'#list_table_filter_div'f>>r>t<'row'<'span6'i><'span6'p>>",
        {if isset($options.defaults.server_side.pagination.domain.enable) && $options.defaults.server_side.pagination.domain.enable }
            "bFilter": false,
            "oLanguage": {
              "sEmptyTable": "No data available in table. Use search field to look for entries."
            },
        {/if}
        "sPaginationType": "bootstrap",
        'aoColumns': [
            null,
            { 'sType': 'num-html' },
            { 'sType': 'num-html' },
            {if isset($options.defaults.list_size.disabled) && !$options.defaults.list_size.disabled}
            { 'sType': 'num-html' },
            {/if}
            null,
            null,
            null,
            null,
            { 'bSortable': false, "bSearchable": false }
        ]
    });

}); // document onready

function toggleActive( elid, id) {
    ossToggle( $( '#' + elid ), "{genUrl controller='domain' action='ajax-toggle-active'}", { "did": id } );
};


function purgeDomain( id, domain )
{
    $( "#purge_domain_name" ).html( domain );

    delDialog = $( '#purge_dialog' ).modal({
        backdrop: true,
        keyboard: true,
        show: true
    });

    $( '#purge_dialog_delete' ).attr( 'href', '{genUrl controller="domain" action="purge"}/did/' + id );

    $( '#purge_dialog_cancel' ).click( function(){
        delDialog.modal('hide');
    });
};

{if isset($options.defaults.server_side.pagination.domain.enable) && $options.defaults.server_side.pagination.domain.enable }
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
                  url: "{genUrl controller='domain' action='list-search'}/search/" + $.trim( $( event.target ).val() ),
                  success: function(data){
                    if( data !== "ko" && data.substr( 0, 1 ) == "[" )
                    {
                        data = jQuery.parseJSON( data );
                        $.each( data, function( index, row ){
                               oDataTable.fnAddData([
                                    row.name,
                                    formatMailboxes( row.id, row.mailboxes, row.maxmailboxes ),
                                    formatAliases( row.id, row.aliases, row.maxaliases ),
                                    {if isset($options.defaults.list_size.disabled) && !$options.defaults.list_size.disabled}
                                    row.mailboxes_size == null ? 0 : (row.mailboxes_size / {$multiplier}).toFixed(1),
                                    {/if}
                                    formatActive( row.id, row.active ),
                                    row.transport,
                                    row.backupmx ? "Yes": "No",
                                    row.created.date.substr( 0, 10 ),
                                    formatControlls( row.id, row.name )
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
    return '<div id="throb-toggle-active-' + id + '" style="float: right;"></div>'
        + '<span id="toggle-active-' + id + '" '
        + 'onclick="toggleActive( \'toggle-active-' + id +  '\', ' + id +  ' );" class="btn btn-mini btn-' + active_class + '">' 
        + active_msg + '</span>';
}

function formatMailboxes( id, mailboxes, maxmailboxes )
{
    var str = '<a class="btn btn-mini have-tooltip" id="add_mailbox_' + id + '" title="Add Mailbox" href="{genUrl controller="mailbox" action="add"}/did/' + id + '">\
        <i class="icon-plus"></i>\
    </a>&nbsp;&nbsp;\
    <a class="ul" href="{genUrl controller="mailbox" action="list"}/did/' + id + '">' + mailboxes;
    if( maxmailboxes != 0 )
       str += '/' +maxmailboxes
    str += '</a>';
    return str;
}

function formatAliases( id, aliases, maxaliases )
{
    var str = '<a class="btn btn-mini have-tooltip" id="add_alias_' + id + '" title="Add Alias" href="{genUrl controller="alias" action="add"}/did/' + id + '">\
        <i class="icon-plus"></i>\
    </a>&nbsp;&nbsp;\
    <a class="ul" href="{genUrl controller="aliases" action="list"}/did/' + id + '">' + aliases;
    if( maxaliases != 0 )
       str += '/' + maxaliases;
    str += '</a>';
    return str;
}

function formatControlls( id, name )
{
    var tmpstr = "";
    var item_id = "";
    var href = "";       
                    
    var str = '<div class="btn-group">\
            <a class="btn btn-mini have-tooltip" id="edit_domain_' + id + '" title="Edit" href="{genUrl controller="domain" action="edit"}/did/' + id + '">\
                <i class="icon-pencil"></i>\
            </a>';
    {if isset( $domain_actions ) }
        {foreach $domain_actions as $action}
            {if isset( $action.menu ) }
                {assign var="action_list_menu" value=$action}
            {else}
                str += '<{$action.tagName} ';
                    {foreach $action as $attrib => $value}
                        {if !in_array( $attrib, [ "tagName", "child"] )}
                            tmpstr = "{$value}";
                            str += '{$attrib}="' + tmpstr.replace( "%id%", id ) + '" ';
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
     
    {if $user->isSuper()}
        str += '<a class="btn btn-mini have-tooltip" id="domain_admins_' + id + '" title="Administrators" href="{genUrl controller="domain" action="admins"}/did/' + id + '">\
            <i class="icon-user"></i>\
        </a>';
    {/if}
            
    str += '<a class="btn btn-mini have-tooltip" id="domain_logs_' + id + '" title="Logs" href="{genUrl controller="log" action="list"}/did/' + id + '">\
                <i class="icon-align-justify"></i>\
            </a>';
            
     {if $user->isSuper()}
        str += '<span  class="btn btn-mini have-tooltip"  id="purge-domain-' + id + '" title="Purge" onclick="purgeDomain( ' + id + ', \'' + name + '\');">\
            <i class="icon-trash"></i>\
        </span>';
    {/if}
            
    {if isset( $action_list_menu)}
        {assign var="action" value=$action_list_menu}
        str += '<{$action.tagName} ';
            {foreach $action as $attrib => $value}
                {if !in_array( $attrib, [ "tagName", "child", "menu" ] )}
                    tmpstr = "{$value}";
                    str += '{$attrib}="' + tmpstr.replace( "%id%", id ) + '" ';
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



