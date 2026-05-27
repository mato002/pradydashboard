<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Products\ProductOperationsService;
use App\Http\Controllers\Concerns\ExportsAdminListing;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Admin\ListingFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    use ExportsAdminListing;

    public function __construct(
        private readonly ProductOperationsService $operations
    ) {}

    public function index(Request $request): View
    {
        $query = $this->listingQuery($request);
        $allProducts = Product::query()
            ->withCount(['hostedProjects', 'tenants'])
            ->orderBy('name')
            ->get();

        $products = $query->paginate(12)->withQueryString();

        $enrichedRows = $products->getCollection()->map(fn (Product $product) => [
            'product' => $product,
            'meta' => $this->operations->enrich($product),
        ]);

        $categories = Product::query()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category', 'category');

        return view('admin.products.index', [
            'products' => $products,
            'enrichedRows' => $enrichedRows,
            'kpis' => $this->operations->kpis($allProducts),
            'filters' => ListingFilters::fromRequest($request, ['q', 'status', 'category']),
            'exportQuery' => ListingFilters::queryExceptPage($request),
            'categoryOptions' => $categories,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->listingQuery($request)->get()->map(fn (Product $p) => [
            $p->name,
            $p->slug,
            $p->category,
            $p->status,
            $p->hosted_projects_count,
            $p->tenants_count,
        ]);

        return $this->exportCsv(
            'products-'.now()->format('Y-m-d-His').'.csv',
            ['Name', 'Slug', 'Category', 'Status', 'Hosted projects', 'Tenants'],
            $rows,
        );
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'product' => new Product,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Product::query()->create($this->validated($request));

        return redirect()->route('products.index')->with('status', __('Product created.'));
    }

    public function show(Product $product): View
    {
        $product->load([
            'hostedProjects' => fn ($q) => $q->with('server')->withCount('tenants')->orderBy('domain'),
            'tenants' => fn ($q) => $q->with('hostedProject')->orderBy('company_name')->limit(20),
        ]);

        return view('admin.products.show', [
            'product' => $product,
            'meta' => $this->operations->enrich($product),
        ]);
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($this->validated($request, $product));

        return redirect()->route('products.show', $product)->with('status', __('Product updated.'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')->with('status', __('Product removed.'));
    }

    private function listingQuery(Request $request)
    {
        $query = Product::query()
            ->withCount(['hostedProjects', 'tenants'])
            ->orderBy('name');

        ListingFilters::applySearch($query, $request->query('q'), ['name', 'slug', 'description', 'category']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('products', 'slug')->ignore($product?->getKey()),
            ],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,suspended,archived'],
            'default_billing_model' => ['required', 'in:subscription,per_seat,usage,enterprise'],
            'default_license_mode' => ['required', 'in:module,full,feature_flags'],
        ]);

        if (empty($data['slug']) && filled($data['name'])) {
            $data['slug'] = Product::generateUniqueSlug($data['name']);
        }

        return $data;
    }
}
