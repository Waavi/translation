<?php

use Illuminate\Database\Migrations\Migration;

class UserEdited extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('language_entries', function($table){
			$table->boolean('overwrite')->after('unstable')->default(1);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}