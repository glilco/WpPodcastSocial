<?php 


class ParseFeed {
  private $podcast = array();
  private $elements   = null;
  private $is_image = false;
  private $parser;
  private $continue_parsing = true;
  private $podcast_feed;
  private $itunes_sumary = false;
  
  function __construct($url_feed) {
    $this->parser =  xml_parser_create(); 
    xml_set_element_handler($this->parser, array($this, "startElements"), array($this, "endElements"));
    xml_set_character_data_handler($this->parser, array($this, "characterData"));
    $this->podcast_feed = esc_url_raw($url_feed);
  }

  function __destruct() {
      xml_parser_free($this->parser);
  }
  
  function startElements($parser, $name, $attrs) {
      
      if(!empty($name) && $this->continue_parsing) {
		 
         $this->elements = $name;
         $this->is_image = $name == "IMAGE"?true:$this->is_image;
         
         if($name == "ITUNES:IMAGE" && isset($attrs["HREF"])) {
			 $this->podcast["ITUNESIMAGE"] = $attrs["HREF"];
		 }
         
         if($name == "ITEM") {
            $this->continue_parsing = false;
         }
      }
   }
   
   
   // Called to this function when tags are closed 
   function endElements($parser, $name) {
      
      if(!empty($name) && $this->continue_parsing) {
         $this->elements = null;
         if($name == "IMAGE") {
            $this->is_image = false;
         }
      }
   }
   
   // Called on the text between the start and end of the tags
   function characterData($parser, $data) {
      
      if(!empty($data) && $this->continue_parsing) {
         if ($this->elements == 'TITLE' || $this->elements == 'LINK' ||  $this->elements == 'DESCRIPTION' ||  $this->elements == 'URL' || $this->elements == 'ITUNES:AUTHOR' ) {
            if($this->is_image) {
                if($this->elements == 'URL') {
					if (!isset($this->podcast["URL"]) || trim($this->podcast["URL"])==='') {
						$this->podcast[$this->elements] = $data;
					}
                }
            } else {
                if($this->elements != 'URL') {
					if(isset($this->podcast[$this->elements])) {
						$this->podcast[$this->elements] .= $data;
					} else  if (!$this->itunes_sumary || $this->elements != 'DESCRIPTION') {
						$this->podcast[$this->elements] = $data;
					}
                }
            }
         } else if($this->elements == 'ITUNES:SUMMARY') {
			 if(!isset($this->podcast['DESCRIPTION'])) {
				 $this->itunes_sumary = true;
				 $this->podcast['DESCRIPTION'] = $data;
			 } else if($this->itunes_sumary) {
				 $this->podcast['DESCRIPTION'] .= $data;
			 }
		 }
      }
   }
   
   function parse_podcast_feed() {
	 ini_set("user_agent","Opera/9.80 (Windows NT 6.1; U; Edition Campaign 21; en-GB) Presto/2.7.62 Version/11.00");
     // open xml file
     if (!($handle = @fopen($this->podcast_feed, "r"))) {
        return false;
     }
     
     while($data = @fread($handle, 4096)) {
        if(!$this->continue_parsing) {
          break;
        }
        $ok = xml_parse($this->parser, $data);  // start parsing an xml document 
     }
     
     return $this->podcast;
   }
}
