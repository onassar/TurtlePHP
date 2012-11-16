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
            var start=(new Date).getTime(),booted=[],included=false,required=[],js=function(e,t){if(arguments.length===0){t=function(){};e=[]}else if(arguments.length===1){t=e;e=[]}var n=function(e,t){var n=document.createElement("script"),r=document.getElementsByTagName("script"),s=r.length,o=function(){try{t&&t()}catch(e){i(e)}};n.setAttribute("type","text/javascript");n.setAttribute("charset","utf-8");if(n.readyState){n.onreadystatechange=function(){if(n.readyState==="loaded"||n.readyState==="complete"){n.onreadystatechange=null;o()}}}else{n.onload=o}n.setAttribute("src",e);document.body.insertBefore(n,r[s-1].nextSibling)},r=function(e,t){for(var n=0,r=e.length;n<r;++n){if(e[n]===t){return true}}return false},i=function(e){log("Caught Exception:");log(e.stack);log("")};if(included===false){if(typeof e==="string"){e=[e]}e=e.concat(required);included=true}if(typeof e==="string"){if(r(booted,e)){t()}else{booted.push(e);n(e,t)}}else if(e.constructor===Array){if(e.length!==0){js(e.shift(),function(){js(e,t)})}else{try{t&&t()}catch(s){i(s)}}}},log=function(){if(typeof console!=="undefined"&&console&&console.log){var e=arguments.length>1?arguments:arguments[0];console.log(e)}},queue=function(){var e=[];return{push:function(t){e.push(t)},process:function(){var t;while(t=e.shift()){t()}}}}(),ready=function(e){var t=false,n=true,r=window.document,i=r.documentElement,s=r.addEventListener?"addEventListener":"attachEvent",o=r.addEventListener?"removeEventListener":"detachEvent",u=r.addEventListener?"":"on",a=function(n){if(n.type==="readystatechange"&&r.readyState!=="complete"){return}(n.type==="load"?window:r)[o](u+n.type,a,false);if(!t&&(t=true)){e()}},f=function(){try{i.doScroll("left")}catch(e){setTimeout(f,50);return}a("poll")};if(r.readyState==="complete"){e.call(window,"lazy")}else{if(r.createEventObject&&i.doScroll){try{n=!window.frameElement}catch(l){}if(n){f()}}r[s](u+"DOMContentLoaded",a,false);r[s](u+"readystatechange",a,false);window[s](u+"load",a,false)}},require=function(e){if(typeof e==="string"){e=[e]}required=required.concat(e)}
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
            <h1>Hello World!</h1>
            <p>Hai :)</p>
        </div>
        <script type="text/javascript">
        //<![CDATA[
            require('/static/js/core.js');
            ready(js);
        //]]>
        </script>
    </body>
</html>
