<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitationLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invitation_links', function (Blueprint $table) {
            $table->string("token")->unique()->index();
            $table->primary("token");

            $table->timestamp("expiry_date")->nullable();
            $table->integer("permission");
            $table->foreignId("save_id")->constrained(); // onDelete and onUpdate added in `add_on_delete_cascade` migration
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invitation_links');
    }
}
