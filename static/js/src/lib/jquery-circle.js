"use strict";(function(a){a.fn.circleChart=function(g){if(typeof Object.assign!="function"){Object.assign=function(target){if(target==null){throw new TypeError("Cannot convert undefined or null to object")}target=Object(target);for(var index=1;index<arguments.length;index++){var source=arguments[index];if(source!=null){for(var key in source){if(Object.prototype.hasOwnProperty.call(source,key)){target[key]=source[key]}}}}return target}}var h={color:"#3459eb",backgroundColor:"#e6e6e6",backgroundBorderColor:"#e5e5e5",background:!0,speed:2000,widthRatio:0.2,value:66,unit:"percent",counterclockwise:!1,size:110,startAngle:0,animate:!0,backgroundFix:!0,lineCap:"round",animation:"easeInOutCubic",text:!1,redraw:!1,cAngle:0,textCenter:!0,textSize:!1,textWeight:"normal",textFamily:"sans-serif",relativeTextSize:1/7,autoCss:!0,onDraw:!1};Math.linearTween=function(r,s,u,v){return u*r/v+s},Math.easeInQuad=function(r,s,u,v){return r/=v,u*r*r+s},Math.easeOutQuad=function(r,s,u,v){return r/=v,-u*r*(r-2)+s},Math.easeInOutQuad=function(r,s,u,v){return(r/=v/2,1>r)?u/2*r*r+s:(r--,-u/2*(r*(r-2)-1)+s)},Math.easeInCubic=function(r,s,u,v){return r/=v,u*r*r*r+s},Math.easeOutCubic=function(r,s,u,v){return r/=v,r--,u*(r*r*r+1)+s},Math.easeInOutCubic=function(r,s,u,v){return(r/=v/2,1>r)?u/2*r*r*r+s:(r-=2,u/2*(r*r*r+2)+s)},Math.easeInQuart=function(r,s,u,v){return r/=v,u*r*r*r*r+s},Math.easeOutQuart=function(r,s,u,v){return r/=v,r--,-u*(r*r*r*r-1)+s},Math.easeInOutQuart=function(r,s,u,v){return(r/=v/2,1>r)?u/2*r*r*r*r+s:(r-=2,-u/2*(r*r*r*r-2)+s)},Math.easeInQuint=function(r,s,u,v){return r/=v,u*r*r*r*r*r+s},Math.easeOutQuint=function(r,s,u,v){return r/=v,r--,u*(r*r*r*r*r+1)+s},Math.easeInOutQuint=function(r,s,u,v){return(r/=v/2,1>r)?u/2*r*r*r*r*r+s:(r-=2,u/2*(r*r*r*r*r+2)+s)},Math.easeInSine=function(r,s,u,v){return -u*Math.cos(r/v*(Math.PI/2))+u+s},Math.easeOutSine=function(r,s,u,v){return u*Math.sin(r/v*(Math.PI/2))+s},Math.easeInOutSine=function(r,s,u,v){return -u/2*(Math.cos(Math.PI*r/v)-1)+s},Math.easeInExpo=function(r,s,u,v){return u*Math.pow(2,10*(r/v-1))+s},Math.easeOutExpo=function(r,s,u,v){return u*(-Math.pow(2,-10*r/v)+1)+s},Math.easeInOutExpo=function(r,s,u,v){return(r/=v/2,1>r)?u/2*Math.pow(2,10*(r-1))+s:(r--,u/2*(-Math.pow(2,-10*r)+2)+s)},Math.easeInCirc=function(r,s,u,v){return r/=v,-u*(Math.sqrt(1-r*r)-1)+s},Math.easeOutCubic=function(r,s,u,v){return r/=v,r--,u*(r*r*r+1)+s},Math.easeInOutCubic=function(r,s,u,v){return(r/=v/2,1>r)?u/2*r*r*r+s:(r-=2,u/2*(r*r*r+2)+s)},Math.easeOutCirc=function(r,s,u,v){return r/=v,r--,u*Math.sqrt(1-r*r)+s},Math.easeInOutCirc=function(r,s,u,v){return(r/=v/2,1>r)?-u/2*(Math.sqrt(1-r*r)-1)+s:(r-=2,u/2*(Math.sqrt(1-r*r)+1)+s)};var i=function(r,s,u,v,w,x,y,z){var A=Object.create(i.prototype);return A.pos=r,A.bAngle=s,A.eAngle=u,A.cAngle=v,A.radius=w,A.lineWidth=x,A.sAngle=y,A.settings=z,A};i.prototype={onDraw:function onDraw(r){if(!1!==this.settings.onDraw){var u=Object.assign({},this),s={percent:p,rad:function rad(v){return v},"default":m};u.value=(s[this.settings.unit]||s["default"])(u.cAngle),u.text=function(v){return j(r,v)},u.settings.onDraw(r,u)}},drawBackgroundBorder:function(r){r.beginPath();r.arc(this.pos,this.pos,this.radius-this.lineWidth/2,0,2*Math.PI);r.lineWidth=1;r.strokeStyle=this.settings.backgroundBorderColor;r.stroke();r.beginPath();r.arc(this.pos,this.pos,this.radius+this.lineWidth/2,0,2*Math.PI);r.lineWidth=1;r.strokeStyle=this.settings.backgroundBorderColor;r.stroke()},drawBackground:function drawBackground(r){r.beginPath(),r.arc(this.pos,this.pos,this.settings.backgroundFix?0.9999*this.radius:this.radius,0,2*Math.PI),r.lineWidth=this.settings.backgroundFix?0.95*this.lineWidth:this.lineWidth,r.strokeStyle=this.settings.backgroundColor,r.stroke();if(this.settings.backgroundBorderColor){this.drawBackgroundBorder(r)}},draw:function draw(r){if(r.beginPath(),this.settings.counterclockwise){var s=2*Math.PI;r.arc(this.pos,this.pos,this.radius,s-this.bAngle,s-(this.bAngle+this.cAngle),this.settings.counterclockwise)}else{r.arc(this.pos,this.pos,this.radius,this.bAngle,this.bAngle+this.cAngle,this.settings.counterclockwise)}r.lineWidth=this.lineWidth,r.lineCap=this.settings.lineCap,r.strokeStyle=this.settings.color,r.stroke()},animate:function animate(r,s,u,v,w){var y=this,x=new Date().getTime()-u;1>x&&(x=1),u-v<1.05*this.settings.speed&&(!w&&1000*this.cAngle<=Math.floor(1000*this.eAngle)||w&&1000*this.cAngle>=Math.floor(1000*this.eAngle))?(this.cAngle=Math[this.settings.animation]((u-v)/x,this.sAngle,this.eAngle-this.sAngle,this.settings.speed/x),s.clearRect(0,0,this.settings.size,this.settings.size),this.settings.background&&this.drawBackground(s),this.draw(s),this.onDraw(r),u=new Date().getTime(),q(function(){return y.animate(r,s,u,v,w)})):(this.cAngle=this.eAngle,s.clearRect(0,0,this.settings.size,this.settings.size),this.settings.background&&this.drawBackground(s),this.draw(s),this.setCurrentAnglesData(r))},setCurrentAnglesData:function setCurrentAnglesData(r){var s={percent:p,rad:function rad(v){return v
},"default":m},u=s[this.settings.unit]||s["default"];r.data("current-c-angle",u(this.cAngle)),r.data("current-start-angle",u(this.bAngle))}};var j=function(r,s){r.data("text",s),a(".circleChart_text",r).html(s)},l=function(r){var s=r.getContext("2d"),u=window.devicePixelRatio||1,v=s.webkitBackingStorePixelRatio||s.mozBackingStorePixelRatio||s.msBackingStorePixelRatio||s.oBackingStorePixelRatio||s.backingStorePixelRatio||1,w=u/v,x=r.width,y=r.height;r.width=x*w,r.height=y*w,r.style.width=x+"px",r.style.height=y+"px",s.scale(w,w)},m=function(r){return 180*(r/Math.PI)},n=function(r){return r/180*Math.PI},o=function(r){return n(360*(r/100))},p=function(r){return 100*(m(r)/360)},q=function(){return window.requestAnimationFrame||window.webkitRequestAnimationFrame||window.mozRequestAnimationFrame||window.oRequestAnimationFrame||window.msRequestAnimationFrame||function(r){window.setTimeout(r,1000/60)}}();return this.each(function(r,s){var D=a(s),u={},v=D.data();for(var E in v){v.hasOwnProperty(E)&&0===E.indexOf("_cache_")&&h.hasOwnProperty(E.substring(7))&&(u[E.substring(7)]=v[E])}var F=Object.assign({},h,u,v,g);for(var G in F){0!==G.indexOf("_cache_")&&D.data("_cache_"+G,F[G])}a("canvas.circleChart_canvas",D).length||(D.append(function(){return a("<canvas/>",{"class":"circleChart_canvas"}).prop({width:F.size,height:F.size}).css(F.autoCss?{"margin-left":"auto","margin-right":"auto",display:"block"}:{})}),l(a("canvas",D).get(0))),a("p.circleChart_text",D).length||!1===F.text||(D.append("<p class='circleChart_text'>"+F.text+"</p>"),F.autoCss&&(F.textCenter?a("p.circleChart_text",D).css({position:"absolute","line-height":F.size+"px",top:0,width:"100%",margin:0,padding:0,"text-align":"center","font-size":!1===F.textSize?F.size*F.relativeTextSize:F.textSize,"font-weight":F.textWeight,"font-family":F.textFamily}):a("p.circleChart_text",D).css({"padding-top":"5px","text-align":"center","font-weight":F.textWeight,"font-family":F.textFamily,"font-size":!1===F.textSize?F.size*F.relativeTextSize:F.textSize}))),F.autoCss&&D.css("position","relative"),F.redraw||(F.cAngle=F.currentCAngle?F.currentCAngle:F.cAngle,F.startAngle=F.currentStartAngle?F.currentStartAngle:F.startAngle);var w=a("canvas",D).get(0),x=w.getContext("2d"),y={percent:o,rad:function rad(L){return L},"default":n},H=y[F.unit]||y["default"],I=H(F.startAngle),J=H(F.value),K=H(F.cAngle),z=F.size/2,A=z*(1-F.widthRatio/2),B=A*F.widthRatio,C=i(z,I,J,K,A,B,K,F);D.data("size",F.size),F.animate?0===F.value?q(function(){x.clearRect(0,0,F.size,F.size),C.settings.background&&C.drawBackground(x),C.onDraw(D)}):C.animate(D,x,new Date().getTime(),new Date().getTime(),K>J):(C.cAngle=C.eAngle,q(function(){x.clearRect(0,0,F.size,F.size),F.background&&C.drawBackground(x),0===F.value?C.settings.background&&C.drawBackground(x):(C.draw(x),C.setCurrentAnglesData(D)),C.onDraw(D)}))})}})(jQuery);