<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\OpenApi(
 *
 *   @OA\Info(
 *       title="Returns Services API",
 *       version="1.0.0",
 *       description="API documentation for Returns Services API",
 *
 *       @OA\Contact(
 *           email="test@example.com"
 *       ),
 *   ),
 *
 *   @OA\Server(
 *       description="Returns Services API",
 *       url=L5_SWAGGER_CONST_HOST
 *   ),
 *
 *   @OA\PathItem(
 *       path="/"
 *   )
 *  ),
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *    tags={"Users"},
     *     path="/users",
     *     summary="List of all users",
     *
     *     @OA\Parameter(
     *          name="search",
     *          in="query",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="integer",
     *              default=1
     *          )
     *     ),
     *
     *     @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="integer",
     *              default=10
     *          )
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *
     *          @OA\JsonContent(
     *                 type="object",
     *
     *                  @OA\Property(
     *                      property="data",
     *                      type="array",
     *
     *                      @OA\Items(ref="#/components/schemas/UserResource")
     *                  )
     *           )
     *      ),
     *
     *      @OA\Response(
     *          response=500,
     *          description="Server error",
     *
     *          @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="message", type="string", example="Server error. Try again later."),
     *          )
     *      )
     *  )
     */
    public function index(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'page' => ['integer'],
            'per_page' => ['integer'],
        ]);

        try {
            $query = User::query()
                ->when(data_get($data, 'search'), static function ($query) use ($data) {
                    $query->whereLike('name', '%'.data_get($data, 'search', '').'%');
                });

            $userCount = (clone $query)->count();
            $users = $query->orderBy('id', 'desc')
                ->simplePaginate(data_get($data, 'per_page', 10), ['*'], 'page', data_get($data, 'page', 1));

            return UserResource::collection($users)->additional([
                'total' => $userCount,
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['error' => $e]);

            return response()->json([
                'message' => __('Something went wrong. Please try again or contact support.'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *    tags={"Users"},
     *     path="/users",
     *     summary="Create new user",
     *     description="Create new user",
     *
     *     @OA\RequestBody(
     *              required=true,
     *
     *              @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="T3hYq@example.com"),
     *                 @OA\Property(property="password", type="string", example="password"),
     *                 @OA\Property(property="password_confirmation", type="string", example="password"),
     *                 @OA\Property(property="cpf", type="string", example="000.000.000-00"),
     *                 @OA\Property(property="phone", type="string", example="(00) 00000-0000"),
     *              ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *
     *          @OA\JsonContent(
     *                  type="object",
     *
     *                   @OA\Property(
     *                       property="data",
     *                       type="array",
     *
     *                       @OA\Items(ref="#/components/schemas/UserResource")
     *                   )
     *           )
     *      ),
     *
     *      @OA\Response(
     *          response=500,
     *          description="Server error",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="message", type="string", example="Server error. Try again later."),
     *          )
     *       )
     *  )
     */
    public function store(StoreUserRequest $request): JsonResponse|UserResource
    {
        $data = $request->validated();

        try {
            if (data_get($data, 'phone')) {
                $data['phone'] = str_replace(['(', ')'], '', $data['phone']);
            }

            return new UserResource(User::create($data));
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['error' => $e]);

            return response()->json([
                'message' => __('Something went wrong. Please try again or contact support.'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *    tags={"Users"},
     *     path="/users/{id}",
     *     summary="Get user by id",
     *     description="Get user by id",
     *
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *
     *          @OA\JsonContent(
     *                 type="object",
     *
     *                  @OA\Property(
     *                      property="data",
     *                      type="array",
     *
     *                      @OA\Items(ref="#/components/schemas/UserResource"),
     *                  )
     *           )
     *      ),
     *
     *      @OA\Response(
     *           response=404,
     *           description="Not Found",
     *
     *           @OA\JsonContent(
     *                  type="object",
     *
     *                  @OA\Property(property="message", type="string", example="User not found"),
     *           )
     *      ),
     *
     *      @OA\Response(
     *          response=500,
     *          description="Server error",
     *
     *          @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="message", type="string", example="Server error. Try again later."),
     *          )
     *      )
     *  )
     */
    public function show(string $id): JsonResponse|UserResource
    {
        try {
            return new UserResource(User::findOrFail($id));
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => __('User not found'),
            ], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['error' => $e]);

            return response()->json([
                'message' => __('Something went wrong. Please try again or contact support.'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Put(
     *    tags={"Users"},
     *     path="/users/{id}",
     *     summary="Update user by id",
     *     description="Update user by id",
     *
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *
     *     @OA\RequestBody(
     *          required=true,
     *
     *          @OA\MediaType(
     *              mediaType="application/json",
     *
     *              @OA\Schema(
     *                  type="object",
     *
     *                  @OA\Property(property="name", type="string", example="John Doe"),
     *                  @OA\Property(property="email", type="string", example="T3hYq@example.com"),
     *                  @OA\Property(property="password", type="string", example="password"),
     *                  @OA\Property(property="password_confirmation", type="string", example="password"),
     *                  @OA\Property(property="cpf", type="string", example="000.000.000-00"),
     *                  @OA\Property(property="phone", type="string", example="(00) 00000-0000"),
     *              )
     *          )
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *
     *          @OA\JsonContent(
     *                 type="object",
     *
     *                  @OA\Property(
     *                      property="data",
     *                      type="array",
     *
     *                      @OA\Items(ref="#/components/schemas/UserResource"),
     *                  )
     *          )
     *      ),
     *
     *      @OA\Response(
     *           response=404,
     *           description="Not Found",
     *
     *           @OA\JsonContent(
     *                  type="object",
     *
     *                  @OA\Property(property="message", type="string", example="User not found"),
     *           )
     *       ),
     *
     *      @OA\Response(
     *          response=500,
     *          description="Server error",
     *
     *          @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="message", type="string", example="Server error. Try again later."),
     *          )
     *      )
     *  )
     */
    public function update(UpdateUserRequest $request, string $id): JsonResponse|UserResource
    {
        $data = $request->validated();

        try {
            $user = User::findOrFail($id);

            if (data_get($data, 'phone')) {
                $data['phone'] = str_replace(['(', ')'], '', $data['phone']);
            }

            $user->update($data);

            $user->refresh();

            return new UserResource($user);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => __('User not found'),
            ], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['error' => $e]);

            return response()->json([
                'message' => __('Something went wrong. Please try again or contact support.'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *    tags={"Users"},
     *     path="/users/{id}",
     *     summary="Delete user by id",
     *     description="Delete user by id",
     *
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *
     *     @OA\Response(
     *          response=204,
     *          description="No Content",
     *
     *          @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="message", type="string", example="User deleted successfully"),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *
     *          @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="message", type="string", example="User not found"),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=500,
     *          description="Server error",
     *
     *          @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="message", type="string", example="Server error. Try again later."),
     *          )
     *      )
     *  )
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $user->delete();

            return response()->json([
                'message' => __('User deleted successfully'),
            ], Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => __('User not found'),
            ], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['error' => $e]);

            return response()->json([
                'message' => __('Something went wrong. Please try again or contact support.'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
