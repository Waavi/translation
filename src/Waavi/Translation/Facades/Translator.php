<?php namespace Waavi\Translation\Facades;

use Illuminate\Translation\Translator as LaravelTranslator;

class Translator extends LaravelTranslator {

	public function test()
	{
		echo Config::get('locale');
	}

}