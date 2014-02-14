<script type="text/javascript">

    $(document).ready( function() {
        $( '#captchaid' ).val( '{$captchaId}' );

        $('#captchaimage').attr( 'src', "{genUrl controller='auth' action='captcha-image' id=$captchaId}" );
        $('#captchaimage').bind( 'click', requestNewCaptcha );
    });


    function requestNewCaptcha()
    {
        $( '#requestnewimage' ).val(1);
        $( '#{$form->getAttrib( 'id' )}' ).submit();
    }

</script>
