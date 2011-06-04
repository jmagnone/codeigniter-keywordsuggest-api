<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 

// Requires SimpleXML

// http://www.docstoc.com/docs/27072322/Google-Suggest-Desktop-Software-Application


class keywordsuggest
{
    private $ci;
    
    
    //pq=suggestqueries.google.com&cp=1
    private $_api_url = "http://clients1.google.com/complete/search?output=toolbar&num=20&hl=en&pq=suggestqueries.google.com&cp=1";
    //private $_api_url = "http://google.com/complete/search?output=toolbar&num=20&hl=en";
    
    
    public function __construct($params = null)
    {

        // do something with params
        
        $this->ci =& get_instance();

        // config values
        //$this->ci->config->item('');
    }
    
    /**
     * keywordsuggest::query()
     * 
     * Run a query in Google suggest and get suggestions for a given keyword, also 
     * return the number of queries for each suggeston.
     * 
     * @return
     */
    public function query($q)
    {
        $url = $this->_api_url.'&q='.urlencode($q);
        $xmlresult = file_get_contents( $url );
        
        $result = $this->_parse_output($xmlresult);
        
        log_message('debug', 'keywordsuggest api: '.print_r($result,TRUE) );
        
        return $result;
    }
    
    /**
     * keywordsuggest::query_advanced()
     * 
     * Uses Google suggest to query appending A..Z values. Can easily be adapted
     * to include numbers 0..9 or other chars. 
     * 
     * This is useful to expand your list of suggested keywords appending a char
     * after your seed keyword. For example, if you look for "recommendation" it will
     * look for "recommendation a", "recommendation b"... and so on.
     * 
     * @return
     */
    public function query_advanced($q)
    {
        $result = $this->query($q);
        
        $alphas = range('a', 'z');
        
        foreach($alphas as $char)
        {
            $subquery = $q . ' ' . $char;
            
            $subres = $this->query($subquery);
            
            $result['keywords'] = array_merge($result['keywords'], $subres['keywords'] );
        }
        
        return $result;
    }
    
    /**
     * keywordsuggest::query_tree()
     * 
     * Runs a query with multiple levels and return a tree structure with suggestions up
     * to max depth parameter.
     * 
     * @return void
     */
    public function query_tree($q, $params = null)
    {
        // under development
        
        return "ERROR";
    }
    
    
    /**
     * keywordsuggest::parse_output()
     * 
     * 
     * 
     * @return
     */
    private function _parse_output($xmlresult, $include_meta = TRUE)
    {
        $result = array();
        
        // Requires SimpleXML
        $xml = new SimpleXMLElement($xmlresult);
        
        $keywords = array();
        $total = 0;
        foreach($xml->children() as $cs )
        {
            $keyword = (string) $cs->suggestion['data'];
            $num_queries = (int) $cs->num_queries['int'];

            //echo "{} - {$cs->num_queries['int']} \r\n";            
            $keywords[] = array( 'suggestion' => $keyword, 'num_queries' => $num_queries );
            $total++; 
        }
        
        if ($include_meta) $result['meta'] = array('total'=>$total);
        
        $result['keywords'] = $keywords;
        
        return $result;
    }
    
    
}

/* End of file keywordsuggest.php */