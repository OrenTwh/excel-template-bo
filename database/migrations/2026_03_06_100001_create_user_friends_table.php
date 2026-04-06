<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserFriendsTable extends Migration
{
    public function up()
    {
        Schema::create( 'user_friends', function ( Blueprint $table ) {
            $table->id();
            $table->unsignedBigInteger( 'user_id' );
            $table->unsignedBigInteger( 'friend_id' );
            $table->tinyInteger( 'status' )->default( 10 )->comment( '10=pending, 20=accepted, 30=declined' );
            $table->timestamps();

            $table->foreign( 'user_id' )->references( 'id' )->on( 'users' )->onDelete( 'cascade' );
            $table->foreign( 'friend_id' )->references( 'id' )->on( 'users' )->onDelete( 'cascade' );

            $table->unique( [ 'user_id', 'friend_id' ] );
        } );
    }

    public function down()
    {
        Schema::dropIfExists( 'user_friends' );
    }
}
