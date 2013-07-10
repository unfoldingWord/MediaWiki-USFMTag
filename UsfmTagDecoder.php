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

class UsfmTagDecoder {
  private $usfm_text;

  const BASE_LEVEL = 0;
  const IS_ITALIC = 1;
  const ALIGNMENT = 2;
  const PARAGRAPH_CLASS = 3;
  private $paragraph_settings = array (
    // Chapter and Verses
    "cd"  => array (0, True, 'left', 'usfm-desc'),
    // Titles, Headings, and Label
    "d"   => array (0, True, 'left', 'usfm-desc'),
    "sp"  => array (0, True, 'left', 'usfm-flush'),
    // Paragraph and Poetry (w/o level parameter)
    "cls" => array (0, False, 'right', 'usfm-right'),
    "m"   => array (0, False, 'justify', 'usfm-flush'),
    "mi"  => array (1, False, 'justify', 'usfm-flush'),
    "p"   => array (0, False, 'justify', 'usfm-indent'),
    "pc"  => array (0, False, 'center', 'usfm-center'),
    "pm"  => array (1, False, 'justify', 'usfm-indent'),
    "pmc" => array (1, False, 'justify', 'usfm-flush'),
    "pmo" => array (1, False, 'justify', 'usfm-flush'),
    "pmr" => array (1, False, 'right', 'usfm-right'),
    "pr"  => array (0, False, 'right', 'usfm-right'),
    "qa"  => array (1, True, 'center', 'usfm-center'),
    "qc"  => array (1, False, 'center', 'usfm-center'),
    "qr"  => array (1, True, 'right', 'usfm-right'),
    // Paragraph and Poetry (w/ level parameter)
    "ph"  => array (0, False, 'justify', 'usfm-hanging'),
    "pi"  => array (1, False, 'justify', 'usfm-indent'),
    "q"   => array (2, False, 'left', 'usfm-hanging'),
    "qm"  => array (1, True, 'left', 'usfm-hanging')
  );  
  private $title_settings = array (
    // Titles, Headings, and Label (w/o level parameter)
    "mr"  => array (2, True),
    "r"   => array (5, True),
    "sr"  => array (5, True),
    // Titles, Headings, and Label (w/ level parameter)
    "mt"  => array (1, False),
    "mte" => array (1, False),
    "ms"  => array (2, False),
    "s"   => array (3, False),    
  );
  
  const IF_NORMAL = 0;
  const IF_ITALIC_PARAGRAPH = 1;
  private $substitution_table = array (
    // Titles, Headings, and Labels
    "rq"   => array ("\n<span class='usfm-selah'><i class='usfm'>"),
    "rq*"  => array ("</i></span>\n"),
    // Paragraphs and Poetry
    "b"    => array ("\n<br>"),
    "qac"  => array ("<big class='usfm-qac'>"),
    "qac*" => array ("</big>"),
    "qs"   => array ("\n<span class='usfm-selah'><i class='usfm'>"),
    "qs*"  => array ("</i></span>\n"),
    // Cross Reference
    "x"    => array ("\n<span class='usfm-selah'>"),
    "x*"   => array ("</span>\n"),
    // Other
    "add"  => array ("<i class='usfm'>[", "</i>["),
    "add*" => array ("]</i>", "]<i class='usfm'>"),
    "bk"   => array ("<i class='usfm'>&quot;", "</i>&quot;"),
    "bk*"  => array ("&quot;</i>", "&quot;<i class='usfm'>"),
    "dc"   => array ("<code class='usfm'>"),
    "dc*"  => array ("</code>"),
    "k"    => array ("<code class='usfm'>"),
    "k*"   => array ("</code>"),
    "lit"  => array ("\n<span class='usfm-selah'><b class='usfm'>"),
    "lit*" => array ("</b></span>\n"),
    "ord"  => array ("<sup class='usfm'>"),
    "ord*" => array ("</sup>"),
    "pn*"  => array (""),
    "qt"   => array ("<i class='usfm'>", "</i>"),
    "qt*"  => array ("</i>", "<i class='usfm'>"),
    "sig"  => array ("<i class='usfm'>", "</i>"),
    "sig*" => array ("</i>", "<i class='usfm'>"),
    "sls"  => array ("<i class='usfm'>", "</i>"),
    "sls*" => array ("</i>", "<i class='usfm'>"),
    "tl"   => array ("<i class='usfm'>", "</i>"),
    "tl*"  => array ("</i>", "<i class='usfm'>"),
    "wj"   => array ("<font color='red'"),
    "wj*"  => array ("</font>"),
    "em"   => array ("<i class='usfm'>", "</i>"),
    "em*"  => array ("</i>", "<i class='usfm'>"),
    "bd"   => array ("<b class='usfm'>"),
    "bd*"  => array ("</b>"),
    "it"   => array ("<i class='usfm'>", "</i>"),
    "it*"  => array ("</i>", "<i class='usfm'>"),
    "bdit" => array ("<i class='usfm'><b class='usfm'>", "</i></b>"),
    "bdit*"=> array ("</b></i>", "<b class='usfm'><i class='usfm'>"),
    "no"   => array ("", "</i>"),
    "no*"  => array ("", "<i class='usfm'>"),
    "sc"   => array ("<small class='usfm'>"),
    "sc*"  => array ("</small>"),
    "\\"   => array ("<br>")
  );
  
