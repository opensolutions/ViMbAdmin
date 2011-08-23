<script type="text/javascript"> /* <![CDATA[ */

    var gotoList = new Array();
    var gotoId = 1; // constantly increment it to make sure it is always unique on the page

    $(document).ready( function()
    {
        $('#goto_empty').keypress( function(e) {
            if( e.which == 13 )
            {
                e.preventDefault();
                //e.stopPropagation();
                //$( '#goto_empty' ).autocomplete( 'close' );
                //addGoto();
            }
        });

        $( '#goto_empty' ).autocomplete({
            source: "{genUrl controller='alias' action='ajax-autocomplete'}",
            minLength: {$options.alias_autocomplete_min_length},
            select: function( event, ui ) {
                $('#goto_empty').val( ui.item ? ui.item.value : this.value );
                if( addGoto() ) setTimeout( "$( '#goto_empty' ).val( '' );" , 100 ); // lame trick to empty the field when using autocomplete
            }
        });

        tempArr = "{$aliasModel.goto}".split( ',' );

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
        str =   '<li id="goto_' + gotoId + '">' + "\n"
              + '<input type="text" name="goto[]" value="' + address + '" size="40" title="Goto" readonly="readonly" />' + "\n"
              + '<img alt="Remove" title="Remove" src="{genUrl}/images/remove.png" class="valign_middle clickable" onclick="removeGoto(' + gotoId + ');" />' + "\n"
              + "</li>\n";

        gotoList[gotoId] = address;
        gotoId++;

        jQuery( str ).appendTo( '#goto_addresses' ).hide().show( 'fast' );
    }


    function removeGoto( id )
    {
        $( '#goto_' + id ).hide( 'fast', function() { $(this).remove() } );
        delete gotoList[id];
    }


    function addGoto()
    {
        address = jQuery.trim( $( '#goto_empty' ).val() );

        if( address != '' )
        {
            if( isValidEmail( address ) )
            {
                if( gotoList.indexOf( address ) == -1 )
                {
                    $( '#goto_empty' ).autocomplete( 'close' );
                    insertGoto( address );
                    $( '#goto_empty' ).val( '' );
                    $( '#invalid_email' ).html( '' );
                    return true;
                }
                else
                {
                    $( '#invalid_email' ).html( 'Already in goto list.' );
                    return false;
                }
            }
            else
            {
                $( '#invalid_email' ).html( 'Invalid email address.' );
                return false;
            }
        }

        return false;
    }

/* ]]> */ </script>
