<?php

require_once ROOT_DIR . '/sys/MemoryCache/CachedValue.php';
class Memcache
{
	private $enableDbCache = true;
    private $vars = array();

    public function get($name){
    	if (array_key_exists($name, $this->vars)) {
            return $this->vars[$name];
        }elseif($this->enableDbCache){
    		try {
			    $cachedValue = new CachedValue();
			    $cachedValue->cacheKey = $name;
			    if ($cachedValue->find(true)) {
			    	if ($cachedValue->expirationTime != 0 && $cachedValue->expirationTime < time()){
			    		return false;
				    }else{
					    $unSerializedValue = unserialize($cachedValue->value);
					    return $unSerializedValue;
				    }
			    }
		    }catch(Exception $e){
    			//Table has not been created ignore
		    }
	    }
        return false;
    }

    public function set($name, $value, $timeout)
    {
	    $this->vars[$name] = $value;
	    if ($this->enableDbCache) {
		    $valueToCache = serialize($value);
		    if (strlen($valueToCache) <= 16384 && strlen($name) < 200) {
			    try {
				    $cachedValue = new CachedValue();
				    $cachedValue->cacheKey = $name;
				    $isNew = true;
				    if ($cachedValue->find(true)) {
					    $isNew = false;
				    }
				    $cachedValue->value = $valueToCache;
				    if ($timeout == 0) {
					    $cachedValue->expirationTime = 0;
				    } else {
					    $cachedValue->expirationTime = time() + $timeout;
				    }

				    if ($isNew) {
					    $cachedValue->insert();
				    } else {
					    $cachedValue->update();
				    }
			    } catch (Exception $e) {
				    //Table has not been created ignore
			    }
		    }else{
		    	global $logger;
		    	$logger->log("data was too large to be cached", Logger::LOG_WARNING);
		    	return false;
		    }
	    }
        return true;
    }

    public function delete($name){
        unset($this->vars[$name]);
	    if($this->enableDbCache) {
		    try {
			    $cachedValue = new CachedValue();
			    $cachedValue->cacheKey = $name;
			    $cachedValue->delete(true);
		    } catch (Exception $e) {
			    //Table has not been created ignore
		    }
	    }
    }
}