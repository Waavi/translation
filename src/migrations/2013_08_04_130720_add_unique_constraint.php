<?php

use Illuminate\Database\Migrations\Migration;

class AddUniqueConstraint extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('languages', function($table){
			$table->unique('locale');
			$table->unique('name');
		});
		Schema::table('language_entries', function($table){
			$table->unique('language_id, namespace, group, item');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
	}

}