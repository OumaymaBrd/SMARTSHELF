<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     title="API Supermarché",
 *     version="1.0.0",
 *     description="API pour la gestion d'un supermarché",
 *     @OA\Contact(
 *         email="contact@example.com",
 *         name="Support API"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://127.0.0.1:8000/api",
 *     description="Serveur local"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class SwaggerController extends Controller
{
    //
}