  const BEFORE_REMAINING = 0;
  const AFTER_REMAINING = 1;
  private $footnote_substitution_table = array (
    // Footnotes
    "fdc" => array ("<i class='usfm'>", ""),
    "fdc*"=> array ("</i>", ""),
    "fl"  => array ("<u class='usfm'>", "</u>"),
    "fm"  => array ("<code class='usfm'>", ""),
    "fm*" => array ("</code>", ""),
    "fp"  => array ("</p>\n<p class='usfm-footer'>", ""),
    "fq"  => array ("<i class='usfm'>", "</i>"),
    "fqa" => array ("<i class='usfm'>", "</i>"),
    "fr"  => array ("<b class='usfm'>", "</b>"),
    "fv"  => array (" <span class='usfm-v'>", "</span>"),
    // Cross References
    "xdc" => array ("<b class='usfm'>", ""),
    "xdc*"=> array ("</b>", ""),
    "xnt" => array ("<b class='usfm'>", ""),
    "xnt*"=> array ("</b>", ""),
    "xot" => array ("<b class='usfm'>", ""),
    "xot*"=> array ("</b>", ""),
    "xo"  => array ("<b class='usfm'>", "</b>"),
    "xq"  => array ("<i class='usfm'>", "</i>")
  );
  
  const MAX_SELAH_CROSS_REFERENCES_LENGTH = 10;
  
  public function __construct($parser) {
    $this->usfm_text = new UsfmText($parser);
  }

  public function decode($raw_text) {
    //wfDebug("Internal encoding: ".mb_internal_encoding());
  	//wfDebug("UTF-8 compatible: ".mb_check_encoding($raw_text, "UTF-8"));
    //wfDebug("ISO-8859-1 compatible: ".mb_check_encoding($raw_text, "ISO-8859-1"));
    
  	$usfm_segments = explode("\\", $raw_text);
    
    for ($i=0; $i<sizeof($usfm_segments); $i++) {
      $remaining = strpbrk($usfm_segments[$i], " \n");
      if ($remaining === false) {
        $raw_command = $usfm_segments[$i];
        $remaining = '';
      } else {
        $raw_command = substr($usfm_segments[$i], 0,
                              strlen($usfm_segments[$i])-
                              strlen($remaining));
        $remaining = trim($remaining, " \n\t\r\0");
        if ( mb_substr($remaining, mb_strlen($remaining)-1, 1) != "\xA0" ) {
        	$remaining .= " ";
        }
      }
      if ($raw_command == '') {
      	continue;
      }
      $main_command_length = strcspn($raw_command, '0123456789');
      $command = substr($raw_command, 0, $main_command_length);
      if (strlen($raw_command) > $main_command_length) {
        $level = strval(substr($raw_command, $main_command_length));
      } else {
        $level = 1;
      }
      
      if (  ($command == 'h')  || (substr($command, 0, 2) == 'id') ||
            ($command == 'rem')  || ($command == 'sts') ||
            (substr($command, 0, 3) == 'toc')  )
      {
        $this->renderIdentification($command, $level, $remaining);

      } elseif (  (substr($command, 0, 1) == 'i') && 
                  (substr($command, 0, 2) <> 'it') ) 
      {
        $this->renderIntroduction($command, $level, $remaining);
      
      } elseif (  (substr($command, 0, 1) == 'm') && 
                  ($command <> 'm') && ($command <> 'mi')  ) 
      {
        $this->renderTitleOrHeadingOrLabel($command, $level, $remaining);
      
      } elseif (  (substr($command, 0, 1) == 's') &&
                  (substr($command, 0, 2) <> 'sc') &&
                  (substr($command, 0, 3) <> 'sig') &&
                  (substr($command, 0, 3) <> 'sls')  )
      {
        $this->renderTitleOrHeadingOrLabel($command, $level, $remaining);
      
      } elseif (  ($command == 'd') || (substr($command, 0, 1) == 'r')  ) {
        $this->renderTitleOrHeadingOrLabel($command, $level, $remaining);
      
      } elseif (  (substr($command, 0, 1) == 'c') || 
                  (substr($command, 0, 1) == 'v')  )
      {
        $this->renderChapterOrVerse($raw_command, $command, 
                                    $level, $remaining);
      
      } elseif (  (substr($command, 0, 1) == 'q') &&
                  (substr($command, 0, 2) <> 'qt')  )
      {
        $this->renderPoetry($command, $level, $remaining);
      
      } elseif (  (substr($command, 0, 1) == 'p')  && ($command <> 'pb') &&
                  (substr($command, 0, 2) <> 'pn') &&
                  (substr($command, 0, 3) <> 'pro')  )
      {
        $this->renderParagraph($command, $level, $remaining);
      
      } elseif (  ($command == 'b') || ($command == 'cls') ||
                  (substr($command, 0, 2) == 'li') || 
                  ($command == 'm') || ($command == 'mi') || 
                  ($command == 'nb')  )
      {
        $this->renderParagraph($command, $level, $remaining);
      
      } elseif (  (substr($command, 0, 1) == 't') &&
                  (substr($command, 0, 2) <> 'tl')  )
      {
        $this->renderTable($command, $level, $remaining);
      
      } elseif (  (substr($command, 0, 1) == 'f') &&
                  (substr($command, 0, 3) <> 'fig')  )
      {
        $this->renderFootnoteOrCrossReference($raw_command, $remaining);
        // located in UsfmTag.3.php
        
      } elseif (substr($command, 0, 1) == 'x') {
        $this->renderFootnoteOrCrossReference($raw_command, $remaining);
        // located in UsfmTag.3.php

      } else {
        $this->renderOther($raw_command, $remaining);
      } // if
    } // for ($i=0; $i<sizeof($usfm_segments); $i++)
    return $this->usfm_text->getAndClearHtmlText();
  }  

