<style>
    h1 { font-size:2em; }
    h2 { 
        margin: 0;
        margin-bottom: .8em;
        background-color: #BDC4E1;
        padding: 0.5em;
        border-radius: 4px;
        border: 1px solid #4F5B93;
        box-shadow: 0 0 2px rgba(0,0,0,.5);
    }
    h2:hover {
        background-color: hsla(228, 37%, 77%, 1);
        box-shadow: 0 0 4px rgba(0,0,0,.5);
        transition:all 0.2s;
    }
    h3 { margin-top:1.5em; margin-bottom: .5em; }
    section.content { max-width:1000px; margin: 80px auto; }
    section.content.top { margin-top:40px; text-align:center; }
    section img { max-width:100%; }
    section ul { margin-left: 2em; margin-bottom:1em; }
    section li { line-height:1.5em; }
    section code { 
        background-color: #E2E4EF;
        padding: 2px 8px;
        border-radius: 2px;
    }
    section pre code,
    pre[class*="language-"] {
        background-color: white;
        box-shadow: 0 0 10px 0 rgba(0,0,0,.5);
        display: block;
        padding: 30px;
        border-radius: 4px;
        max-width: 740px;
        margin: 30px auto;
        overflow: auto;
        font-size:12px;
    }
    @media (min-width:600px) {
        section pre code,
        pre[class*="language-"] { font-size:14px; }
    }
    pre[class*="language-"] { padding:20px; }
    pre[class*="language-"]>code { padding:0; margin:0; }
    .quick-tip {
        border: 1px solid hsla(23, 100%, 43%, 1);
        background-color: #FFAB76;
        background-image: linear-gradient(hsla(23, 100%, 63%, 1), #FFAB76);
        padding: 1em;
    }
    .quick-tip h3 { margin-top:0; }
</style>
<section class="content top">
    <?= $html ?>
</section>
<script>
    // Validates with [jshint]
    (function() {
        'use strict';
        document.addEventListener('DOMContentLoaded', function() {
            // Update Links to open in a new tab if they are not on the same site.
            var pageHost = window.location.hostname;
            var links = document.querySelectorAll('section.content a');
            Array.prototype.forEach.call(links, function(link) {
                if (link.hostname !== pageHost) {
                    link.target = '_blank';
                }
            });

            // Update Code Blocks for Syntax Highlighting
            var codeBlocks = document.querySelectorAll('section.content pre code');
            Array.prototype.forEach.call(codeBlocks, function(code) {
                if (code.className === '') {
                    var text = code.textContent;
                    if (text.indexOf('sudo ') !== -1 ||
                        text.indexOf('xfs_mkfile ') !== -1 ||
                        text.indexOf('/usr/local/etc/apache24/') !== -1
                    ) {
                        code.className = 'language-bash';
                        // Update Width for specific content
                        if (window.location.href.indexOf('file-encryption-bash') !== -1) {
                            code.style.maxWidth = '100%';
                            code.style.paddingBottom = '1em';
                            code.parentNode.style.maxWidth = '100%';
                        }
                    } else if (text.indexOf('?php') !== -1) {
                        code.className = 'language-php';
                    }
                }
            });
            if (window.Prism !== undefined) {
                Prism.highlightAll();
            }
        });
    })();
</script>
