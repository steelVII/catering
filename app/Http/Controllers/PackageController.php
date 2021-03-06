<?php

namespace App\Http\Controllers;

use App\Package;
use Illuminate\Http\Request;
use DataTables;
use DB;
use Carbon\Carbon;
use Session;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.package.index');
    }

    public function data(Datatables $datatables)
    {
        $packages = Package::all();

        return Datatables::of($packages)
            ->addColumn('actions', function($package) {
                return view('admin.action.packages', compact('package'))->render();
            })
            ->editColumn('pdf_file', function ($package) {
                return '<a target="_blank" href="'. asset('storage/pdf/'.$package->pdf_file) .'">'.$package->pdf_file.'</a>';
            })
            ->editColumn('status', function ($package) {
                
                if ($package->trashed())
                {
                    return '<span class="label label-danger">Yes</span>';
                }
                else
                {
                    return '<span class="label label-success">No</span>';
                }


            })
            ->editColumn('display', function ($package) {
                
                if ($package->is_display == 1)
                {
                    return '<span class="label label-success">Active</span>';
                }
                else
                {
                    return '<span class="label label-danger">Not Active</span>';
                }


            })
            ->editColumn('updated_at', function ($package) {
                return $package->updated_at ? with(new Carbon($package->updated_at))->format('d M Y, h:i A') : '';
            })
            ->rawColumns(['actions','pdf_file','status','display'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.package.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $file = $request->file('pdf');
        
        $filename = time().'.'.$file->getClientOriginalExtension();
        
        $destinationPath = 'storage/pdf';
        $file->move($destinationPath,$filename);

        $package = new Package;

        $package->name = $input['name'];
        $package->description = $input['description'];
        $package->price_start = $input['price_start'];
        $package->is_pax = $input['is_pax'];
        $package->is_pdf = $input['is_pdf'];
        $package->pdf_file = $filename;
        $package->terms = $input['terms'];

        $package->save();

        Session::flash('message', 'New Package Was Successfully Added!'); 
        Session::flash('alert-class', 'alert-success');

        return redirect('admin/package');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function show(Package $package, $package_id)
    {
        $package = Package::find($package_id);

        if ($package === null) {
            abort(404);
        }

        return view('admin.package.show',[
            'package' => $package
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function edit(Package $package, $package_id)
    {
        $package = Package::find($package_id);

        if ($package === null) {
            abort(404);
        }

        return view('admin.package.edit',[
            'package' => $package
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Package $package, $package_id)
    {
        $input = $request->all();

        $package = Package::find($package_id);

        if ($request->hasFile('pdf'))
        {
            $file = $request->file('pdf');
        
            $filename = time().'.'.$file->getClientOriginalExtension();
        
            $destinationPath = 'storage/pdf';
            $file->move($destinationPath,$filename);

            $package->pdf_file = $filename;
        }

        
        $package->name = $input['name'];
        $package->description = $input['description'];
        $package->price_start = $input['price_start'];
        $package->is_pax = $input['is_pax'];
        $package->is_pdf = $input['is_pdf'];
        $package->terms = $input['terms'];

        $package->save();

        Session::flash('message', 'Package Succesfully Updated!'); 
        Session::flash('alert-class', 'alert-success');

        return redirect('admin/package/'.$package_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function destroy(Package $package)
    {
        //
    }

    public function active($package_id)
    {
        $package = Package::find($package_id);
        $package->is_display = 1;
        $package->save();

        Session::flash('message', 'New Package Was Successfully Show On Order Page!'); 
        Session::flash('alert-class', 'alert-success');

        return redirect('admin/package/'.$package_id);
    }

    public function deactive($package_id)
    {
        $package = Package::find($package_id);
        $package->is_display = 0;
        $package->save();

        Session::flash('message', 'New Package Was Successfully Hided on Order Page!'); 
        Session::flash('alert-class', 'alert-success');

        return redirect('admin/package/'.$package_id);
    }
}
