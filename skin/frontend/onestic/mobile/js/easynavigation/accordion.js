/**
 * Do not remove or edit this.
 * Modified by Templates-Master, 
 * to provide support of multiple navigation levels 
 * of one accordion instance.
 * 
 * www.templates-master.com
 */
// accordion.js v2.0
//
// Copyright (c) 2007 stickmanlabs
// Author: Kevin P Miller | http://www.stickmanlabs.com
// 
// Accordion is freely distributable under the terms of an MIT-style license.
//

var accordion = Class.create();
accordion.prototype = {
    currentAccordion: null,
    duration: null,
    effects: [],
    animating: false,
    
    initialize: function(container, options) 
    {
        this.showActive(container); //ie6 fix
        
        this.options = Object.extend({
            resizeSpeed: 8,
            classNames: {
                toggle: 'accordion-toggle',
                toggleActive: 'accordion-toggle-active'
            },
            defaultSize: {
                height: null,
                width: null
            },
            direction: 'vertical',
            onEvent: 'click'
        }, options || {});
        
        this.duration = ((11-this.options.resizeSpeed)*0.15);

        var accordions = $$('#'+container+' .'+this.options.classNames.toggle);
        accordions.each(function(accordion) {
            Event.observe(accordion, this.options.onEvent, this.activate.bind(this, accordion), false);
            if (this.options.onEvent == 'click') {
                accordion.onclick = function() {return false;};
            }
            
            if (this.options.direction == 'horizontal') {
                var accordion_options = $H({width: '0px', display: 'none'});
            } else {
                var accordion_options = $H({height: '0px', display: 'none'});
            }
            
            this.currentAccordion = $(accordion.next(0)).setStyle(accordion_options.toJSON());
        }.bind(this));
    },
    
    showActive: function(container)
    {
        $$('#' + container + ' li.active > ul').each(function(el) {
            el.setStyle({ height: 'auto' });
        })
    },
    
    activate: function(accordion) 
    {
        if (this.animating) {
            return false;
        }
        
        this.effects = [];
    
        if (this.options.direction == 'horizontal') {
            this.scaleX = true;
            this.scaleY = false;
        } else {
            this.scaleX = false;
            this.scaleY = true;
        }
        
        this.currentAccordion = $(accordion.next(0));
        
        if (accordion.hasClassName(this.options.classNames.toggleActive)) {
            this.deactivate();
        } else {
            this._handleAccordion();
        }
    },
    
    deactivate: function() 
    {
        this.currentAccordion.previous(0).removeClassName(this.options.classNames.toggleActive);
        
        new Effect.Scale(this.currentAccordion, 0, {
            duration: this.duration,
            scaleContent: false,
            scaleX: this.scaleX,
            scaleY: this.scaleY,
            transition: Effect.Transitions.sinoidal,
            queue: {
                position: 'end', 
                scope: 'accordionAnimation'
            },
            scaleMode: {
                originalHeight: this.options.defaultSize.height ? this.options.defaultSize.height : this.currentAccordion.scrollHeight,
                originalWidth: this.options.defaultSize.width ? this.options.defaultSize.width : this.currentAccordion.scrollWidth
            },
            afterFinish: function() {
                this.animating = false;
            }.bind(this)
        });
    },

    _handleAccordion: function() 
    {
        this.effects.push(
            new Effect.Scale(this.currentAccordion, 100, {
                sync: true,
                scaleFrom: 0,
                scaleContent: false,
                scaleX: this.scaleX,
                scaleY: this.scaleY,
                transition: Effect.Transitions.sinoidal,
                scaleMode: { 
                    originalHeight: this.options.defaultSize.height ? this.options.defaultSize.height : this.currentAccordion.scrollHeight,
                    originalWidth: this.options.defaultSize.width ? this.options.defaultSize.width : this.currentAccordion.scrollWidth
                }
            })
        );
        
        var opened = this._getOpened();
        if (opened) {
            opened.previous(0).removeClassName(this.options.classNames.toggleActive);
            this.effects.push(
                new Effect.Scale(opened, 0, {
                    sync: true,
                    scaleContent: false,
                    scaleX: this.scaleX,
                    scaleY: this.scaleY,
                    transition: Effect.Transitions.sinoidal
                })
            );                
        }
        
        this.currentAccordion.previous(0).addClassName(this.options.classNames.toggleActive);
        new Effect.Parallel(this.effects, {
            duration: this.duration, 
            queue: {
                position: 'end', 
                scope: 'accordionAnimation'
            },
            beforeStart: function() {
                this.animating = true;
            }.bind(this),
            afterFinish: function() {
                this.currentAccordion.setStyle({ height: 'auto' });
                this.animating = false;
            }.bind(this)
        });
    },
    
    _getOpened: function()
    {
        var siblings = this.currentAccordion.up('li').siblings();
        var opened = false;
        siblings.each(function(el) {
            if (opened) {
                return;
            }
            el.childElements().each(function(innerEl) {
                if (innerEl.hasClassName(this.options.classNames.toggleActive)) {
                    opened = innerEl.next(0);
                    return;
                }
            }.bind(this));
        }.bind(this));
        return opened;
    }
}