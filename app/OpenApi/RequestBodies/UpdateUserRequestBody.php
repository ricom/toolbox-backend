<?php

namespace App\OpenApi\RequestBodies;


use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class UpdateUserRequestBody extends RequestBodyFactory
{
    public function build(): RequestBody
    {
        return RequestBody::create('user_update')
            ->description('Nutzerdaten')
            ->content(
                MediaType::json()->schema(
                    Schema::object('User')
                        ->properties(
                            Schema::string('username')->default(null)
                                ->description('new username'),
                            Schema::string('password')->default(null)
                                ->description('new password'),
                            Schema::string('current_password')->default(null)
                                ->description('current_password'),
                        )->required('current_password')
                )
            );
    }
}
