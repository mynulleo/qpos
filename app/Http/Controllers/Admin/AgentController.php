<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Agent;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Base\BaseController;

class AgentController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Agent::latest();

        if (!empty($request->field_name) && !empty($request->value)) {
            $query->whereLike($request->field_name, $request->value);
        }

        if (!empty($request->status)) {
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

                if (!empty($request->image_base64)) {
                    $data["image"] = $this->upload($request->image_base64, "agent", null, true);
                } else if (!empty($request->file("image"))) {
                    $data["image"] = $this->upload($request->image, "agent");
                }

                $res = Agent::create($data);
                return $this->responseReturn("create", $res);
            } catch (Exception $ex) {
                return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if ($request->format() == 'html') {
            return view('layouts.backend_app');
        }
        $agent = Agent::find($id);
        return $agent;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('layouts.backend_app');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $agent = Agent::find($id);
        if ($this->validateCheck($request, $agent->id)) {
            try {
                $data = $request->all();

                if (!empty($request->image_base64)) {
                    $data["image"] = $this->upload($request->image_base64, "agent", $agent->image, true);
                } else if (!empty($request->file("image"))) {
                    if (!empty($agent->image)) {
                        $oldFile = $this->oldFile($agent->image);
                        Storage::delete($oldFile);
                    }
                    $data["image"] = $this->upload($request->image, "agent");
                }

                $agent->fill($data)->save();
                return $this->responseReturn("update", $agent);
            } catch (Exception $ex) {
                return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $agent = Agent::find($id);
        if ($agent) {
            if (!empty($agent->image)) {
                $oldFile = $this->oldFile($agent->image);
                if (Storage::disk("public")->exists($oldFile)) {
                    Storage::delete($oldFile);
                }
            }
            $res = $agent->delete();
            return $this->responseReturn("delete", $res);
        }
        return response()->json(['exception' => 'Agent not found'], 404);
    }

    public function getAgents()
    {
        return Agent::where('status', 'active')->get(['id', 'full_name', 'mobile', 'organization', 'designation', 'address']);
    }

    public function getAgentInfo($id)
    {
        $agent = Agent::find($id);
        return response()->json($agent);
    }

    /**
     * Validate form field.
     *
     * @return bool
     */
    public function validateCheck($request, $id = null)
    {
        return true;
    }
}
