<?php
namespace liuguang\mvc\data;

class ObserverData extends DataMap
{

    private $orgData = [];

    public function __construct(array & $dataArray)
    {
        parent::__construct($dataArray);
        $this->orgData = $dataArray;
    }

    public function getHasChanged(): bool
    {
        return $this->orgData != $this->toArray();
    }
}

