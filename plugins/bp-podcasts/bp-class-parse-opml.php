<?php 


class ParseOPML {
  private $opml = array();
  private $parser;
  private $opml_file;
  
  function __construct($url_feed) {
    $this->parser =  xml_parser_create(); 
    xml_set_element_handler($this->parser, array($this, "startElements"), array($this, "endElements"));
    //xml_set_character_data_handler($this->parser, array($this, "characterData"));
    $this->opml_file = $url_feed;
  }

  function __destruct() {
      xml_parser_free($this->parser);
  }
  
  function startElements($parser, $name, $attrs) {
      
      if(!empty($name)) {
         
         if($name == "OUTLINE") {
           if(isset($attrs['XMLURL'])) {
              $this->opml[] = $attrs['XMLURL'];
            }
         }
      }
   }
   
   function endElements($parser, $name) {
      
   }
   
   
   function parse_opml_file() {
     // open xml file
     if (!($handle = @fopen($this->opml_file, "r"))) {
        return false;
     }
     
     while($data = fread($handle, 4096)) {
        $ok = xml_parse($this->parser, $data);  // start parsing an xml document 
     }
     
     return $this->opml;
   }
}
