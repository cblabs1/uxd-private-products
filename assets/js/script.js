/* global jQuery, uxdPP */
( function ( $ ) {
    'use strict';

    function initForm( $form ) {
        var $input   = $form.find( 'input[type="password"]' );
        var $btn     = $form.find( '.uxd-pp-submit-btn' );
        var $error   = $form.find( '.uxd-pp-inline-error' );
        var redirect = $form.find( 'input[name="uxd_pp_redirect"]' ).val() || window.location.href;

        $form.on( 'submit.uxdpp', function ( e ) {
            e.preventDefault();

            var password = $input.val();
            if ( ! password ) {
                return;
            }

            $btn.prop( 'disabled', true ).text( uxdPP.i18n.loading );
            $error.hide().text( '' );

            $.ajax( {
                url:    uxdPP.ajaxUrl,
                method: 'POST',
                data:   {
                    action:          uxdPP.action,
                    uxd_pp_password: password,
                    uxd_pp_redirect: redirect,
                },
            } )
            .done( function ( response ) {
                if ( response && response.success ) {
                    // Cookie was set server-side; navigate directly — no extra round-trip needed.
                    window.location.href = ( response.data && response.data.redirect )
                        ? response.data.redirect
                        : redirect;
                } else {
                    var msg = ( response && response.data && response.data.message )
                        ? response.data.message
                        : uxdPP.i18n.error;
                    $error.text( msg ).show();
                    $input.val( '' ).focus();
                    $btn.prop( 'disabled', false ).text( $btn.data( 'label' ) );
                }
            } )
            .fail( function () {
                // Network/server error — show inline message, never reload the page.
                $error.text( uxdPP.i18n.error ).show();
                $btn.prop( 'disabled', false ).text( $btn.data( 'label' ) );
            } );
        } );

        // Store original label for restore after error.
        $btn.data( 'label', $btn.text() );

        // Auto-focus.
        setTimeout( function () { $input.focus(); }, 150 );
    }

    // Initialise every gate / popup form on the page.
    $( '.uxd-pp-gate__form, .uxd-pp-popup__form' ).each( function () {
        initForm( $( this ) );
    } );

} )( jQuery );
