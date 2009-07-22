<?php
require_once 'Zend/Http/Client.php';
require_once 'Zend/Json.php';
require_once 'Zend/Date.php';
require_once 'Zend/Validate.php';

class Recordshelf_Service_Backtweets 
{
    const API_KEY_LENGTH = 20;
    private $_apiKey = null;    
    private $_responseFormat = null;
    private $_searchFilter = array();
    private $_apiEndpoint = 'http://backtweets.com/search';
    private $_supportedResponseFormats = array('xml', 'json');
    private $_supportedSearchFilterKeys = array('url', 'from_name', 'to_name', 
        'start', 'end', 'since_id'
    );
    
    /**
     * @param array $configuration
     * @throws Exception
     */
    public function __construct(array $configuration)
    {
        $configuration = array_map('trim', $configuration);
        if (!isset($configuration['api_key'])) {
            throw new Exception('Mandatory API key not set in configuration');
        }
        if (!isset($configuration['response_format'])) {
            throw new Exception('Mandatory response format not set in configuration');
        }        
        if (!in_array($configuration['response_format'], 
            $this->_supportedResponseFormats)) {
            throw new Exception('Unsupported response format used in configuration');
        }
        if (self::API_KEY_LENGTH !== strlen($configuration['api_key'])) {
            throw new Exception('Provided API key is not 20 characters long');
        }        
        $this->_responseFormat = $configuration['response_format'];
        $this->_apiKey = $configuration['api_key'];
    }
    /**
     * Sets the provided search filter
     *
     * @param array $searchFilter
     * @throws Exception
     * @return void
     */
    private function _setSearchFilter(array $searchFilter)
    {
        $this->_searchFilter = array_map('trim', $searchFilter);
        $this->_validateSearchFilter();
    }
    /**
     * Validates the set search filter
     *
     * @throws Exception
     * @return void
     */
    private function _validateSearchFilter() 
    {
        $searchFilterKeys = array_keys($this->_searchFilter);
        foreach ($searchFilterKeys as $searchFilterKey) {
            if (!in_array($searchFilterKey, $this->_supportedSearchFilterKeys)) {
                throw new Exception("Unsupported search filter '{$searchFilterKey}' provided");
            }        
        }
        if (!isset($this->_searchFilter['url'])) {
            throw new Exception("No mandatory search Url provided in search filter");
        }
        
        if (!Zend_Uri::check($this->_searchFilter['url'])) {
            throw new Exception("Invalid Url provided in search filter");
        }
        
        if (isset($this->_searchFilter['start']) || isset($this->_searchFilter['end'])) {
            if (!Zend_Date::isDate($this->_searchFilter['start'], 'yyyy/MM/dd') || 
                !Zend_Date::isDate($this->_searchFilter['end'], 'yyyy/MM/dd')) {
                throw new Exception("Date search filter doesn't seem to be valid dates");
            }
            $dateFormatPattern = '#^[0-9]{4}/[0-9]{2}/[0-9]{2}#';
            if (!Zend_Validate::is($this->_searchFilter['start'], 'Regex', array('pattern' => $dateFormatPattern)) ||
                !Zend_Validate::is($this->_searchFilter['end'], 'Regex', array('pattern' => $dateFormatPattern))) {
                throw new Exception("Date search filter doesn't follow the expected 'YYYY/MM/dd' format");
            }
        }
    }
    /**
     * Applies the search filter and makes the actual API request
     *
     * @param array $searchFilter
     * @return string
     */
    private function _apiRequest(array $searchFilter)
    {    
        $this->_setSearchFilter($searchFilter);
        $serviceUri = $this->_apiEndpoint . ".{$this->_responseFormat}";
        $client = new Zend_Http_Client($serviceUri);
        $client->setParameterGet(array('key' => $this->_apiKey));
            
        foreach ($this->_searchFilter as $key => $value) {
            if ($key === 'url') {
                $key = 'q';
            }
            $client->setParameterGet(array($key => $value));
        }
        
        $response = $client->request();                
        
        if (403 === Zend_Http_Response::extractCode($response)) {
            $codeAsText = strtolower(Zend_Http_Response::responseCodeAsText(403));
            throw new Exception("Access to Backtweets API {$codeAsText}, check used API key");
        }
        return $response->getBody();
    }
    /**
     * Searchs for backtweets according to the feed search filter and 
     * returns the response
     *
     * @param array $searchFilter
     * @param boolean $convertResponseIntoArray
     * @return mixed
     */
    public function search(array $searchFilter, $convertResponseIntoArray = false)
    {
        $response = $this->_apiRequest($searchFilter);
        if ($convertResponseIntoArray) {
            if ('json' === $this->_responseFormat) { 
                $response = Zend_Json::decode($response);
            } elseif ('xml' === $this->_responseFormat) {
                $response = Zend_Json::decode(Zend_Json::fromXml($response));
            }
        }
        return $response;
    }
}