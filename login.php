<style>
    /*
     * Licensed to Jasig under one or more contributor license
     * agreements. See the NOTICE file distributed with this work
     * for additional information regarding copyright ownership.
     * Jasig licenses this file to you under the Apache License,
     * Version 2.0 (the "License"); you may not use this file
     * except in compliance with the License.  You may obtain a
     * copy of the License at the following location:
     *
     *   http://www.apache.org/licenses/LICENSE-2.0
     *
     * Unless required by applicable law or agreed to in writing,
     * software distributed under the License is distributed on an
     * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
     * KIND, either express or implied.  See the License for the
     * specific language governing permissions and limitations
     * under the License.
     */

    /* reset */
    html, body, div, span, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, abbr, address, cite, code, del, dfn, em, img, ins, kbd, q, samp, small, strong, sub, sup, var, b, i, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td, article, aside, canvas, details, figcaption, figure, footer, header, hgroup, menu, nav, section, summary, time, mark, audio, video {
        margin: 0;
        padding: 0;
        border: 0;
        outline: 0;
        font-size: 100%;
        vertical-align: baseline;
        background: transparent;
    }

    body {
        line-height: 1;
    }

    article, aside, details, figcaption, figure, footer, header, hgroup, menu, nav, section {
        display: block;
    }

    nav ul {
        list-style: none;
    }

    blockquote, q {
        quotes: none;
    }

    blockquote:before, blockquote:after, q:before, q:after {
        content: '';
        content: none;
    }

    a {
        margin: 0;
        padding: 0;
        font-size: 100%;
        vertical-align: baseline;
        background: transparent;
    }

    /* change colours to suit your needs */
    ins {
        background-color: #ff9;
        color: #000;
        text-decoration: none;
    }

    /* change colours to suit your needs */
    mark {
        background-color: #ff9;
        color: #000;
        font-style: italic;
        font-weight: bold;
    }

    del {
        text-decoration: line-through;
    }

    abbr[title], dfn[title] {
        border-bottom: 1px dotted;
        cursor: help;
    }

    table {
        border-collapse: collapse;
        border-spacing: 0;
    }

    /* change border colour to suit your needs */
    hr {
        display: block;
        height: 1px;
        border: 0;
        border-top: 1px solid #cccccc;
        margin: 1em 0;
        padding: 0;
    }

    input, select {
        vertical-align: middle;
    }

    /* general page */
    body {
        font-family: Verdana, sans-serif;
        font-size: 11px;
        line-height: 1.4em;
    }

    #cas {
        background: blank;
        max-width: 750px;
    }

    #cas .flc-screenNavigator-view-container {
        width: 95%;
        margin: 0 auto;
    }

    #cas .flc-screenNavigator-view-container #header, #cas .flc-screenNavigator-view-container #content {
        background: #fff;
        -webkit-box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);
        box-shadow: 0px 0px 0px 0px rgba(0, 0, 0, 0.1);
    }

    #cas .flc-screenNavigator-view-container #content {
        -webkit-border-bottom-right-radius: 4px;
        -webkit-border-bottom-left-radius: 4px;
        -moz-border-radius-bottomright: 4px;
        -moz-border-radius-bottomleft: 4px;
        border-bottom-right-radius: 4px;
        border-bottom-left-radius: 4px;
    }

    @media only screen and (max-width: 960px) {
        #cas .flc-screenNavigator-view-container {
            width: 100%;
        }

        #cas .flc-screenNavigator-view-container #content {
            -webkit-border-bottom-right-radius: 0px;
            -webkit-border-bottom-left-radius: 0px;
            -moz-border-radius-bottomright: 0px;
            -moz-border-radius-bottomleft: 0px;
            border-bottom-right-radius: 0px;
            border-bottom-left-radius: 0px;
        }
    }

    /* header */
    #cas #header {
        padding-top: 10px;
    }

    #cas #header #company-name {
        width: 118px;
        height: 31px;
        text-indent: -999em;
        background: url(../images/ja-sig-logo.gif) no-repeat;
        margin: 0 0 10px 10px;
    }

    #cas #header #app-name {
        border-radius: 5px;
        background: #CCC;
        color: white;
        padding: 1.4em 1.4em;
        font-size: 2em;
        font-weight: normal;
    }

    /* content */
    #cas #content {
        padding-top: 10px;
        overflow: hidden;
    }

    #cas #content #msg {
        padding: 20px;
        margin-bottom: 10px;
    }

    #cas #content #msg h2 {
        font-size: 1.4em;
        margin-bottom: 0.5em;
    }

    #cas #content #msg.errors {
        border: 1px dotted #BB0000;
        color: #BB0000;
        padding-left: 100px;
        background: url(../images/error.gif) no-repeat 20px center;
    }

    #cas #content #msg.success {
        border: 1px dotted #390;
        color: #390;
        padding-left: 100px;
        background: url(../images/confirm.gif) no-repeat 20px center;
    }

    #cas #content #msg.info {
        border: 1px dotted #008;
        color: #008;
        padding-left: 100px;
        background: url(../images/info.gif) no-repeat 20px center;
    }

    #cas #content #login {
        width: 320px;
        float: left;
        margin-right: 20px;
    }

    #cas #content #login #fm1 {
        padding: 20px;
        background: #eee;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
    }

    #cas #content #login #fm1 h2 {
        font-size: 1.4em;
        font-weight: normal;
        padding-bottom: 10px;
        margin-bottom: 10px;
        border-bottom: 1px solid #DDDDDD;
    }

    #cas #content #login #fm1 .row {
        margin-bottom: 10px;
    }

    #cas #content #login #fm1 .row .fl-label {
        display: block;
        color: #777777;
    }

    #cas #content #login #fm1 .row input[type=text], #cas #content #login #fm1 .row input[type=password] {
        padding: 6px;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
        border: 1px solid #DDDDDD;
        background: #FFFFDD;
    }

    #cas #content #login #fm1 .row.check {
        padding-bottom: 10px;
        margin-bottom: 10px;
        border-bottom: 1px solid #DDDDDD;
        color: #777;
        font-size: 11px;
    }

    #cas #content #login #fm1 .row .btn-submit {
        border-width: 2px;
        padding: 3px;
        margin-right: 4px;
    }

    #cas #content #login #fm1 .row .btn-reset {
        border: 0;
        background: none;
        color: #777;
        text-transform: lowercase;
        border-left: 1px solid #ddd;
    }

    #cas #content #login #fm1 .row .btn-submit:hover, #cas #content #login #fm1 .row .btn-reset:hover {
        cursor: pointer;
    }

    #cas #content #sidebar {
        width: auto;
    }

    #cas #content #sidebar .sidebar-content {
        padding-left: 20px;
    }

    #cas #content #sidebar .sidebar-content p {
        margin-bottom: 1.4em;
    }

    #cas #content #sidebar .sidebar-content #list-languages ul {
        list-style: none;
    }

    #cas #content #sidebar .sidebar-content #list-languages ul li {
        display: inline-block;
        padding: 0px 10px;
        border-right: 1px solid #e2e2e2;
    }

    #cas #content #sidebar .sidebar-content #list-languages ul li:last-child {
        border: 0;
        line-height: 1.4em;
    }

    /* footer */
    #cas #footer {
        color: #999;
        margin: 20px 0;
    }

    /* < 960 */
    @media only screen and (max-width: 960px) {
        #cas #footer {
            padding-left: 10px;
        }
    }

    /* < 799 */
    @media only screen and (max-width: 799px) {
        #cas #header #app-name {
            font-size: 1em;
        }

        #cas #content #login {
            float: none;
            width: 100%;
        }

        #cas #content #login #fm1 .row .fl-label {
            margin-left: -10px;
        }

        #cas #content #login #fm1 .row input[type=text], #cas #content #login #fm1 .row input[type=password] {
            width: 100%;
            margin-left: -10px;
            padding: 10px;
        }

        #cas #content #login #fm1 .row .btn-submit {
            outline: none;
            -webkit-appearance: none;
            -webkit-border-radius: 0;
            border: 0;
            background: #CCC;
            color: white;
            font-weight: bold;
            width: 100%;
            padding: 10px 20px;
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
            border-radius: 3px;
        }

        #cas #content #login #fm1 .row .btn-reset {
            display: none;
        }

        #cas #content #sidebar {
            margin-top: 20px;
        }

        #cas #content #sidebar .sidebar-content {
            padding: 0;
        }
    }
