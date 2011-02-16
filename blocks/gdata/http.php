<?php
/**
 * Copyright (C) 2009  Moodlerooms Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://opensource.org/licenses/gpl-3.0.html.
 * 
 * @copyright  Copyright (c) 2009 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html     GNU Public License
 * @author Mark Nielsen
 */


/**
 * HTTP Client
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package blocks_gdata
 **/

/**
 * Dependencies
 **/
require_once($CFG->dirroot.'/blocks/gdata/gapps.php');
require_once('Zend/Http/Client.php');

/**
 * Extends Zend_Http_Client and splits
 * Zend_Http_Client->request() method so
 * we can start multiple requests at
 * once to mimic threading.
 *
 * @package blocks_gdata
 **/
class blocks_gdata_http extends Zend_Http_Client {
    /**
     * First half of Zend_Http_Client's request method
     * removed the start of the do-while loop.
     *
     * @return void
     **/
    public function request($method = NULL) {
        if (! $this->uri instanceof Zend_Uri_Http) {
            /** @see Zend_Http_Client_Exception */
            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception('No valid URI has been passed to the client');
        }

        if ($method) $this->setMethod($method);
        $this->redirectCounter = 0;
        $response = null;

        // Make sure the adapter is loaded
        if ($this->adapter == null) $this->setAdapter($this->config['adapter']);

        // Clone the URI and add the additional GET parameters to it
        $uri = clone $this->uri;
        if (! empty($this->paramsGet)) {
            $query = $uri->getQuery();
               if (! empty($query)) $query .= '&';
            $query .= http_build_query($this->paramsGet, null, '&');

            $uri->setQuery($query);
        }

        $body = $this->_prepareBody();
        $headers = $this->_prepareHeaders();

        // Open the connection, send the request and read the response
        $this->adapter->connect($uri->getHost(), $uri->getPort(),
            ($uri->getScheme() == 'https' ? true : false));

        $this->last_request = $this->adapter->write($this->method,
            $uri, $this->config['httpversion'], $headers, $body);
    }

    /**
     * Second half of Zend_Http_Client's request method
     * removed the end of the do-while loop and the
     * redirect code.
     *
     * @return Zend_Http_Response
     **/
    public function getResponse() {
        $response = $this->adapter->read();
        if (! $response) {
            /** @see Zend_Http_Client_Exception */
            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception('Unable to read response, or response is empty');
        }

        $response = Zend_Http_Response::fromString($response);
        if ($this->config['storeresponse']) $this->last_response = $response;

        // Load cookies into cookie jar
        if (isset($this->cookiejar)) $this->cookiejar->addCookiesFromResponse($response, $uri);

        return $response;
    }
}

?>