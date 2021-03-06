<?php
/*
* Copyright (c) 2011, M. Desfrenes <desfrenes@gmail.com>
* All rights reserved.
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions are met:
*
*     * Redistributions of source code must retain the above copyright
*       notice, this list of conditions and the following disclaimer.
*     * Redistributions in binary form must reproduce the above copyright
*       notice, this list of conditions and the following disclaimer in the
*       documentation and/or other materials provided with the distribution.
*     * Neither the name of M. Desfrenes nor the names of its contributors may
*       be used to endorse or promote products derived from this software
*       without specific prior written permission.
*
* THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS ``AS IS'' AND ANY
* EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
* WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
* DISCLAIMED. IN NO EVENT SHALL THE REGENTS AND CONTRIBUTORS BE LIABLE FOR ANY
* DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
* (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
* LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
* ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
* SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
namespace Discogs;
class Exception extends \Exception{}
class WS
{
    private $api_url = 'http://api.discogs.com/';
    private $user_agent;
    
    /**
     * User agent should follow RFC 1945
     * (http://tools.ietf.org/html/rfc1945#section-3.7)
     *
     * @param string $user_agent 
     */
    public function __construct($user_agent)
    {
        $this->user_agent = $user_agent;
    }
    
    /**
     * @param integer $release_id
     * @return stdClass 
     */
    public function release($release_id)
    {
        $result = $this->parse(file_get_contents($this->api_url . 'release/' . 
                $release_id, false, $this->getContext()));
        return $result->resp->release;
    }
    
    /**
     * @param int $master_release_id
     * @return stdClass 
     */
    public function masterRelease($master_release_id)
    {
        $result = $this->parse(file_get_contents($this->api_url . 'master/' . 
                $master_release_id, false, $this->getContext()));
        return $result->resp->master;
    }
    
    /**
     * if $releases is true, will return the list of artist releases
     * 
     * @param string $artist
     * @param bool $releases
     * @return stdClass 
     */
    public function artist($artist, $releases = false)
    {
        $result = $this->parse(file_get_contents($this->api_url . 'artist/' . 
                urlencode($artist) . ($releases ? '?releases=1' : ''), false, 
                $this->getContext()));       
        return $result->resp->artist;
    }

    /**
     * if $releases is true, will return the list of artist releases
     *
     * @param string $label
     * @param bool $releases
     * @return stdClass 
     */
    public function label($label, $releases = false)
    {
        $result = $this->parse(file_get_contents($this->api_url . 'label/' . 
                urlencode($label) . ($releases ? '?releases=1' : ''), false, 
                $this->getContext()));
        return $result->resp->label;
    }
    
    /**
     * @param string $query
     * @param string $type  one off all, releases, artists, labels
     * @param int $page
     * @return stdClass 
     */
    public function search($query, $type = 'all', $page = 1)
    {
        $result = $this->parse(file_get_contents($this->api_url . 'search?q=' . 
                urlencode($query) . '&type=' . $type . '&page=' . 
                (int)$page, false, $this->getContext()));
        return $result->resp->search;        
    }
    
    private function getContext()
    {
        return stream_context_create(array(
            'http'=>array(
                'method' => 'GET',
                'header' =>
                    'Accept-Encoding: x-gzip, deflate\r\n' .
                    'User-Agent: ' . $this->user_agent . "\r\n"
                )));
    }
    
    private function parse($result)
    {
        if($result === false)
        {
            throw new Exception('could not retrieve response');
        }
        $data = json_decode($result);
        if(is_null($data))
        {
            throw new Exception('could not decode response');
        }
        return $data;
    }    
}

if(!count(debug_backtrace()))
{
    $discogs = new WS('PHPDiscogsWS/0.1');
    $artist = $discogs->artist('Theo (3)');
    if($artist->realname == 'Mickaël Desfrênes')
    {
        echo 'API works :-)' . PHP_EOL;
    }
}
