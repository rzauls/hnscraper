<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response(Post::all());
    }

    public function show($id)
    {
        return response(Post::findOrFail($id));
    }

    public function delete($id)
    {
        if (Post::findOrFail($id)->delete()) {
            return view('home', ['posts' => Post::all()]);
        }
        return response([], 404);
    }
}

