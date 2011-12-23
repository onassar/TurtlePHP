
// page init
var init = function() {

    // select project (stored via HTMLElement reference)
    var selected;

    // jigsaw reference
    var jigsaw = (new Jigsaw(
        $('thumbs'),
        $('thumbs').getElements('li')
    ));

    // tag events
    $('tags').getElements('a').addEvents({
        'click': function(event) {
            event.stop();

            // 
            var previous = selected;
            selected = null;
            if (previous) {
                previous.fireEvent('mouseout');
            }
            
            // 
            $('tags').getElement('li a.active').removeClass('active');
            this.addClass('active');
            var path = this.get('href'),
                category = path.split('/')[2];
            jigsaw.filter(category);
        }
    });

    // thumb events
    $('thumbs').getElements('li').addEvents({
        'mouseover': function(event) {
            var classes = this.className;
            $('tags').set('class', 'clear expose ' + classes);
        },
        'mouseout': function(event) {
            if (typeOf(selected) === 'null') {
                $('tags').set('class', 'clear');
            }
        }
    });

    // thumb anchor events
    var anchors = $('thumbs').getElements('a');
    anchors.each(function(anchor) {
        anchor.store('title', anchor.get('title'));
        anchor.set('title', '');
    });
    anchors.addEvents({
        'click': function(event) {
            event.stop();

            // make all active inactive
            var active = $('details').getElement('li.active');
            if (active) {
                active.removeClass('active');
            }

            // grab position of anchor's parent from all items
            var parent = this.getParent().getParent(),
                project = parent.get('rel'),
                all = $('thumbs').getElements('li'),
                offset = all.indexOf(parent);

            // remove from all
            all.splice(offset, 1);
            jigsaw.hide(all);

            // show parent
            jigsaw.show([parent]);

            // set the selected project
            selected = parent;
            setTimeout(function() {
                var block = $('details').getElement('li.' + project);
                block.addClass('active');
            }, 500);
        },
        'mouseover': function(event) {
            
        }
    });
};
