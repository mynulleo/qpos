<?php

/**
 * @Quill Information Technology
 */

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use App\Http\Controllers\Base\BaseController;

class BrandController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Brand::with('category:id,title')->orderBy('sorting', 'asc')->latest('id');

        if ($request->field_name && $request->value) {
            $query->whereLike($request->field_name, $request->value);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->allData) {
            return $query->get();
        } else {
            $datas = $query->paginate($request->pagination ?? 10);
            return new Resource($datas);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('layouts.backend_app');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($this->validateCheck($request)) {
            try {
                $data = $request->all();
                $res = Brand::create($data);
                return $this->responseReturn("create", $res);
            } catch (Exception $ex) {
                return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Brand $brand)
    {
        if ($request->format() == 'html') {
            return view('layouts.backend_app');
        }
        return $brand;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function edit(Brand $brand)
    {
        return view('layouts.backend_app');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Brand $brand)
    {
        if ($this->validateCheck($request, $brand->id)) {
            try {
                $data = $request->all();
                $brand->fill($data)->save();

                return $this->responseReturn("update", $brand);
            } catch (Exception $ex) {
                return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function destroy(Brand $brand)
    {
        $res = $brand->delete();
        return $this->responseReturn("delete", $res);
    }

    /**
     * Validate form field.
     *
     * @return \Illuminate\Http\Response
     */
    public function validateCheck($request, $id = null)
    {
        return true;
    }
}
