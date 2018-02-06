(function($){

    HTMLPressEditor = {

        editorHTML : '',
        editorJS   : '',
        editorCSS  : '',

        /**
         * Init
         */
        init: function()
        {
            if( $('body').hasClass('single-htmlpress') ) {
                this._bind();
                this._setup_editors();
                this._add_to_iframe();
                this._setup_select2();
            }
        },

        _setup_select2: function() {
            if( jQuery( '.htmlpress-wp-scripts, .htmlpress-wp-styles' ).length ) {
                jQuery( '.htmlpress-wp-scripts, .htmlpress-wp-styles' ).select2();

                // init sortable
                if( jQuery("ul.select2-selection__rendered").length ) {
                    jQuery("ul.select2-selection__rendered").sortable({
                        containment: 'parent'
                    });
                }
            }
        },

        _setup_editors: function() {
            if( $('#template-html').length ) {
                HTMLPressEditor.editorHTML = ace.edit( 'template-html' );
                HTMLPressEditor.editorHTML.setTheme("ace/theme/twilight");
                HTMLPressEditor.editorHTML.session.setMode("ace/mode/html");
                HTMLPressEditor.editorHTML.setOptions({
                    highlightActiveLine: true
                });
                HTMLPressEditor.editorHTML.on( "input", HTMLPressEditor._add_to_iframe );
            }
            if( $('#template-css').length ) {
                HTMLPressEditor.editorCSS = ace.edit( 'template-css' );
                HTMLPressEditor.editorCSS.setTheme("ace/theme/twilight");
                HTMLPressEditor.editorCSS.session.setMode("ace/mode/css");
                HTMLPressEditor.editorCSS.setOptions({
                    highlightActiveLine: true
                });
                HTMLPressEditor.editorCSS.on( "input", HTMLPressEditor._add_to_iframe );
            }
            if( $('#template-js').length ) {
                HTMLPressEditor.editorJS = ace.edit( 'template-js' );
                HTMLPressEditor.editorJS.setTheme("ace/theme/twilight");
                HTMLPressEditor.editorJS.session.setMode("ace/mode/js");
                HTMLPressEditor.editorJS.setOptions({
                    highlightActiveLine: true
                });
                HTMLPressEditor.editorJS.on( "input", HTMLPressEditor._add_to_iframe );
            }
        },

        _add_to_iframe: function ()
        {
            var iframe = document.getElementById('htmlpress-preview-frame');

            // var iframe = document.createElement('iframe');
            // iframe.setAttribute("id", "Div1");
            // document.body.appendChild(iframe);

            var body_content = HTMLPressEditor.editorHTML.getValue() || '',
                css_data     = HTMLPressEditor.editorCSS.getValue(),
                js_data     = HTMLPressEditor.editorJS.getValue(),
                head_content = '';

            //  Add DEPENDENCY CSS & JS.
            var scripts = jQuery.parseJSON( _s.post_meta.selected_scripts );
            var styles  = jQuery.parseJSON( _s.post_meta.selected_styles );
            $.each( scripts, function(handle, script_url) {
                console.log( 'script_url: ' + script_url );
                head_content += '<scr'+'ipt src="'+script_url+'" type="text/javascript"></scr'+'ipt>';
            });
            $.each( styles, function(index, style_url) {
                head_content += '<link href="'+style_url+'" rel="stylesheet" type="text/css" />';
            });

            // Add template CSS & JS.        
            head_content += '<scr'+'ipt class="js" type="text/javascript">'+js_data+'</scr'+'ipt>';
            head_content += '<style class="css" type="text/css">'+css_data+'</style>';

            // // Append HTML, CSS & JS        
            // $( '.preview iframe' ).contents().find("body").html( body_content );
            // $( '.preview iframe' ).contents().find("body").append( head_content );
            // // $( '#htmlpress-preview-frame' ).html( body_content );
            
            html = '<head>'+head_content+'</head>';
            html += '<body>'+body_content+'</body>';

            iframe.contentWindow.document.open();
            iframe.contentWindow.document.write(html);
            iframe.contentWindow.document.close();

        },

        /**
         * Binds events
         */
        _bind: function()
        {
            $( document ).on('click', '.preview-laptop', HTMLPressEditor._preview_laptop );
            $( document ).on('click', '.preview-tablet', HTMLPressEditor._preview_tablet );
            $( document ).on('click', '.preview-smartphone', HTMLPressEditor._preview_smartphone );
            $( document ).on('click', '.save-template', HTMLPressEditor._save_module );
            $( document ).on('click', '.icon-full-screen', HTMLPressEditor._full_screen );

            $( document ).on('click', '.editor-template-css .dashicons', HTMLPressEditor._open_css_settings );
            $( document ).on('click', '.editor-template-js .dashicons', HTMLPressEditor._open_js_settings );
            $( document ).on('click', '.editor-template-html .dashicons', HTMLPressEditor._open_html_settings );

            $( document ).on('click', '#saveHTMLBtn', HTMLPressEditor._generate_screenshot );
            $( document ).on('click', '.duplicate-template', HTMLPressEditor._duplicate_module );
        },

        /**
         * Duplicate Template
         */
        _duplicate_module: function( event ) {
            event.preventDefault();

            var btn = jQuery(this);
            var id  = btn.parents('.toolbar').attr('data-id'); // Get editor values.

            // Process Started.
            btn.find( '.msg' ).remove();
            btn.prepend('<span class="msg"></span>');
            btn.find( '.msg' )
                .html( 'Processing...' );
            btn.find('.dashicons')
                .removeClass('dashicons-upload dashicons-yes')
                .addClass('dashicons-image-rotate htmlpress-processing');

            // Process request.
            $.ajax({
                url: _s.ajax_url,
                type: 'POST',
                data: {
                    action: 'htmlpress_duplicate_template',
                    post_id: id,
                },
            })
            .done( function( response ) {

                // Loader
                btn.find('.dashicons')
                    .addClass('dashicons-yes')
                    .removeClass('dashicons-image-rotate htmlpress-processing');

                //  Processing end
                btn.find('.dashicons').removeClass('dashicons-image-rotate htmlpress-processing').addClass('dashicons-yes htmlpress-processing-success');
                setTimeout(function() {

                    // Loader
                    btn.find( '.msg' ).remove();
                    btn.find('.dashicons')
                        .removeClass('dashicons-yes htmlpress-processing-success')
                        .addClass('dashicons-welcome-add-page');
                    window.open( response , '_blank');

                }, 500);
            })
            .fail(function() {
                console.log("error");
            })
            .always(function() {
                // console.log("complete");
            });

        },

        _generate_screenshot: function( event ) {
            event.preventDefault();

            var btn = $( this );

            btn.find( '.msg' )
                .html( '<i>Processing...</i>' );
            btn.find('.dashicons')
                .removeClass('dashicons-format-image dashicons-yes')
                .addClass('dashicons-image-rotate htmlpress-processing');

            html2canvas( $( '#htmlpress-preview-frame' ).contents().find("body"), {
                onrendered: function(canvas) {

                    btn.find( '.msg' ).html( '<i>Saving Thumbnail...</i>' );

                    var id = btn.parents('.toolbar').attr('data-id');
                    jQuery( '#saveHTMLCanvas').html( canvas );

                    var dataURL = canvas.toDataURL();

                    $.ajax({
                        url: _s.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'htmlpress_template_thumbnail_generator',
                            post_id: id,
                            imgBase64: dataURL,
                        },
                    })
                    .done( function( response ) {

                        btn.find( '.msg' ).html( 'Saved as Featured Image' );

                        // Loader
                        btn.find('.dashicons')
                            .addClass('dashicons-yes')
                            .removeClass('dashicons-image-rotate htmlpress-processing');

                        //  Processing end
                        setTimeout(function() {
                            // Loader
                            btn.find('.dashicons')
                                .removeClass('dashicons-yes')
                                .addClass('dashicons-format-image');
                            
                            btn.find( '.msg' ).html( 'Generate Featured Image' );
                        }, 700);

                    })
                    .fail(function() {
                        console.log("error");
                    })
                    .always(function() {
                        // console.log("complete");
                    });
                }
            });
        },

        _open_css_settings: function( event ) {
            event.preventDefault();
            jQuery(this).parents('.editor-template-css').toggleClass('open');
            jQuery('.template-css-contents').toggleClass('open').slideToggle();        
        },

        _open_js_settings: function( event ) {
            event.preventDefault();
            jQuery(this).parents('.editor-template-js').toggleClass('open');
            jQuery('.template-js-contents').toggleClass('open').slideToggle();        
        },

        _open_html_settings: function( event ) {
            event.preventDefault();
            jQuery(this).parents('.editor-template-html').toggleClass('open');
            jQuery('.template-html-contents').slideToggle();        
        },

        /**
         * View Full Screen
         */
        _full_screen: function() {
            $( 'body').toggleClass('view-full-screen');
        },

        _save_module: function( event ) {
            event.preventDefault();

            var btn = jQuery(this);
            var id  = btn.parents('.toolbar').attr('data-id'); // Get editor values.

            // Process Started.
            btn.find( '.msg' ).remove();
            btn.prepend('<span class="msg"></span>');
            btn.find( '.msg' )
                .html( 'Processing...' );
            btn.find('.dashicons')
                .removeClass('dashicons-upload dashicons-yes')
                .addClass('dashicons-image-rotate htmlpress-processing');

            // Get all script as per reordered.
            var scripts = [];
            jQuery('.template-js-contents').find('li.select2-selection__choice').each(function(index, el) {
                var script_src = jQuery( this ).attr('title') || '';
                if( '' !== script_src ) {
                    scripts.push( script_src );
                }
            });
            console.log( 'scripts: ' + JSON.stringify( scripts ) );

            // Get all styles as per reordered.
            var styles = [];
            jQuery('.template-css-contents').find('li.select2-selection__choice').each(function(index, el) {
                var style_src = jQuery( this ).attr('title') || '';
                if( '' !== style_src ) {
                    styles.push( style_src );
                }
            });

            var codeHTML =  encodeURI( HTMLPressEditor.editorHTML.getValue() );
            var codeJS   =  encodeURI( HTMLPressEditor.editorJS.getValue() );
            var codeCSS  =  encodeURI( HTMLPressEditor.editorCSS.getValue() );

            // Save module data.
            $.ajax({
                url: _s.ajax_url,
                type: 'POST',
                data: {
                    action           : 'htmlpress_save_module',
                    post_id          : id,
                    source_codehtml  : codeHTML,
                    source_codejs    : codeJS,
                    source_codecss   : codeCSS,
                    
                    // Selected Scripts & Styles.
                    selected_scripts : scripts,
                    selected_styles  : styles,
                },
            })
            .done( function( response ) {

                console.log( 'result: ' + response );

                // Loader
                btn.find('.dashicons')
                    .addClass('dashicons-yes')
                    .removeClass('dashicons-image-rotate htmlpress-processing');


                //  Processing end
                // btn.removeClass('dashicons-image-rotate htmlpress-processing').addClass('dashicons-yes htmlpress-processing-success');
                setTimeout(function() {

                    // Loader
                    btn.find( '.msg' ).remove();
                    btn.find('.dashicons')
                        .addClass('dashicons-upload');
                        // btn.removeClass('dashicons-yes htmlpress-processing-success').addClass('dashicons-upload');
                }, 700);
            })
            .fail(function() {
                console.log("error");
            })
            .always(function() {
                // console.log("complete");
            });

        },

        _preview_laptop: function() {
            $( '.preview .frame').removeClass('inline-view-480 inline-view-768');
        },

        _preview_tablet: function() {
            $( '.preview .frame').addClass('inline-view-768').removeClass('inline-view-480');
        },

        _preview_smartphone: function() {
            $( '.preview .frame').addClass('inline-view-480').removeClass('inline-view-768');
        }

    };

    /**
     * Initialization
     */
    $(function(){
        HTMLPressEditor.init();
    });

})(jQuery);