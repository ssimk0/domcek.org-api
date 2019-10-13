<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <style>
        * {
            box-sizing: border-box;
            padding: 0;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            text-rendering: optimizeLegibility;
        }

        .page {
            position: relative;
            /*page-break-after: always;*/
            margin: 0;
            padding: 0;
            width: 29.7cm;
            height: 21cm;
            overflow: hidden;
            background-image: url("{{$image}}");
            background-size: cover;
            background-repeat: no-repeat, no-repeat;
        }

        .group {
            position: absolute;
            right: 3cm;
            bottom: 0;
        }

        .page-back {
            position: relative;
            margin: 0;
            padding: 0;
            width: 29.7cm;
            height: 21cm;
            overflow: hidden;
            background-image: url("{{$image_back}}");
            background-size: cover;
            background-repeat: no-repeat, no-repeat;
        }

        .page-volunter {
            position: relative;
            margin: 0;
            padding: 0;
            width: 29.7cm;
            height: 21cm;
            overflow: hidden;
            background-image: url("{{$image_volunteer}}");
            background-size: cover;
            background-repeat: no-repeat, no-repeat;
        }

        .page-volunter-back {
            position: relative;
            margin: 0;
            padding: 0;
            width: 29.7cm;
            height: 21cm;
            overflow: hidden;
            background-image: url("{{$image_volunteer_back}}");
            background-size: cover;
            background-repeat: no-repeat, no-repeat;
        }

        .nameplate-wrapper {
            width: 33.33%;
            height: 6.5cm;
            font-size: 24px;
            position: relative;
            float: left;
        }

        .name {
            position: absolute;
            top: 5cm;
            left: 1.4cm;
            width: 100%;
        }

        .page-divider .nameplate-wrapper:first-child .name {
            top: 5.6cm;
        }


        .page-divider .nameplate-wrapper:nth-child(2) .name {
            top: 5.6cm;
        }

        .page-divider .nameplate-wrapper:nth-child(3) .name {
            top: 5.6cm;
        }

        .page-divider .nameplate-wrapper:nth-child(4) .name {
            top: 5.4cm;
        }

        .page-divider .nameplate-wrapper:nth-child(5) .name {
            top: 5.4cm;
        }

        .page-divider .nameplate-wrapper:nth-child(6) .name {
            top: 5.4cm;
        }

        .vol {
            position: absolute;
            bottom: -8px;
            right: 5px;
            font-size: 12px;
        }

        .page-divider .nameplate-wrapper:first-child .vol {
            bottom: -28px;
            right: 10px;
        }

        .page-divider .nameplate-wrapper:nth-child(2) .vol {
            bottom: -28px;
        }

        .page-divider .nameplate-wrapper:nth-child(3) .vol {
            bottom: -28px;
        }

        .page-divider .nameplate-wrapper:nth-child(4) .vol {
            bottom: -18px;
            right: 10px;
        }

        .page-divider .nameplate-wrapper:nth-child(7) .vol {
            right: 10px;
        }

        .page-divider .nameplate-wrapper:nth-child(5) .vol {
            bottom: -18px;
        }

        .page-divider .nameplate-wrapper:nth-child(6) .vol {
            bottom: -18px;
        }

        .page-divider {
            margin: 1.3cm 1.3cm;
        }

        /*! normalize.css v8.0.1 | MIT License | github.com/necolas/normalize.css */

        /* Document
           ========================================================================== */

        /**
         * 1. Correct the line height in all browsers.
         * 2. Prevent adjustments of font size after orientation changes in iOS.
         */

        html {
            line-height: 1.15; /* 1 */
            -webkit-text-size-adjust: 100%; /* 2 */
        }

        /* Sections
           ========================================================================== */

        /**
         * Remove the margin in all browsers.
         */

        body {
            margin: 0;
        }

        /**
         * Render the `main` element consistently in IE.
         */

        main {
            display: block;
        }

        /**
         * Correct the font size and margin on `h1` elements within `section` and
         * `article` contexts in Chrome, Firefox, and Safari.
         */

        h1 {
            font-size: 2em;
            margin: 0.67em 0;
        }

        /* Grouping content
           ========================================================================== */

        /**
         * 1. Add the correct box sizing in Firefox.
         * 2. Show the overflow in Edge and IE.
         */

        hr {
            box-sizing: content-box; /* 1 */
            height: 0; /* 1 */
            overflow: visible; /* 2 */
        }

        /**
         * 1. Correct the inheritance and scaling of font size in all browsers.
         * 2. Correct the odd `em` font sizing in all browsers.
         */

        pre {
            font-family: monospace, monospace; /* 1 */
            font-size: 1em; /* 2 */
        }

        /* Text-level semantics
           ========================================================================== */

        /**
         * Remove the gray background on active links in IE 10.
         */

        a {
            background-color: transparent;
        }

        /**
         * 1. Remove the bottom border in Chrome 57-
         * 2. Add the correct text decoration in Chrome, Edge, IE, Opera, and Safari.
         */

        abbr[title] {
            border-bottom: none; /* 1 */
            text-decoration: underline; /* 2 */
            text-decoration: underline dotted; /* 2 */
        }

        .clearfix::after {
            display: block;
            content: "";
            clear: both;
        }

        /**
         * Add the correct font weight in Chrome, Edge, and Safari.
         */

        b,
        strong {
            font-weight: bolder;
        }

        /**
         * 1. Correct the inheritance and scaling of font size in all browsers.
         * 2. Correct the odd `em` font sizing in all browsers.
         */

        code,
        kbd,
        samp {
            font-family: monospace, monospace; /* 1 */
            font-size: 1em; /* 2 */
        }

        /**
         * Add the correct font size in all browsers.
         */

        small {
            font-size: 80%;
        }

        /**
         * Prevent `sub` and `sup` elements from affecting the line height in
         * all browsers.
         */

        sub,
        sup {
            font-size: 75%;
            line-height: 0;
            position: relative;
            vertical-align: baseline;
        }

        sub {
            bottom: -0.25em;
        }

        sup {
            top: -0.5em;
        }

        /* Embedded content
           ========================================================================== */

        /**
         * Remove the border on images inside links in IE 10.
         */

        img {
            border-style: none;
        }

        /* Forms
           ========================================================================== */

        /**
         * 1. Change the font styles in all browsers.
         * 2. Remove the margin in Firefox and Safari.
         */

        button,
        input,
        optgroup,
        select,
        textarea {
            font-family: inherit; /* 1 */
            font-size: 100%; /* 1 */
            line-height: 1.15; /* 1 */
            margin: 0; /* 2 */
        }

        /**
         * Show the overflow in IE.
         * 1. Show the overflow in Edge.
         */

        button,
        input { /* 1 */
            overflow: visible;
        }

        /**
         * Remove the inheritance of text transform in Edge, Firefox, and IE.
         * 1. Remove the inheritance of text transform in Firefox.
         */

        button,
        select { /* 1 */
            text-transform: none;
        }

        /**
         * Correct the inability to style clickable types in iOS and Safari.
         */

        button,
        [type="button"],
        [type="reset"],
        [type="submit"] {
            -webkit-appearance: button;
        }

        /**
         * Remove the inner border and padding in Firefox.
         */

        button::-moz-focus-inner,
        [type="button"]::-moz-focus-inner,
        [type="reset"]::-moz-focus-inner,
        [type="submit"]::-moz-focus-inner {
            border-style: none;
            padding: 0;
        }

        /**
         * Restore the focus styles unset by the previous rule.
         */

        button:-moz-focusring,
        [type="button"]:-moz-focusring,
        [type="reset"]:-moz-focusring,
        [type="submit"]:-moz-focusring {
            outline: 1px dotted ButtonText;
        }

        /**
         * Correct the padding in Firefox.
         */

        fieldset {
            padding: 0.35em 0.75em 0.625em;
        }

        /**
         * 1. Correct the text wrapping in Edge and IE.
         * 2. Correct the color inheritance from `fieldset` elements in IE.
         * 3. Remove the padding so developers are not caught out when they zero out
         *    `fieldset` elements in all browsers.
         */

        legend {
            box-sizing: border-box; /* 1 */
            color: inherit; /* 2 */
            display: table; /* 1 */
            max-width: 100%; /* 1 */
            padding: 0; /* 3 */
            white-space: normal; /* 1 */
        }

        /**
         * Add the correct vertical alignment in Chrome, Firefox, and Opera.
         */

        progress {
            vertical-align: baseline;
        }

        /**
         * Remove the default vertical scrollbar in IE 10+.
         */

        textarea {
            overflow: auto;
        }

        /**
         * 1. Add the correct box sizing in IE 10.
         * 2. Remove the padding in IE 10.
         */

        [type="checkbox"],
        [type="radio"] {
            box-sizing: border-box; /* 1 */
            padding: 0; /* 2 */
        }

        /**
         * Correct the cursor style of increment and decrement buttons in Chrome.
         */

        [type="number"]::-webkit-inner-spin-button,
        [type="number"]::-webkit-outer-spin-button {
            height: auto;
        }

        /**
         * 1. Correct the odd appearance in Chrome and Safari.
         * 2. Correct the outline style in Safari.
         */

        [type="search"] {
            -webkit-appearance: textfield; /* 1 */
            outline-offset: -2px; /* 2 */
        }

        /**
         * Remove the inner padding in Chrome and Safari on macOS.
         */

        [type="search"]::-webkit-search-decoration {
            -webkit-appearance: none;
        }

        /**
         * 1. Correct the inability to style clickable types in iOS and Safari.
         * 2. Change font properties to `inherit` in Safari.
         */

        ::-webkit-file-upload-button {
            -webkit-appearance: button; /* 1 */
            font: inherit; /* 2 */
        }

        /* Interactive
           ========================================================================== */

        /*
         * Add the correct display in Edge, IE 10+, and Firefox.
         */

        details {
            display: block;
        }

        /*
         * Add the correct display in all browsers.
         */

        summary {
            display: list-item;
        }

        /* Misc
           ========================================================================== */

        /**
         * Add the correct display in IE 10+.
         */

        template {
            display: none;
        }

        /**
         * Add the correct display in IE 10.
         */

        [hidden] {
            display: none;
        }
    </style>
</head>
<body>

@foreach($participants as $participant_page)
    <div class="page">
        <div class="page-divider clearfix">
            @foreach ($participant_page as $participant)
                <div class="nameplate-wrapper">
                    <div class="nameplate">
                        <div class="name">
                            {{$participant->nick ?? $participant->first_name}} <div class="group">Sk: {{$participant->group_name}}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="page-back"></div>
@endforeach
@foreach($volunteers as $volunteer_page)
    <div class="page-volunter">
        <div class="page-divider clearfix">
            @foreach ($volunteer_page as $participant)
                <div class="nameplate-wrapper">
                    <div class="nameplate">
                        <div class="name">
                            {{$participant->nick ?? $participant->first_name}} <div class="group">Sk: {{$participant->group_name}}</div>
                        </div>
                        <small class="vol"> {{$participant->name}}</small>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="page-volunter-back"></div>
@endforeach

</body>
</html>