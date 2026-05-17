<?php

namespace Webkul\Shopify\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Shopify\DataGrids\Catalog\MetaFieldDataGrid;
use Webkul\Shopify\Helpers\ShoifyMetaFieldType;
use Webkul\Shopify\Http\Requests\MetaFieldForm;
use Webkul\Shopify\Repositories\ShopifyMetaFieldRepository;
use Webkul\Shopify\Services\MetaFieldValidator;

class MetaFieldController extends Controller
{
    /**
     * Kept for backwards-compatibility with any external callers referencing
     * MetaFieldController::SMALLESTUNIT. New code should consume
     * MetaFieldValidator::SMALLESTUNIT directly.
     *
     * @deprecated since 1.x; use MetaFieldValidator::SMALLESTUNIT
     */
    public const SMALLESTUNIT = MetaFieldValidator::SMALLESTUNIT;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ShopifyMetaFieldRepository $shopifyMetaFieldRepository,
        protected AttributeRepository $attributeRepository,
        protected MetaFieldValidator $metaFieldValidator,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return app(MetaFieldDataGrid::class)->toJson();
        }

        $object = (new ShoifyMetaFieldType);
        $metaFieldType = $object->getMetaFieldType();
        $metaFieldTypeInShopify = $object->getMetaFieldTypeInShopify();

        return view('shopify::metafield.index', compact('metaFieldType', 'metaFieldTypeInShopify'));
    }

    /**
     * Create a new MetaField.
     */
    public function store(MetaFieldForm $request): JsonResponse
    {
        $data = $request->all();

        $errors = $this->metaFieldValidator->validateStore($data);

        if (! empty($errors)) {
            return new JsonResponse(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($validationsJson = $this->metaFieldValidator->buildValidationsJson($data)) {
            $data['validations'] = $validationsJson;
        }

        if ($optionsJson = $this->metaFieldValidator->buildOptionsJson($data)) {
            $data['options'] = $optionsJson;
        }

        try {
            $metaFieldCreate = $this->shopifyMetaFieldRepository->create($data);
            session()->flash('success', trans('shopify::app.shopify.metafield.created'));
        } catch (\Exception $e) {
            return new JsonResponse([
                'errors' => ['shopUrl' => [$e->getMessage()]],
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse([
            'redirect_url' => route('shopify.metafield.edit', $metaFieldCreate->id),
        ]);
    }

    /**
     * Back-compat shim. The validation logic now lives in MetaFieldValidator.
     * Kept so any third-party code calling MetaFieldController::checkUnitValue
     * directly (e.g. via subclass) keeps working.
     *
     * @deprecated since 1.x; inject MetaFieldValidator and call ->validateStore()
     *             / ->validateUpdate() instead.
     */
    public function checkUnitValue($data, &$errors)
    {
        $serviceErrors = $this->metaFieldValidator->validateStore(is_array($data) ? $data : (array) $data);

        foreach ($serviceErrors as $key => $value) {
            $errors[$key] = $value;
        }
    }

    /**
     * Back-compat shim. Use MetaFieldValidator::isValidString() in new code.
     *
     * @deprecated since 1.x
     */
    public function isValidString($string)
    {
        return $this->metaFieldValidator->isValidString((string) $string);
    }

    /**
     * Edit a MetaField Definition by ID.
     *
     * @return View
     */
    public function edit(int $id)
    {
        $metaField = $this->shopifyMetaFieldRepository->find($id);

        if (! $metaField) {
            abort(404);
        }

        $object = (new ShoifyMetaFieldType);
        $metaFieldType = $object->getMetaFieldType();
        $metaFieldTypeInShopify = $object->getMetaFieldTypeInShopify();

        return view('shopify::metafield.edit', compact('metaField', 'metaFieldType', 'metaFieldTypeInShopify'));
    }

    /**
     * Update a Meta Field by ID.
     *
     * @return JsonResponse
     */
    public function update(int $id)
    {
        $credential = $this->shopifyMetaFieldRepository->find($id);
        if (! $credential) {
            abort(404);
        }

        $requestData = request()->except(['_token', '_method', 'listvalue']);

        $errors = $this->metaFieldValidator->validateUpdate($requestData);

        $requestData['validations'] = $this->metaFieldValidator->buildValidationsJson($requestData, prettyForUpdate: true);

        if ($optionsJson = $this->metaFieldValidator->buildOptionsJson($requestData, prettyForUpdate: true)) {
            $requestData['options'] = $optionsJson;
        }

        if (! empty($errors)) {
            return redirect()->route('shopify.metafield.edit', $id)
                ->withErrors($errors)
                ->withInput();
        }

        $this->shopifyMetaFieldRepository->update($requestData, $id);
        session()->flash('success', trans('shopify::app.shopify.metafield.update-success'));

        return redirect()->route('shopify.metafield.edit', $id);
    }

    /**
     * Delete a MetaField ID.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->shopifyMetaFieldRepository->delete($id);

        return new JsonResponse([
            'message' => trans('shopify::app.shopify.metafield.delete-success'),
        ]);
    }

    /**
     * Mass Destroy deletes a MetaField
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $metaFieldsId = $massDestroyRequest->input('indices');

        if (empty($metaFieldsId)) {
            return new JsonResponse([
                'message' => trans('shopify::app.shopify.metafield.no-selected'),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $deletedMetaField = $this->shopifyMetaFieldRepository->whereIN('id', $metaFieldsId)->delete();
            if ($deletedMetaField) {
                $message = trans('shopify::app.shopify.metafield.mass-delete-success');
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return new JsonResponse([
            'message' => $message,
        ]);
    }
}
