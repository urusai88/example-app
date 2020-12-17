<?php

namespace App\Http\Controllers;

use App\Models\Goods;
use App\Models\GoodsCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsController extends Controller
{
    protected function _goodsCategoriesTitleRule()
    {
        return 'string|min:2|unique:goods_categories,title';
    }

    protected function _goodsTitleRule()
    {
        return 'string|min:2|unique:goods,title';
    }

    protected function _goodsCategoriesRule()
    {
        return 'array|min:1|exists:goods_categories,id';
    }

    public function goodsCategoryCreate(Request $request)
    {
        $data = $this->validate($request, [
            'title' => $this->_goodsCategoriesTitleRule() . '|required',
        ]);

        $goodsCategory = new GoodsCategories();
        $goodsCategory->title = $data['title'];
        $goodsCategory->save();

        return $goodsCategory;
    }

    public function goodsCategoryUpdate(Request $request)
    {
        $data = $this->validate($request, [
            'id' => 'required|exists:goods_categories',
            'title' => $this->_goodsCategoriesTitleRule(),
        ]);

        /** @var GoodsCategories $goodsCategories */
        $goodsCategories = GoodsCategories::query()->findOrFail($data['id']);
        if (isset($data['title'])) {
            $goodsCategories->title = $data['title'];
        }
        $goodsCategories->save();

        return $goodsCategories;
    }

    public function goodsCategoryDelete(Request $request)
    {
        $data = $this->validate($request, [
            'id' => 'required|exists:goods_categories',
        ]);

        GoodsCategories::query()->whereKey($data['id'])->delete();
    }

    public function goodsCategoryList(Request $request)
    {
        return GoodsCategories::query()->offset($request->query('offset', 0))->limit(2)->get();
    }

    public function goodsCreate(Request $request)
    {
        $data = $this->validate($request, [
            'goods_categories_id' => $this->_goodsCategoriesRule() . '|required',
            'title' => $this->_goodsTitleRule() . '|required',
        ]);

        /** @var Goods $goods */
        $goods = DB::transaction(function () use ($data) {
            $goods = new Goods();
            $goods->title = $data['title'];
            $goods->save();
            $goods->goodsCategories()->attach($data['goods_categories_id']);

            return $goods;
        });

        return $goods;
    }

    public function goodsUpdate(Request $request)
    {
        $data = $this->validate($request, [
            'id' => 'required|exists:goods',
            'goods_categories_id' => $this->_goodsCategoriesRule(),
            'title' => $this->_goodsTitleRule(),
        ]);

        /** @var Goods $goods */
        $goods = DB::transaction(function () use ($data) {
            /** @var Goods $goods */
            $goods = Goods::query()->with('goodsCategories')->findOrFail($data['id']);

            if (isset($data['title'])) {
                $goods->title = $data['title'];
                $goods->save();
            }

            if (isset($data['goods_categories_id'])) {
                $goods->goodsCategories()->sync($data['goods_categories_id']);
                $goods = $goods->fresh('goodsCategories');
            }

            return $goods;
        });

        return $goods;
    }

    public function goodsDelete(Request $request)
    {
        $data = $this->validate($request, [
            'id' => 'required|exists:goods',
        ]);

        $goods = Goods::query()->findOrFail($data['id']);
        $goods->delete();
    }

    public function goodsList(GoodsCategories $cat, Request $request)
    {
        return $cat->goods()->offset($request->query('offset', 0))->limit(2)->orderByDesc('id')->get();
    }
}
