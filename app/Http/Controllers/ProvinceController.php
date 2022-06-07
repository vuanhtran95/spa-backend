<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Http\HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response;
use Exception;
use App\Helper\Translation;

class ProvinceController extends Controller
{

	public function __construct(){}

    public function get(Request $request)
	{
		$provinces = json_decode(file_get_contents(public_path() . "/json/provinces.json"), true);

		try {
			return HttpResponse::toJson(
				true,
				Response::HTTP_OK,
				'Success',
				$provinces
			);
		} catch (Exception $e) {
			return HttpResponse::toJson(false, $e->getMessage());
		}
	}
}