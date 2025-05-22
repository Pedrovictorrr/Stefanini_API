<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Models\Projeto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Exception;

class ProjetoController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        try {
            $projetos = Auth::user()->projetos()->get();
            return response()->json($projetos, 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar projetos.',
                'error' => $e->getMessage()
            ], 500);
        }
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
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar projeto.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Projeto $projeto)
    {
        try {
            $this->authorize('view', $projeto);
            return response()->json($projeto, 200);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'Acesso não autorizado.'
            ], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Projeto não encontrado.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar projeto.',
                'error' => $e->getMessage()
            ], 500);
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

            return response()->json($projeto, 200);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'Acesso não autorizado.'
            ], 403);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Projeto não encontrado.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro ao atualizar projeto.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Projeto $projeto)
    {
        try {
            $this->authorize('delete', $projeto);
            $projeto->delete();
            return response()->json(null, 204);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'Acesso não autorizado.'
            ], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Projeto não encontrado.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro ao deletar projeto.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
