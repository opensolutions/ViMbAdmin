var gotoList = new Array();
var gotoId = 1; // constantly increment it to make sure it is always unique on the page

$(document).ready( function()
{
    $("#address-popover").popover( { trigger: 'hover' } );
    $("#goto-popover").popover( { trigger: 'hover' } );

    $( '#goto_empty' ).keypress( function(e) {
        if( e.which == 13 )
        {
            e.preventDefault();
            if( $( '#goto_empty' ).val().indexOf( "@" ) != -1 )
                addGoto();
        }
    });

    $( '#goto_empty' ).typeahead( {
        source: {$emails}
    })

    tempArr = {if $alias->getGoto()}'{$alias->getGoto()}'.split( ',' ){else}{}{/if};

    for( var i in tempArr )
    {
        gotoItem = jQuery.trim( tempArr[i] );

        if( gotoItem != '' )
            insertGoto( gotoItem );
    }

    {if isset($defaultGoto)}$( '#goto_empty' ).val( '{$defaultGoto}' );{/if}
}); // document onready


function insertGoto( address )
{
    str = '<div id="goto-div-' + gotoId + '" style="margin-top: 5px; margin-bottom: 5px;">' + "\n"
            + '<input type="text" name="goto[]" value="' + address + '" size="40" title="Goto" readonly="readonly" style="border-radius: 4px 0 0 4px;"/>' + "\n"
            + '<span title="Remove got to" class="btn add-on" onclick="removeGoto(' + gotoId + ');" style="margin-left: -5px; height: 20px; border-radius: 0 4px 4px 0;" >' + "\n"
            + '<i class="icon-minus"></i>' + "\n"
            + '</span>' + "\n"
            + '</div>';

    gotoList[gotoId] = address;
    gotoId++;

    jQuery( str ).appendTo( '#div-controls-goto' ).hide().show( 'fast' );
}


function removeGoto( id )
{
    $( '#goto-div-' + id ).hide( 'fast', function() { $(this).remove() } );
    delete gotoList[id];
}


function addGoto()
{
    address = jQuery.trim( $( '#goto_empty' ).val() );

    if( address != '' )
    {
        if( address.substr( 0, 1 ) == '@' && isValidEmailDomain( address.substr( 1 ) ) || isValidEmail( address ) )
        {
            if( gotoList.indexOf( address ) == -1 )
            {
                insertGoto( address );
                $( '#goto_empty' ).val( '' );
                $( '#help-goto' ).html( '' );
                $( '#div-form-goto' ).removeClass( "error" );
                return true;
            }
            else
            {
                $( '#help-goto' ).html( 'Already in goto list.' );
                return false;
            }
        }
        else
        {
            $( '#help-goto' ).html( 'Invalid email address.' );
            return false;
        }
    }

    return false;
}
