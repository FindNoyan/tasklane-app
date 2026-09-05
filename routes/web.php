<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TaskController::class, 'index'])->name('tasks.index');
Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
Route::patch('/tasks/{task}', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