</style>

<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <title>SACRE - Siron AML Consolidated Report</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
</head>
<body id="cas" class="fl-theme-iphone">
<div class="flc-screenNavigator-view-container">
    <div class="fl-screenNavigator-view">
        <div id="header" class="flc-screenNavigator-navbar fl-navbar fl-table">
            <h1 id="app-name" class="fl-table-cell" style="padding-top:20px">
                <img id="logo" src="img/logo.png" width="160" alt="Logo SG" style="vertical-align:middle"/>

                &nbsp;&nbsp;SACRE - Siron AML Consolidated Report</h1>
        </div>
        <div id="content" class="fl-screenNavigator-scroll-container">


            <div class="box fl-panel" id="login">
                <form id="fm1" action="connect.php" method="get">

                    <h2>Enter your login and your password.</h2>
                    <div class="row fl-controls-left">
                        <label for="username" class="fl-label"><span class="accesskey">L</span>ogin:</label>
                        <input id="username" name="login" class="required" tabindex="1" accesskey="l" type="text"
                               value="" size="25" autocomplete="false"/>

                    </div>
                    <div class="row fl-controls-left">
                        <label for="password" class="fl-label"><span class="accesskey">P</span>assword:</label>
                        <input id="password" name="motdepasse" class="required" tabindex="2" accesskey="p"
                               type="password" value="" size="25" autocomplete="off"/>
                    </div>
                    <div class="row btn-row">
                        <input type="hidden" name="lt" value="LT-14493-D1zYAKsmyxAOiKtetmedtdefxswt56"/>
                        <input type="hidden" name="execution" value="e2s1"/>
                        <input type="hidden" name="_eventId" value="submit"/>

                        <input class="btn-submit" name="submit" accesskey="s" value="Sign In" tabindex="4"
                               type="submit"/>
                    </div>
                </form>
            </div>
            <div id="sidebar">
                <div class="sidebar-content">
                    <p class="fl-panel fl-note fl-bevel-white fl-font-size-80">For security reasons, please sign out
                        when you have finished to use SACRE.</p>
                    <div id="list-languages" class="fl-panel">
                        <br/>
                        <a HREF="forget.php">Change your password</a>