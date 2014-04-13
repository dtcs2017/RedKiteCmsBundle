/*
 * This file is part of the RedKite CMS Application and it is distributed
 * under the MIT License. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <webmaster@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    MIT License
 *
 */



;(function( $ ){    
    var stopBlocksMenu = false;
    var isCursorOverEditor = false;
    
    var methods = {
        start: function() 
        {
            var $this = $(this);
            $('body').addClass('cms_started');
            doStartEdit($this);
            hideContentsForEditMode($this);
            $('.inline-list').addClass('collapsed-list');
            
            $(document).trigger("cmsStarted");
            
            return this;
        },
        stop: function()
        {
            if($('body').hasClass('cms_started'))
            {
                showHiddenContentsFromEditMode();
                $("#al_cms_contents a").unbind();
                deactivateEditableInlineContents();
                
                this.each(function() {
                    $(this)
                        .popover('destroy')
                        .removeClass('al_edit_on')
                        .unbind()
                    ;  
                });
                var activeInlineList = $('body').data('al-active-inline-list');
                if (activeInlineList != null) {
                    activeInlineList.inlinelist('stop');
                }

                cmsStartInternalJavascripts();
                $('body').removeClass('cms_started');
                $('.inline-list').removeClass('collapsed-list');
                
                stopBlocksMenu = false;
                
                $(document).trigger("cmsStopped");
            }
            
            return this;
        },
        startEditElement: function()
        {   
            doStartEdit($(this));
                
            return this;
        },
        stopEditElement: function()
        {     
            stopEditElement($(this));
                
            return this;
        },
        hideElementContent: function()
        {     
            hideContentsForEditMode($(this));
                
            return this;
        },
        lockBlocksMenu: function()
        {     
            stopBlocksMenu = true;
                
            return this;
        },
        unlockBlocksMenu: function()
        {     
            stopBlocksMenu = false;
                
            return this;
        },
        stopCursorOverEditor: function()
        {
            isCursorOverEditor = false;
                
            return this;
        },
        isCursorOverEditor: function()
        {     
            return isCursorOverEditor;
        }
    };
    
    function doStartEdit(element)
    {
        startEditElement(element);
            
        // Starts the editor for included blocks
        startEditElement(element.find('[data-editor="enabled"]'));
    }
    
    function startEditElement(element)
    {
        if ( ! $('body').hasClass('cms_started')) {
            return;
        }
        
        element.each(function()
        {
            var $this = $(this);
            var decodedContent = decodeURIComponent($this.attr('data-encoded-content'));
            var popoverOptions = {
                placement: function () {
                    var position = $this.position();

                    if (position.top + 400 < $('#al_cms_contents').height()){
                        return "bottom";
                    }

                    return "top";
                },
                trigger: 'manual',
                content: decodedContent,
                template: '<div class="popover al-popover"><div class="arrow"></div><div class="popover-inner"><div class="popover-title"></div><div class="popover-content"></div></div></div>'
            }
            
            var hasPopover = ($this.attr('rel') == 'popover');            
            if (hasPopover) {
                $this.popover(popoverOptions);
            }
            
            activateEditableInlineContents();
            
            $this
                .unbind()
                .addClass('al_edit_on')
                .mouseenter(function(event)
                {
                    event.stopPropagation(); 
                    if (stopBlocksMenu) {
                        return;
                    }
                    
                    var element = $(this);
                    
                    $this.highligther('highlight');
                    element.css('cursor', 'pointer');
                    
                    $('#al_block_menu_toolbar').show();
                    if (element.is('[data-hide-blocks-menu="true"]')) {
                        $('#al_block_menu_toolbar').hide();

                        return;
                    }
                    
                    $('.al-img-add-bottom-button').show();
                    $('.al-img-add-top-button').show();
                    if (element.is('[data-included="1"]') && element.attr('data-block-id') > 0) {
                        $('.al-img-add-bottom-button').hide();
                        $('.al-img-add-top-button').hide();
                    }

                    $('#al_block_menu_toolbar').data('parent', $this).position({
                            my: "right top",
                            at: "right bottom",
                            of: $this,
                            using: function( pos, ui ) {
                                var $this = $(this);
                                
                                if ( ui.vertical == 'bottom' ) { 
                                    $this.addClass('rk-top').removeClass('rk-bottom');
                                    $($this).css({
                                        left: pos.left + 'px',
                                        top: pos.top - 1 + 'px'
                                    });
                                    
                                } else {
                                    $this.addClass('rk-bottom').removeClass('rk-top');
                                    $($this).css({
                                        left: pos.left + 'px',
                                        top: pos.top + 1 + 'px'
                                    });
                                }
                            }
                        })
                    ;
                    
                    return;
                })
                .click(function(event)
                {   
                    event.stopPropagation();

                    if (isCursorOverEditor && $('.al-popover:visible').length > 0) {
                        return false;
                    }

                    var $this = $(this);

                    if ($this.hasClass('al-empty-slot-placeholer')) {
                        alert(translate('You are trying to edit a placeholder for a slot which does not contain blocks: please do not edit this placeholder but simply add a new block to this slot'));

                        return false;
                    }

                    if ($('body').data('activeBlock') != null) {
                        stopEditElement($('body').data('activeBlock'));

                        if ($this.attr('data-name') == 'block_' + $('body').data('idBlock')) {
                            return false;
                        }
                    }

                    startEdit($this);
                    if (hasPopover) {
                        showPopover($this);
                    }

                    return false;
                })
            ;
            
            $this.find("a").unbind().click(function(event) {
                event.preventDefault();
            });
        });
    }
    
    function startEdit(element)
    {
        element.highligther('activate', {'elements' : {
            "top" : '.al_active_block_menu_top',
            "bottom" : '.al_active_block_menu_bottom',
            "left" : '.al_active_block_menu_left',
            "right" : '.al_active_block_menu_right'
        }});
        var parent = (element.attr('data-parent') != null && element.attr('data-parent').length > 0) ? element.attr('data-parent') : null;
        $('body')
            .data('idBlock', element.attr('data-block-id'))
            .data('slotName', element.attr('data-slot-name'))            
            .data('included', element.attr('data-included'))         
            .data('parent', parent)
            .data('activeBlock', element)
        ;
        $('#al_block_menu_toolbar').hide();
        
        $(document).trigger("startEditingBlocks", [ element ]);
    }
    
    function stopEditElement(element)
    {
        element.highligther('deactivate');
        
        element.each(function(){   
            var $this = $(this);
            $this.popover('destroy');
            startEditElement($this);
        });
        $('body').data('activeBlock', null); 

        $(document).trigger("stopEditingBlocks", [ element ]);
    }
    
    function showPopover(element)
    {
        element.popover('show');

        $('.al-popover:visible').each(function(){
            var popover = $(this);
            
            // prevents to close editor when interacting with the included elements 
            // like inputs, textarea and so on
            popover
                .mouseenter(function(){
                    isCursorOverEditor = true;
                })
                .mouseleave(function(){
                    isCursorOverEditor = false;
                })
            ;

            popover.position({
                my: "left top+10px",
                at: "left bottom",
                of: element,
                collision: "flipfit none"
            });
            
            
            $('.arrow').position({
                my: "left+20px top",
                at: "left bottom",
                of: element
            });
            
            // Forces always the data-title as popover title to avoid conflicts with 
            // image block
            popover.find('.popover-title').html(element.attr('data-title'));
        });
        
        $('.al_editor_save').each(function(){ 
            var $this = $(this);
            $this.unbind().click(function(){
                $('body').EditBlock('Content', $('#al_item_form').serialize());

                return false;
            });
        });
        
        $(document).trigger("popoverShow", [ element ]);
        
        $('.al-popover:visible').find('select').each(function(){
            $(this).on('click', function(event) {
                event.stopPropagation();
            });
        });
    }

    function activateEditableInlineContents()
    {          
        $(document)
            .find('[data-content-editable="true"]')
            .not('[data-texteditor-cfg="simple"]')
            .attr('contenteditable', true)
            .attr('data-texteditor-cfg', 'standard')
        ;
    }
    
    function deactivateEditableInlineContents()
    {
        $('[data-texteditor-cfg="standard"]')
            .removeAttr('data-texteditor-cfg contenteditable')
        ;
    }
    
    function hideContentsForEditMode(element)
    {
        $(element).each(function() {
            var $this = $(this);
            if($this.attr('data-hide-when-edit') == "true") {
                var html = $this.html();
                $this.html('<p>' + translate('A %type% block is not rendered when the editor is active', {'%type%' : $this.attr('data-type')}) + '</p>').data('html', html).addClass('is_hidden_in_edit_mode');
            }
        });
    }

    function showHiddenContentsFromEditMode()
    {
        $('.is_hidden_in_edit_mode').each(function() { 
            var $this = $(this);
            $this.html($this.data('html')).removeClass('is_hidden_in_edit_mode'); 
        });
    }
    
    $.fn.blocksEditor = function( method, options ) {        
        if ( methods[method] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.blocksEditor' );
        }   
    };
})( jQuery );

