(function() {
     tinymce.PluginManager.add('spingo_calendar_editor', function(editor, url) {
          function addToolbar( node ) {
               var rectangle, toolbarHtml, toolbar, left,
                    dom = editor.dom;

               removeToolbar();

               dom.setAttrib( node, 'data-spingo-calendar-edit', 1 );
               rectangle = dom.getRect( node );

               toolbarHtml = '<i class="dashicons dashicons-edit edit" data-mce-bogus="all"></i>' +
                    '<i class="dashicons dashicons-no-alt remove" data-mce-bogus="all"></i>';

               toolbar = dom.create( 'p', {
                    'id': 'spingo-calendar-toolbar',
                    'data-mce-bogus': 'all',
                    'contenteditable': false
               }, toolbarHtml );

               if ( editor.rtl ) {
                    left = rectangle.x + rectangle.w - 82;
               } else {
                    left = rectangle.x;
               }

               editor.getBody().appendChild( toolbar );
               dom.setStyles( toolbar, {
                    top: rectangle.y,
                    left: left
               });

               toolbarActive = true;
          }

          function changeCalendar(data) {
               var calendarFields = [
                    {
                         type: 'textbox', 
                         name: 'calendar_id', 
                         label: 'Partner ID'
                    },{
                         type: 'textbox', 
                         name: 'calendar_parent_url', 
                         label: 'Calendar URL'
                    },{
                         type: 'textbox', 
                         name: 'calendar_twitter_handle', 
                         label: 'Twitter Handle'
                    },{
                         type: 'listbox', 
                         name: 'calendar_user_location', 
                         label: 'Use User Location',
                         values: [
                              { text: 'No', value: false },
                              { text: 'Yes', value: true }
                         ]
                    },{
                         type: 'textbox', 
                         name: 'calendar_postal_code', 
                         label: 'Postal Code'
                    },{
                         type: 'textbox', 
                         name: 'calendar_radius_miles', 
                         label: 'Radius (in Miles)'
                    },{
                         type: 'listbox',
                         name: 'pushstate',
                         label: 'Use PushState',
                         values: [
                              { text: 'No', value: false },
                              { text: 'Yes', value: true }
                         ]
                    },{
                         type: 'textbox',
                         name: 'section_ids',
                         label: 'Section IDs'
                    },{
                         type: 'listbox',
                         name: 'calendar_mode_type',
                         label: 'Mode Type',
                         values: [
                              { text: 'Events', value: 'events' },
                              { text: 'Contributor', value: 'contributor' },
                              { text: 'Venue', value: 'venue' }
                         ]
                    },{
                         type: 'textbox',
                         name: 'calendar_mode_id',
                         label: 'Mode ID'
                    },{
                         type: 'listbox',
                         name: 'default_view',
                         label: 'Default View',
                         values: [
                              { text: 'Grid', value: 'grid' },
                              { text: 'List', value: 'list' },
                              { text: 'Map', value: 'map' }
                         ]
                    }
               ];
               var formFields = [
                    {
                         type: 'textbox',
                         name: 'default_color',
                         label: 'Default Color'
                    },{
                         type: 'textbox',
                         name: 'accent_color',
                         label: 'Accent Color'
                    },{
                         type: 'listbox',
                         name: 'dark_background',
                         label: 'Dark Background',
                         values: [
                              { text: 'No', value: false },
                              { text: 'Yes', value: true }
                         ]
                    },{
                         type: 'textbox',
                         name: 'fixed_top_offset',
                         label: 'Fixed Top Offset'
                    }
               ];
               editor.windowManager.open( {
                    title: 'Change Calendar Embed',
                    data: data,
                    width: 800,
                    height: 450,
                    body: [{
                         type: 'form',
                         layout: 'flow',
                         items: [
                         {
                              type: 'label',
                              label: 'Calendar'
                         },
                         {
                              type: 'form',
                              layout: 'grid',
                              columns: 2,
                              spacingH: 40,
                              items: calendarFields
                         },
                         {
                              type: 'label',
                              label: 'Theme'
                         },
                         {
                              type: 'form',
                              layout: 'grid',
                              columns: 2,
                              spacingH: 40,
                              alignH: 'stretch',
                              items: formFields,
                              minWidth: 300
                         }]
                    }],
                    onsubmit: function(e) {
                         var attrs = getShortcodeAttrs(e.data);
                         var updatedCode = '[spingo_calendar '+attrs.join(' ')+']';
                         editor.insertContent(updatedCode);
                         removeToolbar();
                    }
               });
          }

          function getShortcodeAttrs(formData) {
               var attrs = [];
               for (var property in formData) {
                    var value = formData[property];
                    if (value !== "") {
                         attrs.push(property+"=\""+value+"\"");
                    }
               }
               return attrs;
          }

          function isToolbarButton( node ) {
               return (node && node.nodeName === 'I' && node.parentNode.id === 'spingo-calendar-toolbar');
          }

          function removeToolbar() {
               var toolbar = editor.dom.get( 'spingo-calendar-toolbar' );
               if (toolbar) {
                    editor.dom.remove(toolbar);
               }
          }

          function postProcess(co) {
               function getAttr(s, n) {
                    n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
                    return n ? tinymce.DOM.decode(n[1]) : '';
               }

               return co.replace(/(?:<p[^>]*>)*(<img[^>]+>)(?:<\/p>)*/g, function(a,im) {
                    var cls = getAttr(im, 'class');
                    if ( cls.indexOf('spingo-calendar-box') != -1 ) {
                         var attrs = decodeURIComponent(getAttr(im, 'data-sh-attr'));
                         return '<p>[spingo_calendar'+attrs+']</p>';
                    }
                    return a;
               });
          }

          editor.on('BeforeSetcontent', function(evt){
               evt.content = evt.content.replace(/\[spingo_calendar([^\]]*)\]/g, function(all, attrs, con){
                    var newContent = '<img class="spingo-calendar-box mceItem" data-sh-attr="'+encodeURIComponent(attrs)+'" />';
                    return newContent;
               });
          });
          editor.on('PostProcess', function(evt) {
               if (evt.content) {
                    evt.content = postProcess(evt.content);
               }
          });
          editor.on( 'mousedown', function( event ) {
               if ( isToolbarButton( event.target ) ) {
                    if ( tinymce.Env.ie ) {
                         event.preventDefault();
                    }
               } else if ( event.target.nodeName !== 'IMG' ) {
                    removeToolbar();
               }
          });
          var current = null;
          editor.on('mouseup touchend', function(evt) {
               if (evt.target.nodeName == 'IMG' && editor.dom.hasClass(evt.target, 'spingo-calendar-box') ) {
                    addToolbar(evt.target);
                    current = evt.target;
               } else if (isToolbarButton(evt.target)) {
                    if (editor.dom.hasClass(evt.target, 'edit')) {
                         var data = wp.shortcode.attrs(decodeURIComponent(editor.dom.getAttrib(current, 'data-sh-attr'))).named;
                         for (var property in data) {
                              if (data[property] == 'true') {
                                   data[property] = true;
                              } else if (data['property'] == 'false') {
                                   data[property] = false;
                              }
                         }
                         changeCalendar(data);
                    } else if (editor.dom.hasClass(evt.target, 'remove')) {
                         editor.dom.remove(current);
                         removeToolbar();
                         current = null;
                    } else {
                         current = null;
                    }
               } else {
                    current = null;
               }
          });
     });
})();