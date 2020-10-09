<?php


namespace Voice\OpenApi\Specification\Parts\Parameters;


class QueryParameter implements Parameter
{
    public function __construct()
    {
    }


    public function toSchema(): array
    {
        return [

        ];
    }
}



//- in: query
//  name: metadata
//  schema:
//    type: boolean
//  allowEmptyValue: true  # <-----
