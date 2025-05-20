<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Add this import
use Illuminate\Http\Request;
use App\Models\Projeto;
use Illuminate\Support\Facades\Auth;

class ProjetoController extends Controller
{
    use AuthorizesRequests; // Add this line

    public function index()
    {
        return Auth::user()->projetos;
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'data_inicio' => 'required|date',
                'data_termino' => 'nullable|date|after:data_inicio',
                'status' => 'nullable|string|in:ativo,inativo,concluido'
            ]);

            $projeto = Auth::user()->projetos()->create($validated);

            return response()->json($projeto, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao criar projeto', 'message' => $e->getMessage()], 500);
        }
    }

    public function show(Projeto $projeto)
    {
        try {
            $this->authorize('view', $projeto);
            return $projeto;
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao exibir projeto', 'message' => $e->getMessage()], 403);
        }
    }

    public function update(Request $request, Projeto $projeto)
    {
        try {
            $this->authorize('update', $projeto);

            $validated = $request->validate([
                'nome' => 'sometimes|string|max:255',
                'descricao' => 'nullable|string',
                'data_inicio' => 'sometimes|date',
                'data_termino' => 'nullable|date|after:data_inicio',
                'status' => 'nullable|string|in:ativo,inativo,concluido'
            ]);

            $projeto->update($validated);

            return response()->json($projeto);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao atualizar projeto', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Projeto $projeto)
    {
        try {
            $this->authorize('delete', $projeto);
            $projeto->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao deletar projeto', 'message' => $e->getMessage()], 500);
        }
    }
}
