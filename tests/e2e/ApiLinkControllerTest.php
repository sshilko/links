<?php

namespace Tests\e2e;

use App\Links\CompressedLink;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ApiLinkControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var Collection
     */
    private $links;

    /**
     * @var User
     */
    private $user;

    /**
     * @var User
     */
    private $wrongUser;

    /**
     * @var Collection
     */
    private $wrongLinks;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->wrongUser = factory(User::class)->create();
        $this->actingAs($this->user, 'api');
        $this->links = factory(CompressedLink::class, 3)->make()->each(function (CompressedLink $link) {
            $link->user()->associate($this->user)->save();
        });

        $this->wrongLinks = factory(CompressedLink::class, 3)->make()->each(function (CompressedLink $link) {
            $link->user()->associate($this->wrongUser)->save();
        });

    }

    public function testIndex()
    {
        $response = $this->get('/api/links');
        $response->assertOk();
        $payload = $response->decodeResponseJson()['data'];
        $this->assertEquals($this->links->count(), count($payload));
        $payload = array_map(function ($link) {
            return ['id' => $link['id'], 'link' => $link['link']];
        }, $payload);
        array_walk($payload, function ($link) {
            $this->assertDatabaseHas('compressed_links', $link);
        });
    }

    public function testGet()
    {
        $response = $this->get('/api/links/' . $this->links->first()->id);
        $response->assertOk();
        $payload = $response->decodeResponseJson()['data'];
        $this->assertEquals($this->links->first()->link, $payload['link']);
        $this->assertDatabaseHas('compressed_links', ['id' => $payload['id'], 'link' => $payload['link']]);

    }

    public function testPost()
    {
        $response = $this->post('/api/links', ['link' => 'testword']);
        $this->assertDatabaseHas('compressed_links', ['link' => 'testword']);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testPut()
    {
        $updateValue = 'testword';
        $response = $this->get('/api/links/' . $this->links->first()->id);
        $response->assertOk();
        $link = $response->decodeResponseJson()['data'];
        $link['link'] = $updateValue;

        $response = $this->put('/api/links/' . $this->links->first()->id, $link);
        $response->assertOk();
        $link = $response->decodeResponseJson()['data'];
        $this->assertDatabaseHas('compressed_links', ['link' => $updateValue]);
        $this->assertEquals($updateValue, $link['link']);
    }

    public function testDelete()
    {
        $response = $this->delete('/api/links/' . $this->links->first()->id);
        $response->assertOk();
        $this->assertDatabaseMissing('compressed_links', ['link' => $this->links->first()->link]);
        $response = $this->get('/api/links/' . $this->links->first()->id);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testWrongGet()
    {
        $response = $this->get('/api/links/' . ($this->links->first()->id + 1000));
        $this->assertDatabaseMissing('compressed_links', ['id' => $this->links->first()->id + 1000]);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testWrongPost()
    {
        $response = $this->post('/api/links', ['link' => str_repeat('testword', 1000)]);
        $this->assertDatabaseMissing('compressed_links', ['link' => str_repeat('testword', 1000)]);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testWrongPut()
    {
        $updateValue = str_repeat('testword', 1000);
        $response = $this->get('/api/links/' . $this->links->first()->id);
        $response->assertOk();
        $link = $response->decodeResponseJson()['data'];
        $link['link'] = $updateValue;

        $response = $this->put('/api/links/' . $this->links->first()->id, $link);
        $this->assertDatabaseMissing('compressed_links', ['link' => $updateValue]);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testWrongDelete()
    {
        $response = $this->delete('/api/links/' . ($this->links->first()->id + 1000));
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

}
