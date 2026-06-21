<?php

namespace App\Core\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class BaseApiController extends Controller
{
    protected function respond(mixed $data, int $status = ResponseInterface::HTTP_OK): ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setJSON(['success' => true, 'data' => $data]);
    }

    protected function respondError(
        string $message,
        int $code = ResponseInterface::HTTP_BAD_REQUEST,
        array $errors = []
    ): ResponseInterface {
        return $this->response
            ->setStatusCode($code)
            ->setJSON(['success' => false, 'message' => $message, 'errors' => $errors]);
    }

    protected function respondNotFound(string $message = 'Not found'): ResponseInterface
    {
        return $this->respondError($message, ResponseInterface::HTTP_NOT_FOUND);
    }

    protected function respondUnauthorized(string $message = 'Unauthorized'): ResponseInterface
    {
        return $this->respondError($message, ResponseInterface::HTTP_UNAUTHORIZED);
    }

    protected function respondCreated(mixed $data): ResponseInterface
    {
        return $this->respond($data, ResponseInterface::HTTP_CREATED);
    }
}
