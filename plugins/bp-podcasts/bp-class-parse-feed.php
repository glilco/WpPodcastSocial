<?php 


class ParseFeed {
  private $podcast = array();
  private $elements   = null;
  private $is_image = false;
  private $parser;
  private $continue_parsing = true;
  private $podcast_feed;
  
  function __construct($url_feed) {
    $this->parser =  xml_parser_create(); 
    xml_set_element_handler($this->parser, array($this, "startElements"), array($this, "endElements"));
    xml_set_character_data_handler($this->parser, array($this, "characterData"));
    $this->podcast_feed = $url_feed;
  }

  function __destruct() {
      xml_parser_free($this->parser);
  }
  
  function startElements($parser, $name, $attrs) {
      
      if(!empty($name)) {
         $this->elements = $name;
         $this->is_image = $name == "IMAGE"?true:$this->is_image;
         
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
         if ($this->elements == 'TITLE' || $this->elements == 'LINK' ||  $this->elements == 'DESCRIPTION' ||  $this->elements == 'URL') {
            if($this->is_image) {
                if($this->elements == 'URL') {
                    $this->podcast[$this->elements] = $data;
                }
            } else {
                if($this->elements != 'URL') {
                    $this->podcast[$this->elements] = $data;
                }
            }
         }
      }
   }
   
   function parse_podcast_feed() {
     // open xml file
     if (!($handle = fopen($this->podcast_feed, "r"))) {
        return false;
     }
     
     while($data = fread($handle, 4096)) {
        if(!$this->continue_parsing) {
          break;
        }
        $ok = xml_parse($this->parser, $data);  // start parsing an xml document 
     }
     
     return $this->podcast;
   }
}
