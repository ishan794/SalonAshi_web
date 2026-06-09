<?php

namespace App\Modules\Api\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Modules\Api\Services\ApiUserService;

/**
 * Base controller for all /api/* controllers.
 * Provides ok()/fail()/forbidden() helpers and the authenticated ApiUserService.
 */
abstract class ApiBaseController extends Controller
{
    protected ApiUserService $apiUser;
    protected $helpers = ['url'];

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->apiUser = service('apiUser');
    }

    // ── Response helpers ─────────────────────────────────────────────────────

    /** Success — single object or list. Optionally includes pagination meta. */
    protected function ok(mixed $data, array $meta = [], int $code = 200): ResponseInterface
    {
        $payload = ['ok' => true, 'data' => $data];
        if ($meta) $payload['meta'] = $meta;
        return $this->response->setStatusCode($code)->setJSON($payload);
    }

    /** Error response with optional field-level validation errors. */
    protected function fail(string $msg, int $code = 422, array $errors = []): ResponseInterface
    {
        $payload = ['ok' => false, 'msg' => $msg];
        if ($errors) $payload['errors'] = $errors;
        return $this->response->setStatusCode($code)->setJSON($payload);
    }

    protected function forbidden(string $msg = 'Permission denied.'): ResponseInterface
    {
        return $this->fail($msg, 403);
    }

    protected function notFound(string $msg = 'Not found.'): ResponseInterface
    {
        return $this->fail($msg, 404);
    }

    // ── Permission helpers ────────────────────────────────────────────────────

    /**
     * Gate a method on a permission slug.
     * Returns a 403 response if denied, null if allowed.
     * Usage: if ($r = $this->requirePerm('customers.create')) return $r;
     */
    protected function requirePerm(string $perm): ?ResponseInterface
    {
        if (! $this->apiUser->hasPerm($perm)) {
            return $this->forbidden("Requires permission: {$perm}");
        }
        return null;
    }

    /**
     * For staff-scoped endpoints: admin/manager may query any staff_id,
     * stylists are always forced to their own.
     */
    protected function resolveStaffId(?int $requested): ?int
    {
        if ($this->apiUser->isAdmin()) {
            return $requested;
        }
        return $this->apiUser->staffId(); // stylist sees only self
    }

    // ── Body parsing ─────────────────────────────────────────────────────────

    /**
     * Parse JSON body or fall back to POST form data.
     */
    protected function body(): array
    {
        $json = $this->request->getJSON(true);
        return is_array($json) ? $json : ($this->request->getPost() ?? []);
    }

    // ── Pagination meta builder ───────────────────────────────────────────────

    /**
     * Convert the shape returned by searchPaginated()/paginatedSearch() into
     * the standard API meta envelope.
     */
    protected function pageMeta(array $page): array
    {
        return [
            'page'        => $page['currentPage'],
            'per_page'    => $page['perPage'],
            'total'       => $page['total'],
            'total_pages' => $page['totalPages'],
        ];
    }
}
