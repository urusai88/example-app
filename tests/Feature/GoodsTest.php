<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GoodsTest extends TestCase
{
    use DatabaseMigrations;

    public function testCategories()
    {
        /// create
        $this->postJson('/api/goods_categories/create')->assertStatus(422);
        $this->postJson('/api/goods_categories/create', ['title' => 'P'])->assertStatus(422);;

        $resp = $this->postJson('/api/goods_categories/create', ['title' => 'Phones']);
        $resp->assertSuccessful();
        $resp->assertJsonStructure(['id']);
        $id = $resp->json('id');
        $this->assertDatabaseHas('goods_categories', ['id' => $id, 'title' => 'Phones']);
        $this->postJson('/api/goods_categories/create', ['title' => 'Phones'])->assertStatus(422); // дубликат

        /// update
        $this->postJson("/api/goods_categories/update", ['id' => 0, 'title' => 'Notebooks'])->assertStatus(422);

        $resp = $this->postJson("/api/goods_categories/update", ['id' => $id, 'title' => 'Notebooks']);
        $resp->assertStatus(200);
        $resp->assertJsonStructure(['id', 'title']);
        $this->assertDatabaseHas('goods_categories', ['id' => $id, 'title' => 'Notebooks']);

        /// delete
        $this->postJson("/api/goods_categories/delete", ['id' => 0])->assertStatus(422);
        $this->postJson("/api/goods_categories/delete", ['id' => $id])->assertSuccessful();
        $this->assertDatabaseMissing('goods_categories', ['id' => $id]);

        /// list
        $this->postJson('/api/goods_categories/create', ['title' => 'Phones']);
        $this->postJson('/api/goods_categories/create', ['title' => 'Notebooks']);
        $this->postJson('/api/goods_categories/create', ['title' => 'Microwave ovens']);
        $this->postJson('/api/goods_categories/create', ['title' => 'Video cards']);
        $this->postJson('/api/goods_categories/create', ['title' => 'Headphones']);

        $resp = $this->getJson('/api/goods_categories/list');
        $resp->assertJsonCount(2);
        $resp = $this->getJson('/api/goods_categories/list?offset=2');
        $resp->assertJsonCount(2);
        $resp = $this->getJson('/api/goods_categories/list?offset=4');
        $resp->assertJsonCount(1);
    }

    public function testGoods()
    {
        $resp = $this->postJson('/api/goods_categories/create', ['title' => 'Phones']);
        $cat1 = $resp->json(['id']);
        $resp = $this->postJson('/api/goods_categories/create', ['title' => 'Headphones']);
        $cat2 = $resp->json(['id']);

        /// create-fail
        $this->postJson('/api/goods/create')->assertStatus(422); // no data
        $this->postJson('/api/goods/create', ['title' => 'iPhone 5'])->assertStatus(422); // no category
        $this->postJson('/api/goods/create', ['title' => 'iPhone 5', 'goods_categories_id' => []])->assertStatus(422); // empty category
        $this->postJson('/api/goods/create', ['title' => 'iPhone 5', 'goods_categories_id' => [0]])->assertStatus(422); // wrong category
        $this->postJson('/api/goods/create', ['title' => 'iPhone 5', 'goods_categories_id' => 0])->assertStatus(422); // wrong category
        $this->postJson('/api/goods/create', ['goods_categories_id' => [$cat2]])->assertStatus(422); // no title

        $resp = $this->postJson('/api/goods/create', ['goods_categories_id' => [$cat2], 'title' => 'iPhone 5'])
            ->assertSuccessful()
            ->assertJsonStructure(['id']); // success
        $goods1_1 = $resp->json('id');
        $this->assertDatabaseHas('goods', ['id' => $goods1_1, 'title' => 'iPhone 5']); // in db
        $this->postJson('/api/goods/create', ['goods_categories_id' => [$cat2], 'title' => 'iPhone 5'])->assertStatus(422); // unique title

        /// update
        $this->postJson('/api/goods/update')->assertStatus(422); // no data
        $this->postJson('/api/goods/update', ['id' => 0, 'title' => 'iPhone 10'])->assertStatus(422); // wrong id
        $this->postJson('/api/goods/update', ['id' => $goods1_1, 'title' => 'P'])->assertStatus(422); // wrong title
        $resp = $this->postJson('/api/goods/update', ['id' => $goods1_1, 'title' => 'iPhone 20', 'goods_categories_id' => [$cat1]])
            ->assertSuccessful()
            ->assertJsonStructure(['id', 'title', 'goods_categories'])
            ->assertJson(['id' => $goods1_1, 'title' => 'iPhone 20']);
        $this->assertIsArray($resp->json('goods_categories'));
        $this->assertTrue(count($resp->json('goods_categories')) == 1);
        $this->assertTrue(collect($resp->json('goods_categories'))->contains(function ($e) use ($cat1) {
            return $e['id'] == $cat1;
        }));
        $this->assertFalse($resp->json('goods_categories')[0]['id'] == $cat2);

        // fill
        $resp = $this->postJson('/api/goods/create', ['goods_categories_id' => [$cat1], 'title' => 'Samsung Galaxy Note 8']);
        $goods1_2 = $resp->json('id');
        $resp = $this->postJson('/api/goods/create', ['goods_categories_id' => [$cat1], 'title' => 'Samsung Galaxy Note 7']);
        $goods1_3 = $resp->json('id');

        $resp = $this->postJson('/api/goods/create', ['goods_categories_id' => [$cat2], 'title' => 'Sennheiser HD 206']);
        $goods2_1 = $resp->json('id');
        $resp = $this->postJson('/api/goods/create', ['goods_categories_id' => [$cat2], 'title' => 'Sony MDR-EX15LP']);
        $goods2_2 = $resp->json('id');
        $resp = $this->postJson('/api/goods/create', ['goods_categories_id' => [$cat2], 'title' => 'HOCO M60']);
        $goods2_3 = $resp->json('id');

        // list
        $this->getJson('/api/goods/list/10')->assertNotFound();
        $this->getJson('/api/goods/list')->assertNotFound();
        $resp = $this->getJson("/api/goods/list/$cat2")->assertSuccessful()->assertJsonCount(2);
        $this->assertTrue(data_get($resp->json()[0], 'id') == $goods2_3);
        $this->assertTrue(data_get($resp->json()[1], 'id') == $goods2_2);
        $resp = $this->getJson("/api/goods/list/$cat2?offset=2")->assertSuccessful()->assertJsonCount(1);
        $this->assertTrue(data_get($resp->json()[0], 'id') == $goods2_1);

        // delete
        $this->postJson('/api/goods/delete')->assertStatus(422);
        $this->postJson('/api/goods/delete', ['id' => 10])->assertStatus(422);
        $this->postJson('/api/goods/delete', ['id' => $goods2_2])->assertSuccessful();
        $this->postJson('/api/goods/delete', ['id' => $goods2_3])->assertSuccessful();
        $resp = $this->getJson("/api/goods/list/$cat2")->assertSuccessful()->assertJsonCount(1); // check modified list
        $this->assertTrue(data_get($resp->json()[0], 'id') == $goods2_1);

    }
}
