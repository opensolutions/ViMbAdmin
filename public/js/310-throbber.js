/**
 * @preserve throbber.js v 0.0.2 2014-04-30
 * http://aino.com
 *
 * Copyright (c) Aino Aktiebolag
 * Licensed under the MIT license.
 *
 */

/*global Image, module, define, window */

(function(global, factory) {

    if ( typeof module === "object" && typeof module.exports === "object" ) {
        module.exports = factory( global );
    } else if ( typeof define == 'function' && define.amd ) {
        define( "throbber", [], function() {
            return factory( global );
        });
    } else {
        global.Throbber = factory( global );
    }

}(window || this, function( window ) {

    var document = window.document,

        M = Math,
        setTimeout = window.setTimeout,

        support = ( 'getContext' in document.createElement('canvas') ),

        _extend = function( defaults, obj ) {
            defaults = defaults || {};
            for (var i in obj) {
                defaults[i] = obj[i];
            }
            return defaults;
        },

        _animate = (function() {

            var loops = [];
            var animating = false;

            var requestFrame = (function(){
              var r = 'RequestAnimationFrame';
              return window.requestAnimationFrame || 
                window['webkit'+r] || 
                window['moz'+r] || 
                window['o'+r] || 
                window['ms'+r] || 
                function( callback ) {
                  window.setTimeout(callback, 1000 / 60);
                };
            }());

            function tick() {

                requestFrame(tick);
                var now = +(new Date());

                for(var i=0; i<loops.length; i++) {
                    var loop = loops[i];
                    loop.elapsed = now - loop.then;
                    if (loop.elapsed > loop.fpsInterval) {
                        loop.then = now - (loop.elapsed % loop.fpsInterval);
                        loop.fn();
                    }
                }
            }

            return function animate(fps, draw) {

                var now = +(new Date());
                loops.push({
                    fpsInterval: 1000/fps,
                    then: now,
                    startTime: now,
                    elapsed: 0,
                    fn: draw
                });
                if ( !animating ) {
                    animating = true;
                    tick();
                }
            };
        }()),

        // convert any color to RGB array
        _getRGB = function( color ) {
            if ( !support ) { return { rgb:false, alpha:1 }; }

            var t = document.createElement( 'i' ), rgb;

            t.style.display = 'none';
            t.style.color = color;
            document.body.appendChild( t );

            rgb = window.getComputedStyle( t, null )
                .getPropertyValue( 'color' )
                .replace( /^rgba?\(([^\)]+)\)/,'$1' ).replace( /\s/g, '' ).split(',').splice( 0, 4 );

            document.body.removeChild( t );
            t = null;

            return {
                alpha: rgb.length == 4 ? rgb.pop() : 1,
                rgb: rgb
            };
        },

        // used when rotating
        _restore = function( ctx, size, back ) {
            var n = back ? -2 : 2;
            ctx.translate( size/n, size/n );
        },

        // locar vars
        fade, i, l, ad, rd,

        // draw the frame
        _draw = function( alpha, o, ctx, step ) {

            fade = 1-alpha || 0;
            ad = 1; rd = -1;

            var size = o.size;

            if ( o.clockwise === false ) {
                ad = -1;
                rd = 1;
            }

            ctx.clearRect(0, 0, size, size);
            ctx.globalAlpha = o.alpha;
            ctx.lineWidth = o.strokewidth;

            for (i=0; i < o.lines; i++) {

                l = i+step >= o.lines ? i-o.lines+step : i+step;

                ctx.strokeStyle = 'rgba(' + o.color.join(',') + ',' + M.max(0, ((l/o.lines) - fade) ).toFixed(2) + ')';
                ctx.beginPath();

                ctx.moveTo( size/2, size/2-o.padding/2 );
                ctx.lineTo( size/2, 0 );
                ctx.lineWidth = o.strokewidth;
                ctx.stroke();
                _restore( ctx, size, false );
                ctx.rotate( ad * ( 360/o.lines ) * M.PI/180 );
                _restore( ctx, size, true );
            }

            if ( o.rotationspeed ) {
                ctx.save();
                _restore( ctx, size, false );

                ctx.rotate( rd * ( 360/o.lines/( 20-o.rotationspeed*2 ) ) * M.PI/180 ); //rotate in origin
                _restore( ctx, size, true );
            }
        };


    // Throbber constructor
    function Throbber( options ) {

        if ( !(this instanceof Throbber )) {
            return new Throbber( options );
        }

        var elem = this.elem = document.createElement('canvas'),
            scope = this;

        if ( !isNaN( options ) ) {
            options = { size: options };
        }

        // default options
        // note that some of these are placeholder and calculated against size if not defined
        this.o = {
            size: 34,           // diameter of loader
            rotationspeed: 6,   // rotation speed (1-10)
            clockwise: true,    // direction, set to false for counter clockwise
            color: '#fff',      // color of the spinner, can be any CSS compatible value
            fade: 300,          // duration of fadein/out when calling .start() and .stop()
            fallback: false,    // a gif fallback for non-supported browsers
            alpha: 1            // global alpha, can be defined using rgba as color or separatly
        };

        /*
        // more options, but these are calculated from size if not defined:

        fps                     // frames per second (~size)
        padding                 // diameter of clipped inner area (~size/2)
        strokewidth             // width of the lines (~size/30)
        lines                   // number of lines (~size/2+4)

        */

        // _extend options
        this.configure( options );

        // fade phase
        // 0 = idle
        // 1 = fadein
        // 2 = running
        // 3 = fadeout
        this.phase = -1;

        // references
        if ( support ) {
            this.ctx = elem.getContext('2d');
            elem.width = elem.height = this.o.size;
        } else if ( this.o.fallback ) {
            elem = this.elem = new Image();
            elem.src = this.o.fallback;
        }

        ///////////////////
        // the loop

        this.loop = (function() {

            var o = scope.o,
                alpha = 0,
                fade = 1000/o.fade/o.fps,
                interval = 1000/o.fps,
                step = scope.step,

                style = elem.style,
                currentStyle = elem.currentStyle,
                filter = currentStyle && currentStyle.filter || style.filter,
                ie = 'filter' in style && o.fallback && !support;

            // the canvas loop
            return function() {

                if ( scope.phase == 3 ) {

                    // fadeout
                    alpha -= fade;
                    if ( alpha <= 0 ) {
                        scope.phase = 0;
                    }

                }

                if ( scope.phase == 1 ) {

                    // fadein
                    alpha += fade;
                    if ( alpha >= 1 ) {
                        scope.phase = 2;
                    }
                }

                if ( ie ) {
                    style.filter = 'alpha(opacity=' + M.min( o.alpha*100, M.max(0, Math.round( alpha*100 ) ) ) + ')';
                } else if ( !support && o.fallback ) {
                    style.opacity = alpha;
                } else if ( support ) {
                    _draw( alpha, o, scope.ctx, step );
                    step = step === 0 ? scope.o.lines : step-1;
                }
            };
        }());

        _animate(this.o.fps, this.loop);

    }

    // Throbber prototypes
    Throbber.prototype = {

        constructor: Throbber,

        // append the loader to a HTML element
        appendTo: function( elem ) {

            this.elem.style.display = 'none';
            elem.appendChild( this.elem );

            return this;
        },

        // _extend options and apply calculate meassures
        configure: function( options ) {

            var o = this.o, color;

            _extend(o, options || {});

            color = _getRGB( o.color );

            // do some sensible calculations if not defined
            _extend( o, _extend({
                padding: o.size/2,
                strokewidth: M.max( 1, M.min( o.size/30, 3 ) ),
                lines: M.min( 30, o.size/2+4 ),
                alpha: color.alpha || 1,
                fps: M.min( 30, o.size+4 )
            }, options ));

            // grab the rgba array
            o.color = color.rgb;

            // copy the amount of lines into steps
            this.step = o.lines;
            
            // double-up for retina screens
            if (!!window.devicePixelRatio) {
                // lock element into desired end size
                this.elem.style.width = o.size + 'px';
                this.elem.style.height = o.size + 'px';

                o.size *= window.devicePixelRatio;
                o.padding *= window.devicePixelRatio;
                o.strokewidth *= window.devicePixelRatio;
            }

            return this;
        },

        // starts the animation
        start: function() {

            this.elem.style.display = 'block';
            if ( this.phase == -1 ) {
                this.loop();
            }
            this.phase = 1;

            return this;
        },

        // stops the animation
        stop: function() {
            this.phase = 3;
            return this;
        },

        toggle: function() {
            if ( this.phase == 2 ) {
                this.stop();
            } else {
                this.start();
            }
        }
    };

    return Throbber;

}));
