<?php

namespace QueryParser\Tests;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    public function getTable()
    {
        return 'test';
    }
}
