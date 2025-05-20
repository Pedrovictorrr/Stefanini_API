<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Models\Projeto;
use Illuminate\Support\Facades\Auth;

class ProjetoController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        // Retorna todos os projetos do usuário autenticado como array JSON
        $projetos = Auth::user()->projetos()->get();
        return response()->json($projetos, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'data_inicio' => 'required|date',
            'data_termino' => 'nullable|date|after:data_inicio',
            'status' => 'nullable|string|in:ativo,inativo,concluido'
        ]);

        $projeto = Auth::user()->projetos()->create($validated);

        return response()->json($projeto, 201);
    }

    public function show(Projeto $projeto)
    {
        $this->authorize('view', $projeto);
        return response()->json($projeto, 200);
    }

    public function update(Request $request, Projeto $projeto)
    {
        $this->authorize('update', $projeto);

        $validated = $request->validate([
            'nome' => 'sometimes|string|max:255',
            'descricao' => 'nullable|string',
            'data_inicio' => 'sometimes|date',
            'data_termino' => 'nullable|date|after:data_inicio',
            'status' => 'nullable|string|in:ativo,inativo,concluido'
        ]);

        $projeto->update($validated);

        return response()->json($projeto, 200);
    }

    public function destroy(Projeto $projeto)
    {
        $this->authorize('delete', $projeto);
        $projeto->delete();
        return response()->json(null, 204);
    }
}
