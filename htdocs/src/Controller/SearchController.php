<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Task;
use App\Entity\TaskList;
use App\Helper\ApiResponse;
use App\Repository\GroupRepository;
use App\Repository\TaskListRepository;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/search", name="search_")
 */
class SearchController extends AbstractController
{

    /**
     * @Route("/task/", name="task", methods={"GET"})
     * @param Request $request
     * @return ApiResponse
     */
    public function task(Request $request)
    {
        $tasks = $this->getRepositoryTask()->findSearch(
            $request->get('name'),
            $request->get('description'),
            [
                'value' => $request->get('eta')['value'] ?? null,
                'cond' => $request->get('eta')['cond'] ?? '='
            ],
            [
                'value' => $request->get('spend')['value'] ?? null,
                'cond' => $request->get('spend')['cond'] ?? '='
            ],
            $request->get('status')
        );
        return new ApiResponse('', $tasks);
    }

    /**
     * @Route("/group", name="group", methods={"GET"})
     * @param Request $request
     * @return ApiResponse
     */
    public function group(Request $request)
    {
        $groups = $this->getRepositoryTask()->findSearch(
            $request->get('name'),
            $request->get('description'),
            [
                'value' => $request->get('eta')['value'] ?? null,
                'cond' => $request->get('eta')['cond'] ?? '='
            ]
        );
        return new ApiResponse('', $groups);
    }

    /**
     * @Route("/task/list/", name="task_list", methods={"GET"})
     * @param Request $request
     * @return ApiResponse
     */
    public function taskList(Request $request)
    {
        $taskList = $this->getRepositoryTaskList()->findSearch($request->get('name'));
        return new ApiResponse('', $taskList);
    }

    /**
     * @return TaskRepository
     */
    private function getRepositoryTask(): TaskRepository
    {
        return $this->getDoctrine()->getRepository(Task::class);
    }

    /**
     * @return GroupRepository
     */
    private function getRepositoryGroup(): GroupRepository
    {
        return $this->getDoctrine()->getRepository(Group::class);
    }

    /**
     * @return TaskListRepository
     */
    private function getRepositoryTaskList(): TaskListRepository
    {
        return $this->getDoctrine()->getRepository(TaskList::class);
    }
}