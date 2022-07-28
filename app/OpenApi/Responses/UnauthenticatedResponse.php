<?php

namespace App\OpenApi\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class UnauthenticatedResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        return Response::unauthorized('unauthenticated')->content(
            MediaType::json()->schema(
                Schema::object()->properties(
                    Schema::string('message')->default('Unauthenticated'),
                )
            )
        )->description('The current user isn\'t authenticated');
    }
}
