
/**
 * Jigsaw
 * 
 * MooTools based class that enables the filtering of a list of items through
 * position and opacity tweening. Filtered elements are distinguished by their
 * classes.
 * 
 * @author Oliver Nassar <onassar@gmail.com>
 * @notes  injects wrapper around element-contents to maintain any element
 *         css3-transitions without complications (eg. otherwise setting
 *         position would follow transition rather than explicit set call).
 * @example
 * <code>
 *     // instantiation
 *     var jigsaw = (new Jigsaw(
 *         $$('ul#parent').shift(),
 *         $$('ul#parent').shift().getElements('li')
 *     ));
 *     
 *     // events
 *     $$('nav').shift().getElements('a').addEvent(
 *         'click',
 *         function(event) {
 *             event.stop();
 *             jigsaw.filter(this.get('rel'));
 *         }
 *     );
 * </code>
 */
var Jigsaw = (new Class({

    /**
     * Implements.
     * 
     * @public
     * @var Class
     */
    Implements: Options,

    /**
     * __coordinates. 
     * 
     * @private
     * @var Array
     */
    __coordinates: [],

    /**
     * __container. 
     * 
     * @private
     * @var HTMLElement
     */
    __container: null,

    /**
     * __elements. 
     * 
     * @private
     * @var Array
     */
    __elements: [],

    /**
     * __wrappers. 
     * 
     * @private
     * @var Array
     */
    __wrappers: [],

    /**
     * options. 
     * 
     * @public
     * @var Object
     */
    options: {
        duration: 500,
        opacity: 1,
        events: {
            hidden: function(morph) {
                morph.element.setStyle('visibility', 'hidden');
            },
            shown: function(morph) {
            },
            toggled: function(morph, toggled) {
                if (!toggled) {
                    this.options.events.hidden(morph);
                }
            }
        }
    },

    /**
     * initialize. 
     * 
     * @public
     * @param HTMLElement container
     * @param Array elements
     * @param Object options
     * @return void
     */
    initialize: function(container, elements, options) {
        this.__container = container;
        this.__elements = elements;
        this.setOptions(options);
        this.setup();
    },

    /**
     * filter. Filters the elements passed in during instantiation based on a
     *     class or array of classes that the element must much.
     * 
     * @public
     * @param String|Array klass
     * @return void
     */
    filter: function(klass) {

        // relevant/non elements
        var relevant = [],
            non = [];

        // loop through elements
        var x, l;
        this.__elements.each(function(element) {
            if (typeOf(klass) === 'string') {
                if (element.hasClass(klass)) {
                    relevant.push(element);
                } else {
                    non.push(element);
                }
            } else if (typeOf(klass) === 'array') {
                var found = false;
                for (x = 0, l = klass.length; x < l; ++x) {
                    if (element.hasClass(klass[x])) {
                        found = true;
                        break;
                    }
                }
                if (found) {
                    relevant.push(element);
                } else {
                    non.push(element);
                }
            } else {
                non.push(element);
            }
        });

        // hide non-relevant elements
        this.hide(non);

        // show relevant elements
        this.show(relevant);
    },

    /**
     * hide. Hides the passed in elements (setting their z-index to be less than
     *     those that were shown, to therefore be behind them).
     * 
     * @public
     * @param Array elements
     * @return void
     */
    hide: function(elements) {
        var morph, wrapper;
        elements.each(function(element) {
            wrapper = element.getElement('.JigsawElementWrapper');
            morph = wrapper.retrieve('JigsawMorph');
            morph.start({
                opacity: 0,
                zIndex: 1
            });
        });
    },

    /**
     * setup. 
     * 
     * @public
     * @return void
     */
    setup: function() {

        // parent container must be relative for offset animating
        this.__container.setStyles({
            position: 'relative',
            height: this.__container.getStyle('height')
        });

        // coordinate saving
        this.__coordinates = this.__elements.getCoordinates(this.__container);

        // inject wrapper for element contents
        var wrapper;
        this.__elements.each(function(element) {

            // create wrapper
            wrapper = (new Element('div', {
                'class': 'JigsawElementWrapper'
            }));

            // adopt direct children
            wrapper.adopt(element.getElements('> *'));
            element.adopt(wrapper);
        });

        // set wrappers
        this.__wrappers = this.__elements.getElement('div.JigsawElementWrapper');

        // setup morph
        var self = this,
            morph;
        
        /**
         * Closure had to be created for the morph-complete event to prevent the
         *     <morph> variable from being evaluated as the final
         *     reference/resource for each complete event.
         */
        this.__wrappers.each(function(wrapper) {

            // individual morph with customized settings
            morph = (new Fx.Morph(wrapper, {
                duration: self.options.duration,
                link: 'cancel'
            }));

            // closure, as described above
            (function(morph){
                morph.addEvent('complete', function(wrapper) {
                    var toggled = false;
                    if (wrapper.getStyle('opacity').toInt() === 1) {
                        toggled = true;
                    }
                    self.options.events.toggled.bind(self)(morph, toggled);
                });
            })(morph);

            // store for hide/show events
            wrapper.store('JigsawMorph', morph);
        });

        // re-align
        var self = this;
        this.__wrappers.each(function(wrapper, offset) {
            wrapper.setStyles({
                position: 'absolute',
                left: self.__coordinates[offset].left,
                top: self.__coordinates[offset].top
            });
        });
    },

    /**
     * show. Shows the passed in elements (first marking as visible). The
     *     z-index is set to be above those that were hidden (to ensure the
     *     ability to click on them).
     * 
     * @public
     * @param Array elements
     * @return void
     */
    show: function(elements) {
        var self = this,
            morph, wrapper;
        elements.each(function(element, offset) {
            wrapper = element.getElement('.JigsawElementWrapper');
            morph = wrapper.retrieve('JigsawMorph');
            morph.start({
                visibility: 'visible',
                opacity: self.options.opacity,
                left: self.__coordinates[offset].left,
                top: self.__coordinates[offset].top,
                zIndex: 2
            });
        });
    }
}));
