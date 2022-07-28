<?php

namespace App\OpenApi\Schemas;

use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AnyOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Not;
use GoldSpecDigital\ObjectOrientedOAS\Objects\OneOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\SchemaFactory;

class PaginationMetaSchema extends SchemaFactory implements Reusable
{
    /**
     * @return AllOf|OneOf|AnyOf|Not|Schema
     */
    public function build(): SchemaContract
    {
        return Schema::object('pagination')
            ->properties(
                Schema::object('links')->properties(
                    Schema::string('first')
                        ->description('Link zur ersten Seite'),
                    Schema::string('last')
                        ->description('Link zur letzen Seite'),
                    Schema::string('prev')->nullable()
                        ->description('Link zur vorherigen Seite'),
                    Schema::string('next')->nullable()
                        ->description('Link zur nächsten Seite')
                ),
                Schema::object('meta')->properties(
                    Schema::integer('current_page')
                        ->example(2),
                    Schema::integer('from')->example(15)
                        ->description('Position des ersten Eintrags dieser Seite von allen Einträgen'),
                    Schema::integer('last_page')->example(3),
                    Schema::integer('path')
                        ->description('link zur Startseite dieser Pagination'),
                    Schema::integer('per_page')->example(15)
                        ->description('Anzahl der Einträge pro Seite'),
                    Schema::integer('to')->example(29)
                        ->description('Position des letzen Eintrags dieser Seite von allen Einträgen'),
                    Schema::integer('total')->example(38)
                        ->description('Anzahl aller Einträge über alle Seiten')
                )
            );
    }
}
