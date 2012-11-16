<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />


        <!-- og -->
        <title>Application</title>
        <meta name="description" content="" />
        <meta name="author" content="" />
        <meta property="og:title" content="(imagination)" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://website.com" />
        <meta property="og:image" content="http://website.com/lennon.jpg"/>
        <meta property="og:site_name" content="(imagination)" />
        <meta property="og:description" content="(imagination)" />


        <!-- bootstrap -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link href="/static/vendors/bootstrap/css/bootstrap.css" rel="stylesheet" />
        <style>
            body {
                padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
            }
        </style>
        <link href="/static/vendors/bootstrap/css/bootstrap-responsive.css" rel="stylesheet" />


        <!-- shiv -->
        <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->


        <!-- js loading -->
        <script type="text/javascript">
        //<![CDATA[
            var start=(new Date()).getTime(),booted=[],included=false,required=[],js=function(assets,callback){if(arguments.length===1){callback=assets;assets=[]}var __boot=function(src,callback){var script=document.createElement("script"),scripts=document.getElementsByTagName("script"),length=scripts.length,loaded=function(){try{callback&&callback()}catch(exception){log("[Caught Exception]",exception)}};script.setAttribute("type","text/javascript");script.setAttribute("charset","utf-8");if(script.readyState){script.onreadystatechange=function(){if(script.readyState==="loaded"||script.readyState==="complete"){script.onreadystatechange=null;loaded()}}}else{script.onload=loaded}script.setAttribute("src",src);document.body.insertBefore(script,scripts[(length-1)].nextSibling)},__contains=function(arr,query){for(var x=0,l=arr.length;x<l;++x){if(arr[x]===query){return true}}return false};if(included===false){if(typeof assets==="string"){assets=[assets]}assets=assets.concat(required);included=true}if(typeof assets==="string"){if(__contains(booted,assets)){callback()}else{booted.push(assets);__boot(assets,callback)}}else{if(assets.constructor===Array){if(assets.length!==0){js(assets.shift(),function(){js(assets,callback)})}else{try{callback&&callback()}catch(exception){log("[Caught Exception]",exception)}}}}},log=function(){if(typeof(console)!=="undefined"&&console&&console.log){var args=arguments.length>1?arguments:arguments[0];console.log(args)}},queue=(function(){var stack=[];return{push:function(task){stack.push(task)},process:function(){var task;while(task=stack.shift()){task()}}}})(),ready=function(callback){var done=false,top=true,doc=window.document,root=doc.documentElement,add=doc.addEventListener?"addEventListener":"attachEvent",rem=doc.addEventListener?"removeEventListener":"detachEvent",pre=doc.addEventListener?"":"on",init=function(e){if(e.type==="readystatechange"&&doc.readyState!=="complete"){return}(e.type==="load"?window:doc)[rem](pre+e.type,init,false);if(!done&&(done=true)){callback.call(window,e.type||e)}},poll=function(){try{root.doScroll("left")}catch(e){setTimeout(poll,50);return}init("poll")};if(doc.readyState==="complete"){callback.call(window,"lazy")}else{if(doc.createEventObject&&root.doScroll){try{top=!window.frameElement}catch(e){}if(top){poll()}}doc[add](pre+"DOMContentLoaded",init,false);doc[add](pre+"readystatechange",init,false);window[add](pre+"load",init,false)}},require=function(assets){if(typeof assets==="string"){assets=[assets]}required=required.concat(assets)};
        //]]>
        </script>


        <!-- analytics -->
        <script type="text/javascript">
        //<![CDATA[
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', 'UA-xxxxxx-xx']);
            _gaq.push(['_trackPageview']);
            (function() {
                var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
            })(); 
        //]]>
        </script>
    </head>
    <body>
        <div class="navbar navbar-inverse navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <a class="brand" href="#">Application</a>
                    <div class="nav-collapse collapse">
                        <ul class="nav">
                            <li class="active"><a href="#">Home</a></li>
                            <li><a href="#about">Element</a></li>
                        </ul>
                    </div><!--/.nav-collapse -->
                </div>
            </div>
        </div>
        <div class="container">
            <h1>Bootstrap</h1>
            <p>:-)</p>
        </div>
    </body>
</html>
