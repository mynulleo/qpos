<?php

/**
 * @Quill Information Technology
 */

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Series;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use App\Http\Controllers\Base\BaseController;

class SeriesController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Series::with(['category:id,title', 'brand:id,title'])
            ->orderBy('sorting', 'asc')
            ->latest('id');

        if ($request->field_name && $request->value) {
            $query->whereLike($request->field_name, $request->value);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->brand_id) {
            $query->where('brand_id', $request->brand_id);
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
        return view('admin.layouts.admin_app');
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
                $res = Series::create($data);
                return $this->responseReturn("create", $res);
            } catch (Exception $ex) {
                return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }
        $series = Series::with(['category:id,title', 'brand:id,title'])->find($id);
        if (!$series) {
            return response()->json(['message' => 'Series not found'], 404);
        }
        return $series;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('admin.layouts.admin_app');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $series = Series::find($id);
        if (!$series) {
            return response()->json(['message' => 'Series not found'], 404);
        }

        if ($this->validateCheck($request, $series->id)) {
            try {
                $data = $request->all();
                $series->fill($data)->save();

                return $this->responseReturn("update", $series);
            } catch (Exception $ex) {
                return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $series = Series::find($id);
        if (!$series) {
            return response()->json(['message' => 'Series not found'], 404);
        }
        $res = $series->delete();
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
