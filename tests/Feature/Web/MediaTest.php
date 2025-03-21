<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaTest extends BaseSetup
{
    private string $filename;

    private Media $media;

    protected function setUp(): void
    {
        parent::setUp();

        $file = UploadedFile::fake()->image('TestFile.png');

        $this->media = $this->user->addMedia($file)
            ->usingFileName($this->filename = Str::random() . '.png')
            ->toMediaCollection();
    }

    public function test_download_media(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('media.{media}.{filename}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/media/' . $this->media->id . '/' . $this->filename)
            ->assertStatus(200)
            ->assertDownload();
    }

    public function test_download_media_media_not_found(): void
    {
        $this->media->delete();

        $this->user->givePermissionTo(Permission::findOrCreate('media.{media}.{filename}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/media/' . $this->media->id . '/' . $this->filename)
            ->assertStatus(404);
    }

    public function test_download_media_no_user(): void
    {
        $this->get('/media/' . $this->media->id . '/' . $this->filename)
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_download_media_without_permission(): void
    {
        Permission::findOrCreate('media.{media}.{filename}.get', 'web');

        $this->actingAs($this->user, 'web')->get('/media/' . $this->media->id . '/' . $this->filename)
            ->assertStatus(403);
    }
}
