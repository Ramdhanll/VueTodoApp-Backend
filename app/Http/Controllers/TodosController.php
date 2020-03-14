<?php

namespace App\Http\Controllers;

use App\Todo;
use Illuminate\Http\Request;

class TodosController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
      return Todo::where('user_id', auth()->user()->id)->get();
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
      $data = $request->validate([
          'title'   => 'required|string',
          'completed' => 'required|boolean'
      ]);

      $todo = Todo::create([
        'user_id' => auth()->user()->id,
        'title'   => $request->title,
        'completed' =>$request->completed
      ]);

      return response($todo, 201);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Todo  $todo
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, Todo $todo)
  {

    /**
     * Karna sudah ada $todo jadi otomatis sudah mendapatkan
     * id todo mana yang harus diupdate
     */

    if ($todo->user_id !== auth()->user()->id) {
      return response()->json('Unauthorized', 401);
    }

    $data = $request->validate([
        'title'   => 'required|string',
        'completed' => 'required|boolean'
    ]);

    $todo->update($data);

    return response($todo, 200);
  }

  public function updateAll(Request $request) {

    $data = $request->validate([
      'completed' => 'required|boolean'
    ]);

    Todo::where('user_id', auth()->user()->id)->update($data); // update completed todos menjadi true berdasarkan user_id

    return response('Updated all completed', 200);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Todo  $todo
   * @return \Illuminate\Http\Response
   */
  public function destroy(Todo $todo) {
    if ($todo->user_id !== auth()->user()->id) {
      return response()->json('Unauthorized', 401);
    }

    $todo->delete();
    return response('Deleted todo item', 200);

  }

  public function destroyCompleted(Request $request) {

    // ambil id yang mau di delete
    $todosToDelete = $request->todos;
    // ambil id todo berdasarkan auth user
    $userTodoIds = auth()->user()->todos->map(function($todo){
      return $todo->id;
    });

    /**
     * The every method may be used to verify that all elements of a collection pass a given truth test:
     */
    // apakah $userTodoIds berisi $todoToDelete
    $valid = collect($todosToDelete)->every(function($value, $key) use ($userTodoIds) {
      return $userTodoIds->contains($value);
    });
    // Lakukan validasi
    if(!$valid){
      return response()->json('Unauthorized', 401); 
    }

    $request->validate([
      'todos'   => 'required|array',
    ]);
    // Delete
    Todo::destroy($request->todos); // deleted sekaligus

    return response()->json('Deleted', 200);
  }
}
