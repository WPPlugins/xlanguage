if (typeof(tinymce)!="undefined")
{
    // WordPress 2.5+ / TinyMCE 3
    ( function() {
        tinymce.create('tinymce.plugins.xLanguagePlugin', {
            getInfo:function() {
                return { longname:'xLanguage', author:'Sam Wong', authorurl:'http://hellosam.net', infourl:'http://hellosam.net/xlanguage', version:'2.0' };
            },
            init:function(ed,url) {
                ed.addCommand('xlanguage_clear', function() { this._applyLang(ed, ''); }, this);
                ed.addCommand('xlanguage_highlight', function() {
                    var doc = ed.getDoc();
                    var body = (doc.getElementsByTagName('body'))[0];
                    var classes = body.className ? body.className : '';
                    if (/ xlanguage-highlight/.test(classes)) {
                        body.className = classes.replace(' xlanguage-highlight', '');
                    } else {
                        body.className = classes + ' xlanguage-highlight';
                    }
                });
                ed.addButton('xlanguage_clear', {title:'Clear the language tag of the selected text (Ctrl+Alt+0)',cmd:'xlanguage_clear',image:wp_base_xlanguage+'/images/lang0.png'});
                ed.addShortcut('ctrl+alt+0', 'Clear the language tag of the selected text', 'xlanguage_clear');
                ed.addButton('xlanguage_highlight', {title:'Highlight the paragraph language (Ctrl+Alt+Shift+0)',cmd:'xlanguage_highlight',image:wp_base_xlanguage+'/images/langh.png'});
                ed.addShortcut('ctrl+alt+shift+0', 'Highlight the paragraph language', 'xlanguage_highlight');
                
                var lang_func = function(ui, value) { this._applyLang(ed, value); };
                for (var i = 1; i <= xlanguage_language.length; i++) {
                    var lang = xlanguage_language[i-1];
                    ed.addButton('xlanguage__'+lang+'__'+i, {title:'Tag as <'+lang+'> (Ctrl+Alt+'+i+')',cmd:'xlanguage_lang_'+i,image:wp_base_xlanguage+'/images/lang'+(i>5?'0':i)+'.png',value:lang});
                    ed.addCommand('xlanguage_lang_'+i, lang_func, this);
                    ed.addShortcut('ctrl+alt+'+i,'Tag as <'+lang+'>',['xlanguage_lang_'+i,false,lang]);
                } 

                // Hacking the undoManager to provide our own BeginUndoLevel functionalities as they have removed it
                ed.onInit.add(function(){
                    ed.undoManager.realAdd = ed.undoManager.add;
                    ed.undoManager.inBeginUndoLevel = false;
                    ed.undoManager.add = function(l) {
                        if (!this.inBeginUndoLevel) this.realAdd(l);
                    };
                });
            },
            /* functions from TinyMCE's compat2x */
            _getElementsByAttributeValue:function(n,e,a,v){var i,nl=n.getElementsByTagName(e),o=[];for(i=0;i<nl.length;i++){if(tinyMCE.activeEditor.dom.getAttrib(nl[i],a).indexOf(v)!=-1)o[o.length]=nl[i];}return o;},
            _renameElement : function(e, n, d) {
                var ne, i, ar;
                if (e) {
                    ne = d.createElement(n); 
                    ar = e.attributes;
                    for (i=ar.length-1; i>-1; i--) {
                        if (ar[i].specified && ar[i].nodeValue) ne.setAttribute(ar[i].nodeName.toLowerCase(), ar[i].nodeValue);
                    } 
                    ar = e.childNodes;
                    for (i=0; i<ar.length; i++) ne.appendChild(ar[i].cloneNode(true)); 
                    e.parentNode.replaceChild(ne, e);
                }
            },
            /**
             * Apply the language tag to the selection
             *
             * @param {object} inst The TinyMCE editor instance
             * @param {string} lang The language code to apply, empty if to clear the language tag instead
             * @return {void}
             */
            _applyLang : function (inst, lang) {
                var doc = inst.getDoc();
                var obj, bm, obm;
                
                inst.execCommand('mceBeginUndoLevel');
                inst.undoManager.inBeginUndoLevel = true; // Until mceBeginUndoLevel is working, we need this hack
                
                obm = inst.selection.getBookmark();
                
                if (inst.selection.isCollapsed()) {
                    inst.selection.select(
                        inst.dom.getParent(inst.selection.getNode(), function(n) { return inst.dom.isBlock(n); }, inst.dom.getRoot()),
                        false, false);
                }

                bm = inst.selection.getBookmark();

                if (xlanguage_mode == 1) {
                    // XHtml Mode
                    
                    // Backup the real strike
                    obj = doc.getElementsByTagName('STRIKE');
                    for (var i = obj.length - 1; i >= 0; i--) {
                        var e = obj[i];
                        e.setAttribute('mce_xlanguage', 'backup');
                        this._renameElement(e, 'SPAN', doc);
                    }
                    // Change the span-lang to strike
                    obj = this._getElementsByAttributeValue(doc, 'SPAN', 'lang', '');
                    for (var i = obj.length - 1; i >= 0; i--) {
                        var e = obj[i];
                        if (!e.getAttribute('mce_xlanguage'))
                            this._renameElement(e, 'STRIKE', doc);
                    }
                    
                    // Apply strike
                    inst.selection.moveToBookmark(bm);
                    inst.execCommand('Strikethrough');
                    inst.selection.moveToBookmark(bm);
                    inst.execCommand('Strikethrough');
                    inst.selection.moveToBookmark(bm);
                    // Does the selection does have strike already...?
                    var state = inst.selection.getNode().nodeName == 'STRIKE' || ( /<(strike)(?: [^>]*)?>.*<\/\1>/i.test(inst.selection.getContent()));
                    if (state && !lang || !state && lang) {
                        // Apply one more time, depends on if we want lang or not
                        inst.execCommand('Strikethrough');
                    }
                    inst.selection.moveToBookmark(bm);
                    
                    // Change the strike back to span-lang
                    obj = doc.getElementsByTagName('STRIKE');
                    for (var i = obj.length - 1; i >= 0; i--) {
                        var e = obj[i];
                        if (!e.getAttribute('lang'))
                            e.setAttribute('lang', lang);
                        this._renameElement(e, 'SPAN', doc);
                    }

                    // Restore the real strike
                    obj = this._getElementsByAttributeValue(doc, 'SPAN', 'mce_xlanguage', 'backup');
                    for (var i = obj.length - 1; i >= 0; i--) {
                        var e = obj[i];
                        e.removeAttribute('mce_xlanguage');
                        this._renameElement(e, 'STRIKE', doc);
                    }
                } else {
                    // SB Mode
                    // [xlanguage_sb_prefix]

                    inst.selection.moveToBookmark(bm);
                    var re_lang = new RegExp("^(?:<[^>]+>)?\\[(" + xlanguage_sb_prefix + "[^\\]]*)\\](.*)\\[/\\1\\](?:</[^>]+>)?$");
                    var re_tag = new RegExp("^<(([^ >]+)(?:[^>]*)?)>(.*)</\\2>$");

                    var text = inst.selection.getContent();
                    var match_lang = re_lang.exec(text);
                    var match_tag = re_tag.exec(text);
                    
                    var match_text = '';
                    var pre_text = '';
                    var post_text = '';
                    if (match_tag != null) {
                        pre_text = '<' + match_tag[1] + '>';
                        post_text = '</' + match_tag[2] + '>';
                        text = match_tag[3];
                    }
                    if (match_lang != null) {
                        match_text = match_lang[2];
                    }
                    
                    if (match_text != '') {
                        // Match
                        if (lang != '') {
                            // Change Lang
                            inst.execCommand('mceReplaceContent', false, pre_text + '[' + xlanguage_sb_prefix + lang + ']' + match_text + '[/' + xlanguage_sb_prefix + lang + ']' + post_text);
                        } else {
                            // Clear Lang
                            inst.execCommand('mceReplaceContent', false, pre_text + match_text + post_text);
                        }
                    } else {
                        // No Match
                        if (lang != '') {
                            // Add Lang
                            inst.execCommand('mceReplaceContent', false, pre_text + '[' + xlanguage_sb_prefix + lang + ']' + text + '[/' + xlanguage_sb_prefix + lang + ']' + post_text);
                        }
                    }
                }
                inst.selection.moveToBookmark(obm);
                
                inst.undoManager.inBeginUndoLevel = false;
                inst.execCommand('mceEndUndoLevel');
                
                inst.execCommand('mceRepaint');
                inst.addVisual();
            }
        });
        tinymce.PluginManager.add('xlanguage', tinymce.plugins.xLanguagePlugin);
    } )();
} else
{
    // WordPress < 2.5 / TinyMCE 2
    var TinyMCE_xLanguagePlugin = {
        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @returns Name/value array containing information about the plugin.
         * @type Array 
         */
        getInfo : function() {
            return {
                longname : 'xLanguage',
                author : 'Sam Wong',
                authorurl : 'http://hellosam.net',
                infourl : 'http://hellosam.net/xlanguage',
                version : '1.0'
            };
        },

        /**
         * Gets executed when a TinyMCE editor instance is initialized.
         *
         * @param {TinyMCE_Control} Initialized TinyMCE editor control instance. 
         */
        initInstance : function(inst) {
            // You can take out plugin specific parameters
            // Register custom keyboard shortcut
            for (var i = 0; i < xlanguage_language.length; i++) {
                inst.addShortcut('ctrl+alt', '' + (i + 1), "lang_xlanguage__" + xlanguage_language[i].replace(/-/g, '_'), 'xlanguage_lang', false, xlanguage_language[i]);
            }
            inst.addShortcut('ctrl+alt', '0', 'lang_xlanguage_clear', 'xlanguage_clear');
            inst.addShortcut('ctrl+alt', '`', 'lang_xlanguage_highlight', 'xlanguage_highlight');
        },

        /**
         * Gets executed when a TinyMCE editor instance is removed.
         *
         * @param {TinyMCE_Control} Removed TinyMCE editor control instance. 
         */
        removeInstance : function(inst) {
            // Cleanup instance resources
        },

        /**
         * Gets executed when a TinyMCE editor instance is displayed using for example mceToggleEditor command.
         *
         * @param {TinyMCE_Control} Visible TinyMCE editor control instance. 
         */
        showInstance : function(inst) {
            // Show instance resources
        },

        /**
         * Gets executed when a TinyMCE editor instance is hidden using for example mceToggleEditor command.
         *
         * @param {TinyMCE_Control} Hidden TinyMCE editor control instance. 
         */
        hideInstance : function(inst) {
            // Hide instance resources
        },

        /**
         * Returns the HTML code for a specific control or empty string if this plugin doesn't have that control.
         * A control can be a button, select list or any other HTML item to present in the TinyMCE user interface.
         * The variable {$editor_id} will be replaced with the current editor instance id and {$pluginurl} will be replaced
         * with the URL of the plugin. Language variables such as {$lang_somekey} will also be replaced with contents from
         * the language packs.
         *
         * @param {string} cn Editor control/button name to get HTML for.
         * @return HTML code for a specific control or empty string.
         * @type string
         */
        getControlHTML : function(cn) {
            switch (cn) {
                case 'xlanguage_clear':
                    return tinyMCE.getButtonHTML(cn, 'lang_' + cn, wp_base_xlanguage + '/images/lang0.png', cn);
                case 'xlanguage_highlight':
                    return tinyMCE.getButtonHTML(cn, 'lang_' + cn, wp_base_xlanguage + '/images/langh.png', cn);
            }

            var m;
            if ( (m = /^xlanguage__(.+)__(\d+)$/.exec(cn)) ) {
                return tinyMCE.getButtonHTML(cn, 'lang_xlanguage__' + m[1].replace(/-/g,'_'), wp_base_xlanguage + '/images/lang' + (m[2] > 5 ? '0' : m[2]) + '.png', 'xlanguage_lang', false, m[1]);
            }

            return "";
        },

        /**
         * Executes a specific command, this function handles plugin commands.
         *
         * @param {string} editor_id TinyMCE editor instance id that issued the command.
         * @param {HTMLElement} element Body or root element for the editor instance.
         * @param {string} command Command name to be executed.
         * @param {string} user_interface True/false if a user interface should be presented.
         * @param {mixed} value Custom value argument, can be anything.
         * @return true/false if the command was executed by this plugin or not.
         * @type
         */
        execCommand : function(editor_id, element, command, user_interface, value) {
            var inst = tinyMCE.getInstanceById(editor_id);

            // Handle commands
            switch (command) {
                // Force the selection to a specific language
                // Apply to the parent block element if there is no selection
                case 'xlanguage_lang':
                    this._applyLang(inst, value);
                    return true;
                case 'xlanguage_clear':
                    this._applyLang(inst, '');
                    return true;
                case 'xlanguage_highlight':
                    var doc = inst.getDoc();
                    var body = (doc.getElementsByTagName('body'))[0];
                    var classes = body.className ? body.className : '';
                    if (/ xlanguage-highlight/.test(classes)) {
                        body.className = classes.replace(' xlanguage-highlight', '');
                    } else {
                        body.className = classes + ' xlanguage-highlight';
                    }
                    return true;
            }

            // Pass to next handler in chain
            return false;
        },

        /**
         * Apply the language tag to the selection
         *
         * @param {object} inst The TinyMCE editor instance
         * @param {string} lang The language code to apply, empty if to clear the language tag instead
         * @return {void}
         */
        _applyLang : function (inst, lang) {
            inst.execCommand('mceBeginUndoLevel');
            
            var doc = inst.getDoc();
            
            var obj, bm, obm;
            obm = inst.selection.getBookmark();
            
            if (inst.selection.isCollapsed()) {
                inst.selection.selectNode(tinyMCE.getParentBlockElement(inst.selection.getFocusElement()), false, false);
            }

            bm = inst.selection.getBookmark();

            if (xlanguage_mode == 1) {
                // XHtml Mode
                
                // Backup the real strike
                obj = doc.getElementsByTagName('STRIKE');
                for (var i = obj.length - 1; i >= 0; i--) {
                    var e = obj[i];
                    e.setAttribute('mce_xlanguage', 'backup');
                    tinyMCE.renameElement(e, 'SPAN', doc);
                }
                // Change the span-lang to strike
                obj = tinyMCE.getElementsByAttributeValue(doc, 'SPAN', 'lang', '');
                for (var i = obj.length - 1; i >= 0; i--) {
                    var e = obj[i];
                    if (!e.getAttribute('mce_xlanguage'))
                        tinyMCE.renameElement(e, 'STRIKE', doc);
                }
                
                // Apply strike
                inst.selection.moveToBookmark(bm);
                inst.execCommand('Strikethrough');
                inst.selection.moveToBookmark(bm);
                inst.execCommand('Strikethrough');
                inst.selection.moveToBookmark(bm);
                // Does the selection does have strike already...?
                var state = inst.selection.getFocusElement().nodeName == 'STRIKE' || ( /<(strike)(?: [^>]*)?>.*<\/\1>/i.test(inst.selection.getSelectedHTML()));
                if (state && !lang || !state && lang) {
                    // Apply one more time, depends on if we want lang or not
                    inst.execCommand('Strikethrough');
                }
                inst.selection.moveToBookmark(bm);
                
                // Change the strike back to span-lang
                obj = doc.getElementsByTagName('STRIKE');
                for (var i = obj.length - 1; i >= 0; i--) {
                    var e = obj[i];
                    if (!e.getAttribute('lang'))
                        e.setAttribute('lang', lang);
                    tinyMCE.renameElement(e, 'SPAN', doc);
                }

                // Restore the real strike
                obj = tinyMCE.getElementsByAttributeValue(doc, 'SPAN', 'mce_xlanguage', 'backup');
                for (var i = obj.length - 1; i >= 0; i--) {
                    var e = obj[i];
                    e.removeAttribute('mce_xlanguage');
                    tinyMCE.renameElement(e, 'STRIKE', doc);
                }
            } else {
                // SB Mode
                // [xlanguage_sb_prefix]

                inst.selection.moveToBookmark(bm);
                var re_lang = new RegExp("^(?:<[^>]+>)?\\[(" + xlanguage_sb_prefix + "[^\\]]*)\\](.*)\\[/\\1\\](?:</[^>]+>)?$");
                var re_tag = new RegExp("^<(([^ >]+)(?:[^>]*)?)>(.*)</\\2>$");

                var text = inst.selection.getSelectedHTML();
                var match_lang = re_lang.exec(text);
                var match_tag = re_tag.exec(text);
                
                var match_text = '';
                var pre_text = '';
                var post_text = '';
                if (match_tag != null) {
                    pre_text = '<' + match_tag[1] + '>';
                    post_text = '</' + match_tag[2] + '>';
                    text = match_tag[3];
                }
                if (match_lang != null) {
                    match_text = match_lang[2];
                }
                
                if (match_text != '') {
                    // Match
                    if (lang != '') {
                        // Change Lang
                        inst.execCommand('mceReplaceContent', false, pre_text + '[' + xlanguage_sb_prefix + lang + ']' + match_text + '[/' + xlanguage_sb_prefix + lang + ']' + post_text);
                    } else {
                        // Clear Lang
                        inst.execCommand('mceReplaceContent', false, pre_text + match_text + post_text);
                    }
                } else {
                    // No Match
                    if (lang != '') {
                        // Add Lang
                        inst.execCommand('mceReplaceContent', false, pre_text + '[' + xlanguage_sb_prefix + lang + ']' + text + '[/' + xlanguage_sb_prefix + lang + ']' + post_text);
                    }
                }
            }
            inst.selection.moveToBookmark(obm);
            inst.repaint();
            
            tinyMCE.handleVisualAid(inst.getBody(), true, tinyMCE.settings['visual']);
            inst.execCommand('mceEndUndoLevel');
        }
    };

    // Adds the plugin class to the list of available TinyMCE plugins
    tinyMCE.addPlugin('xlanguage', TinyMCE_xLanguagePlugin);

    var xlanguage_language_lang = new function() { 
        for (var i = 1; i <= xlanguage_language.length; i++) {
            this["xlanguage__" + xlanguage_language[i-1].replace(/-/g, '_')] = "Tag as <" + xlanguage_language[i-1] + "> (Ctrl+Alt+"+i+")";
        }
    };
    tinyMCE.addToLang('',xlanguage_language_lang);
    tinyMCE.addToLang('',{
    'xlanguage_clear': 'Clear the language tag of the selected text (Ctrl+Alt+0)',
    'xlanguage_highlight': 'Highlight the paragraph language (Ctrl+Alt+`)'
    });
}