function Navigate(language, page)
{
    location.href = frontController + 'backend/' + language + '/' + page;
}

function translate(value, placeholders, domain)
{
    if (!domain) {
        domain = lang;
    }

    var found = domain[value];
    if (found == null) { // falls back to default language
        found = value;
    }
    
    if (placeholders != null) {
        $.each(placeholders, function(key, value){
            found = found.replace(key, value);
        });
    }
    
    return found;
}

$(document).ready(function(){
    try
    { 
        $('body').controlPanel('init');
        
        $('#al_start_slots_management').click(function() {
            if ($('.rk-stop-editor').is(':visible')) {    
                alert(translate("This operation is not allowed when you are editing the contents"));
                
                return false;
            }
            
            $('[data-editor="enabled"]').changeTheme('start');
            
            return false;
        });
        
        $('#al_stop_slots_management').click(function(){
            $('[data-editor="enabled"]').changeTheme('stop');
            $('.al_block_menu').hide();
            
            return false;
        });
        
        $('#al_cms_contents').click(function(){ 
            var block = $('body').data('activeBlock');
            if (block == null || $(document).blocksEditor('isCursorOverEditor')) { // Removed experimentally || block.attr('rel') == 'popover'
                return;
            }
            
            block.blocksEditor('stopEditElement');
            $(document).blocksEditor('stopCursorOverEditor');
        });
        
        $('.al_language_item').click(function()
        {
            Navigate($(this).attr('rel'), $('#al_pages_navigator').html());
            
            return false;
        });

        $('.al_page_item').click(function()
        {
            Navigate($('#al_languages_navigator').html(), $(this).attr('rel'));
            
            return false;
        });
            
        $('.rk-start-editor').click(function()
        {   
            $('[data-editor="enabled"]').blocksEditor('start');
            $('.rk-stop-editor').show();
            $('.rk-start-editor').hide();

            return false;
        });

        $('.rk-stop-editor').click(function()
        {
            $('[data-editor="enabled"]').trigger("editorStopping").blocksEditor('stop');
            $('.al_block_menu').hide();
            $('#al_block_menu_toolbar').hide();
            $('.al_blocks_list').hide();
            $('.rk-start-editor').show();
            $('.rk-stop-editor').hide();

            return false;
        });
        
        $("#al_tab a").click(function ()
        {
            $("#al_tab a").toggle();
                
            return false;
        });
        
        $("#al_toggle_edit_buttons a").click(function ()
        {
            if ($('#al_stop_slots_management').is(':visible')) {    
                return false;
            }
            
            $("#al_toggle_edit_buttons a").toggle();
                
            return false;
        });
        
        $("#al_toggle_slots_changer a").click(function ()
        {
            if ($('.rk-stop-editor').is(':visible')) {    
                return false;
            }
            
            $("#al_toggle_slots_changer a").toggle();
                
            return false;
        });
        
        $("#al_tab .al_tab").click(function ()
        {
            $(".al_tab").toggle();
                
            return false;
        });
        
        $(".al_tab_open").click(function ()
        {
            $("#al_control_panel_body").toggle();
            $('#al_tab').css('top', $("#al_control_panel_body").height() + 'px');
                
            return false;
	    });
        
        $(".al_tab_close").click(function ()
        {
            $('#al_tab').css('top', '0');
            $("#al_control_panel_body").toggle();
                
            return false;
	    });
        
        $('#al_show_navigation').click(function ()
        {
            $("#al_toggle_nav_button").toggle();
                
            return false;
        });
        
        $('#al_open_users_manager').security('users_list');

        $('#al_logout').click(function()
        {
            location.href = frontController + 'backend/logout';
        });
        
        $('#al_open_pages_panel').click(function()
        {
            try{
                $.ajax({
                    type: 'POST',
                    url: frontController + 'backend/' + $('#al_available_languages option:selected').val() + '/al_showPages',
                    data: {
                        'page' :  $('#al_pages_navigator').html(),
                        'language' : $('#al_languages_navigator').html()
                    },
                    beforeSend: function()
                    {
                        $('body').AddAjaxLoader();
                    },
                    success: function(html)
                    {
                        $('#al_panel').OpenPanel(html, function(){
                            $('body').pages('init');
                        });
                    },
                    error: function(err)
                    {
                        $('body').showAlert(err.responseText, 0, 'alert-error alert-danger');
                    },
                    complete: function()
                    {
                        $('body').RemoveAjaxLoader();
                    }
                });
            }
            catch(e){
                $('body').showAlert('An unespected error occoured in redkite.js file while opening the pages panel. Here is the error from the server:<br/><br/>' + e + '<br/><br/>Please open an issue at <a href="https://github.com/redkite-labs/RedKiteCmsBundle/issues">Github</a> reporting this entire message.', 0, 'alert-error alert-danger');
            }

            return false;
        });

        $('#al_open_languages_panel').click(function()
        {
            try{
                $.ajax({
                    type: 'POST',
                    url: frontController + 'backend/' + $('#al_available_languages option:selected').val() + '/al_showLanguages',
                    data: {
                        'page' :  $('#al_pages_navigator').html(),
                        'language' : $('#al_languages_navigator').html()
                    },
                    beforeSend: function()
                    {
                        $('body').AddAjaxLoader();
                    },
                    success: function(html)
                    {
                        $('#al_panel').OpenPanel(html, function(){
                            $('body').languages('init');
                        });
                    },
                    error: function(err)
                    {
                        $('body').showAlert(err.responseText, 0, 'alert-error alert-danger');
                    },
                    complete: function()
                    {
                        $('body').RemoveAjaxLoader();
                    }
                });
            }
            catch(e){
                $('body').showAlert('An unespected error occoured in redkite.js file while opening the languages panel. Here is the error from the server:<br/><br/>' + e + '<br/><br/>Please open an issue at <a href="https://github.com/redkite-labs/RedKiteCmsBundle/issues">Github</a> reporting this entire message.', 0, 'alert-error alert-danger');
            }

            return false;
        });

        $('#al_open_themes_panel').click(function()
        {
            try{
                $.ajax({
                    type: 'POST',
                    url: frontController + 'backend/' + $('#al_available_languages option:selected').val() + '/al_showThemesPanel',
                    data: {
                        'page' :  $('#al_pages_navigator').html(),
                        'language' : $('#al_languages_navigator').html()
                    },
                    beforeSend: function()
                    {
                        $('body').AddAjaxLoader();
                    },
                    success: function(html)
                    {
                        $('#al_panel').OpenPanel(html, function(){
                            $('body').manageTheme('init');
                        });
                    },
                    error: function(err)
                    {
                        $('body').showAlert(err.responseText, 0, 'alert-error alert-danger');
                    },
                    complete: function()
                    {
                        $('body').RemoveAjaxLoader();
                    }
                });
            }
            catch(e){
                $('body').showAlert('An unespected error occoured in redkite.js file while opening the themes panel. Here is the error from the server:<br/><br/>' + e + '<br/><br/>Please open an issue at <a href="https://github.com/redkite-labs/RedKiteCmsBundle/issues">Github</a> reporting this entire message.', 0, 'alert-error alert-danger');
            }

            return false;
        });

        $('#al_open_media_library').click(function()
        {
            $('<div />').dialogelfinder({
                url: frontController + 'backend/' + $('#al_available_languages option:selected').val() + '/al_elFinderMediaConnect',
                lang : $('#al_available_languages option:selected').val(),
                width : 840,
                destroyOnClose : true
             });

            return false;
        });

        $('.al_deployer').click(function()
        {
            var env = $(this).attr('rel');
            if ( ! confirm(translate('Are you sure to start the deploying of "%env%" environment', {'%env%' : env}))) {
                return;
            }
            
            try{
                $.ajax({
                    type: 'POST',
                    url: frontController + 'backend/' + $('#al_available_languages option:selected').val() + '/al_' + env + 'Deploy',
                    data: {'page' :  $('#al_pages_navigator').attr('rel'),
                        'language' : $('#al_languages_navigator').attr('rel')},
                    beforeSend: function()
                    {
                        $('body').AddAjaxLoader();
                    },
                    success: function(html)
                    {
                        $('body').showAlert(html);
                    },
                    error: function(err)
                    {
                        $('body').showAlert(err.responseText, 0, 'alert-error alert-danger');
                    },
                    complete: function()
                    {
                        $('body').RemoveAjaxLoader();
                    }
                });
            }
            catch(e){
                $('body').showAlert('An unespected error occoured in redkite.js file while deploying the website. Here is the error from the server:<br/><br/>' + e + '<br/><br/>Please open an issue at <a href="https://github.com/redkite-labs/RedKiteCmsBundle/issues">Github</a> reporting this entire message.', 0, 'alert-error alert-danger');
            }

            return false;
        });
        
        $('#al_available_languages').change(function()
        {
            try{
                var languageName = $('#al_available_languages option:selected').val();            
                $.ajax({
                    type: 'POST',
                    url: frontController + 'backend/' + $('#al_available_languages option:selected').val() + '/al_changeCmsLanguage',
                    data: {'page' :  $('#al_pages_navigator').attr('rel'),
                        'language' : $('#al_languages_navigator').attr('rel'),
                        'languageName' : languageName
                    },
                    beforeSend: function()
                    {
                        $('body').AddAjaxLoader();
                    },
                    success: function(html)
                    {
                        $('body').showAlert(html);

                        Navigate($('#al_languages_navigator').html(), $('#al_pages_navigator').html());
                    },
                    error: function(err)
                    {
                        $('body').showAlert(err.responseText, 0, 'alert-error alert-danger');
                    },
                    complete: function()
                    {
                        $('body').RemoveAjaxLoader();
                    }
                });
            }
            catch(e){
                $('body').showAlert('An unespected error occoured in redkite.js file while changing RedKite CMS language. Here is the error from the server:<br/><br/>' + e + '<br/><br/>Please open an issue at <a href="https://github.com/redkite-labs/RedKiteCmsBundle/issues">Github</a> reporting this entire message.', 0, 'alert-error alert-danger');
            }

            return false;
        });
    }
    catch(e)
    {
        alert(e);
    }
});