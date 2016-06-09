function ttScrollBar(){this.init.apply(this,arguments)}
ttScrollBar.prototype={
		init:function(options){
			this.extend(options);
			this.maxH=this.minH=0;  //bar的最小,大top值
			this.createBar();
			this._posY=this.minH;
			this._y=0;
			this.limitMin=false;
			this.limitMax=true;
			this.preventBubble();
			this._dragDown=this.bind(this,this.dragDown);
			this._dragMove=this.bind(this,this.dragMove);
			this._dragUp=this.bind(this,this.dragUp);
			this._mouseWheel=this.bind(this,this.mouseWheel);
			this.isAEVT=this.parent.clientHeight<this.scroll.offsetHeight;
			this.isAEVT && this.addEvent(this.scrollHandle,'mousedown',this._dragDown);
			this.opt.isOpenKeyEvent && this.isAEVT && this.addEvent(document,'keydown',this.bind(this,this.keyDown));
			this.focusObj=this.opt.focusObj =='scroll'? this.parent : (this.opt.focusObj=='scrollBar' ? this.scrollBar : document); 
			this.opt.isMouseWheel && this.isAEVT && this.addEvent(this.focusObj,'mousewheel',this._mouseWheel);
			this.opt.isMouseWheel && this.isAEVT && this.addEvent(this.focusObj,'DOMMouseScroll',this._mouseWheel);
			this.startEvt=this.opt.scrollEventType;
			this.endEvt= this.startEvt== 'mousedown' ? 'mouseup':'mouseout';
			this.addEvent(this.scrollDown,this.startEvt,this.bind(this,this.downMove));
			this.addEvent(this.scrollUp,this.startEvt,this.bind(this,this.upMove));
			//this.resetLayout();
		},
		resetLayout:function(){//重置滚动条显示与隐藏及重新添加或删除事件,主要用于滚动容器高度发生变化时
			if(this.scroll.offsetHeight < this.parent.clientHeight)
			{  
			    this.delEvent(this.focusObj, 'DOMMouseScroll', this._mouseWheel);
			    this.delEvent(this.focusObj, 'mousewheel', this._mouseWheel);
			    this.scrollHandle.style.top=this.minH+'px';
			    this.scroll.style.top=0;
			    this.scrollBar.style.display='none';
			    
			}
			else
			{
			    this.scrollBar.style.display='block';
			    this.opt.isMouseWheel&&this.addEvent(this.focusObj, 'mousewheel',this._mouseWheel);
			    this.opt.isMouseWheel&&this.addEvent(this.focusObj, 'DOMMouseScroll', this._mouseWheel);
			    this.addEvent(this.scrollHandle,'mousedown',this._dragDown);
			}
		},
		keyDown:function(e){
			var ev=window.event||e,
			key=ev.keyCode;
			switch(key)
			{
				case 38:
					if(this.opt.isBuffer)
					{
						var t=this.setTop(this.scrollHandle.offsetTop-this.opt.rollScale,true);
						this.doMove(this.scrollHandle,t[1]);
						this.doMove(this.scroll,t[0]);
					}
					else
					{
						this.setTop(this.scrollHandle.offsetTop-this.opt.rollScale)
					}
				break;
				case 40:
					if(this.opt.isBuffer)
					{
						var t=this.setTop(this.scrollHandle.offsetTop+this.opt.rollScale,true);
						this.doMove(this.scrollHandle,t[1]);
						this.doMove(this.scroll,t[0]);
					}
					else
					{
						this.setTop(this.scrollHandle.offsetTop+this.opt.rollScale)
					} 
				break;
			}
			ev.preventDefault && ev.preventDefault();
			return false;
		},
		dragDown:function(e){ 
			var ev=window.event||e;
			this._posY=this._y=ev.clientY-this.scrollHandle.offsetTop;
			this.addEvent(document,'mousemove',this._dragMove);
			this.addEvent(document,'mouseup',this._dragUp);
			ev.preventDefault && ev.preventDefault();
        	this.scrollHandle.setCapture && this.scrollHandle.setCapture();
		},
		dragMove:function(e){
			var ev=window.event||e,
			top=ev.clientY-this._y;
			if(this.opt.isBuffer)
			{
				var t=this.setTop(top,true)[1];
				this.scrollHandle.style.top=t+'px';
			}
			else
			{
				this.setTop(top,false);
			}
		},
		dragUp:function(e){
			if(this.opt.isBuffer)
			{
				var ev=window.event||e;
				this._posY=ev.clientY-this._posY;
				var top=this.setTop(this._posY,true)[0];
				this.doMove(this.scroll,top);
			}
			this.delEvent(document,'mousemove',this._dragMove);
			this.delEvent(document,'mouseup',this._dragUp);
			this.scrollHandle.releaseCapture && this.scrollHandle.releaseCapture();
		},
		downMove:function()
		{
			var This=this;
			clearInterval(this.scrollUp.timer);
			if(this.scrollHandle.offsetTop==this.maxH || this.limitMax==false)return;
			this.scrollDown.timer=setInterval(function(){
				This.limitMax=true;
				if(This.scrollHandle.offsetTop==This.maxH)
				{
					This.limitMax=false;
					clearInterval(This.scrollDown.timer);
					This.delEvent(This.scrollDown,This.startEvt,This.downMove);
				}
				var a=This.setTop(This.scrollHandle.offsetTop+This.opt.rollScale,true);
				This.doMove(This.scrollHandle,a[1]);
				This.doMove(This.scroll,a[0]);
			},50);
			this.addEvent(this.scrollDown,this.endEvt,function(){
				  clearInterval(This.scrollDown.timer);
				  This.delEvent(This.scrollDown,This.startEvt,This.downMove);
			})
		},
		upMove:function()
		{
			var This=this;
			clearInterval(this.scrollUp.timer);
			if(this.scrollHandle.offsetTop==this.minH || this.limitMin==false)return;
			this.scrollUp.timer=setInterval(function(){
				This.limitMin=true;
				if(This.scrollHandle.offsetTop==This.minH)
				{
					This.limitMin=false;
					clearInterval(This.scrollUp.timer);
					This.delEvent(This.scrollUp,This.startEvt,This.upMove);
				}
				var a=This.setTop(This.scrollHandle.offsetTop-This.opt.rollScale,true);
				This.doMove(This.scrollHandle,a[1]);
				This.doMove(This.scroll,a[0]);
			},50);
			this.addEvent(this.scrollUp,this.endEvt,function(){
				  clearInterval(This.scrollUp.timer);
				  This.delEvent(This.scrollUp,This.startEvt,This.upMove);
			})
		},
		setTop:function(t,f){
			t < this.minH ? (t=this.minH) && (this.limitMin=false) : (this.limitMin=true);
			t > this.maxH ? (t=this.maxH) && (this.limitMax=false) : (this.limitMax=true);
			var top=(this.scroll.offsetHeight-this.parent.clientHeight)/(this.maxH-this.minH) * (t-this.minH);
			
			if(!f)
			{
				this.scroll.style.top=-top+'px';
				this.scrollHandle.style.top=t+'px';
			}
			else
			{
				return [parseInt(-top),parseInt(t)];
			}
		},
		mouseWheel:function(e){
			var ev=window.event||e
			,flag=true
			,ht=this.scrollHandle.offsetTop
			,rs=this.opt.rollScale;
			flag=ev.wheelDelta ? ev.wheelDelta < 0 : ev.detail > 0;
			switch(flag)
			{
				case true :
					this.opt.isBuffer?(this.doMove(this.scrollHandle,this.setTop(ht+rs,true)[1]),this.doMove(this.scroll,this.setTop(ht+rs,true)[0])) :this.setTop(ht+rs);
				break;
				default :
					this.opt.isBuffer?(this.doMove(this.scrollHandle,this.setTop(ht-rs,true)[1]),this.doMove(this.scroll,this.setTop(ht-rs,true)[0])):this.setTop(ht-rs)
			}
			ev.preventDefault && ev.preventDefault();
       		return false;
		},
		preventBubble:function()  //阻止冒泡
		{
			this.scrollHandle.onclick=function(ev)
			{
				var oEvent = ev || window.event;      
				oEvent.stopPropagation ? oEvent.stopPropagation() : (oEvent.cancelBubble = true);
			}
		},
		createBar:function(){//创建滚动条
			var opt=this.opt
			,//parent=opt.container.parentNode
			parent=document.getElementById('Scrollbox')
			,scroll=document.createElement('div')
			,scrollBar=document.createElement('div')
			,scrollUp=document.createElement('div')
			,scrollHandle=document.createElement('div')
			,scrollDown=document.createElement('div');

			scrollBar.className=opt.scrollBarClass =='scrollBar'? 'scrollBar':'scrollBar '+opt.scrollBarClass ;
			scrollHandle.className=opt.scrollHandleClass=='scrollHandle'? 'scrollHandle':'scrollHandle '+opt.scrollBarClass ;;
			scrollBar.appendChild(scrollUp);
			scrollBar.appendChild(scrollHandle);
			scrollBar.appendChild(scrollDown);
			scroll.style.height=opt.container.offsetHeight+'px';
			scroll.style.width=parent.clientWidth-10 +'px';
			scroll.style.position='absolute';
			scroll.style.left='0';
			scroll.style.top='0';
			if(this.getStyle(parent,'position')=='static'){
				parent.style.position='relative';
			}
			if(this.getStyle(parent,'overflow')=='visible')
			{
				parent.style.overflow='hidden';
			}
			parseInt(this.getStyle(parent,'paddingTop'))>0 && (scroll.style.paddingTop=this.getStyle(parent,'paddingTop'),(parent.style.paddingTop=0));
			parseInt(this.getStyle(parent,'paddingLeft'))>0 && (scroll.style.paddingLeft=this.getStyle(parent,'paddingLeft'),(parent.style.paddingLeft=0));
			parseInt(this.getStyle(parent,'paddingRight'))>0 && (scroll.style.paddingRight=this.getStyle(parent,'paddingRight'),(parent.style.paddingRight=0));
			parseInt(this.getStyle(parent,'paddingBottom'))>0 && (scroll.style.paddingBottom=this.getStyle(parent,'paddingBottom'),(parent.style.paddingBottom=0));
			scroll.appendChild(opt.container);
			//parent.innerHTML='';
			parent.appendChild(scrollBar);
			parent.appendChild(scroll);
			this.maxH=scrollBar.clientHeight-scrollUp.clientHeight+opt.scrollDValue/2-scrollHandle.clientHeight;
			this.minH=scrollUp.clientHeight-opt.scrollDValue/2;
			this.parent=parent;
			this.scroll=scroll;
			this.scrollBar=scrollBar;
			this.scrollHandle=scrollHandle;
			this.scrollUp=scrollUp;
			this.scrollDown=scrollDown;
		},
		extend:function(p){//配置参数
			this.opt={
			  	   container:'scrollBarDemo',	//要加滚动条的对象
			  	   isBuffer:true,		//是否开启缓冲效果
			  	   isMouseWheel:true,   //是否开启鼠标滚动
			  	   rollScale:30,		//缓冲最小单位值
			  	   focusObj:'scroll',	//鼠标滚轮焦点对象 可选 document,scroll,scrollBar
			  	   scrollEventType:'mouseover', //mousedown,mouseover 两种类型
			  	   scrollBarClass:'scrollBar',  //class
			  	   scrollUpClass:'scrollUp',
			  	   scrollHandleClass:'scrollHandle',
			  	   scrollDownClass:'scrollDown',
			  	   isOpenKeyEvent:true,	//是否支持键盘按键
			  	   scrollDValue:8  	//滚动的差值,上下的和 半圆部分
				}
			for(var a in p){
				this.opt[a]=p[a];
			}
		},
		doMove:function(o,t,fn){
			  o.timer && clearInterval(o.timer);
			  o.timer=setInterval(function(){
			  		var ic=o.offsetTop || 0,is=(t-ic)/5;
			  		is=is>0 ? Math.ceil(is):Math.floor(is);
			  		ic == t ? (clearInterval(o.timer),fn&&fn()):(o.style.top=ic+is+'px');
			  },30)
		},
		getStyle:function(o,a){
			 return o.currentStyle ? o.currentStyle[a] : getComputedStyle(o,false)[a];
		},
		bind: function(obj, fn) {
		    return function() {
		        return fn.apply(obj,arguments);
		    }
		},
		addEvent: function(obj, oEv, fn) {
		    return obj.attachEvent ? obj.attachEvent('on' + oEv, fn) : obj.addEventListener(oEv, fn, false);
		},
		delEvent: function(obj, oEv, fn) {
		    return obj.detachEvent ? obj.detachEvent('on' + oEv, fn) : obj.removeEventListener(oEv, fn, false);
		}
}