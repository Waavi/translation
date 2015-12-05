<?php

use Illuminate\Database\Migrations\Migration;

class RenameOverwriteToLocked extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('language_entries', function($table){
			$table->boolean('locked')->after('unstable')->default(0);
		});
		// Change the sign of locked for all entries that do not allow overwrite:
		DB::table('language_entries')->where('overwrite', '0')->update(array('locked' => '1'));
		Schema::table('language_entries', function($table){
			$table->dropColumn('overwrite');
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