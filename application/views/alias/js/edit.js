<script type="text/javascript"> /* <![CDATA[ */

    var gotoList = new Array();
    var gotoId = 1; // constantly increment it to make sure it is always unique on the page

    $(document).ready( function()
    {
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
        str = '<div class="input-append" id="goto-div-' + gotoId + '" style="margin-bottom: 5px;">' + "\n"
		      + '<input type="text" name="goto[]" value="' + address + '" size="40" title="Goto" readonly="readonly"/>' + "\n"
    		  + '<span title="Remove got to" class="btn add-on" onclick="removeGoto(' + gotoId + ');" style="margin-left: -5px;" >' + "\n"
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
            if( isValidEmail( address ) )
            {
                if( gotoList.indexOf( address ) == -1 )
                {
                    insertGoto( address );
                    $( '#goto_empty' ).val( '' );
                    $( '#help-goto' ).html( '' );
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

/* ]]> */ </script>
