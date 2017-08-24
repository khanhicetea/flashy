<?php
namespace Flashy\Http\Handler\Formatter;

use Throwable;
use SplFileObject;
use Flashy\Http\Utils;
use Psr\Http\Message\ServerRequestInterface;

class DebugError extends HtmlError
{
    protected function processErrorBody(ServerRequestInterface $request, Throwable $e) : string
    {
        $traces = $this->parseTraceString($e->getTraceAsString());

        return $this->render($this->template, [
            'msg' => sprintf("%s (%s)", $e->getMessage(), $e->getCode()),
            'q' => sprintf("%s %s", $e->getMessage(), basename($e->getFile())),
            'description' => sprintf('<a class="clipboard" data-clipboard-text="%s"><strong>%s</strong></a> : %s', $e->getFile(), $e->getFile(), $e->getLine()),
            'error_code' => $this->readErrorFile($e),
            'traces' => $this->renderTraceString($traces),
            'http_request' => implode("\n", Utils::dumpHeader($request)),
            'http_payload' => Utils::dumpPayload($request),
        ]);
    }

    protected function parseTraceString(string $string) : array
    {
        return array_map(function ($line) {
            if (preg_match('/#(\d+) (.*\/)([^\/]*)\((\d+)\): (.*)/', $line, $matches)) {
                return [
                    '#' => $matches[1],
                    'dir' => $matches[2],
                    'file' => $matches[3],
                    'line' => $matches[4],
                    'call' => $matches[5],
                ];
            }
            return substr($line, strpos($line, " ") + 1);
        }, explode("\n", $string));
    }

    protected function renderTraceString(array $traces)
    {
        $count = 0;
        $items = [];
        foreach ($traces as $trace) {
            if (is_array($trace)) {
                $path = $trace['dir'].$trace['file'];
                $items[] = sprintf('<div class="media"><div class="media-left"><div class="avatarholder">%d</div></div><div class="media-body"><div class="media-heading"><a class="clipboard" title="%s" data-clipboard-text="%s">%s</a> : %s</div><div class="media-content">%s</div></div></div>', $count, $path, $path, $trace['file'], $trace['line'], htmlentities($trace['call']));
            } else {
                $items[] = sprintf('<div class="media"><div class="media-left"><div class="avatarholder">%d</div></div><div class="media-body"><div class="media-heading"><a>%s</a></div><div class="media-content"></div></div></div>', $count, htmlentities($trace));
            }
            $count++;
        }
        return implode("\n", $items);
    }

    protected function render($html, $data)
    {
        foreach ($data as $key => $value) {
            $html = str_replace('{{'.$key.'}}', $value, $html);
        }
        return $html;
    }

    protected function readErrorFile(Throwable $e, $surround = 3)
    {
        $file = new SplFileObject($e->getFile());
        $begin = max(0, $e->getLine() - $surround);
        $count = 0;
        $content = "";
        $strlen = strlen($begin + $surround);

        while (!$file->eof()) {
            $count++;
            $line = $file->fgets();
            if ($count >= $begin && ($count - $begin) < 2 * $surround) {
                $content .= str_pad($count, $strlen, " ", STR_PAD_LEFT).". ".$line;
            }
        }

        return $content;
    }

