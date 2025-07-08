<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentationController extends Controller
{
    public function index()
    {
        return view('docs.index');  // The home page for your docs
    }

    public function authentication_fn()
    {
        return view('docs.authentication');  // The page listing API endpoints
    }

    public function get_districts()
    {
        return view('docs.get_districts');  // Authentication section of docs
    }

    public function create_districts()
    {
        return view('docs.create_districts');  // Error codes and handling
    }
    public function single_districts()
    {
        return view('docs.single_districts');  // Error codes and handling
    }
    public function update_districts()
    {
        return view('docs.update_districts');  // Error codes and handling
    }
}
