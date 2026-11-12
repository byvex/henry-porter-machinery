<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Services\Catalogue\CatalogueAdmin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AdminMediaController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage-catalogue');

        return view('admin.media', [
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'name']),
            'totalCount' => MediaAsset::count(),
        ]);
    }

    public function items(Request $request): JsonResponse
    {
        Gate::authorize('manage-catalogue');

        $query = MediaAsset::query()
            ->with([
                'products' => fn ($q) => $q->select('products.id', 'products.name', 'products.slug', 'product_media.sort_order'),
                'brandsAsLogo:id,name',
                'brandsAsImage:id,name',
                'categoriesAsImage:id,name',
            ])
            ->withCount(['products', 'brandsAsImage', 'brandsAsLogo', 'categoriesAsImage'])
            ->filterSearch($request->query('search'))
            ->filterSource($request->query('source'))
            ->filterAttachment($request->query('attachment'));

        if ($request->filled('brand')) {
            $brandId = $request->query('brand');
            $query->where(function ($q) use ($brandId) {
                $q->whereHas('products', fn ($pq) => $pq->where('brand_id', $brandId))
                    ->orWhereHas('brandsAsImage', fn ($bq) => $bq->where('id', $brandId))
                    ->orWhereHas('brandsAsLogo', fn ($bq) => $bq->where('id', $brandId));
            });
        }

        if ($request->filled('product')) {
            $productId = $request->query('product');
            $query->whereHas('products', fn ($pq) => $pq->where('products.id', $productId));
        }

        $perPage = min(max((int) $request->query('per_page', 36), 12), 120);
        $paginator = $query->latest('created_at')->paginate($perPage);

        $items = $paginator->getCollection()->map(function (MediaAsset $asset) {
            $firstProduct = $asset->products->first();
            $isCover = $firstProduct && ($firstProduct->pivot->sort_order === 0);
            $isWhatsApp = str_contains($asset->storage_path, 'whatsapp') || str_contains((string) $asset->source_path, 'whatsapp');

            return [
                'id' => $asset->id,
                'title' => $asset->title ?: basename($asset->storage_path),
                'alt_text' => $asset->alt_text ?? '',
                'url' => $asset->url(),
                'mime_type' => $asset->mime_type,
                'storage_path' => $asset->storage_path,
                'source_path' => $asset->source_path,
                'checksum' => $asset->checksum ? substr($asset->checksum, 0, 16).'…' : null,
                'file_size' => $asset->formattedFileSize(),
                'created_at' => $asset->created_at?->format('d M Y, H:i') ?? '',
                'is_whatsapp' => $isWhatsApp,
                'is_cover' => $isCover,
                'products_count' => $asset->products_count,
                'brands_as_image_count' => $asset->brands_as_image_count,
                'brands_as_logo_count' => $asset->brands_as_logo_count,
                'categories_as_image_count' => $asset->categories_as_image_count,
                'products' => $asset->products->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sort_order' => $p->pivot->sort_order,
                    'is_cover' => $p->pivot->sort_order === 0,
                    'edit_url' => route('admin.catalogue.edit', ['resource' => 'products', 'id' => $p->id]),
                ]),
                'brands_as_logo' => $asset->brandsAsLogo->map(fn ($b) => [
                    'id' => $b->id,
                    'name' => $b->name,
                    'edit_url' => route('admin.catalogue.edit', ['resource' => 'brands', 'id' => $b->id]),
                ]),
                'brands_as_image' => $asset->brandsAsImage->map(fn ($b) => [
                    'id' => $b->id,
                    'name' => $b->name,
                    'edit_url' => route('admin.catalogue.edit', ['resource' => 'brands', 'id' => $b->id]),
                ]),
                'categories_as_image' => $asset->categoriesAsImage->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'edit_url' => route('admin.catalogue.edit', ['resource' => 'categories', 'id' => $c->id]),
                ]),
            ];
        });

        return response()->json([
            'data' => $items,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        Gate::authorize('manage-catalogue');

        $request->validate([
            'files' => 'required|array',
            'files.*' => 'image|max:10240',
        ]);

        $assets = app(CatalogueAdmin::class)->uploadToLibrary($request->file('files'));

        return response()->json([
            'success' => true,
            'count' => count($assets),
            'message' => count($assets).' image(s) uploaded successfully.',
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        Gate::authorize('manage-catalogue');

        $asset = app(CatalogueAdmin::class)->updateMediaMetadata($id, $request->only('title', 'alt_text'));

        return response()->json([
            'success' => true,
            'message' => 'Details saved.',
            'asset' => [
                'id' => $asset->id,
                'title' => $asset->title,
                'alt_text' => $asset->alt_text,
            ],
        ]);
    }

    public function detach(Request $request, string $id): JsonResponse
    {
        Gate::authorize('manage-catalogue');

        $request->validate(['product_id' => 'required|uuid']);
        app(CatalogueAdmin::class)->detachMediaFromProduct($id, $request->input('product_id'));

        return response()->json([
            'success' => true,
            'message' => 'Image detached from product.',
        ]);
    }

    public function bulkDetach(Request $request): JsonResponse
    {
        Gate::authorize('manage-catalogue');

        $request->validate(['ids' => 'required|array', 'ids.*' => 'uuid']);
        $ids = $request->input('ids');

        DB::table('product_media')->whereIn('media_asset_id', $ids)->delete();

        return response()->json([
            'success' => true,
            'count' => count($ids),
            'message' => 'Detached '.count($ids).' image(s) from products.',
        ]);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        Gate::authorize('manage-catalogue');

        $request->validate(['ids' => 'required|array', 'ids.*' => 'uuid']);
        $result = app(CatalogueAdmin::class)->deleteMediaAssets($request->input('ids'), detachFirst: true);

        $msg = "Permanently deleted {$result['deleted']} image(s).";
        if ($result['skipped_protected'] > 0) {
            $msg .= " ({$result['skipped_protected']} protected or referenced image(s) were skipped).";
        }

        return response()->json([
            'success' => true,
            'deleted' => $result['deleted'],
            'skipped' => $result['skipped_protected'],
            'message' => $msg,
        ]);
    }

    public function deleteSingle(string $id): JsonResponse
    {
        Gate::authorize('manage-catalogue');

        $result = app(CatalogueAdmin::class)->deleteMediaAssets([$id], detachFirst: true);

        return response()->json([
            'success' => $result['deleted'] > 0,
            'message' => $result['deleted'] > 0 ? 'Image permanently deleted.' : 'Image is protected or referenced and could not be deleted.',
        ]);
    }

    public function prune(): JsonResponse
    {
        Gate::authorize('manage-catalogue');

        Artisan::call('catalogue:prune-orphaned-media', ['--apply' => true]);
        $output = trim(Artisan::output());

        return response()->json([
            'success' => true,
            'output' => $output ?: 'Orphaned media pruned successfully.',
        ]);
    }
}
