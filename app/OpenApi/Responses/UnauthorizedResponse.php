<?php

namespace App\OpenApi\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class UnauthorizedResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        return Response::forbidden('unauthorized')->content(
            MediaType::json()->schema(
                Schema::object()->properties(
                    Schema::string('message')->default('This action is unauthorized.'),
                )
            )
        )->description('The current user isn\'t authorized to do this action');
    }
}
