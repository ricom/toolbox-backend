<?php

namespace App\OpenApi\Responses;

use App\OpenApi\Schemas\CompleteUserSchema;
use App\OpenApi\Schemas\PaginationMetaSchema;
use App\OpenApi\Schemas\SimpleUserSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\OneOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class UserListResponse extends ResponseFactory
{
    public function build(): Response
    {
        return Response::ok()->description('Successful response')
            ->content(
                MediaType::json()->schema(
                    AllOf::create()->schemas(
                        PaginationMetaSchema::ref(),
                        Schema::object()->properties(
                            Schema::array('data')->items(
                                OneOf::create()->schemas(
                                    SimpleUserSchema::ref(),
                                    CompleteUserSchema::ref()
                                )
                            )
                        )
                    )
                )
            );
    }
}
