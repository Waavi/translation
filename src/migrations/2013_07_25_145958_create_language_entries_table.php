<?php

use Illuminate\Database\Migrations\Migration;

class CreateLanguageEntriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('language_entries', function($table){
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->integer('language_id')->unsigned();
			$table->string('namespace', 150);
			$table->string('group', 150);
			$table->string('item', 150);
			$table->text('text');
			$table->timestamps();
			$table->foreign('language_id')->references('id')->on('languages');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('language_entries');
	}

}