  protected function renderIdentification($command, $level, 
                                          $remaining)
  {
    $this->displayUnsupportedCommand($command, $level, $remaining);
  }
  
  protected function renderIntroduction($command, $level,
                                        $remaining)
  {
    $this->displayUnsupportedCommand($command, $level, $remaining);
  }
  
  protected function renderTitleOrHeadingOrLabel($command, $level,
                                                 $remaining) 
  {
    $this->renderGeneralCommand($command, $level, $remaining);  
  }
  
  protected function renderChapterOrVerse($raw_command, $command, 
                                          $level, $remaining)
  {
  	$remaining = trim($remaining, " ");
    if ( (substr($command, 0, 1) == 'v') &&
         (strlen($raw_command) == strlen($command)) ) {
      $level = $this->extractSubCommand($remaining);
    }
    switch ($command) {
    case 'c':
      $this->usfm_text->setChapterNumber($remaining);
      break;
    case 'ca':
      $this->usfm_text->setAlternateChapterNumber($remaining);
      break;
    case 'cl':
      $this->usfm_text->setChapterLabel($remaining);
      break;
    case 'cp':
      $this->usfm_text->setPublishedChapterNumber($remaining);
      break;
    case 'cd':
      $this->switchParagraph($command, $level);
      $this->usfm_text->printHtmlText($remaining);
      break;
    case 'v':
      $this->usfm_text->setVerseNumber($level);
      $this->usfm_text->printHtmlText($remaining);
      break;
    case 'va':
      $this->usfm_text->setAlternateVerseNumber($verse_number);
      break;
    case 'vp':
      $this->usfm_text->setPublishedChapterNumber($verse_number);
      break;
    default:
      $this->usfm_text->printHtmlText($remaining);
    }
  }

  protected function renderPoetry($command, $level, $remaining) {
    $this->renderGeneralCommand($command, $level, $remaining);
  }
  
  protected function renderParagraph($command, $level, $remaining) {
    switch ($command) {
      case 'nb':
        $this->usfm_text->flushPendingDropCapNumeral(True);
        $this->usfm_text->printHtmlText($remaining);
        break;
      case 'li':
        $this->usfm_text->switchListLevel($level);
        $this->usfm_text->printHtmlText("<li class='usfm'>".$remaining);
        break;
      default:
        $this->renderGeneralCommand($command, $level, $remaining);
    }
  }
  
  protected function renderTable($command, $level, $remaining) {
    switch ($command) {
    case 'tr':
      $this->usfm_text->flushPendingTableColumns();
      break;
    case 'th':
      $this->usfm_text->insertTableColumn(True, False, $remaining);
      break;
    case 'thr':
      $this->usfm_text->insertTableColumn(True, True, $remaining);
      break;
    case 'tc':
      $this->usfm_text->insertTableColumn(False, False, $remaining);
      break;
    case 'tcr':
      $this->usfm_text->insertTableColumn(False, True, $remaining);
    }
  }
    
