<?php

class HabravelInit extends Illuminate\Database\Migrations\Migration {
  function up() {
    Schema::create('polls', function ($table) {
      $table->increments('id');
      $table->timestamps();
      $table->softDeletes();
      $table->text('caption');
      $table->boolean('multiple');
    });

    Schema::create('poll_options', function ($table) {
      $table->increments('id');
      $table->timestamps();
      $table->softDeletes();
      $table->integer('poll')->unsigned();
      $table->text('caption');

      $table->foreign('poll')->references('id')->on('polls')
        ->onUpdate('cascade')->onDelete('cascade');
    });

    Schema::create('users', function ($table) {
      $table->increments('id');
      $table->timestamps();
      $table->string('password', 255);
      $table->string('remember_token', 255);
      $table->string('email', 255);
      $table->string('name', 255);
      $table->mediumText('info');
      $table->integer('poll')->unsigned()->nullable();
      $table->integer('score')->default(0);
      $table->integer('rating')->default(0);
      $table->char('regIP', 15);
      $table->timestamp('loginTime');
      $table->char('loginIP', 15)->default('');
      $table->mediumText('flags');
      $table->string('avatar', 255)->default('');

      $table->unique('email');
      $table->unique('name');
      $table->index('rating');

      $table->foreign('poll')->references('id')->on('polls')
        ->onUpdate('cascade')->onDelete('set null');
    });

    Schema::create('poll_votes', function ($table) {
      $table->timestamps();
      $table->integer('poll')->unsigned();
      // NULL = abstained.
      $table->integer('option')->unsigned()->nullable();
      $table->integer('user')->unsigned();
      $table->char('ip', 15);

      $table->unique(array('poll', 'option', 'user'));

      $table->foreign('poll')->references('id')->on('polls')
        ->onUpdate('cascade')->onDelete('cascade');

      $table->foreign('option')->references('id')->on('poll_options')
        ->onUpdate('cascade')->onDelete('cascade');

      $table->foreign('user')->references('id')->on('users')
        ->onUpdate('cascade')->onDelete('cascade');
    });

    Schema::create('posts', function ($table) {
      $table->increments('id');
      $table->timestamps();
      $table->integer('top')->unsigned()->nullable()->default(null);
      $table->integer('parent')->unsigned()->nullable()->default(null);
      $table->string('url', 255);
      $table->integer('author')->unsigned();
      $table->integer('poll')->unsigned()->nullable();
      $table->integer('score')->default(0);
      $table->integer('views')->unsigned()->default(0);
      $table->mediumText('info');
      $table->text('sourceURL');
      $table->string('sourceName', 255)->default('');
      $table->string('caption', 255);
      $table->string('markup', 255);
      $table->mediumText('text');
      $table->mediumText('html');
      $table->mediumText('introHTML');
      $table->mediumText('flags');
      $table->timestamp('listTime')->nullable();
      $table->timestamp('pubTime')->nullable();

      $table->unique('url');
      //$table->index('author');    // implied by foreign key.
      $table->index(array('score', 'listTime'));
      $table->index('listTime');
      $table->index('sourceName');

      $table->foreign('top')->references('id')->on('posts')
        ->onUpdate('cascade')->onDelete('cascade');

      $table->foreign('parent')->references('id')->on('posts')
        ->onUpdate('cascade')->onDelete('cascade');

      $table->foreign('poll')->references('id')->on('polls')
        ->onUpdate('cascade')->onDelete('set null');

      $table->foreign('author')->references('id')->on('users')
        ->onUpdate('cascade')->onDelete('cascade');
    });

    DB::unprepared('ALTER TABLE posts ADD `seen` MEDIUMBLOB NOT NULL');

    Schema::create('tags', function ($table) {
      $table->increments('id');
      $table->timestamps();
      $table->integer('parent')->unsigned()->nullable()->default(null);
      $table->string('type', 255)->default('');
      $table->string('caption', 255);
      $table->mediumText('flags');

      $table->index('type');
      $table->unique('caption');

      $table->foreign('parent')->references('id')->on('tags')
        ->onUpdate('cascade')->onDelete('set null');
    });

    Schema::create('post_tag', function ($table) {
      $table->integer('post_id')->unsigned();
      $table->integer('tag_id')->unsigned();

      $table->primary(array('post_id', 'tag_id'));

      $table->foreign('post_id')->references('id')->on('posts')
        ->onUpdate('cascade')->onDelete('cascade');

      $table->foreign('tag_id')->references('id')->on('tags')
        ->onUpdate('cascade')->onDelete('cascade');
    });

    Schema::create('poll_post', function ($table) {
      $table->integer('post_id')->unsigned();
      $table->integer('poll_id')->unsigned();
      $table->softDeletes();

      $table->primary(array('post_id', 'poll_id'));

      $table->foreign('post_id')->references('id')->on('posts')
        ->onUpdate('cascade')->onDelete('cascade');

      $table->foreign('poll_id')->references('id')->on('polls')
        ->onUpdate('cascade')->onDelete('cascade');
    });

    DB::table('polls')->insert(array(array(
      'id'                => 1,
      'caption'           => 'Vote up/down',
      'multiple'          => 0,
    )));

    // So that id = ((int) true/false) + 1.
    DB::table('poll_options')->insert(array(array('id' => 1, 'poll' => 1, 'caption' => 'down'),
                                            array('id' => 2, 'poll' => 1, 'caption' => 'up')));
  }

  function down() {
    Schema::drop('polls');
    Schema::drop('poll_options');
    Schema::drop('users');
    Schema::drop('poll_votes');
    Schema::drop('posts');
    Schema::drop('tags');
    Schema::drop('post_tag');
    Schema::drop('poll_post');
  }
}
