/*
CropZoom v1.0.4
Release Date: April 17, 2010

Copyright (c) 2010 Gaston Robledo

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
( function( $ ) {

    var _self = null;
    var $options = null;

    var supportSvg = function () {
        return $.browser.msie && ( $.browser.version >= 9 ) || !$.browser.msie;
    };

    $.fn.cropzoom = function( options ) {

        $options = $.extend( true, $.fn.cropzoom.defaults, options );

        return this.each( function() {

            //Verificamos que esten los plugins necesarios
            if ( !$.isFunction( $.fn.draggable ) || !$.isFunction( $.fn.resizable ) || !$.isFunction( $.fn.slider ) ) {
                alert( "You must include ui.draggable, ui.resizable and ui.slider to use cropZoom" );
                return;
            }
            if ( $options.image.source == '' || $options.image.width == 0 || $options.image.height == 0 ) {
                alert( 'You must set the source, witdth and height of the image element' );
                return;
            }

            $( '#img_to_crop' ).remove();

            _self = $( this );
            _self.empty();
            _self.css( {
                'width': $options.width,
                'height': $options.height,
                //'background-color':$options.bgColor,
                //'overflow':'hidden',
                'position': 'relative'
            } );


            setData( 'image', {
                h: $options.image.height,
                w: $options.image.width,
                posY: 0,
                posX: 0,
                scaleX: 0,
                scaleY: 0,
                rotation: 0,
                source: $options.image.source
            } );

            setData( 'selector', {
                x: $options.selector.x,
                y: $options.selector.y,
                w: ( $options.selector.maxWidth != null ? ( $options.selector.w > $options.selector.maxWidth ? $options.selector.maxWidth : $options.selector.w ) : $options.selector.w ),
                h: ( $options.selector.maxHeight != null ? ( $options.selector.h > $options.selector.maxHeight ? $options.selector.maxHeight : $options.selector.h ) : $options.selector.h )
            } );

            calculateFactor();
            getCorrectSizes();

            getData( 'image' ).posX = ( $options.width / 2 ) - ( getData( 'image' ).w / 2 ) + 1;
            getData( 'image' ).posY = ( $options.height / 2 ) - ( getData( 'image' ).h / 2 ) + 1;
            var $svg = null;
            var $image = null;
            if ( supportSvg() ) {
                $svg = _self[ 0 ].ownerDocument.createElementNS( 'http://www.w3.org/2000/svg', 'svg' );
                $svg.setAttribute( 'id', 'k' );
                $svg.setAttribute( 'width', $options.width );
                $svg.setAttribute( 'height', $options.height );
                $svg.setAttribute( 'preserveAspectRatio', 'none' );
                $image = _self[ 0 ].ownerDocument.createElementNS( 'http://www.w3.org/2000/svg', 'image' );
                $image.setAttributeNS( 'http://www.w3.org/1999/xlink', 'href', $options.image.source );
                $image.setAttribute( 'width', getData( 'image' ).w );
                $image.setAttribute( 'height', getData( 'image' ).h );
                $image.setAttribute( 'id', 'img_to_crop' );
                $image.setAttribute( 'preserveAspectRatio', 'none' );
                $( $image ).attr2( 'x', 0 );
                $( $image ).attr2( 'y', 0 );
                $( $image ).css( 'cursor', 'move' );
                $svg.appendChild( $image );
                if ( $.browser.msie && $.browser.version >= 10 ) {
                    window.setTimeout( function () {
                        // ie10够扯
                        $image.setAttributeNS( 'http://www.w3.org/1999/xlink', 'href', '' );
                        $image.setAttributeNS( 'http://www.w3.org/1999/xlink', 'href', $options.image.source );
                    }, 0 );
                }
            } else {
                // Add VML includes and namespace
                _self[ 0 ].ownerDocument.namespaces.add( 'v', 'urn:schemas-microsoft-com:vml', "#default#VML" );
                // Add required css rules
                var style = document.createStyleSheet();
                style.addRule( 'v\\:image', "behavior: url(#default#VML);display:inline-block" );
                style.addRule( 'v\\:image', "antiAlias: false;" );

                $svg = $( "<div />" ).attr2( "id", "k" ).css( {
                    'width': $options.width,
                    'height': $options.height,
                    'position': 'absolute'
                } );
                $image = document.createElement( 'v:image' );
                $image.setAttribute( 'id', 'img_to_crop' );
                $image.setAttribute( 'src', $options.image.source );
                $image.setAttribute( 'gamma', '0' );

                $( $image ).css( {
                    'position': 'absolute',
                    'left': 0,
                    'top': 0,
                    'width': getData( 'image' ).w,
                    'height': getData( 'image' ).h,
                    'cursor': 'move'
                } );
                $image.setAttribute( 'coordsize', '21600,21600' );
                $image.outerHTML = $image.outerHTML;


                var ext = getExtensionSource();
                if ( ext == 'png' || ext == 'gif' )
                    $image.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + $options.image.source + "',sizingMethod='scale');";
                $svg.append( $image );
            }
            _self.append( $svg );
            calculateTranslationAndRotation();
            //Bindear el drageo a la imagen a cortar

            //Creamos el selector  
            createSelector();
            //Cambiamos el resizable por un color solido
            _self.find( '.ui-icon-gripsmall-diagonal-se' ).css( {
                //'background':'#FFF',
                //'border':'1px solid #000',
                'width': 8,
                'height': 8
            } );
            //Creamos la Capa de oscurecimiento
            createOverlay();
            createFakeLayer();
            //Creamos el Control de Zoom 
            if ( $options.enableZoom )
                createZoomSlider();
            //Creamos el Control de Rotacion
            if ( $options.enableRotation )
                createRotationSlider();
            //Maintein Chaining 
            return this;
        } );

    }

    function getExtensionSource() {
        var parts = $options.image.source.split( '.' );
        return parts[ parts.length - 1 ];
    }


    function calculateFactor() {
        getData( 'image' ).scaleX 
            = parseFloat( getData( 'selector' ).w / getData( 'image' ).w );
        getData( 'image' ).scaleY 
            = parseFloat( getData( 'selector' ).h / getData( 'image' ).h );
    }

    function getCorrectSizes() {

        var scaleX = getData( 'image' ).scaleX;
        var scaleY = getData( 'image' ).scaleY;
        if ( scaleY > scaleX ) {
            getData( 'image' ).h = getData( 'selector' ).h + 2;
            getData( 'image' ).w = Math.round( getData( 'image' ).w * scaleY );
            $options.image.minZoom = getData( 'selector' ).h / $options.image.height * 100;
        } else {
            getData( 'image' ).h = Math.round( getData( 'image' ).h * scaleX );
            getData( 'image' ).w = getData( 'selector' ).w + 2;
            $options.image.minZoom = getData( 'selector' ).w / $options.image.width * 100;
        }

        $options.image.maxZoom = $options.image.minZoom * 3;

    }

    function calculateTranslationAndRotation() {
        var rotacion = "";
        var traslacion = "";
        if ( !supportSvg() ) {
            rotacion = getData( 'image' ).rotation;
            $( '#img_to_crop' ).css( {
                'rotation': rotacion,
                'top': getData( 'image' ).posY,
                'left': getData( 'image' ).posX
            } );
            // ie 8,9,10
            // $( '#img_to_crop' ).attr2( 'x', getData( 'image' ).posX );
            // $( '#img_to_crop' ).attr2( 'y', getData( 'image' ).posY );
        } else {
            rotacion = "rotate(" + getData( 'image' ).rotation + "," + ( getData( 'image' ).posX + ( getData( 'image' ).w / 2 ) ) + "," + ( getData( 'image' ).posY + ( getData( 'image' ).h / 2 ) ) + ")";
            traslacion = " translate(" + getData( 'image' ).posX + "," + getData( 'image' ).posY + ")";
            rotacion += traslacion
            $( '#img_to_crop' ).attr2( "transform", rotacion );
        }
    }

    function createRotationSlider() {
        var rotationContainerSlider = $( "<div />" ).css( {
            'position': 'absolute',
            'background-color': '#FFF',
            'z-index': 3,
            'opacity': 0.6,
            'width': 31,
            'height': _self.height() / 2,
            'top': 5,
            'left': 5
        } ).mouseover( function() {
            $( this ).css( 'opacity', 1 );
        } ).mouseout( function() {
            $( this ).css( 'opacity', 0.6 );
        } );

        var rotMin = $( '<div />' ).css( {
            'color': '#000',
            'font': '700 11px Arial',
            'margin': 'auto',
            'width': 10
        } );
        var rotMax = $( '<div />' ).css( {
            'color': '#000',
            'font': '700 11px Arial',
            'margin': 'auto',
            'width': 21
        } );
        rotMin.html( "0" );
        rotMax.html( "360" );

        var $slider = $( "<div />" );
        //Aplicamos el Slider            
        $slider.slider( {
            orientation: "horizontal",
            value: 360,
            min: 0,
            max: 360,
            step: ( ( $options.rotationSteps > 360 || $options.rotationSteps < 0 ) ? 1 : $options.rotationSteps ),
            slide: function( event, ui ) {
                getData( 'image' ).rotation = Math.abs( 360 - ui.value );
                calculateTranslationAndRotation();
                if ( $options.onRotate != null )
                    $options.onRotate( $( '#img_to_crop' ), getData( 'image' ).rotation );
            }
        } )
        rotationContainerSlider.append( rotMin );
        rotationContainerSlider.append( $slider );
        rotationContainerSlider.append( rotMax );
        $slider.css( {
            'margin': ' 7px auto',
            'height': ( _self.height() / 2 ) - 60,
            'position': 'relative',
            'width': 7
        } );
        _self.append( rotationContainerSlider );
    }

    function createZoomSlider() {
        //判断after的代码是否存在，即放大缩小按钮的是否重复出现
        var zoomSlider = _self.next( 'div.isExist' );
        if ( zoomSlider.length > 0 ) {
            zoomSlider.remove();
        }

        var zoomContainerSlider = $( "<div class='isExist' />" ).css( {
            //'position':'absolute',
            'background-color': '#fff',
            //'z-index':3,
            'opacity': 1,
            //'width':360,
            'height': ( _self.height() / 2 - 168 ),
            'width': '286px',
            'margin': '20px auto 0 auto'

        } );

        var zoomMin = $( '<div />' ).css( {
            'color': '#000',
            'font': '700 14px Arial',
            'margin-left':'10px',
            //'width':'100%',
            'text-align': 'left',
            'float': 'left'
        } ).html( "<img class='right' src='" + SLPGER.root + "/public/images/mr_headright.png'/>" );
        var zoomMax = $( '<div />' ).css( {
            'color': '#000',
            'font': '700 14px Arial',
            'float': 'left',
            'margin-right':'10px',
            //'width':'100%',
            'text-align': 'right'
        } ).html( "<img class='left' src='" + SLPGER.root + "/public/images/mr_headleft.png'/>" );

        var $slider = $( "<div />" );
        //Aplicamos el Slider   
        $slider.slider( {
            orientation: "horizontal ",
            value: getPercentOfZoom(),
            min: $options.image.minZoom,
            max: $options.image.maxZoom,
            step: ( ( $options.zoomSteps > $options.image.maxZoom || $options.zoomSteps < 0 ) ? 1 : $options.zoomSteps ),
            slide: function( event, ui ) {
                var zoomInPx_width = ( ( $options.image.width * Math.abs( ui.value ) ) / 100 );
                var zoomInPx_height = ( ( $options.image.height * Math.abs( ui.value ) ) / 100 );
                
                var minWidth = getData( 'selector' ).w + 2;
                var minHeight = getData( 'selector' ).h + 2;
                zoomInPx_width < minWidth
                    && ( zoomInPx_width = minWidth );
                zoomInPx_height < minHeight
                    && ( zoomInPx_height = minHeight );

                if ( supportSvg() ) {
                    $( '#img_to_crop' ).attr2( 'width', zoomInPx_width + "px" );
                    $( '#img_to_crop' ).attr2( 'height', zoomInPx_height + "px" );
                } else {
                    $( '#img_to_crop' ).css( {
                        'width': zoomInPx_width + "px",
                        'height': zoomInPx_height + "px"
                    } );
                }

                getData( 'image' ).w = zoomInPx_width;
                getData( 'image' ).h = zoomInPx_height;
                calculateFactor();
                getData( 'image' ).posX = ( ( $options.width / 2 ) - ( getData( 'image' ).w / 2 ) ) + 1;
                getData( 'image' ).posY = ( ( $options.height / 2 ) - ( getData( 'image' ).h / 2 ) ) + 1;
                calculateTranslationAndRotation();
                if ( $options.onZoom != null ) {
                    $options.onZoom( $( '#img_to_crop' ), getData( 'image' ) );
                }

            }
        } );

        zoomContainerSlider.append( zoomMax );
        zoomContainerSlider.append( $slider );
        zoomContainerSlider.append( zoomMin );
        $slider.css( {
            //'margin':' 7px auto',
            'margin': ' 7px',
            'float': 'left',
            //'height': (_self.height() / 2) - 178,
            'height': '5px',
            'width': 200,
            'position': 'relative'
        } );

        _self.after( zoomContainerSlider );
    }

    function getPercentOfZoom() {
        var percent = 0;
        if ( getData( 'image' ).w > getData( 'image' ).h ) {
            percent = ( ( getData( 'image' ).w * 100 ) / $options.image.width );
        } else {
            percent = ( ( getData( 'image' ).h * 100 ) / $options.image.height );
        }
        return percent;
    }

    function createSelector() {
        if ( $options.selector.centered ) {
            getData( 'selector' ).y = ( $options.height / 2 ) - ( getData( 'selector' ).h / 2 );
            getData( 'selector' ).x = ( $options.width / 2 ) - ( getData( 'selector' ).w / 2 );
        }
        var _selector = $( '<div />' ).attr2( 'id', 'selector' ).css( {
            'width': getData( 'selector' ).w,
            'height': getData( 'selector' ).h,
            'top': getData( 'selector' ).y + 'px',
            'left': getData( 'selector' ).x + 'px',
            'border': '1px solid ' + $options.selector.borderColor,
            'position': 'absolute',
            'cursor': 'move',
            'user-select': 'none',
            'z-index': '1'
        } ).mouseover( function() {
            $( this ).css( {
                'border': '1px solid ' + $options.selector.borderColorHover
            } )
        } ).mouseout( function() {
            $( this ).css( {
                'border': '1px solid ' + $options.selector.borderColor
            } )
        } );

        showInfo( _selector );
        //Agregamos el selector al objeto contenedor
        _self.append( _selector );
    };

    function showInfo( _selector ) {

        var _infoView = null;
        var alreadyAdded = false;
        if ( _selector.find( "#infoSelector" ).length > 0 ) {
            _infoView = _selector.find( "#infoSelector" );
        } else {
            _infoView = $( '<div />' ).attr2( 'id', 'infoSelector' ).css( {
                'position': 'absolute',
                'top': 0,
                'left': 0,
                //'background':$options.selector.bgInfoLayer,
                // 'opacity':0.6,
                'font-size': $options.selector.infoFontSize + 'px',
                'font-family': 'Arial',
                'color': $options.selector.infoFontColor,
                'width': '100%'
            } );
        }
        if ( $options.selector.showDimetionsOnDrag ) {
            _infoView.html( "X:" + getData( 'selector' ).x + "px - Y:" + getData( 'selector' ).y + "px" );
            alreadyAdded = true;
        }
        if ( $options.selector.showPositionsOnDrag ) {
            if ( alreadyAdded )
                _infoView.html( _infoView.html() + " | W:" + getData( 'selector' ).w + "px - H:" + getData( 'selector' ).h + "px" );
            else
            //_infoView.html("W:"+ getData('selector').w + "px - H:" + getData('selector').h + "px");
                _infoView.html( '' );
        }
        _selector.append( _infoView );
    }

    function createFakeLayer() {

        var layer = $( "<div />" ).attr2( "id", 'fakelayer' ).css( {
            'position': 'absolute',
            'z-index': 1000,
            'visibility': 'invisible',
            'width': '360px',
            'height': '360px',
            'top': '74px',
            'left': '30px',
            'cursor': 'move',
            'background': 'transparent',
            'zoom': '1'
        } );

        var startX = 0, startY = 0;

        var oldPosY, oldPosX;

        function handleOffsetXY( event ) {
            event.offsetX = undefined != event.offsetX 
                ? event.offsetX 
                : event.originalEvent.layerX;
            event.offsetY = undefined != event.offsetY 
                ? event.offsetY 
                : event.originalEvent.layerY;
        } 

        function layerMove( event ) {

            handleOffsetXY( event );

            var offsetX = event.offsetX - startX;
            var offsetY = event.offsetY - startY;
            var positionTop = oldPosY + offsetY;
            var positionLeft = oldPosX + offsetX;
            var vGap = ( $options.width - getData( 'selector' ).w ) / 2;
            var hGap = ( $options.height - getData( 'selector' ).h ) / 2;

            // 限制
            positionTop = positionTop < hGap
                ? positionTop
                : hGap;
            positionLeft = positionLeft < vGap
                ? positionLeft
                : vGap;

            var leftOffset = getData( 'image' ).w + vGap - $options.width;
            positionLeft = positionLeft < ( - leftOffset + 2 )
                ? ( - leftOffset + 2 )
                : positionLeft;
            var topOffset = getData( 'image' ).h + hGap - $options.height;
            positionTop = positionTop < ( - topOffset + 2 )
                ? ( - topOffset + 2 )
                : positionTop;

            getData( 'image' ).posY = positionTop;
            getData( 'image' ).posX = positionLeft;

            calculateTranslationAndRotation();

        }
        layer.bind( 'mousedown', function ( event ) {
            // console.log( 'mousedown mousedown' );
            handleOffsetXY( event );
            startX = event.offsetX; 
            startY = event.offsetY;
            oldPosX = getData( 'image' ).posX;
            oldPosY = getData( 'image' ).posY;
            layer.bind( 'mousemove', layerMove );
        } );

        layer.bind( 'mouseup mouseleave', function ( event ) {
            layer.unbind( 'mousemove', layerMove );
            // console.log( 'mouseup mouseleave' );
        } );

        // var clonelayer = layer.clone();
        // clonelayer.id = 'mmmm';
        // _self.parent().append( clonelayer );
        _self.parent().append( layer );
        // _self.append( layer );

    }

    function createOverlay() {
        var arr = [ 't', 'b', 'l', 'r' ]
        $.each( arr, function() {
            var divO = $( "<div />" ).attr2( "id", this ).css( {
                'overflow': 'hidden',
                'background': $options.overlayColor,
                'opacity': 0.6,
                'position': 'absolute',
                'z-index': 2,
                'visibility': 'visible'
            } );
            _self.append( divO );
        } );
        var topHeight = ( $options.height - getData( 'selector' ).h ) / 2;
        var leftWidth = ( $options.width - getData( 'selector' ).w ) / 2;
        _self.find( '#t' ).css( {
            display: 'block', width: '100%', 
            height: topHeight,
            left: 0, top: 0
        } );
        _self.find( '#b' ).css( {
            display: 'block', width: '100%', 
            height: topHeight,
            left: 0, top: ( $options.height / 2 + getData( 'selector' ).h / 2 + 2)
        } );
        _self.find( '#l' ).css( {
            display: 'block', width: leftWidth, 
            height: getData( 'selector' ).h + 2,
            left: 0, top: topHeight
        } );
        _self.find( '#r' ).css( {
            display: 'block', width: leftWidth - 2, 
            height: getData( 'selector' ).h + 2,
            right: 0, top: topHeight
        } );
    }

    function makeOverlayPositions( ui ) {
        $( "#t" ).css( {
            "display": "block",
            "width": $options.width,
            'height': ui.position.top,
            'left': 0,
            'top': 0
        } );
        $( "#b" ).css( {
            "display": "block",
            "width": $options.width,
            'height': $options.height,
            'top': ( ui.position.top + $( "#selector" ).height() ) + "px",
            'left': 0
        } )
        $( "#l" ).css( {
            "display": "block",
            'left': 0,
            'top': ui.position.top,
            'width': ui.position.left,
            'height': $( "#selector" ).height()
        } )
        $( "#r" ).css( {
            "display": "block",
            'top': ui.position.top,
            'left': ( ui.position.left + $( "#selector" ).width() ) + "px",
            'width': $options.width,
            'height': $( "#selector" ).height() + "px"
        } )
    }

    function hideOverlay() {
        $( "#t,#b,#l,#r" ).hide();
    }

    function setData( key, data ) {
        _self.data( key, data );
    }

    function getData( key ) {
        return _self.data( key );
    }


    /*Code taken from jquery.svgdom.js */
    /* Support adding class names to SVG nodes. */
    var origAddClass = $.fn.addClass;

    $.fn.addClass = function( classNames ) {
        classNames = classNames || '';
        return this.each( function() {
            if ( isSVGElem( this ) ) {
                var node = this;
                $.each( classNames.split( /\s+/ ), function( i, className ) {
                    var classes = ( node.className ? node.className.baseVal : node.getAttribute( 'class' ) );
                    if ( $.inArray( className, classes.split( /\s+/ ) ) == -1 ) {
                        classes += ( classes ? ' ' : '' ) + className;
                        ( node.className ? node.className.baseVal = classes :
                            node.setAttribute( 'class', classes ) );
                    }
                } );
            } else {
                origAddClass.apply( $( this ), [ classNames ] );
            }
        } );
    };

    /* Support removing class names from SVG nodes. */
    var origRemoveClass = $.fn.removeClass;

    $.fn.removeClass = function( classNames ) {
        classNames = classNames || '';
        return this.each( function() {
            if ( isSVGElem( this ) ) {
                var node = this;
                $.each( classNames.split( /\s+/ ), function( i, className ) {
                    var classes = ( node.className ? node.className.baseVal : node.getAttribute( 'class' ) );
                    classes = $.grep( classes.split( /\s+/ ), function( n, i ) {
                        return n != className;
                    } ).
                    join( ' ' );
                    ( node.className ? node.className.baseVal = classes :
                        node.setAttribute( 'class', classes ) );
                } );
            } else {
                origRemoveClass.apply( $( this ), [ classNames ] );
            }
        } );
    };

    /* Support toggling class names on SVG nodes. */
    var origToggleClass = $.fn.toggleClass;

    $.fn.toggleClass = function( className, state ) {
        return this.each( function() {
            if ( isSVGElem( this ) ) {
                if ( typeof state !== 'boolean' ) {
                    state = !$( this ).hasClass( className );
                }
                $( this )[ ( state ? 'add' : 'remove' ) + 'Class' ]( className );
            } else {
                origToggleClass.apply( $( this ), [ className, state ] );
            }
        } );
    };

    /* Support checking class names on SVG nodes. */
    var origHasClass = $.fn.hasClass;

    $.fn.hasClass = function( className ) {
        className = className || '';
        var found = false;
        this.each( function() {
            if ( isSVGElem( this ) ) {
                var classes = ( this.className ? this.className.baseVal :
                    this.getAttribute( 'class' ) ).split( /\s+/ );
                found = ( $.inArray( className, classes ) > -1 );
            } else {
                found = ( origHasClass.apply( $( this ), [ className ] ) );
            }
            return !found;
        } );
        return found;
    };

    /* Support attributes on SVG nodes. */
    var origAttr = $.fn.attr;

    $.fn.attr2 = function( name, value, type ) {
        if ( typeof name === 'string' && value === undefined ) {
            var val = origAttr.apply( this, [ name, value, type ] );
            return ( val && val.baseVal ? val.baseVal.valueAsString : val );
        }
        var options = name;
        if ( typeof name === 'string' ) {
            options = {};
            options[ name ] = value;
        }
        return this.each( function() {
            if ( isSVGElem( this ) ) {
                for ( var n in options ) {
                    this.setAttribute( n, ( typeof options[ n ] == 'function' ? options[ n ]() : options[ n ] ) );
                }
            } else {
                origAttr.apply( $( this ), [ name, value, type ] );
            }
        } );
    };

    /* Support removing attributes on SVG nodes. */
    var origRemoveAttr = $.fn.removeAttr;

    $.fn.removeAttr = function( name ) {
        return this.each( function() {
            if ( isSVGElem( this ) ) {
                ( this[ name ] && this[ name ].baseVal ? this[ name ].baseVal.value = '' :
                    this.setAttribute( name, '' ) );
            } else {
                origRemoveAttr.apply( $( this ), [ name ] );
            }
        } );
    };

    function isSVGElem( node ) {
        return ( node.nodeType == 1 && node.namespaceURI == 'http://www.w3.org/2000/svg' );
    }

    function getParameters( custom ) {
            var image = getData( 'image' );
            var selector = getData( 'selector' );
            var fixed_data = {
                'viewPortW': _self.width(),
                'viewPortH': _self.height(),
                'imageX': image.posX,
                'imageY': image.posY,
                'imageRotate': image.rotation,
                'imageW': image.w,
                'imageH': image.h,
                'imageSource': image.source,
                'selectorX': selector.x,
                'selectorY': selector.y,
                'selectorW': selector.w,
                'selectorH': selector.h
            };
            return $.extend( fixed_data, custom );
        }
        /* Defaults */
    $.fn.cropzoom.defaults = {
        width: 500,
        height: 375,
        bgColor: '#000',
        overlayColor: '#fff',
        selector: {
            x: 0,
            y: 0,
            w: 229,
            h: 100,
            aspectRatio: false,
            centered: false,
            borderColor: 'yellow',
            borderColorHover: 'red',
            bgInfoLayer: '#FFF',
            infoFontSize: 10,
            infoFontColor: 'blue',
            showPositionsOnDrag: true,
            showDimetionsOnDrag: true,
            maxHeight: null,
            maxWidth: null
        },
        image: {
            source: '',
            rotation: 0,
            width: 0,
            height: 0,
            minZoom: 10,
            maxZoom: 150
        },
        enableRotation: true,
        enableZoom: true,
        zoomSteps: 1,
        rotationSteps: 5,
        onSelectorDrag: null,
        onSelectorDragStop: null,
        onSelectorResize: null,
        onSelectorResizeStop: null,
        onZoom: null,
        onRotate: null,
        onImageDrag: null

    };

    $.fn.extend( {
        //Function to set the selector position and sizes
        setSelector: function( x, y, w, h, animate ) {
            if ( animate != undefined && animate == true ) {
                $( '#selector' ).animate( {
                    'top': y,
                    'left': x,
                    'width': w,
                    'height': h
                }, 'slow' );
            } else {
                $( '#selector' ).css( {
                    'top': y,
                    'left': x,
                    'width': w,
                    'height': h
                } );
            }
            setData( 'selector', {
                x: x,
                y: y,
                w: w,
                h: h
            } );
        },
        //Restore the Plugin
        restore: function() {
            _self.empty();
            setData( 'image', {} );
            setData( 'selector', {} );
            $( '#img_to_crop' ).remove();
            // _self.cropzoom( $options );

        },
        //Send the Data to the Server
        send: function( url, type, custom, onSuccess ) {
            // console.log( getParameters( custom ) );
            var response = "";
            $.ajax( {
                url: url,
                type: type,
                data: ( getParameters( custom ) ),
                dataType:'json',
                success: function( r ) {
                    setData( 'imageResult', r );
                    if( null != r.resubmitToken && '' != r.resubmitToken ){
                        globals.token = r.resubmitToken;
                    }
                    if ( onSuccess !== undefined && onSuccess != null )
                        onSuccess( r );
                },
                error:function( r ){
                    if( null != r.resubmitToken && '' != r.resubmitToken ){
                        globals.token = r.resubmitToken;
                    }
                }
            } );
        }
    } );

} )( jQuery );