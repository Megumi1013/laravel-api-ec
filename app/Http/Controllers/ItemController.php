<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemCollectionResource;
use App\Http\Resources\ItemResource;
use App\Http\Resources\ItemReviewCollectionResource;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $data = $request->all();

        $rules = [
            'page' => 'required|numeric',
            'perPage' => 'required|numeric',
            'orderBy' => 'required|string',
            'orderDirection' => 'required|in:asc,desc',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $this->createErrorResponse(400, 'The request data was invalid', 'request_data_invalid', $validator->errors());
        }

        $items = Item::query()->orderBy($data['orderBy'], $data['orderDirection']);

        // Some other conditional queries

        $data = new ItemCollectionResource($items->paginate($data['perPage']));

        return $this->createSuccessResponse(200, 'Successfully retrieved Items', 'items_index_success', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'is_disabled' => 'required|boolean',
            'price' => 'required|numeric',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $this->createErrorResponse(400, 'The request data was invalid', 'request_data_invalid', $validator->errors());
        }

        $item = new Item;

        $item->name = $data['name'];
        $item->description = $data['description'];
        $item->price = $data['price'];
        $item->is_disabled = $data['is_disabled'];

        if ($item->save()) {

            $data = new ItemResource($item);
            return $this->createSuccessResponse(200, 'Successfully saved Item', 'item_store_success', $data);

        }

        return $this->createErrorResponse(500, 'Item could not be saved', 'item_store_failure');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $item = Item::find($id);

        if ($item) {

            $data = new ItemResource($item);
            return $this->createSuccessResponse(200, 'Successfully retrieved Items', 'items_index_success', $data);

        }

        return $this->createErrorResponse(404, 'The Item was not found', 'item_not_found');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'is_disabled' => 'required|boolean',
            'price' => 'required|numeric',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $this->createErrorResponse(400, 'The request data was invalid', 'request_data_invalid', $validator->errors());
        }

        $item = Item::find($id);

        if ($item) {

            $item->name = $data['name'];
            $item->description = $data['description'];
            $item->price = $data['price'];
            $item->is_disabled = $data['is_disabled'];

            if ($item->save()) {

                $data = new ItemResource($item);
                return $this->createSuccessResponse(200, 'Successfully updated Item', 'item_update_success', $data);

            }

            return $this->createErrorResponse(500, 'Item could not be saved', 'item_store_failure');

        }

        return $this->createErrorResponse(404, 'The Item was not found', 'item_not_found');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $item = Item::find($id);

        if ($item) {

            if ($item->delete()) {
                return $this->createSuccessResponse(200, 'Successfully deleted Item', 'item_destroy_success');
            }

            return $this->createErrorResponse(500, 'Item could not be deleted', 'item_destroy_failure');

        }

        return $this->createErrorResponse(404, 'The Item was not found', 'item_not_found');
    }


}