    public function __construct()
    {
        $this->template = <<<EOF
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>{{msg}}</title>
    </head>
    <style>
    html{font-size:12px}*{box-sizing:border-box;text-rendering:geometricPrecision}body{font-size:1rem;line-height:1.5rem;margin:0;font-family:Menlo,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New,monospace,serif;word-wrap:break-word}h1,h2,h3,h4,h5,h6{line-height:1.3em}fieldset{border:none;padding:0;margin:0}pre{padding:2rem;margin:1.75rem 0;background-color:#fff;border:1px solid #ccc;overflow:auto}code[class*=language-],pre[class*=language-],pre code{font-weight:100;text-shadow:none;margin:1.75rem 0}a{cursor:pointer;color:#ff2e88;text-decoration:none;border-bottom:1px solid #ff2e88}a:hover{background-color:#ff2e88;color:#fff}.grid{display:-ms-flexbox;display:flex;-ms-flex-wrap:wrap;flex-wrap:wrap}.grid.\-top{-ms-flex-align:start;-ms-grid-row-align:flex-start;align-items:flex-start}.grid.\-middle{-ms-flex-align:center;-ms-grid-row-align:center;align-items:center}.grid.\-bottom{-ms-flex-align:end;-ms-grid-row-align:flex-end;align-items:flex-end}.grid.\-stretch{-ms-flex-align:stretch;-ms-grid-row-align:stretch;align-items:stretch}.grid.\-baseline{-ms-flex-align:baseline;-ms-grid-row-align:baseline;align-items:baseline}.grid.\-left{-ms-flex-pack:start;justify-content:flex-start}.grid.\-center{-ms-flex-pack:center;justify-content:center}.grid.\-right{-ms-flex-pack:end;justify-content:flex-end}.grid.\-between{-ms-flex-pack:justify;justify-content:space-between}.grid.\-around{-ms-flex-pack:distribute;justify-content:space-around}.cell{-ms-flex:1;flex:1;box-sizing:border-box}@media screen and (min-width:768px){.cell.\-1of12{-ms-flex:0 0 8.33333%;flex:0 0 8.33333%}.cell.\-2of12{-ms-flex:0 0 16.66667%;flex:0 0 16.66667%}.cell.\-3of12{-ms-flex:0 0 25%;flex:0 0 25%}.cell.\-4of12{-ms-flex:0 0 33.33333%;flex:0 0 33.33333%}.cell.\-5of12{-ms-flex:0 0 41.66667%;flex:0 0 41.66667%}.cell.\-6of12{-ms-flex:0 0 50%;flex:0 0 50%}.cell.\-7of12{-ms-flex:0 0 58.33333%;flex:0 0 58.33333%}.cell.\-8of12{-ms-flex:0 0 66.66667%;flex:0 0 66.66667%}.cell.\-9of12{-ms-flex:0 0 75%;flex:0 0 75%}.cell.\-10of12{-ms-flex:0 0 83.33333%;flex:0 0 83.33333%}.cell.\-11of12{-ms-flex:0 0 91.66667%;flex:0 0 91.66667%}}@media screen and (max-width:768px){.grid{-ms-flex-direction:column;flex-direction:column}.cell{-ms-flex:0 0 auto;flex:0 0 auto}}.hack,.hack blockquote,.hack code,.hack em,.hack h1,.hack h2,.hack h3,.hack h4,.hack h5,.hack h6,.hack strong{font-size:1rem;font-style:normal;font-family:Menlo,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New,monospace,serif}.hack blockquote,.hack code,.hack em,.hack strong{line-height:20px}.hack blockquote,.hack code,.hack footer,.hack h1,.hack h2,.hack h3,.hack h4,.hack h5,.hack h6,.hack header,.hack li,.hack ol,.hack p,.hack section,.hack ul{float:none;margin:0;padding:0}.hack blockquote,.hack h1,.hack ol,.hack p,.hack ul{margin-top:20px;margin-bottom:20px}.hack h1{position:relative;display:inline-block;display:table-cell;padding:20px 0 30px;margin:0;overflow:hidden}.hack h1:after{content:"====================================================================================================";position:absolute;bottom:10px;left:0}.hack h1+*{margin-top:0}.hack h2,.hack h3,.hack h4,.hack h5,.hack h6{position:relative;margin-bottom:1.75rem}.hack h2:before,.hack h3:before,.hack h4:before,.hack h5:before,.hack h6:before{display:inline}.hack h2:before{content:"## "}.hack h3:before{content:"### "}.hack h4:before{content:"#### "}.hack h5:before{content:"##### "}.hack h6:before{content:"###### "}.hack li{position:relative;display:block;padding-left:20px}.hack li:after{position:absolute;top:0;left:0}.hack ul>li:after{content:"-"}.hack ol{counter-reset:a}.hack ol>li:after{content:counter(a) ".";counter-increment:a}.hack blockquote{position:relative;padding-left:17px;padding-left:2ch;overflow:hidden}.hack blockquote:after{content:">\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>\A>";white-space:pre;position:absolute;top:0;left:0;line-height:20px}.hack em:after,.hack em:before{content:"*";display:inline}.hack pre code:after,.hack pre code:before{content:''}.hack code{font-weight:700}.hack code:after,.hack code:before{content:"`";display:inline}.hack hr{position:relative;height:20px;overflow:hidden;border:0;margin:20px 0}.hack hr:after{content:"----------------------------------------------------------------------------------------------------";position:absolute;top:0;left:0;line-height:20px;width:100%;word-wrap:break-word}@-moz-document url-prefix(){.hack h1{display:block}}.hack-ones ol>li:after{content:"1."}p{margin:0 0 1.75rem}.container{max-width:70rem}.container,.container-fluid{margin:0 auto;padding:0 1rem}.inner{padding:1rem}.inner2x{padding:2rem}.pull-left{float:left}.pull-right{float:right}.progress-bar{height:8px;opacity:.8;background-color:#ccc;margin-top:12px}.progress-bar.progress-bar-show-percent{margin-top:38px}.progress-bar-filled{background-color:gray;height:100%;transition:width .3s ease;position:relative;width:0}.progress-bar-filled:before{content:'';border:6px solid transparent;border-top-color:gray;position:absolute;top:-12px;right:-6px}.progress-bar-filled:after{color:gray;content:attr(data-filled);display:block;font-size:12px;white-space:nowrap;position:absolute;border:6px solid transparent;top:-38px;right:0;-ms-transform:translateX(50%);transform:translateX(50%)}table{width:100%;border-collapse:collapse;margin:1.75rem 0;color:#778087}table td,table th{vertical-align:top;border:1px solid #ccc;line-height:15px;padding:10px}table thead th{font-size:10px}table tbody td:first-child{font-weight:700;color:#333}.form{width:30rem}.form-group{margin-bottom:1.75rem;overflow:auto}.form-group label{border-bottom:2px solid #ccc;color:#333;width:10rem;display:inline-block;height:38px;line-height:38px;padding:0;float:left;position:relative}.form-group.form-success label{color:#4caf50!important;border-color:#4caf50!important}.form-group.form-warning label{color:#ff9800!important;border-color:#ff9800!important}.form-group.form-error label{color:#f44336!important;border-color:#f44336!important}.form-control{outline:none;border:none;border-bottom:2px solid #ccc;padding:.5rem 0;width:20rem;height:38px;background-color:transparent}.form-control:focus{border-color:#555}.form-group.form-textarea label:after{position:absolute;content:'';width:2px;background-color:#fff;right:-2px;top:0;bottom:0}textarea.form-control{height:auto;resize:none;padding:1rem 0;border-bottom:2px solid #ccc;border-left:2px solid #ccc;padding:.5rem}select.form-control{border-radius:0;background-color:transparent;-webkit-appearance:none;-moz-appearance:none;-ms-appearance:none}.help-block{color:#999;margin-top:.5rem}.form-actions{margin-bottom:1.75rem}.btn{display:-ms-inline-flexbox;display:inline-flex;-ms-flex-align:center;align-items:center;-ms-flex-pack:center;justify-content:center;cursor:pointer;outline:none;padding:.65rem 2rem;font-size:1rem;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;position:relative;z-index:1}.btn:active{box-shadow:inset 0 1px 3px rgba(0,0,0,.12)}.btn.btn-ghost{border-color:#757575;color:#757575;background-color:transparent}.btn.btn-ghost:focus,.btn.btn-ghost:hover{border-color:#424242;color:#424242;z-index:2}.btn.btn-ghost:hover{background-color:transparent}.btn-block{width:100%;display:-ms-flexbox;display:flex}.btn-default{color:#fff;background-color:#e0e0e0;border:1px solid #e0e0e0;color:#333}.btn-default:focus:not(.btn-ghost),.btn-default:hover{background-color:#dcdcdc;border-color:#dcdcdc}.btn-success{color:#fff;background-color:#4caf50;border:1px solid #4caf50}.btn-success:focus:not(.btn-ghost),.btn-success:hover{background-color:#43a047;border-color:#43a047}.btn-success.btn-ghost{border-color:#4caf50;color:#4caf50}.btn-success.btn-ghost:focus,.btn-success.btn-ghost:hover{border-color:#388e3c;color:#388e3c;z-index:2}.btn-error{color:#fff;background-color:#f44336;border:1px solid #f44336}.btn-error:focus:not(.btn-ghost),.btn-error:hover{background-color:#e53935;border-color:#e53935}.btn-error.btn-ghost{border-color:#f44336;color:#f44336}.btn-error.btn-ghost:focus,.btn-error.btn-ghost:hover{border-color:#d32f2f;color:#d32f2f;z-index:2}.btn-warning{color:#fff;background-color:#ff9800;border:1px solid #ff9800}.btn-warning:focus:not(.btn-ghost),.btn-warning:hover{background-color:#fb8c00;border-color:#fb8c00}.btn-warning.btn-ghost{border-color:#ff9800;color:#ff9800}.btn-warning.btn-ghost:focus,.btn-warning.btn-ghost:hover{border-color:#f57c00;color:#f57c00;z-index:2}.btn-info{color:#fff;background-color:#00bcd4;border:1px solid #00bcd4}.btn-info:focus:not(.btn-ghost),.btn-info:hover{background-color:#00acc1;border-color:#00acc1}.btn-info.btn-ghost{border-color:#00bcd4;color:#00bcd4}.btn-info.btn-ghost:focus,.btn-info.btn-ghost:hover{border-color:#0097a7;color:#0097a7;z-index:2}.btn-primary{color:#fff;background-color:#2196f3;border:1px solid #2196f3}.btn-primary:focus:not(.btn-ghost),.btn-primary:hover{background-color:#1e88e5;border-color:#1e88e5}.btn-primary.btn-ghost{border-color:#2196f3;color:#2196f3}.btn-primary.btn-ghost:focus,.btn-primary.btn-ghost:hover{border-color:#1976d2;color:#1976d2;z-index:2}.btn-group{overflow:auto}.btn-group .btn{float:left}.btn-group .btn-ghost:not(:first-child){margin-left:-1px}.card{border:1px solid #ccc}.card .card-header{color:#333;text-align:center;background-color:#ddd;padding:.5rem 0}.alert{color:#ccc;padding:1rem;border:1px solid #ccc;margin-bottom:1.75rem}.alert-success{color:#4caf50;border-color:#4caf50}.alert-error{color:#f44336;border-color:#f44336}.alert-info{color:#00bcd4;border-color:#00bcd4}.alert-warning{color:#ff9800;border-color:#ff9800}.media:not(:last-child){margin-bottom:1.25rem}.media-left{padding-right:1rem}.media-left,.media-right{display:table-cell;vertical-align:top}.media-right{padding-left:1rem}.media-body{display:table-cell;vertical-align:top}.media-heading{font-size:1.16667rem;font-weight:700}.media-content{margin-top:.3rem}.avatarholder,.placeholder{background-color:#f0f0f0;text-align:center;color:#b9b9b9;font-size:1rem;border:1px solid #f0f0f0}.avatarholder{width:48px;height:48px;line-height:46px;font-size:2rem;background-size:cover;background-position:50%;background-repeat:no-repeat}.avatarholder.rounded{border-radius:33px}.loading{display:inline-block;content:'&nbsp;';height:20px;width:20px;margin:0 .5rem;animation:a .6s infinite linear;border:2px solid #e91e63;border-right-color:transparent;border-radius:50%}.btn .loading{margin-bottom:0;width:14px;height:14px}.btn div.loading{float:left}.alert .loading{margin-bottom:-5px}@keyframes a{0%{transform:rotate(0deg)}to{transform:rotate(1turn)}}.menu{width:100%}.menu .menu-item{display:block;color:#616161;border-color:#616161}.menu .menu-item.active,.menu .menu-item:hover{color:#000;border-color:#000;background-color:transparent}@media screen and (max-width:768px){.form-group label{display:block;border-bottom:none;width:100%}.form-group.form-textarea label:after{display:none}.form-control{width:100%}textarea.form-control{border-left:none;padding:.5rem 0}pre::-webkit-scrollbar{height:3px}}@media screen and (max-width:480px){.form{width:100%}}
    </style>
    <style>
    #traces {max-height: 270px;overflow: hidden; }
    #traces.active {max-height: inherit;}
    #viewmore { margin-bottom: 10px; }
    </style>
    <script>
    /*!
    * clipboard.js v1.7.1
    * https://zenorocha.github.io/clipboard.js
    *
    * Licensed MIT © Zeno Rocha
    */
   !function(t){if("object"==typeof exports&&"undefined"!=typeof module)module.exports=t();else if("function"==typeof define&&define.amd)define([],t);else{var e;e="undefined"!=typeof window?window:"undefined"!=typeof global?global:"undefined"!=typeof self?self:this,e.Clipboard=t()}}(function(){var t,e,n;return function t(e,n,o){function i(a,c){if(!n[a]){if(!e[a]){var l="function"==typeof require&&require;if(!c&&l)return l(a,!0);if(r)return r(a,!0);var s=new Error("Cannot find module '"+a+"'");throw s.code="MODULE_NOT_FOUND",s}var u=n[a]={exports:{}};e[a][0].call(u.exports,function(t){var n=e[a][1][t];return i(n||t)},u,u.exports,t,e,n,o)}return n[a].exports}for(var r="function"==typeof require&&require,a=0;a<o.length;a++)i(o[a]);return i}({1:[function(t,e,n){function o(t,e){for(;t&&t.nodeType!==i;){if("function"==typeof t.matches&&t.matches(e))return t;t=t.parentNode}}var i=9;if("undefined"!=typeof Element&&!Element.prototype.matches){var r=Element.prototype;r.matches=r.matchesSelector||r.mozMatchesSelector||r.msMatchesSelector||r.oMatchesSelector||r.webkitMatchesSelector}e.exports=o},{}],2:[function(t,e,n){function o(t,e,n,o,r){var a=i.apply(this,arguments);return t.addEventListener(n,a,r),{destroy:function(){t.removeEventListener(n,a,r)}}}function i(t,e,n,o){return function(n){n.delegateTarget=r(n.target,e),n.delegateTarget&&o.call(t,n)}}var r=t("./closest");e.exports=o},{"./closest":1}],3:[function(t,e,n){n.node=function(t){return void 0!==t&&t instanceof HTMLElement&&1===t.nodeType},n.nodeList=function(t){var e=Object.prototype.toString.call(t);return void 0!==t&&("[object NodeList]"===e||"[object HTMLCollection]"===e)&&"length"in t&&(0===t.length||n.node(t[0]))},n.string=function(t){return"string"==typeof t||t instanceof String},n.fn=function(t){return"[object Function]"===Object.prototype.toString.call(t)}},{}],4:[function(t,e,n){function o(t,e,n){if(!t&&!e&&!n)throw new Error("Missing required arguments");if(!c.string(e))throw new TypeError("Second argument must be a String");if(!c.fn(n))throw new TypeError("Third argument must be a Function");if(c.node(t))return i(t,e,n);if(c.nodeList(t))return r(t,e,n);if(c.string(t))return a(t,e,n);throw new TypeError("First argument must be a String, HTMLElement, HTMLCollection, or NodeList")}function i(t,e,n){return t.addEventListener(e,n),{destroy:function(){t.removeEventListener(e,n)}}}function r(t,e,n){return Array.prototype.forEach.call(t,function(t){t.addEventListener(e,n)}),{destroy:function(){Array.prototype.forEach.call(t,function(t){t.removeEventListener(e,n)})}}}function a(t,e,n){return l(document.body,t,e,n)}var c=t("./is"),l=t("delegate");e.exports=o},{"./is":3,delegate:2}],5:[function(t,e,n){function o(t){var e;if("SELECT"===t.nodeName)t.focus(),e=t.value;else if("INPUT"===t.nodeName||"TEXTAREA"===t.nodeName){var n=t.hasAttribute("readonly");n||t.setAttribute("readonly",""),t.select(),t.setSelectionRange(0,t.value.length),n||t.removeAttribute("readonly"),e=t.value}else{t.hasAttribute("contenteditable")&&t.focus();var o=window.getSelection(),i=document.createRange();i.selectNodeContents(t),o.removeAllRanges(),o.addRange(i),e=o.toString()}return e}e.exports=o},{}],6:[function(t,e,n){function o(){}o.prototype={on:function(t,e,n){var o=this.e||(this.e={});return(o[t]||(o[t]=[])).push({fn:e,ctx:n}),this},once:function(t,e,n){function o(){i.off(t,o),e.apply(n,arguments)}var i=this;return o._=e,this.on(t,o,n)},emit:function(t){var e=[].slice.call(arguments,1),n=((this.e||(this.e={}))[t]||[]).slice(),o=0,i=n.length;for(o;o<i;o++)n[o].fn.apply(n[o].ctx,e);return this},off:function(t,e){var n=this.e||(this.e={}),o=n[t],i=[];if(o&&e)for(var r=0,a=o.length;r<a;r++)o[r].fn!==e&&o[r].fn._!==e&&i.push(o[r]);return i.length?n[t]=i:delete n[t],this}},e.exports=o},{}],7:[function(e,n,o){!function(i,r){if("function"==typeof t&&t.amd)t(["module","select"],r);else if(void 0!==o)r(n,e("select"));else{var a={exports:{}};r(a,i.select),i.clipboardAction=a.exports}}(this,function(t,e){"use strict";function n(t){return t&&t.__esModule?t:{default:t}}function o(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}var i=n(e),r="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},a=function(){function t(t,e){for(var n=0;n<e.length;n++){var o=e[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(t,o.key,o)}}return function(e,n,o){return n&&t(e.prototype,n),o&&t(e,o),e}}(),c=function(){function t(e){o(this,t),this.resolveOptions(e),this.initSelection()}return a(t,[{key:"resolveOptions",value:function t(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};this.action=e.action,this.container=e.container,this.emitter=e.emitter,this.target=e.target,this.text=e.text,this.trigger=e.trigger,this.selectedText=""}},{key:"initSelection",value:function t(){this.text?this.selectFake():this.target&&this.selectTarget()}},{key:"selectFake",value:function t(){var e=this,n="rtl"==document.documentElement.getAttribute("dir");this.removeFake(),this.fakeHandlerCallback=function(){return e.removeFake()},this.fakeHandler=this.container.addEventListener("click",this.fakeHandlerCallback)||!0,this.fakeElem=document.createElement("textarea"),this.fakeElem.style.fontSize="12pt",this.fakeElem.style.border="0",this.fakeElem.style.padding="0",this.fakeElem.style.margin="0",this.fakeElem.style.position="absolute",this.fakeElem.style[n?"right":"left"]="-9999px";var o=window.pageYOffset||document.documentElement.scrollTop;this.fakeElem.style.top=o+"px",this.fakeElem.setAttribute("readonly",""),this.fakeElem.value=this.text,this.container.appendChild(this.fakeElem),this.selectedText=(0,i.default)(this.fakeElem),this.copyText()}},{key:"removeFake",value:function t(){this.fakeHandler&&(this.container.removeEventListener("click",this.fakeHandlerCallback),this.fakeHandler=null,this.fakeHandlerCallback=null),this.fakeElem&&(this.container.removeChild(this.fakeElem),this.fakeElem=null)}},{key:"selectTarget",value:function t(){this.selectedText=(0,i.default)(this.target),this.copyText()}},{key:"copyText",value:function t(){var e=void 0;try{e=document.execCommand(this.action)}catch(t){e=!1}this.handleResult(e)}},{key:"handleResult",value:function t(e){this.emitter.emit(e?"success":"error",{action:this.action,text:this.selectedText,trigger:this.trigger,clearSelection:this.clearSelection.bind(this)})}},{key:"clearSelection",value:function t(){this.trigger&&this.trigger.focus(),window.getSelection().removeAllRanges()}},{key:"destroy",value:function t(){this.removeFake()}},{key:"action",set:function t(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"copy";if(this._action=e,"copy"!==this._action&&"cut"!==this._action)throw new Error('Invalid "action" value, use either "copy" or "cut"')},get:function t(){return this._action}},{key:"target",set:function t(e){if(void 0!==e){if(!e||"object"!==(void 0===e?"undefined":r(e))||1!==e.nodeType)throw new Error('Invalid "target" value, use a valid Element');if("copy"===this.action&&e.hasAttribute("disabled"))throw new Error('Invalid "target" attribute. Please use "readonly" instead of "disabled" attribute');if("cut"===this.action&&(e.hasAttribute("readonly")||e.hasAttribute("disabled")))throw new Error('Invalid "target" attribute. You can\'t cut text from elements with "readonly" or "disabled" attributes');this._target=e}},get:function t(){return this._target}}]),t}();t.exports=c})},{select:5}],8:[function(e,n,o){!function(i,r){if("function"==typeof t&&t.amd)t(["module","./clipboard-action","tiny-emitter","good-listener"],r);else if(void 0!==o)r(n,e("./clipboard-action"),e("tiny-emitter"),e("good-listener"));else{var a={exports:{}};r(a,i.clipboardAction,i.tinyEmitter,i.goodListener),i.clipboard=a.exports}}(this,function(t,e,n,o){"use strict";function i(t){return t&&t.__esModule?t:{default:t}}function r(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}function a(t,e){if(!t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!e||"object"!=typeof e&&"function"!=typeof e?t:e}function c(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function, not "+typeof e);t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,enumerable:!1,writable:!0,configurable:!0}}),e&&(Object.setPrototypeOf?Object.setPrototypeOf(t,e):t.__proto__=e)}function l(t,e){var n="data-clipboard-"+t;if(e.hasAttribute(n))return e.getAttribute(n)}var s=i(e),u=i(n),f=i(o),d="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},h=function(){function t(t,e){for(var n=0;n<e.length;n++){var o=e[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(t,o.key,o)}}return function(e,n,o){return n&&t(e.prototype,n),o&&t(e,o),e}}(),p=function(t){function e(t,n){r(this,e);var o=a(this,(e.__proto__||Object.getPrototypeOf(e)).call(this));return o.resolveOptions(n),o.listenClick(t),o}return c(e,t),h(e,[{key:"resolveOptions",value:function t(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};this.action="function"==typeof e.action?e.action:this.defaultAction,this.target="function"==typeof e.target?e.target:this.defaultTarget,this.text="function"==typeof e.text?e.text:this.defaultText,this.container="object"===d(e.container)?e.container:document.body}},{key:"listenClick",value:function t(e){var n=this;this.listener=(0,f.default)(e,"click",function(t){return n.onClick(t)})}},{key:"onClick",value:function t(e){var n=e.delegateTarget||e.currentTarget;this.clipboardAction&&(this.clipboardAction=null),this.clipboardAction=new s.default({action:this.action(n),target:this.target(n),text:this.text(n),container:this.container,trigger:n,emitter:this})}},{key:"defaultAction",value:function t(e){return l("action",e)}},{key:"defaultTarget",value:function t(e){var n=l("target",e);if(n)return document.querySelector(n)}},{key:"defaultText",value:function t(e){return l("text",e)}},{key:"destroy",value:function t(){this.listener.destroy(),this.clipboardAction&&(this.clipboardAction.destroy(),this.clipboardAction=null)}}],[{key:"isSupported",value:function t(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:["copy","cut"],n="string"==typeof e?[e]:e,o=!!document.queryCommandSupported;return n.forEach(function(t){o=o&&!!document.queryCommandSupported(t)}),o}}]),e}(u.default);t.exports=p})},{"./clipboard-action":7,"good-listener":4,"tiny-emitter":6}]},{},[8])(8)});
   </script>
    <body class="hack">
        <div class="main container">
            <h1><a target="_blank" href="https://encrypted.google.com/search?q={{q}}">{{msg}}</a></h1>
            <div class="alert alert-error">{{description}}</div>
            <pre>{{error_code}}</pre>
            <h2>Stacktraces</h2>
            <div id="traces">{{traces}}</div>
            <p align="right"><a id="viewmore">View more</a></p>
            <h2>HTTP Request</h2>
            <pre>{{http_request}}</pre>
            <pre>{{http_payload}}</pre>
        </div>
        <script>
            var cb = new Clipboard('.clipboard');
            document.getElementById('viewmore').onclick = function () {
                document.getElementById('traces').classList.add('active');
                this.remove();
            }
        </script>
    </body>
</html>
EOF;
    }
}
