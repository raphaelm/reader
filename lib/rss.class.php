<?php
/*
 *      rss.class.php
 *      part of geeksrss class
 *      
 *      Author: Raphael Michel <webmaster@raphaelmichel.de>
 *      Copyright: 2009 geek's factory
 *      License: GNU Lesser General Public License
 */
class geeksrss {
    protected $simplexml;
    protected $data = array();
    
    // RSS SPEC 2.0.11: http://www.rssboard.org/rss-2-0-1 
    
    // Channel-Attr-List without: item, image, textInput
    protected $rss_channel_elements = array(
                "title", "link", "description", "language", "copyright", 
                "managingEditor", "webMaster", "pubDate", "lastBuildDate", 
                "generator", "docs", "rating", "skipHours", "skipDays", "ttl",
                "category");
                
    protected $rss_image_elements = array(
                "url", "title", "link", "width", "height", "description");
    protected $rss_textinput_elements = array(
                "name", "title", "link", "description");
                
    protected $rss_item_elements = array(
                "title", "link", "description", "author", "pubDate",
                "comments", "guid", "source");
                
    
    public function loadFromFile($url){
        if(file_exists($url)){
            $this->simplexml = simplexml_load_file($url);
            $this->_parse();
            return ($this->simplexml) ? true : false;
        }else{
            trigger_error('geeksrss->loadFromFile failed: File not found');
            return false;
        }
    }
    
    public function loadFromString($rss){
        $this->simplexml = simplexml_load_string($rss);
        $this->_parse();
        return ($this->simplexml) ? true : false;
    }
    
    public function getAllChannelData(){
        return $this->data[-1];
    }
    
    public function getAllItemData(){
        $data = $this->data;
        unset($data[-1]);
        return $data;
    }
    
    public function getChannelData($field){
        $chan = (array) $this->simplexml->channel;
        if(isset($this->data[-1][$field])){
            return $this->data[-1][$field];
        }elseif(isset($chan[$field])){
            return $chan->$field;
        }else{
            return false;
        }
    }
    
    public function getItemData($item, $field){
        $_item = (array) $this->simplexml->channel->item[$item];
        if(isset($this->data[$item][$field])){
            return $this->data[$item][$field];
        }elseif(isset($_item[$field])){
            return $_item[$field];
        }else{
            return false;
        }
    }
    
    public function itemCount(){
        return count($this->data)-1;
    }
    
    protected function _parse(){
        $xml = $this->simplexml;
        if(!isset($xml->channel)){
            trigger_error('geeksrss parser failed: no &lt;channel&gt; found');
            return false;
        }
        $channel = $xml->channel;
        foreach($this->rss_channel_elements as $element){
            if(isset($channel->$element)){
                $el = (array) $channel->$element;
                $this->data[-1][$element] = $el[0];
            }
        }
        // <image>
        if(isset($channel->image)){
            $image = $channel->image;
            foreach($this->rss_image_elements as $element){
                if(isset($image->$element)){
                    $el = (array) $image->$element;
                    $this->data[-1]['image'][$element] = $el[0];
                }
            }
        }
        // <cloud>
        if(isset($channel->cloud)){
            $cloud = (array) $channel->cloud;
            foreach($cloud['@attributes'] as $name => $val){
                $this->data[-1]['cloud'][$name] = $val;
            }
        }
        // <textInput>
        if(isset($channel->textInput)){
            $tI = $channel->textInput;
            foreach($this->rss_textinput_elements as $element){
                if(isset($rI->$element)){
                    $el = (array) $rI->$element;
                    $this->data[-1]['textInput'][$element] = $el[0];
                }
            }
        }
        // ITEMS
        $i = 0;
        foreach($channel->item as $item){
            foreach($this->rss_item_elements as $element){
                if(isset($item->$element)){
                    $el = (string) $item->$element;
                    $this->data[$i][$element] = $el;
                }
            }
            
            // <source>
            if(isset($item->source)){
                $source = (array) $item->source;
                $el = $source['@attributes']['url'];
                $this->data[$i]['source.url'] = $el;
            }
            
            // <enclosure>
            if(isset($item->enclosure)){
                $enc = (array) $item->enclosure;
                $this->data[$i]['enclosure']['url'] = $enc['@attributes']['url'];
                $this->data[$i]['enclosure']['length'] = $enc['@attributes']['length'];
                $this->data[$i]['enclosure']['type'] = $enc['@attributes']['type'];
            }
            
            // <category>
            if(isset($item->category)){
                foreach($item->category as $j => $cat){
                    $el = (array) $cat;
                    if(isset($el['@attributes']['domain'])){
                        $this->data[$i]['category'][] = array($el[0], 'domain' => $el['@attributes']['domain']);
                    }else{
                        $this->data[$i]['category'][] = array($el[0]);
                    }
                }
            }
            
            // timestamp
            if(isset($item->pubDate)){
                $el = (array) $item->pubDate;
                $this->data[$i]['@timestamp'] = @strtotime($el[0]);
            }
            
            // <guid>
            if(isset($item->guid)){
                $guid = (array) $item->guid;
                if(isset($guid['@attributes']['isPermaLink'])){
                    $el = $guid['@attributes']['isPermaLink'];
                    $this->data[$i]['guid.isPermaLink'] = (bool) $el;
                }
            }
            
            $i++;
        }
    }
    
}
