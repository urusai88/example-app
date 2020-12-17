<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $title
 */
class Goods extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function goodsCategories()
    {
        return $this->belongsToMany(GoodsCategories::class, 'goods_2_goods_categories', 'goods_id', 'goods_categories_id');
    }
}
