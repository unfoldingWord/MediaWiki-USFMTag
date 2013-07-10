<?php
/**
 * Copyright (c) 2011 Rusmin Soetjipto
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

# Credits
$wgExtensionCredits['parserhook'][] = array(
  'name' => 'USFM Tag Extension',
  'author' => 'Rusmin Soetjipto',
  'url' => '',
  'version' => '1.0',
  'description' => 'Parses Unified Scripture Format Markers'
);

# Provide file location for helper functions and UsfmTag class
$wgAutoloadClasses['UsfmParagraphState'] = dirname(__FILE__) . '/UsfmParagraphState.php';
$wgAutoloadClasses['UsfmText'] = dirname(__FILE__) . '/UsfmText.php';
$wgAutoloadClasses['UsfmTagDecoder'] = dirname(__FILE__) . '/UsfmTagDecoder.php';

# Setup hook for initialization function
if (defined('MW_SUPPORTS_PARSERFIRSTCALLINIT')) {
  $wgHooks['ParserFirstCallInit'][] = 'wfUsfmTagInit';
} else {
  $wgExtensionFunctions[] = 'wfUsfmTagInit';
}

# The (callback) initialization function
function wfUsfmTagInit($parser) {
  $parser->setHook('usfm', 'wfUsfmTagRender');
  return true;
}

function wfUsfmTagRender($input, $argv, $parser, $frame) {
  $usfm_tag_decoder = new UsfmTagDecoder($parser);
  return $usfm_tag_decoder->decode($input);
}