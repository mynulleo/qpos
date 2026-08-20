<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;

class Item extends BaseModel
{
	protected $guarded = ['id'];

	protected $logName = "Item";

	protected $appends = ['barcode_image', 'original_image'];

	// file image push
	public function getImageAttribute($value)
	{
		if (empty($value)) {
			return null;
		}

		if (!is_string($value)) {
			return json_encode($value);
		}

		if (is_base64($value)) {
			return $value;
		}

		if (str_contains($value, '[') || str_contains($value, '{')) {
			return $value;
		}

		if (!str_starts_with($value, 'http://') && !str_starts_with($value, 'https://')) {
			return url("") . "/public/storage/" . $value;
		}

		return $value;
	}

	public function getOriginalImageAttribute()
	{
		$raw = $this->getRawOriginal('image');
		if (empty($raw)) {
			return null;
		}
		if (!str_contains($raw, '[') && !str_contains($raw, '{')) {
			return $this->image;
		}
		return getJsonMediaUrl($raw, 'original');
	}

	public function getBarcodeImageAttribute()
	{
		if (!empty($this->barcode)) {
			try {
				$dns1d = new \Milon\Barcode\DNS1D();
				return 'data:image/png;base64,' . $dns1d->getBarcodePNG((string)$this->barcode, 'C128', 2, 45);
			} catch (\Exception $e) {
				return null;
			}
		}
		return null;
	}

	public static function generateBarcode()
	{
		$maxBarcode = \Illuminate\Support\Facades\DB::table('items')
			->whereRaw("barcode REGEXP '^[0-9]+$'")
			->selectRaw('MAX(CAST(barcode AS UNSIGNED)) as max_barcode')
			->value('max_barcode');

		$nextBarcode = $maxBarcode ? max(111, intval($maxBarcode) + 1) : 111;

		while (\Illuminate\Support\Facades\DB::table('items')->where('barcode', (string)$nextBarcode)->exists()) {
			$nextBarcode++;
		}

		return (string)$nextBarcode;
	}
	// date format

	public function category()
	{
		return $this->belongsTo(Category::class, 'category_id', 'id');
	}

	public function unit()
	{
		return $this->belongsTo(Unit::class, 'unit_id', 'id');
	}

	public function itemPrices()
	{
		return $this->hasMany(ItemPrice::class, 'item_id', 'id');
	}

	public function stockSummaries()
	{
		return $this->hasMany(ItemStockSummary::class, 'item_id', 'id');
	}
}