  protected function renderFootnoteOrCrossReference($command, 
                                                    $remaining) 
  {
    switch ($command) {
    case 'x':
    case 'f':
    case 'fe':
      if (substr($remaining, 1, 1) == ' ') {
        $this->extractSubCommand($remaining);
      }
      if ( (mb_strlen($remaining) <= self::MAX_SELAH_CROSS_REFERENCES_LENGTH)
    	     && (strpos($remaining, ' ') !== False) && ($command = 'x') )
    	{
    	  $this->is_selah_cross_reference = True;
    	  $this->renderGeneralCommand($command, 1, $remaining);     	
    	} else {
        $this->is_selah_cross_reference = False;     
        $this->usfm_text->newFooterEntry();
        $this->usfm_text->printHtmlTextToFooter($remaining);
    	}
      break;
    case 'x*':
    case 'f*':
    case 'fe*':
    	if ($this->is_selah_cross_reference) {
    		$this->renderGeneralCommand($command, 1, $remaining);
    	} else {
        $this->usfm_text->closeFooterEntry();
        $this->usfm_text->printHtmlText($remaining);
    	}
    	break;
    case 'fk':
    case 'xk':
      $this->usfm_text
           ->printHtmlTextToFooter(netscapeCapitalize($remaining));
      break;
    default:
      if (array_key_exists($command, 
                           $this->footnote_substitution_table))
      {
        $setting = $this->footnote_substitution_table[$command];
        $remaining = $setting[self::BEFORE_REMAINING].$remaining.
                     $setting[self::AFTER_REMAINING];
      }
      $this->usfm_text->printHtmlTextToFooter($remaining);    
    }
  }

  protected function renderOther($command, $remaining) {
    switch ($command) {
    case 'nd':
      $this->usfm_text->printHtmlText(netscapeCapitalize($remaining));
      break;
    default:
      $this->renderGeneralCommand($command, 1, $remaining);
    }
  }
  
  protected function displayUnsupportedCommand($command, $level,
                                               $remaining)
  {
    if ($level > 1) {
    	$command = $command.$level;
    }
  	$this->usfm_text
         ->printHtmlText("<!-- usfm:\\".$command.' '.$remaining." -->\n");  
  }
  
  protected function renderGeneralCommand($command, $level, 
                                          $remaining)
  {  
    if (array_key_exists($command, $this->substitution_table)) {   
      $html_command = $this->substitution_table[$command];
      if (sizeof($html_command) > 1) {
        $this->usfm_text
             ->printItalicsToBody($html_command[self::IF_NORMAL],
                                  $html_command[self::IF_ITALIC_PARAGRAPH]);
      } else {
        $this->usfm_text->printHtmlText($html_command[self::IF_NORMAL]);
      }          
      $this->usfm_text->printHtmlText($remaining);
    } elseif (array_key_exists($command, $this->paragraph_settings)) {
      $this->switchParagraph($command, $level);
      $this->usfm_text->printHtmlText($remaining);
    } elseif (array_key_exists($command, $this->title_settings)) {
      $this->printTitle($command, $level, $remaining);
    } else {
      $this->displayUnsupportedCommand($command, $level, $remaining);
    }
  }
  
  private function extractSubCommand(&$remaining) {
  	$second_whitespace = strpos($remaining, ' ');
    if ($second_whitespace === False) {
      $second_whitespace = strlen($remaining);
    }
    $result = substr($remaining, 0, $second_whitespace);
    $remaining = substr($remaining, $second_whitespace+1);
    return $result;
  }
  
  private function switchParagraph($command, $level) {
    $setting = $this->paragraph_settings[$command];
    $this->usfm_text
         ->switchParagraph($level + $setting[self::BASE_LEVEL] - 1,
                           $setting[self::IS_ITALIC],
                           $setting[self::ALIGNMENT],
                           $setting[self::PARAGRAPH_CLASS]);
  }
  
  private function printTitle($command, $level, $content) {
    $setting = $this->title_settings[$command];
    $this->usfm_text
         ->printTitle($level + $setting[self::BASE_LEVEL] - 1,
                      $setting[self::IS_ITALIC], $content);
    
  }
}

function netscapeCapitalize($raw_text) {
  // Uppercase all letters, but make the first letter of every word bigger than
  // the rest, i.e. style of headings in the original Netscape Navigator website
  $words = explode(' ', strtoupper($raw_text));
  wfDebug(sizeof($words));
  for ($i=0; $i<sizeof($words); $i++) {
    if (mb_strlen($words[$i]) > 1) {
      $words[$i] = mb_substr($words[$i], 0, 1)."<small>".
                   mb_substr($words[$i], 1)."</small>";
    }
    wfDebug($words[$i]);
  }
  return implode(' ', $words);
}