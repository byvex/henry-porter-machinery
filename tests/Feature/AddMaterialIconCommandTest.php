<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AddMaterialIconCommandTest extends TestCase
{
    private string $iconPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->iconPath = storage_path('framework/testing/icons-'.uniqid());
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->iconPath);
        parent::tearDown();
    }

    private function svg(string $d): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="'.$d.'"/></svg>';
    }

    public function test_it_generates_a_component_with_outlined_and_filled_paths(): void
    {
        Http::fake([
            '*/check_circle/default/24px.svg' => Http::response($this->svg('M1-OUTLINED')),
            '*/check_circle/fill1/24px.svg' => Http::response($this->svg('M2-FILLED')),
        ]);

        $this->artisan('icon:add', ['names' => ['check-circle'], '--path' => $this->iconPath])->assertSuccessful();

        $contents = File::get($this->iconPath.'/check-circle.blade.php');
        $this->assertStringContainsString("@props(['filled' => false])", $contents);
        $this->assertStringContainsString('M1-OUTLINED', $contents);
        $this->assertStringContainsString('M2-FILLED', $contents);
        $this->assertStringContainsString('viewBox="0 -960 960 960"', $contents);
        $this->assertStringContainsString("'aria-hidden' => 'true'", $contents);
    }

    public function test_it_falls_back_to_the_outlined_path_when_there_is_no_filled_variant(): void
    {
        Http::fake([
            '*/add/default/24px.svg' => Http::response($this->svg('M-ADD')),
            '*/add/fill1/24px.svg' => Http::response('', 404),
        ]);

        $this->artisan('icon:add', ['names' => ['add'], '--path' => $this->iconPath])->assertSuccessful();

        $this->assertSame(2, substr_count(File::get($this->iconPath.'/add.blade.php'), 'M-ADD'));
    }

    public function test_it_skips_existing_icons_unless_forced(): void
    {
        Http::fake(['*' => Http::response($this->svg('M-NEW'))]);
        File::ensureDirectoryExists($this->iconPath);
        File::put($this->iconPath.'/home.blade.php', 'original');

        $this->artisan('icon:add', ['names' => ['home'], '--path' => $this->iconPath])->assertSuccessful();
        $this->assertSame('original', File::get($this->iconPath.'/home.blade.php'));

        $this->artisan('icon:add', ['names' => ['home'], '--path' => $this->iconPath, '--force' => true])->assertSuccessful();
        $this->assertStringContainsString('M-NEW', File::get($this->iconPath.'/home.blade.php'));
    }

    public function test_it_rejects_invalid_and_reserved_names(): void
    {
        Http::fake();

        $this->artisan('icon:add', ['names' => ['../evil', 'index'], '--path' => $this->iconPath])->assertFailed();

        Http::assertNothingSent();
        $this->assertFileDoesNotExist($this->iconPath.'/index.blade.php');
    }

    public function test_it_fails_cleanly_when_the_icon_does_not_exist_upstream(): void
    {
        Http::fake(['*' => Http::response('', 404)]);

        $this->artisan('icon:add', ['names' => ['not_a_real_icon'], '--path' => $this->iconPath])->assertFailed();

        $this->assertFileDoesNotExist($this->iconPath.'/not-a-real-icon.blade.php');
    }

    public function test_generated_icons_render_through_the_dynamic_wrapper(): void
    {
        $this->blade('<x-icon name="arrow_forward" class="size-4" />')
            ->assertSee('<svg', false)
            ->assertSee('size-4', false)
            ->assertSee('aria-hidden="true"', false);

        $this->blade('<x-site.button href="/x" iconTrailing="arrow_outward">Go</x-site.button>')
            ->assertSee('icon-nudge icon-nudge--outward', false);
    }
}
