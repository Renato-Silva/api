<?php

declare(strict_types=1);

namespace VOSTPT\Http\Controllers;

use Illuminate\Http\JsonResponse;
use VOSTPT\Filters\Contracts\ParishFilter;
use VOSTPT\Http\Requests\Parish\Index;
use VOSTPT\Http\Requests\Parish\View;
use VOSTPT\Http\Serializers\ParishSerializer;
use VOSTPT\Models\Parish;
use VOSTPT\Repositories\Contracts\ParishRepository;

class ParishController extends Controller
{
    /**
     * Index Counties.
     *
     * @param Index            $request
     * @param ParishFilter     $filter
     * @param ParishRepository $parishRepository
     *
     * @throws \OutOfBoundsException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Index $request, ParishFilter $filter, ParishRepository $parishRepository): JsonResponse
    {
        $filter->setSortColumn($request->input('sort', $filter->getSortColumn()))
            ->setSortOrder($request->input('order', $filter->getSortOrder()))
            ->setPageNumber((int) $request->input('page.number', 1))
            ->setPageSize((int) $request->input('page.size', 10));

        if ($search = $request->input('search')) {
            $filter->withSearch($search);
        }

        $paginator = $this->createPaginator(Parish::class, $parishRepository->createQueryBuilder(), $filter);

        return response()->paginator($paginator, new ParishSerializer());
    }

    /**
     * View a Parish.
     *
     * @param View   $request
     * @param Parish $parish
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(View $request, Parish $parish): JsonResponse
    {
        return response()->resource($parish, new ParishSerializer(), [
            'county',
        ]);
    }
}
