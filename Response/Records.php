<?php

namespace Hatimeria\ExtJSBundle\Response;

/**
 * Records response, for extjs store with paging implemented
 *
 * @author Michal Wujas
 */
class Records implements Response
{
    private $limit = 0, $total, $records, $start = 0;
    
    public function records(array $records)
    {
        $this->total   = count($records);
        $this->records = $records;
        $this->limit   = 0;
        
        return $this;
    }
    
    public function limit($limit)
    {
        $this->limit = $limit;
        
        return $this;
    }
    
    public function total($total)
    {
        $this->total = $total;
        
        return $this;
    }
    
    public function toArray()
    {
        $r = new Success();
        $r->set("limit", $this->limit);
        $r->set("records", $this->records);
        $r->set("total", $this->total);
        $r->set("start", $this->start);
        
        return $r->toArray();
    }
}