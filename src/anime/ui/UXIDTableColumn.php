<?php

namespace anime\ui;


use php\gui\UXTableColumn;

class UXIDTableColumn extends UXTableColumn
{
    public function __construct(string $id, string $text, bool $editable = true)
    {
        parent::__construct();

        $this->id = $id;
        $this->text = $text;
        $this->editable = $editable;
    }
}