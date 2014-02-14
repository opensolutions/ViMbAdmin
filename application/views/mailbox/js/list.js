var purgeDialog;
var oDataTable;

$(document).ready( function() {

    {if isset($options.defaults.list_size.disabled) && !$options.defaults.list_size.disabled}
        $( "a[id|='dir-size']" ).bind( "click", showSizes );
    {/if}
    
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
                 {if isset($options.defaults.list_size.disabled) && !$options.defaults.list_size.disabled}
                    $( "a[id|='dir-size']" ).unbind().bind( "click", showSizes );
                {/if}
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
            {if isset($options.defaults.list_size.disabled) && !$options.defaults.list_size.disabled}
            { 'sType': 'num-html' },
            {/if}
            null,
            { "bSearchable": false },
            { 'bSortable': false, "bSearchable": false }
        ]
    });
    

}); // document onready

function toggleActive(elid, id) {
    ossToggle( $( '#' + elid ), "{genUrl controller='mailbox' action='ajax-toggle-active'}", { "mid": id } );
};

{if isset($options.defaults.list_size.disabled) && !$options.defaults.list_size.disabled}
    function showSizes( event ) {
        event.preventDefault();
        data = $( event.target ).attr( 'data-sizes' ).split( '|' );
        hdirsize = data[1] / data[3];
        mdirsize = data[2] / data[3];
        msg =  "<table class=\"table\"><thead>";
        msg += "<tr><th>Last size update:</th><td>" + data[0] + "</td></tr></thead>";
        msg += "<tr><th>Home directory size:</th><td> " + hdirsize.toFixed( 5 ) + data[4] + "</td></tr>";
        msg += "<tr><th>Mail directory size:</th><td> " + mdirsize.toFixed( 5 ) + data[4];
        if( data[5] != 0 )
        {
            prc = 100 / data[5] * data[2];
            msg += " (" + prc.toFixed(0) + "%)";
        }
        msg += "</td></tr></table>";
        bootbox.alert( msg );
    }
{/if}

{if isset($options.defaults.server_side.pagination.enable) && $options.defaults.server_side.pagination.enable }

    var timeOut = null;
    var ignore_keys = [ 13, 38, 40, 37, 39 ,27, 32, 17, 18, 9, 16, 20, 36, 35, 33, 34, 144 ];

    {if isset( $options.defaults.server_side.pagination.min_search_str ) }
        var str_len = {$options.defaults.server_side.pagination.min_search_str};
    {else}
        var str_len = 3;
    {/if}

    function getEntries( event ) {
        event.preventDefault();

        if( jQuery.inArray( event.which, ignore_keys ) != -1 )
            return;
         
        clearTimeout( timeOut );    
        
        if( $.trim( $( event.target ).val() ).length >= str_len )
        { 
            timeOut = setTimeout( function() { 
                $('body').css('cursor', 'wait');
                setTimeout( function() {
                    oDataTable.fnClearTable();
                    $.ajax({
                      async: false,
                      url: "{genUrl controller='mailbox' action='list-search'}/search/" + $.trim( $( event.target ).val() ),
                      success: function(data){
                        if( data !== "ko" && data.substr( 0, 1 ) == "[" )
                        {
                            data = jQuery.parseJSON( data );
                            $.each( data, function( index, row ){
                                   oDataTable.fnAddData([
                                        row.username,
                                        row.name,
                                        {if isset($options.defaults.list_size.disabled) && !$options.defaults.list_size.disabled}
                                            formatMdirsize( row.id, row.maildir_size, row.homedir_size, row.size_at, row.quota ),
                                        {/if}
                                        row.domain,
                                        formatActive( row.id, row.active ),
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

    function formatControlls( id )
    {
        var tmpstr = "";
        var item_id = "";
        var href = "";
        var str = '<div class="btn-group">\
                <a class="btn btn-mini have-tooltip" id="edit_mailbox_' + id + '" title="Edit" href="{genUrl controller="mailbox" action="edit"}/mid/' + id + '">\
                    <i class="icon-pencil"></i>\
                </a>';
                {if isset( $mailbox_actions ) }
                    {foreach $mailbox_actions as $action}
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
                
        str += '<a class="btn btn-mini have-tooltip" id="password_' + id + '" title="Password" href="{genUrl controller="mailbox" action="password"}/mid/' + id + '">\
                    <i class="icon-lock"></i>\
                </a>\
                <a class="btn btn-mini have-tooltip" id="mailbox_aliases_' + id + '" title="List Aliases" href="{genUrl controller="mailbox" action="aliases"}/mid/' + id + '">\
                    <i class="icon-random"></i>\
                </a>\
                <a class="btn btn-mini have-tooltip" id="modal-dialog-mailbox_settings_' + id + '" title="Send Settings" href="{genUrl controller="mailbox" action="email-settings"}/mid/' + id + '">\
                    <i class="icon-envelope"></i>\
                </a>\
                <a class="btn btn-mini have-tooltip" id="archive_' + id + '" title="Archive" href="{genUrl controller="archive" action="add"}/mid/' + id + '">\
                    <i class="icon-inbox"></i>\
                </a>\
                <a class="btn btn-mini have-tooltip" id="purge_' + id + '" title="Purge" href="{genUrl controller="mailbox" action="purge"}/mid/' + id + '">\
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

    {if isset($options.defaults.list_size.disabled) && !$options.defaults.list_size.disabled}
    function formatMdirsize( id, maildir_size, homedir_size, size_at, quota )
    {
        if( maildir_size != null ){
            if( maildir_size / {$multiplier} < 0.1 )
                var mdir_size = 0.1;
            else
                var mdir_size = maildir_size / {$multiplier};
            return '<a href="#" data-sizes="'+ size_at.date + '|' + homedir_size + '|' + maildir_size + '|{$multiplier}|{$size_multiplier}|' + quota + '" id="dir-size-' + id + '">' + mdir_size.toFixed(1) + '</a>';
        }
        else
            return "0" ;
    }
    {/if}

{/if}
