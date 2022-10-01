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
        return response(Post::withTrashed()->findOrFail($id));
    }

    public function delete($id)
    {
        if (Post::findOrFail($id)->delete()) {
            return redirect()->route('home');
        }
        return response([], 404);
    }
}

