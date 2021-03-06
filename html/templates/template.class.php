<?php

	/**
	 * Simple template engine class (use [@tag] tags in your templates).
	 * 
	 * @link http://www.broculos.net/ Broculos.net Programming Tutorials
	 * @author Nuno Freitas <nunofreitas@gmail.com>
	 * @version 1.0
	 * Edits by James Loewen
	 */
    class Template {
    	/**
    	 * The filename of the template to load.
    	 *
    	 * @access protected
    	 * @var string
    	 */
        protected $file;
        
	/**
	 * The root directory that contains templates
	 */
	private $rootdir;

        /**
         * An array of values for replacing each tag on the template (the key for each value is its corresponding tag).
         *
         * @access protected
         * @var array
         */
        protected $values = array();
        
        /**
         * Creates a new Template object and sets its associated file.
         *
         * @param string $file the filename of the template to load
         */
        public function __construct($folder, $file) {
            $this->rootdir = $_SERVER['DOCUMENT_ROOT'] . "/templates/";
            $this->file = $folder . "/" . $file;
        }
        
        /**
         * Sets a value for replacing a specific tag.
         *
         * @param string $key the name of the tag to replace
         * @param string $value the value to replace
         */
        public function set($key, $value) {
            $this->values[$key] = $value;
        }
        
        /**
         * Outputs the content of the template, replacing the keys for its respective values.
         *
         * @return string
         */
        public function output() {
        	/**
        	 * Tries to verify if the file exists.
        	 * If it doesn't return with an error message.
        	 * Anything else loads the file contents and loops through the array replacing every key for its value.
        	 */
            $fullPath = $this->rootdir . $this->file;
            if (!file_exists($fullPath)) {
            	return "Error loading template file ($this->file).<br />";
            }
            $output = file_get_contents($fullPath);

            // removes PHP style comments
            $comment_pattern = array('#/\*.*?\*/#s', '#(?<!:)//.*#'); 
            $output = preg_replace($comment_pattern, NULL, $output);
            
            // parse if statements
            $ifblocks = [];
            $if_pattern = "/\[\?if (?P<tag>\w+)\](?P<text>.*?)?\[\?fi\]/s";
            preg_match_all($if_pattern, $output, $ifblocks, PREG_SET_ORDER);
            foreach ($ifblocks as $ifblock) {
              if (strcmp($this->values[$ifblock['tag']], '')) {
                $output = str_replace($ifblock[0], $ifblock['text'], $output);
              } else {
                $output = str_replace($ifblock[0], '', $output);
              }
            }
            
            // replace tags
            foreach ($this->values as $key => $value) {
            	$tagToReplace = "[@$key]";
            	$output = str_replace($tagToReplace, $value, $output);
            	
            }
            $tagPattern = "/\[@\w+\]/";
            $output = preg_replace($tagPattern, "", $output);

            return $output;
        }
        
        /**
         * Merges the content from an array of templates and separates it with $separator.
         *
         * @param array $templates an array of Template objects to merge
         * @param string $separator the string that is used between each Template object
         * @return string
         */
        static public function merge($templates, $separator = "\n") {
        	/**
        	 * Loops through the array concatenating the outputs from each template, separating with $separator.
        	 * If a type different from Template is found we provide an error message. 
        	 */
            $output = "";
            
            foreach ($templates as $template) {
            	$content = (get_class($template) !== "Template")
            		? "Error, incorrect type - expected Template."
            		: $template->output();
            	$output .= $content . $separator;
            }
            
            return $output;
        }
    }

    $mainTemplate = new Template("common", "layout.tpl");
    $sidebar = new Template("common", "sidebar.tpl");
    $header = new Template("common", "header.tpl");
    $footer = new Template("common", "footer.tpl");
    $mainTemplate->set("sidebar", $sidebar->output());
    $mainTemplate->set("header", $header->output());
    $mainTemplate->set("footer", $footer->output());
?>